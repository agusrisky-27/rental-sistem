# Plan: SewaKen full stack (admin + pelanggan)

## Jawaban user
1. Full stack = tambah sisi pelanggan (daftar, login, katalog, booking, upload bukti bayar, riwayat). Admin tetap pakai dashboard.
2. Satu app Vue, route terpisah: `/` publik, `/akun/*` pelanggan, `/admin/*` admin.
3. Konteks: tugas kuliah / portfolio.
4. Role: `admin`, `pelanggan`.
5. Tabel `pelanggan` diberi `user_id` nullable; admin tetap bisa tambah pelanggan walk-in tanpa akun.
6. Register publik hanya untuk pelanggan. Admin dibuat lewat seeder.
7. Alur: `menunggu` -> pelanggan upload bukti -> admin verifikasi -> `aktif` (kendaraan `disewa`) -> pengembalian (hitung denda) -> `selesai` (kendaraan `tersedia`). Booking `menunggu` tanpa pembayaran pending/berhasil selama 24 jam otomatis `dibatalkan`.
8. Bayar lunas sekaligus, tanpa DP.
9. Transfer manual + upload bukti. Tanpa payment gateway.
10. Denda = harga/hari x hari telat, dibayar terpisah, masuk pendapatan hanya setelah dibayar.
11. Hapus kendaraan/pelanggan = soft delete.
12. Sewa per hari, tanpa sopir, tanpa lokasi antar.
13. Fitur tambahan: notifikasi (admin & pelanggan), kalender ketersediaan, laporan pendapatan per bulan + export CSV + cetak/PDF via print browser.
14. Stack tetap: Laravel 13 + Sanctum token, Vue 3 + Pinia + Vue Router + Tailwind 3, MySQL (test: sqlite memory).
15. Jalan lokal saja, tanpa deploy/Docker.
16. Tanpa deadline.
17. Desain halaman pelanggan ikut gaya dashboard sekarang (Tailwind, Material Symbols, dark mode).
18. Kriteria selesai: feature test lulus, `npm run build` tanpa error, alur pelanggan bisa dijalankan manual.

## Keputusan final

### Backend
- Tidak ada service layer. Logika bersama ditaruh sebagai method statis/scope di model (pola codebase: controller + model).
- Validasi tetap inline `$request->validate()` (pola codebase). Tidak menambah Form Request / API Resource.
- Middleware `App\Http\Middleware\EnsureRole` (alias `role`), pakai: `role:admin`, `role:pelanggan`. Balas 403 `{"message":"Akses ditolak."}`.
- `POST /auth/login` dan `POST /auth/register` diberi `throttle:5,1`.
- Register: buat `users` (role `pelanggan`) + baris `pelanggan` (`user_id`, nama, email, telepon, level `Basic`). Jika ada pelanggan walk-in dengan email sama dan `user_id` null, baris itu ditautkan (bukan dibuat baru).
- Login/me mengembalikan user + relasi `pelanggan` (untuk role pelanggan).
- Status transaksi: `menunggu`, `aktif`, `selesai`, `dibatalkan`. Transisi yang sah:
  - `menunggu` -> `aktif` (verifikasi pembayaran sewa, atau admin `konfirmasi` = bayar tunai)
  - `menunggu` -> `dibatalkan` (pelanggan batal, admin tolak, auto-expire)
  - `aktif` -> `selesai` (hanya lewat pencatatan pengembalian)
  - Selain itu balas 422 `{"message": "..."}`.
