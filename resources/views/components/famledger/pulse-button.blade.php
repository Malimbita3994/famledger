{{--
    FamLedger pulse action control — links and buttons share one look.

    <x-famledger.pulse-button variant="primary" href="{{ route('...') }}">Label</x-famledger.pulse-button>
    <x-famledger.pulse-button variant="outline" :href="route('x')"><i class="ki-filled ki-plus"></i> Add</x-famledger.pulse-button>
    <x-famledger.pulse-button variant="danger" size="sm" type="submit">Remove</x-famledger.pulse-button>

    variant: primary | outline | danger  (danger = outline + destructive styling)
    size: md | sm
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $variant = match ($variant) {
        'outline', 'secondary' => 'outline',
        'danger' => 'danger',
        default => 'primary',
    };
    $classes = match ($variant) {
        'primary' => 'fin-pulse-btn-primary',
        'outline' => 'fin-pulse-btn-outline',
        'danger' => 'fin-pulse-btn-outline fin-pulse-btn-outline-danger',
    };
    if ($size === 'sm') {
        $classes .= ' fin-pulse-btn-sm';
    }
    $buttonType = $attributes->get('type') ?? 'button';
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->except('type')->class($classes)->merge(['type' => $buttonType]) }}>{{ $slot }}</button>
@endif
