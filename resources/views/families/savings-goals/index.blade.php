@extends('layouts.metronic')

@section('title', 'Savings Goals')
@section('page_title', 'Savings Goals')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Savings Goals</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Define targets and build wealth. Progress = sum of contributions.</p>
        </div>
        <a href="{{ route('families.savings-goals.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i>
            New goal
        </a>
    </div>

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

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-content p-0">
            @if ($goals->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-safe text-4xl mb-2"></i>
                    <p class="text-sm">No savings goals yet.</p>
                    <a href="{{ route('families.savings-goals.create', $family) }}" class="kt-btn kt-btn-outline mt-4">New goal</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[180px]">Goal</th>
                                <th class="min-w-[80px]">Priority</th>
                                <th class="min-w-[100px]">Target</th>
                                <th class="min-w-[100px]">Saved</th>
                                <th class="min-w-[80px]">Progress</th>
                                <th class="min-w-[100px]">Wallet</th>
                                <th class="w-[100px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($goals as $goal)
                            @php
                                $pct = $goal->completion_percent;
                                $barClass = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-primary' : 'bg-muted');
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('families.savings-goals.show', [$family, $goal]) }}" class="font-medium text-foreground hover:text-primary">{{ $goal->name }}</a>
                                    @if ($goal->target_date)
                                        <p class="text-xs text-muted-foreground mt-0.5">By {{ $goal->target_date->format('M j, Y') }}</p>
                                    @endif
                                </td>
                                <td><span class="kt-badge kt-badge-sm kt-badge-outline">{{ \App\Models\SavingsGoal::priorities()[$goal->priority] ?? $goal->priority }}</span></td>
                                <td class="tabular-nums">{{ number_format($goal->target_amount, 2) }} {{ $goal->currency_code }}</td>
                                <td class="tabular-nums text-success">{{ number_format($goal->saved_amount, 2) }} {{ $goal->currency_code }}</td>
                                <td class="w-24">
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div class="h-full rounded-full {{ $barClass }}" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ $pct }}%</span>
                                </td>
                                <td>
                                    <a href="{{ route('families.wallets.show', [$family, $goal->wallet]) }}" class="text-primary hover:underline text-sm">{{ $goal->wallet->name }}</a>
                                </td>
                                <td>
                                    <a href="{{ route('families.savings-goals.show', [$family, $goal]) }}" class="kt-btn kt-btn-ghost kt-btn-sm">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-border">
                    {{ $goals->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
