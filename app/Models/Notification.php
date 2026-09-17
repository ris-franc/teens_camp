<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'camp_season_id',
        'user_id',
        'target_role',
        'title',
        'message',
        'type',
        'icon',
        'link',
        'is_read',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function campSeason(): BelongsTo
    {
        return $this->belongsTo(CampSeason::class, 'camp_season_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Resolve icon class based on type or custom icon.
     */
    public function getIconClassAttribute(): string
    {
        if (!empty($this->icon)) {
            return $this->icon;
        }

        return match ($this->type) {
            'payment' => 'bi-cash-stack text-success',
            'campaign_sale' => 'bi-bag-check-fill text-warning',
            'batch_approved' => 'bi-shield-check text-success',
            'form' => 'bi-file-earmark-text-fill text-info',
            'adopt_a_teen' => 'bi-heart-pulse-fill text-danger',
            'registration' => 'bi-person-plus-fill text-primary',
            'checkin' => 'bi-geo-alt-fill text-success',
            'donation' => 'bi-piggy-bank-fill text-danger',
            'packing' => 'bi-backpack-fill text-danger',
            'security' => 'bi-key-fill text-warning',
            default => 'bi-bell-fill text-camp-red',
        };
    }

    /**
     * Create and append a notification to the database.
     */
    public static function send(
        ?int $userId,
        string $title,
        string $message,
        string $type = 'system',
        ?string $link = null,
        ?string $targetRole = null,
        ?string $icon = null,
        ?int $createdBy = null,
        ?int $seasonId = null
    ): self {
        if (!$seasonId) {
            $seasonId = CampSeason::find(session('admin_selected_season_id'))?->id ?? CampSeason::getActive()?->id;
        }

        $notification = self::create([
            'camp_season_id' => $seasonId,
            'user_id' => $userId,
            'target_role' => $targetRole,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'icon' => $icon,
            'link' => $link,
            'is_read' => false,
            'created_by' => $createdBy,
        ]);

        // Dispatch Email Notification via Brevo
        try {
            app(\App\Services\BrevoMailService::class)->dispatchNotificationEmail($notification);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Email notification dispatch error: ' . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Notify a specific user.
     */
    public static function notifyUser($userOrId, string $title, string $message, string $type = 'system', ?string $link = null, ?string $icon = null): ?self
    {
        $userId = is_object($userOrId) ? $userOrId->id : $userOrId;
        if (!$userId) return null;

        return self::send(
            userId: $userId,
            title: $title,
            message: $message,
            type: $type,
            link: $link,
            targetRole: null,
            icon: $icon
        );
    }

    /**
     * Notify an entire role (e.g. 'staff', 'admin', 'parent', 'teen', or 'all').
     */
    public static function notifyRole(string $role, string $title, string $message, string $type = 'system', ?string $link = null, ?string $icon = null): self
    {
        return self::send(
            userId: null,
            title: $title,
            message: $message,
            type: $type,
            link: $link,
            targetRole: $role,
            icon: $icon
        );
    }

    /**
     * Notify staff and administrators.
     */
    public static function notifyStaff(string $title, string $message, string $type = 'system', ?string $link = null, ?string $icon = null): self
    {
        return self::notifyRole('staff', $title, $message, $type, $link, $icon);
    }
}
