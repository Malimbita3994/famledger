@extends('layouts.metronic')

@section('title', 'Super Admin Dashboard')
@section('page_title', 'Platform Dashboard')

@php
    $currency = isset($currency) ? $currency : config('currencies.default', 'TZS');
    $fmt = function ($n, $code = null) use ($currency) {
        return number_format((float) $n, 0) . ' ' . ($code ?? $currency);
    };
@endphp

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-foreground">Platform Owner Dashboard</h1>
        <p class="text-muted-foreground mt-1">System health, families, users, and financial ecosystem overview. Default currency: {{ $currency }}.</p>
    </div>

    {{-- Top summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <a href="{{ route('admin.users.index') }}" class="kt-card p-5 hover:border-primary transition-colors">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Families</p>
                    <p class="text-2xl font-bold tabular-nums mt-1">{{ $totalFamilies ?? 0 }}</p>
                </div>
                <span class="rounded-full size-12 flex items-center justify-center bg-primary/10 text-primary"><i class="ki-filled ki-profile-circle text-xl"></i></span>
            </div>
        </a>
        <a href="{{ route('admin.users.index') }}" class="kt-card p-5 hover:border-primary transition-colors">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Users</p>
                    <p class="text-2xl font-bold tabular-nums mt-1">{{ $totalUsers ?? 0 }}</p>
                </div>
                <span class="rounded-full size-12 flex items-center justify-center bg-primary/10 text-primary"><i class="ki-filled ki-people text-xl"></i></span>
            </div>
        </a>
        <div class="kt-card p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Active Families</p>
                    <p class="text-2xl font-bold tabular-nums mt-1">{{ $activeFamilies ?? 0 }}</p>
                </div>
                <span class="rounded-full size-12 flex items-center justify-center bg-green-500/10 text-green-600"><i class="ki-filled ki-check-circle text-xl"></i></span>
            </div>
        </div>
        <div class="kt-card p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Wallet Balance</p>
                    <p class="text-xl font-bold tabular-nums mt-1">{{ $fmt($totalWalletBalance ?? 0) }}</p>
                </div>
                <span class="rounded-full size-12 flex items-center justify-center bg-green-500/10 text-green-600"><i class="ki-filled ki-wallet text-xl"></i></span>
            </div>
        </div>
        <div class="kt-card p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Transactions Today</p>
                    <p class="text-2xl font-bold tabular-nums mt-1">{{ $transactionsToday ?? 0 }}</p>
                </div>
                <span class="rounded-full size-12 flex items-center justify-center bg-blue-500/10 text-blue-600"><i class="ki-filled ki-chart-line text-xl"></i></span>
            </div>
        </div>
    </div>

    {{-- Families & Users overview --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="kt-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Families Overview</h2>
                <p class="text-sm text-muted-foreground mt-0.5">Platform families</p>
            </div>
            <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div><p class="text-xs text-muted-foreground">Total</p><p class="text-lg font-semibold">{{ $totalFamilies ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground">Active</p><p class="text-lg font-semibold text-green-600">{{ $activeFamilies ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground">New this month</p><p class="text-lg font-semibold">{{ $newFamiliesThisMonth ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground">Inactive (30d)</p><p class="text-lg font-semibold text-amber-600">{{ $inactiveFamilies ?? 0 }}</p></div>
            </div>
            <div class="px-5 pb-4"><a href="{{ route('families.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary">View families</a></div>
        </div>
        <div class="kt-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Users Overview</h2>
                <p class="text-sm text-muted-foreground mt-0.5">System-wide users</p>
            </div>
            <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div><p class="text-xs text-muted-foreground">Total</p><p class="text-lg font-semibold">{{ $totalUsers ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground">Active</p><p class="text-lg font-semibold text-green-600">{{ $activeUsers ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground">New this month</p><p class="text-lg font-semibold">{{ $newUsersThisMonth ?? 0 }}</p></div>
                <div><p class="text-xs text-muted-foreground">Suspended/Locked</p><p class="text-lg font-semibold text-destructive">{{ $suspendedUsers ?? 0 }}</p></div>
            </div>
            <div class="px-5 pb-4"><span class="text-sm text-muted-foreground">Avg members per family: {{ $usersPerFamily ?? 0 }}</span> · <a href="{{ route('admin.users.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary">Manage users</a></div>
        </div>
    </div>

    {{-- Financial & transaction activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="kt-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Financial Activity (Aggregated)</h2>
                <p class="text-sm text-muted-foreground mt-0.5">Platform totals — no per-family detail</p>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex justify-between"><span class="text-muted-foreground">Total income (all time)</span><span class="font-medium text-green-600">{{ $fmt($totalIncomePlatform ?? 0) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-foreground">Total expenses (all time)</span><span class="font-medium text-destructive">{{ $fmt($totalExpensesPlatform ?? 0) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-foreground">Net flow</span><span class="font-medium">{{ $fmt($netFlow ?? 0) }}</span></div>
                <div class="flex justify-between pt-2 border-t border-border"><span class="text-muted-foreground">Income this month</span><span class="font-medium">{{ $fmt($incomeThisMonth ?? 0) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-foreground">Expenses this month</span><span class="font-medium">{{ $fmt($expensesThisMonth ?? 0) }}</span></div>
            </div>
        </div>
        <div class="kt-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Transaction Activity</h2>
                <p class="text-sm text-muted-foreground mt-0.5">Usage statistics</p>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex justify-between"><span class="text-muted-foreground">Income transactions</span><span class="font-medium">{{ number_format($totalIncomeTransactions ?? 0) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-foreground">Expense transactions</span><span class="font-medium">{{ number_format($totalExpenseTransactions ?? 0) }}</span></div>
                <div class="flex justify-between"><span class="text-muted-foreground">Transfers</span><span class="font-medium">{{ number_format($totalTransfers ?? 0) }}</span></div>
                <div class="flex justify-between pt-2 border-t border-border"><span class="text-muted-foreground">Today</span><span class="font-semibold">{{ $transactionsToday ?? 0 }}</span></div>
                <div class="flex justify-between"><span class="text-muted-foreground">This month</span><span class="font-semibold">{{ $transactionsThisMonth ?? 0 }}</span></div>
            </div>
        </div>
    </div>

    {{-- Wallets, Budgets, Savings --}}
    <style>
        .admin-stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .admin-stats-card {
            flex: 1 1 100%;
        }

        @media (min-width: 900px) {
            .admin-stats-card {
                flex: 0 0 calc(33.333% - 1rem);
                max-width: calc(33.333% - 1rem);
            }
        }
    </style>
    <div class="admin-stats-grid">
        <div class="kt-card admin-stats-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Wallet Statistics</h2>
            </div>
            <div class="p-5 space-y-2">
                <p class="text-2xl font-bold">{{ $totalWallets ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">Total wallets · Avg {{ $avgWalletsPerFamily ?? 0 }} per family</p>
            </div>
        </div>
        <div class="kt-card admin-stats-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Budget Statistics</h2>
            </div>
            <div class="p-5 space-y-2">
                <p class="text-2xl font-bold">{{ $activeBudgets ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">Active budgets · <span class="text-destructive">{{ $overBudgetCount ?? 0 }}</span> over budget</p>
                @if (isset($avgBudgetAmount) && $avgBudgetAmount > 0)
                    <p class="text-xs text-muted-foreground">Avg budget: {{ $fmt($avgBudgetAmount) }}</p>
                @endif
            </div>
        </div>
        <div class="kt-card admin-stats-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Savings Goals</h2>
            </div>
            <div class="p-5 space-y-2">
                <p class="text-2xl font-bold">{{ $totalSavingsGoals ?? 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ $completedGoals ?? 0 }} completed · {{ $overdueGoals ?? 0 }} overdue</p>
                <p class="text-sm font-medium">Accumulated: {{ $fmt($totalSavingsAccumulated ?? 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Growth chart --}}
    <div class="kt-card rounded-xl border border-border overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-base font-semibold text-foreground">Growth Trends</h2>
            <p class="text-sm text-muted-foreground mt-0.5">Cumulative users, families, and transaction volume (last 6 months)</p>
        </div>
        <div class="p-4 min-h-[300px]">
            <div id="admin_growth_chart" class="w-full" style="min-height: 280px;"></div>
        </div>
    </div>

    {{-- Recent events --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="kt-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                <h2 class="text-base font-semibold text-foreground">Recent Families</h2>
                <a href="{{ route('families.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary">View all</a>
            </div>
            <div class="divide-y divide-border">
                @forelse ($recentFamilies ?? [] as $f)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <span class="font-medium">{{ $f->name }}</span>
                        <span class="text-sm text-muted-foreground">{{ $f->created_at?->format('M j, Y') }}</span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-muted-foreground text-sm">No families yet.</div>
                @endforelse
            </div>
        </div>
        <div class="kt-card rounded-xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                <h2 class="text-base font-semibold text-foreground">Recent Users</h2>
                <a href="{{ route('admin.users.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary">View all</a>
            </div>
            <div class="divide-y divide-border">
                @forelse ($recentUsers ?? [] as $u)
                    <div class="px-5 py-3 flex items-center justify-between gap-2">
                        <div class="min-w-0"><p class="font-medium truncate">{{ $u->name }}</p><p class="text-xs text-muted-foreground truncate">{{ $u->email }}</p></div>
                        <span class="text-sm text-muted-foreground shrink-0">{{ $u->created_at?->format('M j, Y') }}</span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-muted-foreground text-sm">No users yet.</div>
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
            { name: 'Users (cumulative)', data: userData },
            { name: 'Families (cumulative)', data: familyData },
            { name: 'Transactions (monthly)', data: txnData }
        ],
        chart: { type: 'line', height: 280, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#3b82f6', '#10b981', '#f59e0b'],
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
