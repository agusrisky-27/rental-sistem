# Penjelasan Proyek Sistem Rental Kendaraan (SiwaKen)

Proyek ini adalah sebuah Sistem Informasi Manajemen Rental Kendaraan berbasis web (Admin Dashboard) yang dirancang untuk memudahkan pengelola/admin dalam mengelola data kendaraan, pelanggan, transaksi sewa, pembayaran, dan pengembalian. Sistem ini dibangun dengan arsitektur **Client-Server (Decoupled)** di mana frontend dan backend terpisah secara utuh namun saling berkomunikasi.

## 1. Frontend (Antarmuka Pengguna)
Bagian Frontend bertanggung jawab atas tampilan dan interaksi dengan pengguna (admin).
- **Teknologi Utama**: Vue.js 3 (Composition API), Vite 5, Tailwind CSS 3.
- **State Management**: Pinia (digunakan untuk mengelola state global seperti status autentikasi/login di `auth.js`).
- **Routing**: Vue Router 4 (untuk navigasi antar halaman tanpa *reload* / membangun *Single Page Application*).
- **HTTP Client**: Axios (digunakan untuk melakukan *request* API ke server backend).
- **Peran**: Menyediakan antarmuka visual (Dashboard, Tabel Data, Form Input) yang responsif dan menarik. Ketika admin melakukan aksi (misal menekan tombol Simpan), frontend akan mengumpulkan data dari *form* dan mengirimkannya melalui *HTTP Request* ke backend.

## 2. Backend (Server dan Database)
Bagian Backend merupakan otak, pemroses logika, dan penyedia data dari sistem ini.
- **Teknologi Utama**: Laravel 11, PHP 8.3, MySQL.
- **Autentikasi**: Laravel Sanctum (untuk mengamankan API menggunakan sistem *Token*).
- **Arsitektur**: RESTful API. Backend pada sistem ini hanya merespons dan menerima data dalam bentuk JSON, bukan merender HTML/View secara langsung.
- **Peran**: Menangani logika bisnis, mengelola keamanan (Autentikasi & Otorisasi), berinteraksi dengan database (menggunakan Eloquent ORM untuk proses CRUD), serta memvalidasi data yang masuk. Terdapat beberapa entitas utama di database: `Kendaraan`, `Pelanggan`, `Transaksi`, `Pembayaran`, dan `Pengembalian`.

## 3. Hubungan Frontend dan Backend (Integrasi)
Frontend dan Backend dihubungkan melalui **REST API**. Keduanya berjalan di server, *service*, atau *port* yang berbeda (misalnya saat *development*, Frontend di port `3000`, Backend di port `8000`).
- **Protokol**: Berkomunikasi via HTTP/HTTPS.
- **Format Data**: JSON (JavaScript Object Notation).
- **Keamanan**: Saat login berhasil, backend memberikan **Token** (melalui Laravel Sanctum). Frontend akan menyimpan token ini (di `localStorage` pada browser) dan selalu menyisipkannya pada *HTTP Header* (`Authorization: Bearer <token>`) setiap kali melakukan *request* (mengambil, menambah, mengubah, atau menghapus data) ke backend. Tanpa token yang valid, backend akan menolak *request* tersebut.

## 4. Bagaimana Sistem Berfungsi (Contoh: Halaman Login)
1. **Input Data**: Admin memasukkan *Email* dan *Password* di antarmuka halaman login frontend (Vue).
2. **Pengiriman Data (Request)**: Vue, melalui store Pinia (`auth.js`), menggunakan Axios untuk mengirim HTTP `POST` *request* ke endpoint backend (`/api/auth/login`) beserta *payload* email dan password.
3. **Pemrosesan (Backend)**: Controller Laravel menerima request tersebut, memvalidasi input, lalu mencocokkan kredensial (email dan password) dengan data *users* di database.
4. **Respon (Response)**:
   - *Berhasil*: Backend membuat token (Sanctum) dan mengirim balasan (response) status `200 OK` beserta token dan detail data *user* dalam bentuk JSON.
   - *Gagal*: Backend mengirim status `401 Unauthorized` atau error validasi.
5. **Reaksi Frontend**:
   - Jika berhasil: Pinia (`auth.js`) menyimpan token dan data user ke `localStorage`, mengubah *state* `isLoggedIn` menjadi *true*, lalu Vue Router mengarahkan (*redirect*) admin ke halaman Dashboard utama.
   - Jika gagal: Frontend menangkap pesan error dan menampilkannya sebagai *toast notification* atau teks peringatan ke pengguna.

## 5. Detail Fitur & Halaman (Pages)
Sistem ini memiliki beberapa halaman utama yang diakses oleh admin melalui menu navigasi samping (Sidebar). Berikut adalah penjelasan detail fungsi masing-masing halaman dan bagaimana mereka saling terhubung:

