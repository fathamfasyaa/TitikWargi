# TitikWargi

Platform warga Cianjur kota untuk melaporkan jalan rusak. Warga memotret kerusakan,
lokasinya tampil di peta publik, dan warga lain bisa ikut "mendukung" laporan.

Dibuat oleh warga bersama Velvorfa. Tidak terafiliasi dengan pemerintah atau partai mana pun.

## Teknologi

- Laravel + PHP 8.3
- MySQL 8
- Blade + Tailwind CSS (Vite)
- Leaflet + OpenStreetMap

## Menjalankan secara lokal dengan Laragon

Kebutuhan: Laragon (Nginx, MySQL 8), PHP 8.3+, Composer, dan Node.js LTS.
Ekstensi PHP yang harus aktif: `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `zip`.

1. Clone repo ke folder `www` milik Laragon, misalnya `D:\laragon\www\titik-wargi`.
2. Buka **Terminal Laragon** di folder project, lalu jalankan:

   ```
   composer install
   copy .env.example .env
   php artisan key:generate
   npm install
   ```

3. Buat database `titik_wargi` (lewat HeidiSQL di Laragon, atau perintah berikut):

   ```
   mysql -u root -e "CREATE DATABASE titik_wargi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```

4. Jalankan migration:

   ```
   php artisan migrate
   ```

5. Di Laragon, klik **Menu > Nginx > Reload** (atau **Stop** lalu **Start All**) supaya
   virtual host `titik-wargi.test` dibuat otomatis.
6. Jalankan Vite selama development:

   ```
   npm run dev
   ```

7. Buka http://titik-wargi.test di browser.

## Keamanan

Jangan pernah commit file `.env` atau menulis password/API key di file yang ikut di-commit.
Contoh konfigurasi hanya ada di `.env.example`.
