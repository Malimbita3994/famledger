<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Budgets = planning layer. They guide decisions; they do not move money.
 * Used amount = sum of expenses in period matching scope, plus savings→budget allocations in that period.
 */
class Budget extends Model
{
    protected $fillable = [
        'family_id',
        'name',
        'type',
        'amount',
        'currency_code',
        'start_date',
        'end_date',
        'recurrence',
        'project_id',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public const TYPE_FAMILY = 'family';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_WALLET = 'wallet';
    public const TYPE_PROJECT = 'project';

    public static function types(): array
    {
        // UI-facing labels. Internal type values stay the same.
        return [
            self::TYPE_FAMILY => 'Main',
            self::TYPE_PROJECT => 'Project',
            self::TYPE_CATEGORY => 'Expenses',
        ];
    }

    public static function recurrences(): array
    {
        return [
            'none' => 'Single period',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Project this budget tracks (type = project). */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** Wallets this budget applies to (type = wallet). */
    public function wallets(): BelongsToMany
    {
        return $this->belongsToMany(Wallet::class, 'budget_wallet')->withTimestamps();
    }

    /** Expense categories this budget applies to (type = category). */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ExpenseCategory::class, 'budget_category', 'budget_id', 'expense_category_id')->withTimestamps();
    }

    /**
     * Expenses in period matching scope, plus savings→budget allocations in that period.
     */
    public function getUsedAmountAttribute(): float
    {
        $baseQuery = $this->family->expenses()
            ->whereBetween('expense_date', [$this->start_date, $this->end_date]);

        // 1) Primary relationship (clear & explicit):
        //    If expenses are tagged with this budget, always use that.
        $taggedSum = (float) (clone $baseQuery)
            ->where('budget_id', $this->id)
            ->sum('amount');

        if ($taggedSum > 0) {
            return $taggedSum;
        }

        // 2) Fallback relationship (when budget_id isn't set on expenses yet):
        //    Use the budget's scope (wallet/category/project) based on its pivots.
        switch ($this->type) {
            case self::TYPE_FAMILY:
                return (float) $baseQuery->sum('amount');

            case self::TYPE_WALLET:
                $walletIds = $this->wallets->pluck('id')->toArray();
                if (empty($walletIds)) {
                    return (float) $baseQuery->sum('amount');
                }

                return (float) (clone $baseQuery)
                    ->whereIn('wallet_id', $walletIds)
                    ->sum('amount');

            case self::TYPE_CATEGORY:
                $categoryIds = $this->categories->pluck('id')->toArray();
                if (empty($categoryIds)) {
                    return (float) $baseQuery->sum('amount');
                }

                return (float) (clone $baseQuery)
                    ->whereIn('category_id', $categoryIds)
                    ->sum('amount');

            case self::TYPE_PROJECT:
                if ($this->project_id) {
                    return (float) (clone $baseQuery)
                        ->where('project_id', (int) $this->project_id)
                        ->sum('amount');
                }

                return 0.0;

            default:
                return 0.0;
        }
    }

    public function getUtilizationPercentAttribute(): float
    {
        if ((float) $this->amount <= 0) {
            return 0;
        }
        return min(100, round(($this->used_amount / (float) $this->amount) * 100, 1));
    }

    public function getIsExceededAttribute(): bool
    {
        return $this->used_amount >= (float) $this->amount;
    }

    public function getRemainingAmountAttribute(): float
    {
        return round((float) $this->amount - $this->used_amount, 2);
    }
}
