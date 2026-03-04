@extends('layouts.metronic')

@section('title', 'Reconcile Wallet')
@section('page_title', 'Reconcile Wallet')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.reconciliations.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to reconciliation
    </a>

    <style>
    .reconcile-main-row {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .reconcile-main-row .reconcile-main-col {
        width: 100%;
        transition: transform 160ms ease-out, box-shadow 160ms ease-out, border-color 160ms ease-out, background-color 160ms ease-out;
    }
    .reconcile-main-row .reconcile-main-col:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.10);
        border-color: rgba(59, 130, 246, 0.6);
        background-color: rgba(249, 250, 251, 0.95);
    }
    @media (min-width: 900px) {
        .reconcile-main-row {
            flex-direction: row;
        }
        .reconcile-main-row .reconcile-main-col {
            flex: 1 1 0;
        }
    }
    </style>

    <form action="{{ route('families.reconciliations.store', $family) }}" method="POST" id="reconcile-form">
        @csrf

        <div class="kt-card p-5 lg:p-7.5 max-w-4xl">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-lg font-semibold text-mono">Reconcile wallet</h1>
                    <p class="text-sm text-muted-foreground mt-0.5">
                        Enter the verified balance for a wallet and FamLedger will create an automatic adjustment if there is any difference.
                    </p>
                </div>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                <div class="reconcile-main-row">
                    <div class="reconcile-main-col p-3 rounded-lg border border-border bg-muted/40">
                        <label for="wallet_id" class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5 block">
                            Wallet <span class="text-destructive">*</span>
                        </label>
                        <select name="wallet_id" id="wallet_id" required class="kt-select w-full"
                                onchange="window.location.href='{{ route('families.reconciliations.create', $family) }}?wallet_id='+this.value">
                            @foreach ($wallets as $w)
                                <option value="{{ $w->id }}" {{ $selectedWallet && $selectedWallet->id == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->currency_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                    </div>

                    @if ($selectedWallet)
                    <div class="reconcile-main-col p-3 rounded-lg border border-border bg-muted/40">
                        <div class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5">Current system balance</div>
                        <div class="text-xl lg:text-2xl font-semibold tabular-nums text-foreground">
                            {{ number_format($selectedWallet->balance, 2) }} {{ $selectedWallet->currency_code }}
                        </div>
                        @if ($lastReconciliation)
                            <p class="text-xs text-muted-foreground mt-1">
                                Last reconciled: {{ $lastReconciliation->reconciled_at->format('M j, Y') }}
                                @if ((float) $lastReconciliation->difference !== 0.0)
                                    ({{ $lastReconciliation->isSurplus() ? '+' : '−' }}{{ number_format(abs($lastReconciliation->difference), 2) }} {{ $selectedWallet->currency_code }})
                                @endif
                            </p>
                        @else
                            <p class="text-xs text-muted-foreground mt-1">Not reconciled yet.</p>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="reconcile-main-row">
                    <div class="reconcile-main-col p-3 rounded-lg border border-border bg-muted/40">
                        <label for="actual_balance" class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5 block">
                            Actual balance <span class="text-destructive">*</span>
                        </label>
                        <input type="number" name="actual_balance" id="actual_balance"
                               value="{{ old('actual_balance') }}" step="0.01" min="0"
                               placeholder="0.00" class="kt-input w-full" required />
                        <p class="text-xs text-muted-foreground mt-1">
                            The real balance you see (cash count, bank app, or statement).
                        </p>
                        @error('actual_balance')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="reconcile-main-col p-3 rounded-lg border border-border bg-muted/40">
                        <label for="notes" class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5 block">
                            Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3" class="kt-input w-full" placeholder="Optional details about this reconciliation">{{ old('notes') }}</textarea>
                        @error('notes')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/20 p-3 text-sm text-amber-800 dark:text-amber-200">
                    <strong>What happens:</strong> If the actual balance is different from the system balance, FamLedger will create a
                    <span class="font-semibold">Reconciliation Adjustment</span> (income for surplus, expense for shortage) so the ledger stays correct.
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
    </form>
</div>
@endsection
