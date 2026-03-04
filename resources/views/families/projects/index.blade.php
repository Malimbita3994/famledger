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
        <div class="kt-card rounded-xl border border-border bg-card px-4 py-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-muted-foreground mr-1">Filter:</span>
                <a href="{{ route('families.projects.index', [$family, 'filter' => 'all']) }}" class="kt-btn kt-btn-xs {{ ($filter ?? 'all') === 'all' ? 'kt-btn-primary' : 'kt-btn-outline' }}">All</a>
                <a href="{{ route('families.projects.index', [$family, 'filter' => 'active']) }}" class="kt-btn kt-btn-xs {{ ($filter ?? '') === 'active' ? 'kt-btn-primary' : 'kt-btn-outline' }}">Active</a>
                <a href="{{ route('families.projects.index', [$family, 'filter' => 'completed']) }}" class="kt-btn kt-btn-xs {{ ($filter ?? '') === 'completed' ? 'kt-btn-primary' : 'kt-btn-outline' }}">Completed</a>
                <a href="{{ route('families.projects.index', [$family, 'filter' => 'planning']) }}" class="kt-btn kt-btn-xs {{ ($filter ?? '') === 'planning' ? 'kt-btn-primary' : 'kt-btn-outline' }}">Planning</a>
            </div>
        </div>

        {{-- Cards grid (Metronic 3-columns style) --}}
        <div id="projects_cards">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5 lg:gap-7.5">
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
                    $startLabel = $project->start_date ? $project->start_date->format('M d') : null;
                    $endLabel = $project->target_end_date ? $project->target_end_date->format('M d') : null;
                    $initial = mb_strtoupper(mb_substr($project->name ?? 'P', 0, 1));
                @endphp
                <div
                    class="kt-card flex flex-col rounded-2xl border border-border shadow-sm overflow-hidden bg-card"
                    style="padding: 1.75rem 1.75rem 1.5rem;">
                    <div class="flex items-center justify-between mb-3 lg:mb-6">
                        <div class="flex items-center justify-center size-[50px] rounded-lg bg-accent/60">
                            <span class="text-lg font-semibold text-primary">
                                {{ $initial }}
                            </span>
                        </div>
                        <span class="kt-badge kt-badge-sm {{ $statusBadge }} kt-badge-outline">
                            {{ $projectStatuses[$project->status] ?? ucfirst($project->status) }}
                        </span>
                    </div>

                    <div class="flex flex-col mb-3 lg:mb-6">
                        <a href="{{ route('families.projects.show', [$family, $project]) }}"
                           class="text-lg font-semibold text-mono hover:text-primary mb-px">
                            {{ $project->name }}
                        </a>
                        <span class="text-sm text-secondary-foreground leading-relaxed">
                            {{ $project->description ?: ($projectTypes[$project->type] ?? 'Family project') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-5 mb-3.5 lg:mb-7 text-sm text-secondary-foreground">
                        @if($startLabel)
                            <span>
                                Start:
                                <span class="font-medium text-foreground">{{ $startLabel }}</span>
                            </span>
                        @endif
                        @if($endLabel)
                            <span>
                                End:
                                <span class="font-medium text-foreground">{{ $endLabel }}</span>
                            </span>
                        @endif
                    </div>

                    <div class="kt-progress h-1.5 {{ $spendingPct >= 100 ? 'kt-progress-destructive' : 'kt-progress-primary' }} mb-4 lg:mb-8">
                        <div class="kt-progress-indicator" style="width: {{ max(3, min(100, $spendingPct)) }}%"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground">
                            Budget:
                            <span class="font-medium text-foreground">
                                {{ number_format($planned, 0) }} {{ $project->currency_code }}
                            </span>
                        </span>
                        <div class="flex -space-x-2">
                            <div class="flex">
                                <span class="hover:z-5 relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 ring-background size-[30px] bg-primary/10 text-xs font-semibold text-primary">
                                    {{ mb_substr($family->name, 0, 1) }}
                                </span>
                            </div>
                            <div class="flex">
                                <span class="hover:z-5 relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 ring-background size-[30px] bg-muted text-[11px] font-semibold text-muted-foreground">
                                    +{{ $project->id }}
                                </span>
                            </div>
                        </div>
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
