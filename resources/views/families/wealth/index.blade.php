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
    </style>
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Family Wealth</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Net worth derived from wallets, properties and projects for {{ $family->name }}.
            </p>
        </div>
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
                <div class="flex flex-col gap-3 text-xs text-muted-foreground">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-2">
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
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-2">
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
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-2">
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

    {{-- Spacer between allocation row and trend card --}}
    <div class="mt-4"></div>

    {{-- Wealth trend --}}
    <div class="kt-card rounded-2xl border border-border shadow-sm bg-card">
        <div class="kt-card-header border-b border-border flex items-center justify-between gap-3">
            <h3 class="kt-card-title text-sm">Wealth trend</h3>
            <span class="text-xs text-muted-foreground">Snapshots over time (one per day when viewed).</span>
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
@endsection

