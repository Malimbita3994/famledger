<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Application and database audit trail.
 * type: 'application' (logins, actions) | 'database' (model create/update/delete with old/new values).
 */
class AuditLog extends Model
{
    public const TYPE_APPLICATION = 'application';
    public const TYPE_DATABASE = 'database';

    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';

    protected $fillable = [
        'type',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'user_id',
        'family_id',
        'ip',
        'user_agent',
        'url',
        'request_method',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    public function scopeApplication($query)
    {
        return $query->where('type', self::TYPE_APPLICATION);
    }

    public function scopeDatabase($query)
    {
        return $query->where('type', self::TYPE_DATABASE);
    }

    public function scopeForFamily($query, $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Log an application-level event (login, logout, custom action).
     */
    public static function logApplication(string $action, ?string $description = null, array $properties = []): ?self
    {
        $request = request();
        $data = [
            'type' => self::TYPE_APPLICATION,
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
            'user_id' => auth()->id(),
            'family_id' => $request && $request->hasSession() ? $request->session()->get('current_family_id') : null,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'url' => $request ? $request->fullUrl() : null,
            'request_method' => $request ? $request->method() : null,
        ];
        return self::create($data);
    }

    /**
     * Log a database-level change (model create/update/delete with old/new values).
     */
    public static function logDatabase(
        string $table,
        $subjectId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        array $overrides = []
    ): ?self {
        $subjectType = $overrides['subject_type'] ?? null;
        $familyId = $overrides['family_id'] ?? null;
        $request = request();
        $data = [
            'type' => self::TYPE_DATABASE,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'properties' => array_filter([
                'table' => $table,
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]),
            'user_id' => auth()->id(),
            'family_id' => $familyId ?: ($request && $request->hasSession() ? $request->session()->get('current_family_id') : null),
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'url' => $request ? $request->fullUrl() : null,
            'request_method' => $request ? $request->method() : null,
        ];
        return self::create(array_merge($data, array_diff_key($overrides, array_flip(['subject_type', 'family_id']))));
    }

    /**
     * Human-readable area/category for UI (derived from subject_type or action).
     */
    public function getAreaAttribute(): string
    {
        if ($this->subject_type) {
            $short = class_basename($this->subject_type);
            $map = [
                'Expense' => 'Transactions',
                'Income' => 'Transactions',
                'Transfer' => 'Transactions',
                'Budget' => 'Budgets',
                'Wallet' => 'Wallets & accounts',
                'SavingsGoal' => 'Wallets & accounts',
                'Project' => 'Projects',
                'Property' => 'Properties',
                'FamilyLiability' => 'Liabilities',
                'Family' => 'Members & roles',
                'FamilyMember' => 'Members & roles',
                'FamilyInvitation' => 'Members & roles',
            ];
            return $map[$short] ?? $short;
        }
        if (in_array($this->action, [self::ACTION_LOGIN, self::ACTION_LOGOUT], true)) {
            return 'Security';
        }
        return 'General';
    }
}
