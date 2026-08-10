<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * MongoDB: collection `users`.
 *
 * Migration MySQL lama (database/migrations) TIDAK dihapus dan tetap menjadi
 * referensi struktur. Migration ini khusus MongoDB Atlas dan hanya membuat
 * collection + index (MongoDB schema-flexible, kolom tidak perlu dideklarasikan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mongodb')->create('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('users');
    }
};
