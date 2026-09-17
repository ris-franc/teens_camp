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
            if (!Schema::hasColumn('camp_seasons', 'poster_path')) {
                $table->string('poster_path')->nullable()->after('announcement_banner');
            }
            if (!Schema::hasColumn('camp_seasons', 'core_values')) {
                $table->json('core_values')->nullable()->after('poster_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camp_seasons', function (Blueprint $table) {
            if (Schema::hasColumn('camp_seasons', 'poster_path')) {
                $table->dropColumn('poster_path');
            }
            if (Schema::hasColumn('camp_seasons', 'core_values')) {
                $table->dropColumn('core_values');
            }
        });
    }
};
