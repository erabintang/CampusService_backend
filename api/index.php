<?php

/**
 * Bootstrap Vercel (runtime vercel-php) untuk Laravel campus-service.
 *
 * Vercel memanggil file ini sebagai function entrypoint; file ini memuat
 * front controller Laravel yang sama dengan public/index.php. Semua request
 * di-route oleh vercel.json ke sini, lalu diteruskan ke Laravel.
 *
 * CATATAN (batasan platform Vercel — lihat README bagian Deployment):
 * - Vercel serverless TIDAK menyediakan filesystem persisten; chunked upload
 *   yang menyimpan chunk ke storage lokal tidak akan bertahan lintas request.
 * - Body request dan durasi function dibatasi (jauh di bawah kebutuhan
 *   upload 500 MB / 1 GB).
 * - Fitur lengkap (chunked upload besar, storage persisten) membutuhkan
 *   server PHP tradisional (VPS / Laragon di server / shared hosting).
 */

$app = require __DIR__.'/../bootstrap/app.php';

$request = Illuminate\Http\Request::capture();

$response = $app->handleRequest($request);

$response->send();

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}
