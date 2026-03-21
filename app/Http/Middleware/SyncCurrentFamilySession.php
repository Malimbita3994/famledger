<?php

namespace App\Http\Middleware;

use App\Models\Family;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keep session('current_family_id') in sync with the family in the URL so audit_logs.family_id is filled.
 *
 * The layout uses the user's first family for UI; audit logging previously only read session, which was
 * almost never set (except right after creating a family).
 */
class SyncCurrentFamilySession
{
    public function handle(Request $request, Closure $next): Response
    {
        $family = $request->route('family');

        if ($family instanceof Family && $request->user()) {
            $request->session()->put('current_family_id', $family->id);
        }

        return $next($request);
    }
}
