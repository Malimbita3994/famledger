@extends('layouts.metronic')

@section('title', 'Family Wealth')
@section('page_title', 'Family Wealth')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <style>
    .wealth-kpi-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 0.75rem !important;
        width: 100% !important;
    }
    .wealth-allocation-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 0.75rem !important;
        width: 100% !important;
    }
    </style>
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Family Wealth</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Live net worth from real wallet balances, property values and funded projects for {{ $family->name }}.
            </p>
        </div>
        <a href="{{ route('families.wealth.export-pdf', $family) }}"
           class="kt-btn kt-btn-sm kt-btn-outline inline-flex items-center gap-1.5">
            <i class="ki-filled ki-file-down text-base"></i>
            Export PDF
        </a>
    </div>

    {{-- Wealth overview (KPI style, similar to reports) --}}
    <div class="grid gap-5 lg:gap-7.5 lg:grid-cols-3 mb-6">
        <div class="lg:col-span-2">
            <div class="kt-card rounded-2xl border border-border shadow-sm bg-card p-5 lg:p-6">
                <div class="wealth-kpi-grid items-start">
                    <div class="rounded-xl border border-border bg-background px-3 py-2.5">
                        <h2 class="text-[11px] text-muted-foreground uppercase tracking-wide mb-1.5">Total family wealth</h2>
                        <div class="text-xl lg:text-2xl font-semibold text-foreground tabular-nums">
                            {{ number_format($overview['net_wealth'], 0) }} {{ $currency }}
                        </div>
                        <p class="text-[11px] text-secondary-foreground mt-1 leading-snug">
                            Wallets + properties + projects (minus liabilities).
                        </p>
                    </div>
                    <div class="rounded-xl border border-border bg-background px-3 py-2.5">
                        <div class="text-[11px] uppercase tracking-wide text-muted-foreground">Wallets</div>
                        <div class="text-sm font-semibold tabular-nums mt-1">
                            {{ number_format($overview['wallet_total'], 0) }} {{ $currency }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-border bg-background px-3 py-2.5">
                        <div class="text-[11px] uppercase tracking-wide text-muted-foreground">Properties</div>
                        <div class="text-sm font-semibold tabular-nums mt-1">
                            {{ number_format($overview['property_total'], 0) }} {{ $currency }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-border bg-background px-3 py-2.5">
                        <div class="text-[11px] uppercase tracking-wide text-muted-foreground">Projects</div>
                        <div class="text-sm font-semibold tabular-nums mt-1">
                            {{ number_format($overview['project_total'], 0) }} {{ $currency }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Asset allocation --}}
        <div class="mb-6 lg:mb-0">
            <div class="kt-card rounded-2xl border border-border shadow-sm bg-card p-5 lg:p-6">
                <h2 class="text-xs text-muted-foreground uppercase tracking-wide mb-3">Asset allocation</h2>
                <div class="wealth-allocation-grid text-xs text-muted-foreground">
                    {{-- Wallets --}}
                    <div class="rounded-xl border border-border bg-background px-3 py-2.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block size-2 rounded-full bg-primary"></span>
                                Wallets
                            </span>
                            <span class="font-semibold text-foreground tabular-nums">{{ $allocation['wallet_pct'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-muted overflow-hidden">
                            <div class="h-full rounded-full bg-primary" style="width: {{ min(100, $allocation['wallet_pct']) }}%"></div>
                        </div>
                    </div>

                    {{-- Properties --}}
                    <div class="rounded-xl border border-border bg-background px-3 py-2.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block size-2 rounded-full bg-success"></span>
                                Properties
                            </span>
                            <span class="font-semibold text-foreground tabular-nums">{{ $allocation['property_pct'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-muted overflow-hidden">
                            <div class="h-full rounded-full bg-success" style="width: {{ min(100, $allocation['property_pct']) }}%"></div>
                        </div>
                    </div>

                    {{-- Projects --}}
                    <div class="rounded-xl border border-border bg-background px-3 py-2.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block size-2 rounded-full bg-warning-500"></span>
                                Projects
                            </span>
                            <span class="font-semibold text-foreground tabular-nums">{{ $allocation['project_pct'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-muted overflow-hidden">
                            <div class="h-full rounded-full bg-warning-500" style="width: {{ min(100, $allocation['project_pct']) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Spacer between allocation row and charts --}}
    <div class="mt-4"></div>

    @if (!empty($wealthCharts['hasData']))
    <div class="grid gap-5 lg:gap-7.5 lg:grid-cols-2 mb-6">
        <div class="kt-card rounded-2xl border border-border shadow-sm bg-card">
            <div class="kt-card-header border-b border-border flex flex-col gap-1 items-start">
                <h3 class="kt-card-title text-sm">Net wealth trend</h3>
                <p class="text-xs text-muted-foreground font-normal">
                    Daily snapshots from this page. When there are at least two points, the dashed line extends a simple linear trend
                    {{ !empty($wealthCharts['projection']['enabled']) ? '(' . (int) $wealthCharts['projection']['daysAhead'] . ' days ahead)' : '' }}
                    — illustrative only, not a forecast of future results.
                </p>
            </div>
            <div class="kt-card-content p-4 lg:p-5">
                <div id="famledger_wealth_net_chart" class="min-h-[300px] w-full"></div>
            </div>
        </div>
        <div class="kt-card rounded-2xl border border-border shadow-sm bg-card">
            <div class="kt-card-header border-b border-border flex flex-col gap-1 items-start">
                <h3 class="kt-card-title text-sm">Composition over time</h3>
                <p class="text-xs text-muted-foreground font-normal">
                    Stacked wallets, properties and project funds. Net wealth in the left chart already reflects liabilities.
                </p>
            </div>
            <div class="kt-card-content p-4 lg:p-5">
                <div id="famledger_wealth_composition_chart" class="min-h-[320px] w-full"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Wealth trend table --}}
    <div class="kt-card rounded-2xl border border-border shadow-sm bg-card">
        <div class="kt-card-header border-b border-border flex items-center justify-between gap-3">
            <h3 class="kt-card-title text-sm">Snapshot history</h3>
            <span class="text-xs text-muted-foreground">Historical daily snapshots. Cards above are always based on live balances.</span>
        </div>
        <div class="kt-card-content p-0">
            @if ($trend->isEmpty())
                <div class="py-10 text-center text-muted-foreground text-sm">
                    No wealth snapshots yet. This view will build up as you keep using FamLedger.
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border text-xs">
                        <thead>
                            <tr>
                                <th class="min-w-[120px]">Date</th>
                                <th class="min-w-[120px] text-right">Wallets</th>
                                <th class="min-w-[120px] text-right">Properties</th>
                                <th class="min-w-[120px] text-right">Projects</th>
                                <th class="min-w-[120px] text-right">Liabilities</th>
                                <th class="min-w-[140px] text-right">Net wealth</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trend as $row)
                                <tr>
                                    <td>{{ $row->snapshot_date->format('M j, Y') }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->wallet_total, 0) }} {{ $currency }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->property_total, 0) }} {{ $currency }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->project_total, 0) }} {{ $currency }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->liability_total, 0) }} {{ $currency }}</td>
                                    <td class="text-right tabular-nums font-semibold">{{ number_format($row->net_wealth, 0) }} {{ $currency }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var wc = @json($wealthCharts ?? ['hasData' => false]);

            if (typeof ApexCharts !== 'undefined' && wc.hasData) {
                var currency = wc.currency || '';
                function famledgerWealthCompactAxis(v) {
                    v = Number(v) || 0;
                    var a = Math.abs(v);
                    if (a >= 1e9) return (v / 1e9).toFixed(1).replace(/\.0$/, '') + 'B';
                    if (a >= 1e6) return (v / 1e6).toFixed(1).replace(/\.0$/, '') + 'M';
                    if (a >= 1e3) return (v / 1e3).toFixed(1).replace(/\.0$/, '') + 'k';
                    return (v % 1 === 0 ? String(v) : v.toFixed(1));
                }
                function famledgerWealthTooltip(v) {
                    return (v == null || v === '') ? '—' : (Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 }) + (currency ? ' ' + currency : ''));
                }

                var netEl = document.getElementById('famledger_wealth_net_chart');
                if (netEl) {
                    var proj = wc.projection || {};
                    var netCategories = proj.enabled ? proj.fullCategories : wc.categories;
                    var netSeries = proj.enabled
                        ? [
                            { name: 'Net wealth', data: proj.netActual },
                            { name: 'Trend projection', data: proj.netForecast },
                        ]
                        : [{ name: 'Net wealth', data: wc.netWealth }];

                    new ApexCharts(netEl, {
                        series: netSeries,
                        chart: {
                            type: 'line',
                            height: 300,
                            width: '100%',
                            toolbar: { show: true, tools: { download: true } },
                            zoom: { enabled: true },
                            redrawOnParentResize: true,
                            animations: { enabled: true },
                        },
                        colors: proj.enabled ? ['#009EF7', '#94a3b8'] : ['#009EF7'],
                        stroke: {
                            curve: 'smooth',
                            width: proj.enabled ? [3, 2] : [3],
                            dashArray: proj.enabled ? [0, 6] : [0],
                        },
                        dataLabels: { enabled: false },
                        xaxis: {
                            categories: netCategories,
                            labels: {
                                rotate: netCategories.length > 8 ? -35 : 0,
                                rotateAlways: netCategories.length > 8,
                                hideOverlappingLabels: true,
                                style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                        },
                        yaxis: {
                            labels: {
                                style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                                formatter: famledgerWealthCompactAxis,
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                        },
                        grid: {
                            borderColor: 'var(--color-border)',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: false } },
                            yaxis: { lines: { show: true } },
                            padding: { top: 8, right: 12, left: 8, bottom: 4 },
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'end',
                            fontSize: '11px',
                            labels: { colors: 'var(--color-muted-foreground)' },
                        },
                        tooltip: {
                            theme: 'dark',
                            shared: true,
                            intersect: false,
                            y: { formatter: famledgerWealthTooltip },
                        },
                        markers: {
                            size: proj.enabled ? [4, 0] : [4],
                            strokeWidth: 2,
                            hover: { sizeOffset: 2 },
                        },
                    }).render();
                }

                var compEl = document.getElementById('famledger_wealth_composition_chart');
                if (compEl) {
                    new ApexCharts(compEl, {
                        series: [
                            { name: 'Wallets', data: wc.wallet },
                            { name: 'Properties', data: wc.property },
                            { name: 'Projects', data: wc.project },
                        ],
                        chart: {
                            type: 'area',
                            height: 320,
                            width: '100%',
                            stacked: true,
                            toolbar: { show: true, tools: { download: true } },
                            zoom: { enabled: true },
                            redrawOnParentResize: true,
                        },
                        colors: ['#009EF7', '#22c55e', '#f59e0b'],
                        stroke: {
                            curve: 'smooth',
                            width: [2, 2, 2],
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                opacityFrom: 0.45,
                                opacityTo: 0.06,
                            },
                        },
                        dataLabels: { enabled: false },
                        xaxis: {
                            categories: wc.categories,
                            labels: {
                                rotate: wc.categories.length > 8 ? -35 : 0,
                                rotateAlways: wc.categories.length > 8,
                                hideOverlappingLabels: true,
                                style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                        },
                        yaxis: {
                            labels: {
                                style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                                formatter: famledgerWealthCompactAxis,
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                        },
                        grid: {
                            borderColor: 'var(--color-border)',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: false } },
                            yaxis: { lines: { show: true } },
                            padding: { top: 8, right: 12, left: 8, bottom: 4 },
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'end',
                            fontSize: '11px',
                            labels: { colors: 'var(--color-muted-foreground)' },
                        },
                        tooltip: {
                            theme: 'dark',
                            shared: true,
                            intersect: false,
                            y: { formatter: famledgerWealthTooltip },
                        },
                    }).render();
                }
            }

            setInterval(function () {
                if (document.visibilityState === 'visible') {
                    window.location.reload();
                }
            }, 300000);
        });
    </script>
@endpush
@endsection