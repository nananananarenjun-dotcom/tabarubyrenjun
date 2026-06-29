<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id(); // ID notifikasi biarkan angka bawaan saja

            // 1. Relasi manual ke tabel users (karena USR001)
            $table->string('user_id', 10);
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->cascadeOnDelete();

            // 2. Relasi manual ke tabel orders (karena TRX001) dan boleh kosong (nullable)
            $table->string('order_id', 10)->nullable();
            $table->foreign('order_id')
                  ->references('order_id')
                  ->on('orders')
                  ->nullOnDelete();

            $table->string('type')->default('order_status');
            $table->string('title');
            $table->text('message');

            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};