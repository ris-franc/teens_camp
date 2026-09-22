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
        'poster_path',
        'core_values',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'price' => 'decimal:2',
            'capacity' => 'integer',
            'is_registration_open' => 'boolean',
            'core_values' => 'array',
        ];
    }

    public function getCoreValues(): array
    {
        if (!empty($this->core_values) && is_array($this->core_values)) {
            return $this->core_values;
        }

        return [
            [
                'title' => 'Christ-Centered Faith',
                'icon' => 'bi-fire',
                'description' => 'Dynamic worship, powerful morning & evening word sessions, and authentic prayer encounters that ground teenagers in Christ.',
            ],
            [
                'title' => 'Brotherhood & Community',
                'icon' => 'bi-people-fill',
                'description' => 'A safe, welcoming family environment where every teenager is known, valued, and built up in genuine Christian fellowship.',
            ],
            [
                'title' => 'Courage & Adventure',
                'icon' => 'bi-compass-fill',
                'description' => 'Conquering obstacle trails, outdoor sports, and team wilderness challenges that cultivate resilience and discipline.',
            ],
            [
                'title' => 'Character & Leadership',
                'icon' => 'bi-award-fill',
                'description' => 'Instilling moral integrity, responsibility, and empathy so campers return home as positive leaders in their homes and schools.',
            ],
            [
                'title' => 'Pastoral Care & Safety',
                'icon' => 'bi-shield-fill-check',
                'description' => 'Trained staff counselors, on-site medical care, safe accommodations, and verified attendance giving parents peace of mind.',
            ],
        ];
    }

    public function getPosterUrl(): string
    {
        if ($this->poster_path) {
            if (str_starts_with($this->poster_path, 'http://') || str_starts_with($this->poster_path, 'https://')) {
                return $this->poster_path;
            }
            if (file_exists(public_path($this->poster_path))) {
                return asset($this->poster_path);
            }
            return \App\Services\SupabaseStorageService::getUrl($this->poster_path);
        }
        if (file_exists(public_path('images/camp-poster.jpg'))) {
            return asset('images/camp-poster.jpg');
        }
        return asset('images/hero-camp.jpg');
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
