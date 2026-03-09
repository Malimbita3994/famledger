<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model for family_user (user ↔ family membership).
 * Each family should have exactly one primary owner (is_primary = true).
 */
class FamilyMember extends Pivot
{
    protected $table = 'family_user';

    public $incrementing = true;

    protected $fillable = [
        'family_id',
        'user_id',
        'role_id',
        'member_name',
        'sex',
        'member_type',
        'joined_at',
        'status',
        'leave_reason',
        'leave_notes',
        'leave_requested_at',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'leave_requested_at' => 'datetime',
            'is_primary' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(FamilyRole::class, 'role_id');
    }
}
