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
            <a href="{{ route('families.incomes.create', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-arrow-down"></i>
                Record income
            </a>
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

    {{-- Summary / hero --}}
    <div class="kt-card mb-5 lg:mb-7.5">
        <div class="kt-card-content py-6 lg:py-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 class="text-xl lg:text-2xl font-semibold text-foreground truncate">{{ $wallet->name }}</h2>
                        <span class="kt-badge {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px] shrink-0">
                            <span class="kt-badge-dot size-1.5"></span>
                            {{ ucfirst($wallet->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ $walletTypes[$wallet->type] ?? $wallet->type }} · {{ $wallet->currency_code }}
                        @if($wallet->is_shared)
                            · Shared with family
                        @else
                            · Personal wallet
                        @endif
                    </p>
                    @if ($wallet->description)
                        <p class="text-secondary-foreground text-sm leading-relaxed max-w-2xl mt-2">{{ Str::limit($wallet->description, 160) }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm text-muted-foreground">Current balance</p>
                    <p class="text-2xl lg:text-3xl font-semibold tabular-nums">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency_code }}</p>
                </div>
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
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex items-center justify-center size-10 rounded-full bg-muted text-muted-foreground shrink-0">
                            <i class="ki-filled ki-wallet text-lg"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground truncate">
                                {{ $wallet->name }}
                            </div>
                            <div class="text-xs text-muted-foreground mt-0.5 truncate">
                                {{ $walletTypes[$wallet->type] ?? $wallet->type }} · {{ $wallet->currency_code }}
                            </div>
                        </div>
                    </div>

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
                <div class="kt-card-content grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">Income</p>
                        @if($recentIncomes->isEmpty())
                            <p class="text-xs text-muted-foreground">No income recorded for this wallet yet.</p>
                        @else
                            <ul class="space-y-1.5">
                                @foreach($recentIncomes as $income)
                                    <li class="flex items-center justify-between text-sm">
                                        <div class="flex flex-col">
                                            <span class="tabular-nums font-medium text-success">+ {{ number_format($income->amount, 2) }} {{ $income->currency_code }}</span>
                                            <span class="text-xs text-muted-foreground">
                                                {{ $income->received_date?->format('M j, Y') }} · {{ $income->category->name ?? 'Income' }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">Expenses</p>
                        @if($recentExpenses->isEmpty())
                            <p class="text-xs text-muted-foreground">No expenses recorded for this wallet yet.</p>
                        @else
                            <ul class="space-y-1.5">
                                @foreach($recentExpenses as $expense)
                                    <li class="flex items-center justify-between text-sm">
                                        <div class="flex flex-col">
                                            <span class="tabular-nums font-medium text-destructive">− {{ number_format($expense->amount, 2) }} {{ $expense->currency_code }}</span>
                                            <span class="text-xs text-muted-foreground">
                                                {{ $expense->expense_date?->format('M j, Y') }} · {{ $expense->category->name ?? 'Expense' }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
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
