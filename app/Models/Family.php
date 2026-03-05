<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
