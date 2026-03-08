<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PropertyValuation;
use App\Models\PropertyDepreciation;

class Family extends Model
{
    protected $fillable = [
        'name',
        'description',
        'currency_code',
        'timezone',
        'country',
        'created_by',
        'status',
        'invite_token',
    ];

    public function getInviteLinkAttribute(): ?string
    {
        if (! $this->invite_token) {
            return null;
        }

        return route('invite.join', ['token' => $this->invite_token]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'family_user')
            ->withPivot(['role_id', 'joined_at', 'status', 'is_primary'])
            ->withTimestamps()
            ->using(FamilyMember::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class)->orderByDesc('is_primary');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(FamilyInvitation::class);
    }

    public function primaryMember(): HasMany
    {
        return $this->hasMany(FamilyMember::class)->where('is_primary', true);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function mainWallet(): ?Wallet
    {
        return $this->wallets()->where('is_primary', true)->first();
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(WalletReconciliation::class, 'family_id');
    }

    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class, 'family_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function liabilities(): HasMany
    {
        return $this->hasMany(FamilyLiability::class);
    }

    /**
     * Calculate total wealth from liquid, wealth-contributing wallets.
     */
    public function getWalletWealthTotal(): float
    {
        return $this->wallets()
            ->where('is_liquid', true)
            ->where('is_wealth_wallet', true)
            ->get()
            ->sum(function ($wallet) {
                return $wallet->balance;
            });
    }

    /**
     * Calculate total wealth from project wallets.
     */
    public function getProjectWealthTotal(): float
    {
        return $this->wallets()
            ->where('type', 'project_fund')
            ->get()
            ->sum(function ($wallet) {
                return $wallet->balance;
            });
    }

    /**
     * Calculate total property wealth (latest valuations/depreciations).
     */
    public function getPropertyWealthTotal(): float
    {
        $properties = $this->properties()->get(['id', 'purchase_price', 'current_estimated_value']);
        $propertyIds = $properties->pluck('id')->all();

        $latestValuations = PropertyValuation::whereIn('property_id', $propertyIds)
            ->select('property_id', 'estimated_value', 'valuation_date')
            ->orderBy('valuation_date', 'desc')
            ->get()
            ->groupBy('property_id')
            ->map->first();

        $latestDepreciations = PropertyDepreciation::whereIn('property_id', $propertyIds)
            ->select('property_id', 'year', 'book_value')
            ->orderBy('year', 'desc')
            ->get()
            ->groupBy('property_id')
            ->map->first();

        $total = 0.0;
        foreach ($properties as $property) {
            $latestVal = $latestValuations[$property->id] ?? null;
            $latestDep = $latestDepreciations[$property->id] ?? null;
            $purchase = (float) ($property->purchase_price ?? 0);
            $valuation = $latestVal ? (float) $latestVal->estimated_value : (float) ($property->current_estimated_value ?? 0);
            $book = $latestDep ? (float) $latestDep->book_value : ($valuation ?: $purchase);
            $total += $book;
        }

        return $total;
    }

    /**
     * Calculate total liabilities.
     */
    public function getLiabilityTotal(): float
    {
        return (float) $this->liabilities()
            ->where('status', '!=', 'closed')
            ->sum('outstanding_balance');
    }

    /**
     * Calculate net wealth (assets - liabilities).
     */
    public function getNetWealth(): float
    {
        $assets = $this->getWalletWealthTotal() + $this->getPropertyWealthTotal() + $this->getProjectWealthTotal();
        $liabilities = $this->getLiabilityTotal();
        return $assets - $liabilities;
    }
}
