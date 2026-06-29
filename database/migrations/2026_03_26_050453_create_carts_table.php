<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id(); // ID untuk tabel carts biarkan saja auto increment bawaan

            // 1. Relasi ke tabel users
            // (Asumsi: di tabel users kamu sudah merubah ID-nya jadi string 'user_id' seperti 'USR001')
            $table->string('user_id', 10);
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();

            // 2. Relasi ke tabel products (WAJIB MANUAL KARENA PAKAI STRING 'PRD001')
            $table->string('product_id', 10);
            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();

            // Jumlah barang
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};