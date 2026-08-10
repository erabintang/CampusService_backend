<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * MongoDB: collection `categories` (koneksi mongodb).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mongodb')->create('categories', function (Blueprint $table) {
            // Tidak ada index unik — nama kategori boleh diubah; identitas
            // produk merujuk category_id, bukan nama.
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('categories');
    }
};
