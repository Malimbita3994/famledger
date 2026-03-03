@extends('layouts.metronic')

@section('title', 'Income')
@section('page_title', 'Income')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Income</h1>
            <p class="text-sm text-muted-foreground mt-0.5">All income is recorded into a wallet. No wallet → no valid income.</p>
        </div>
        <a href="{{ route('families.incomes.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i>
            Record income
        </a>
    </div>
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center gap-3 text-red-800 dark:text-red-200">
            <i class="ki-filled ki-information-2 text-xl shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($wallets->isNotEmpty())
        <div class="kt-card mb-6">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Wallet current balance</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-wrap gap-4">
                    @foreach ($wallets as $w)
                        <a href="{{ route('families.wallets.show', [$family, $w]) }}" class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 hover:bg-muted/50 transition-colors">
                            <i class="ki-filled ki-wallet text-muted-foreground"></i>
                            <span class="font-medium text-sm">{{ $w->name }}</span>
                            <span class="tabular-nums text-sm {{ $w->balance >= 0 ? 'text-success' : 'text-destructive' }}">{{ number_format($w->balance, 2) }} {{ $w->currency_code }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-header flex-wrap gap-2">
            <h3 class="kt-card-title text-sm">Income records</h3>
            <form method="get" action="{{ route('families.incomes.index', $family) }}" class="flex items-center gap-2">
                <label for="wallet_id" class="text-sm text-muted-foreground">Wallet</label>
                <select name="wallet_id" id="wallet_id" class="kt-select kt-select-sm w-auto" onchange="this.form.submit()">
                    <option value="">All wallets</option>
                    @foreach ($wallets as $w)
                        <option value="{{ $w->id }}" {{ request('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="kt-card-content p-0">
            @if ($incomes->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-arrow-up text-4xl mb-2"></i>
                    <p class="text-sm">No income recorded yet.</p>
                    <a href="{{ route('families.incomes.create', $family) }}" class="kt-btn kt-btn-outline mt-4">Record income</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[100px]">Date</th>
                                <th class="min-w-[140px]">Wallet</th>
                                <th class="min-w-[100px]">Category</th>
                                <th class="min-w-[120px]">Amount</th>
                                <th class="min-w-[140px]">Source</th>
                                <th class="min-w-[100px]">Recorded by</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($incomes as $income)
                            <tr>
                                <td class="text-foreground">{{ $income->received_date->format('M j, Y') }}</td>
                                <td>
                                    <a href="{{ route('families.wallets.show', [$family, $income->wallet]) }}" class="text-primary hover:underline">{{ $income->wallet->name }}</a>
                                    <span class="text-muted-foreground text-xs">({{ $income->wallet->currency_code }})</span>
                                </td>
                                <td class="text-foreground">{{ $income->category?->name ?? '—' }}</td>
                                <td class="font-medium tabular-nums text-success">+ {{ number_format($income->amount, 2) }} {{ $income->currency_code }}</td>
                                <td class="text-foreground">{{ $income->source ?? '—' }}</td>
                                <td class="text-muted-foreground text-sm">{{ $income->createdBy?->name ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-border">
                    {{ $incomes->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
