<?php

/**
 * Bootstrap Vercel (runtime vercel-php@0.7.4 = PHP 8.3) untuk Laravel campus-service.
 *
 * Vercel memanggil file ini sebagai function entrypoint; file ini memuat
 * front controller Laravel yang sama dengan public/index.php. Semua request
 * di-route oleh vercel.json ke sini, lalu diteruskan ke Laravel.
 *
 * CATATAN (batasan platform Vercel — lihat README bagian Deployment):
 * - Vercel serverless TIDAK menyediakan filesystem persisten; chunked upload
 *   yang menyimpan chunk ke storage lokal tidak akan bertahan lintas request,
 *   dan limit body/durasi function jauh di bawah kebutuhan upload 500 MB/1 GB.
 * - Fitur lengkap (chunked upload besar, storage persisten, session) membutuhkan
 *   server PHP tradisional (VPS / Laragon di server / shared hosting).
 */

// Pastikan direktori storage framework ada (Vercel meng-upload repo tanpa
// direktori kosong; Laravel butuh folder ini saat runtime).
$storageDirs = [
    __DIR__.'/../storage/app',
    __DIR__.'/../storage/app/private',
    __DIR__.'/../storage/framework',
    __DIR__.'/../storage/framework/cache',
    __DIR__.'/../storage/framework/cache/data',
    __DIR__.'/../storage/framework/sessions',
    __DIR__.'/../storage/framework/views',
    __DIR__.'/../storage/logs',
];

foreach ($storageDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$app = require __DIR__.'/../bootstrap/app.php';

$request = Illuminate\Http\Request::capture();

$response = $app->handleRequest($request);

$response->send();

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}
