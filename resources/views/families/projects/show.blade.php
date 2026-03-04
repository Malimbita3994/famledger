@extends('layouts.metronic')

@section('title', $project->name)
@section('page_title', $project->name)

@section('content')
<style>
.project-kpi-grid {
    display: grid !important;
    width: 100% !important;
    column-gap: 1.5rem !important;
    row-gap: 1.5rem !important;
    margin-top: 1.5rem !important;
    margin-bottom: 2rem !important;
}
.project-kpi-grid .project-kpi-card {
    min-width: 0 !important;
    box-sizing: border-box !important;
    margin: 0.5rem !important;
}
@media (min-width: 768px) {
    .project-kpi-grid .project-kpi-card {
        margin: 0.75rem !important;
    }
}
@media (min-width: 1024px) {
    .project-kpi-grid { grid-template-columns: repeat(4, 1fr) !important; }
}
@media (min-width: 768px) and (max-width: 1023px) {
    .project-kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 767px) {
    .project-kpi-grid { grid-template-columns: 1fr !important; }
}
</style>
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.projects.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to projects
    </a>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 flex items-center gap-3 text-green-800">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-semibold text-lg text-mono">{{ $project->name }}</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                {{ \App\Models\Project::statuses()[$project->status] ?? $project->status }}
                @if($project->type) · {{ \App\Models\Project::types()[$project->type] ?? $project->type }}@endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('families.projects.funding.create', $family) }}?project_id={{ $project->id }}" class="kt-btn kt-btn-outline">Add funding</a>
            <a href="{{ route('families.projects.edit', [$family, $project]) }}" class="kt-btn kt-btn-outline">Edit</a>
        </div>
    </div>

    @if($project->description)
    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6" style="padding: 1.25rem 1.5rem;">
        <p class="text-muted-foreground text-sm font-medium mb-1">Description</p>
        <p class="text-foreground text-sm">{{ $project->description }}</p>
    </div>
    @endif

    @php
        $linkedBudget = $project->budget;
        // Prefer linked budget amount when available, fall back to project's own planned_budget field
        $planned = (float) optional($linkedBudget)->amount ?: (float) $project->planned_budget;
        $totalFunding = (float) ($project->fundings_sum_amount ?? 0);
        $totalExpenses = (float) ($project->expenses_sum_amount ?? 0);
        $remaining = $totalFunding - $totalExpenses;
        $spendingPct = $planned > 0 ? round(($totalExpenses / $planned) * 100, 1) : 0;
        $fundingPct = $planned > 0 ? min(100, round(($totalFunding / $planned) * 100, 1)) : 0;
    @endphp

    <div class="project-kpi-grid">
        <div class="project-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <p class="text-muted-foreground text-sm font-medium">Planned budget</p>
            <p class="text-xl font-bold tabular-nums text-foreground mt-1">{{ number_format($planned, 0) }} {{ $project->currency_code }}</p>
        </div>
        <div class="project-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <p class="text-muted-foreground text-sm font-medium">Total funding</p>
            <p class="text-xl font-bold tabular-nums text-green-600 mt-1">{{ number_format($totalFunding, 0) }} {{ $project->currency_code }}</p>
            <p class="text-muted-foreground text-xs mt-0.5">{{ $fundingPct }}% of budget</p>
        </div>
        <div class="project-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <p class="text-muted-foreground text-sm font-medium">Total expenses</p>
            <p class="text-xl font-bold tabular-nums text-destructive mt-1">{{ number_format($totalExpenses, 0) }} {{ $project->currency_code }}</p>
            <p class="text-muted-foreground text-xs mt-0.5">{{ $spendingPct }}% of budget</p>
        </div>
        <div class="project-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
            <p class="text-muted-foreground text-sm font-medium">Remaining funds</p>
            <p class="text-xl font-bold tabular-nums mt-1 {{ $remaining >= 0 ? 'text-green-600' : 'text-destructive' }}">{{ number_format($remaining, 0) }} {{ $project->currency_code }}</p>
        </div>
    </div>

    <div class="kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card mb-6" style="padding: 1.25rem 1.5rem;">
        <p class="text-muted-foreground text-sm font-medium mb-1">Spending vs budget</p>
        <p class="text-lg font-bold tabular-nums text-foreground mb-2">{{ number_format($totalExpenses, 0) }} / {{ number_format($planned, 0) }} {{ $project->currency_code }}</p>
        <div class="kt-progress h-2 {{ $spendingPct >= 100 ? 'kt-progress-destructive' : 'kt-progress-primary' }}">
            <div class="kt-progress-indicator" style="width: {{ min(100, $spendingPct) }}%"></div>
        </div>
        @if($linkedBudget)
            @php
                $budgetUsed = (float) $linkedBudget->used_amount;
                $budgetRemaining = max(0, (float) $linkedBudget->amount - $budgetUsed);
                $budgetPct = (float) $linkedBudget->amount > 0 ? min(100, round(($budgetUsed / (float) $linkedBudget->amount) * 100, 1)) : 0;
            @endphp
            <div class="mt-4 pt-3 border-t border-dashed border-border text-sm text-muted-foreground">
                <div class="flex items-center justify-between mb-1.5">
                    <span>Linked budget: <span class="font-medium text-foreground">{{ $linkedBudget->name }}</span></span>
                    <span class="tabular-nums">{{ number_format($budgetPct, 1) }}%</span>
                </div>
                <div class="flex items-center justify-between text-xs mb-1">
                    <span>Allocated: <span class="font-semibold text-foreground">{{ number_format($linkedBudget->amount, 0) }} {{ $linkedBudget->currency_code }}</span></span>
                    <span>Used: <span class="font-semibold text-destructive">{{ number_format($budgetUsed, 0) }} {{ $linkedBudget->currency_code }}</span></span>
                </div>
                <div class="flex items-center justify-between text-xs mb-2">
                    <span>Remaining</span>
                    <span class="font-semibold {{ $budgetRemaining > 0 ? 'text-foreground' : 'text-destructive' }}">{{ number_format($budgetRemaining, 0) }} {{ $linkedBudget->currency_code }}</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-muted overflow-hidden">
                    <div class="h-full rounded-full {{ $budgetPct >= 100 ? 'bg-red-500' : 'bg-primary' }}" style="width: {{ min(100, $budgetPct) }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="kt-card flex flex-col rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="px-5 py-4 border-b border-border">
                <h3 class="text-base font-semibold text-foreground">Recent funding</h3>
            </div>
            <div class="divide-y divide-border">
                @forelse($project->fundings->take(10) as $f)
                <div class="px-5 py-3 flex items-center justify-between">
                    <span class="text-sm">{{ number_format($f->amount, 0) }} {{ $f->currency_code }}</span>
                    <span class="text-xs text-muted-foreground">{{ $f->funding_date->format('M j, Y') }} · {{ $f->wallet->name ?? '—' }}</span>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-muted-foreground text-sm">No funding yet. <a href="{{ route('families.projects.funding.create', $family) }}?project_id={{ $project->id }}" class="text-primary hover:underline">Add funding</a></div>
                @endforelse
            </div>
        </div>
        <div class="kt-card flex flex-col rounded-xl border border-border shadow-sm overflow-hidden bg-card">
            <div class="px-5 py-4 border-b border-border">
                <h3 class="text-base font-semibold text-foreground">Recent expenses</h3>
            </div>
            <div class="divide-y divide-border">
                @forelse($project->expenses->take(10) as $e)
                <div class="px-5 py-3 flex items-center justify-between">
                    <span class="text-sm">{{ number_format($e->amount, 0) }} {{ $e->currency_code }}</span>
                    <span class="text-xs text-muted-foreground">{{ $e->expense_date->format('M j, Y') }} · {{ $e->category->name ?? '—' }}</span>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-muted-foreground text-sm">No project expenses recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    @if($project->start_date || $project->target_end_date)
    <div class="mt-6 text-sm text-muted-foreground">
        @if($project->start_date) Start: {{ $project->start_date->format('M j, Y') }}. @endif
        @if($project->target_end_date) Target end: {{ $project->target_end_date->format('M j, Y') }}. @endif
        @if($project->actual_end_date) Actual end: {{ $project->actual_end_date->format('M j, Y') }}. @endif
    </div>
    @endif
</div>
@endsection
