<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'category',
        'item_name',
        'notes',
        'is_essential',
        'is_released',
    ];

    protected function casts(): array
    {
        return [
            'is_essential' => 'boolean',
            'is_released' => 'boolean',
        ];
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }
}
