@extends('layouts.metronic')

@section('title', 'Transfers')
@section('page_title', 'Transfers')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12 w-full max-w-full min-w-0">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Transfers</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Move money between wallets. Family wealth stays the same; only distribution changes.</p>
        </div>
        <a href="{{ route('families.transfers.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-arrow-right-left"></i>
            New transfer
        </a>
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

    <div class="flex flex-col w-full max-w-full min-w-0" style="row-gap: clamp(2rem, 2vw + 1.5rem, 3.5rem);">
        <div class="famledger-chart-card kt-card rounded-xl border border-border shadow-sm shrink-0 min-w-0 max-w-full">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Transfer trend</h2>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Last 6 months · amount moved and number of transfers
                    @if (request('wallet_id'))
                        · Selected wallet
                    @endif
                </p>
            </div>
            <div class="famledger-chart-panel p-4">
                @if (array_sum($chartTransferCountByMonth) === 0)
                    <div class="flex items-center justify-center min-h-[240px] text-muted-foreground text-sm text-center px-4">
                        No transfers in this period{{ request('wallet_id') ? ' for the selected wallet' : '' }}.
                    </div>
                @else
                    <div id="famledger_transfer_trend_chart" class="w-full max-w-full min-w-0" style="min-height: 280px;"></div>
                @endif
            </div>
        </div>

        <div class="kt-card kt-card-grid w-full min-w-0 max-w-full shrink-0">
        <div class="kt-card-header flex-wrap gap-2 min-w-0">
            <h3 class="kt-card-title text-sm shrink-0">Transfer history</h3>
            <form method="get" action="{{ route('families.transfers.index', $family) }}" class="flex flex-wrap items-center gap-2 min-w-0 max-w-full">
                <label for="wallet_id" class="text-sm text-muted-foreground">Wallet</label>
                <select name="wallet_id" id="wallet_id" class="kt-select kt-select-sm w-auto" onchange="this.form.submit()">
                    <option value="">All wallets</option>
                    @foreach ($wallets as $w)
                        <option value="{{ $w->id }}" {{ request('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="kt-card-content p-0">
            @if ($transfers->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-arrow-right-left text-4xl mb-2"></i>
                    <p class="text-sm">No transfers yet.</p>
                    <a href="{{ route('families.transfers.create', $family) }}" class="kt-btn kt-btn-outline mt-4">New transfer</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[100px]">Date</th>
                                <th class="min-w-[140px]">From</th>
                                <th class="min-w-[40px]"></th>
                                <th class="min-w-[140px]">To</th>
                                <th class="min-w-[120px]">Amount</th>
                                <th class="min-w-[160px]">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transfers as $transfer)
                            <tr>
                                <td class="text-foreground">{{ $transfer->transfer_date->format('M j, Y') }}</td>
                                <td>
                                    <a href="{{ route('families.wallets.show', [$family, $transfer->fromWallet]) }}" class="text-primary hover:underline">{{ $transfer->fromWallet->name }}</a>
                                </td>
                                <td class="text-muted-foreground text-center"><i class="ki-filled ki-arrow-right text-sm"></i></td>
                                <td>
                                    <a href="{{ route('families.wallets.show', [$family, $transfer->toWallet]) }}" class="text-primary hover:underline">{{ $transfer->toWallet->name }}</a>
                                </td>
                                <td class="font-medium tabular-nums">{{ number_format($transfer->amount, 2) }} {{ $transfer->currency_code }}</td>
                                <td class="text-foreground">{{ Str::limit($transfer->description ?? '—', 30) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-border">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if (array_sum($chartTransferCountByMonth) > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') return;
    var el = document.getElementById('famledger_transfer_trend_chart');
    if (!el) return;

    var currency = @json($chartCurrency);
    var monthLabels = @json($chartMonthLabels);
    var amounts = @json($chartTransferAmountByMonth);
    var counts = @json($chartTransferCountByMonth);

    function famledgerCompactAxis(v) {
        v = Number(v) || 0;
        var a = Math.abs(v);
        if (a >= 1e9) return (v / 1e9).toFixed(1).replace(/\.0$/, '') + 'B';
        if (a >= 1e6) return (v / 1e6).toFixed(1).replace(/\.0$/, '') + 'M';
        if (a >= 1e3) return (v / 1e3).toFixed(1).replace(/\.0$/, '') + 'k';
        return (v % 1 === 0 ? String(v) : v.toFixed(1));
    }

    new ApexCharts(el, {
        series: [
            { name: 'Amount moved', type: 'column', data: amounts.map(Number) },
            { name: 'Transfer count', type: 'line', data: counts.map(Number) },
        ],
        chart: {
            type: 'line',
            height: 300,
            width: '100%',
            toolbar: { show: false },
            redrawOnParentResize: true,
        },
        stroke: { width: [0, 3], curve: 'smooth' },
        plotOptions: {
            bar: { columnWidth: '42%', borderRadius: 4 },
        },
        colors: ['#009EF7', '#f97316'],
        dataLabels: { enabled: false },
        xaxis: {
            categories: monthLabels,
            labels: {
                style: { colors: 'var(--color-muted-foreground)', fontSize: '11px' },
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: [
            {
                seriesName: 'Amount moved',
                labels: {
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                    formatter: function (v) {
                        return famledgerCompactAxis(v);
                    },
                },
                title: {
                    text: currency,
                    style: { color: 'var(--color-muted-foreground)', fontSize: '11px' },
                },
            },
            {
                opposite: true,
                seriesName: 'Transfer count',
                min: 0,
                tickAmount: 4,
                labels: {
                    style: { colors: 'var(--color-muted-foreground)', fontSize: '10px' },
                    formatter: function (v) {
                        return v == null ? '0' : String(Math.round(v));
                    },
                },
                title: {
                    text: 'Count',
                    style: { color: 'var(--color-muted-foreground)', fontSize: '11px' },
                },
            },
        ],
        grid: {
            borderColor: 'var(--color-border)',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
            padding: { top: 8, left: 6, right: 14, bottom: 4 },
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            offsetY: 4,
            labels: { colors: 'var(--color-muted-foreground)' },
        },
        tooltip: {
            shared: true,
            intersect: false,
            theme: 'dark',
            y: {
                formatter: function (value, opts) {
                    if (opts.seriesIndex === 0) {
                        return (value || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }) + ' ' + currency;
                    }
                    var n = value || 0;
                    return n + (n === 1 ? ' transfer' : ' transfers');
                },
            },
        },
    }).render();
});
</script>
@endif
@endpush
