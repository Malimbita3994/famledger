<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChangedIfRequired
{
    /**
     * Block app usage until users created with the configured default member password set a new password.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($this->isAllowedRoute($request)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => __('You must change your temporary password before continuing.'),
                'must_change_password' => true,
            ], 403);
        }

        return redirect()
            ->route('profile.edit')
            ->withFragment('password-section')
            ->with('warning', __('For security, please change your temporary password before using FamLedger.'));
    }

    private function isAllowedRoute(Request $request): bool
    {
        $name = $request->route()?->getName();

        $allowedNames = [
            'profile.edit',
            'profile.update',
            'password.update',
            'logout',
            'verification.notice',
            'verification.verify',
            'verification.send',
            'password.confirm',
        ];

        if ($name && in_array($name, $allowedNames, true)) {
            return true;
        }

        if ($request->is('confirm-password') && $request->isMethod('post')) {
            return true;
        }

        // API: current user + change password + logout only
        if ($request->is('api/user') && $request->isMethod('get')) {
            return true;
        }

        if ($request->is('api/user/password') && $request->isMethod('post')) {
            return true;
        }

        if ($request->is('api/logout') && $request->isMethod('post')) {
            return true;
        }

        return false;
    }
}
