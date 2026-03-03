@extends('layouts.metronic')

@section('title', 'Family Projects')
@section('page_title', 'Family Projects')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-col gap-5 lg:gap-7.5">
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div>
                <h1 class="font-semibold text-lg text-mono">Family Projects</h1>
                <p class="text-sm text-muted-foreground mt-0.5">Goal-driven initiatives with budget, funding, and timeline.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('families.projects.funding.create', $family) }}" class="kt-btn kt-btn-outline">
                    <i class="ki-filled ki-wallet"></i>
                    Add funding
                </a>
                <a href="{{ route('families.projects.create', $family) }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i>
                    New project
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
                <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center gap-3 text-red-800 dark:text-red-200">
                <i class="ki-filled ki-information-2 text-xl shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('families.projects.index', [$family, 'filter' => 'all']) }}" class="kt-btn kt-btn-sm {{ ($filter ?? 'all') === 'all' ? 'kt-btn-primary' : 'kt-btn-outline' }}">All</a>
            <a href="{{ route('families.projects.index', [$family, 'filter' => 'active']) }}" class="kt-btn kt-btn-sm {{ ($filter ?? '') === 'active' ? 'kt-btn-primary' : 'kt-btn-outline' }}">Active</a>
            <a href="{{ route('families.projects.index', [$family, 'filter' => 'completed']) }}" class="kt-btn kt-btn-sm {{ ($filter ?? '') === 'completed' ? 'kt-btn-primary' : 'kt-btn-outline' }}">Completed</a>
            <a href="{{ route('families.projects.index', [$family, 'filter' => 'planning']) }}" class="kt-btn kt-btn-sm {{ ($filter ?? '') === 'planning' ? 'kt-btn-primary' : 'kt-btn-outline' }}">Planning</a>
        </div>

        {{-- Cards grid (dashboard-style) --}}
        <div id="projects_cards">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse ($projects as $project)
                @php
                    $fundingSum = (float) ($project->fundings_sum_amount ?? 0);
                    $expenseSum = (float) ($project->expenses_sum_amount ?? 0);
                    $planned = (float) $project->planned_budget;
                    $spendingPct = $planned > 0 ? min(100, round(($expenseSum / $planned) * 100, 1)) : 0;
                    $statusBadge = match($project->status) {
                        'active' => 'kt-badge-primary',
                        'completed' => 'kt-badge-success',
                        'planning' => 'kt-badge-outline',
                        'on_hold' => 'kt-badge-warning',
                        'cancelled' => 'kt-badge-destructive',
                        default => 'kt-badge-outline',
                    };
                @endphp
                <div class="kt-card flex flex-col rounded-xl border border-border shadow-sm overflow-hidden bg-card" style="padding: 1.25rem 1.5rem;">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-muted-foreground text-sm font-medium">{{ $projectStatuses[$project->status] ?? $project->status }}</span>
                        <span class="rounded-full size-10 flex items-center justify-center bg-primary/10 text-primary shrink-0">
                            <i class="ki-filled ki-briefcase text-xl"></i>
                        </span>
                    </div>
                    <a href="{{ route('families.projects.show', [$family, $project]) }}" class="text-xl font-bold text-foreground hover:text-primary mb-1 block">{{ $project->name }}</a>
                    <p class="text-sm text-muted-foreground line-clamp-2 min-h-[2.5rem]">{{ $project->description ?: ($projectTypes[$project->type] ?? 'Project') }}</p>
                    @if($project->start_date || $project->target_end_date)
                    <div class="text-muted-foreground text-sm mt-2">
                        @if($project->start_date) Start: <span class="font-medium text-foreground">{{ $project->start_date->format('M j') }}</span>@endif
                        @if($project->start_date && $project->target_end_date) · @endif
                        @if($project->target_end_date) End: <span class="font-medium text-foreground">{{ $project->target_end_date->format('M j') }}</span>@endif
                    </div>
                    @endif
                    <div class="mt-4 pt-4 border-t border-border">
                        <p class="text-muted-foreground text-sm font-medium mb-1">Spending vs budget</p>
                        <div class="text-lg font-bold text-foreground tabular-nums">{{ number_format($expenseSum, 0) }} / {{ number_format($planned, 0) }} {{ $project->currency_code }}</div>
                        <div class="kt-progress h-2 {{ $spendingPct >= 100 ? 'kt-progress-destructive' : 'kt-progress-primary' }} mt-2">
                            <div class="kt-progress-indicator" style="width: {{ min(100, $spendingPct) }}%"></div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-2">
                        <span class="text-sm text-muted-foreground">Funding: <span class="font-medium text-foreground">{{ number_format($fundingSum, 0) }} {{ $project->currency_code }}</span></span>
                        <a href="{{ route('families.projects.show', [$family, $project]) }}" class="kt-btn kt-btn-sm kt-btn-ghost text-primary shrink-0">View</a>
                    </div>
                </div>
                @empty
                <div class="col-span-full kt-card rounded-xl border border-border shadow-sm overflow-hidden p-12 text-center">
                    <i class="ki-filled ki-folder text-4xl text-muted-foreground mb-4"></i>
                    <h3 class="text-base font-semibold text-foreground">No projects yet</h3>
                    <p class="text-sm text-muted-foreground mt-1">Create a project to track funding, budget, and expenses for a goal.</p>
                    <a href="{{ route('families.projects.create', $family) }}" class="kt-btn kt-btn-primary mt-6">New project</a>
                </div>
                @endforelse
            </div>
            @if($projects->hasPages())
            <div class="flex justify-center pt-5 lg:pt-7.5">
                {{ $projects->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
