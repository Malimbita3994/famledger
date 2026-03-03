<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Verify wallet system balance matches actual balance.
 * difference = actual_balance - system_balance; surplus creates income adjustment, shortage creates expense adjustment.
 */
class WalletReconciliation extends Model
{
    protected $table = 'wallet_reconciliations';

    protected $fillable = [
        'family_id',
        'wallet_id',
        'system_balance',
        'actual_balance',
        'difference',
        'reconciled_at',
        'method',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'system_balance' => 'decimal:2',
            'actual_balance' => 'decimal:2',
            'difference' => 'decimal:2',
            'reconciled_at' => 'datetime',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function adjustmentIncome(): HasOne
    {
        return $this->hasOne(Income::class, 'reconciliation_id');
    }

    public function adjustmentExpense(): HasOne
    {
        return $this->hasOne(Expense::class, 'reconciliation_id');
    }

    public function isBalanced(): bool
    {
        return (float) $this->difference === 0.0;
    }

    public function isSurplus(): bool
    {
        return (float) $this->difference > 0;
    }

    public function isShortage(): bool
    {
        return (float) $this->difference < 0;
    }
}
