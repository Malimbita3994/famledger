<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Family;

trait AuthorizesFamilyMember
{
    protected function authorizeFamilyMember(Family $family): void
    {
        $this->authorize('view', $family);
    }
}
