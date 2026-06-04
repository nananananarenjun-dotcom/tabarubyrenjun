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
        Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->morphs('payable'); // Otomatis membuat kolom payable_type (string) & payable_id (bigint)
    $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
    $table->decimal('amount', 12, 2);
    $table->string('payment_proof');
    $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
