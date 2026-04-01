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
        <x-fin-back-link href="{{ route('families.reports.index') }}">
        Back to Reports
    </x-fin-back-link>

    <div class="mb-6">
        <h1 class="font-medium text-lg text-mono">Cash Flow Report</h1>
    </div>

    {{-- Filters (card style like accounts/income) --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header flex-wrap gap-0 border-b border-border !p-0 overflow-visible">
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 sm:px-6 pt-5 sm:pt-6 pb-5 w-full">
                <h3 class="text-sm font-semibold tracking-tight text-primary mb-0">{{ __('Filter report') }}</h3>
                <x-famledger.export-pdf-button :href="route('families.reports.cash-flow.export-pdf') . '?' . http_build_query(request()->only(['from','to','wallet_id']))" />
            </div>
        </div>
        <div class="kt-card-content px-4 sm:px-6 pt-4">
            <form method="get" action="{{ route('families.reports.cash-flow') }}" class="flex flex-wrap items-end gap-4">
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
                <a href="{{ route('families.reports.cash-flow') }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    {{-- KPI cards — one row, four columns (forced layout) --}}
    <div class="cash-flow-kpi-grid">
        <x-famledger.pulse-stat-card
            class="cash-flow-kpi-card"
            label="Opening balance"
            :value="number_format($openingBalance, 0) . ' ' . $currency"
        >
            Start of period
        </x-famledger.pulse-stat-card>

        <x-famledger.pulse-stat-card
            class="cash-flow-kpi-card"
            label="Total income"
            :value="'+' . ' ' . number_format($totalIncome, 0) . ' ' . $currency"
        >
            In period
        </x-famledger.pulse-stat-card>

        <x-famledger.pulse-stat-card
            class="cash-flow-kpi-card cash-flow-kpi-card--expenses"
            label="Total expenses"
            :value="'− ' . number_format($totalExpenses, 0) . ' ' . $currency"
        >
            In period
        </x-famledger.pulse-stat-card>

        @php
            $liabilitySign = $liabilityChange >= 0 ? '+' : '−';
            $liabilityAbs = abs($liabilityChange);
        @endphp
        <x-famledger.pulse-stat-card
            class="cash-flow-kpi-card"
            label="Liabilities"
            :value="$liabilitySign . ' ' . number_format($liabilityAbs, 0) . ' ' . $currency"
        >
            Closing outstanding: {{ number_format($periodLiabilityTotal, 0) }} {{ $currency }}
        </x-famledger.pulse-stat-card>
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
                            <td class="text-red-600 dark:text-red-400 font-medium">− Total expenses</td>
                            <td class="text-right tabular-nums font-medium text-red-600 dark:text-red-400">− {{ number_format($totalExpenses, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium text-foreground">= Net cash flow</td>
                            <td class="text-right tabular-nums font-bold {{ $netFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $netFlow >= 0 ? '+' : '' }}{{ number_format($netFlow, 0) }}</td>
                        </tr>
                        <tr class="border-t-2 border-border">
                            <td class="font-semibold text-foreground">Closing balance (end of period)</td>
                            <td class="text-right tabular-nums font-bold">{{ number_format($closingBalance, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted-foreground">Change in liabilities (loan draws − repayments)</td>
                            <td class="text-right tabular-nums font-medium {{ $liabilityChange >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $liabilityChange >= 0 ? '+' : '' }}{{ number_format($liabilityChange, 0) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted-foreground">Closing liabilities (outstanding)</td>
                            <td class="text-right tabular-nums font-medium text-red-600">{{ number_format($periodLiabilityTotal, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