- Bentrok jadwal: transaksi lain di kendaraan yang sama dengan status `menunggu|aktif` dan `mulai_lama < akhir_baru AND akhir_lama > mulai_baru`. Hari kembali boleh sama dengan hari mulai booking berikutnya.
- Booking ditolak (422) jika kendaraan `maintenance`, jadwal bentrok, atau `tanggal_mulai` < hari ini (khusus pelanggan). Durasi = `max(1, hari antara mulai dan akhir)`, total = harga x durasi.
- Booking TIDAK mengubah `kendaraan.status`. `disewa` di-set saat transaksi jadi `aktif`; `tersedia` di-set saat selesai/batal jika tidak ada transaksi `aktif` lain di kendaraan itu.
- Pembayaran punya kolom `jenis` (`sewa|denda`, default `sewa`) dan `catatan` (alasan tolak). `jumlah` selalu dihitung server: sewa = `transaksi.total`, denda = `pengembalian.denda`. Tidak diambil dari request.
- Satu transaksi bisa punya banyak pembayaran (re-upload setelah ditolak). Relasi `Transaksi::pembayaran()` = pembayaran sewa terbaru (`hasOne ... ofMany`), plus `Transaksi::semuaPembayaran()` hasMany.
- Upload bukti ditolak (422) jika masih ada pembayaran jenis sama berstatus `menunggu verifikasi` atau sudah `berhasil`.
- Verifikasi dan tolak hanya dari status `menunggu verifikasi`.
- Pengembalian: satu jalur, `Pengembalian::catat(Transaksi, tanggal_kembali, kondisi)`, hanya untuk transaksi `aktif`. Hitung telat dari tanggal (startOfDay), denda = harga x telat. Status `tepat waktu|terlambat`. Kolom baru `denda_status`: `tidak ada|belum bayar|lunas`. Transaksi -> `selesai`.
- `PATCH /pengembalian/{id}/lunas` (admin): denda dibayar tunai -> buat pembayaran `denda`, metode `tunai`, `berhasil`, lalu `denda_status = lunas`. Route `terima` dihapus.
- Admin `konfirmasi` (menunggu -> aktif) membuat pembayaran sewa `tunai` `berhasil` jika belum ada pembayaran sewa berhasil.
- Pendapatan (dashboard & laporan) = jumlah pembayaran `berhasil` (sewa + denda) per `tanggal_bayar`. Denda belum bayar tidak dihitung.
- Semua aksi yang mengubah lebih dari satu tabel dibungkus `DB::transaction`. Booking memakai `lockForUpdate()` pada baris kendaraan sebelum cek bentrok.
- Soft delete `kendaraan` dan `pelanggan`. Relasi `Transaksi::kendaraan()`/`pelanggan()` memakai `withTrashed()`. Hapus kendaraan/pelanggan yang punya transaksi `menunggu|aktif` -> 422.
- Notifikasi: `Notification::keAdmin(type, title, message, data)` dan `Notification::keUser(userId, ...)`. `type` memakai nilai yang sudah dikenal frontend: `transaction`, `payment`, `system`, `user`.
  - Ke admin: booking baru, bukti bayar baru (sewa/denda), booking dibatalkan pelanggan.
  - Ke pelanggan (jika punya `user_id`): pembayaran diverifikasi/ditolak, booking dibatalkan admin/otomatis, pengembalian tercatat dengan denda.
- Auto-expire: command `transaksi:expire` di `routes/console.php`, dijadwalkan `hourly()`.
- Endpoint publik: `GET /katalog`, `GET /katalog/{kendaraan}`, `GET /settings/public`.
- Users: migration tambah `telepon`, `foto_profil` (dengan guard `hasColumn`, karena `updateProfile` sudah memakainya tetapi migration-nya belum ada).

### Kontrak API (dipakai frontend & test)
Publik:
- `POST /auth/register` `{name,email,telepon,password,password_confirmation}` -> 201 `{token,user}` (user memuat `pelanggan`)
- `POST /auth/login` `{email,password}` -> `{token,user}` (user memuat `role`, dan `pelanggan` jika pelanggan)
- `GET /katalog?search=&tipe=&tanggal_mulai=&tanggal_akhir=&page=` -> paginasi 12, kendaraan non-`maintenance`; jika dua tanggal diisi, kendaraan yang bentrok disaring.
- `GET /katalog/{id}` -> `{...kendaraan, jadwal:[{tanggal_mulai,tanggal_akhir}]}` (transaksi `menunggu|aktif`, `tanggal_akhir >= hari ini`)
- `GET /settings/public` -> `{data:{app_name, app_description, rekening_info}}`

Semua role (auth): `/auth/logout`, `/auth/me`, `/auth/profile`, `/auth/password`, `/notifications*` (tetap seperti sekarang).

