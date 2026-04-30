@extends('layouts.metronic')

@section('title', 'Funding')
@section('page_title', 'Funding')

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
        <x-fin-back-link href="{{ route('families.overview') }}">
        Back to {{ $family->name }}
    </x-fin-back-link>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-semibold text-lg text-mono">Funding</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Allocate funds from family wallets to projects.</p>
        </div>
        <a href="{{ route('families.projects.funding.create') }}" class="kt-btn kt-btn-primary">
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
            <x-famledger.pulse-stat-card
                label="Total budget"
                :value="number_format($totalBudget, 0) . ' ' . $currency"
            />

            <x-famledger.pulse-stat-card
                label="Total funded"
                :value="number_format($totalFunding, 0) . ' ' . $currency"
            />

            @php
                $remainingAll = max(0, $totalBudget - $totalExpenses);
            @endphp
            <x-famledger.pulse-stat-card
                label="Remaining vs budget"
                :value="number_format($remainingAll, 0) . ' ' . $currency"
            />
        </div>
    @endif

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($projects->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-wallet text-4xl mb-2"></i>
                    <p class="text-sm">No projects to fund yet.</p>
                    <a href="{{ route('families.projects.create') }}" class="kt-btn kt-btn-primary mt-4">Create project</a>
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
                                <th class="w-[60px]">ACTION</th>
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
                                        <button type="button" class="flex flex-col gap-0.5 hover:text-primary text-start max-w-full bg-transparent border-0 p-0 cursor-pointer font-inherit" data-project-funding-index-detail="{{ $project->id }}">
                                            <span class="text-sm font-medium text-mono">{{ $project->name }}</span>
                                            @if ($project->description)
                                                <span class="text-xs text-muted-foreground truncate max-w-[260px]">{{ Str::limit($project->description, 80) }}</span>
                                            @endif
                                        </button>
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
                                        <div class="kt-menu flex-inline" data-kt-menu="true">
                                            <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button" aria-label="{{ __('Actions') }}">
                                                    <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                </button>
                                                <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                    <div class="kt-menu-item">
                                                        <button type="button" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer" data-project-funding-index-detail="{{ $project->id }}">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                            <span class="kt-menu-title">{{ __('View') }}</span>
                                                        </button>
                                                    </div>
                                                    <div class="kt-menu-item">
                                                        <a class="kt-menu-link" href="{{ route('families.projects.edit', $project) }}">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                            <span class="kt-menu-title">{{ __('Edit') }}</span>
                                                        </a>
                                                    </div>
                                                    <div class="kt-menu-item">
                                                        <a class="kt-menu-link" href="{{ route('families.expenses.create', ['project_id' => $project->id]) }}">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-arrow-down"></i></span>
                                                            <span class="kt-menu-title">{{ __('Record expense') }}</span>
                                                        </a>
                                                    </div>
                                                    <div class="kt-menu-separator"></div>
                                                    <div class="kt-menu-item">
                                                        <form action="{{ route('families.projects.destroy', $project) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="{{ __('Delete this project?') }}" data-confirm-message="{{ __('This cannot be undone. Projects with funding or expenses cannot be deleted.') }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-trash"></i></span>
                                                                <span class="kt-menu-title">{{ __('Delete') }}</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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

<x-famledger.entity-detail-modal
    id="project_funding_index_detail_modal"
    :title="__('Project details')"
    :payloads="$projectFundingIndexModalPayloads"
    :open-on-load="null"
    variant="grid4"
    trigger-attribute="data-project-funding-index-detail"
/>
@endsection
