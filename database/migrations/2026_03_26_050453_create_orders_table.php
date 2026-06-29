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
        Schema::create('orders', function (Blueprint $table) {
            // 1. Ubah Primary Key jadi order_id bertipe String
            $table->string('order_id', 10)->primary();

            // 2. Relasi manual ke tabel users (karena USR001)
            $table->string('user_id', 10);
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();

            $table->string('invoice_number')->unique();
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->text('shipping_address');
            $table->string('courier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->string('snap_token')->nullable(); // <-- Tambahan dari kamu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
