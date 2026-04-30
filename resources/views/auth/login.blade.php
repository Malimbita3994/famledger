<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Sign in') }} - {{ config('app.name') }}</x-slot>

    @if (session('status'))
        <div class="text-sm text-slate-600 mb-3 px-3 py-2.5 rounded-xl bg-sky-50 border border-sky-100/80">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="text-sm text-danger mb-3 px-3 py-2.5 rounded-xl bg-red-50 border border-red-100/80" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form x-data="{ loading: false }" x-on:submit="loading = true" method="POST" action="{{ route('login') }}" class="flex flex-col gap-4 sm:gap-5" id="sign_in_form">
        @csrf

        <div class="text-center mb-1">
            <h3 class="font-semibold leading-none mb-2">
                {{ __('Sign in') }}
            </h3>
            <p class="text-xs text-slate-500 font-medium tracking-wide uppercase opacity-90 mb-0">
                {{ __('Secure Access Portal') }}
            </p>
            <div class="flex items-center justify-center font-medium mt-3">
                <span class="text-sm text-secondary-foreground me-1.5">
                    {{ __('Need an account?') }}
                </span>
                <a class="text-sm kt-link" href="{{ route('register') }}">
                    {{ __('Sign up') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
            <a class="kt-btn kt-btn-outline justify-center" href="{{ route('auth.google.redirect') }}">
                <img alt="" class="size-3.5 shrink-0" src="{{ asset('metronic/assets/media/brand-logos/google.svg') }}"/>
                {{ __('Use Google') }}
            </a>
            <a class="kt-btn kt-btn-outline justify-center" href="{{ route('auth.apple.redirect') }}">
                <img alt="" class="size-3.5 shrink-0 dark:hidden" src="{{ asset('metronic/assets/media/brand-logos/apple-black.svg') }}"/>
                <img alt="" class="size-3.5 shrink-0 hidden dark:block" src="{{ asset('metronic/assets/media/brand-logos/apple-white.svg') }}"/>
                {{ __('Use Apple') }}
            </a>
        </div>

        <div class="flex items-center gap-2">
            <span class="border-t border-border w-full"></span>
            <span class="text-xs text-muted-foreground font-medium uppercase">{{ __('Or') }}</span>
            <span class="border-t border-border w-full"></span>
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="email">{{ __('Email') }}</label>
            <input id="email" class="kt-input auth-text-input @error('email') border-danger @enderror" type="email" name="email"
                   placeholder="email@email.com" value="{{ old('email') }}" required autofocus autocomplete="username"/>
            @error('email')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex flex-col gap-1">
            <div class="flex items-center justify-between gap-1">
                <label class="kt-form-label font-normal text-mono" for="password">{{ __('Password') }}</label>
                @if (Route::has('password.request'))
                    <a class="text-sm kt-link shrink-0" href="{{ route('password.request') }}">
                        {{ __('Forgot Password?') }}
                    </a>
                @endif
            </div>
            <div class="kt-input flex items-center gap-1 @error('password') border-danger @enderror auth-text-input" data-kt-toggle-password="true">
                <input id="password" name="password" class="flex-1 min-w-0 border-0 bg-transparent focus:ring-0 focus:outline-none p-0"
                       type="password" placeholder="{{ __('Enter Password') }}" required autocomplete="current-password"/>
                <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button" tabindex="-1">
                    <span class="kt-toggle-password-active:hidden">
                        <i class="ki-filled ki-eye text-muted-foreground"></i>
                    </span>
                    <span class="hidden kt-toggle-password-active:block">
                        <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                    </span>
                </button>
            </div>
            @error('password')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <label class="kt-label">
            <input id="remember_me" class="kt-checkbox kt-checkbox-sm" type="checkbox" name="remember" value="1"/>
            <span class="kt-checkbox-label">{{ __('Remember me') }}</span>
        </label>

        {{-- Do not disable the submit button on submit: disabling during the submit event can cancel the POST in some browsers. --}}
        <button type="submit" class="kt-btn kt-btn-primary"
                x-bind:aria-busy="loading">
            <span x-show="!loading" class="inline-flex items-center">{{ __('Sign In') }}</span>
            <span x-show="loading" class="inline-flex items-center justify-center" aria-hidden="true">
                <svg class="auth-pulse-btn-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        </button>
    </form>

    <style>
        .auth-hide-branding-toggle-wrap {
            margin-top: 1.75rem;
        }
        .auth-hide-branding-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.48rem 0.8rem;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            background: rgba(255, 255, 255, 0.85);
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .auth-hide-branding-toggle:hover {
            color: #0369a1;
            border-color: rgba(3, 105, 161, 0.35);
            background: rgba(14, 165, 233, 0.08);
            transform: translateY(-1px);
        }
        .auth-hide-branding-toggle:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.22);
        }
        .auth-hide-branding-toggle__icon {
            font-size: 0.95rem;
            line-height: 1;
        }
    </style>

    <p class="auth-hide-branding-toggle-wrap text-center mb-0">
        <button
            type="button"
            class="auth-hide-branding-toggle"
            data-label-hide="{{ __('Hide marketing panel') }}"
            data-label-show="{{ __('Show marketing panel') }}"
            aria-pressed="false"
        >
            <i class="ki-filled ki-eye-slash auth-hide-branding-toggle__icon" aria-hidden="true"></i>
            <span class="auth-hide-branding-toggle__label"></span>
        </button>
    </p>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.querySelector('.auth-hide-branding-toggle');
            if (!btn) {
                return;
            }
            function sync() {
                var hidden = document.documentElement.classList.contains('auth-hide-branded');
                var label = btn.querySelector('.auth-hide-branding-toggle__label');
                if (label) {
                    label.textContent = hidden ? btn.getAttribute('data-label-show') : btn.getAttribute('data-label-hide');
                }
                btn.setAttribute('aria-pressed', hidden ? 'true' : 'false');
            }
            btn.addEventListener('click', function () {
                document.documentElement.classList.toggle('auth-hide-branded');
                try {
                    localStorage.setItem(
                        'famledger_auth_hide_branding',
                        document.documentElement.classList.contains('auth-hide-branded') ? '1' : '0'
                    );
                } catch (e) {}
                sync();
            });
            sync();
        });
    </script>
</x-guest-metronic-layout>
