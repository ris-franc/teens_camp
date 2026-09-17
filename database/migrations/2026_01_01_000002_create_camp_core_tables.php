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
        // 1. Parent - Teen relationship
        Schema::create('parent_teen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teen_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship')->default('Parent/Guardian');
            $table->timestamps();

            $table->unique(['parent_id', 'teen_id']);
        });

        // 2. Registrations (per season)
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->foreignId('teen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['registered', 'signed_in', 'withdrawn'])->default('registered');
            $table->boolean('phone_carried')->default(false);
            $table->text('medication_notes')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->dateTime('signed_in_at')->nullable();
            $table->foreignId('signed_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['camp_season_id', 'teen_id']);
        });

        // 3. Receipts
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->enum('type', [
                'direct_payment',
                'kitty_donation_in',
                'kitty_campaign_in',
                'kitty_adopt_out'
            ]);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        // 4. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('source', ['direct_payment', 'adopt_a_teen_transfer']);
            $table->string('reference');
            $table->string('receipt_number');
            $table->string('payment_method')->default('Direct');
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Dynamic Forms
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('target_role', ['teen', 'parent', 'both'])->default('both');
            $table->boolean('requires_parent_approval')->default(false);
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('label');
            $table->enum('field_type', ['text', 'dropdown', 'multiple_choice', 'checkbox', 'file_upload']);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('help_text')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teen_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'pending_parent_review', 'returned', 'submitted'])->default('submitted');
            $table->text('parent_feedback')->nullable();
            $table->foreignId('reviewed_by_parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('form_submission_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained('form_submissions')->cascadeOnDelete();
            $table->foreignId('form_field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->longText('value')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        // 6. Packing Lists (Release-controlled per season)
        Schema::create('packing_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->string('category');
            $table->string('item_name');
            $table->string('notes')->nullable();
            $table->boolean('is_essential')->default(true);
            $table->boolean('is_released')->default(false);
            $table->timestamps();
        });

        // 7. Adopt-a-Teen Requests
        Schema::create('adopt_a_teen_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teen_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount_requested', 10, 2);
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'approved-awaiting-funds', 'denied'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_notes')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 8. Adopt-a-Teen Kitty Ledger
        Schema::create('adopt_a_teen_kitty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->enum('type', ['donation_in', 'campaign_profit_in', 'adopt_out']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('description');
            $table->string('reference')->nullable();
            $table->foreignId('receipt_id')->nullable()->constrained('receipts')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 9. Campaign System
        Schema::create('campaign_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('profit_per_unit', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('campaign_weekly_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->string('week_label');
            $table->date('week_start');
            $table->date('week_end');
            $table->decimal('total_sales_amount', 10, 2)->default(0);
            $table->decimal('total_profit_amount', 10, 2)->default(0);
            $table->enum('status', ['pending_review', 'approved', 'returned'])->default('pending_review');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->constrained('camp_seasons')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('campaign_weekly_batches')->nullOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('campaign_products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('unit_profit', 10, 2);
            $table->decimal('total_profit', 10, 2);
            $table->dateTime('sold_at');
            $table->timestamps();
        });

        // 10. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_season_id')->nullable()->constrained('camp_seasons')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('target_role')->nullable(); // 'all', 'teen', 'parent', 'staff'
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['system', 'adopt_a_teen', 'form', 'payment'])->default('system');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('campaign_sales');
        Schema::dropIfExists('campaign_weekly_batches');
        Schema::dropIfExists('campaign_products');
        Schema::dropIfExists('adopt_a_teen_kitty');
        Schema::dropIfExists('adopt_a_teen_requests');
        Schema::dropIfExists('packing_lists');
        Schema::dropIfExists('form_submission_values');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('parent_teen');
    }
};
