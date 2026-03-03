<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Register') }} - {{ config('app.name') }}</x-slot>
    <x-slot name="brandedDescription">{{ __('Create your account for secure access to your family ledger.') }}</x-slot>

    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-5">
        @csrf

        <div class="text-center mb-2.5">
            <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                {{ __('Sign up') }}
            </h3>
            <div class="flex items-center justify-center font-medium">
                <span class="text-sm text-secondary-foreground me-1.5">
                    {{ __('Already have an account?') }}
                </span>
                <a class="text-sm kt-link" href="{{ route('login') }}">
                    {{ __('Sign in') }}
                </a>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="border-t border-border w-full"></span>
            <span class="text-xs text-muted-foreground font-medium uppercase">{{ __('Or') }}</span>
            <span class="border-t border-border w-full"></span>
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="name">{{ __('Name') }}</label>
            <input id="name" class="kt-input @error('name') border-danger @enderror" type="text" name="name"
                   value="{{ old('name') }}" required autofocus autocomplete="name"/>
            @error('name')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="email">{{ __('Email') }}</label>
            <input id="email" class="kt-input @error('email') border-danger @enderror" type="email" name="email"
                   placeholder="email@email.com" value="{{ old('email') }}" required autocomplete="username"/>
            @error('email')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="password">{{ __('Password') }}</label>
            <div class="kt-input flex items-center gap-1 @error('password') border-danger @enderror" data-kt-toggle-password="true">
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
            <div class="kt-input flex items-center gap-1" data-kt-toggle-password="true">
                <input id="password_confirmation" name="password_confirmation" class="flex-1 min-w-0 border-0 bg-transparent focus:ring-0 focus:outline-none p-0"
                       type="password" placeholder="{{ __('Confirm Password') }}" required autocomplete="new-password"/>
                <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button" tabindex="-1">
                    <span class="kt-toggle-password-active:hidden"><i class="ki-filled ki-eye text-muted-foreground"></i></span>
                    <span class="hidden kt-toggle-password-active:block"><i class="ki-filled ki-eye-slash text-muted-foreground"></i></span>
                </button>
            </div>
            @error('password_confirmation')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">
            {{ __('Register') }}
        </button>
    </form>
</x-guest-metronic-layout>
