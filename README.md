# SiwaKen — Admin Dashboard
Vehicle Rental System | Laravel 11 + Vue 3 + Vite + Tailwind CSS

---

## 📁 Struktur Project

```
siwaken/
├── backend/     ← Laravel 11 (REST API + Sanctum)
└── frontend/    ← Vue 3 + Vite + Pinia + Tailwind CSS
```

---

## 🚀 Setup Backend (Laravel)

### 1. Install Laravel (jika belum ada project)
```bash
composer create-project laravel/laravel backend
cd backend
```

### 2. Copy file-file dari folder backend/ ini ke project Laravel kamu:
- `app/Http/Controllers/Api/` → semua controller
- `app/Models/` → semua model
- `routes/api.php`
- `database/migrations/` → semua migration
- `database/seeders/DatabaseSeeder.php`

### 3. Install Sanctum
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 4. Setup .env
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env` → isi DB_DATABASE, DB_USERNAME, DB_PASSWORD

### 5. Tambahkan kolom `role` ke users migration
```php
$table->string('role')->default('admin');
```

### 6. Jalankan migration & seeder
```bash
php artisan migrate --seed
php artisan storage:link
```

### 7. Jalankan server
```bash
php artisan serve
# Berjalan di http://localhost:8000
```

---

## 🎨 Setup Frontend (Vue 3)

### 1. Masuk ke folder frontend
```bash
cd frontend
```

### 2. Install dependencies
```bash
npm install
```

### 3. Setup .env
```bash
cp .env.example .env
# VITE_API_URL=http://localhost:8000/api
```

### 4. Jalankan dev server
```bash
npm run dev
# Berjalan di http://localhost:3000
```

---

## 🔐 Kredensial Default

| Email                | Password   | Role  |
|----------------------|------------|-------|
| admin@siwaken.com    | password   | Admin |

---

## 📋 Halaman & Fitur

| Halaman        | Fitur                                                   |
|----------------|---------------------------------------------------------|
| Login          | Autentikasi dengan Sanctum token                        |
| Dashboard      | Stats cards, aktivitas terbaru                          |
| Kendaraan      | CRUD + modal tambah/edit/hapus + filter + search        |
| Pelanggan      | CRUD + level keanggotaan (Gold/Silver/Basic)            |
| Transaksi      | Lihat detail, konfirmasi, tandai selesai, stepper status|
| Pembayaran     | Verifikasi manual, detail modal                         |
| Pengembalian   | Terima kendaraan, hitung denda, catatan kondisi         |

---

## 🎨 Design System

Semua warna, font, dan spacing 100% matching dengan desain Stitch:
- **Font**: Plus Jakarta Sans (headline) + Inter (body)
- **Primary Blue**: `#0058be` (secondary token)
- **Active nav**: `bg-secondary-fixed text-secondary`
- **Icons**: Material Symbols Outlined

---

## 🛠 Tech Stack

**Frontend**
- Vue 3 (Composition API + `<script setup>`)
- Vite 5
- Pinia (state management)
- Vue Router 4
- Tailwind CSS 3
- Axios

**Backend**
- Laravel 11
- Laravel Sanctum (token auth)
- MySQL
- Eloquent ORM
