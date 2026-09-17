<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdoptATeenKitty extends Model
{
    use HasFactory;

    protected $table = 'adopt_a_teen_kitty';

    protected $fillable = [
        'camp_season_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'reference',
        'receipt_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public static function getCurrentBalance(int $campSeasonId): float
    {
        $latest = static::where('camp_season_id', $campSeasonId)->latest('id')->first();
        return $latest ? (float) $latest->balance_after : 0.00;
    }

    public static function recordDonation(
        int $campSeasonId,
        float $amount,
        string $description,
        string $donorName,
        ?string $reference = null,
        ?int $createdBy = null,
        ?int $receiptId = null
    ): self {
        $currentBalance = static::getCurrentBalance($campSeasonId);
        $newBalance = $currentBalance + $amount;

        return static::create([
            'camp_season_id' => $campSeasonId,
            'type' => 'donation_in',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description,
            'reference' => $reference,
            'receipt_id' => $receiptId,
            'created_by' => $createdBy,
        ]);
    }


    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
