<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Reset Password') }} - {{ config('app.name') }}</x-slot>
    <x-slot name="brandedDescription">{{ __('Choose a new password for your account.') }}</x-slot>

    <form x-data="{ loading: false }" x-on:submit="loading = true" method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="text-center mb-2.5">
            <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                {{ __('Reset Password') }}
            </h3>
            <a class="text-sm kt-link" href="{{ route('login') }}">
                {{ __('Back to sign in') }}
            </a>
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="email">{{ __('Email') }}</label>
            <input id="email" class="kt-input auth-text-input @error('email') border-danger @enderror" type="email" name="email"
                   placeholder="email@email.com" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"/>
            @error('email')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="password">{{ __('Password') }}</label>
            <div class="kt-input flex items-center gap-1 @error('password') border-danger @enderror auth-text-input" data-kt-toggle-password="true">
                <input id="password" name="password" class="flex-1 min-w-0 border-0 bg-transparent focus:ring-0 focus:outline-none p-0"
                       type="password" placeholder="{{ __('Enter Password') }}" required autocomplete="new-password"/>
                <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button" tabindex="-1">
                    <span class="kt-toggle-password-active:hidden"><i class="ki-filled ki-eye text-muted-foreground"></i></span>
                    <span class="hidden kt-toggle-password-active:block"><i class="ki-filled ki-eye-slash text-muted-foreground"></i></span>
                </button>
            </div>
            @error('password')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="password_confirmation">{{ __('Confirm Password') }}</label>
            <div class="kt-input flex items-center gap-1 auth-text-input">
                <input id="password_confirmation" name="password_confirmation" class="flex-1 min-w-0 border-0 bg-transparent focus:ring-0 focus:outline-none p-0"
                       type="password" placeholder="{{ __('Confirm Password') }}" required autocomplete="new-password"/>
            </div>
            @error('password_confirmation')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="kt-btn kt-btn-primary flex justify-center items-center gap-2 grow"
                x-bind:disabled="loading">
            <span x-show="!loading">{{ __('Reset Password') }}</span>
            <div x-show="loading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
        </button>
    </form>
</x-guest-metronic-layout>
