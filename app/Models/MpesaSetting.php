<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'paybill_number',
        'camp_fee_account',
        'adopt_account',
        'consumer_key',
        'consumer_secret',
        'passkey',
        'environment',
        'is_mock_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_mock_enabled' => 'boolean',
        ];
    }

    /**
     * Get or create the singleton settings record.
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'paybill_number' => '880100',
            'camp_fee_account' => 'CAMP-FEES',
            'adopt_account' => 'ADOPT-A-TEEN',
            'environment' => 'sandbox',
            'is_mock_enabled' => true,
        ]);
    }

    /**
     * Get the Paybill account number for a specific payment purpose.
     */
    public function getAccountForType(string $type): string
    {
        if ($type === 'adopt_a_teen' || $type === 'adopt') {
            return $this->adopt_account ?: 'ADOPT-A-TEEN';
        }

        return $this->camp_fee_account ?: 'CAMP-FEES';
    }

    public function isLive(): bool
    {
        return $this->environment === 'live';
    }

    public function isMock(): bool
    {
        return (bool)$this->is_mock_enabled || empty($this->consumer_key) || empty($this->consumer_secret);
    }
}
