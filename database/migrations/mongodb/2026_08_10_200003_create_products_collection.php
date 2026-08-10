<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * MongoDB: collection `products` (koneksi mongodb).
 *
 * unique index pada slug (analog UNIQUE MySQL) dan index category_id
 * untuk mempercepat filter & relasi category.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mongodb')->create('products', function (Blueprint $table) {
            $table->unique('slug');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('products');
    }
};
