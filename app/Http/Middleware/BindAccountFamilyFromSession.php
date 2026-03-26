<?php

namespace App\Http\Middleware;

use App\Models\Family;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For /account/*, /family/*, and /accounts/* URLs, inject the current family as route parameter
 * "family" so controllers keep type-hinting Family $family without {family} in the path.
 */
class BindAccountFamilyFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $scoped = $request->is('account', 'account/*')
            || $request->is('family', 'family/*')
            || $request->is('accounts', 'accounts/*');

        if (! $scoped) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $family = null;
        $id = $request->session()->get('current_family_id');
        if ($id) {
            $candidate = Family::query()->whereKey($id)->first();
            if ($candidate) {
                if ($candidate->members()->where('user_id', $user->id)->exists()) {
                    $family = $candidate;
                } elseif ($user->families()->exists()) {
                    // Session pins another family's id while this user belongs to a different family (tampering / stale tab).
                    abort(403, __('You do not have access to this family.'));
                }
            }
        }

        if (! $family) {
            $family = $user->families()->first();
        }

        if (! $family) {
            return redirect()
                ->route('families.index')
                ->with('error', __('Choose or create a family to continue.'));
        }

        $request->route()?->setParameter('family', $family);

        return $next($request);
    }
}
