<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Confirm Password') }} - {{ config('app.name') }}</x-slot>
    <x-slot name="brandedDescription">{{ __('This is a secure area. Please confirm your password to continue.') }}</x-slot>

    <div class="text-sm text-secondary-foreground mb-2">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="flex flex-col gap-5">
        @csrf

        <div class="text-center mb-2.5">
            <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                {{ __('Confirm Password') }}
            </h3>
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="password">{{ __('Password') }}</label>
            <div class="kt-input flex items-center gap-1 @error('password') border-danger @enderror" data-kt-toggle-password="true">
                <input id="password" name="password" class="flex-1 min-w-0 border-0 bg-transparent focus:ring-0 focus:outline-none p-0"
                       type="password" placeholder="{{ __('Enter Password') }}" required autocomplete="current-password"/>
                <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button" tabindex="-1">
                    <span class="kt-toggle-password-active:hidden"><i class="ki-filled ki-eye text-muted-foreground"></i></span>
                    <span class="hidden kt-toggle-password-active:block"><i class="ki-filled ki-eye-slash text-muted-foreground"></i></span>
                </button>
            </div>
            @error('password')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">
            {{ __('Confirm') }}
        </button>
    </form>
</x-guest-metronic-layout>
