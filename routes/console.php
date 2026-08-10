<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Bersihkan chunk upload yang ditinggalkan / gagal / dibatalkan secara berkala.
Schedule::command('uploads:cleanup')->everySixHours();
