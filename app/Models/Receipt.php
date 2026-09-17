<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'receipt_number',
        'type',
        'user_id',
        'amount',
        'description',
        'meta_data',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta_data' => 'array',
        ];
    }

    public static function generateReceiptNumber(string $prefix = 'REC'): string
    {
        return sprintf('%s-%s-%05d', $prefix, date('Ymd'), mt_rand(1000, 99999));
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
