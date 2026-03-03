@extends('layouts.metronic')

@section('title', 'Reconciliation')
@section('page_title', 'Reconciliation')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Reconciliation</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Verify each wallet’s system balance matches the actual balance. Trust but verify.</p>
        </div>
        <a href="{{ route('families.reconciliations.create', $family) }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-check-circle"></i>
            Reconcile wallet
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
        <div class="kt-card-header flex-wrap gap-2">
            <h3 class="kt-card-title text-sm">Reconciliation history</h3>
            <form method="get" action="{{ route('families.reconciliations.index', $family) }}" class="flex items-center gap-2">
                <label for="wallet_id" class="text-sm text-muted-foreground">Wallet</label>
                <select name="wallet_id" id="wallet_id" class="kt-select kt-select-sm w-auto" onchange="this.form.submit()">
                    <option value="">All wallets</option>
                    @foreach ($wallets as $w)
                        <option value="{{ $w->id }}" {{ request('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="kt-card-content p-0">
            @if ($reconciliations->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-check-circle text-4xl mb-2"></i>
                    <p class="text-sm">No reconciliations yet.</p>
                    <p class="text-xs mt-1">Reconcile a wallet to verify system balance matches actual (e.g. cash count or bank balance).</p>
                    <a href="{{ route('families.reconciliations.create', $family) }}" class="kt-btn kt-btn-outline mt-4">Reconcile wallet</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[120px]">Date</th>
                                <th class="min-w-[140px]">Wallet</th>
                                <th class="min-w-[100px]">System</th>
                                <th class="min-w-[100px]">Actual</th>
                                <th class="min-w-[100px]">Difference</th>
                                <th class="min-w-[100px]">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reconciliations as $rec)
                            <tr>
                                <td class="text-foreground">{{ $rec->reconciled_at->format('M j, Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('families.wallets.show', [$family, $rec->wallet]) }}" class="text-primary hover:underline">{{ $rec->wallet->name }}</a>
                                    <span class="text-muted-foreground text-xs">({{ $rec->wallet->currency_code }})</span>
                                </td>
                                <td class="tabular-nums">{{ number_format($rec->system_balance, 2) }} {{ $rec->wallet->currency_code }}</td>
                                <td class="tabular-nums">{{ number_format($rec->actual_balance, 2) }} {{ $rec->wallet->currency_code }}</td>
                                <td class="tabular-nums">
                                    @if ((float) $rec->difference === 0.0)
                                        <span class="text-success">Balanced</span>
                                    @elseif ($rec->isSurplus())
                                        <span class="text-success">+ {{ number_format($rec->difference, 2) }} {{ $rec->wallet->currency_code }}</span>
                                    @else
                                        <span class="text-destructive">− {{ number_format(abs($rec->difference), 2) }} {{ $rec->wallet->currency_code }}</span>
                                    @endif
                                </td>
                                <td class="text-muted-foreground text-sm">{{ $rec->createdBy?->name ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-border">
                    {{ $reconciliations->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
