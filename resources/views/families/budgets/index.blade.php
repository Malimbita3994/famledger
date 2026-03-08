@extends('layouts.metronic')

@section('title', 'Budgets')
@section('page_title', 'Budgets')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Budgets</h1>
            <p class="text-sm text-muted-foreground mt-0.5 mb-2.5">Plan and monitor spending. Budgets guide decisions; they do not move money.</p>
        </div>
        <a href="{{ route('families.budgets.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i>
            New budget
        </a>
    </div>

    @if($mainBudget)
        @php
            $currency = $mainBudget->currency_code;
            $planned = (float) $mainBudget->amount;
            $subBudgets = $family->budgets()->where('type', '!=', \App\Models\Budget::TYPE_FAMILY)->get();
            $allocatedToSubBudgets = (float) $subBudgets->sum('amount');
            // Used amount across all sub-budgets (out of the main budget)
            $usedAlready = (float) $subBudgets->sum(function ($b) { return $b->used_amount; });
            // Remaining unplanned capacity: main budget minus what is allocated to sub-budgets
            $unplanned = max(0, $planned - $allocatedToSubBudgets);
        @endphp
        <style>
            .budget-summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.75rem;
                width: 100%;
                margin-bottom: 1.5rem;
            }
            .budget-summary-grid-card {
                transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            }
            .budget-summary-grid-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px -10px rgba(0,0,0,0.18);
                border-color: rgba(59,130,246,0.6); /* primary-ish */
            }
        </style>
        <div class="budget-summary-grid">
            <div class="budget-summary-grid-card rounded-xl border border-border bg-card px-4 py-3">
                <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Main budget</div>
                <div class="mt-1.5 text-lg font-semibold tabular-nums">
                    {{ number_format($planned, 2) }} {{ $currency }}
                </div>
            </div>
            <div class="budget-summary-grid-card rounded-xl border border-border bg-card px-4 py-3">
                <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Allocated to sub-budgets</div>
                <div class="mt-1.5 text-lg font-semibold tabular-nums">
                    {{ number_format($allocatedToSubBudgets, 2) }} {{ $currency }}
                </div>
            </div>
            <div class="budget-summary-grid-card rounded-xl border border-border bg-card px-4 py-3">
                <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Unplanned amount</div>
                <div class="mt-1.5 text-lg font-semibold tabular-nums {{ $unplanned > 0 ? 'text-success' : 'text-muted-foreground' }}">
                    {{ number_format($unplanned, 2) }} {{ $currency }}
                </div>
            </div>
            <div class="budget-summary-grid-card rounded-xl border border-border bg-card px-4 py-3">
                <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Used amount already</div>
                <div class="mt-1.5 text-lg font-semibold tabular-nums text-foreground">
                    {{ number_format($usedAlready, 2) }} {{ $currency }}
                </div>
            </div>
        </div>
    @endif

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($budgets->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-chart-pie text-4xl mb-2"></i>
                    <p class="text-sm">No budgets yet.</p>
                    <p class="text-xs mt-1">Create a budget to plan and track spending against a limit.</p>
                    <a href="{{ route('families.budgets.create', $family) }}" class="kt-btn kt-btn-outline mt-4">New budget</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[180px]">Budget</th>
                                <th class="min-w-[100px]">Type</th>
                                <th class="min-w-[100px]">Period</th>
                                <th class="min-w-[100px]">Amount</th>
                                <th class="min-w-[100px]">Used</th>
                                <th class="min-w-[100px]">Remaining</th>
                                <th class="min-w-[80px]">Progress</th>
                                <th class="w-[80px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($budgets as $budget)
                            @php
                                // For the main budget, "Used" should reflect what sub-budgets have spent.
                                if ($mainBudget && $budget->id === $mainBudget->id) {
                                    $used = isset($subBudgets) ? (float) $subBudgets->sum(function ($b) { return $b->used_amount; }) : (float) $budget->used_amount;
                                    $remaining = max(0, (float) $budget->amount - $used);
                                    $pct = (float) $budget->amount > 0 ? min(100, round(($used / (float) $budget->amount) * 100, 1)) : 0;
                                } else {
                                    $used = (float) $budget->used_amount;
                                    $remaining = $budget->remaining_amount;
                                    $pct = $budget->utilization_percent;
                                }
                                $barClass = $pct >= 100 ? 'bg-destructive' : ($pct >= 75 ? 'bg-amber-500' : 'bg-primary');
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('families.budgets.show', [$family, $budget]) }}" class="font-medium text-foreground hover:text-primary">
                                        {{ $budget->name }}
                                        @if ($budget->type === \App\Models\Budget::TYPE_FAMILY)
                                            <span class="ml-1 inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary uppercase tracking-wide">
                                                Main
                                            </span>
                                        @endif
                                    </a>
                                    @if ($budget->type === 'wallet' && $budget->wallets->isNotEmpty())
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ $budget->wallets->pluck('name')->join(', ') }}</p>
                                    @endif
                                    @if ($budget->type === 'category' && $budget->categories->isNotEmpty())
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ $budget->categories->pluck('name')->join(', ') }}</p>
                                    @endif
                                </td>
                                <td class="text-foreground">{{ \App\Models\Budget::types()[$budget->type] ?? $budget->type }}</td>
                                <td class="text-foreground text-sm">{{ $budget->start_date->format('M j') }} – {{ $budget->end_date->format('M j, Y') }}</td>
                                <td class="tabular-nums">{{ number_format($budget->amount, 2) }} {{ $budget->currency_code }}</td>
                                <td class="tabular-nums {{ $used > 0 ? 'text-foreground' : 'text-muted-foreground' }}">{{ number_format($used, 2) }} {{ $budget->currency_code }}</td>
                                <td class="tabular-nums {{ $remaining > 0 ? 'text-success' : 'text-destructive' }}">{{ number_format($remaining, 2) }} {{ $budget->currency_code }}</td>
                                <td class="w-24">
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div class="h-full rounded-full {{ $barClass }}" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ $pct }}%</span>
                                </td>
                                <td>
                                    <a href="{{ route('families.budgets.show', [$family, $budget]) }}" class="kt-btn kt-btn-ghost kt-btn-sm">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-border">
                    {{ $budgets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
