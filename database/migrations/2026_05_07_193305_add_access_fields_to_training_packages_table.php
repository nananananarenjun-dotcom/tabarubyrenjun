<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('training_packages', 'delivery_mode')) {
                $table->string('delivery_mode')->default('offline');
            }

            if (!Schema::hasColumn('training_packages', 'group_link')) {
                $table->text('group_link')->nullable();
            }

            if (!Schema::hasColumn('training_packages', 'meeting_link')) {
                $table->text('meeting_link')->nullable();
            }

            if (!Schema::hasColumn('training_packages', 'material_link')) {
                $table->text('material_link')->nullable();
            }

            if (!Schema::hasColumn('training_packages', 'after_acceptance_note')) {
                $table->text('after_acceptance_note')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_packages', function (Blueprint $table) {
            if (Schema::hasColumn('training_packages', 'delivery_mode')) {
                $table->dropColumn('delivery_mode');
            }

            if (Schema::hasColumn('training_packages', 'group_link')) {
                $table->dropColumn('group_link');
            }

            if (Schema::hasColumn('training_packages', 'meeting_link')) {
                $table->dropColumn('meeting_link');
            }

            if (Schema::hasColumn('training_packages', 'material_link')) {
                $table->dropColumn('material_link');
            }

            if (Schema::hasColumn('training_packages', 'after_acceptance_note')) {
                $table->dropColumn('after_acceptance_note');
            }
        });
    }
};