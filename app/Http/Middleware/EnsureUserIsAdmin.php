<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Restrict to users with Super Admin or Admin role (or access_admin_panel permission).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->hasRole('Super Admin') || $request->user()->hasRole('Admin') || $request->user()->can('access_admin_panel')) {
            return $next($request);
        }

        abort(403, 'You do not have access to the administration area.');
    }
}