Pelanggan (`role:pelanggan`, prefix `/akun`):
- `GET /akun/transaksi?page=` -> paginasi 10 milik sendiri, with `kendaraan`, `pembayaran`, `semuaPembayaran`, `pengembalian`
- `POST /akun/transaksi` `{kendaraan_id,tanggal_mulai,tanggal_akhir}` -> 201
- `GET /akun/transaksi/{id}` -> milik sendiri, selain itu 403
- `PATCH /akun/transaksi/{id}/batal` -> hanya `menunggu` tanpa pembayaran sewa `berhasil`
- `POST /akun/transaksi/{id}/pembayaran` multipart `{jenis: sewa|denda, metode, bukti (image jpg/png <=5MB, wajib)}` -> 201. `sewa` hanya jika transaksi `menunggu`; `denda` hanya jika `pengembalian.denda_status = belum bayar`.
- `GET /akun/profil` -> pelanggan milik user
- `POST /akun/profil` multipart `{nama, telepon, alamat?, foto_ktp?}` -> pelanggan (juga sinkron `users.name`)

Admin (`role:admin`): semua route lama + perubahan:
- `PATCH /transaksi/{id}` hanya terima `{status:"dibatalkan"}` dari `menunggu`
- `PATCH /transaksi/{id}/selesai` `{tanggal_kembali?, kondisi_kendaraan?}` -> memanggil `Pengembalian::catat` (default tanggal hari ini)
- `POST /pengembalian` `{transaksi_id,tanggal_kembali,kondisi_kendaraan?}` -> memanggil `Pengembalian::catat`
- `PATCH /pengembalian/{id}` `{kondisi_kendaraan}` saja
- `PATCH /pengembalian/{id}/lunas`
- `PATCH /pembayaran/{id}` `{status:"ditolak", catatan?}`
- `GET /laporan/pendapatan?tahun=2026` -> `{tahun, data:[{bulan:1..12, sewa, denda, total, jumlah_transaksi}], total}` (`jumlah_transaksi` = transaksi dibuat di bulan itu yang tidak `dibatalkan`)
- `GET /laporan/pendapatan/export?tahun=2026` -> CSV (`text/csv`, header `Bulan,Pendapatan Sewa,Pendapatan Denda,Total,Jumlah Transaksi`, nama bulan Indonesia, baris TOTAL)

Format error: 422 `{"message": "...", "errors"?: {...}}` (default Laravel), 403 `{"message":"Akses ditolak."}`.

### Frontend
- Router:
  - `PublicLayout`: `/` KatalogPage, `/kendaraan/:id` KendaraanDetailPage, `/akun/booking` BookingSayaPage, `/akun/profil` ProfilPelangganPage (dua terakhir `meta: {requiresAuth:true, role:'pelanggan'}`).
  - `/login`, `/register` (`meta.guest`).
  - `/admin` (`AdminLayout`, `meta:{requiresAuth:true, role:'admin'}`) children: `dashboard`, `kendaraan`, `pelanggan`, `transaksi`, `pembayaran`, `pengembalian`, `laporan`, `pengaturan`. `/admin` redirect ke `/admin/dashboard`.
  - Guard: belum login -> `/login?redirect=<fullPath>`; role tidak cocok atau guest sudah login -> beranda role (`admin` -> `/admin/dashboard`, `pelanggan` -> `/akun/booking`).
- Auth store: tambah `register()`, `isAdmin`, `homePath` (computed beranda role).
- Login: setelah sukses ke `route.query.redirect` atau `auth.homePath`. Tambah link ke `/register`.
- Semua link/`router.push` admin lama (`/dashboard`, `/kendaraan`, ...) diganti prefix `/admin`.
- Halaman baru file: `src/layouts/PublicLayout.vue`, `src/pages/RegisterPage.vue`, `src/pages/public/KatalogPage.vue`, `src/pages/public/KendaraanDetailPage.vue`, `src/pages/akun/BookingSayaPage.vue`, `src/pages/akun/ProfilPelangganPage.vue`, `src/pages/LaporanPage.vue`.
- Kalender: komponen sendiri `src/components/ui/KalenderKetersediaan.vue` (grid bulan, tombol bulan sebelumnya/berikutnya, hari `d` ditandai terisi jika `mulai <= d < akhir`). Tanpa library baru.
- Laporan: tabel 12 bulan + baris total, bar chart sederhana dari div Tailwind, tombol Export CSV (axios `responseType:'blob'`) dan Cetak (`window.print()` + class `print:hidden` untuk sidebar/topnav/tombol).
- Tanpa dependency npm/composer baru.

