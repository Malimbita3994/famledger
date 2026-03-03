@extends('layouts.metronic')

@section('title', 'Budget')
@section('page_title', 'Budget')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reports.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to Reports
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Budget</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Planned budget and actual spending. Filter by type, status, or date range.</p>
        </div>
    </div>

    {{-- Filter report card (standard) --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header">
            <h3 class="kt-card-title text-sm">Filter report</h3>
        </div>
        <div class="kt-card-content pt-4">
            <form method="get" action="{{ route('families.reports.budget-vs-actual', $family) }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">From</label>
                    <input type="date" name="from" value="{{ $dateFrom ?? now()->startOfMonth()->format('Y-m-d') }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">To</label>
                    <input type="date" name="to" value="{{ $dateTo ?? now()->endOfMonth()->format('Y-m-d') }}" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[140px]">
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Type</label>
                    <select name="type" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[160px]">
                        <option value="">All types</option>
                        @foreach($budgetTypes ?? [] as $value => $label)
                            <option value="{{ $value }}" {{ ($filterType ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Status</label>
                    <select name="status" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[120px]">
                        <option value="">All</option>
                        <option value="active" {{ ($filterStatus ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="archived" {{ ($filterStatus ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">Apply</button>
                <a href="{{ route('families.reports.budget-vs-actual', $family) }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    {{-- KPI cards (cash-flow style) --}}
    @php
        $budgetCount = count($rows ?? []);
        $budgetTotalPlanned = collect($rows ?? [])->sum('planned');
        $budgetTotalUsed = collect($rows ?? [])->sum('used');
    @endphp
    <div class="report-kpi-grid report-kpi-grid--2">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Budgets</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-chart-pie-simple"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $budgetCount }}</div>
            <div class="text-muted-foreground text-sm mt-2">In selected period</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Total planned</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-wallet"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ number_format($budgetTotalPlanned, 0) }} {{ $currency }}</div>
            <div class="text-muted-foreground text-sm mt-2">{{ number_format($budgetTotalUsed, 0) }} {{ $currency }} used</div>
        </div>
    </div>

    {{-- Content card: table with S/N, Budget Name, Allocated amount, Spent amount --}}
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Budgets</h3>
        </div>
        <div class="kt-card-content p-0">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[60px]">S/N</th>
                            <th class="min-w-[200px]">Budget Name</th>
                            <th class="min-w-[140px] text-right">Allocated amount ({{ $currency }})</th>
                            <th class="min-w-[140px] text-right">Spent amount ({{ $currency }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $sn => $row)
                            <tr>
                                <td class="text-muted-foreground tabular-nums">{{ $sn + 1 }}</td>
                                <td class="font-medium text-foreground">{{ $row['budget']->name }}</td>
                                <td class="text-right tabular-nums">{{ number_format($row['planned'], 0) }}</td>
                                <td class="text-right tabular-nums {{ $row['over'] ? 'text-red-600 font-medium' : '' }}">{{ number_format($row['used'], 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-4 text-center text-muted-foreground text-sm">No budgets in the current period. Create a budget from the Budgets section.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
