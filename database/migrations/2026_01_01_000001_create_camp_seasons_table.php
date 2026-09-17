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
        Schema::create('camp_seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Teen Camp 2026"
            $table->string('year', 4); // "2026"
            $table->string('theme')->nullable(); // e.g. "Unstoppable"
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('venue');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('capacity')->default(200);
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->text('description')->nullable();
            $table->text('landing_subtitle')->nullable();
            $table->text('announcement_banner')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camp_seasons');
    }
};
