@extends('layouts.metronic')

@section('title', 'Income Report')
@section('page_title', 'Income Report')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
        <x-fin-back-link href="{{ route('families.reports.index') }}">
        Back to Reports
    </x-fin-back-link>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Income Report</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Where money comes from — by category group and over time.</p>
        </div>
    </div>

    {{-- Filter report card (standard) --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">Filter report</h3>
        </div>
        <div class="kt-card-content pt-4">
            <form method="get" action="{{ route('families.reports.income') }}" class="flex flex-wrap items-end gap-4">
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
                <a href="{{ route('families.reports.income') }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    {{-- KPI cards (cash-flow style: label + icon, value, subtitle) --}}
    <div class="report-kpi-grid report-kpi-grid--2">
        <x-famledger.pulse-stat-card
            class="report-kpi-card"
            label="Total Income"
            :value="number_format($totalIncome, 0) . ' ' . $currency"
        >
            Selected period
        </x-famledger.pulse-stat-card>

        <x-famledger.pulse-stat-card
            class="report-kpi-card"
            label="Category groups"
            :value="(string) $bySource->count()"
        >
            Income category groups in period
        </x-famledger.pulse-stat-card>
    </div>

    {{-- Content card (same as cash flow summary card) --}}
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Income by category group (share)</h3>
        </div>
        <div class="kt-card-content p-5">
            <div class="space-y-3">
                @foreach($bySource as $s)
                    @php $pct = $totalIncome > 0 ? min(100, ($s['total'] / $totalIncome) * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-foreground">{{ $s['name'] }}</span>
                            <span class="tabular-nums">{{ number_format($s['total'], 0) }} {{ $currency }} ({{ $s['percent'] }}%)</span>
                        </div>
                        <div class="h-2 rounded-full bg-muted overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
