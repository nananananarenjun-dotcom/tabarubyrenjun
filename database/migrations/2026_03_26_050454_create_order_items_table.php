<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // ID tabel detail biarkan angka bawaan saja

            // 1. Relasi manual ke tabel orders (karena TRX001)
            $table->string('order_id', 10);
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();

            // 2. Relasi manual ke tabel products (karena PRD001)
            $table->string('product_id', 10);
            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();

            $table->integer('quantity');
            $table->decimal('price', 12, 2); // Harga saat dibeli
            $table->string('no_resi')->nullable();
            $table->enum('status_pengiriman', ['menunggu', 'diproses', 'dikirim', 'selesai'])->default('menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
