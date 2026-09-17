<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mpesa_settings', function (Blueprint $table) {
            $table->id();
            $table->string('paybill_number', 50)->default('880100');
            $table->string('camp_fee_account', 100)->default('CAMP-FEES');
            $table->string('adopt_account', 100)->default('ADOPT-A-TEEN');
            $table->string('consumer_key', 255)->nullable();
            $table->string('consumer_secret', 255)->nullable();
            $table->string('passkey', 255)->nullable();
            $table->string('environment', 20)->default('sandbox'); // sandbox or live
            $table->boolean('is_mock_enabled')->default(true); // Allows simulated STK push in dev/offline
            $table->timestamps();
        });

        // Insert initial default setting record
        DB::table('mpesa_settings')->insert([
            'paybill_number' => '880100',
            'camp_fee_account' => 'CAMP-FEES',
            'adopt_account' => 'ADOPT-A-TEEN',
            'environment' => 'sandbox',
            'is_mock_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_settings');
    }
};
