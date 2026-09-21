<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->text('admin_feedback')->nullable()->after('parent_feedback');
            $table->enum('returned_to_role', ['teen', 'parent', 'both'])->nullable()->after('admin_feedback');
            $table->foreignId('returned_by_staff_id')->nullable()->after('reviewed_by_parent_id')->constrained('users')->nullOnDelete();
            $table->dateTime('returned_at')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropForeign(['returned_by_staff_id']);
            $table->dropColumn([
                'admin_feedback',
                'returned_to_role',
                'returned_by_staff_id',
                'returned_at',
            ]);
        });
    }
};
