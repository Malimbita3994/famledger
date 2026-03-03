@extends('layouts.metronic')

@section('title', $budget->name)
@section('page_title', $budget->name)

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.budgets.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to budgets
    </a>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5 max-w-4xl">
        <div class="kt-card">
            <div class="kt-card-content flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-foreground">{{ $budget->name }}</h1>
                    <p class="text-sm text-muted-foreground mt-0.5">{{ \App\Models\Budget::types()[$budget->type] ?? $budget->type }} · {{ $budget->start_date->format('M j, Y') }} – {{ $budget->end_date->format('M j, Y') }}</p>
                    @if ($budget->type === 'wallet' && $budget->wallets->isNotEmpty())
                        <p class="text-xs text-muted-foreground mt-1">Wallets: {{ $budget->wallets->pluck('name')->join(', ') }}</p>
                    @endif
                    @if ($budget->type === 'category' && $budget->categories->isNotEmpty())
                        <p class="text-xs text-muted-foreground mt-1">Categories: {{ $budget->categories->pluck('name')->join(', ') }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('families.budgets.edit', [$family, $budget]) }}" class="kt-btn kt-btn-sm kt-btn-ghost">Edit</a>
                    <form action="{{ route('families.budgets.destroy', [$family, $budget]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this budget?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost text-destructive">Remove</button>
                    </form>
                </div>
            </div>
        </div>

        @php
            $used = $budget->used_amount;
            $remaining = $budget->remaining_amount;
            $pct = $budget->utilization_percent;
            $barClass = $pct >= 100 ? 'bg-destructive' : ($pct >= 75 ? 'bg-amber-500' : 'bg-primary');
        @endphp
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Spending progress</h3>
                <span class="kt-badge kt-badge-sm {{ $budget->is_exceeded ? 'kt-badge-danger' : ($pct >= 75 ? 'kt-badge-warning' : 'kt-badge-success') }} kt-badge-outline">{{ $pct }}% used</span>
            </div>
            <div class="kt-card-content space-y-4">
                <div class="h-3 rounded-full bg-muted overflow-hidden">
                    <div class="h-full rounded-full {{ $barClass }} transition-all" style="width: {{ min(100, $pct) }}%"></div>
                </div>
                <dl class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Budget amount</dt>
                        <dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ number_format($budget->amount, 2) }} {{ $budget->currency_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Used</dt>
                        <dd class="mt-0.5 text-lg font-semibold tabular-nums text-foreground">{{ number_format($used, 2) }} {{ $budget->currency_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Remaining</dt>
                        <dd class="mt-0.5 text-lg font-semibold tabular-nums {{ $remaining >= 0 ? 'text-success' : 'text-destructive' }}">{{ number_format($remaining, 2) }} {{ $budget->currency_code }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Details</h3>
            </div>
            <div class="kt-card-content">
                <dl class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Recurrence</dt>
                        <dd class="mt-0.5">{{ \App\Models\Budget::recurrences()[$budget->recurrence] ?? $budget->recurrence }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Status</dt>
                        <dd class="mt-0.5">
                            <span class="kt-badge kt-badge-sm {{ $budget->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">{{ ucfirst($budget->status) }}</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
