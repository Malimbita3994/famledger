@extends('layouts.metronic')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@php
    $currency = $currency ?? config('currencies.default', 'TZS');
    $totalIncome = $totalIncome ?? 0;
    $totalExpenses = $totalExpenses ?? 0;
    $totalSavings = $totalSavings ?? 0;
    $budgetUsedPercent = $budgetUsedPercent ?? 0;
    $budgetUsedLabel = $budgetUsedLabel ?? '—';
    $chartMonths = $chartMonths ?? [];
    $chartIncome = $chartIncome ?? [];
    $chartExpense = $chartExpense ?? [];
    $expensesByCategory = $expensesByCategory ?? collect();
    $recentActivity = $recentActivity ?? collect();
    $formatAmount = function ($amount, $code = null) {
        return number_format((float) $amount, 0) . ' ' . ($code ?? config('currencies.default', 'TZS'));
    };
@endphp

@section('content')
<style>
.dashboard-kpi-grid {
    display: grid !important;
    width: 100% !important;
    gap: 1.5rem !important;
    margin-top: 0.5rem !important;
    margin-bottom: 1.5rem !important;
}
.dashboard-kpi-grid .dashboard-kpi-card {
    min-width: 0 !important;
    box-sizing: border-box !important;
    margin: 0.5rem !important;
}
@media (min-width: 768px) {
    .dashboard-kpi-grid .dashboard-kpi-card {
        margin: 0.75rem !important;
    }
}
@media (min-width: 1024px) {
    .dashboard-kpi-grid { grid-template-columns: repeat(4, 1fr) !important; }
}
@media (min-width: 768px) and (max-width: 1023px) {
    .dashboard-kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 767px) {
    .dashboard-kpi-grid { grid-template-columns: 1fr !important; }
}
</style>
<div class="kt-container-fixed px-4 lg:px-6 pb-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-foreground">FamLedger Dashboard</h2>
        <p class="text-muted-foreground mt-1">Welcome to your Family Finance & Budget Management System. Default currency: {{ $currency }}.</p>
    </div>

    @if (!isset($currentFamily) || !$currentFamily)
        <div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-4 py-3 text-amber-800 dark:text-amber-200">
            <a href="{{ route('families.create') }}" class="font-medium underline">Create or join a family</a> to see income, expenses, and budgets here.
        </div>
    @endif

    <div class="dashboard-kpi-grid">
        <div class="dashboard-kpi-card card bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Income</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-arrow-up"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-gray-900 dark:text-white">{{ $formatAmount($totalIncome, $currency) }}</div>
            <div class="text-muted-foreground text-sm mt-2">This month</div>
        </div>
        <div class="dashboard-kpi-card card bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Expenses</span>
                <span class="text-red-500 text-lg shrink-0"><i class="ki-filled ki-arrow-down"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-gray-900 dark:text-white">{{ $formatAmount($totalExpenses, $currency) }}</div>
            <div class="text-muted-foreground text-sm mt-2">This month</div>
        </div>
        <div class="dashboard-kpi-card card bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Budget Used</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-chart-pie"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-gray-900 dark:text-white">{{ $budgetUsedPercent }}%</div>
            <div class="text-gray-600 dark:text-gray-300 text-sm mt-2">{{ $budgetUsedLabel }}</div>
        </div>
        <div class="dashboard-kpi-card card bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Balance</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-safe"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-gray-900 dark:text-white">{{ $formatAmount($totalSavings, $currency) }}</div>
            <div class="text-muted-foreground text-sm mt-2">All wallets</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 lg:gap-5 mb-6">
        <div class="xl:col-span-2 kt-card flex flex-col rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h3 class="text-base font-semibold text-foreground">Income vs Expenses</h3>
                <p class="text-sm text-muted-foreground mt-0.5">Monthly comparison (last 6 months) — {{ $currency }}</p>
            </div>
            <div class="p-4 min-h-[280px]">
                <div id="famledger_income_expense_chart" class="w-full" style="min-height: 260px;"></div>
            </div>
        </div>
        <div class="kt-card flex flex-col rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h3 class="text-base font-semibold text-foreground">Expenses by Category</h3>
                <p class="text-sm text-muted-foreground mt-0.5">This month</p>
            </div>
            <div class="p-4 flex items-center justify-center min-h-[280px]">
                <div id="famledger_category_chart" class="w-full max-w-[260px] mx-auto" style="min-height: 260px;"></div>
            </div>
        </div>
    </div>

    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="text-base font-semibold text-foreground">Recent Activity</h3>
                <p class="text-sm text-muted-foreground mt-0.5">Latest transactions</p>
            </div>
            @if (isset($currentFamily) && $currentFamily)
                <a href="{{ route('families.accounts.income', $currentFamily) }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary">Income</a>
                <a href="{{ route('families.accounts.expenses', $currentFamily) }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary">Expenses</a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/30">
                        <th class="text-start font-medium text-muted-foreground px-5 py-3">Description</th>
                        <th class="text-start font-medium text-muted-foreground px-5 py-3">Category</th>
                        <th class="text-end font-medium text-muted-foreground px-5 py-3">Amount</th>
                        <th class="text-end font-medium text-muted-foreground px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($recentActivity as $item)
                    <tr class="hover:bg-muted/30">
                        <td class="px-5 py-3 text-foreground">{{ $item->description }}</td>
                        <td class="px-5 py-3 text-muted-foreground">{{ $item->category }}</td>
                        <td class="px-5 py-3 text-end font-medium {{ $item->type === 'income' ? 'text-green-600' : 'text-destructive' }}">
                            {{ $item->type === 'income' ? '+' : '-' }}{{ $formatAmount($item->amount, $item->currency_code) }}
                        </td>
                        <td class="px-5 py-3 text-end text-muted-foreground">{{ $item->date?->format('M j, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-muted-foreground">No transactions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ApexCharts === 'undefined') return;

    var chartMonths = @json($chartMonths);
    var chartIncome = @json($chartIncome);
    var chartExpense = @json($chartExpense);
    var categoryNames = @json($expensesByCategory->pluck('category_name'));
    var categoryAmounts = @json($expensesByCategory->pluck('total'));
    var currency = @json($currency);

    var incomeExpenseEl = document.getElementById('famledger_income_expense_chart');
    if (incomeExpenseEl && chartMonths.length) {
        new ApexCharts(incomeExpenseEl, {
            series: [
                { name: 'Income', data: chartIncome },
                { name: 'Expenses', data: chartExpense }
            ],
            chart: { type: 'area', height: 260, toolbar: { show: false } },
            colors: ['var(--color-green-500)', 'var(--color-destructive)'],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.05 } },
            dataLabels: { enabled: false },
            legend: { position: 'top', horizontalAlign: 'right', labels: { colors: 'var(--color-muted-foreground)' } },
            xaxis: {
                categories: chartMonths,
                labels: { style: { colors: 'var(--color-muted-foreground)', fontSize: '12px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '12px' },
                    formatter: function(v) { return (v / 1000).toFixed(0) + 'k'; }
                },
                axisTicks: { show: false }
            },
            grid: {
                borderColor: 'var(--color-border)',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } }
            },
            tooltip: {
                theme: 'dark',
                y: { formatter: function(v) { return (v || 0).toLocaleString() + ' ' + currency; } }
            }
        }).render();
    }

    var categoryEl = document.getElementById('famledger_category_chart');
    if (categoryEl) {
        if (categoryNames.length && categoryAmounts.length) {
            new ApexCharts(categoryEl, {
                series: [{
                    name: 'Expenses',
                    data: categoryAmounts.map(Number),
                }],
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: { show: false },
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        barHeight: '60%',
                        distributed: true, // give each bar its own color
                    },
                },
                // Palette based on FamLedger logo color for each category
                colors: [
                    '#009EF7',
                    '#38bdf8',
                    '#0ea5e9',
                    '#0369a1',
                    '#22c55e',
                    '#a855f7',
                    '#f97316',
                    '#ef4444'
                ],
                dataLabels: {
                    enabled: true,
                    style: { colors: ['#374151'], fontSize: '11px' },
                    formatter: function (val) {
                        return (val || 0).toLocaleString() + ' ' + currency;
                    },
                },
                xaxis: {
                    categories: categoryNames,
                    labels: {
                        style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' },
                        formatter: function (v) {
                            return (v || 0).toLocaleString();
                        },
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: {
                        style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' },
                    },
                },
                grid: {
                    borderColor: 'var(--color-border)',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (v) {
                            return (v || 0).toLocaleString() + ' ' + currency;
                        },
                    },
                },
                legend: { show: false },
            }).render();
        } else {
            categoryEl.innerHTML = '<div class="flex items-center justify-center h-full text-muted-foreground text-sm">No expense data this month</div>';
        }
    }
});
</script>
@endsection
