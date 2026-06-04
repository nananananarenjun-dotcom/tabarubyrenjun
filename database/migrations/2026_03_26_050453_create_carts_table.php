<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            // Menyambungkan keranjang ke tabel users (pembeli)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Menyambungkan keranjang ke tabel products (barang)
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
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