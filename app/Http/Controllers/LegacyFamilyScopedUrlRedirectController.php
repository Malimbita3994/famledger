<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 301 redirect from old /families/{id}/... scoped URLs to /family/..., /account/..., or /accounts/...
 */
class LegacyFamilyScopedUrlRedirectController extends Controller
{
    private const ALLOWED_FIRST_SEGMENTS = [
        'members',
        'invites',
        'wallets',
        'liabilities',
        'incomes',
        'expenses',
        'transactions',
        'transfers',
        'budgets',
        'reconciliations',
        'savings-goals',
        'projects',
        'projects-funding',
        'properties',
        'accounts',
        'reports',
        'audit-trail',
        'wealth',
    ];

    public function __invoke(Request $request, Family $family, string $path): RedirectResponse
    {
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(404);
        }

        $first = explode('/', trim($path, '/'), 2)[0] ?? '';
        if ($first === '' || ! in_array($first, self::ALLOWED_FIRST_SEGMENTS, true)) {
            abort(404);
        }

        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($family->members()->where('user_id', $user->id)->exists(), 403);

        $request->session()->put('current_family_id', $family->id);

        $path = ltrim($path, '/');
        $target = self::mapLegacyFamilyPathToUrl($path);
        if ($query = $request->getQueryString()) {
            $target .= '?'.$query;
        }

        return redirect()->to($target, 301);
    }

    private static function mapLegacyFamilyPathToUrl(string $path): string
    {
        $first = explode('/', $path, 2)[0] ?? '';
        $rest = str_contains($path, '/') ? substr($path, strpos($path, '/') + 1) : '';

        return match ($first) {
            'members' => '/family/'.$path,
            'invites' => '/family/invitation'.($rest !== '' ? '/'.$rest : ''),
            'transactions' => '/accounts/'.$path,
            'transfers' => '/accounts/'.$path,
            'accounts' => '/accounts/'.ltrim($rest, '/'),
            default => '/account/'.$path,
        };
    }
}