## Larangan
- Jangan menambah dependency (composer/npm).
- Jangan mengubah kontrak API di atas. Jika butuh field tambahan, lapor ke leader.
- Jangan mengubah logika inti di `Transaksi`, `Pembayaran`, `Pengembalian`, `EnsureRole`, `AuthController`, `AkunController` (milik leader).
- Jangan menghapus komentar yang sudah ada.
- Jangan mengubah migration lama. Perubahan skema hanya lewat migration baru.
- Jangan menyimpan token selain di `localStorage` key `token` (pola yang ada).
- Jangan menulis ulang halaman admin; ubah hanya bagian yang disebut di tugas.

## Tugas
- [ ] T1 [leader] Migration baru, model (soft delete, relasi, helper), middleware `EnsureRole` + alias di `bootstrap/app.php`, `routes/api.php` (struktur role), AuthController (register pelanggan, login/me load pelanggan), seeder (akun demo `pelanggan@siwaken.com` / `password` ditautkan ke Ahmad Santoso, setting `rekening_info`). Selesai: `php artisan migrate:fresh --seed` sukses.
- [ ] T2 [leader] Logika inti: booking (`Transaksi::buat`), cek bentrok, transisi status, pembayaran (`Pembayaran::ajukan`, verifikasi, tolak), `Pengembalian::catat` + `lunas`, `AkunController`, `KatalogController`, settings publik, dashboard pendapatan, command `transaksi:expire`, notifikasi. Selesai: endpoint sesuai kontrak.
- [ ] T3 [executor] `backend/app/Http/Controllers/Api/LaporanController.php` (`pendapatan`, `export`) sesuai kontrak; route sudah disiapkan leader di `routes/api.php`. Selesai: test T5 bagian laporan lulus.
- [ ] T4 [executor] Frontend sesuai bagian "Frontend" + perubahan halaman admin:
  - `TransaksiPage.vue` `tandaiKembali`: buka modal (input `tanggal_kembali` default hari ini, `kondisi_kendaraan` textarea) -> `PATCH /transaksi/{id}/selesai`. Tampilkan `error.response.data.message` di toast saat gagal (berlaku juga untuk `konfirmasiTransaksi`, `tolakTransaksi`).
  - `PengembalianPage.vue`: hapus aksi `terima`/`selesaikan`; tampilkan badge `denda_status`; tombol "Lunasi Denda (Tunai)" untuk `denda_status === 'belum bayar'` -> `PATCH /pengembalian/{id}/lunas`. Filter status: `tepat waktu`, `terlambat`.
  - `PembayaranPage.vue`: badge `jenis` (Sewa/Denda); `tolak` meminta alasan (modal input) -> `PATCH /pembayaran/{id}` `{status:'ditolak', catatan}`; toast memakai pesan error server.
  - `AppSidebar.vue`: prefix `/admin`, tambah menu Laporan (icon `bar_chart`), Pengaturan -> `/admin/pengaturan`.
  - `DashboardPage.vue` dan halaman lain: semua link admin diberi prefix `/admin`.
  - `PengaturanPage.vue`: tambah field `rekening_info` (textarea) di form pengaturan aplikasi yang sudah ada.
  - BookingSayaPage: daftar transaksi milik sendiri (kartu: foto, nama kendaraan, tanggal, total, badge status, status pembayaran terakhir + `catatan` jika ditolak, info denda). Aksi: "Upload Bukti Bayar" (jika `menunggu` dan pembayaran sewa terakhir null/`ditolak`), "Batalkan" (jika `menunggu` dan pembayaran sewa terakhir bukan `berhasil`/`menunggu verifikasi`), "Bayar Denda" (jika `pengembalian.denda_status === 'belum bayar'` dan tidak ada pembayaran denda `menunggu verifikasi`). Modal upload: pilih metode (`Transfer Bank`, `QRIS`, `E-Wallet`), file bukti, tampilkan `rekening_info` dari `/settings/public`. Info: booking tanpa bukti bayar dibatalkan otomatis 24 jam.
  - KendaraanDetailPage: detail + KalenderKetersediaan + form booking (tanggal mulai >= hari ini, akhir > mulai, estimasi total = harga x max(1, hari)). Belum login -> `/login?redirect=/kendaraan/:id`. Login sebagai admin -> tombol disembunyikan. Sukses -> toast + ke `/akun/booking`.
  - KatalogPage: filter search, tipe, tanggal mulai/akhir; grid kartu responsif; paginasi.
  - PublicLayout: header (logo + "SewaKen", Katalog; jika pelanggan: Booking Saya, Profil, `NotificationDropdown`, Keluar; jika admin: link Dashboard Admin; jika tamu: Masuk, Daftar), `ToastContainer`, footer sederhana. Responsif mobile.
  - ProfilPelangganPage: form nama, telepon, alamat, foto KTP (preview `foto_ktp_url`) -> `POST /akun/profil`; form ganti password -> `PATCH /auth/password`.
  - Selesai: `npm run build` tanpa error; tidak ada path admin lama tersisa (`grep -rn "'/dashboard'\|\"/kendaraan\"\|to=\"/pelanggan\"" src` kosong kecuali redirect).
