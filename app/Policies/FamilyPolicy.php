<?php

namespace App\Policies;

use App\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    /**
     * Whether the user may access this family's ledger and resources.
     *
     * Super Admin has no elevated access to families they do not belong to (list-only via {@see FamilyController::index}).
     */
    public function view(User $user, Family $family): bool
    {
        return $family->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Family metadata and relationships (tree, members, etc.).
     */
    public function update(User $user, Family $family): bool
    {
        return $this->view($user, $family);
    }
}
