<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Stand-alone family wallet (internal ledger).
 * Money lives here; no bank/external integration.
 */
class Wallet extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

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
            'project_fund' => 'Goal fund',
            'allowance' => 'Allowance',
            'business_fund' => 'Business Fund',
            'other' => 'Other',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
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

    /** Project that uses this wallet as its funded cash bucket (created when project is first funded). */
    public function dedicatedProject(): HasOne
    {
        return $this->hasOne(Project::class, 'wallet_id');
    }

    /**
     * User-facing label: goal-linked wallets use the goal name (avoids legacy "Project: …" stored names).
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->relationLoaded('dedicatedProject')) {
            if ($this->dedicatedProject) {
                return (string) $this->dedicatedProject->name;
            }
        } else {
            $linkedName = $this->dedicatedProject()->value('name');
            if ($linkedName !== null && $linkedName !== '') {
                return (string) $linkedName;
            }
        }

        $name = (string) ($this->attributes['name'] ?? '');
        if ($this->type === 'project_fund' && str_starts_with($name, 'Project: ')) {
            return trim(substr($name, strlen('Project: ')));
        }

        return $name;
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

    /**
     * Ledger balance at end of the given calendar day (inclusive of that day’s transactions).
     * Used by reports (opening balance before a period, wallet statement running balance).
     */
    public function balanceAsOf(Carbon $date): float
    {
        $initial = $this->resolveInitialBalanceForPartialModel();
        $end = $date->copy()->endOfDay();

        $income = (float) $this->incomes()
            ->where('received_date', '<=', $end)
            ->sum('amount');

        $expense = (float) $this->expenses()
            ->where('expense_date', '<=', $end)
            ->sum('amount');

        $incoming = (float) $this->incomingTransfers()
            ->where('transfer_date', '<=', $end)
            ->sum('amount');

        $outgoing = (float) $this->outgoingTransfers()
            ->where('transfer_date', '<=', $end)
            ->sum('amount');

        return $initial + $income - $expense + $incoming - $outgoing;
    }

    /**
     * Report queries sometimes load wallets with only id/name; initial_balance must still apply.
     */
    protected function resolveInitialBalanceForPartialModel(): float
    {
        if (array_key_exists('initial_balance', $this->attributes) && $this->attributes['initial_balance'] !== null) {
            return (float) $this->attributes['initial_balance'];
        }

        $value = static::query()->whereKey($this->getKey())->value('initial_balance');

        return (float) ($value ?? 0);
    }

    /**
     * Whether this debit can be applied without pushing balance below zero
     * (ignored when famledger.allow_negative_wallet_balance is true).
     */
    public function canAffordDebit(float $amount): bool
    {
        if (config('famledger.allow_negative_wallet_balance', false)) {
            return true;
        }

        return round((float) $this->balance, 2) >= round($amount, 2);
    }
}
