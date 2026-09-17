<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('desk_form_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default');
            $table->json('fields'); // JSON array of field definitions
            $table->timestamps();
        });

        // Seed the default field configuration
        $defaultFields = [
            // Parent Section
            ['key' => 'parent_email',   'label' => 'Parent Email Address',       'type' => 'email',    'section' => 'parent', 'required' => true,  'enabled' => true,  'order' => 1,  'options' => null],
            ['key' => 'parent_name',    'label' => 'Parent Full Name',            'type' => 'text',     'section' => 'parent', 'required' => true,  'enabled' => true,  'order' => 2,  'options' => null],
            ['key' => 'parent_phone',   'label' => 'Parent Phone Number',         'type' => 'tel',      'section' => 'parent', 'required' => true,  'enabled' => true,  'order' => 3,  'options' => null],
            ['key' => 'relationship',   'label' => 'Relationship to Camper',      'type' => 'select',   'section' => 'parent', 'required' => false, 'enabled' => true,  'order' => 4,  'options' => ['Mother', 'Father', 'Guardian', 'Sponsor', 'Other']],
            // Teen Section
            ['key' => 'teen_name',      'label' => 'Teen Camper Full Name',       'type' => 'text',     'section' => 'teen',   'required' => true,  'enabled' => true,  'order' => 5,  'options' => null],
            ['key' => 'teen_email',     'label' => 'Teen Email Address',          'type' => 'email',    'section' => 'teen',   'required' => true,  'enabled' => true,  'order' => 6,  'options' => null],
            ['key' => 'teen_gender',    'label' => 'Gender',                      'type' => 'select',   'section' => 'teen',   'required' => true,  'enabled' => true,  'order' => 7,  'options' => ['male', 'female']],
            ['key' => 'teen_dob',       'label' => 'Date of Birth',               'type' => 'date',     'section' => 'teen',   'required' => false, 'enabled' => true,  'order' => 8,  'options' => null],
            ['key' => 'teen_phone',     'label' => 'Camper Phone Number',         'type' => 'tel',      'section' => 'teen',   'required' => false, 'enabled' => true,  'order' => 9,  'options' => null],
            // Declarations Section
            ['key' => 'phone_carried',  'label' => 'Mobile Phone Declaration (Teen carries phone to camp)', 'type' => 'checkbox', 'section' => 'declarations', 'required' => false, 'enabled' => true, 'order' => 10, 'options' => null],
            ['key' => 'medical_conditions', 'label' => 'Medical Conditions / Allergies', 'type' => 'text', 'section' => 'declarations', 'required' => false, 'enabled' => true, 'order' => 11, 'options' => null],
            ['key' => 'medication_notes', 'label' => 'Regular Medication & Dosage', 'type' => 'text', 'section' => 'declarations', 'required' => false, 'enabled' => true, 'order' => 12, 'options' => null],
            // Payment Section
            ['key' => 'initial_payment',  'label' => 'Initial Payment Amount (KES)', 'type' => 'number', 'section' => 'payment', 'required' => false, 'enabled' => true, 'order' => 13, 'options' => null],
            ['key' => 'payment_method',   'label' => 'Payment Method',            'type' => 'select',  'section' => 'payment', 'required' => false, 'enabled' => true, 'order' => 14, 'options' => ['M-Pesa (Till 5412345)', 'M-Pesa (Paybill 880100)', 'Cash at Desk', 'Bank Transfer / EFT']],
            ['key' => 'payment_reference','label' => 'M-Pesa Transaction Code',   'type' => 'text',    'section' => 'payment', 'required' => false, 'enabled' => true, 'order' => 15, 'options' => null],
        ];

        DB::table('desk_form_configs')->insert([
            'name' => 'default',
            'fields' => json_encode($defaultFields),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desk_form_configs');
    }
};
