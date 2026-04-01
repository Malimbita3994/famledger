@extends('layouts.metronic')

@section('title', __('Income details'))
@section('page_title', __('Income details'))

@section('content')
@php
    $categoryLabel = $income->category
        ? ($income->category->parent ? $income->category->parent->name.' › '.$income->category->name : $income->category->name)
        : '—';
    $sourceTypeLabel = $income->source_entity_type
        ? (\App\Models\Income::sourceEntityTypes()[$income->source_entity_type] ?? $income->source_entity_type)
        : '—';
    $recurringLabel = $income->is_recurring
        ? (\App\Models\Income::recurringFrequencies()[$income->recurring_frequency] ?? $income->recurring_frequency ?? '—')
        : __('No');
@endphp

<div class="pb-5">
    <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center flex-wrap gap-1 lg:gap-5 min-w-0">
            <h1 class="font-medium text-lg text-mono truncate">{{ __('Income') }}</h1>
            <div class="flex items-center gap-1 text-sm font-normal">
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                <span class="text-muted-foreground text-sm">/</span>
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('families.incomes.index') }}">{{ __('Income') }}</a>
                <span class="text-muted-foreground text-sm">/</span>
                <span class="text-mono tabular-nums truncate">+ {{ number_format((float) $income->amount, 2) }} {{ $income->currency_code }}</span>
            </div>
        </div>
        <div class="flex items-center flex-wrap gap-1.5 lg:gap-3.5 shrink-0">
            <a href="{{ route('families.incomes.edit', $income) }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-pencil"></i>
                {{ __('Edit') }}
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-12">
    <style>
    @media (min-width: 1024px) {
        .income-details-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
    }
    @media (min-width: 768px) and (max-width: 1023px) {
        .income-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
    }
    @media (max-width: 767px) {
        .income-details-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
            width: 100%;
        }
    }
    .income-details-item {
        min-width: 0;
        box-sizing: border-box;
    }
    </style>

    {{-- Summary hero (aligned with wallet show) --}}
    <div class="kt-card mb-5 lg:mb-7.5 overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-stretch">
            <div class="shrink-0 min-w-0 p-5 sm:p-6 lg:p-8 flex gap-4 sm:gap-5 items-start">
                <div class="flex size-12 sm:size-14 shrink-0 items-center justify-center rounded-2xl bg-success/10 text-success ring-1 ring-inset ring-success/15">
                    <i class="ki-filled ki-arrow-down text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0 flex flex-col gap-2.5 sm:gap-3">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5">
                            <h2 class="text-xl sm:text-2xl font-semibold text-foreground tracking-tight">{{ $income->source ?: __('Income') }}</h2>
                            <span class="kt-badge kt-badge-success kt-badge-outline rounded-full shrink-0 text-[11px]">
                                <span class="kt-badge-dot size-1.5"></span>
                                {{ __('Recorded') }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ $income->received_date->format('M j, Y') }}
                            @if($income->wallet)
                                · <a href="{{ route('families.wallets.show', $income->wallet) }}" class="text-primary hover:underline">{{ $income->wallet->name }}</a>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-x-2 gap-y-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-2.5 py-1 text-xs font-medium text-foreground shadow-sm">
                            <i class="ki-filled ki-category text-muted-foreground text-[13px]"></i>
                            {{ Str::limit($categoryLabel, 42) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-0 border-t lg:border-t-0 lg:border-s border-border bg-muted/30 dark:bg-muted/15 px-5 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-7 flex flex-col justify-center lg:items-end lg:text-end">
                <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-[0.12em] text-muted-foreground mb-1.5">{{ __('Amount') }}</p>
                <p class="text-2xl sm:text-3xl lg:text-[2.125rem] font-bold tabular-nums tracking-tight leading-none text-success">
                    + {{ number_format((float) $income->amount, 2) }}
                    <span class="text-base sm:text-lg font-semibold text-muted-foreground ms-1.5 tabular-nums">{{ $income->currency_code }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Details card (same structure as wallet Details) --}}
    <div class="kt-card max-w-4xl">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">{{ __('Details') }}</h3>
        </div>
        <div class="kt-card-content">
            <dl class="income-details-grid text-sm">
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Wallet') }}</dt>
                    <dd class="text-foreground font-medium">
                        <a href="{{ route('families.wallets.show', $income->wallet) }}" class="text-primary hover:underline">{{ $income->wallet->name }}</a>
                    </dd>
                </div>
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Category') }}</dt>
                    <dd class="text-foreground font-medium">{{ $categoryLabel }}</dd>
                </div>
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Currency') }}</dt>
                    <dd class="text-foreground">{{ $income->currency_code }}</dd>
                </div>
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Received') }}</dt>
                    <dd class="text-foreground">{{ $income->received_date->format('M j, Y') }}</dd>
                </div>
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Source') }}</dt>
                    <dd class="text-foreground break-words">{{ $income->source ?? '—' }}</dd>
                </div>
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Source type') }}</dt>
                    <dd class="text-foreground">{{ $sourceTypeLabel }}</dd>
                </div>
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Recorded by') }}</dt>
                    <dd class="text-foreground">{{ $income->createdBy?->name ?? '—' }}</dd>
                </div>
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Recurring') }}</dt>
                    <dd class="text-foreground">{{ $recurringLabel }}</dd>
                </div>
                @if($income->linkedProject)
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Project') }}</dt>
                    <dd class="text-foreground font-medium">
                        <a href="{{ route('families.projects.show', $income->linkedProject) }}" class="text-primary hover:underline">{{ $income->linkedProject->name }}</a>
                    </dd>
                </div>
                @endif
                @if($income->linkedProperty)
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Property') }}</dt>
                    <dd class="text-foreground">{{ $income->linkedProperty->name }}</dd>
                </div>
                @endif
                @if($income->familyLiability)
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Liability') }}</dt>
                    <dd class="text-foreground">{{ $income->familyLiability->name }}</dd>
                </div>
                @endif
                @if($income->created_at)
                <div class="income-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Created') }}</dt>
                    <dd class="text-foreground">{{ $income->created_at->timezone(config('app.timezone'))->format('M j, Y') }}</dd>
                </div>
                @endif
            </dl>

            @if(filled($income->notes))
            <div class="mt-5 pt-4 border-t border-border">
                <h4 class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5">{{ __('Notes') }}</h4>
                <p class="text-sm text-foreground leading-relaxed whitespace-pre-wrap">{{ $income->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('families.incomes.index') }}" class="text-sm text-primary hover:underline">{{ __('← Back to income list') }}</a>
    </div>
</div>
@endsection
