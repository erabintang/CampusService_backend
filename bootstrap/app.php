<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Di belakang proxy (Railway/nginx), Laravel tidak tahu request aslinya
        // HTTPS. Trust proxies: skema/protocol asli diambil dari header
        // X-Forwarded-* sehingga asset()/url()/redirect() menghasilkan https://
        // (mencegah mixed-content yang memblokir CSS/JS di produksi).
        // Hanya FOR/PROTO/PORT: host tidak di-trust (APP_URL sudah mematok host),
        // menghindari host-header spoofing.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Header keamanan dasar di semua respons (clickjacking, MIME sniffing, dsb.).
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        // User yang sudah login dan membuka /login atau /register diarahkan ke beranda.
        \Illuminate\Auth\Middleware\RedirectIfAuthenticated::redirectUsing(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Endpoint /api/* selalu merespons JSON (termasuk error validasi),
        // tanpa bergantung pada header Accept dari klien.
        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
