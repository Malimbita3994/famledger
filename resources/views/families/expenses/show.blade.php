@extends('layouts.metronic')

@section('title', __('Expense details'))
@section('page_title', __('Expense details'))

@section('content')
@php
    $categoryLabel = $expense->category ? $expense->category->name : '—';
    if ($expense->subcategory) {
        $categoryLabel .= ' › '.$expense->subcategory;
    }
@endphp

<div class="pb-5">
    <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center flex-wrap gap-1 lg:gap-5 min-w-0">
            <h1 class="font-medium text-lg text-mono truncate">{{ __('Expense') }}</h1>
            <div class="flex items-center gap-1 text-sm font-normal">
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                <span class="text-muted-foreground text-sm">/</span>
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('families.expenses.index') }}">{{ __('Expenses') }}</a>
                <span class="text-muted-foreground text-sm">/</span>
                <span class="text-mono tabular-nums truncate">− {{ number_format((float) $expense->amount, 2) }} {{ $expense->currency_code }}</span>
            </div>
        </div>
    </div>
</div>

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-12">
    <style>
    @media (min-width: 1024px) {
        .expense-details-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
    }
    @media (min-width: 768px) and (max-width: 1023px) {
        .expense-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
    }
    @media (max-width: 767px) {
        .expense-details-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
            width: 100%;
        }
    }
    .expense-details-item {
        min-width: 0;
        box-sizing: border-box;
    }
    </style>

    <div class="kt-card mb-5 lg:mb-7.5 overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-stretch">
            <div class="shrink-0 min-w-0 p-5 sm:p-6 lg:p-8 flex gap-4 sm:gap-5 items-start">
                <div class="flex size-12 sm:size-14 shrink-0 items-center justify-center rounded-2xl bg-destructive/10 text-destructive ring-1 ring-inset ring-destructive/15">
                    <i class="ki-filled ki-arrow-up text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0 flex flex-col gap-2.5 sm:gap-3">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5">
                            <h2 class="text-xl sm:text-2xl font-semibold text-foreground tracking-tight">{{ $expense->description ?: ($expense->merchant ?: __('Expense')) }}</h2>
                            <span class="kt-badge kt-badge-destructive kt-badge-outline rounded-full shrink-0 text-[11px]">
                                <span class="kt-badge-dot size-1.5"></span>
                                {{ __('Recorded') }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ $expense->expense_date->format('M j, Y') }}
                            @if($expense->wallet)
                                · <a href="{{ route('families.wallets.show', $expense->wallet) }}" class="text-primary hover:underline">{{ $expense->wallet->name }}</a>
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
                <p class="text-2xl sm:text-3xl lg:text-[2.125rem] font-bold tabular-nums tracking-tight leading-none text-destructive">
                    − {{ number_format((float) $expense->amount, 2) }}
                    <span class="text-base sm:text-lg font-semibold text-muted-foreground ms-1.5 tabular-nums">{{ $expense->currency_code }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="kt-card max-w-4xl">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">{{ __('Details') }}</h3>
        </div>
        <div class="kt-card-content">
            <dl class="expense-details-grid text-sm">
                @if($expense->wallet)
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Wallet') }}</dt>
                    <dd class="text-foreground font-medium">
                        <a href="{{ route('families.wallets.show', $expense->wallet) }}" class="text-primary hover:underline">{{ $expense->wallet->name }}</a>
                    </dd>
                </div>
                @endif
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Category') }}</dt>
                    <dd class="text-foreground font-medium">{{ $categoryLabel }}</dd>
                </div>
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Currency') }}</dt>
                    <dd class="text-foreground">{{ $expense->currency_code }}</dd>
                </div>
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Date') }}</dt>
                    <dd class="text-foreground">{{ $expense->expense_date->format('M j, Y') }}</dd>
                </div>
                @if($expense->merchant)
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Merchant') }}</dt>
                    <dd class="text-foreground break-words">{{ $expense->merchant }}</dd>
                </div>
                @endif
                @if($expense->payment_method)
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Payment') }}</dt>
                    <dd class="text-foreground">{{ \App\Models\Expense::paymentMethods()[$expense->payment_method] ?? $expense->payment_method }}</dd>
                </div>
                @endif
                @if($expense->paidBy)
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Paid by') }}</dt>
                    <dd class="text-foreground">{{ $expense->paidBy->name }}</dd>
                </div>
                @endif
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Recorded by') }}</dt>
                    <dd class="text-foreground">{{ $expense->createdBy?->name ?? '—' }}</dd>
                </div>
                @if($expense->reference)
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Reference') }}</dt>
                    <dd class="text-foreground">{{ $expense->reference }}</dd>
                </div>
                @endif
                @if($expense->created_at)
                <div class="expense-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                    <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Created') }}</dt>
                    <dd class="text-foreground">{{ $expense->created_at->timezone(config('app.timezone'))->format('M j, Y') }}</dd>
                </div>
                @endif
            </dl>

            @if(filled($expense->description))
            <div class="mt-5 pt-4 border-t border-border">
                <h4 class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5">{{ __('Description') }}</h4>
                <p class="text-sm text-foreground leading-relaxed whitespace-pre-wrap">{{ $expense->description }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('families.expenses.index') }}" class="text-sm text-primary hover:underline">{{ __('← Back to expense list') }}</a>
    </div>
</div>
@endsection
