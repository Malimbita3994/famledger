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
        'is_shared',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'is_primary' => 'boolean',
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

    /**
     * Balance = initial_balance + income - expenses + transfers_in - transfers_out.
     * Uses *_sum_amount when eager-loaded via withSum().
     */
    public function getBalanceAttribute(): float
    {
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
}
