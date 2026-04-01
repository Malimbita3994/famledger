{{-- Teal pill Export PDF — same as cash-flow / finance reports (kt-btn-primary + fin-kt-btn-accent). --}}
@props([
    'href' => '#',
])
<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'kt-btn kt-btn-primary fin-kt-btn-accent inline-flex shrink-0 items-center']) }}
>
    <i class="ki-filled ki-file-down" aria-hidden="true"></i>
    {{ __('Export PDF') }}
</a>
