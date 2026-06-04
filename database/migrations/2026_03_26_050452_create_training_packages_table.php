<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_packages', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2);

            $table->integer('min_participants')->default(1);
            $table->integer('max_quota_per_session');

            $table->string('type')->default('regular');
            // regular / custom

            $table->date('regular_date')->nullable();
            $table->time('regular_time')->nullable();
            $table->text('location')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_packages');
    }
};