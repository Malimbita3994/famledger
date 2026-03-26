@extends('layouts.metronic')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<style>
    .dashboard-pulse-page {
        --dp-accent: #009ef7;
        --dp-accent-2: #0ea5e9;
    }
    .dashboard-pulse-page .dashboard-pulse-frame {
        max-width: none;
        margin-left: 0;
        margin-right: 0;
    }
    .dashboard-pulse-eyebrow {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .dashboard-pulse-title {
        font-size: clamp(1.35rem, 2.8vw, 1.7rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: var(--dp-accent);
    }
    .dashboard-pulse-frame {
        padding: 3px;
        border-radius: 24px;
        background: linear-gradient(
            135deg,
            rgba(0, 158, 247, 0.42) 0%,
            rgba(255, 255, 255, 0.96) 46%,
            rgba(14, 165, 233, 0.3) 100%
        );
        box-shadow:
            0 4px 24px rgba(0, 158, 247, 0.12),
            0 24px 48px rgba(15, 23, 42, 0.08);
        width: 100%;
    }
    .dark .dashboard-pulse-frame {
        box-shadow:
            0 4px 24px rgba(0, 158, 247, 0.1),
            0 24px 48px rgba(0, 0, 0, 0.25);
    }
    .dashboard-pulse-card-inner {
        background: #fff;
        border-radius: 21px;
        padding: 1.5rem 1.35rem 1.65rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }
    @media (min-width: 640px) {
        .dashboard-pulse-card-inner {
            padding: 1.75rem 1.65rem 1.85rem;
        }
    }
    .dark .dashboard-pulse-card-inner {
        background: rgb(15 23 42 / 0.96);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    .dashboard-pulse-section-title {
        font-size: clamp(1.05rem, 2vw, 1.2rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--dp-accent);
    }
    .dashboard-pulse-card-stack {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    @media (min-width: 768px) {
        .dashboard-pulse-card-stack {
            gap: 2rem;
        }
    }
    .dashboard-kpi-grid {
        display: grid !important;
        width: 100% !important;
        gap: 1rem !important;
        margin: 0 !important;
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
    .dashboard-kpi-grid .dashboard-kpi-card {
        min-width: 0 !important;
        box-sizing: border-box !important;
    }
    .dashboard-pulse-page .dashboard-pulse-kpi-card {
        border-radius: 16px !important;
        border: 1px solid rgba(14, 165, 233, 0.2) !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .dashboard-pulse-page .dashboard-pulse-kpi-card:hover {
        border-color: rgba(0, 158, 247, 0.35) !important;
        box-shadow: 0 8px 24px rgba(0, 158, 247, 0.1);
        transform: translateY(-1px);
    }
    .dark .dashboard-pulse-page .dashboard-pulse-kpi-card {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.55) 0%, rgb(15 23 42 / 0.72) 100%) !important;
        border-color: rgba(14, 165, 233, 0.22) !important;
    }
    .dashboard-pulse-page .dashboard-pulse-kt-card {
        border-radius: 16px !important;
        border: 1px solid rgba(14, 165, 233, 0.16) !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .dark .dashboard-pulse-page .dashboard-pulse-kt-card {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.5) 0%, rgb(15 23 42 / 0.85) 100%) !important;
        border-color: rgba(14, 165, 233, 0.2) !important;
    }
    .dashboard-pulse-page .dashboard-pulse-kt-card .border-b {
        border-bottom-color: rgba(14, 165, 233, 0.12) !important;
    }
    .dark .dashboard-pulse-page .dashboard-pulse-kt-card .border-b {
        border-bottom-color: rgba(14, 165, 233, 0.18) !important;
    }
    .dashboard-pulse-btn-outline-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        background: rgba(255, 255, 255, 0.95);
        color: #334155 !important;
        text-decoration: none !important;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .dashboard-pulse-btn-outline-sm:hover {
        border-color: var(--dp-accent);
        background: rgba(0, 158, 247, 0.08);
    }
    .dark .dashboard-pulse-btn-outline-sm {
        background: rgba(30, 41, 59, 0.9);
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.35);
    }
    @media (prefers-reduced-motion: reduce) {
        .dashboard-pulse-page .dashboard-pulse-kpi-card:hover {
            transform: none;
        }
    }
</style>
@endpush

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
<div class="dashboard-pulse-page min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="dashboard-pulse-eyebrow mb-1.5">{{ __('Overview') }}</p>
                <h1 class="dashboard-pulse-title">{{ __('Dashboard') }}</h1>
                <p class="text-sm text-muted-foreground mt-2 max-w-3xl">
                    {{ __('Welcome to your family finance overview. Default currency: :currency.', ['currency' => $currency]) }}
                </p>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-10 max-w-full overflow-x-hidden dashboard-pulse-card-stack">
        @if (!isset($currentFamily) || !$currentFamily)
            <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-4 py-3 text-amber-800 dark:text-amber-200">
                <a href="{{ route('families.create') }}" class="font-medium underline">{{ __('Create or join a family') }}</a>
                {{ __('to see income, expenses, and budgets here.') }}
            </div>
        @endif

        <div class="dashboard-pulse-frame shrink-0 min-w-0 max-w-full">
            <div class="dashboard-pulse-card-inner min-w-0 max-w-full overflow-hidden">
                <h2 class="dashboard-pulse-section-title mb-4">{{ __('This month at a glance') }}</h2>
                <div class="dashboard-kpi-grid">
                    <x-famledger.pulse-stat-card
                        class="dashboard-kpi-card dashboard-pulse-kpi-card cursor-pointer js-dashboard-card"
                        data-card-type="income"
                        label="{{ __('Total Income') }}"
                        :value="$formatAmount($totalIncome, $currency)"
                    >
                        {{ __('This month') }}
                    </x-famledger.pulse-stat-card>

                    <x-famledger.pulse-stat-card
                        class="dashboard-kpi-card dashboard-pulse-kpi-card cursor-pointer js-dashboard-card"
                        data-card-type="expenses"
                        label="{{ __('Total Expenses') }}"
                        :value="$formatAmount($totalExpenses, $currency)"
                    >
                        {{ __('This month') }}
                    </x-famledger.pulse-stat-card>

                    <x-famledger.pulse-stat-card
                        class="dashboard-kpi-card dashboard-pulse-kpi-card cursor-pointer js-dashboard-card"
                        data-card-type="budget"
                        label="{{ __('Budget Used') }}"
                        :value="(string) $budgetUsedPercent . '%'"
                    >
                        {{ $budgetUsedLabel }}
                    </x-famledger.pulse-stat-card>

                    <x-famledger.pulse-stat-card
                        class="dashboard-kpi-card dashboard-pulse-kpi-card cursor-pointer js-dashboard-card"
                        data-card-type="wallets"
                        label="{{ __('Total Balance') }}"
                        :value="$formatAmount($totalSavings, $currency)"
                    >
                        {{ __('All wallets') }}
                    </x-famledger.pulse-stat-card>
                    @if(isset($currentFamily) && $currentFamily)
                        <x-famledger.pulse-stat-card
                            class="dashboard-kpi-card dashboard-pulse-kpi-card cursor-pointer js-dashboard-card"
                            data-card-type="projects"
                            label="{{ __('Projects') }}"
                            :value="(string) ($projectCount ?? 0)"
                        >
                            {{ $activeProjectCount ?? 0 }} {{ __('active') }}
                        </x-famledger.pulse-stat-card>

                        <x-famledger.pulse-stat-card
                            class="dashboard-kpi-card dashboard-pulse-kpi-card cursor-pointer js-dashboard-card"
                            data-card-type="properties"
                            label="{{ __('Properties') }}"
                            :value="(string) ($propertyCount ?? 0)"
                        >
                            {{ __('Value') }}: {{ number_format($propertyTotalValue ?? 0, 0) }} {{ $currency }}
                        </x-famledger.pulse-stat-card>
                    @endif
                </div>
            </div>
        </div>

        <div class="dashboard-pulse-frame shrink-0 min-w-0 max-w-full">
            <div class="dashboard-pulse-card-inner min-w-0 max-w-full overflow-hidden">
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 lg:gap-5">
                    <div class="xl:col-span-2 kt-card famledger-chart-card flex flex-col dashboard-pulse-kt-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-border">
                            <h3 class="dashboard-pulse-section-title text-base mb-0.5">{{ __('Income vs Expenses') }}</h3>
                            <p class="text-sm text-muted-foreground mt-0.5">{{ __('Monthly comparison (last 6 months) — :currency', ['currency' => $currency]) }}</p>
                        </div>
                        <div class="p-4 min-h-[280px] famledger-chart-panel">
                            <div id="famledger_income_expense_chart" class="w-full" style="min-height: 260px;"></div>
                        </div>
                    </div>
                    <div class="kt-card famledger-chart-card flex flex-col dashboard-pulse-kt-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-border">
                            <h3 class="dashboard-pulse-section-title text-base mb-0.5">{{ __('Expenses by Category') }}</h3>
                            <p class="text-sm text-muted-foreground mt-0.5">{{ __('This month') }}</p>
                        </div>
                        <div class="p-4 flex items-center justify-center min-h-[280px] famledger-chart-panel">
                            <div id="famledger_category_chart" class="w-full max-w-[260px] mx-auto" style="min-height: 260px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-pulse-frame shrink-0 min-w-0 max-w-full">
            <div class="dashboard-pulse-card-inner min-w-0 max-w-full overflow-hidden p-0">
                <div class="kt-card dashboard-pulse-kt-card border-0 shadow-none rounded-[18px] overflow-hidden bg-transparent">
                    <div class="px-5 py-4 border-b border-border flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h3 class="dashboard-pulse-section-title text-base mb-0.5">{{ __('Recent Activity') }}</h3>
                            <p class="text-sm text-muted-foreground mt-0.5">{{ __('Latest transactions') }}</p>
                        </div>
                        @if (isset($currentFamily) && $currentFamily)
                            <a href="{{ route('families.transactions.index') }}" class="dashboard-pulse-btn-outline-sm shrink-0">{{ __('Transactions') }}</a>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/30">
                                    <th class="text-start font-medium text-muted-foreground px-5 py-3">{{ __('Description') }}</th>
                                    <th class="text-start font-medium text-muted-foreground px-5 py-3">{{ __('Category') }}</th>
                                    <th class="text-end font-medium text-muted-foreground px-5 py-3">{{ __('Amount') }}</th>
                                    <th class="text-end font-medium text-muted-foreground px-5 py-3">{{ __('Date') }}</th>
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
                                    <td colspan="4" class="px-5 py-8 text-center text-muted-foreground">{{ __('No transactions yet.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lightweight auto-refresh so dashboard stats stay up to date
    setInterval(function () {
        if (document.visibilityState === 'visible') {
            window.location.reload();
        }
    }, 60000); // 1 minute

    // Clickable KPI cards -> SweetAlert2 modals with more detail
    try {
        var dashboardCards = document.querySelectorAll('.js-dashboard-card');
        dashboardCards.forEach(function(card) {
            card.addEventListener('click', function () {
                var type = card.getAttribute('data-card-type');
                var currency = @json($currency);
                var totalIncome = @json($totalIncome);
                var totalExpenses = @json($totalExpenses);
                var totalSavings = @json($totalSavings);
                var budgetUsedPercent = @json($budgetUsedPercent);
                var budgetUsedLabel = @json($budgetUsedLabel);
                var projectCount = @json($projectCount ?? 0);
                var activeProjectCount = @json($activeProjectCount ?? 0);
                var propertyCount = @json($propertyCount ?? 0);
                var propertyTotalValue = @json($propertyTotalValue ?? 0);

                var title = '';
                var html = '';

                switch (type) {
                    case 'income':
                        title = 'Total Income (This month)';
                        html = '<p class="text-sm mb-2">Sum of all income recorded this month across your families.</p>' +
                               '<p class="text-lg font-semibold">' + (totalIncome || 0).toLocaleString() + ' ' + currency + '</p>';
                        break;
                    case 'expenses':
                        title = 'Total Expenses (This month)';
                        html = '<p class="text-sm mb-2">Sum of all expenses recorded this month across your families.</p>' +
                               '<p class="text-lg font-semibold">' + (totalExpenses || 0).toLocaleString() + ' ' + currency + '</p>';
                        break;
                    case 'budget':
                        title = 'Budget Used (This month)';
                        html = '<p class="text-sm mb-2">Usage across all active budgets in the current family and month.</p>' +
                               '<p class="text-lg font-semibold">' + (budgetUsedPercent || 0) + '%</p>' +
                               '<p class="text-sm text-muted-foreground mt-1">' + (budgetUsedLabel || '') + '</p>';
                        break;
                    case 'wallets':
                        title = 'Total Wallet Balance';
                        html = '<p class="text-sm mb-2">All family wallets: initial balance + income − expenses ± transfers.</p>' +
                               '<p class="text-lg font-semibold">' + (totalSavings || 0).toLocaleString() + ' ' + currency + '</p>';
                        break;
                    case 'projects':
                        title = 'Projects Overview';
                        html = '<p class="text-sm mb-2">Projects for the current family.</p>' +
                               '<p class="text-lg font-semibold mb-1">' + (projectCount || 0) + ' projects</p>' +
                               '<p class="text-sm text-muted-foreground">' + (activeProjectCount || 0) + ' active right now.</p>';
                        break;
                    case 'properties':
                        title = 'Properties Overview';
                        html = '<p class="text-sm mb-2">Properties for the current family.</p>' +
                               '<p class="text-lg font-semibold mb-1">' + (propertyCount || 0) + ' properties</p>' +
                               '<p class="text-sm text-muted-foreground">Total value ' + (propertyTotalValue || 0).toLocaleString() + ' ' + currency + ' (purchase/estimated).</p>';
                        break;
                    default:
                        return;
                }

                if (typeof window.swalAlert === 'function') {
                    window.swalAlert({
                        title: title,
                        html: html,
                        icon: 'info',
                        confirmButtonText: 'Close',
                    });
                } else if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        title: title,
                        html: html,
                        icon: 'info',
                        confirmButtonText: 'Close',
                    });
                } else {
                    // Fallback if SweetAlert2 is not available
                    window.alert(title + '\n\n' + html.replace(/<[^>]+>/g, ' '));
                }
            });
        });
    } catch (e) {
        console.error(e);
    }

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
