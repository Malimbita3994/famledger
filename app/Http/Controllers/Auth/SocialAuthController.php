<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Response;

class SocialAuthController extends Controller
{
    public function redirectGoogle(): Response|RedirectResponse
    {
        if (! $this->googleConfigured()) {
            return $this->missingConfigRedirect();
        }

        return Socialite::driver('google')->redirect();
    }

    public function redirectApple(): Response|RedirectResponse
    {
        if (! $this->appleConfigured()) {
            return $this->missingConfigRedirect();
        }

        return Socialite::driver('apple')->redirect();
    }

    public function callbackGoogle(Request $request): RedirectResponse
    {
        if (! $this->googleConfigured()) {
            return $this->missingConfigRedirect();
        }

        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->with('error', __('Google sign-in session expired. Please try again.'));
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->with('error', __('Google sign-in failed. Please try again.'));
        }

        return $this->completeSocialLogin($request, 'google', $socialUser);
    }

    public function callbackApple(Request $request): RedirectResponse
    {
        if (! $this->appleConfigured()) {
            return $this->missingConfigRedirect();
        }

        try {
            $socialUser = Socialite::driver('apple')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->with('error', __('Apple sign-in session expired. Please try again.'));
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->with('error', __('Apple sign-in failed. Please try again.'));
        }

        return $this->completeSocialLogin($request, 'apple', $socialUser);
    }

    private function completeSocialLogin(Request $request, string $provider, SocialiteUserContract $socialUser): RedirectResponse
    {
        $providerId = (string) $socialUser->getId();
        $email = $socialUser->getEmail() ? strtolower(trim((string) $socialUser->getEmail())) : null;

        $name = $socialUser->getName() ?: ($email ? strstr($email, '@', true) : null);
        if (! $name) {
            $name = 'User';
        }

        $account = SocialAccount::query()->where('provider', $provider)->where('provider_id', $providerId)->first();
        if ($account) {
            $user = $account->user;
            if (! $this->userMayLogin($user)) {
                return $this->blockedRedirect();
            }

            return $this->loginAndRedirect($request, $user, false);
        }

        if ($email) {
            $user = User::query()->where('email', $email)->first();
            if ($user) {
                if (! $this->userMayLogin($user)) {
                    return $this->blockedRedirect();
                }

                SocialAccount::query()->firstOrCreate(
                    [
                        'provider' => $provider,
                        'provider_id' => $providerId,
                    ],
                    [
                        'user_id' => $user->id,
                    ]
                );

                return $this->loginAndRedirect($request, $user, false);
            }
        }

        if (! $email) {
            return redirect()->route('login')->with('error', __('Sign-in with Apple did not provide an email. Please use email and password, or try again.'));
        }

        return DB::transaction(function () use ($request, $provider, $providerId, $email, $name, $socialUser) {
            $user = new User([
                'name' => $name,
                'email' => $email,
                'password' => null,
                'status' => User::STATUS_ACTIVE,
            ]);
            $user->email_verified_at = now();
            $user->save();

            SocialAccount::query()->create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $providerId,
            ]);

            $avatar = $socialUser->getAvatar();
            if (is_string($avatar) && $avatar !== '') {
                $user->avatar = $avatar;
                $user->save();
            }

            event(new Registered($user));

            return $this->loginAndRedirect($request, $user, true);
        });
    }

    private function userMayLogin(User $user): bool
    {
        return ! in_array($user->status, [User::STATUS_SUSPENDED, User::STATUS_LOCKED], true);
    }

    private function blockedRedirect(): RedirectResponse
    {
        return redirect()->route('login')->with('error', __('This account is not active. Please contact support.'));
    }

    private function loginAndRedirect(Request $request, User $user, bool $isNew): RedirectResponse
    {
        Auth::login($user, true);
        $request->session()->regenerate();

        $primaryFamily = $user->families()->first();
        if ($primaryFamily) {
            $request->session()->put('current_family_id', $primaryFamily->id);
        }

        try {
            AuditLogger::application(AuditLog::ACTION_LOGIN, 'Signed in to FamLedger', [
                'email' => $user->email,
                'via' => 'social',
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        $msg = $isNew ? __('Welcome! Your account is ready.') : __('Welcome back!');

        return redirect()
            ->intended(route('dashboard', absolute: false))
            ->with('success', $msg);
    }

    private function missingConfigRedirect(): RedirectResponse
    {
        return redirect()->route('login')->with('error', __('This sign-in method is not configured yet.'));
    }

    private function googleConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    private function appleConfigured(): bool
    {
        if (! filled(config('services.apple.client_id'))) {
            return false;
        }

        if (filled(config('services.apple.client_secret'))) {
            return true;
        }

        return filled(config('services.apple.private_key'))
            && filled(config('services.apple.team_id'))
            && filled(config('services.apple.key_id'));
    }
}
