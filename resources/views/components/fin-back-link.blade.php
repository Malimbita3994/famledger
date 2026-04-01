@props([
    'href' => '#',
])

<a href="{{ $href }}" {{ $attributes->class(['fin-back-pill']) }}>
    <i class="ki-filled ki-left fin-back-pill__icon" aria-hidden="true"></i>
    <span>{{ $slot }}</span>
</a>
