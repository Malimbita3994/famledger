<?php

namespace App\Policies;

use App\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    /**
     * Whether the user may access this family's ledger and resources.
     */
    public function view(User $user, Family $family): bool
    {
        if ($user->hasRole(['Super Admin', 'super-admin'])) {
            return true;
        }

        return $family->members()->where('user_id', $user->id)->exists();
    }
}
