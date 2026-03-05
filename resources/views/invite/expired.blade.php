<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Invitation expired') }} - {{ config('app.name') }}</x-slot>
    <x-slot name="brandedHeading">{{ __('Invitation expired or cancelled') }}</x-slot>
    <x-slot name="brandedDescription">
        {{ __('This invite link is no longer valid. Ask the family owner for a new invitation.') }}
    </x-slot>

    <div class="text-center mb-4">
        <h3 class="text-lg font-medium text-mono leading-none mb-2">
            {{ __('Invitation no longer valid') }}
        </h3>
        @if(isset($invitation) && $invitation->family)
            <p class="text-sm text-muted-foreground">
                {{ $invitation->family->name }}
            </p>
        @endif
    </div>

    <a href="{{ route('landing') }}" class="kt-btn kt-btn-outline w-full justify-center">
        {{ __('Back to home') }}
    </a>
</x-guest-metronic-layout>
