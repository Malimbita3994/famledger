<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Income always goes into a wallet. Family → Wallet → Income.
 * No wallet = no valid income record.
 */
class Income extends Model
{
    protected $fillable = [
        'family_id',
        'wallet_id',
        'category_id',
        'family_liability_id',
        'amount',
        'currency_code',
        'source',
        'received_date',
        'notes',
        'received_by',
        'created_by',
        'reconciliation_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_date' => 'date',
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
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function familyLiability(): BelongsTo
    {
        return $this->belongsTo(FamilyLiability::class, 'family_liability_id');
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(WalletReconciliation::class, 'reconciliation_id');
    }
}
