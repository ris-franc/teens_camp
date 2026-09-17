<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'teen_id',
        'registered_by',
        'status',
        'phone_carried',
        'medication_notes',
        'medical_conditions',
        'emergency_contact_name',
        'emergency_contact_phone',
        'signed_in_at',
        'signed_in_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'phone_carried' => 'boolean',
            'signed_in_at' => 'datetime',
        ];
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function teen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teen_id');
    }

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function signedInByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_in_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'registration_id');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getBalanceRemainingAttribute(): float
    {
        $cost = $this->campSeason ? (float) $this->campSeason->price : 0;
        return max(0, $cost - $this->total_paid);
    }

    public function getPaymentPercentAttribute(): int
    {
        $cost = $this->campSeason ? (float) $this->campSeason->price : 0;
        if ($cost <= 0) return 100;
        return min(100, (int) round(($this->total_paid / $cost) * 100));
    }
}
