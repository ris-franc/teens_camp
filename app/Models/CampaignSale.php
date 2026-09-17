<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'batch_id',
        'seller_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'unit_profit',
        'total_profit',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'unit_profit' => 'decimal:2',
            'total_profit' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CampaignWeeklyBatch::class, 'batch_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CampaignProduct::class, 'product_id');
    }
}
