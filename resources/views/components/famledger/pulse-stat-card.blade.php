{{-- Pulse-style statistics card.
     Usage:
       <x-famledger.pulse-stat-card label="Total Families" :value="$families->count()">
         <span>Optional extra line</span>
       </x-famledger.pulse-stat-card>
--}}
@props([
    'label' => '',
    'value' => '',
])

<div {{ $attributes->merge(['class' => 'famledger-pulse-stat-card']) }}>
    <div class="famledger-pulse-stat-card__label">{{ $label }}</div>
    <p class="famledger-pulse-stat-card__value">{{ $value }}</p>
    @if (trim($slot->toHtml()))
        <div class="famledger-pulse-stat-card__extra">
            {{ $slot }}
        </div>
    @endif
</div>

