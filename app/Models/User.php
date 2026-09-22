<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'pin',
        'role',
        'pin_reset_required',
        'is_suspended',
        'avatar',
        'phone',
        'gender',
        'date_of_birth',
        'address',
    ];

    protected $hidden = [
        'pin',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'pin_reset_required' => 'boolean',
            'is_suspended' => 'boolean',
        ];
    }

    public function isSuspended(): bool
    {
        return (bool) ($this->is_suspended ?? false);
    }

    /**
     * Override auth password name to use 4-digit PIN.
     */
    public function getAuthPasswordName(): string
    {
        return 'pin';
    }

    public function getAuthPassword(): string
    {
        return $this->pin;
    }

    // Role checks
    public function isTeen(): bool
    {
        return $this->role === 'teen';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPastor(): bool
    {
        return $this->role === 'pastor';
    }

    public function isRegistration(): bool
    {
        return $this->role === 'registration';
    }

    public function isCampaign(): bool
    {
        return $this->role === 'campaign';
    }

    public function isCampaignHead(): bool
    {
        return $this->role === 'campaign_head';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'pastor', 'registration', 'campaign', 'campaign_head']);
    }

    /**
     * Get avatar URL (Supabase Storage or local).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }
        return \App\Services\SupabaseStorageService::getUrl($this->avatar);
    }

    // Relationships
    public function teens(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_teen', 'parent_id', 'teen_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_teen', 'teen_id', 'parent_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    /**
     * Get all sibling teens linked to the same parent(s).
     */
    public function siblings()
    {
        if (!$this->isTeen()) {
            return collect();
        }

        $parentIds = $this->parents()->allRelatedIds();
        if ($parentIds->isEmpty()) {
            return collect();
        }

        return User::whereHas('parents', function ($q) use ($parentIds) {
            $q->whereIn('parent_teen.parent_id', $parentIds);
        })->where('users.id', '!=', $this->id)->get();
    }



    public function primaryParent(): ?User
    {
        return $this->parents->first();
    }


    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'teen_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function adoptATeenRequests(): HasMany
    {
        return $this->hasMany(AdoptATeenRequest::class, 'parent_id');
    }

    public function campaignSales(): HasMany
    {
        return $this->hasMany(CampaignSale::class, 'seller_id');
    }
}
