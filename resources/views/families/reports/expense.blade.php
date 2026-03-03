@extends('layouts.metronic')

@section('title', 'Expense Report')
@section('page_title', 'Expense Report')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reports.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to Reports
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Expense Report</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Expenses by category, monthly trend, and top spending categories.</p>
        </div>
    </div>

    {{-- Filter report card (standard) --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">Filter report</h3>
        </div>
        <div class="kt-card-content pt-4">
            <form method="get" action="{{ route('families.reports.expense', $family) }}" class="flex flex-wrap items-end gap-4">
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
                <a href="{{ route('families.reports.expense', $family) }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    {{-- KPI cards (cash-flow style: label + icon, value, subtitle) --}}
    <div class="report-kpi-grid report-kpi-grid--2">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total Expenses</span>
                <span class="text-red-500 text-lg shrink-0"><i class="ki-filled ki-arrow-down"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-red-600">{{ number_format($totalExpenses, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">Selected period</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Categories</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-chart-pie-simple"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $byCategory->count() }}</div>
            <div class="text-muted-foreground text-sm mt-2">Spending categories in period</div>
        </div>
    </div>

    {{-- Content card (same as cash flow summary card) --}}
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Expenses by category (progress)</h3>
        </div>
        <div class="kt-card-content p-5">
            <div class="space-y-3">
                @foreach($byCategory as $c)
                    @php $pct = $totalExpenses > 0 ? min(100, ($c['total'] / $totalExpenses) * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-foreground">{{ $c['name'] }}</span>
                            <span class="tabular-nums">{{ number_format($c['total'], 0) }} {{ $currency }} ({{ $c['percent'] }}%)</span>
                        </div>
                        <div class="h-2 rounded-full bg-muted overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
