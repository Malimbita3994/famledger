<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Family;

trait AuthorizesFamilyMember
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }
}
