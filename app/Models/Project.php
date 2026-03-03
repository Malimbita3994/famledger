<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    public const TYPE_CONSTRUCTION = 'construction';
    public const TYPE_EVENT = 'event';
    public const TYPE_EDUCATION = 'education';
    public const TYPE_BUSINESS = 'business';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_INVESTMENT = 'investment';
    public const TYPE_OTHER = 'other';

    public const STATUS_PLANNING = 'planning';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    protected $fillable = [
        'family_id',
        'wallet_id',
        'budget_id',
        'name',
        'description',
        'type',
        'planned_budget',
        'currency_code',
        'start_date',
        'target_end_date',
        'actual_end_date',
        'status',
        'priority',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'planned_budget' => 'decimal:2',
            'start_date' => 'date',
            'target_end_date' => 'date',
            'actual_end_date' => 'date',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_CONSTRUCTION => 'Construction',
            self::TYPE_EVENT => 'Event',
            self::TYPE_EDUCATION => 'Education',
            self::TYPE_BUSINESS => 'Business',
            self::TYPE_PURCHASE => 'Purchase',
            self::TYPE_INVESTMENT => 'Investment',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PLANNING => 'Planning',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_ON_HOLD => 'On Hold',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function priorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_CRITICAL => 'Critical',
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

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fundings(): HasMany
    {
        return $this->hasMany(ProjectFunding::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /** Total funding received (sum of project_fundings). */
    public function getTotalFundingAttribute(): float
    {
        return (float) $this->fundings()->sum('amount');
    }

    /** Total expenses linked to this project. */
    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    /** Remaining funds: funding - expenses. */
    public function getRemainingFundsAttribute(): float
    {
        return $this->total_funding - $this->total_expenses;
    }

    /** Budget variance: expenses - planned_budget (positive = over budget). */
    public function getBudgetVarianceAttribute(): float
    {
        return $this->total_expenses - (float) $this->planned_budget;
    }

    /** Funding progress % (funding / planned_budget * 100). */
    public function getFundingProgressPercentAttribute(): float
    {
        $planned = (float) $this->planned_budget;
        if ($planned <= 0) {
            return 0;
        }
        return min(100, round(($this->total_funding / $planned) * 100, 1));
    }

    /** Spending progress % (expenses / planned_budget * 100). */
    public function getSpendingProgressPercentAttribute(): float
    {
        $planned = (float) $this->planned_budget;
        if ($planned <= 0) {
            return 0;
        }
        return round(($this->total_expenses / $planned) * 100, 1);
    }
}
