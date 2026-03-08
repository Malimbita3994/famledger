@extends('layouts.metronic')

@section('title', 'Allocate to Budget')
@section('page_title', 'Allocate to Budget')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.savings-goals.show', [$family, $savingsGoal]) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to goal
    </a>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center gap-3 text-red-800 dark:text-red-200">
            <i class="ki-filled ki-information-2 text-xl shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card pb-2.5 max-w-2xl">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Allocate to Budget</h3>
            </div>
            <div class="kt-card-content grid gap-5">
                <p class="text-sm text-muted-foreground">
                    Allocate accumulated savings from <strong>{{ $savingsGoal->name }}</strong> to a budget.
                    The allocated amount will count toward the budget's used total and help track spending against the limit.
                </p>

                <form action="{{ route('families.savings-goals.allocate.store', [$family, $savingsGoal]) }}" method="POST">
                    @csrf

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5 mb-5">
                        <label for="amount" class="kt-form-label max-w-56">Amount to allocate <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0.01" placeholder="0.00" class="kt-input" />
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-muted-foreground mt-1">Available to allocate: <strong>{{ number_format($availableToAllocate, 2) }} {{ $savingsGoal->currency_code }}</strong></p>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5 mb-5">
                        <label for="budget_id" class="kt-form-label max-w-56">Target budget <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="budget_id" id="budget_id" required class="kt-select">
                                <option value="">Select a budget</option>
                                @foreach ($budgets as $budget)
                                    <option value="{{ $budget->id }}" {{ old('budget_id') == $budget->id ? 'selected' : '' }}>
                                        {{ $budget->name }} ({{ \App\Models\Budget::types()[$budget->type] ?? $budget->type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('budget_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5 mb-5">
                        <label for="reason" class="kt-form-label max-w-56">Reason (optional)</label>
                        <div class="grow">
                            <textarea name="reason" id="reason" rows="3" class="kt-input" placeholder="e.g. Releasing funds for clothing budget">{{ old('reason') }}</textarea>
                            @error('reason')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.savings-goals.show', [$family, $savingsGoal]) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Allocate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if ($allocations->isNotEmpty())
            <div class="kt-card max-w-2xl">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Recent allocations</h3>
                    <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $allocations->count() }}</span>
                </div>
                <div class="kt-card-content">
                    <div class="space-y-3">
                        @foreach ($allocations as $allocation)
                            <div class="flex items-center justify-between p-3 border border-border rounded-lg">
                                <div>
                                    <p class="font-medium text-foreground">{{ $allocation->budget->name }}</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ $allocation->allocated_date->format('M j, Y') }}</p>
                                    @if ($allocation->reason)
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ $allocation->reason }}</p>
                                    @endif
                                </div>
                                <div class="text-right tabular-nums">
                                    <p class="font-semibold text-foreground">{{ number_format($allocation->amount, 2) }} {{ $allocation->currency_code }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
