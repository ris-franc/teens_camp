<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'name',
        'unit_cost',
        'unit_price',
        'profit_per_unit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'profit_per_unit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(CampaignSale::class, 'product_id');
    }
}
