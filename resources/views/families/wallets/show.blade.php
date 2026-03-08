@extends('layouts.metronic')

@section('title', $wallet->name)
@section('page_title', $wallet->name)

@section('content')
<div class="kt-container-fixed pb-6">
    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-1.5 text-xs text-muted-foreground mb-3">
        <a class="hover:text-foreground transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="ki-filled ki-right text-[10px] opacity-60"></i>
        <a class="hover:text-foreground transition-colors" href="{{ route('families.wallets.index', $family) }}">Wallets</a>
        <i class="ki-filled ki-right text-[10px] opacity-60"></i>
        <span class="text-foreground truncate max-w-[180px] sm:max-w-none">{{ $wallet->name }}</span>
    </nav>

    {{-- Title + actions row --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-5">
        <div class="min-w-0 flex-1 sm:flex-initial">
            <h1 class="text-xl sm:text-2xl font-semibold text-foreground truncate">{{ $wallet->name }}</h1>
            @if(!$wallet->is_primary)
            <p class="mt-1.5 text-sm text-muted-foreground">
                Income is recorded to the main wallet only. Use <a href="{{ route('families.transfers.create', $family) }}" class="text-primary hover:underline font-medium">Transfer</a> to move money here.
            </p>
            @endif
        </div>
        <div class="flex flex-wrap items-center justify-end sm:justify-end gap-2 shrink-0 sm:ml-auto">
            @if($wallet->is_primary)
            <a href="{{ route('families.incomes.create', $family) }}" class="kt-btn kt-btn-primary kt-btn-sm">
                <i class="ki-filled ki-arrow-down text-base"></i>
                Record income
            </a>
            @endif
            <a href="{{ route('families.expenses.create', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-primary kt-btn-sm">
                <i class="ki-filled ki-arrow-up text-base"></i>
                Record expense
            </a>
            <a href="{{ route('families.wallets.edit', [$family, $wallet]) }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-pencil text-base"></i>
                Edit
            </a>
        </div>
    </div>

    {{-- Balance card (hero) --}}
    <div class="kt-card bg-card border border-border rounded-xl mb-6 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="kt-badge {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-full text-xs shrink-0">
                        <span class="kt-badge-dot size-1.5"></span>
                        {{ ucfirst($wallet->status) }}
                    </span>
                    <span class="text-sm text-muted-foreground">{{ $walletTypes[$wallet->type] ?? $wallet->type }} · {{ $wallet->currency_code }}</span>
                    @if($wallet->is_shared)
                    <span class="text-xs text-muted-foreground">· Shared with family</span>
                    @endif
                </div>
                @if ($wallet->description)
                <p class="text-sm text-muted-foreground mt-1.5 line-clamp-2">{{ $wallet->description }}</p>
                @endif
            </div>
            <div class="sm:text-right border-t border-border pt-4 sm:pt-0 sm:border-t-0 sm:pl-6 shrink-0">
                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Current balance</p>
                <p class="text-2xl sm:text-3xl font-bold tabular-nums text-foreground mt-0.5">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency_code }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-5 lg:gap-6 lg:grid-cols-3">
        {{-- Details card --}}
        <div class="lg:col-span-1">
            <div class="kt-card h-full">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Details</h3>
                </div>
                <div class="kt-card-content">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-3 py-2 border-b border-border">
                            <dt class="text-muted-foreground shrink-0">Type</dt>
                            <dd class="text-foreground font-medium text-right">{{ $walletTypes[$wallet->type] ?? $wallet->type }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 py-2 border-b border-border">
                            <dt class="text-muted-foreground shrink-0">Currency</dt>
                            <dd class="text-foreground text-right">{{ $wallet->currency_code }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 py-2 border-b border-border">
                            <dt class="text-muted-foreground shrink-0">Shared</dt>
                            <dd class="text-foreground text-right">{{ $wallet->is_shared ? 'Yes — all members' : 'No — personal' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 py-2 border-b border-border">
                            <dt class="text-muted-foreground shrink-0">Status</dt>
                            <dd class="text-right">
                                <span class="kt-badge kt-badge-sm {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-full">
                                    <span class="kt-badge-dot size-1.5"></span>
                                    {{ ucfirst($wallet->status) }}
                                </span>
                            </dd>
                        </div>
                        @if ($wallet->creator)
                        <div class="flex justify-between gap-3 py-2 border-b border-border">
                            <dt class="text-muted-foreground shrink-0">Created by</dt>
                            <dd class="text-foreground text-right">{{ $wallet->creator->name }}</dd>
                        </div>
                        @endif
                        @if ($wallet->created_at)
                        <div class="flex justify-between gap-3 py-2 border-b border-border">
                            <dt class="text-muted-foreground shrink-0">Created</dt>
                            <dd class="text-foreground text-right">{{ $wallet->created_at->format('M j, Y') }}</dd>
                        </div>
                        @endif
                    </dl>
                    @if ($wallet->description)
                    <div class="mt-4 pt-3 border-t border-border">
                        <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1.5">Description</h4>
                        <p class="text-sm text-foreground leading-relaxed">{{ $wallet->description }}</p>
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
                    <div class="flex items-center gap-1.5 ml-auto">
                        <a href="{{ route('families.incomes.index', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-xs kt-btn-ghost text-muted-foreground hover:text-foreground">All income</a>
                        <a href="{{ route('families.expenses.index', $family) }}?wallet_id={{ $wallet->id }}" class="kt-btn kt-btn-xs kt-btn-ghost text-muted-foreground hover:text-foreground">All expenses</a>
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
