@extends('layouts.metronic')

@section('title', 'New Transfer')
@section('page_title', 'New Transfer')

@section('content')
<style>
 .transfer-main-row {
     display: flex;
     flex-direction: column;
     gap: 1.25rem;
 }

 .transfer-main-row .transfer-main-col {
     width: 100%;
 }

 @media (min-width: 900px) {
     .transfer-main-row {
         flex-direction: row;
     }

     .transfer-main-row .transfer-main-col {
         flex: 1 1 0;
     }
 }
</style>
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.transfers.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to transfers
    </a>

    <form action="{{ route('families.transfers.store', $family) }}" method="POST" id="transfer-form" class="space-y-6">
        @csrf

        <div class="kt-card p-5 lg:p-7.5 max-w-5xl mx-auto">
            <div class="mb-5">
                <h1 class="text-lg font-semibold text-mono">New transfer</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Move money between two wallets in the same family. Both wallets must use the same currency; total family wealth does not change.
                </p>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                {{-- First row: 4 columns (from, to, amount, date) --}}
                <div class="transfer-main-row">
                    <div class="transfer-main-col grid gap-1.5">
                        <label for="from_wallet_id" class="kt-form-label">From wallet <span class="text-destructive">*</span></label>
                        <select name="from_wallet_id" id="from_wallet_id" required class="kt-select" aria-invalid="{{ $errors->has('from_wallet_id') ? 'true' : 'false' }}">
                            <option value="">Select source wallet</option>
                            @foreach ($wallets as $w)
                                <option value="{{ $w->id }}" data-currency="{{ $w->currency_code }}" {{ old('from_wallet_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->currency_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('from_wallet_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="transfer-main-col grid gap-1.5">
                        <label for="to_wallet_id" class="kt-form-label">To wallet <span class="text-destructive">*</span></label>
                        <select name="to_wallet_id" id="to_wallet_id" required class="kt-select" aria-invalid="{{ $errors->has('to_wallet_id') ? 'true' : 'false' }}">
                            <option value="">Select destination wallet</option>
                            @foreach ($wallets as $w)
                                <option value="{{ $w->id }}" data-currency="{{ $w->currency_code }}" {{ old('to_wallet_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->currency_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('to_wallet_id')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="transfer-main-col grid gap-1.5">
                        <label for="amount" class="kt-form-label">Amount <span class="text-destructive">*</span></label>
                        <input
                            type="number"
                            name="amount"
                            id="amount"
                            value="{{ old('amount') }}"
                            required
                            step="0.01"
                            min="0.01"
                            placeholder="0.00"
                            class="kt-input"
                            aria-invalid="{{ $errors->has('amount') ? 'true' : 'false' }}"
                        />
                        @error('amount')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="transfer-main-col grid gap-1.5">
                        <label for="transfer_date" class="kt-form-label">Transfer date <span class="text-destructive">*</span></label>
                        <input
                            type="date"
                            name="transfer_date"
                            id="transfer_date"
                            value="{{ old('transfer_date', now()->format('Y-m-d')) }}"
                            required
                            class="kt-input"
                            aria-invalid="{{ $errors->has('transfer_date') ? 'true' : 'false' }}"
                        />
                        @error('transfer_date')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                <input type="hidden" name="currency_code" id="currency_code" value="{{ old('currency_code', $wallets->first()?->currency_code ?? '') }}" />

                {{-- Second row: description & reference --}}
                <div class="transfer-main-row">
                    <div class="transfer-main-col grid gap-1.5">
                        <label for="description" class="kt-form-label">Description</label>
                        <input
                            type="text"
                            name="description"
                            id="description"
                            value="{{ old('description') }}"
                            placeholder="e.g. Cash withdrawal for groceries"
                            class="kt-input"
                            aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                        />
                        @error('description')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="transfer-main-col grid gap-1.5">
                        <label for="reference" class="kt-form-label">Reference</label>
                        <input
                            type="text"
                            name="reference"
                            id="reference"
                            value="{{ old('reference') }}"
                            placeholder="Optional"
                            class="kt-input"
                            aria-invalid="{{ $errors->has('reference') ? 'true' : 'false' }}"
                        />
                        @error('reference')<p class="kt-form-message">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('families.transfers.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                        <i class="ki-filled ki-check"></i>
                        Record transfer
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var fromSelect = document.getElementById('from_wallet_id');
    var currencyInput = document.getElementById('currency_code');
    if (!fromSelect || !currencyInput) return;
    function syncCurrency() {
        var opt = fromSelect.options[fromSelect.selectedIndex];
        if (opt && opt.value && opt.getAttribute('data-currency')) {
            currencyInput.value = opt.getAttribute('data-currency');
        }
    }
    fromSelect.addEventListener('change', syncCurrency);
    syncCurrency();
})();
</script>
@endpush
@endsection
