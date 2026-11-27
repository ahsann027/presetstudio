# Laravel Preset Store

Toko preset foto berbasis Laravel 12 dengan katalog publik, checkout, pembayaran Midtrans atau simulasi, unduhan bertanda tangan, email notifikasi, dan area admin.

## Persyaratan

- PHP 8.2+
- Composer
- Node.js dan npm
- SQLite untuk pengembangan sederhana, atau MySQL/PostgreSQL

## Instalasi

1. Salin environment:
   ```powershell
   Copy-Item .env.example .env
   ```
2. Instal dependencies dan buat application key:
   ```powershell
   composer install
   npm install
   php artisan key:generate
   ```
3. Isi konfigurasi database dan akun admin di `.env`.
4. Jalankan migrasi dan seeder:
   ```powershell
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   npm run build
   ```

Untuk menjalankan aplikasi saat pengembangan:

```powershell
composer run dev
```

## Konfigurasi penting

- `PAYMENT_DRIVER=simulation` untuk demo lokal atau `midtrans` untuk gateway sungguhan.
- Isi `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, dan `MIDTRANS_IS_PRODUCTION` saat menggunakan Midtrans.
- Isi `ADMIN_EMAIL` dan `ADMIN_PASSWORD` sebelum menjalankan seeder agar akun admin dibuat.
- `SHOP_DOWNLOAD_DAYS` mengatur masa berlaku unduhan.
- `SHOP_DOWNLOAD_LIMIT` mengatur jumlah unduhan per pesanan.
- Midtrans webhook harus dapat diakses publik pada `/midtrans/notification`.

## Pengujian

```powershell
php artisan test
```
