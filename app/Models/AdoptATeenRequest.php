<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdoptATeenRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'parent_id',
        'teen_id',
        'amount_requested',
        'reason',
        'status',
        'reviewed_by',
        'decision_notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_requested' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function teen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teen_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
