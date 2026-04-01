<?php

namespace App\Models;

use App\Traits\BroadcastsFamilyProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Income always goes into a wallet. Family → Wallet → Income.
 * No wallet = no valid income record.
 */
class Income extends Model
{
    use BroadcastsFamilyProfile;

    public const SOURCE_ENTITY_EMPLOYER = 'employer';

    public const SOURCE_ENTITY_TENANT = 'tenant';

    public const SOURCE_ENTITY_CLIENT = 'client';

    public const SOURCE_ENTITY_OTHER = 'other';

    public const RECURRING_WEEKLY = 'weekly';

    public const RECURRING_MONTHLY = 'monthly';

    protected $fillable = [
        'family_id',
        'wallet_id',
        'category_id',
        'family_liability_id',
        'linked_project_id',
        'linked_property_id',
        'amount',
        'currency_code',
        'source',
        'source_entity_type',
        'received_date',
        'notes',
        'is_recurring',
        'recurring_frequency',
        'is_taxable',
        'received_by',
        'created_by',
        'reconciliation_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_date' => 'date',
            'is_recurring' => 'boolean',
            'is_taxable' => 'boolean',
        ];
    }

    public static function sourceEntityTypes(): array
    {
        return [
            self::SOURCE_ENTITY_EMPLOYER => __('Employer'),
            self::SOURCE_ENTITY_TENANT => __('Tenant'),
            self::SOURCE_ENTITY_CLIENT => __('Client'),
            self::SOURCE_ENTITY_OTHER => __('Other'),
        ];
    }

    public static function recurringFrequencies(): array
    {
        return [
            self::RECURRING_WEEKLY => __('Weekly'),
            self::RECURRING_MONTHLY => __('Monthly'),
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

    public function linkedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'linked_project_id');
    }

    public function linkedProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'linked_property_id');
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(WalletReconciliation::class, 'reconciliation_id');
    }
}
