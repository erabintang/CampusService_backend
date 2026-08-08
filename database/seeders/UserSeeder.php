<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed admin dan beberapa user contoh.
     *
     * Idempotent: updateOrCreate berdasarkan email, sehingga aman dijalankan
     * berulang kali tanpa membuat duplikat.
     */
    public function run(): void
    {
        $users = [
            // Admin
            [
                'name' => 'Admin CampusService',
                'email' => 'admin@example.com',
                'phone' => '081234567000',
                'role' => 'admin',
                'status' => true,
                'password' => 'password',
            ],
            // User contoh
            [
                'name' => 'Raka Pratama',
                'email' => 'raka@example.com',
                'phone' => '081234567111',
                'role' => 'user',
                'status' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Salsa Maharani',
                'email' => 'salsa@example.com',
                'phone' => '081234567222',
                'role' => 'user',
                'status' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Dimas Saputra',
                'email' => 'dimas@example.com',
                'phone' => '081234567333',
                'role' => 'user',
                'status' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Ayu Lestari',
                'email' => 'ayu@example.com',
                'phone' => '081234567444',
                'role' => 'user',
                'status' => true,
                'password' => 'password',
            ],
        ];

        foreach ($users as $user) {
            // Model User memakai cast 'hashed' untuk password,
            // jadi string 'password' otomatis di-hash.
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}
