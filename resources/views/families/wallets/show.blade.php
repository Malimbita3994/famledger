@extends('layouts.metronic')

@section('title', $wallet->name)
@section('page_title', $wallet->name)

@section('content')
<div class="pb-5">
    <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center flex-wrap gap-1 lg:gap-5">
            <h1 class="font-medium text-lg text-mono">{{ $wallet->name }}</h1>
            <div class="flex items-center gap-1 text-sm font-normal">
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Home</a>
                <span class="text-muted-foreground text-sm">/</span>
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('families.wallets.index', $family) }}">Wallets</a>
                <span class="text-muted-foreground text-sm">/</span>
                <span class="text-mono">{{ $wallet->name }}</span>
            </div>
        </div>
        <div class="flex items-center flex-wrap gap-1.5 lg:gap-3.5">
            @if($wallet->is_primary)
            <a href="{{ route('families.incomes.create', $family) }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-arrow-down"></i>
                Record income
            </a>
            @else
            <span class="text-xs text-muted-foreground self-center max-w-[200px]">
                Income is recorded to the main wallet only. Use <a href="{{ route('families.transfers.create', $family) }}" class="text-primary hover:underline">Transfer</a> to move money here.
            </span>
            @endif
            <a href="{{ route('families.expenses.create', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-arrow-up"></i>
                Record expense
            </a>
            <a href="{{ route('families.wallets.edit', [$family, $wallet]) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-pencil"></i>
                Edit
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed">
    <style>
    @media (min-width: 1024px) {
        .wallet-details-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
    }
    @media (min-width: 768px) and (max-width: 1023px) {
        .wallet-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
    }
    @media (max-width: 767px) {
        .wallet-details-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
            width: 100%;
        }
    }
    .wallet-details-item {
        min-width: 0;
        box-sizing: border-box;
    }
    </style>

    {{-- Summary: meta sizes to content; balance sits in a growing tint so no dead center gap --}}
    <div class="kt-card mb-5 lg:mb-7.5 overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-stretch">
            <div class="shrink-0 min-w-0 p-5 sm:p-6 lg:p-8 flex gap-4 sm:gap-5 items-start">
                <div class="flex size-12 sm:size-14 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-inset ring-primary/15">
                    <i class="ki-filled ki-wallet text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0 flex flex-col gap-2.5 sm:gap-3">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5">
                            <h2 class="text-xl sm:text-2xl font-semibold text-foreground tracking-tight">{{ $wallet->name }}</h2>
                            <span class="kt-badge {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-full shrink-0 text-[11px]">
                                <span class="kt-badge-dot size-1.5"></span>
                                {{ ucfirst($wallet->status) }}
                            </span>
                        </div>
                        @if ($wallet->is_primary)
                            <p class="text-xs text-muted-foreground">Default wallet for family income and primary balances.</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-x-2 gap-y-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-2.5 py-1 text-xs font-medium text-foreground shadow-sm">
                            <i class="ki-filled ki-chart-simple text-muted-foreground text-[13px]"></i>
                            {{ $walletTypes[$wallet->type] ?? $wallet->type }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-2.5 py-1 text-xs font-medium text-foreground shadow-sm">
                            <i class="ki-filled ki-people text-muted-foreground text-[13px]"></i>
                            {{ $wallet->is_shared ? 'Shared · family' : 'Personal' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-0 border-t lg:border-t-0 lg:border-s border-border bg-muted/30 dark:bg-muted/15 px-5 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-7 flex flex-col justify-center lg:items-end lg:text-end">
                <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-[0.12em] text-muted-foreground mb-1.5">Current balance</p>
                <p class="text-2xl sm:text-3xl lg:text-[2.125rem] font-bold tabular-nums tracking-tight leading-none {{ (float) $wallet->balance < 0 ? 'text-destructive' : 'text-foreground' }}">
                    {{ number_format($wallet->balance, 2) }}
                    <span class="text-base sm:text-lg font-semibold text-muted-foreground ms-1.5 tabular-nums">{{ $wallet->currency_code }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-5 lg:gap-7.5 lg:grid-cols-3">
        {{-- Details card --}}
        <div class="lg:col-span-1">
            <div class="kt-card h-full">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Details</h3>
                </div>
                <div class="kt-card-content">
                    <dl class="wallet-details-grid text-sm">
                        <div class="wallet-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Type</dt>
                            <dd class="text-foreground font-medium">{{ $walletTypes[$wallet->type] ?? $wallet->type }}</dd>
                        </div>
                        <div class="wallet-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Currency</dt>
                            <dd class="text-foreground">{{ $wallet->currency_code }}</dd>
                        </div>
                        <div class="wallet-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Shared</dt>
                            <dd class="text-foreground">{{ $wallet->is_shared ? 'Yes — all members can use' : 'No — personal wallet' }}</dd>
                        </div>
                        <div class="wallet-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Status</dt>
                            <dd>
                                <span class="kt-badge kt-badge-sm {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px]">
                                    <span class="kt-badge-dot size-1.5"></span>
                                    {{ ucfirst($wallet->status) }}
                                </span>
                            </dd>
                        </div>
                        @if ($wallet->creator)
                        <div class="wallet-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Created by</dt>
                            <dd class="text-foreground">{{ $wallet->creator->name }}</dd>
                        </div>
                        @endif
                        @if ($wallet->created_at)
                        <div class="wallet-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Created</dt>
                            <dd class="text-foreground">{{ $wallet->created_at->format('M j, Y') }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if ($wallet->description)
                        <div class="mt-5 pt-4 border-t border-border">
                            <h4 class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5">Description</h4>
                            <p class="text-sm text-foreground leading-relaxed">
                                {{ $wallet->description }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Activity + transfers --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="kt-card">
                <div class="kt-card-header flex-wrap gap-2">
                    <h3 class="kt-card-title text-sm">Recent activity</h3>
                    <div class="flex items-center gap-2 ml-auto text-xs">
                        <a href="{{ route('families.incomes.index', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-xs kt-btn-ghost">All income</a>
                        <a href="{{ route('families.expenses.index', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-xs kt-btn-ghost">All expenses</a>
                    </div>
                </div>
                <div class="kt-card-content space-y-6">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex size-7 items-center justify-center rounded-md bg-success/10 text-success shrink-0">
                                <i class="ki-filled ki-arrow-down text-sm"></i>
                            </span>
                            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Income</p>
                        </div>
                        @if($recentIncomes->isEmpty())
                            <p class="text-xs text-muted-foreground py-2">No income recorded for this wallet yet.</p>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($recentIncomes as $income)
                                    <div class="rounded-lg border border-border bg-muted/20 dark:bg-muted/10 px-3 py-2.5 min-w-0 flex flex-col gap-1">
                                        <span class="tabular-nums text-sm font-semibold text-success leading-tight truncate" title="+ {{ number_format($income->amount, 2) }} {{ $income->currency_code }}">
                                            +{{ number_format($income->amount, 2) }} <span class="text-xs font-medium text-success/80">{{ $income->currency_code }}</span>
                                        </span>
                                        <span class="text-[11px] text-muted-foreground leading-snug line-clamp-2">
                                            {{ $income->received_date?->format('M j, Y') }} · {{ $income->category->name ?? 'Income' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex size-7 items-center justify-center rounded-md bg-destructive/10 text-destructive shrink-0">
                                <i class="ki-filled ki-arrow-up text-sm"></i>
                            </span>
                            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Expenses</p>
                        </div>
                        @if($recentExpenses->isEmpty())
                            <p class="text-xs text-muted-foreground py-2">No expenses recorded for this wallet yet.</p>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($recentExpenses as $expense)
                                    <div class="rounded-lg border border-border bg-muted/20 dark:bg-muted/10 px-3 py-2.5 min-w-0 flex flex-col gap-1">
                                        <span class="tabular-nums text-sm font-semibold text-destructive leading-tight truncate" title="− {{ number_format($expense->amount, 2) }} {{ $expense->currency_code }}">
                                            −{{ number_format($expense->amount, 2) }} <span class="text-xs font-medium text-destructive/80">{{ $expense->currency_code }}</span>
                                        </span>
                                        <span class="text-[11px] text-muted-foreground leading-snug line-clamp-2">
                                            {{ $expense->expense_date?->format('M j, Y') }} · {{ $expense->category->name ?? 'Expense' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($outgoingTransfers->isNotEmpty() || $incomingTransfers->isNotEmpty())
            <div class="kt-card">
                <div class="kt-card-header flex-wrap gap-2">
                    <h3 class="kt-card-title text-sm">Transfers</h3>
                    <a href="{{ route('families.transfers.index', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-sm kt-btn-ghost">View all</a>
                </div>
                <div class="kt-card-content">
                    @if ($outgoingTransfers->isNotEmpty())
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">Outgoing</p>
                    <ul class="space-y-1.5 mb-4">
                        @foreach ($outgoingTransfers as $t)
                        <li class="flex items-center justify-between text-sm">
                            <span>To <a href="{{ route('families.wallets.show', [$family, $t->toWallet]) }}" class="text-primary hover:underline">{{ $t->toWallet->name }}</a></span>
                            <span class="tabular-nums text-destructive">− {{ number_format($t->amount, 2) }} {{ $t->currency_code }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                    @if ($incomingTransfers->isNotEmpty())
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">Incoming</p>
                    <ul class="space-y-1.5">
                        @foreach ($incomingTransfers as $t)
                        <li class="flex items-center justify-between text-sm">
                            <span>From <a href="{{ route('families.wallets.show', [$family, $t->fromWallet]) }}" class="text-primary hover:underline">{{ $t->fromWallet->name }}</a></span>
                            <span class="tabular-nums text-success">+ {{ number_format($t->amount, 2) }} {{ $t->currency_code }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
