<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeskFormConfig extends Model
{
    protected $fillable = ['name', 'fields'];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
        ];
    }

    /**
     * Core keys that cannot be disabled as they are required for account creation.
     */
    public static array $coreKeys = [
        'parent_email', 'parent_name', 'parent_phone',
        'teen_name', 'teen_email', 'teen_gender',
    ];

    /**
     * Get or create the single global config record.
     */
    public static function getCurrent(): self
    {
        $config = static::first();

        if (!$config) {
            $config = static::create([
                'name' => 'default',
                'fields' => static::defaultFields(),
            ]);
        }

        return $config;
    }

    /**
     * Get only enabled fields, sorted by order.
     */
    public function enabledFields(): array
    {
        return collect($this->fields ?? [])
            ->where('enabled', true)
            ->sortBy('order')
            ->values()
            ->toArray();
    }

    /**
     * Get enabled fields for a given section.
     */
    public function enabledFieldsForSection(string $section): array
    {
        return collect($this->enabledFields())
            ->where('section', $section)
            ->values()
            ->toArray();
    }

    /**
     * Get all fields sorted by order (for admin editor).
     */
    public function allFieldsSorted(): array
    {
        return collect($this->fields ?? [])
            ->sortBy('order')
            ->values()
            ->toArray();
    }

    /**
     * Default field definitions.
     */
    public static function defaultFields(): array
    {
        return [
            ['key' => 'parent_email',       'label' => 'Parent Email Address',       'type' => 'email',    'section' => 'parent',       'required' => true,  'enabled' => true,  'order' => 1,  'options' => null],
            ['key' => 'parent_name',         'label' => 'Parent Full Name',            'type' => 'text',     'section' => 'parent',       'required' => true,  'enabled' => true,  'order' => 2,  'options' => null],
            ['key' => 'parent_phone',        'label' => 'Parent Phone Number',         'type' => 'tel',      'section' => 'parent',       'required' => true,  'enabled' => true,  'order' => 3,  'options' => null],
            ['key' => 'relationship',        'label' => 'Relationship to Camper',      'type' => 'select',   'section' => 'parent',       'required' => false, 'enabled' => true,  'order' => 4,  'options' => ['Mother', 'Father', 'Guardian', 'Sponsor', 'Other']],
            ['key' => 'teen_name',           'label' => 'Teen Camper Full Name',       'type' => 'text',     'section' => 'teen',         'required' => true,  'enabled' => true,  'order' => 5,  'options' => null],
            ['key' => 'teen_email',          'label' => 'Teen Email Address',          'type' => 'email',    'section' => 'teen',         'required' => true,  'enabled' => true,  'order' => 6,  'options' => null],
            ['key' => 'teen_gender',         'label' => 'Gender',                      'type' => 'select',   'section' => 'teen',         'required' => true,  'enabled' => true,  'order' => 7,  'options' => ['male', 'female']],
            ['key' => 'teen_dob',            'label' => 'Date of Birth',               'type' => 'date',     'section' => 'teen',         'required' => false, 'enabled' => true,  'order' => 8,  'options' => null],
            ['key' => 'teen_phone',          'label' => 'Camper Phone Number',         'type' => 'tel',      'section' => 'teen',         'required' => false, 'enabled' => true,  'order' => 9,  'options' => null],
            ['key' => 'phone_carried',       'label' => 'Mobile Phone Declaration (Teen carries phone)', 'type' => 'checkbox', 'section' => 'declarations', 'required' => false, 'enabled' => true, 'order' => 10, 'options' => null],
            ['key' => 'medical_conditions',  'label' => 'Medical Conditions / Allergies', 'type' => 'text', 'section' => 'declarations', 'required' => false, 'enabled' => true,  'order' => 11, 'options' => null],
            ['key' => 'medication_notes',    'label' => 'Regular Medication & Dosage', 'type' => 'text',    'section' => 'declarations', 'required' => false, 'enabled' => true,  'order' => 12, 'options' => null],
            ['key' => 'initial_payment',     'label' => 'Initial Payment Amount (KES)', 'type' => 'number', 'section' => 'payment',      'required' => false, 'enabled' => true,  'order' => 13, 'options' => null],
            ['key' => 'payment_method',      'label' => 'Payment Method',              'type' => 'select',  'section' => 'payment',      'required' => false, 'enabled' => true,  'order' => 14, 'options' => ['M-Pesa (Till 5412345)', 'M-Pesa (Paybill 880100)', 'Cash at Desk', 'Bank Transfer / EFT']],
            ['key' => 'payment_reference',   'label' => 'M-Pesa Transaction Code',     'type' => 'text',    'section' => 'payment',      'required' => false, 'enabled' => true,  'order' => 15, 'options' => null],
        ];
    }
}