### A. Halaman Dashboard (`/dashboard`)
- **Fungsi**: Sebagai pusat kendali utama (Home) yang memberikan ringkasan informasi secara cepat (*Quick Glance*).
- **Detail**: Menampilkan metrik penting seperti total kendaraan, total pelanggan, jumlah transaksi aktif, dan total pendapatan. Biasanya juga menampilkan tabel ringkasan transaksi terbaru yang belum selesai.
- **Interaksi Backend**: Memanggil endpoint `/api/dashboard/stats` yang mana Laravel akan melakukan *query aggregasi* (COUNT, SUM) pada tabel kendaraan, pelanggan, dan transaksi untuk dikirimkan ke frontend.

### B. Halaman Kendaraan (`/kendaraan`)
- **Fungsi**: Mengelola *master data* aset kendaraan (Mobil, Motor, dll) yang disewakan.
- **Detail**: Menampilkan daftar (tabel) seluruh kendaraan beserta statusnya (Tersedia, Disewa, Diperbaiki). Admin dapat **Menambah** (Create), **Melihat Detail** (Read), **Mengubah** (Update), dan **Menghapus** (Delete) data. Tersedia juga fitur pencarian (berdasarkan plat nomor/nama) dan filter.
- **Hubungan**: Saat membuat "Transaksi" baru, sistem hanya akan memuat data kendaraan yang berstatus "Tersedia" dari tabel ini.

### C. Halaman Pelanggan (`/pelanggan`)
- **Fungsi**: Mengelola *master data* profil (*Customer*) yang menyewa.
- **Detail**: Menampilkan data pelanggan (Nama, NIK, No. HP, Alamat) dan Level Keanggotaan (Basic, Silver, Gold). Memiliki fitur CRUD penuh.
- **Hubungan**: Data pelanggan wajib ada sebelum admin bisa membuat "Transaksi". Nama pelanggan akan dipilih melalui *dropdown* saat transaksi dibuat.

### D. Halaman Transaksi (`/transaksi`)
- **Fungsi**: Mencatat "Perjanjian Sewa" utama antara Pelanggan dan sistem (Kendaraan).
- **Detail**: Ini adalah modul inti. Di halaman ini admin menentukan siapa menyewa apa, dari tanggal berapa hingga tanggal berapa, dan total harganya. Terdapat indikator "Stepper Status" (misal: Menunggu Pembayaran -> Disewa -> Selesai). Admin bisa melakukan aksi *Konfirmasi* transaksi atau menandainya sebagai *Selesai*.
- **Interaksi Backend**: Saat transaksi dibuat, backend akan otomatis mengunci kendaraan dengan mengubah status Kendaraan yang bersangkutan dari "Tersedia" menjadi "Disewa" agar tidak bisa disewa orang lain di waktu yang sama.

### E. Halaman Pembayaran (`/pembayaran`)
- **Fungsi**: Mengelola pencatatan arus kas/uang masuk dari suatu Transaksi.
- **Detail**: Dikhususkan untuk pencatatan keuangan. Menampilkan daftar tagihan. Saat pelanggan membayar (via transfer/tunai), admin masuk ke halaman ini, mencatat jumlah uang yang diterima, dan menekan tombol **Verifikasi Pembayaran**. Terdapat modal detail yang merinci komponen tagihan transaksi.
- **Interaksi Backend**: Memanggil endpoint `/api/pembayaran/{id}/verifikasi`. Setelah pembayaran diverifikasi (lunas/DP), ini adalah syarat bagi transaksi untuk dapat diproses lebih lanjut (kendaraan diserahkan ke pelanggan).

### F. Halaman Pengembalian (`/pengembalian`)
- **Fungsi**: Mencatat penyelesaian sewa (pengembalian fisik kendaraan).
- **Detail**: Saat kendaraan dikembalikan, admin memilih Transaksi yang bersangkutan lalu menekan **Terima Kendaraan**. Di form ini, admin mengisi: Tanggal aktual kembali (apakah telat dari perjanjian awal?) dan Catatan kondisi (apakah ada lecet/baret?). 
- **Otomatisasi**: Sistem akan otomatis membandingkan tanggal rencana kembali dan tanggal aktual. Jika terlambat, sistem **otomatis menghitung denda** berdasarkan tarif harian.
- **Interaksi Backend**: Aksi penerimaan ini memicu backend untuk: (1) Menyimpan data pengembalian, (2) Mengubah status Transaksi secara otomatis menjadi "Selesai", dan (3) Mengembalikan status Kendaraan menjadi "Tersedia" lagi.

