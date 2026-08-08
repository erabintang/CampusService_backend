# Run doc — CampusService (Laravel 12)

Server utama: **FrankenPHP** di http://127.0.0.1:8000 (Caddyfile di root project).
Fallback: `php artisan serve` (tetap berfungsi).

## Prasyarat
- MySQL XAMPP harus berjalan (port 3306). Jalankan via XAMPP Control Panel (butuh admin).
- Aset Vite sudah dibuild (`npm run build` → `public/build`).

## Reproduksi artefak (`tools/frankenphp/`)
1. Unduh binary Windows resmi: `https://github.com/php/frankenphp/releases/download/v1.12.7/frankenphp-windows-x86_64.zip`
2. Ekstrak ke `tools/frankenphp/`.
3. Buat `php.ini` di folder yang sama: `cp php.ini-development php.ini`, lalu aktifkan
   `extension_dir = "ext"` dan ekstensi yang dibutuhkan (`pdo_mysql`, `mbstring`, `openssl`,
   `curl`, `fileinfo`, `gd`, `intl`, `mysqli`, `pdo_sqlite`, `sodium`, `sqlite3`, `zip`).
   Tanpa langkah ini, pdo_mysql tidak termuat dan koneksi MySQL gagal.

## Menjalankan server (dari root project)
```bash
tools/frankenphp/frankenphp.exe run
```
Melayani `http://127.0.0.1:8000` (sama dengan `php artisan serve`, APP_URL tidak berubah).

Log preview: `.freebuff/preview-842f8ed2-87ce-4b40-80e5-86760f1f1b8f.log`

## Fallback
```bash
php artisan serve
```

## Catatan
- Mode classic (`php_server`), bukan worker mode — tidak ada dependency baru di composer.
- Tidak ada perubahan pada `.env`, migration, atau business logic aplikasi.
