<?php

namespace App\Http\Controllers\Api\Concerns;

trait AuthorizesAdmin
{
    protected function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('access_admin_panel')) {
            return;
        }
        abort(403, 'You do not have access to the administration area.');
    }
}
