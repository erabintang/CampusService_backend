<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * MongoDB: collection `uploads` (koneksi mongodb).
 *
 * Metadata upload pindah ke MongoDB; binary chunk/file final tetap di
 * filesystem (disk local -> storage/app/private/uploads), tidak pernah
 * disimpan di database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mongodb')->create('uploads', function (Blueprint $table) {
            $table->unique('uuid');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('uploads');
    }
};
