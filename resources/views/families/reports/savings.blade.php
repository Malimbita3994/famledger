@extends('layouts.metronic')

@section('title', 'Savings Report')
@section('page_title', 'Savings Report')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reports.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to Reports
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Savings Report</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Track savings goals progress, contributions, and completion.</p>
        </div>
    </div>

    {{-- KPI cards (cash-flow style) --}}
    @php
        $savingsGoalCount = count($rows ?? []);
        $savingsTotalSaved = collect($rows ?? [])->sum('saved');
        $savingsTotalTarget = collect($rows ?? [])->sum('target');
    @endphp
    <div class="report-kpi-grid report-kpi-grid--2">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total saved</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-dollar"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-green-600">{{ number_format($savingsTotalSaved, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">Across all goals</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Goals</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-safe"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $savingsGoalCount }}</div>
            <div class="text-muted-foreground text-sm mt-2">Target: {{ number_format($savingsTotalTarget, 0) }} {{ $currency }}</div>
        </div>
    </div>

    {{-- Content card (same as cash flow summary card) --}}
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Savings goals</h3>
        </div>
        <div class="kt-card-content p-5 space-y-6">
            @forelse($rows as $row)
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-medium text-foreground">{{ $row['goal']->name }}</p>
                            @if($row['goal']->target_date)
                                <p class="text-xs text-muted-foreground">Target: {{ $row['goal']->target_date->format('M j, Y') }}</p>
                            @endif
                        </div>
                        <div class="text-right text-sm">
                            <span class="tabular-nums text-green-600">{{ number_format($row['saved'], 0) }}</span>
                            <span class="text-muted-foreground"> / {{ number_format($row['target'], 0) }} {{ $currency }}</span>
                        </div>
                    </div>
                    <div class="h-3 rounded-full bg-muted overflow-hidden">
                        <div class="h-full rounded-full bg-green-500 transition-all" style="width: {{ min(100, $row['percent']) }}%"></div>
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">{{ $row['percent'] }}% · {{ number_format($row['remaining'], 0) }} {{ $currency }} remaining</p>
                </div>
            @empty
                <p class="text-muted-foreground text-sm">No savings goals yet. Create one from the Savings section.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
