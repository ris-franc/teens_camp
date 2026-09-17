<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignWeeklyBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'week_label',
        'week_start',
        'week_end',
        'total_sales_amount',
        'total_profit_amount',
        'status',
        'approved_by',
        'notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'total_sales_amount' => 'decimal:2',
            'total_profit_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(CampaignSale::class, 'batch_id');
    }
}
