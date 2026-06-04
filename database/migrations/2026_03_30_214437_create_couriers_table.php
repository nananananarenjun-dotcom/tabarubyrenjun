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
    Schema::create('couriers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Contoh: "JNE Reguler"
        $table->decimal('cost', 12, 2); // Harga ongkir: 15000
        $table->boolean('is_active')->default(true); // Biar admin bisa nonaktifkan kurir
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
