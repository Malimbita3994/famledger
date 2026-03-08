<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Budgets = planning layer. They guide decisions; they do not move money.
 * Monitors expenses only. Used amount = sum of expenses in period matching scope.
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

    public function savingsAllocations(): HasMany
    {
        return $this->hasMany(SavingsBudgetAllocation::class);
    }

    /**
     * Sum of expenses in [start_date, end_date] matching this budget's scope or assignment.
     * Plus any allocations from savings goals to this budget.
     */
    public function getUsedAmountAttribute(): float
    {
        $query = $this->family->expenses()
            ->whereBetween('expense_date', [$this->start_date, $this->end_date]);

        $expenses = 0;
        if ($this->type === self::TYPE_FAMILY) {
            // For main budget, sum all expenses
            $expenses = (float) $query->sum('amount');
        } else {
            // For sub-budgets, sum assigned expenses
            $expenses = (float) $query->where('budget_id', $this->id)->sum('amount');
        }

        // Add allocations from savings goals
        $allocations = (float) $this->savingsAllocations()
            ->whereBetween('allocated_date', [$this->start_date, $this->end_date])
            ->sum('amount');

        return $expenses + $allocations;
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - $this->used_amount);
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

    /**
     * Check if this budget can be supported by the given wallet balance.
     */
    public function canBeSupportedByWallet(Wallet $wallet): array
    {
        $remainingBudget = $this->remaining_amount;
        $availableBalance = $wallet->balance;

        return [
            'can_support' => $availableBalance >= $remainingBudget,
            'remaining_budget' => $remainingBudget,
            'available_balance' => $availableBalance,
            'shortfall' => max(0, $remainingBudget - $availableBalance),
        ];
    }
}
