@extends('layouts.metronic')

@section('title', 'Transactions')
@section('page_title', 'Transactions')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12 w-full max-w-full min-w-0">
    <x-fin-back-link href="{{ route('families.overview') }}">
        Back to {{ $family->name }}
    </x-fin-back-link>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 sm:mb-10">
        <div>
            <h1 class="font-medium text-lg text-mono">Transactions</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('families.incomes.create') }}" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                <i class="ki-filled ki-arrow-up"></i>
                {{ __('Add income') }}
            </a>
            <a href="{{ route('families.expenses.create') }}" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                <i class="ki-filled ki-arrow-down"></i>
                {{ __('Add expense') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center gap-3 text-red-800 dark:text-red-200">
            <i class="ki-filled ki-information-2 text-xl shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Summary Cards --}}
    <style>
        .txn-stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
            width: 100%;
            max-width: 100%;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 640px) {
            .txn-stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
    <div class="txn-stats-grid mt-2 sm:mt-3">
        <div class="kt-card rounded-xl border border-border bg-card px-4 py-3">
            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Income</div>
            <div class="mt-1.5 text-lg font-semibold tabular-nums text-green-600">{{ $family->currency_code }} {{ number_format((float) $totalIncome, 2) }}</div>
        </div>
        <div class="kt-card rounded-xl border border-border bg-card px-4 py-3">
            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Expenses</div>
            <div class="mt-1.5 text-lg font-semibold tabular-nums text-destructive">{{ $family->currency_code }} {{ number_format((float) $totalExpenses, 2) }}</div>
        </div>
        <div class="kt-card rounded-xl border border-border bg-card px-4 py-3">
            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Balance</div>
            <div class="mt-1.5 text-lg font-semibold tabular-nums {{ $balance >= 0 ? 'text-green-600' : 'text-destructive' }}">{{ $family->currency_code }} {{ number_format((float) $balance, 2) }}</div>
        </div>
    </div>

    {{-- Analytics + table: column gap avoids margin collapse (no visible space with mb-* alone) --}}
    <div class="flex flex-col gap-8 lg:gap-10 w-full max-w-full min-w-0">
    <div class="flex flex-col md:flex-row gap-4 lg:gap-5 w-full max-w-full min-w-0">
        <div class="famledger-chart-card w-full md:flex-1 md:min-w-0 md:basis-0 max-w-full kt-card flex flex-col rounded-xl border border-border shadow-sm min-w-0">
            <div class="px-4 py-3 border-b border-border shrink-0">
                <h2 class="text-sm font-semibold text-foreground leading-snug">Income vs expenses</h2>
                <p class="text-xs text-muted-foreground mt-0.5">
                    Last 6 months · {{ $chartCurrency }}
                    @if (request('wallet_id'))
                        · Wallet filter
                    @endif
                </p>
            </div>
            <div class="famledger-chart-panel p-3 min-h-[240px]">
                <div id="famledger_txn_flow_chart" class="w-full max-w-full min-w-0" style="min-height: 220px;"></div>
            </div>
        </div>
        <div class="famledger-chart-card w-full md:flex-1 md:min-w-0 md:basis-0 max-w-full kt-card flex flex-col rounded-xl border border-border shadow-sm min-w-0">
            <div class="px-4 py-3 border-b border-border shrink-0">
                <h2 class="text-sm font-semibold text-foreground leading-snug">Expenses by category</h2>
                <p class="text-xs text-muted-foreground mt-0.5">Same period · top 8</p>
            </div>
            <div class="famledger-chart-panel p-3 min-h-[240px]">
                <div id="famledger_txn_category_chart" class="w-full max-w-full min-w-0" style="min-height: 220px;"></div>
            </div>
        </div>
        <div class="famledger-chart-card w-full md:flex-1 md:min-w-0 md:basis-0 max-w-full kt-card flex flex-col rounded-xl border border-border shadow-sm min-w-0">
            <div class="px-4 py-3 border-b border-border shrink-0">
                <h2 class="text-sm font-semibold text-foreground leading-snug">Transaction volume</h2>
                <p class="text-xs text-muted-foreground mt-0.5">Count of records per month</p>
            </div>
            <div class="famledger-chart-panel p-3 min-h-[240px]">
                <div id="famledger_txn_volume_chart" class="w-full max-w-full min-w-0" style="min-height: 220px;"></div>
            </div>
        </div>
    </div>

    <div class="kt-card kt-card-grid w-full min-w-0 max-w-full shrink-0">
        <div class="border-b border-border bg-muted/30 px-5 py-4 sm:px-6 sm:py-5 dark:bg-muted/10">
            <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start sm:justify-between sm:gap-8">
                <div class="min-w-0 max-w-xl sm:pt-0.5">
                    <h3 class="text-lg font-semibold leading-snug tracking-tight text-primary sm:text-xl">
                        {{ __('All transactions') }}
                    </h3>
                </div>
                <form
                    method="get"
                    action="{{ route('families.transactions.index') }}"
                    class="flex w-full min-w-0 flex-wrap items-center justify-end gap-x-3 gap-y-2.5 sm:ms-auto sm:w-auto sm:shrink-0 sm:justify-end"
                >
                    <div
                        class="flex items-center gap-2.5 rounded-lg border border-border bg-background px-3 py-2 shadow-sm dark:bg-card sm:gap-3 sm:px-3.5 sm:py-2"
                    >
                        <label for="txn-filter-type" class="shrink-0 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {{ __('Type') }}
                        </label>
                        <select
                            name="type"
                            id="txn-filter-type"
                            class="kt-select kt-select-sm w-[11.5rem] min-w-[10.5rem] max-w-[14rem]"
                            onchange="this.form.submit()"
                        >
                            <option value="" {{ ($type ?? '') === '' ? 'selected' : '' }}>{{ __('All') }}</option>
                            <option value="income" {{ ($type ?? '') === 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
                            <option value="expense" {{ ($type ?? '') === 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
                        </select>
                    </div>
                    <div
                        class="flex items-center gap-2.5 rounded-lg border border-border bg-background px-3 py-2 shadow-sm dark:bg-card sm:gap-3 sm:px-3.5 sm:py-2"
                    >
                        <label for="txn-filter-wallet" class="shrink-0 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {{ __('Wallet') }}
                        </label>
                        <select
                            name="wallet_id"
                            id="txn-filter-wallet"
                            class="kt-select kt-select-sm w-[11.5rem] min-w-[10.5rem] max-w-[14rem]"
                            onchange="this.form.submit()"
                        >
                            <option value="">{{ __('All wallets') }}</option>
                            @foreach ($wallets as $w)
                                <option value="{{ $w->id }}" {{ request('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <div class="kt-card-content p-0">
            @if ($transactions->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-arrows-loop text-4xl mb-2"></i>
                    <p class="text-sm">{{ __('No transactions found.') }}</p>
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                        <a href="{{ route('families.incomes.create') }}" class="kt-btn kt-btn-primary kt-btn-sm inline-flex items-center gap-1.5">
                            <i class="ki-filled ki-arrow-up text-sm"></i>
                            {{ __('Add income') }}
                        </a>
                        <a href="{{ route('families.expenses.create') }}" class="kt-btn kt-btn-primary kt-btn-sm inline-flex items-center gap-1.5">
                            <i class="ki-filled ki-arrow-down text-sm"></i>
                            {{ __('Add expense') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[90px]">Type</th>
                                <th class="min-w-[110px]">Date</th>
                                <th class="min-w-[140px]">Wallet</th>
                                <th class="min-w-[120px]">Category</th>
                                <th class="min-w-[140px]">Description</th>
                                <th class="min-w-[120px]">Amount</th>
                                <th class="min-w-[120px]">Recorded by</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $t)
                                <tr>
                                    <td>
                                        @if ($t->type === 'income')
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/20 px-2 py-1 text-[11px] font-medium text-green-700 dark:text-green-300">
                                                <i class="ki-filled ki-arrow-up text-xs"></i> Income
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 px-2 py-1 text-[11px] font-medium text-red-700 dark:text-red-300">
                                                <i class="ki-filled ki-arrow-down text-xs"></i> Expense
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-foreground">{{ \Carbon\Carbon::parse($t->date)->format('M j, Y') }}</td>
                                    <td class="text-foreground">
                                        {{ $t->wallet_name }}
                                        <span class="text-muted-foreground text-xs">({{ $t->wallet_currency }})</span>
                                    </td>
                                    <td class="text-foreground">{{ $t->category_name ?? '—' }}</td>
                                    <td class="text-foreground">{{ \Illuminate\Support\Str::limit($t->description ?? '—', 40) }}</td>
                                    <td class="font-medium tabular-nums {{ $t->type === 'income' ? 'text-success' : 'text-destructive' }}">
                                        {{ $t->type === 'income' ? '+' : '−' }} {{ number_format((float) $t->amount, 2) }} {{ $t->currency_code }}
                                    </td>
                                    <td class="text-muted-foreground text-sm">{{ $t->user_name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-t border-border bg-muted/20 dark:bg-muted/5">
                    <p class="text-xs text-muted-foreground tabular-nums order-2 sm:order-1">
                        @if ($transactions->total() > 0)
                            {{ __('Showing :from–:to of :total', [
                                'from' => $transactions->firstItem(),
                                'to' => $transactions->lastItem(),
                                'total' => $transactions->total(),
                            ]) }}
                            @if ($transactions->hasPages())
                                <span class="text-muted-foreground/80">· {{ __('10 per page') }}</span>
                            @endif
                        @endif
                    </p>
                    @if ($transactions->hasPages())
                        <div class="order-1 sm:order-2 min-w-0 flex justify-end [&_.pagination]:mb-0">
                            {{ $transactions->withQueryString()->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
    </div>{{-- end analytics + table column --}}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') return;

    var currency = @json($chartCurrency);
    var monthLabels = @json($chartMonthLabels);
    var incomeByMonth = @json($chartIncomeByMonth);
    var expenseByMonth = @json($chartExpenseByMonth);
    var incomeCountByMonth = @json($chartIncomeCountByMonth);
    var expenseCountByMonth = @json($chartExpenseCountByMonth);
    var categoryNames = @json($chartCategoryNames);
    var categoryTotals = @json($chartCategoryTotals);

    function famledgerCompactAxis(v) {
        v = Number(v) || 0;
        var a = Math.abs(v);
        if (a >= 1e9) return (v / 1e9).toFixed(1).replace(/\.0$/, '') + 'B';
        if (a >= 1e6) return (v / 1e6).toFixed(1).replace(/\.0$/, '') + 'M';
        if (a >= 1e3) return (v / 1e3).toFixed(1).replace(/\.0$/, '') + 'k';
        return (v % 1 === 0 ? String(v) : v.toFixed(1));
    }

    var palette = ['#009EF7', '#38bdf8', '#0ea5e9', '#0369a1', '#22c55e', '#a855f7', '#f97316', '#ef4444', '#14b8a6', '#eab308'];

    var flowEl = document.getElementById('famledger_txn_flow_chart');
    if (flowEl && monthLabels.length) {
        new ApexCharts(flowEl, {
            series: [
                { name: 'Income', data: incomeByMonth.map(Number) },
                { name: 'Expenses', data: expenseByMonth.map(Number) },
            ],
            chart: {
                type: 'bar',
                width: '100%',
                height: 280,
                toolbar: { show: false },
                redrawOnParentResize: true,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 3,
                    columnWidth: '72%',
                },
            },
            colors: ['#22c55e', '#ef4444'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: monthLabels,
                labels: {
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                    formatter: function (v) {
                        return famledgerCompactAxis(v);
                    },
                },
            },
            grid: {
                borderColor: 'var(--color-border)',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 4, left: 4, right: 8, bottom: 4 },
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                offsetY: 4,
                labels: { colors: 'var(--color-muted-foreground)' },
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (v) {
                        return (v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }) + ' ' + currency;
                    },
                },
            },
        }).render();
    }

    var catEl = document.getElementById('famledger_txn_category_chart');
    if (catEl) {
        if (categoryNames.length && categoryTotals.length) {
            new ApexCharts(catEl, {
                series: [{ name: 'Expenses', data: categoryTotals.map(Number) }],
                chart: {
                    type: 'bar',
                    width: '100%',
                    height: 280,
                    toolbar: { show: false },
                    redrawOnParentResize: true,
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 4,
                        columnWidth: '55%',
                        distributed: true,
                    },
                },
                colors: palette,
                dataLabels: {
                    enabled: categoryNames.length <= 8,
                    offsetY: -4,
                    style: { colors: ['var(--color-foreground)'], fontSize: '9px' },
                    formatter: function (val) {
                        return (val || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
                    },
                },
                xaxis: {
                    categories: categoryNames,
                    labels: {
                        rotate: categoryNames.length > 3 ? -40 : 0,
                        rotateAlways: categoryNames.length > 3,
                        hideOverlappingLabels: true,
                        trim: true,
                        maxHeight: 70,
                        style: { colors: 'var(--color-muted-foreground)', fontSize: '9px' },
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: {
                        style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                        formatter: function (v) {
                            return famledgerCompactAxis(v);
                        },
                    },
                },
                grid: {
                    borderColor: 'var(--color-border)',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } },
                    yaxis: { lines: { show: true } },
                    padding: { top: 8, left: 4, right: 8, bottom: 4 },
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (v) {
                            return (v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }) + ' ' + currency;
                        },
                    },
                },
                legend: { show: false },
            }).render();
        } else {
            catEl.innerHTML = '<div class="flex items-center justify-center h-[200px] text-muted-foreground text-sm text-center px-3">No expenses in this period.</div>';
        }
    }

    var volEl = document.getElementById('famledger_txn_volume_chart');
    if (volEl && monthLabels.length) {
        new ApexCharts(volEl, {
            series: [
                { name: 'Income', data: incomeCountByMonth.map(Number) },
                { name: 'Expenses', data: expenseCountByMonth.map(Number) },
            ],
            chart: {
                type: 'bar',
                width: '100%',
                height: 280,
                toolbar: { show: false },
                redrawOnParentResize: true,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 3,
                    columnWidth: '72%',
                },
            },
            colors: ['#22c55e', '#f97316'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: monthLabels,
                labels: {
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                    formatter: function (v) {
                        return (v % 1 === 0 ? v : v.toFixed(1));
                    },
                },
                min: 0,
                forceNiceScale: true,
            },
            grid: {
                borderColor: 'var(--color-border)',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 4, left: 4, right: 8, bottom: 4 },
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                offsetY: 4,
                labels: { colors: 'var(--color-muted-foreground)' },
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (v) {
                        return (v || 0) + ' transactions';
                    },
                },
            },
        }).render();
    }
});
</script>
@endpush
