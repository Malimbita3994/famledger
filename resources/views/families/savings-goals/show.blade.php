@extends('layouts.metronic')

@section('title', 'Savings Goal')
@section('page_title', 'Savings Goal')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.savings-goals.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to savings goals
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

    <div class="grid gap-5 lg:gap-7.5 max-w-4xl">
        <div class="kt-card">
            <div class="kt-card-content flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-foreground">{{ $savingsGoal->name }}</h1>
                    <p class="text-sm text-muted-foreground mt-0.5">Wallet: <a href="{{ route('families.wallets.show', [$family, $savingsGoal->wallet]) }}" class="text-primary hover:underline">{{ $savingsGoal->wallet->name }}</a></p>
                    @if ($savingsGoal->target_date)
                        <p class="text-xs text-muted-foreground mt-1">Target by {{ $savingsGoal->target_date->format('M j, Y') }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('families.savings-goals.contribute', [$family, $savingsGoal]) }}" class="kt-btn kt-btn-primary kt-btn-sm">Contribute</a>
                    <a href="{{ route('families.savings-goals.allocate', [$family, $savingsGoal]) }}" class="kt-btn kt-btn-outline kt-btn-sm">Allocate to budget</a>
                    <a href="{{ route('families.savings-goals.edit', [$family, $savingsGoal]) }}" class="kt-btn kt-btn-ghost kt-btn-sm">Edit</a>
                </div>
            </div>
        </div>

        @php
            $pct = $savingsGoal->completion_percent;
            $barClass = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-primary' : 'bg-muted');
        @endphp
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Progress</h3>
                <span class="kt-badge kt-badge-sm {{ $savingsGoal->is_completed ? 'kt-badge-success' : 'kt-badge-outline' }}">{{ $pct }}%</span>
            </div>
            <div class="kt-card-content space-y-4">
                <div class="h-3 rounded-full bg-muted overflow-hidden">
                    <div class="h-full rounded-full {{ $barClass }}" style="width: {{ min(100, $pct) }}%"></div>
                </div>
                <dl class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Target</dt>
                        <dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ number_format($savingsGoal->target_amount, 2) }} {{ $savingsGoal->currency_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Saved</dt>
                        <dd class="mt-0.5 text-lg font-semibold tabular-nums text-success">{{ number_format($savingsGoal->saved_amount, 2) }} {{ $savingsGoal->currency_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Remaining</dt>
                        <dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ number_format($savingsGoal->remaining_amount, 2) }} {{ $savingsGoal->currency_code }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header flex-wrap gap-2">
                <h3 class="kt-card-title text-sm">Contributions</h3>
                <a href="{{ route('families.savings-goals.contribute', [$family, $savingsGoal]) }}" class="kt-btn kt-btn-sm kt-btn-ghost">Add contribution</a>
            </div>
            <div class="kt-card-content p-0">
                @if ($contributions->isEmpty())
                    <div class="py-8 text-center text-muted-foreground text-sm">No contributions yet.</div>
                @else
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table table-auto kt-table-border">
                            <thead>
                                <tr>
                                    <th class="min-w-[100px]">Date</th>
                                    <th class="min-w-[100px]">Amount</th>
                                    <th class="min-w-[120px]">Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contributions as $c)
                                <tr>
                                    <td>{{ $c->contribution_date->format('M j, Y') }}</td>
                                    <td class="tabular-nums text-success">+ {{ number_format($c->amount, 2) }} {{ $c->currency_code }}</td>
                                    <td class="text-sm">
                                        @if ($c->source_type === 'transfer' && $c->fromWallet)
                                            From {{ $c->fromWallet->name }}
                                        @else
                                            {{ ucfirst($c->source_type) }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-t border-border">
                        {{ $contributions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
