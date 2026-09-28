# TitikWargi

Platform warga Cianjur kota untuk melaporkan jalan rusak. Warga memotret kerusakan,
lokasinya tampil di peta publik, dan warga lain bisa ikut "mendukung" laporan.

Dibuat oleh warga bersama Velvorfa. Tidak terafiliasi dengan pemerintah atau partai mana pun.

## Teknologi

- Laravel + PHP 8.3
- MySQL 8
- Blade + Tailwind CSS (Vite)
- Leaflet + OpenStreetMap
- Login dengan Google (Laravel Socialite)

## Menjalankan secara lokal

Kebutuhan: Laragon (untuk MySQL 8), PHP 8.3+, Composer, dan Node.js LTS.
Ekstensi PHP yang harus aktif: `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `zip`.

Aplikasi dijalankan di **http://localhost:8000** dengan `php artisan serve`, bukan lewat
virtual host `.test` Laragon. Alasannya, Google hanya menerima redirect URL `http://`
untuk `localhost`.

### Pertama kali

1. Clone repo, misalnya ke `D:\laragon\www\titik-wargi`.
2. Pastikan MySQL di Laragon sudah jalan (**Start All**).
3. Buka **Terminal Laragon** di folder project, lalu jalankan:

   ```
   composer install
   copy .env.example .env
   php artisan key:generate
   npm install
   ```

4. Buat database `titik_wargi`, lewat HeidiSQL atau perintah berikut:

   ```
   mysql -u root -e "CREATE DATABASE titik_wargi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```

5. Jalankan migration beserta data contoh:

   ```
   php artisan migrate --seed
   ```

6. Isi `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` di `.env` (lihat bagian di bawah).

### Setiap kali mulai bekerja

```
composer run dev
```

Perintah ini menjalankan web server (`php artisan serve`), queue, dan Vite sekaligus.
Buka http://localhost:8000. Tekan `Ctrl+C` untuk menghentikannya.

### Login Google

1. Di [Google Cloud Console](https://console.cloud.google.com), buat OAuth Client dengan
   tipe **Web application**.
2. Isi **Authorized redirect URIs** dengan:

   ```
   http://localhost:8000/auth/google/callback
   ```

3. Salin Client ID dan Client secret ke `.env`, lalu jalankan `php artisan config:clear`.
4. Selama aplikasi berstatus *Testing*, tambahkan email Google Anda sebagai **Test user**.
5. Setelah login sekali, jadikan akun Anda admin:

   ```
   php artisan user:make-admin email-anda@gmail.com
   ```

## Menjalankan test

Test memakai database MySQL terpisah, `titik_wargi_test`, karena SQLite tidak mendukung
kolom lokasi (POINT). Test tidak pernah menyentuh database `titik_wargi`.

```
mysql -u root -e "CREATE DATABASE titik_wargi_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan test
```

## Checklist sebelum online (production)

- `APP_ENV=production` dan `APP_DEBUG=false`.
- Wajib HTTPS. HTTPS dibutuhkan untuk GPS, PWA, dan login Google. Set juga `SESSION_SECURE_COOKIE=true`.
- `APP_URL` diisi domain asli. Tambahkan `https://domain-anda/auth/google/callback` di Google
  Cloud Console, lalu publikasikan OAuth consent screen (keluar dari mode *Testing*).
- Batas upload server minimal 32 MB (3 foto × maks 10 MB): `upload_max_filesize`,
  `post_max_size` (PHP), dan `client_max_body_size` (Nginx).
- Jalankan:

  ```
  composer install --no-dev --optimize-autoloader
  npm ci && npm run build
  php artisan migrate --force
  php artisan storage:link
  php artisan optimize
  ```

- Jadikan akun Anda admin: `php artisan user:make-admin email@anda.com`.
- Lengkapi bagian bertanda `[BELUM DITENTUKAN]` di halaman Kebijakan Privasi dan Aturan Komunitas.

## Keamanan

Jangan pernah commit file `.env` atau menulis password/API key di file yang ikut di-commit.
Contoh konfigurasi hanya ada di `.env.example`.
