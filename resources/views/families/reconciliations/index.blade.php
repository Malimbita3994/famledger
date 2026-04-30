@extends('layouts.metronic')

@section('title', 'Reconciliation')
@section('page_title', 'Reconciliation')

@section('content')
<div class="kt-container-fixed w-full max-w-full min-w-0 px-5 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="mb-5 sm:mb-6">
        <x-fin-back-link href="{{ route('families.overview') }}">
            Back to {{ $family->name }}
        </x-fin-back-link>
    </div>

    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6 mb-8">
        <div class="space-y-2 min-w-0 flex-1">
            <h1 class="font-medium text-lg text-mono leading-snug">Reconciliation</h1>
            <p class="text-sm text-muted-foreground leading-relaxed max-w-prose">Verify each wallet’s system balance matches the actual balance. Trust but verify.</p>
        </div>
        <a href="{{ route('families.reconciliations.create') }}" class="kt-btn kt-btn-primary shrink-0 w-full sm:w-auto justify-center inline-flex items-center gap-2">
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

    <div class="kt-card kt-card-grid min-w-full overflow-hidden rounded-xl border border-border/50 shadow-sm">
        <div class="kt-card-header flex-col items-stretch sm:flex-row sm:items-center gap-4 px-5 py-4 sm:px-6 sm:py-5 border-b border-border/40">
            <h3 class="kt-card-title text-sm font-semibold shrink-0">Reconciliation history</h3>
            <form method="get" action="{{ route('families.reconciliations.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3 sm:ms-auto sm:justify-end w-full sm:w-auto min-w-0">
                <label for="wallet_id" class="text-sm font-medium text-muted-foreground sm:pt-0.5 shrink-0">{{ __('Wallet') }}</label>
                <select name="wallet_id" id="wallet_id" class="kt-select kt-select-sm w-full sm:w-auto min-w-0 sm:min-w-[12rem]" onchange="this.form.submit()">
                    <option value="">{{ __('All wallets') }}</option>
                    @foreach ($wallets as $w)
                        <option value="{{ $w->id }}" {{ request('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="kt-card-content p-0">
            @if ($reconciliations->isEmpty())
                <div class="px-5 py-12 sm:px-8 text-center text-muted-foreground">
                    <i class="ki-filled ki-check-circle text-4xl mb-3 opacity-80"></i>
                    <p class="text-sm font-medium text-foreground">No reconciliations yet.</p>
                    <p class="text-xs sm:text-sm mt-2 leading-relaxed max-w-md mx-auto px-1">Reconcile a wallet to verify system balance matches actual (e.g. cash count or bank balance).</p>
                    <a href="{{ route('families.reconciliations.create') }}" class="kt-btn kt-btn-primary mt-6 inline-flex">Reconcile wallet</a>
                </div>
            @else
                <div class="kt-scrollable-x-auto px-3 sm:px-5">
                    <table class="kt-table table-auto kt-table-border text-sm [&_th]:px-3 [&_th]:py-3.5 [&_th]:align-bottom [&_td]:px-3 [&_td]:py-3.5 sm:[&_th]:px-4 sm:[&_td]:px-4">
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
                                <td class="text-foreground whitespace-nowrap align-top">{{ $rec->reconciled_at->format('M j, Y H:i') }}</td>
                                <td class="align-top">
                                    <a href="{{ route('families.wallets.show', $rec->wallet) }}" class="text-primary hover:underline leading-snug">{{ $rec->wallet->name }}</a>
                                    <span class="text-muted-foreground text-xs"> ({{ $rec->wallet->currency_code }})</span>
                                </td>
                                <td class="tabular-nums align-top whitespace-nowrap">{{ number_format($rec->system_balance, 2) }} {{ $rec->wallet->currency_code }}</td>
                                <td class="tabular-nums align-top whitespace-nowrap">{{ number_format($rec->actual_balance, 2) }} {{ $rec->wallet->currency_code }}</td>
                                <td class="tabular-nums align-top whitespace-nowrap">
                                    @if ((float) $rec->difference === 0.0)
                                        <span class="text-success">Balanced</span>
                                    @elseif ($rec->isSurplus())
                                        <span class="text-success">+ {{ number_format($rec->difference, 2) }} {{ $rec->wallet->currency_code }}</span>
                                    @else
                                        <span class="text-destructive">− {{ number_format(abs($rec->difference), 2) }} {{ $rec->wallet->currency_code }}</span>
                                    @endif
                                </td>
                                <td class="text-muted-foreground text-sm align-top">{{ $rec->createdBy?->name ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 sm:px-6 border-t border-border/60 bg-muted/20">
                    {{ $reconciliations->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
