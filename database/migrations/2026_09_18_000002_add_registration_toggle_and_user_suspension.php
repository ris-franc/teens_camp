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
        Schema::table('camp_seasons', function (Blueprint $table) {
            if (!Schema::hasColumn('camp_seasons', 'is_registration_open')) {
                $table->boolean('is_registration_open')->default(true)->after('status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_suspended')) {
                $table->boolean('is_suspended')->default(false)->after('pin_reset_required');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camp_seasons', function (Blueprint $table) {
            if (Schema::hasColumn('camp_seasons', 'is_registration_open')) {
                $table->dropColumn('is_registration_open');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_suspended')) {
                $table->dropColumn('is_suspended');
            }
        });
    }
};
