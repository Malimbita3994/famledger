@extends('layouts.metronic')

@section('title', config('app.name'))
@section('page_title', __('Super Admin overview'))

@php
    $currency = isset($currency) ? $currency : config('currencies.default', 'TZS');
    $fmt = function ($n, $code = null) use ($currency) {
        return number_format((float) $n, 0) . ' ' . ($code ?? $currency);
    };
@endphp

@push('styles')
<style>
    .families-pulse-hero {
        border: 1px solid rgba(14, 165, 233, 0.2);
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(0, 158, 247, 0.04) 55%, rgba(255, 255, 255, 0.9) 100%);
        border-radius: 1rem;
        box-shadow: 0 8px 26px rgba(15, 23, 42, 0.06);
    }
    .families-pulse-hero .kt-card-content {
        padding-inline: 1.75rem !important;
    }
    .admin-dash-top-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        width: 100%;
        margin-bottom: 1.5rem;
    }
    @media (min-width: 768px) {
        .admin-dash-top-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (min-width: 1280px) {
        .admin-dash-top-stats {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }
    .admin-dash-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem 1.25rem;
    }
    @media (min-width: 640px) {
        .admin-dash-stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .admin-dash-two-col {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    @media (min-width: 1024px) {
        .admin-dash-two-col {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    .admin-dash-three-col {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    @media (min-width: 768px) {
        .admin-dash-three-col {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .admin-dash-two-col--last {
        margin-bottom: 0;
    }
    /* ApexCharts in CSS grid: keep SVG inside card (min-w-0 chain + max-width on canvas) */
    .admin-dash-chart-card {
        min-width: 0;
        max-width: 100%;
    }
    .admin-dash-chart-wrap {
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
    }
    .admin-dash-chart-wrap .apexcharts-canvas,
    .admin-dash-chart-wrap svg {
        max-width: 100% !important;
    }
    .admin-dash-kpi-card {
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .admin-dash-kpi-card:hover {
        border-color: rgba(0, 158, 247, 0.35) !important;
        box-shadow: 0 8px 24px rgba(0, 158, 247, 0.1);
        transform: translateY(-1px);
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-dash-kpi-card:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
{{-- Toolbar (same pattern as /families) --}}
<div class="pb-5">
    <div class="kt-container-fixed families-pulse-hero py-4 sm:py-5">
        <div class="kt-card-content flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center flex-wrap gap-1 lg:gap-5">
                <div>
                    <p class="fin-pulse-eyebrow mb-1">{{ __('Admin') }}</p>
                    <h1 class="font-semibold text-xl text-mono leading-tight">{{ __('Super Admin overview') }}</h1>
                </div>
                <div class="flex items-center gap-1 text-sm font-normal">
                    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground text-sm">/</span>
                    <span class="text-mono">{{ __('Admin') }}</span>
                </div>
            </div>
            <div class="flex items-center flex-wrap gap-1.5 lg:gap-3.5">
                <x-famledger.pulse-button variant="outline" :href="route('families.index')">
                    <i class="ki-filled ki-people"></i>
                    {{ __('Families') }}
                </x-famledger.pulse-button>
                <x-famledger.pulse-button variant="primary" :href="route('admin.users.index')">
                    <i class="ki-filled ki-badge"></i>
                    {{ __('Users') }}
                </x-famledger.pulse-button>
            </div>
        </div>
    </div>
</div>

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="admin-dash-top-stats">
        <x-famledger.pulse-stat-card
            class="rounded-xl admin-dash-kpi-card js-admin-stat-card"
            data-card-type="families"
            :label="__('Families registered')"
            :value="$totalFamilies ?? 0"
        />
        <x-famledger.pulse-stat-card
            class="rounded-xl admin-dash-kpi-card js-admin-stat-card"
            data-card-type="users"
            :label="__('User accounts')"
            :value="$totalUsers ?? 0"
        />
        <x-famledger.pulse-stat-card
            class="rounded-xl admin-dash-kpi-card js-admin-stat-card"
            data-card-type="active_families"
            :label="__('Families (active status)')"
            :value="$activeFamilies ?? 0"
        >
            <span class="famledger-pulse-stat-card__extra" style="color:#16a34a;font-weight:600;">{{ __('Active') }}</span>
        </x-famledger.pulse-stat-card>
        <x-famledger.pulse-stat-card
            class="rounded-xl admin-dash-kpi-card js-admin-stat-card"
            data-card-type="wallet_balance"
            :label="__('Sum of wallet balances')"
            :value="$fmt($totalWalletBalance ?? 0)"
        />
        <x-famledger.pulse-stat-card
            class="rounded-xl admin-dash-kpi-card js-admin-stat-card"
            data-card-type="ledger_today"
            :label="__('Ledger entries today')"
            :value="$transactionsToday ?? 0"
        />
    </div>

    <div class="admin-dash-two-col">
        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header flex-wrap gap-2">
                <div>
                    <h3 class="kt-card-title text-sm">{{ __('Families') }}</h3>
                    <p class="text-xs text-muted-foreground mt-0.5 font-normal">{{ __('Registration and activity') }}</p>
                </div>
                <a href="{{ route('families.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary shrink-0">{{ __('View families') }}</a>
            </div>
            <div class="kt-card-content pt-2">
                <div class="admin-dash-stat-grid">
                    <div>
                        <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Total') }}</p>
                        <p class="text-lg font-semibold text-foreground">{{ $totalFamilies ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Active status') }}</p>
                        <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $activeFamilies ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('New this month') }}</p>
                        <p class="text-lg font-semibold text-foreground">{{ $newFamiliesThisMonth ?? 0 }}</p>
                    </div>
                    <div title="{{ __('No income, expense, or transfer in the last 30 days') }}">
                        <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('No activity (30d)') }}</p>
                        <p class="text-lg font-semibold text-amber-600">{{ $inactiveFamilies ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header flex-wrap gap-2">
                <div>
                    <h3 class="kt-card-title text-sm">{{ __('Users') }}</h3>
                    <p class="text-xs text-muted-foreground mt-0.5 font-normal">{{ __('All accounts') }}</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary shrink-0">{{ __('Manage users') }}</a>
            </div>
            <div class="kt-card-content pt-2">
                <div class="admin-dash-stat-grid">
                    <div>
                        <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Total') }}</p>
                        <p class="text-lg font-semibold text-foreground">{{ $totalUsers ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Active') }}</p>
                        <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $activeUsers ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('New this month') }}</p>
                        <p class="text-lg font-semibold text-foreground">{{ $newUsersThisMonth ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Suspended / locked') }}</p>
                        <p class="text-lg font-semibold text-destructive">{{ $suspendedUsers ?? 0 }}</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground mt-4 pt-4 border-t border-border">{{ __('Avg memberships per family: :n', ['n' => $usersPerFamily ?? 0]) }}</p>
            </div>
        </div>
    </div>

    <div class="admin-dash-two-col">
        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">{{ __('Money recorded (aggregated)') }}</h3>
                <p class="text-xs text-muted-foreground mt-1 font-normal">{{ __('Income and expense amounts across all families') }}</p>
            </div>
            <div class="kt-card-content space-y-3 pt-2">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">{{ __('All time') }}</p>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Income total') }}</span><span class="font-medium text-emerald-600 dark:text-emerald-400 shrink-0 tabular-nums">{{ $fmt($totalIncomePlatform ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Expense total') }}</span><span class="font-medium text-destructive shrink-0 tabular-nums">{{ $fmt($totalExpensesPlatform ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Net (income − expenses)') }}</span><span class="font-medium shrink-0 tabular-nums">{{ $fmt($netFlow ?? 0) }}</span></div>

                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground pt-2 border-t border-border">{{ __('Financial year :fy', ['fy' => $financialYearLabel ?? '']) }}</p>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Income') }}</span><span class="font-medium shrink-0 tabular-nums">{{ $fmt($incomeThisFY ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Expenses') }}</span><span class="font-medium shrink-0 tabular-nums">{{ $fmt($expensesThisFY ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Net') }}</span><span class="font-medium shrink-0 tabular-nums">{{ $fmt($netFlowThisFY ?? 0) }}</span></div>

                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground pt-2 border-t border-border">{{ __('This calendar month') }}</p>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Income') }}</span><span class="font-medium shrink-0 tabular-nums">{{ $fmt($incomeThisMonth ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Expenses') }}</span><span class="font-medium shrink-0 tabular-nums">{{ $fmt($expensesThisMonth ?? 0) }}</span></div>
            </div>
        </div>

        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">{{ __('Ledger volume (row counts)') }}</h3>
                <p class="text-xs text-muted-foreground mt-1 font-normal">{{ __('Record counts, not currency') }}</p>
            </div>
            <div class="kt-card-content space-y-3 pt-2">
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Income records (all time)') }}</span><span class="font-medium shrink-0 tabular-nums">{{ number_format($totalIncomeTransactions ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Expense records (all time)') }}</span><span class="font-medium shrink-0 tabular-nums">{{ number_format($totalExpenseTransactions ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Transfers (all time)') }}</span><span class="font-medium shrink-0 tabular-nums">{{ number_format($totalTransfers ?? 0) }}</span></div>
                <div class="flex justify-between gap-2 text-sm pt-2 border-t border-border"><span class="text-muted-foreground">{{ __('Entries dated today') }}</span><span class="font-semibold shrink-0 tabular-nums">{{ $transactionsToday ?? 0 }}</span></div>
                <div class="flex justify-between gap-2 text-sm"><span class="text-muted-foreground">{{ __('Entries this calendar month') }}</span><span class="font-semibold shrink-0 tabular-nums">{{ $transactionsThisMonth ?? 0 }}</span></div>
            </div>
        </div>
    </div>

    <div class="admin-dash-three-col">
        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">{{ __('Wallets') }}</h3>
                <p class="text-xs text-muted-foreground mt-1 font-normal">{{ __('Active vs dormant (90d)') }}</p>
            </div>
            <div class="kt-card-content pt-2 space-y-2">
                <p class="text-2xl font-bold text-foreground tabular-nums">{{ $totalWallets ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ __('Total · avg :n per family', ['n' => $avgWalletsPerFamily ?? 0]) }}</p>
                <p class="text-sm text-foreground">{{ __('Active: :a · Dormant: :d', ['a' => $activeWallets ?? 0, 'd' => $dormantWallets ?? 0]) }}</p>
            </div>
        </div>
        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">{{ __('Budgets') }}</h3>
                <p class="text-xs text-muted-foreground mt-1 font-normal">{{ __('Current month overlap') }}</p>
            </div>
            <div class="kt-card-content pt-2 space-y-2">
                <p class="text-2xl font-bold text-foreground tabular-nums">{{ $activeBudgets ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ __('Active this month') }} · <span class="text-destructive font-semibold">{{ $overBudgetCount ?? 0 }}</span> {{ __('exceeded') }}</p>
                @if (isset($avgBudgetAmount) && $avgBudgetAmount > 0)
                    <p class="text-xs text-muted-foreground">{{ __('Avg budget: :amt', ['amt' => $fmt($avgBudgetAmount)]) }}</p>
                @endif
            </div>
        </div>
        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">{{ __('Savings goals') }}</h3>
                <p class="text-xs text-muted-foreground mt-1 font-normal">{{ __('Goals and contributions') }}</p>
            </div>
            <div class="kt-card-content pt-2 space-y-2">
                <p class="text-2xl font-bold text-foreground tabular-nums">{{ $totalSavingsGoals ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ __(':c completed · :o overdue', ['c' => $completedGoals ?? 0, 'o' => $overdueGoals ?? 0]) }}</p>
                <p class="text-sm font-medium text-foreground">{{ __('Contributions: :amt', ['amt' => $fmt($totalSavingsAccumulated ?? 0)]) }}</p>
            </div>
        </div>
    </div>

    <div class="admin-dash-two-col">
        <div class="admin-dash-chart-card kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0 max-w-full">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">{{ __('Accounts (cumulative)') }}</h3>
                <p class="text-xs text-muted-foreground mt-1 font-normal">{{ __('Last 6 months') }}</p>
            </div>
            <div class="kt-card-content pt-2 min-w-0 overflow-hidden">
                <div id="admin_chart_accounts" class="admin-dash-chart-wrap w-full max-w-full min-h-[260px]"></div>
            </div>
        </div>
        <div class="admin-dash-chart-card kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0 max-w-full">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">{{ __('Monthly ledger activity') }}</h3>
                <p class="text-xs text-muted-foreground mt-1 font-normal">{{ __('Entries per month') }}</p>
            </div>
            <div class="kt-card-content pt-2 min-w-0 overflow-hidden">
                <div id="admin_chart_txns" class="admin-dash-chart-wrap w-full max-w-full min-h-[260px]"></div>
            </div>
        </div>
    </div>

    <div class="admin-dash-two-col admin-dash-two-col--last">
        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header flex items-center justify-between gap-2 flex-wrap">
                <h3 class="kt-card-title text-sm mb-0">{{ __('Recent families') }}</h3>
                <a href="{{ route('families.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary shrink-0">{{ __('View all') }}</a>
            </div>
            <div class="divide-y divide-border">
                @forelse ($recentFamilies ?? [] as $f)
                    <div class="px-5 py-3 flex items-center justify-between gap-2 sm:px-6">
                        <span class="font-medium text-sm truncate text-foreground">{{ $f->name }}</span>
                        <span class="text-sm text-muted-foreground shrink-0 tabular-nums">{{ $f->created_at?->format('M j, Y') }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-muted-foreground text-sm sm:px-6">{{ __('No families yet.') }}</div>
                @endforelse
            </div>
        </div>
        <div class="kt-card fin-pulse-kt-card rounded-xl border border-border shadow-sm overflow-hidden min-w-0">
            <div class="kt-card-header flex items-center justify-between gap-2 flex-wrap">
                <h3 class="kt-card-title text-sm mb-0">{{ __('Recent users') }}</h3>
                <a href="{{ route('admin.users.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary shrink-0">{{ __('View all') }}</a>
            </div>
            <div class="divide-y divide-border">
                @forelse ($recentUsers ?? [] as $u)
                    <div class="px-5 py-3 flex items-center justify-between gap-2 sm:px-6">
                        <div class="min-w-0">
                            <p class="font-medium text-sm truncate text-foreground">{{ $u->name }}</p>
                            <p class="text-xs text-muted-foreground truncate">{{ $u->email }}</p>
                        </div>
                        <span class="text-sm text-muted-foreground shrink-0 tabular-nums">{{ $u->created_at?->format('M j, Y') }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-muted-foreground text-sm sm:px-6">{{ __('No users yet.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Top KPI cards -> SweetAlert2 (same pattern as /dashboard)
    try {
        var familiesUrl = @json(route('families.index'));
        var usersUrl = @json(route('admin.users.index'));
        var totalFamilies = @json($totalFamilies ?? 0);
        var totalUsers = @json($totalUsers ?? 0);
        var activeFamilies = @json($activeFamilies ?? 0);
        var totalWalletBalanceFormatted = @json($fmt($totalWalletBalance ?? 0));
        var transactionsToday = @json($transactionsToday ?? 0);

        document.querySelectorAll('.js-admin-stat-card').forEach(function (card) {
            card.addEventListener('click', function () {
                var type = card.getAttribute('data-card-type');
                var title = '';
                var html = '';

                switch (type) {
                    case 'families':
                        title = @json(__('Families registered'));
                        html =
                            '<p class="text-sm mb-2 text-start">' +
                            @json(__('All family workspaces ever created on the platform.')) +
                            '</p>' +
                            '<p class="text-lg font-semibold text-start">' +
                            (totalFamilies || 0).toLocaleString() +
                            '</p>' +
                            '<p class="mt-3 text-start"><a class="text-primary font-semibold hover:underline" href="' +
                            familiesUrl +
                            '">' +
                            @json(__('View families')) +
                            '</a></p>';
                        break;
                    case 'users':
                        title = @json(__('User accounts'));
                        html =
                            '<p class="text-sm mb-2 text-start">' +
                            @json(__('Registered users across all families (every account status).')) +
                            '</p>' +
                            '<p class="text-lg font-semibold text-start">' +
                            (totalUsers || 0).toLocaleString() +
                            '</p>' +
                            '<p class="mt-3 text-start"><a class="text-primary font-semibold hover:underline" href="' +
                            usersUrl +
                            '">' +
                            @json(__('Manage users')) +
                            '</a></p>';
                        break;
                    case 'active_families':
                        title = @json(__('Families (active status)'));
                        html =
                            '<p class="text-sm mb-2 text-start">' +
                            @json(__('Families whose status is currently marked active.')) +
                            '</p>' +
                            '<p class="text-lg font-semibold text-start">' +
                            (activeFamilies || 0).toLocaleString() +
                            '</p>' +
                            '<p class="mt-3 text-start"><a class="text-primary font-semibold hover:underline" href="' +
                            familiesUrl +
                            '">' +
                            @json(__('View families')) +
                            '</a></p>';
                        break;
                    case 'wallet_balance':
                        title = @json(__('Sum of wallet balances'));
                        html =
                            '<p class="text-sm mb-2 text-start">' +
                            @json(__('Aggregated balance across every wallet. Amounts in different family currencies are combined as plain numbers for this total.')) +
                            '</p>' +
                            '<p class="text-lg font-semibold text-start">' +
                            totalWalletBalanceFormatted +
                            '</p>' +
                            '<p class="mt-3 text-start"><a class="text-primary font-semibold hover:underline" href="' +
                            familiesUrl +
                            '">' +
                            @json(__('Open families')) +
                            '</a></p>';
                        break;
                    case 'ledger_today':
                        title = @json(__('Ledger entries today'));
                        html =
                            '<p class="text-sm mb-2 text-start">' +
                            @json(__('Income, expense, and transfer rows dated today (server timezone).')) +
                            '</p>' +
                            '<p class="text-lg font-semibold text-start">' +
                            (transactionsToday || 0).toLocaleString() +
                            '</p>' +
                            '<p class="mt-3 text-start"><a class="text-primary font-semibold hover:underline" href="' +
                            familiesUrl +
                            '">' +
                            @json(__('Browse families')) +
                            '</a></p>';
                        break;
                    default:
                        return;
                }

                if (typeof window.swalAlert === 'function') {
                    window.swalAlert({
                        title: title,
                        html: html,
                        icon: 'info',
                        confirmButtonText: @json(__('Close')),
                    });
                } else if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        title: title,
                        html: html,
                        icon: 'info',
                        confirmButtonText: @json(__('Close')),
                    });
                } else {
                    window.alert(title);
                }
            });
        });
    } catch (e) {
        console.error(e);
    }

    if (typeof ApexCharts === 'undefined') return;
    var months = @json($chartMonths ?? []);
    var userData = @json($userGrowthData ?? []);
    var familyData = @json($familyGrowthData ?? []);
    var txnData = @json($txnGrowthData ?? []);
    if (!months.length) return;

    var muted = typeof getComputedStyle !== 'undefined'
        ? (getComputedStyle(document.documentElement).getPropertyValue('--color-muted-foreground') || '#64748b').trim()
        : '#64748b';
    var border = typeof getComputedStyle !== 'undefined'
        ? (getComputedStyle(document.documentElement).getPropertyValue('--color-border') || '#e2e8f0').trim()
        : '#e2e8f0';

    var adminChartAccounts = null;
    var adminChartTxns = null;
    function adminDashboardChartsResize() {
        try { if (adminChartAccounts) adminChartAccounts.resize(); } catch (e) {}
        try { if (adminChartTxns) adminChartTxns.resize(); } catch (e) {}
    }

    var elA = document.getElementById('admin_chart_accounts');
    if (elA) {
        adminChartAccounts = new ApexCharts(elA, {
            series: [
                { name: @json(__('Users (cumulative)')), data: userData },
                { name: @json(__('Families (cumulative)')), data: familyData },
            ],
            chart: {
                type: 'line',
                height: 260,
                width: '100%',
                toolbar: { show: false },
                zoom: { enabled: false },
                redrawOnParentResize: true,
                animations: { enabled: true },
            },
            stroke: { curve: 'smooth', width: 2 },
            colors: ['#009ef7', '#10b981'],
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                offsetY: 0,
                labels: { colors: muted },
            },
            xaxis: {
                categories: months,
                labels: { style: { colors: muted, fontSize: '12px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: muted, fontSize: '12px' } },
                axisTicks: { show: false },
                title: { text: @json(__('Cumulative count')), style: { color: muted, fontSize: '11px' } }
            },
            grid: {
                borderColor: border,
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 4, right: 8, left: 4, bottom: 4 },
            },
            tooltip: { theme: 'dark' }
        });
        adminChartAccounts.render();
        setTimeout(adminDashboardChartsResize, 50);
    }

    var elT = document.getElementById('admin_chart_txns');
    if (elT) {
        adminChartTxns = new ApexCharts(elT, {
            series: [{ name: @json(__('Ledger entries (monthly)')), data: txnData }],
            chart: {
                type: 'bar',
                height: 260,
                width: '100%',
                toolbar: { show: false },
                zoom: { enabled: false },
                redrawOnParentResize: true,
                animations: { enabled: true },
            },
            colors: ['#0ea5e9'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
            legend: { show: false },
            xaxis: {
                categories: months,
                labels: { style: { colors: muted, fontSize: '12px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: muted, fontSize: '12px' } },
                axisTicks: { show: false },
                title: { text: @json(__('Entries this month')), style: { color: muted, fontSize: '11px' } }
            },
            grid: {
                borderColor: border,
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 4, right: 8, left: 4, bottom: 4 },
            },
            tooltip: { theme: 'dark' }
        });
        adminChartTxns.render();
        setTimeout(adminDashboardChartsResize, 50);
    }

    var adminDashResizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(adminDashResizeTimer);
        adminDashResizeTimer = setTimeout(adminDashboardChartsResize, 120);
    });
});
</script>
@endsection