## 6. Alur Penggunaan Sistem (User Flow)
Berikut adalah *skenario* normal operasional bisnis dalam aplikasi ini secara kronologis:
1. **Autentikasi**: Admin melakukan login ke sistem.
2. **Master Data**: Admin memastikan data Kendaraan dan Pelanggan sudah terdaftar di sistem.
3. **Proses Transaksi (Sewa)**: Pelanggan menyewa kendaraan -> Admin membuat **Transaksi** -> Status kendaraan otomatis menjadi "Disewa".
4. **Proses Pembayaran**: Pelanggan membayar tagihan -> Admin memproses verifikasi di menu **Pembayaran**.
5. **Pengembalian Kendaraan**: Waktu sewa habis -> Pelanggan mengembalikan kendaraan -> Admin mencatatnya di menu **Pengembalian** -> Denda dihitung (jika telat) -> Transaksi Selesai -> Kendaraan kembali "Tersedia".

## 7. Manfaat Sistem
- **Digitalisasi & Efisiensi**: Menghilangkan pencatatan manual di buku/kertas; data terpusat, mudah dicari, dan tidak mudah hilang atau rusak.
- **Akurasi Data Tinggi**: Perhitungan durasi sewa, total harga, dan akumulasi denda dilakukan oleh sistem sehingga sangat meminimalisir kesalahan manusia (*human error*).
- **Pemantauan Cepat**: Admin dapat melihat rangkuman operasional (misal: jumlah pendapatan, berapa kendaraan yang sedang disewa) secara *real-time* di halaman Dashboard.

## 8. Apa yang Bisa Dipelajari dari Proyek Ini
- **Konsep Arsitektur Terpisah (Decoupled)**: Memahami secara nyata bagaimana memisahkan logika antarmuka (Frontend) dan logika server (Backend) sepenuhnya.
- **RESTful API Development**: Belajar bagaimana merancang endpoint (*Routes*), *Controllers*, *Resources*, dan *Models* di Laravel yang sesuai standar.
- **Autentikasi Stateless berbasis Token**: Memahami cara kerja Laravel Sanctum, penyimpanan token di sisi klien, keamanannya, dan cara menyisipkan token pada *interceptor* di *library* HTTP.
- **Modern Frontend Development**: Menguasai penggunaan ekosistem Vue 3 terbaru (*Composition API* & `<script setup>`), Pinia untuk manajemen *state* global, *Routing*, serta desain responsif menggunakan utilitas CSS Tailwind.

## 9. Perkembangan yang Bisa Dilakukan ke Depan
- **Aplikasi Khusus Pelanggan (Customer Facing App)**: Membuat *frontend* atau aplikasi *mobile* tambahan khusus untuk pelanggan agar mereka bisa melihat katalog kendaraan, ketersediaan secara langsung, dan melakukan *booking* (pemesanan) secara mandiri dari rumah.
- **Integrasi Payment Gateway**: Menghubungkan sistem dengan pihak ketiga (seperti Midtrans, Xendit, Stripe) agar pembayaran dari pelanggan (seperti Virtual Account, E-Wallet) bisa terdeteksi dan dikonfirmasi secara otomatis, tanpa campur tangan konfirmasi manual dari admin.
- **Sistem Notifikasi dan Reminder**: Mengimplementasikan bot pengiriman Email, SMS, atau WhatsApp secara otomatis untuk mengingatkan pelanggan (misalnya H-1) sebelum masa sewa mereka habis.
- **Laporan dan Analitik Mendalam (Reporting)**: Fitur ekspor riwayat transaksi ke format PDF/Excel, serta visualisasi grafik analitik canggih (contoh: pendapatan per bulan, kendaraan terlaris, hari paling sibuk).
- **Manajemen Akses Berlapis (RBAC)**: Membedakan hak akses di dalam sistem (misal role *Super Admin*, *Kasir*, dan *Mekanik/Supir*) dengan izin fitur yang berbeda-beda.

## 10. Hambatan dan Kekurangannya
- **Kompleksitas Deployment (Setup Awal)**: Karena arsitektur terbagi dua, setup server (proses *deployment* ke *production*) akan lebih rumit dan membutuhkan biaya lebih dibandingkan sistem Laravel monolitik (Laravel + Blade). Harus mengatur dua proses, domain/subdomain, *build* frontend, dan seringkali menemui masalah CORS (*Cross-Origin Resource Sharing*).
- **Manajemen State Klien-Server (Data Tidak Real-time)**: Ada risiko data tidak sinkron jika dua admin bekerja bersamaan. Misalnya, admin A melihat mobil X "Tersedia" di layar laptopnya, padahal di detik yang sama admin B baru saja menyewakan mobil X tersebut. Tanpa me-*refresh* halaman, admin A mungkin mendapat error saat mencoba menyewa mobil yang sama. Hal ini membutuhkan teknologi *WebSocket* tambahan (seperti Laravel Reverb atau Pusher) agar data di layar *frontend* *update* secara *real-time*.
- **Belum Ada Validasi Keamanan Penyewa**: Sistem saat ini baru menangani pencatatan nama dan level pelanggan, namun belum memiliki fitur unggah dan verifikasi identitas mutakhir (misalnya *scan* OCR KTP atau SIM), sehingga validasi kelayakan penyewa kendaraan masih bergantung pada kewaspadaan manual admin.
