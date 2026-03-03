@extends('layouts.metronic')

@section('title', 'Projects Funding')
@section('page_title', 'Projects Funding')

@section('content')
<style>
@media (min-width: 1024px) {
    .projects-funding-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
        width: 100%;
    }
}
@media (min-width: 768px) and (max-width: 1023px) {
    .projects-funding-kpi-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
        width: 100%;
    }
}
@media (max-width: 767px) {
    .projects-funding-kpi-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
        width: 100%;
    }
}
.projects-funding-kpi-card {
    min-width: 0;
    box-sizing: border-box;
}
</style>
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-semibold text-lg text-mono">Projects Funding</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Allocate funds from family wallets to projects.</p>
        </div>
        <a href="{{ route('families.projects.funding.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i>
            Add funding
        </a>
    </div>

    @php
        $totalFunding = (float) $projects->sum('fundings_sum_amount');
        $totalExpenses = (float) $projects->sum('expenses_sum_amount');
        $totalBudget   = (float) $projects->sum('planned_budget');
    @endphp

    @if ($projects->isNotEmpty())
        <div class="projects-funding-kpi-grid">
            <div class="projects-funding-kpi-card rounded-xl border border-border bg-card px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-medium text-muted-foreground uppercase">Total budget</span>
                    <span class="text-sm font-semibold text-foreground tabular-nums">{{ number_format($totalBudget, 0) }} {{ $currency }}</span>
                </div>
                <span class="inline-flex items-center justify-center rounded-full bg-muted size-9">
                    <i class="ki-filled ki-briefcase text-muted-foreground"></i>
                </span>
            </div>
            <div class="projects-funding-kpi-card rounded-xl border border-border bg-card px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-medium text-muted-foreground uppercase">Total funded</span>
                    <span class="text-sm font-semibold text-foreground tabular-nums">{{ number_format($totalFunding, 0) }} {{ $currency }}</span>
                </div>
                <span class="inline-flex items-center justify-center rounded-full bg-primary/5 size-9">
                    <i class="ki-filled ki-wallet text-primary"></i>
                </span>
            </div>
            <div class="projects-funding-kpi-card rounded-xl border border-border bg-card px-4 py-3 flex items-center justify-between gap-3">
                @php
                    $remainingAll = max(0, $totalBudget - $totalExpenses);
                @endphp
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-medium text-muted-foreground uppercase">Remaining vs budget</span>
                    <span class="text-sm font-semibold text-foreground tabular-nums">{{ number_format($remainingAll, 0) }} {{ $currency }}</span>
                </div>
                <span class="inline-flex items-center justify-center rounded-full bg-success/5 size-9">
                    <i class="ki-filled ki-chart-line-up text-success"></i>
                </span>
            </div>
        </div>
    @endif

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($projects->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-wallet text-4xl mb-2"></i>
                    <p class="text-sm">No projects to fund yet.</p>
                    <a href="{{ route('families.projects.create', $family) }}" class="kt-btn kt-btn-outline mt-4">Create project</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[220px]">Project</th>
                                <th class="min-w-[120px]">Budget</th>
                                <th class="min-w-[120px]">Funded</th>
                                <th class="min-w-[120px]">Expenses</th>
                                <th class="min-w-[160px]">Funding progress</th>
                                <th class="w-[120px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                @php
                                    $fundingSum = (float) ($project->fundings_sum_amount ?? 0);
                                    $expenseSum = (float) ($project->expenses_sum_amount ?? 0);
                                    $planned    = (float) $project->planned_budget;
                                    $progress   = $planned > 0 ? min(100, round(($fundingSum / $planned) * 100)) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('families.projects.show', [$family, $project]) }}" class="flex flex-col gap-0.5 hover:text-primary">
                                            <span class="text-sm font-medium text-mono">{{ $project->name }}</span>
                                            @if ($project->description)
                                                <span class="text-xs text-muted-foreground truncate max-w-[260px]">{{ Str::limit($project->description, 80) }}</span>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="tabular-nums text-sm">{{ number_format($planned, 0) }} {{ $currency }}</td>
                                    <td class="tabular-nums text-sm text-success">{{ number_format($fundingSum, 0) }} {{ $currency }}</td>
                                    <td class="tabular-nums text-sm text-destructive">{{ number_format($expenseSum, 0) }} {{ $currency }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 rounded-full bg-muted overflow-hidden">
                                                <div class="h-full bg-primary" style="width: {{ $progress }}%;"></div>
                                            </div>
                                            <span class="text-xs tabular-nums text-muted-foreground">{{ $progress }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('families.projects.funding.create', $family) }}?project_id={{ $project->id }}" class="kt-btn kt-btn-xs kt-btn-primary">
                                            Add funding
                                        </a>
                                    </td>
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
