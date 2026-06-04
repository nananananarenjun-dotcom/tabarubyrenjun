<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'payment_proof')) {
                    $table->string('payment_proof')->nullable();
                }

                if (! Schema::hasColumn('orders', 'snap_token')) {
                    $table->string('snap_token')->nullable();
                }
            });
        }

        if (Schema::hasTable('training_registrations')) {
            Schema::table('training_registrations', function (Blueprint $table) {
                if (! Schema::hasColumn('training_registrations', 'payment_proof')) {
                    $table->string('payment_proof')->nullable();
                }

                if (! Schema::hasColumn('training_registrations', 'snap_token')) {
                    $table->string('snap_token')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'payment_proof')) {
                    $table->dropColumn('payment_proof');
                }

                if (Schema::hasColumn('orders', 'snap_token')) {
                    $table->dropColumn('snap_token');
                }
            });
        }

        if (Schema::hasTable('training_registrations')) {
            Schema::table('training_registrations', function (Blueprint $table) {
                if (Schema::hasColumn('training_registrations', 'payment_proof')) {
                    $table->dropColumn('payment_proof');
                }

                if (Schema::hasColumn('training_registrations', 'snap_token')) {
                    $table->dropColumn('snap_token');
                }
            });
        }
    }
};