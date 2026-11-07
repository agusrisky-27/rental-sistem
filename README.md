# SewaKen — Sistem Sewa Kendaraan
Laravel 13 + Sanctum (REST API) · Vue 3 + Vite + Pinia + Tailwind CSS

Dua sisi dalam satu aplikasi:
- **Pelanggan** (`/`): katalog, cek ketersediaan, booking, upload bukti bayar, riwayat, bayar denda.
- **Admin** (`/admin`): dashboard, kendaraan, pelanggan, transaksi, pembayaran, pengembalian, laporan, pengaturan.

---

## 📁 Struktur Project

```
rental_sistem/
├── backend/     ← Laravel 13 (REST API + Sanctum)
├── frontend/    ← Vue 3 + Vite + Pinia + Tailwind CSS
└── docs/plan.md ← keputusan desain & daftar tugas
```

---

## 🚀 Setup Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Edit .env → DB_DATABASE, DB_USERNAME, DB_PASSWORD (MySQL)
php artisan migrate --seed
php artisan storage:link
php artisan serve            # http://localhost:8000
```

Booking yang belum dibayar 24 jam dibatalkan otomatis oleh scheduler. Saat development jalankan di terminal terpisah:

```bash
php artisan schedule:work
# atau sekali jalan: php artisan transaksi:expire
```

Test (sqlite in-memory, tidak menyentuh DB MySQL):

```bash
php artisan test
```

---

## 🎨 Setup Frontend

```bash
cd frontend
npm install
cp .env.example .env         # VITE_API_URL=http://localhost:8000/api
npm run dev                  # http://localhost:3000
```

---

## 🔐 Akun Demo

| Email                  | Password | Role      |
|------------------------|----------|-----------|
| admin@siwaken.com      | password | Admin     |
| pelanggan@siwaken.com  | password | Pelanggan |

Register publik (`/register`) hanya membuat akun **pelanggan**. Akun admin hanya dibuat lewat seeder.

---

## 🔄 Alur Sewa

1. Pelanggan memilih mobil & tanggal → transaksi **menunggu** (jadwal mobil terkunci).
2. Pelanggan upload bukti transfer → pembayaran **menunggu verifikasi**.
3. Admin verifikasi → transaksi **aktif**, kendaraan **disewa**.
   (Bayar tunai: admin klik *Konfirmasi* di halaman Transaksi.)
   Jika bukti ditolak, pelanggan bisa upload ulang.
4. Admin catat pengembalian → transaksi **selesai**, kendaraan **tersedia**.
   Telat → denda = harga/hari × hari telat, status denda **belum bayar**.
5. Pelanggan upload bukti bayar denda (atau admin *Lunasi Denda* tunai) → denda **lunas**.

Pendapatan dashboard & laporan hanya menghitung pembayaran **berhasil**.
Hapus kendaraan/pelanggan memakai soft delete, jadi riwayat transaksi tetap ada.

---

## 📋 Halaman

| Halaman (route)         | Fitur                                                        |
|-------------------------|--------------------------------------------------------------|
| `/`                     | Katalog + filter tipe & tanggal tersedia                     |
| `/kendaraan/:id`        | Detail, kalender ketersediaan, form booking                  |
| `/akun/booking`         | Riwayat booking, upload bukti bayar, batal, bayar denda      |
| `/akun/profil`          | Data diri, foto KTP, ganti password                          |
| `/admin/dashboard`      | Statistik, transaksi terbaru                                 |
| `/admin/transaksi`      | Konfirmasi tunai, tolak, catat pengembalian                  |
| `/admin/pembayaran`     | Verifikasi / tolak bukti (dengan alasan)                     |
| `/admin/pengembalian`   | Daftar pengembalian, status & pelunasan denda                |
| `/admin/laporan`        | Pendapatan per bulan, export CSV, cetak/PDF                  |
| `/admin/pengaturan`     | Profil, password, pengaturan aplikasi & info rekening        |

Notifikasi (ikon lonceng) dikirim ke admin saat ada booking/bukti bayar baru, dan ke pelanggan saat pembayaran diverifikasi/ditolak, booking dibatalkan, atau ada denda.

---

## 🛠 Tech Stack

**Frontend**: Vue 3 (`<script setup>`), Vite 5, Pinia, Vue Router 4, Tailwind CSS 3, Axios, Material Symbols.

**Backend**: Laravel 13, Sanctum (token), MySQL, Eloquent (soft delete), scheduler, PHPUnit.

**Keamanan**: role middleware (`role:admin` / `role:pelanggan`), throttle login/register 5×/menit, cek kepemilikan transaksi pelanggan, jumlah pembayaran dihitung server.
