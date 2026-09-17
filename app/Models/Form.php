<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'title',
        'description',
        'target_role',
        'requires_parent_approval',
        'is_published',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'requires_parent_approval' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class, 'form_id')->orderBy('order_index');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
