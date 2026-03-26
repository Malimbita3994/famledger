@extends('layouts.metronic')

@section('title', __('Super Admin Dashboard'))
@section('page_title', __('Platform Dashboard'))

@php
    $currency = isset($currency) ? $currency : config('currencies.default', 'TZS');
    $fmt = function ($n, $code = null) use ($currency) {
        return number_format((float) $n, 0) . ' ' . ($code ?? $currency);
    };
@endphp

@push('styles')
<style>
    .admin-dash-pulse {
        --ad-accent: #009ef7;
        --ad-accent-2: #0ea5e9;
    }
    .admin-dash-eyebrow {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .admin-dash-title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: var(--ad-accent);
    }
    .admin-dash-sub {
        font-size: 0.875rem;
        line-height: 1.55;
        color: #64748b;
        margin-top: 0.35rem;
        max-width: 42rem;
    }
    .dark .admin-dash-sub {
        color: #94a3b8;
    }
    .admin-dash-kpi {
        display: block;
        border-radius: 16px;
        border: 1px solid rgba(14, 165, 233, 0.18);
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%);
        padding: 1.4rem 1.5rem;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    a.admin-dash-kpi:hover {
        border-color: rgba(0, 158, 247, 0.4);
        box-shadow: 0 12px 32px rgba(0, 158, 247, 0.12);
        transform: translateY(-2px);
    }
    .dark .admin-dash-kpi {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.9) 0%, rgb(15 23 42 / 0.95) 100%);
        border-color: rgba(14, 165, 233, 0.22);
    }
    .admin-dash-kpi-label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #64748b;
    }
    .admin-dash-kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #0f172a;
        margin-top: 0.25rem;
    }
    .dark .admin-dash-kpi-value {
        color: #f1f5f9;
    }
    .admin-dash-kpi-icon {
        border-radius: 9999px;
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .admin-dash-kpi-icon--accent {
        background: rgba(0, 158, 247, 0.12);
        color: var(--ad-accent);
    }
    .admin-dash-kpi-icon--green {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }
    .admin-dash-kpi-icon--blue {
        background: rgba(14, 165, 233, 0.15);
        color: var(--ad-accent-2);
    }
    .admin-dash-panel {
        border-radius: 1rem;
        border: 1px solid rgba(14, 165, 233, 0.15);
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .dark .admin-dash-panel {
        background: rgb(15 23 42 / 0.5);
        border-color: rgba(14, 165, 233, 0.2);
    }
    .admin-dash-panel-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(14, 165, 233, 0.12);
        background: rgba(240, 249, 255, 0.55);
    }
    .dark .admin-dash-panel-header {
        background: rgba(14, 165, 233, 0.08);
        border-bottom-color: rgba(51, 65, 85, 0.6);
    }
    .admin-dash-panel-title {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--ad-accent);
    }
    .admin-dash-link {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--ad-accent) !important;
    }
    .admin-dash-link:hover {
        color: var(--ad-accent-2) !important;
        text-decoration: underline;
    }
    /* Grids use explicit CSS — Metronic bundle may not include Tailwind gap/grid utilities */
    .admin-dash-pulse .admin-dash-grid-kpi {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }
    @media (min-width: 640px) {
        .admin-dash-pulse .admin-dash-grid-kpi {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.35rem;
        }
    }
    @media (min-width: 1024px) {
        .admin-dash-pulse .admin-dash-grid-kpi {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 1.35rem;
        }
    }
    .admin-dash-pulse .admin-dash-grid-2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.75rem;
        margin-bottom: 2.5rem;
    }
    @media (min-width: 1024px) {
        .admin-dash-pulse .admin-dash-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 2rem;
        }
    }
    .admin-dash-pulse .admin-dash-grid-2--flush {
        margin-bottom: 0;
    }
    .admin-dash-panel-body {
        padding: 1.5rem 1.75rem;
    }
    @media (min-width: 640px) {
        .admin-dash-panel-body {
            padding: 1.75rem 2rem;
        }
    }
    .admin-dash-panel-footer {
        padding: 0.65rem 1.75rem 1.35rem;
    }
    @media (min-width: 640px) {
        .admin-dash-panel-footer {
            padding-left: 2rem;
            padding-right: 2rem;
            padding-bottom: 1.5rem;
        }
    }
    .admin-dash-panel-body--stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem 1.5rem;
    }
    @media (min-width: 640px) {
        .admin-dash-panel-body--stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .admin-dash-panel-body--stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .admin-dash-panel-body--stack-sm {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }
    .admin-dash-pulse .admin-dash-panel-chart {
        margin-bottom: 2.5rem;
    }
    .admin-stats-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.75rem;
        margin-bottom: 2.5rem;
    }
    @media (min-width: 900px) {
        .admin-stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 2rem;
        }
    }
    .admin-dash-list-row {
        padding: 0.85rem 1.75rem;
    }
    @media (min-width: 640px) {
        .admin-dash-list-row {
            padding-left: 2rem;
            padding-right: 2rem;
        }
    }
    .admin-dash-list-empty {
        padding: 1.75rem 1.75rem;
    }
    @media (min-width: 640px) {
        .admin-dash-list-empty {
            padding: 2rem 2rem;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        a.admin-dash-kpi:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-dash-pulse kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-14">
    <div class="mb-10">
        <p class="admin-dash-eyebrow mb-1.5">{{ __('Platform administration') }}</p>
        <h1 class="admin-dash-title">{{ __('Platform owner dashboard') }}</h1>
        <p class="admin-dash-sub">{{ __('System health, families, users, and financial ecosystem overview. Default currency: :currency.', ['currency' => $currency]) }}</p>
    </div>

    {{-- Top summary cards --}}
    <div class="admin-dash-grid-kpi">
        <a href="{{ route('admin.users.index') }}" class="admin-dash-kpi">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="admin-dash-kpi-label">{{ __('Total families') }}</p>
                    <p class="admin-dash-kpi-value tabular-nums">{{ $totalFamilies ?? 0 }}</p>
                </div>
                <span class="admin-dash-kpi-icon admin-dash-kpi-icon--accent"><i class="ki-filled ki-profile-circle text-xl"></i></span>
            </div>
        </a>
        <a href="{{ route('admin.users.index') }}" class="admin-dash-kpi">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="admin-dash-kpi-label">{{ __('Total users') }}</p>
                    <p class="admin-dash-kpi-value tabular-nums">{{ $totalUsers ?? 0 }}</p>
                </div>
                <span class="admin-dash-kpi-icon admin-dash-kpi-icon--accent"><i class="ki-filled ki-people text-xl"></i></span>
            </div>
        </a>
        <div class="admin-dash-kpi" style="cursor: default;">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="admin-dash-kpi-label">{{ __('Active families') }}</p>
                    <p class="admin-dash-kpi-value tabular-nums text-emerald-600 dark:text-emerald-400">{{ $activeFamilies ?? 0 }}</p>
                </div>
                <span class="admin-dash-kpi-icon admin-dash-kpi-icon--green"><i class="ki-filled ki-check-circle text-xl"></i></span>
            </div>
        </div>
        <div class="admin-dash-kpi" style="cursor: default;">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="admin-dash-kpi-label">{{ __('Total wallet balance') }}</p>
                    <p class="text-lg font-bold tabular-nums mt-1 text-foreground">{{ $fmt($totalWalletBalance ?? 0) }}</p>
                </div>
                <span class="admin-dash-kpi-icon admin-dash-kpi-icon--green"><i class="ki-filled ki-wallet text-xl"></i></span>
            </div>
        </div>
        <div class="admin-dash-kpi" style="cursor: default;">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="admin-dash-kpi-label">{{ __('Transactions today') }}</p>
                    <p class="admin-dash-kpi-value tabular-nums">{{ $transactionsToday ?? 0 }}</p>
                </div>
                <span class="admin-dash-kpi-icon admin-dash-kpi-icon--blue"><i class="ki-filled ki-chart-line text-xl"></i></span>
            </div>
        </div>
    </div>

    {{-- Families & Users overview --}}
    <div class="admin-dash-grid-2">
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <h2 class="admin-dash-panel-title">{{ __('Families overview') }}</h2>
                <p class="text-sm text-muted-foreground mt-0.5">{{ __('Platform families') }}</p>
            </div>
            <div class="admin-dash-panel-body admin-dash-panel-body--stats">
                <div><p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Total') }}</p><p class="text-lg font-semibold">{{ $totalFamilies ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Active') }}</p><p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $activeFamilies ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('New this month') }}</p><p class="text-lg font-semibold">{{ $newFamiliesThisMonth ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Inactive (30d)') }}</p><p class="text-lg font-semibold text-amber-600">{{ $inactiveFamilies ?? 0 }}</p></div>
            </div>
            <div class="admin-dash-panel-footer"><a href="{{ route('families.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost admin-dash-link">{{ __('View families') }}</a></div>
        </div>
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <h2 class="admin-dash-panel-title">{{ __('Users overview') }}</h2>
                <p class="text-sm text-muted-foreground mt-0.5">{{ __('System-wide users') }}</p>
            </div>
            <div class="admin-dash-panel-body admin-dash-panel-body--stats">
                <div><p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Total') }}</p><p class="text-lg font-semibold">{{ $totalUsers ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Active') }}</p><p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $activeUsers ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('New this month') }}</p><p class="text-lg font-semibold">{{ $newUsersThisMonth ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Suspended / locked') }}</p><p class="text-lg font-semibold text-destructive">{{ $suspendedUsers ?? 0 }}</p></div>
            </div>
            <div class="admin-dash-panel-footer"><span class="text-sm text-muted-foreground">{{ __('Avg members per family: :n', ['n' => $usersPerFamily ?? 0]) }}</span> · <a href="{{ route('admin.users.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost admin-dash-link">{{ __('Manage users') }}</a></div>
        </div>
    </div>

    {{-- Financial & transaction activity --}}
    <div class="admin-dash-grid-2">
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <h2 class="admin-dash-panel-title">{{ __('Financial activity (aggregated)') }}</h2>
                <p class="text-sm text-muted-foreground mt-0.5">{{ __('Platform totals — no per-family detail') }}</p>
            </div>
            <div class="admin-dash-panel-body admin-dash-panel-body--stack">
                <div class="flex justify-between gap-2"><span class="text-muted-foreground">{{ __('Total income (all time)') }}</span><span class="font-medium text-emerald-600 dark:text-emerald-400 shrink-0">{{ $fmt($totalIncomePlatform ?? 0) }}</span></div>
                <div class="flex justify-between gap-2"><span class="text-muted-foreground">{{ __('Total expenses (all time)') }}</span><span class="font-medium text-destructive shrink-0">{{ $fmt($totalExpensesPlatform ?? 0) }}</span></div>
                <div class="flex justify-between gap-2"><span class="text-muted-foreground">{{ __('Net flow') }}</span><span class="font-medium shrink-0">{{ $fmt($netFlow ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 pt-2 border-t border-sky-100/80 dark:border-slate-600/50"><span class="text-muted-foreground">{{ __('Income this month') }}</span><span class="font-medium shrink-0">{{ $fmt($incomeThisMonth ?? 0) }}</span></div>
                <div class="flex justify-between gap-2"><span class="text-muted-foreground">{{ __('Expenses this month') }}</span><span class="font-medium shrink-0">{{ $fmt($expensesThisMonth ?? 0) }}</span></div>
            </div>
        </div>
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <h2 class="admin-dash-panel-title">{{ __('Transaction activity') }}</h2>
                <p class="text-sm text-muted-foreground mt-0.5">{{ __('Usage statistics') }}</p>
            </div>
            <div class="admin-dash-panel-body admin-dash-panel-body--stack">
                <div class="flex justify-between gap-2"><span class="text-muted-foreground">{{ __('Income transactions') }}</span><span class="font-medium shrink-0">{{ number_format($totalIncomeTransactions ?? 0) }}</span></div>
                <div class="flex justify-between gap-2"><span class="text-muted-foreground">{{ __('Expense transactions') }}</span><span class="font-medium shrink-0">{{ number_format($totalExpenseTransactions ?? 0) }}</span></div>
                <div class="flex justify-between gap-2"><span class="text-muted-foreground">{{ __('Transfers') }}</span><span class="font-medium shrink-0">{{ number_format($totalTransfers ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 pt-2 border-t border-sky-100/80 dark:border-slate-600/50"><span class="text-muted-foreground">{{ __('Today') }}</span><span class="font-semibold shrink-0">{{ $transactionsToday ?? 0 }}</span></div>
                <div class="flex justify-between gap-2"><span class="text-muted-foreground">{{ __('This month') }}</span><span class="font-semibold shrink-0">{{ $transactionsThisMonth ?? 0 }}</span></div>
            </div>
        </div>
    </div>

    {{-- Wallets, Budgets, Savings --}}
    <div class="admin-stats-grid">
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <h2 class="admin-dash-panel-title">{{ __('Wallet statistics') }}</h2>
            </div>
            <div class="admin-dash-panel-body admin-dash-panel-body--stack-sm">
                <p class="text-2xl font-bold text-foreground">{{ $totalWallets ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ __('Total wallets · Avg :n per family', ['n' => $avgWalletsPerFamily ?? 0]) }}</p>
            </div>
        </div>
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <h2 class="admin-dash-panel-title">{{ __('Budget statistics') }}</h2>
            </div>
            <div class="admin-dash-panel-body admin-dash-panel-body--stack-sm">
                <p class="text-2xl font-bold text-foreground">{{ $activeBudgets ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ __('Active budgets') }} · <span class="text-destructive font-semibold">{{ $overBudgetCount ?? 0 }}</span> {{ __('over budget') }}</p>
                @if (isset($avgBudgetAmount) && $avgBudgetAmount > 0)
                    <p class="text-xs text-muted-foreground">{{ __('Avg budget: :amt', ['amt' => $fmt($avgBudgetAmount)]) }}</p>
                @endif
            </div>
        </div>
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <h2 class="admin-dash-panel-title">{{ __('Savings goals') }}</h2>
            </div>
            <div class="admin-dash-panel-body admin-dash-panel-body--stack-sm">
                <p class="text-2xl font-bold text-foreground">{{ $totalSavingsGoals ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ __(':c completed · :o overdue', ['c' => $completedGoals ?? 0, 'o' => $overdueGoals ?? 0]) }}</p>
                <p class="text-sm font-medium">{{ __('Accumulated: :amt', ['amt' => $fmt($totalSavingsAccumulated ?? 0)]) }}</p>
            </div>
        </div>
    </div>

    {{-- Growth chart --}}
    <div class="admin-dash-panel admin-dash-panel-chart">
        <div class="admin-dash-panel-header">
            <h2 class="admin-dash-panel-title">{{ __('Growth trends') }}</h2>
            <p class="text-sm text-muted-foreground mt-0.5">{{ __('Cumulative users, families, and transaction volume (last 6 months)') }}</p>
        </div>
        <div class="admin-dash-panel-body" style="min-height: 300px;">
            <div id="admin_growth_chart" class="w-full" style="min-height: 280px;"></div>
        </div>
    </div>

    {{-- Recent events --}}
    <div class="admin-dash-grid-2 admin-dash-grid-2--flush">
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header flex items-center justify-between gap-2">
                <h2 class="admin-dash-panel-title mb-0">{{ __('Recent families') }}</h2>
                <a href="{{ route('families.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost admin-dash-link shrink-0">{{ __('View all') }}</a>
            </div>
            <div class="divide-y divide-sky-100/60 dark:divide-slate-600/50">
                @forelse ($recentFamilies ?? [] as $f)
                    <div class="admin-dash-list-row flex items-center justify-between gap-2">
                        <span class="font-medium truncate">{{ $f->name }}</span>
                        <span class="text-sm text-muted-foreground shrink-0">{{ $f->created_at?->format('M j, Y') }}</span>
                    </div>
                @empty
                    <div class="admin-dash-list-empty text-center text-muted-foreground text-sm">{{ __('No families yet.') }}</div>
                @endforelse
            </div>
        </div>
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header flex items-center justify-between gap-2">
                <h2 class="admin-dash-panel-title mb-0">{{ __('Recent users') }}</h2>
                <a href="{{ route('admin.users.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost admin-dash-link shrink-0">{{ __('View all') }}</a>
            </div>
            <div class="divide-y divide-sky-100/60 dark:divide-slate-600/50">
                @forelse ($recentUsers ?? [] as $u)
                    <div class="admin-dash-list-row flex items-center justify-between gap-2">
                        <div class="min-w-0"><p class="font-medium truncate">{{ $u->name }}</p><p class="text-xs text-muted-foreground truncate">{{ $u->email }}</p></div>
                        <span class="text-sm text-muted-foreground shrink-0">{{ $u->created_at?->format('M j, Y') }}</span>
                    </div>
                @empty
                    <div class="admin-dash-list-empty text-center text-muted-foreground text-sm">{{ __('No users yet.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ApexCharts === 'undefined') return;
    var el = document.getElementById('admin_growth_chart');
    if (!el) return;
    var months = @json($chartMonths ?? []);
    var userData = @json($userGrowthData ?? []);
    var familyData = @json($familyGrowthData ?? []);
    var txnData = @json($txnGrowthData ?? []);
    if (!months.length) return;
    new ApexCharts(el, {
        series: [
            { name: @json(__('Users (cumulative)')), data: userData },
            { name: @json(__('Families (cumulative)')), data: familyData },
            { name: @json(__('Transactions (monthly)')), data: txnData }
        ],
        chart: { type: 'line', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#009ef7', '#10b981', '#0ea5e9'],
        legend: { position: 'top', horizontalAlign: 'right', labels: { colors: 'var(--color-muted-foreground)' } },
        xaxis: {
            categories: months,
            labels: { style: { colors: 'var(--color-muted-foreground)', fontSize: '12px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: { style: { colors: 'var(--color-muted-foreground)', fontSize: '12px' } },
            axisTicks: { show: false }
        },
        grid: {
            borderColor: 'var(--color-border)',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } }
        },
        tooltip: { theme: 'dark' }
    }).render();
});
</script>
@endsection
