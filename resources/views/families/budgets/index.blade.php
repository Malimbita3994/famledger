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
            <p class="text-sm text-muted-foreground mt-0.5">Plan and monitor spending. Budgets guide decisions; they do not move money.</p>
        </div>
        <a href="{{ route('families.budgets.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i>
            New budget
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
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
                                $used = $budget->used_amount;
                                $remaining = $budget->remaining_amount;
                                $pct = $budget->utilization_percent;
                                $barClass = $pct >= 100 ? 'bg-destructive' : ($pct >= 75 ? 'bg-amber-500' : 'bg-primary');
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('families.budgets.show', [$family, $budget]) }}" class="font-medium text-foreground hover:text-primary">{{ $budget->name }}</a>
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