- [ ] T5 [executor] Feature test PHPUnit di `backend/tests/Feature/` (RefreshDatabase, sqlite memory dari `phpunit.xml`; data dibuat langsung dengan `Model::create`, `Storage::fake('public')` + `UploadedFile::fake()->image()`):
  - `AuthRoleTest`: register -> role `pelanggan` + baris pelanggan tertaut; register menautkan pelanggan walk-in email sama; pelanggan ke `GET /api/kendaraan` -> 403; admin ke `/api/akun/transaksi` -> 403; login salah 6x -> 429 pada percobaan ke-6.
  - `BookingTest`: booking sukses 201, total = harga x hari, kendaraan tetap `tersedia`; bentrok -> 422; back-to-back (mulai = akhir lama) -> 201; kendaraan `maintenance` -> 422; tanggal lampau -> 422; batal oleh pelanggan -> `dibatalkan`; pelanggan lihat transaksi orang lain -> 403; katalog menyaring kendaraan bentrok saat tanggal diisi; `katalog/{id}` berisi `jadwal`.
  - `PembayaranTest`: upload bukti -> `jumlah` = `total` walau request kirim `jumlah` lain; upload kedua saat pending -> 422; verifikasi -> transaksi `aktif`, kendaraan `disewa`, ada notifikasi ke user pelanggan; verifikasi ulang -> 422; tolak dengan catatan -> pelanggan bisa upload lagi; admin `konfirmasi` membuat pembayaran tunai berhasil.
  - `PengembalianTest`: tepat waktu -> denda 0, `denda_status` `tidak ada`, transaksi `selesai`, kendaraan `tersedia`; telat 2 hari -> denda = 2 x harga, `terlambat`, `belum bayar`; pengembalian untuk transaksi `menunggu` -> 422; `lunas` -> pembayaran denda berhasil + `denda_status` `lunas`; pelanggan upload bukti denda -> jumlah = denda.
  - `ExpireTest`: transaksi `menunggu` > 24 jam tanpa pembayaran -> `dibatalkan`; yang punya pembayaran `menunggu verifikasi` -> tetap; yang < 24 jam -> tetap.
  - `LaporanTest`: data 12 bulan, denda belum bayar tidak dihitung, export CSV status 200 + header `text/csv`.
  - `SoftDeleteTest`: hapus kendaraan dengan transaksi aktif -> 422; tanpa transaksi aktif -> soft delete, riwayat transaksi tetap memuat kendaraan.
  - Hapus `tests/Feature/ExampleTest.php` jika gagal karena route `/` (biarkan jika lulus).
  - Selesai: `php artisan test` semua lulus.
- [ ] T6 [leader] Review semua diff, jalankan test + build, update README (setup, akun demo, alur, scheduler `php artisan schedule:work`).

## Log revisi
- T1/T2: kode leader ditulis 2026-11-07. Belum diverifikasi karena Bash diblokir hook context-mode (Node v24.14.0 < 24.15.0). Centang setelah `migrate:fresh --seed` dan test lulus.
- T3 putaran 1: cast `(float)` -> `(int)` (rupiah bigInteger), diperbaiki leader langsung.
- Catatan kontrak: key JSON relasi `semuaPembayaran` = `semua_pembayaran`.
- T5 putaran 1: 32 test ditulis, belum dijalankan (Bash diblokir). Leader mengubah `buatAdmin()` jadi `firstOrCreate` agar aman dipanggil dua kali.
- T4 putaran 1: kode selesai, build belum bisa dijalankan (Bash diblokir). Leader memperbaiki "hari ini" dari `toISOString()` (UTC) ke tanggal lokal di TransaksiPage, KatalogPage, KendaraanDetailPage; backend `config/app.php` timezone -> `Asia/Jakarta` (via `APP_TIMEZONE`).
