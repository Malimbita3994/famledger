<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    /**
     * Sum of expenses in [start_date, end_date] matching this budget's scope.
     */
    public function getUsedAmountAttribute(): float
    {
        $query = $this->family->expenses()
            ->whereBetween('expense_date', [$this->start_date, $this->end_date]);

        switch ($this->type) {
            case self::TYPE_FAMILY:
                // All family expenses in period
                break;
            case self::TYPE_WALLET:
                $walletIds = $this->wallets->pluck('id')->toArray();
                if (empty($walletIds)) {
                    return 0;
                }
                $query->whereIn('wallet_id', $walletIds);
                break;
            case self::TYPE_CATEGORY:
                $categoryIds = $this->categories->pluck('id')->toArray();
                if (empty($categoryIds)) {
                    return 0;
                }
                $query->whereIn('category_id', $categoryIds);
                break;
            case self::TYPE_PROJECT:
                if ($this->project_id) {
                    $query->where('project_id', $this->project_id);
                }
                break;
            default:
                break;
        }

        return (float) $query->sum('amount');
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
}
