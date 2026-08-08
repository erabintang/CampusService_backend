<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data awal aplikasi: user, kategori, dan layanan.
     *
     * Pesanan (orders) tidak di-seed karena berasal dari aktivitas user
     * nyata melalui alur checkout.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
