<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // ID Custom String (PRD001)
            $table->string('product_id', 10)->primary();
            
            // Foreign Key ke tabel categories (pastikan di tabel categories ID-nya juga string ya!)
            $table->string('category_id', 10);
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('cascade');
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(1);
            
            // Ini tempat gambarnya tersimpan!
            $table->string('image')->nullable();
            
            // Status untuk menjawab revisi "Produk Belum Terealisasi"
            $table->enum('status', ['Tersedia', 'Habis', 'Arsip', 'Belum Terealisasi'])->default('Tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};