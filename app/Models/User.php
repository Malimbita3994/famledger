<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_LOCKED = 'locked';

    public const STATUS_PENDING = 'pending';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'status',
        'last_login_at',
        'created_by',
        'notification_preferences',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'notification_preferences' => 'array',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Default notification preference keys (merged with stored JSON).
     *
     * @return array<string, mixed>
     */
    public static function defaultNotificationPreferences(): array
    {
        return [
            'team_wide_alerts' => true,
            'family_wide_alerts' => true,
            'channel_email_enabled' => true,
            'channel_mobile_enabled' => false,
            'channel_slack_enabled' => false,
            'slack_webhook_url' => null,
            'channel_desktop_enabled' => true,
            'notify_task_assigned' => true,
            'notify_budget_warning' => true,
            'notify_invoice' => true,
            'notify_feedback' => true,
            'notify_collaboration' => true,
            'notify_meeting_reminder' => true,
            'notify_status_change' => true,
            'dnd_enabled' => false,
            'dnd_until' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mergedNotificationPreferences(): array
    {
        return array_merge(
            self::defaultNotificationPreferences(),
            $this->notification_preferences ?? []
        );
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_LOCKED => 'Locked',
            self::STATUS_PENDING => 'Pending Verification',
        ];
    }

    /**
     * Public URL for the profile photo, or null if unset or the file is missing on disk.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $raw = $this->attributes['avatar'] ?? null;
        if (! is_string($raw)) {
            return null;
        }
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        $path = ltrim(str_replace('\\', '/', $raw), '/');

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        // Root-relative URL avoids broken images when APP_URL host differs from the browser (e.g. localhost vs 127.0.0.1).
        return '/storage/'.$path;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Families (households) this user belongs to.
     */
    public function families(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'family_user')
            ->withPivot(['role_id', 'joined_at', 'status', 'is_primary'])
            ->withTimestamps()
            ->using(FamilyMember::class);
    }

    /**
     * Family membership records (pivot with role, status, etc.).
     */
    public function familyMemberships(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'user_id');
    }

    /**
     * Incomes added by or received by this user. Usually linked by created_by for contribution.
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class, 'created_by');
    }

    /**
     * Expenses recorded by this user.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function engagementActivities(): HasMany
    {
        return $this->hasMany(EngagementActivity::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Linked OAuth identities (Google, Apple, etc.).
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }
}
