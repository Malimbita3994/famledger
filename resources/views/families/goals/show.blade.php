@extends('layouts.metronic')

@section('title', $goal->title.' — '.__('Vision Board'))
@section('page_title', $goal->title)

@section('content')
<style>
    /* Metronic bundle has no mb-* utilities; use flex gap for reliable vertical spacing */
    .goal-vb-stack {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .goal-vb-block-spacing {
        margin-bottom: 1.5rem;
    }
    .goal-vb-card-pad {
        padding: 1.5rem 1.75rem;
    }
    .goal-vb-card-pad--image {
        padding: 1.25rem 1.5rem;
    }
    .goal-vb-steps-head {
        padding: 1.25rem 1.5rem;
    }
    .goal-vb-steps-row {
        padding: 1rem 1.5rem;
    }
    .vision-goal-show-hero {
        position: relative;
        width: 100%;
        height: 14rem;
        max-height: 40vh;
        min-height: 10rem;
        overflow: hidden;
        border-radius: 0.5rem;
    }
    .vision-goal-show-hero__img {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    .vision-goal-show-hero__badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 1;
    }
</style>
@php
    $fallbackImage = 'https://images.unsplash.com/photo-1511895426328-dc8714191300?auto=format&fit=crop&q=80&w=1200';
@endphp

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <x-fin-back-link href="{{ route('families.goals.index') }}">
        {{ __('Back to vision board') }}
    </x-fin-back-link>

    @if (session('success'))
        <div class="goal-vb-block-spacing rounded-xl border border-green-200 bg-green-50 px-4 py-3 flex items-center gap-3 text-green-800">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4 goal-vb-block-spacing">
        <div>
            <h1 class="font-semibold text-lg text-mono">{{ $goal->title }}</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                {{ ucfirst($goal->status) }}
                @if($goal->category)
                    · {{ ucfirst($goal->category) }}
                @endif
                @if($goal->target_date)
                    · {{ __('Target:') }} {{ $goal->target_date->format('M j, Y') }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('families.goals.edit', $goal) }}" class="kt-btn kt-btn-primary">{{ __('Edit') }}</a>
        </div>
    </div>

    <div class="goal-vb-stack">
    <div class="kt-card rounded-xl border border-border shadow-sm bg-card goal-vb-card-pad--image">
        <div class="vision-goal-show-hero bg-muted">
            <img src="{{ $goal->resolved_image_url ?? $fallbackImage }}" alt="" class="vision-goal-show-hero__img" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ $fallbackImage }}'">
            @if($goal->status === 'completed')
                <span class="vision-goal-show-hero__badge kt-badge kt-badge-success kt-badge-outline rounded-full">{{ __('Completed') }}</span>
            @endif
        </div>
    </div>

    @if($goal->description)
        <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card goal-vb-card-pad">
            <p class="text-muted-foreground text-sm font-medium mb-1">{{ __('Description') }}</p>
            <p class="text-foreground text-sm whitespace-pre-wrap">{{ $goal->description }}</p>
        </div>
    @endif

    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card goal-vb-card-pad">
        <p class="text-muted-foreground text-sm font-medium mb-1">{{ __('Progress') }}</p>
        <p class="text-lg font-bold tabular-nums text-foreground mb-2">{{ (int) $goal->progress }}%</p>
        <div class="kt-progress h-2 {{ (int) $goal->progress >= 100 ? 'kt-progress-destructive' : 'kt-progress-primary' }}">
            <div class="kt-progress-indicator" style="width: {{ min(100, (int) $goal->progress) }}%"></div>
        </div>
    </div>

    @php
        $steps = is_array($goal->steps) ? $goal->steps : [];
    @endphp
    @if(count($steps))
        <div class="kt-card flex flex-col rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="goal-vb-steps-head border-b border-border">
                <h3 class="text-base font-semibold text-foreground">{{ __('Action steps') }}</h3>
            </div>
            <div class="divide-y divide-border">
                @foreach($steps as $step)
                    @php
                        $title = is_array($step) ? ($step['title'] ?? '') : (string) $step;
                        $done = is_array($step) && !empty($step['completed']);
                    @endphp
                    @if(trim($title) !== '')
                        <div class="goal-vb-steps-row flex items-start gap-3 text-sm">
                            @if($done)
                                <i class="ki-filled ki-check-circle text-success shrink-0 mt-0.5"></i>
                            @else
                                <i class="ki-filled ki-circle text-muted-foreground shrink-0 mt-0.5"></i>
                            @endif
                            <span class="{{ $done ? 'text-muted-foreground line-through' : 'text-foreground' }}">{{ $title }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
    </div>
</div>
@endsection
