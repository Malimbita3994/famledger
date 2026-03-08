<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Stand-alone family wallet (internal ledger).
 * Money lives here; no bank/external integration.
 */
class Wallet extends Model
{
    protected $fillable = [
        'family_id',
        'name',
        'type',
        'currency_code',
        'description',
        'initial_balance',
        'is_primary',
        'is_liquid',
        'is_wealth_wallet',
        'is_shared',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'is_primary' => 'boolean',
            'is_liquid' => 'boolean',
            'is_wealth_wallet' => 'boolean',
            'is_shared' => 'boolean',
        ];
    }

    public static function types(): array
    {
        return [
            'cash' => 'Cash',
            'savings' => 'Savings',
            'daily_spending' => 'Daily Spending',
            'emergency_fund' => 'Emergency Fund',
            'project_fund' => 'Project Fund',
            'allowance' => 'Allowance',
            'business_fund' => 'Business Fund',
            'other' => 'Other',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_wallet_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_wallet_id');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(WalletReconciliation::class, 'wallet_id');
    }

    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class, 'wallet_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Balance computed from wallet_transactions ledger.
     * Falls back to initial_balance + manual calculation if no ledger entries.
     */
    public function getBalanceAttribute(): float
    {
        // First try to get balance from the latest transaction
        $latestTransaction = $this->transactions()->latest('created_at')->first();
        if ($latestTransaction && $latestTransaction->balance_after !== null) {
            return (float) $latestTransaction->balance_after;
        }

        // Fallback to manual calculation (for backward compatibility during migration)
        $income = isset($this->attributes['incomes_sum_amount'])
            ? (float) $this->attributes['incomes_sum_amount']
            : (float) $this->incomes()->sum('amount');
        $expense = isset($this->attributes['expenses_sum_amount'])
            ? (float) $this->attributes['expenses_sum_amount']
            : (float) $this->expenses()->sum('amount');
        $incoming = isset($this->attributes['incoming_transfers_sum_amount'])
            ? (float) $this->attributes['incoming_transfers_sum_amount']
            : (float) $this->incomingTransfers()->sum('amount');
        $outgoing = isset($this->attributes['outgoing_transfers_sum_amount'])
            ? (float) $this->attributes['outgoing_transfers_sum_amount']
            : (float) $this->outgoingTransfers()->sum('amount');
        return (float) $this->initial_balance + $income - $expense + $incoming - $outgoing;
    }

    /**
     * Get balance as of a specific date from the ledger.
     */
    public function balanceAsOf(\Carbon\Carbon $asOf): float
    {
        // Find the latest transaction on or before the given date
        $latestTransaction = $this->transactions()
            ->where('created_at', '<=', $asOf)
            ->latest('created_at')
            ->first();

        if ($latestTransaction && $latestTransaction->balance_after !== null) {
            return (float) $latestTransaction->balance_after;
        }

        // Fallback to manual calculation for transactions up to the date
        $income = $this->incomes()->where('received_date', '<=', $asOf)->sum('amount');
        $expense = $this->expenses()->where('expense_date', '<=', $asOf)->sum('amount');
        $incoming = $this->incomingTransfers()->where('transfer_date', '<=', $asOf)->sum('amount');
        $outgoing = $this->outgoingTransfers()->where('transfer_date', '<=', $asOf)->sum('amount');
        return (float) $this->initial_balance + $income - $expense + $incoming - $outgoing;
    }
}
