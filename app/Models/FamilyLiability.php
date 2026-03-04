<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyLiability extends Model
{
    protected $fillable = [
        'family_id',
        'name',
        'type',
        'status',
        'principal_amount',
        'interest_rate',
        'due_date',
        'wallet_id',
        'project_id',
        'property_id',
        'budget_id',
        'savings_goal_id',
        'created_by',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function savingsGoal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class, 'savings_goal_id');
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class, 'family_liability_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'family_liability_id');
    }

    /**
     * Computed outstanding balance = principal + loan draws (incomes) - repayments (expenses).
     */
    public function getOutstandingBalanceAttribute(): float
    {
        $principal = (float) ($this->principal_amount ?? 0);
        $draws = (float) $this->incomes()->sum('amount');
        $repayments = (float) $this->expenses()->sum('amount');

        return max(0, $principal + $draws - $repayments);
    }
}

