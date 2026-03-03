<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Every expense reduces a wallet balance. Family → Wallet → Expense.
 * No wallet = no valid expense.
 */
class Expense extends Model
{
    protected $fillable = [
        'family_id',
        'wallet_id',
        'category_id',
        'project_id',
        'amount',
        'currency_code',
        'description',
        'expense_date',
        'paid_by',
        'merchant',
        'payment_method',
        'reference',
        'is_recurring',
        'created_by',
        'reconciliation_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    public static function paymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'wallet_transfer' => 'Wallet Transfer',
            'card' => 'Card',
            'mobile_money' => 'Mobile Money',
            'other' => 'Other',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(WalletReconciliation::class, 'reconciliation_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
