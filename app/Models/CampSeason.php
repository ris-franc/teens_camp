<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampSeason extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'theme',
        'start_date',
        'end_date',
        'venue',
        'price',
        'capacity',
        'status',
        'is_registration_open',
        'description',
        'landing_subtitle',
        'announcement_banner',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'price' => 'decimal:2',
            'capacity' => 'integer',
            'is_registration_open' => 'boolean',
        ];
    }

    public function isRegistrationOpen(): bool
    {
        return (bool) ($this->is_registration_open ?? true);
    }

    public static function getActive(): ?self
    {
        return static::where('status', 'active')->first() ?? static::latest('id')->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'camp_season_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'camp_season_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'camp_season_id');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class, 'camp_season_id');
    }

    public function packingLists(): HasMany
    {
        return $this->hasMany(PackingList::class, 'camp_season_id');
    }

    public function adoptATeenRequests(): HasMany
    {
        return $this->hasMany(AdoptATeenRequest::class, 'camp_season_id');
    }

    public function kittyLedger(): HasMany
    {
        return $this->hasMany(AdoptATeenKitty::class, 'camp_season_id');
    }

    public function campaignProducts(): HasMany
    {
        return $this->hasMany(CampaignProduct::class, 'camp_season_id');
    }

    public function campaignWeeklyBatches(): HasMany
    {
        return $this->hasMany(CampaignWeeklyBatch::class, 'camp_season_id');
    }

    public function campaignSales(): HasMany
    {
        return $this->hasMany(CampaignSale::class, 'camp_season_id');
    }
}
