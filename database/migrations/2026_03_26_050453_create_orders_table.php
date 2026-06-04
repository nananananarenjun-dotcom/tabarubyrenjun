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
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('invoice_number')->unique();
    $table->decimal('total_price', 12, 2);
    $table->enum('status', ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');
    $table->text('shipping_address');
    $table->string('courier_name')->nullable();
    $table->string('tracking_number')->nullable();
    $table->timestamp('shipped_at')->nullable();
    $table->timestamps();
    $table->string('snap_token')->nullable(); // <-- Tambahkan ini
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
