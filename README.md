# SYNRCYPRO — Laravel Monitoring Dashboard

Starter project Laravel 13 yang mengubah mockup login menjadi halaman autentikasi fungsional dan dashboard operasi responsif.

## Fitur

- Halaman login bergaya control room sesuai referensi gambar.
- Login Google menggunakan Laravel Socialite.
- Tombol masuk sebagai Guest.
- Dashboard responsif untuk desktop, tablet, dan ponsel.
- Statistik operasional, grafik performa tanpa library eksternal, safety compliance, status sistem, progres proyek, dan tabel insiden.
- SQLite sebagai konfigurasi awal agar cepat dijalankan.
- Seluruh CSS dan JavaScript tersimpan lokal; tidak membutuhkan proses build frontend.

## Persyaratan

- PHP 8.3 atau lebih baru.
- Composer.
- Ekstensi PHP: `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, dan `ctype`.

## Cara menjalankan

```bash
unzip synrcypro-laravel.zip
cd synrcypro-laravel

composer install
cp .env.example .env
php artisan key:generate

# Linux/macOS
touch database/database.sqlite

# Windows PowerShell, bila file belum ada:
# New-Item database/database.sqlite -ItemType File

php artisan migrate
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Tombol **SIGN IN AS GUEST** langsung membuat akun guest sementara di database dan membuka dashboard.

## Konfigurasi Google Login

Buat OAuth 2.0 Client pada Google Cloud Console, lalu tambahkan Authorized Redirect URI:

```text
http://127.0.0.1:8000/auth/google/callback
```

Isi `.env`:

```env
GOOGLE_CLIENT_ID=client-id-anda
GOOGLE_CLIENT_SECRET=client-secret-anda
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Sesudah mengubah `.env`, jalankan:

```bash
php artisan config:clear
```

## Akun demo dari seeder

Seeder menyediakan akun berikut untuk pengembangan:

```text
Email    : admin@synrcypro.local
Password : password
```

Project ini belum menyediakan form login email/password karena desain awal hanya menampilkan Google Login dan Guest Login. Akun seeder disiapkan untuk pengembangan lanjutan.

## Menggunakan MySQL

Ubah bagian database pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=synrcypro
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan:

```bash
php artisan migrate:fresh --seed
```

## Struktur penting

```text
app/Http/Controllers/AuthController.php
app/Http/Controllers/DashboardController.php
resources/views/auth/login.blade.php
resources/views/dashboard.blade.php
public/assets/css/app.css
public/assets/js/dashboard.js
public/assets/images/control-room.jpg
routes/web.php
```

## Catatan keamanan

- Jangan memasukkan kredensial Google ke repository.
- Gunakan HTTPS dan `APP_DEBUG=false` pada production.
- Guest login pada contoh ini membuat record pengguna baru. Untuk production, tambahkan rate limiting atau ubah menjadi satu akun guest bersama.
