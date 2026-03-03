@extends('layouts.metronic')

@section('title', 'Reconcile Wallet')
@section('page_title', 'Reconcile Wallet')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reconciliations.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to reconciliation
    </a>

    <form action="{{ route('families.reconciliations.store', $family) }}" method="POST" id="reconcile-form">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:max-w-2xl">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Reconcile wallet</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Enter the actual balance you verified (e.g. from cash count or bank app). The system will compare it to the current system balance and create an adjustment if needed.</p>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="wallet_id" class="kt-form-label max-w-56">Wallet <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="wallet_id" id="wallet_id" required class="kt-select" onchange="window.location.href='{{ route('families.reconciliations.create', $family) }}?wallet_id='+this.value">
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" {{ $selectedWallet && $selectedWallet->id == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    @if ($selectedWallet)
                    <div class="rounded-lg border border-border bg-muted/30 p-4 space-y-2">
                        <p class="text-sm font-medium text-foreground">Current system balance</p>
                        <p class="text-2xl font-semibold tabular-nums">{{ number_format($selectedWallet->balance, 2) }} {{ $selectedWallet->currency_code }}</p>
                        @if ($lastReconciliation)
                            <p class="text-xs text-muted-foreground">Last reconciled: {{ $lastReconciliation->reconciled_at->format('M j, Y') }}
                                @if ((float) $lastReconciliation->difference !== 0.0)
                                    ({{ $lastReconciliation->isSurplus() ? '+' : '−' }}{{ number_format(abs($lastReconciliation->difference), 2) }} {{ $selectedWallet->currency_code }})
                                @endif
                            </p>
                        @else
                            <p class="text-xs text-muted-foreground">Not reconciled yet.</p>
                        @endif
                    </div>
                    @endif

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="actual_balance" class="kt-form-label max-w-56">Actual balance <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="number" name="actual_balance" id="actual_balance" value="{{ old('actual_balance') }}" step="0.01" min="0" placeholder="0.00" class="kt-input" required />
                            <p class="text-xs text-muted-foreground mt-1">The balance you verified (cash count, bank app, statement).</p>
                            @error('actual_balance')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="notes" class="kt-form-label max-w-56">Notes</label>
                        <div class="grow">
                            <textarea name="notes" id="notes" rows="2" class="kt-input" placeholder="Optional">{{ old('notes') }}</textarea>
                            @error('notes')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/20 p-3 text-sm text-amber-800 dark:text-amber-200">
                        <strong>What happens:</strong> If actual ≠ system balance, the system will create a Reconciliation Adjustment (income for surplus, expense for shortage) so the ledger stays correct.
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.reconciliations.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Reconcile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
