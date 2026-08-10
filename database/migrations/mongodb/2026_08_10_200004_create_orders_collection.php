<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * MongoDB: collection `orders` (koneksi mongodb).
 *
 * unique index pada order_code — ini pengganti constraint UNIQUE MySQL dan
 * menjadi dasar idempotensi checkout saat DB::transaction tidak tersedia
 * (Atlas M0 tidak mendukung multi-document transaction).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mongodb')->create('orders', function (Blueprint $table) {
            $table->unique('order_code');
            $table->index('user_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('orders');
    }
};
