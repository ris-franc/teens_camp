<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'camp_season_id',
        'user_id',
        'teen_id',
        'status',
        'parent_feedback',
        'reviewed_by_parent_id',
        'reviewed_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teen_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(FormSubmissionValue::class, 'form_submission_id');
    }

    public function reviewedByParent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_parent_id');
    }
}
