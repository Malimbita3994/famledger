@props([
    'size' => 'md', // md = list row, lg = empty state hero
])

@php
    $famledgerMarkSrc = is_file(public_path('images/logo.png'))
        ? asset('images/logo.png')
        : asset('metronic/assets/media/app/logo-32.svg');
    $wrap = match ($size) {
        'lg' => 'size-16',
        default => 'size-9',
    };
    $img = match ($size) {
        'lg' => 'size-12',
        default => 'size-7',
    };
@endphp
<span {{ $attributes->class('inline-flex items-center justify-center shrink-0 overflow-hidden rounded-full bg-gradient-to-br from-sky-50 to-cyan-50 ring-1 ring-primary/25 shadow-sm dark:from-sky-950/55 dark:to-cyan-950/45 dark:ring-primary/35 '.$wrap) }}>
    <img src="{{ $famledgerMarkSrc }}" alt="" loading="lazy" decoding="async" class="object-contain {{ $img }}" />
</span>
