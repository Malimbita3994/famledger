<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A savings goal = target amount + deadline + wallet where funds accumulate.
 * Progress = sum of contributions.
 */
class SavingsGoal extends Model
{
    protected $fillable = [
        'family_id',
        'name',
        'description',
        'target_amount',
        'currency_code',
        'target_date',
        'start_date',
        'wallet_id',
        'budget_id',
        'linked_project_id',
        'status',
        'priority',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'target_date' => 'date',
            'start_date' => 'date',
        ];
    }

    public static function statuses(): array
    {
        return [
            'active' => 'Active',
            'completed' => 'Completed',
            'paused' => 'Paused',
            'cancelled' => 'Cancelled',
            'overdue' => 'Overdue',
        ];
    }

    public static function priorities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SavingsContribution::class, 'savings_goal_id');
    }

    public function budgetAllocations(): HasMany
    {
        return $this->hasMany(SavingsBudgetAllocation::class);
    }

    public function getSavedAmountAttribute(): float
    {
        if (isset($this->attributes['contributions_sum_amount'])) {
            return (float) $this->attributes['contributions_sum_amount'];
        }
        return (float) $this->contributions()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->target_amount - $this->saved_amount);
    }

    public function getCompletionPercentAttribute(): float
    {
        if ((float) $this->target_amount <= 0) {
            return 0;
        }
        return min(100, round(((float) $this->saved_amount / (float) $this->target_amount) * 100, 1));
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->saved_amount >= (float) $this->target_amount;
    }
}
