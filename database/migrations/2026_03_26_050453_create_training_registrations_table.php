<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('training_package_id')
                ->constrained('training_packages')
                ->onDelete('cascade');

            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->text('location')->nullable();

            $table->boolean('is_custom_schedule')->default(false);

            $table->integer('participant_count');
            $table->decimal('total_price', 12, 2);

            $table->enum('status', [
                'pending',
                'approved_by_admin',
                'paid',
                'completed',
                'rejected',
            ])->default('pending');

            $table->text('user_notes')->nullable();

            $table->string('payment_proof')->nullable();
            $table->string('snap_token')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_registrations');
    }
};