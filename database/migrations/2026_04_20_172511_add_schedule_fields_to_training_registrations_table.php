<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('training_registrations')) {
            return;
        }

        Schema::table('training_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('training_registrations', 'scheduled_time')) {
                $table->time('scheduled_time')->nullable();
            }

            if (! Schema::hasColumn('training_registrations', 'location')) {
                $table->text('location')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('training_registrations')) {
            return;
        }

        Schema::table('training_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('training_registrations', 'scheduled_time')) {
                $table->dropColumn('scheduled_time');
            }

            if (Schema::hasColumn('training_registrations', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};