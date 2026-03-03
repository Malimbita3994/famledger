<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFunding extends Model
{
    public const SOURCE_TRANSFER = 'transfer';
    public const SOURCE_SAVINGS = 'savings';
    public const SOURCE_INCOME = 'income';

    protected $fillable = [
        'project_id',
        'wallet_id',
        'amount',
        'currency_code',
        'funding_date',
        'source_type',
        'reference',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'funding_date' => 'date',
        ];
    }

    public static function sourceTypes(): array
    {
        return [
            self::SOURCE_TRANSFER => 'Transfer from wallet',
            self::SOURCE_SAVINGS => 'Savings goal',
            self::SOURCE_INCOME => 'Income allocation',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
