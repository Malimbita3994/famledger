<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Sign in') }} - {{ config('app.name') }}</x-slot>
    <x-slot name="brandedTitle">{{ __('Secure Access Portal') }}</x-slot>

    @if (session('status'))
        <div class="text-sm text-muted-foreground mb-2 p-2 rounded bg-muted/50">
            {{ session('status') }}
        </div>
    @endif

    <form x-data="{ loading: false }" x-on:submit="loading = true" method="POST" action="{{ route('login') }}" class="flex flex-col gap-5" id="sign_in_form">
        @csrf

        <div class="text-center mb-2.5">
            <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                {{ __('Sign in') }}
            </h3>
            <div class="flex items-center justify-center font-medium">
                <span class="text-sm text-secondary-foreground me-1.5">
                    {{ __('Need an account?') }}
                </span>
                <a class="text-sm kt-link" href="{{ route('register') }}">
                    {{ __('Sign up') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
            <a class="kt-btn kt-btn-outline justify-center" href="#" type="button">
                <img alt="" class="size-3.5 shrink-0" src="{{ asset('metronic/assets/media/brand-logos/google.svg') }}"/>
                {{ __('Use Google') }}
            </a>
            <a class="kt-btn kt-btn-outline justify-center" href="#" type="button">
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

        <button type="submit" class="kt-btn kt-btn-primary flex justify-center items-center gap-2 grow"
                x-bind:disabled="loading">
            <span x-show="!loading">{{ __('Sign In') }}</span>
            <div x-show="loading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
        </button>
    </form>
</x-guest-metronic-layout>
