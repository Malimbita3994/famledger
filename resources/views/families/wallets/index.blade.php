@extends('layouts.metronic')

@section('title', 'Wallets')
@section('page_title', 'Wallets')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12 w-full max-w-full min-w-0">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center gap-3 mb-4">
        <div>
            <h1 class="font-medium text-lg text-mono">Family Wallets</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Stand-alone wallets where family money lives. No bank link.</p>
        </div>
        <div class="ml-auto flex flex-wrap justify-end gap-1.5">
            <a href="{{ route('families.incomes.create', $family) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-arrow-up"></i>
                Record income
            </a>
            <a href="{{ route('families.expenses.create', $family) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-arrow-down"></i>
                Record expense
            </a>
            <a href="{{ route('families.transfers.create', $family) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-arrow-right-left"></i>
                Transfer
            </a>
            <a href="{{ route('families.reconciliations.create', $family) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-check-circle"></i>
                Reconcile
            </a>
            <a href="{{ route('families.savings-goals.index', $family) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-safe"></i>
                Savings goals
            </a>
            <a href="{{ route('families.wallets.create', $family) }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i>
                Add wallet
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($wallets->isEmpty())
        <div class="kt-card">
            <div class="kt-card-content py-12 text-center">
                <i class="ki-filled ki-wallet text-5xl text-muted-foreground mb-4"></i>
                <p class="font-semibold text-foreground">No wallets yet</p>
                <p class="text-sm text-secondary-foreground mt-1">Create a wallet to start tracking income, expenses, and transfers for this family.</p>
                <a href="{{ route('families.wallets.create', $family) }}" class="kt-btn kt-btn-primary mt-6">Add wallet</a>
            </div>
        </div>
    @else
        {{-- flex + min-w-0/basis-0 keeps each chart in exactly one third; grid can let one column steal width --}}
        <div class="flex flex-col md:flex-row gap-4 lg:gap-5 mt-4 mb-6 w-full max-w-full min-w-0">
            <div class="famledger-chart-card w-full md:flex-1 md:min-w-0 md:basis-0 max-w-full kt-card flex flex-col rounded-xl border border-border shadow-sm min-w-0">
                <div class="px-4 py-3 border-b border-border shrink-0">
                    <h2 class="text-sm font-semibold text-foreground leading-snug">Balance by wallet</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Per wallet
                        @if ($chartCurrencyLabel)
                            · {{ $chartCurrencyLabel }}
                        @endif
                    </p>
                </div>
                <div class="famledger-chart-panel p-3 min-h-[220px]">
                    <div id="famledger_wallets_balance_chart" class="w-full max-w-full min-w-0" style="min-height: 200px;"></div>
                </div>
            </div>
            <div class="famledger-chart-card w-full md:flex-1 md:min-w-0 md:basis-0 max-w-full kt-card flex flex-col rounded-xl border border-border shadow-sm min-w-0">
                <div class="px-4 py-3 border-b border-border shrink-0">
                    <h2 class="text-sm font-semibold text-foreground leading-snug">Share of positive balances</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">Wallets above zero</p>
                </div>
                <div class="famledger-chart-panel p-3 flex items-center justify-center min-h-[220px]">
                    <div id="famledger_wallets_share_chart" class="w-full max-w-full min-w-0" style="min-height: 200px;"></div>
                </div>
            </div>
            <div class="famledger-chart-card w-full md:flex-1 md:min-w-0 md:basis-0 max-w-full kt-card flex flex-col rounded-xl border border-border shadow-sm min-w-0">
                <div class="px-4 py-3 border-b border-border shrink-0">
                    <h2 class="text-sm font-semibold text-foreground leading-snug">Total by type</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">Sum by wallet type</p>
                </div>
                <div class="famledger-chart-panel p-3 min-h-[220px]">
                    <div id="famledger_wallets_type_chart" class="w-full max-w-full min-w-0" style="min-height: 200px;"></div>
                </div>
            </div>
        </div>

        <div class="kt-card kt-card-grid w-full min-w-0 max-w-full mt-4">
            <div class="kt-card-content p-0">
                {{-- Desktop / tablet table --}}
                <div class="kt-scrollable-x-auto hidden md:block">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[200px]">Wallet</th>
                                <th class="min-w-[120px]">Type</th>
                                <th class="min-w-[100px]">Currency</th>
                                <th class="min-w-[120px]">Balance</th>
                                <th class="min-w-[80px]">Shared</th>
                                <th class="min-w-[80px]">Main</th>
                                <th class="min-w-[80px]">Status</th>
                                <th class="w-[60px]">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wallets as $wallet)
                            <tr>
                                <td>
                                    <a href="{{ route('families.wallets.show', [$family, $wallet]) }}" class="flex items-center gap-2.5 hover:opacity-90">
                                        <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground font-medium text-sm">
                                            <i class="ki-filled ki-wallet text-lg"></i>
                                        </span>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-sm font-medium text-mono hover:text-primary">{{ $wallet->name }}</span>
                                            @if ($wallet->description)
                                                <span class="text-sm text-secondary-foreground truncate max-w-[220px] block">{{ Str::limit($wallet->description, 35) }}</span>
                                            @endif
                                        </div>
                                    </a>
                                </td>
                                <td class="text-foreground font-normal">{{ $walletTypes[$wallet->type] ?? $wallet->type }}</td>
                                <td class="text-foreground font-normal">{{ $wallet->currency_code }}</td>
                                <td class="font-medium tabular-nums">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency_code }}</td>
                                <td>
                                    @if ($wallet->is_shared)
                                        <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Shared</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">Personal</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($wallet->is_primary)
                                        <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">Main wallet</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="kt-badge kt-badge-sm {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">{{ ucfirst($wallet->status) }}</span>
                                </td>
                                <td>
                                    <div class="kt-menu flex-inline" data-kt-menu="true">
                                        <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                            <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button">
                                                <i class="ki-filled ki-dots-vertical text-lg"></i>
                                            </button>
                                            <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link" href="{{ route('families.wallets.show', [$family, $wallet]) }}">
                                                        <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                        <span class="kt-menu-title">View</span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-item">
                                                    <a class="kt-menu-link" href="{{ route('families.wallets.edit', [$family, $wallet]) }}">
                                                        <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                        <span class="kt-menu-title">Edit</span>
                                                    </a>
                                                </div>
                                                <div class="kt-menu-separator"></div>
                                                <div class="kt-menu-item">
                                                    <form action="{{ route('families.wallets.destroy', [$family, $wallet]) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="Remove this wallet?" data-confirm-message="This cannot be undone.">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-trash"></i></span>
                                                            <span class="kt-menu-title">Remove</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden p-4 space-y-4">
                    @foreach ($wallets as $wallet)
                        <div class="rounded-2xl border border-border bg-background shadow-sm p-4 flex flex-col gap-3">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground">
                                        <i class="ki-filled ki-wallet text-lg"></i>
                                    </span>
                                    <div class="flex flex-col min-w-0">
                                        <a href="{{ route('families.wallets.show', [$family, $wallet]) }}" class="text-sm font-semibold text-foreground hover:text-primary truncate">
                                            {{ $wallet->name }}
                                        </a>
                                        @if ($wallet->description)
                                            <span class="text-[11px] text-secondary-foreground mt-0.5 line-clamp-2">
                                                {{ $wallet->description }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    <span class="kt-badge kt-badge-sm {{ $wallet->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">
                                        {{ ucfirst($wallet->status) }}
                                    </span>
                                    @if ($wallet->is_primary)
                                        <span class="kt-badge kt-badge-xs kt-badge-primary kt-badge-outline">Main wallet</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Key numbers --}}
                            <div class="flex items-center justify-between gap-3 border border-border/60 rounded-xl px-3 py-2 bg-muted/40">
                                <div class="flex flex-col">
                                    <span class="text-[11px] text-muted-foreground uppercase tracking-wide">Balance</span>
                                    <span class="text-sm font-semibold text-foreground tabular-nums">
                                        {{ number_format($wallet->balance, 2) }} {{ $wallet->currency_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[11px] text-muted-foreground uppercase tracking-wide">Type</span>
                                    <span class="text-xs font-medium text-foreground">
                                        {{ $walletTypes[$wallet->type] ?? $wallet->type }}
                                    </span>
                                </div>
                            </div>

                            {{-- Meta --}}
                            <div class="flex items-center justify-between text-[11px] text-muted-foreground">
                                <span>Currency: <span class="font-medium text-foreground">{{ $wallet->currency_code }}</span></span>
                                <span>
                                    @if ($wallet->is_shared)
                                        <span class="kt-badge kt-badge-xs kt-badge-success kt-badge-outline">Shared</span>
                                    @else
                                        <span class="kt-badge kt-badge-xs kt-badge-secondary kt-badge-outline">Personal</span>
                                    @endif
                                </span>
                            </div>

                            {{-- Actions --}}
                            <div class="flex flex-wrap justify-end gap-2 pt-1">
                                <a href="{{ route('families.wallets.show', [$family, $wallet]) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                                    View
                                </a>
                                <a href="{{ route('families.wallets.edit', [$family, $wallet]) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                                    Edit
                                </a>
                                <form action="{{ route('families.wallets.destroy', [$family, $wallet]) }}" method="POST" class="js-confirm-delete inline-block" data-confirm-title="Remove this wallet?" data-confirm-message="This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-destructive">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') return;

    var currency = @json($chartCurrencyLabel);
    var walletNames = @json($chartWalletNames);
    var walletBalances = @json($chartWalletBalances);
    var typeLabels = @json($chartTypeLabels);
    var typeBalances = @json($chartTypeBalances);
    var shareLabels = @json($chartShareLabels);
    var shareValues = @json($chartShareValues);

    var palette = ['#009EF7', '#38bdf8', '#0ea5e9', '#0369a1', '#22c55e', '#a855f7', '#f97316', '#ef4444', '#14b8a6', '#eab308'];

    function famledgerCompactAxis(v) {
        v = Number(v) || 0;
        var a = Math.abs(v);
        if (a >= 1e9) return (v / 1e9).toFixed(1).replace(/\.0$/, '') + 'B';
        if (a >= 1e6) return (v / 1e6).toFixed(1).replace(/\.0$/, '') + 'M';
        if (a >= 1e3) return (v / 1e3).toFixed(1).replace(/\.0$/, '') + 'k';
        return (v % 1 === 0 ? String(v) : v.toFixed(1));
    }

    var balanceEl = document.getElementById('famledger_wallets_balance_chart');
    if (balanceEl && walletNames.length && walletBalances.length) {
        new ApexCharts(balanceEl, {
            series: [{ name: 'Balance', data: walletBalances.map(Number) }],
            chart: {
                type: 'bar',
                width: '100%',
                height: 300,
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
            colors: walletBalances.map(function (b, i) {
                return Number(b) < 0 ? '#ef4444' : palette[i % palette.length];
            }),
            dataLabels: {
                enabled: walletNames.length <= 10,
                offsetY: -4,
                style: { colors: ['var(--color-foreground)'], fontSize: '10px' },
                formatter: function (val) {
                    return (val || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
                },
            },
            xaxis: {
                categories: walletNames,
                labels: {
                    rotate: walletNames.length > 3 ? -40 : 0,
                    rotateAlways: walletNames.length > 3,
                    hideOverlappingLabels: true,
                    trim: true,
                    maxHeight: 72,
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
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
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            grid: {
                borderColor: 'var(--color-border)',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 8, right: 8, left: 4 },
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (v) {
                        return (v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }) + (currency ? ' ' + currency : '');
                    },
                },
            },
            legend: { show: false },
        }).render();
    } else if (balanceEl) {
        balanceEl.innerHTML = '<div class="flex items-center justify-center h-[200px] text-muted-foreground text-sm">No wallet data</div>';
    }

    var shareEl = document.getElementById('famledger_wallets_share_chart');
    if (shareEl) {
        if (shareLabels.length && shareValues.length) {
            new ApexCharts(shareEl, {
                series: shareValues.map(Number),
                labels: shareLabels,
                chart: {
                    type: 'donut',
                    width: '100%',
                    height: 240,
                    toolbar: { show: false },
                    redrawOnParentResize: true,
                },
                colors: palette,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function () {
                                        var t = shareValues.reduce(function (a, b) { return a + Number(b); }, 0);
                                        return t.toLocaleString(undefined, { maximumFractionDigits: 2 }) + (currency ? ' ' + currency : '');
                                    },
                                },
                            },
                        },
                    },
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return (val != null ? val.toFixed(0) : '0') + '%';
                    },
                },
                stroke: { width: 0 },
                legend: {
                    position: 'bottom',
                    labels: { colors: 'var(--color-muted-foreground)' },
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (v) {
                            return (v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }) + (currency ? ' ' + currency : '');
                        },
                    },
                },
            }).render();
        } else {
            shareEl.innerHTML = '<div class="flex items-center justify-center h-[200px] text-muted-foreground text-sm text-center px-4">No wallets with a positive balance to chart.</div>';
        }
    }

    var typeEl = document.getElementById('famledger_wallets_type_chart');
    if (typeEl && typeLabels.length && typeBalances.length) {
        new ApexCharts(typeEl, {
            series: [{ name: 'Balance', data: typeBalances.map(Number) }],
            chart: {
                type: 'bar',
                width: '100%',
                height: 300,
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
            colors: typeBalances.map(function (b, i) {
                return Number(b) < 0 ? '#ef4444' : palette[i % palette.length];
            }),
            dataLabels: {
                enabled: typeLabels.length <= 12,
                offsetY: -4,
                style: { colors: ['var(--color-foreground)'], fontSize: '10px' },
                formatter: function (val) {
                    return (val || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
                },
            },
            xaxis: {
                categories: typeLabels,
                labels: {
                    rotate: typeLabels.length > 3 ? -40 : 0,
                    rotateAlways: typeLabels.length > 3,
                    hideOverlappingLabels: true,
                    trim: true,
                    maxHeight: 72,
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
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
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            grid: {
                borderColor: 'var(--color-border)',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 8, right: 8, left: 4 },
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (v) {
                        return (v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }) + (currency ? ' ' + currency : '');
                    },
                },
            },
            legend: { show: false },
        }).render();
    } else if (typeEl) {
        typeEl.innerHTML = '<div class="flex items-center justify-center h-[180px] text-muted-foreground text-sm">No type breakdown</div>';
    }
});
</script>
@endpush
