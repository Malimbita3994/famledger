<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Verify Email') }} - {{ config('app.name') }}</x-slot>
    <x-slot name="brandedDescription">{{ __('We sent a verification link to your email.') }}</x-slot>

    <div class="text-sm text-secondary-foreground mb-4">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-success p-2 rounded bg-success/10">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="kt-btn kt-btn-primary w-full justify-center">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="kt-btn kt-btn-ghost w-full justify-center text-muted-foreground">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-metronic-layout>
