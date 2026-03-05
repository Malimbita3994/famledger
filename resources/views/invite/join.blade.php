<x-guest-metronic-layout>
    <x-slot name="title">{{ $type === 'email' ? 'Invitation' : 'Join family' }} - {{ config('app.name') }}</x-slot>
    @php
        $brandedHeading = $type === 'email'
            ? __('You\'re invited to join :family', ['family' => $family->name])
            : __('Join :family', ['family' => $family->name]);
    @endphp
    <x-slot name="brandedHeading">{{ $brandedHeading }}</x-slot>
    <x-slot name="brandedDescription">
        @if($type === 'email' && isset($invitation))
            {{ __('You were invited as :role. Sign in or create an account to accept.', ['role' => $invitation->role?->name ?? 'member']) }}
        @else
            {{ __('Anyone with this link can join this family as a member. Sign in or create an account to continue.') }}
        @endif
    </x-slot>

    <div class="text-center mb-4">
        <h3 class="text-lg font-medium text-mono leading-none mb-2">
            {{ $type === 'email' ? __('Accept invitation') : __('Join family') }}
        </h3>
        <p class="text-sm text-muted-foreground">
            {{ $family->name }}
        </p>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm text-red-800 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    @auth
        <form action="{{ route('invite.accept') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <button type="submit" class="kt-btn kt-btn-primary w-full">
                <i class="ki-filled ki-check"></i>
                {{ $type === 'email' ? __('Accept invitation') : __('Join family') }}
            </button>
        </form>
    @else
        <div class="flex flex-col gap-3">
            <a href="{{ route('login') }}" class="kt-btn kt-btn-primary w-full justify-center">
                <i class="ki-filled ki-entrance-right"></i>
                {{ __('Sign in to accept') }}
            </a>
            <a href="{{ route('register') }}" class="kt-btn kt-btn-outline w-full justify-center">
                {{ __('Create an account') }}
            </a>
        </div>
    @endauth

    <p class="text-xs text-muted-foreground text-center mt-4">
        <a href="{{ route('landing') }}" class="hover:underline">{{ __('Back to home') }}</a>
    </p>
</x-guest-metronic-layout>
