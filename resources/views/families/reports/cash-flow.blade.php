@extends('layouts.metronic')

@section('title', 'Cash Flow Report')
@section('page_title', 'Cash Flow Report')

@section('content')
<style>
.cash-flow-kpi-grid {
    display: grid !important;
    width: 100% !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 1.5rem !important;
    margin-top: 1.5rem !important;
    margin-bottom: 1.5rem !important;
}
.cash-flow-kpi-grid .cash-flow-kpi-card {
    min-width: 0 !important;
    max-width: none !important;
    box-sizing: border-box !important;
    padding-top: 1.5rem !important;
}
@media (max-width: 767px) {
    .cash-flow-kpi-grid { grid-template-columns: 1fr !important; }
}
</style>
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reports.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to Reports
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Cash Flow Report</h1>
        </div>
    </div>

    {{-- Filters (card style like accounts/income) --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header flex-wrap gap-2">
            <h3 class="kt-card-title text-sm">Filter report</h3>
        </div>
        <div class="kt-card-content">
            <form method="get" action="{{ route('families.reports.cash-flow', $family) }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">From</label>
                    <input type="date" name="from" value="{{ $dateFrom }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">To</label>
                    <input type="date" name="to" value="{{ $dateTo }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Wallet</label>
                    <select name="wallet_id" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[160px]">
                        <option value="">All wallets</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}" {{ $walletId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">Apply</button>
                <a href="{{ route('families.reports.cash-flow', $family) }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    {{-- KPI cards — one row, four columns (forced layout) --}}
    <div class="cash-flow-kpi-grid">
        <div class="cash-flow-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Opening balance</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-wallet"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ number_format($openingBalance, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">Start of period</div>
        </div>
        <div class="cash-flow-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total income</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-arrow-up"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-green-600">+ {{ number_format($totalIncome, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">In period</div>
        </div>
        <div class="cash-flow-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total expenses</span>
                <span class="text-red-500 text-lg shrink-0"><i class="ki-filled ki-arrow-down"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-red-600">− {{ number_format($totalExpenses, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">In period</div>
        </div>
        <div class="cash-flow-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Closing balance</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-safe"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ number_format($closingBalance, 0) }} {{ $currency }}</div>
            <div class="text-sm mt-2 {{ $netFlow >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">Net flow: {{ $netFlow >= 0 ? '+' : '' }}{{ number_format($netFlow, 0) }} {{ $currency }}</div>
        </div>
    </div>

    {{-- Cash flow summary table (income index card/table style) --}}
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">Cash flow summary</h3>
        </div>
        <div class="kt-card-content p-0">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Item</th>
                            <th class="min-w-[140px] text-right">Amount ({{ $currency }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-foreground">Opening balance (start of period)</td>
                            <td class="text-right tabular-nums font-medium">{{ number_format($openingBalance, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="text-foreground">+ Total income</td>
                            <td class="text-right tabular-nums font-medium text-green-600">+ {{ number_format($totalIncome, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="text-foreground">− Total expenses</td>
                            <td class="text-right tabular-nums font-medium text-red-600">− {{ number_format($totalExpenses, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium text-foreground">= Net cash flow</td>
                            <td class="text-right tabular-nums font-bold {{ $netFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $netFlow >= 0 ? '+' : '' }}{{ number_format($netFlow, 0) }}</td>
                        </tr>
                        <tr class="border-t-2 border-border">
                            <td class="font-semibold text-foreground">Closing balance (end of period)</td>
                            <td class="text-right tabular-nums font-bold">{{ number_format($closingBalance, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
