<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); // ID untuk tabel review biarkan angka bawaan saja

            // 1. Relasi manual ke tabel users (karena pakai format USR001)
            $table->string('user_id', 10);
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            // 2. Relasi manual ke tabel products (karena pakai format PRD001)
            $table->string('product_id', 10);
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');

            $table->integer('rating'); // Bintang 1 sampai 5
            $table->text('comment')->nullable(); // Isi ulasan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};