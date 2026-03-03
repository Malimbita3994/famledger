@extends('layouts.metronic')

@section('title', 'Project Summary Report')
@section('page_title', 'Project Summary Report')

@php
    $filterTab = $filterTab ?? 'all';
    $tabs = [
        'all'       => ['label' => 'All Projects', 'icon' => 'ki-briefcase'],
        'active'    => ['label' => 'Active Projects', 'icon' => 'ki-flag'],
        'completed' => ['label' => 'Completed Projects', 'icon' => 'ki-check-circle'],
        'funding'   => ['label' => 'Projects Funding', 'icon' => 'ki-wallet'],
    ];
@endphp

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reports.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to Reports
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Project Summary Report</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Status, budget, spent, remaining, and completion. Filter by tab or search by name.</p>
        </div>
    </div>

    {{-- Filter report card with tabs (cash-flow style) --}}
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6">
        <div class="kt-card-header flex-wrap gap-2 border-b border-border">
            <h3 class="kt-card-title text-sm">Filter report</h3>
            <nav class="flex flex-wrap gap-1 mt-2 md:mt-0" role="tablist">
                @foreach($tabs as $key => $tab)
                    @php
                        $tabUrl = route('families.reports.project-summary', [$family, 'tab' => $key, 'search' => $filterSearch ?? '']);
                    @endphp
                    <a href="{{ $tabUrl }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $filterTab === $key ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}">
                        <i class="ki-filled {{ $tab['icon'] }} text-base"></i>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
        <div class="kt-card-content pt-4">
            <form method="get" action="{{ route('families.reports.project-summary', $family) }}" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" value="{{ $filterTab }}">
                <div>
                    <label class="block text-sm text-muted-foreground mb-1">Search by name</label>
                    <input type="text" name="search" value="{{ $filterSearch ?? '' }}" placeholder="Project name…" class="kt-input rounded-lg border border-border px-3 py-2 text-sm min-w-[180px]">
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">Apply</button>
                <a href="{{ route('families.reports.project-summary', [$family, 'tab' => $filterTab]) }}" class="kt-btn kt-btn-ghost">Reset</a>
            </form>
        </div>
    </div>

    {{-- KPI cards (cash-flow style: 4-col) --}}
    <div class="report-kpi-grid">
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">All Projects</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-briefcase"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $totalProjects ?? 0 }}</div>
            <div class="text-muted-foreground text-sm mt-2">Total projects</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Active</span>
                <span class="text-green-500 text-lg shrink-0"><i class="ki-filled ki-flag"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums text-green-600">{{ $activeCount ?? 0 }}</div>
            <div class="text-muted-foreground text-sm mt-2">In progress</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">Completed</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-check-circle"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $completedCount ?? 0 }}</div>
            <div class="text-muted-foreground text-sm mt-2">Finished</div>
        </div>
        <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3">
                <span class="text-muted-foreground text-sm font-medium">With funding</span>
                <span class="text-primary text-lg shrink-0"><i class="ki-filled ki-wallet"></i></span>
            </div>
            <div class="text-xl font-bold mt-3 text-foreground tabular-nums">{{ $fundedCount ?? 0 }}</div>
            <div class="text-muted-foreground text-sm mt-2">Projects funded</div>
        </div>
    </div>

    {{-- Table card (same as cash flow summary card) --}}
    <div class="kt-card kt-card-grid min-w-full rounded-xl border border-border shadow-sm overflow-hidden bg-card">
        <div class="kt-card-header border-b border-border">
            <h3 class="kt-card-title text-sm">Projects</h3>
        </div>
        <div class="kt-card-content p-0">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[140px]">Project</th>
                            <th class="min-w-[100px]">Status</th>
                            <th class="min-w-[100px] text-right">Budget</th>
                            <th class="min-w-[100px] text-right">Spent</th>
                            <th class="min-w-[100px] text-right">Remaining</th>
                            <th class="min-w-[100px] text-right">Completion</th>
                            <th class="min-w-[120px]">Dates</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $p)
                            @php
                                $planned = (float) $p->planned_budget;
                                $spent = (float) ($p->expenses_sum_amount ?? 0);
                                $funded = (float) ($p->fundings_sum_amount ?? 0);
                                $remaining = $planned - $spent;
                                $pct = $planned > 0 ? min(100, round(($spent / $planned) * 100, 1)) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('families.projects.show', [$family, $p]) }}" class="font-medium text-primary hover:underline">{{ $p->name }}</a>
                                </td>
                                <td>
                                    <span class="kt-badge kt-badge-sm {{ $p->status === 'active' ? 'kt-badge-primary' : ($p->status === 'completed' ? 'kt-badge-success' : 'kt-badge-outline') }}">{{ ucfirst($p->status) }}</span>
                                </td>
                                <td class="text-right tabular-nums">{{ number_format($planned, 0) }} {{ $currency }}</td>
                                <td class="text-right tabular-nums text-red-600">{{ number_format($spent, 0) }} {{ $currency }}</td>
                                <td class="text-right tabular-nums {{ $remaining >= 0 ? 'text-foreground' : 'text-red-600' }}">{{ number_format($remaining, 0) }} {{ $currency }}</td>
                                <td class="text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <div class="w-16 h-2 rounded-full bg-muted overflow-hidden">
                                            <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-red-500' : 'bg-primary' }}" style="width: {{ min(100, $pct) }}%"></div>
                                        </div>
                                        <span class="text-xs tabular-nums">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="text-sm text-muted-foreground">
                                    @if($p->start_date) {{ $p->start_date->format('M j') }} @endif
                                    @if($p->start_date && $p->target_end_date) – @endif
                                    @if($p->target_end_date) {{ $p->target_end_date->format('M j, Y') }} @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 px-4 text-center text-muted-foreground text-sm">No projects in this view.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
