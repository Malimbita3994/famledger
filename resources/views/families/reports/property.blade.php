@extends('layouts.metronic')

@section('title', 'Property Reports')
@section('page_title', 'Property Reports')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Property reports</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Overview of funded projects treated as family assets. Detailed analytics will be added later.
            </p>
        </div>
    </div>

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($projects->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-home-3 text-4xl mb-2"></i>
                    <p class="text-sm">No projects found yet. Create a project and allocate funding to see it here.</p>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[220px]">Asset (project)</th>
                                <th class="min-w-[120px]">Planned budget</th>
                                <th class="min-w-[120px]">Total funded</th>
                                <th class="min-w-[120px]">Total expenses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projects as $project)
                                @php
                                    $fundingSum = (float) ($project->fundings_sum_amount ?? 0);
                                    $expenseSum = (float) ($project->expenses_sum_amount ?? 0);
                                    $planned    = (float) $project->planned_budget;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="text-sm font-medium text-mono">{{ $project->name }}</span>
                                    </td>
                                    <td class="tabular-nums text-sm">{{ number_format($planned, 0) }} {{ $currency }}</td>
                                    <td class="tabular-nums text-sm text-success">{{ number_format($fundingSum, 0) }} {{ $currency }}</td>
                                    <td class="tabular-nums text-sm text-destructive">{{ number_format($expenseSum, 0) }} {{ $currency }}</td>
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

