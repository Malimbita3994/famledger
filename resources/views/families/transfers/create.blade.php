@extends('layouts.metronic')

@section('title', 'New Transfer')
@section('page_title', 'New Transfer')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.transfers.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to transfers
    </a>

    <form action="{{ route('families.transfers.store', $family) }}" method="POST" id="transfer-form">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">New transfer</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Move money between two wallets. Same family only; both wallets must use the same currency. Total family wealth does not change.</p>

                    {{-- 1. From Wallet --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="from_wallet_id" class="kt-form-label max-w-56">From wallet <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="from_wallet_id" id="from_wallet_id" required class="kt-select" aria-invalid="{{ $errors->has('from_wallet_id') ? 'true' : 'false' }}">
                                <option value="">Select source wallet</option>
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" data-currency="{{ $w->currency_code }}" {{ old('from_wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('from_wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 2. To Wallet --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="to_wallet_id" class="kt-form-label max-w-56">To wallet <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="to_wallet_id" id="to_wallet_id" required class="kt-select" aria-invalid="{{ $errors->has('to_wallet_id') ? 'true' : 'false' }}">
                                <option value="">Select destination wallet</option>
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" data-currency="{{ $w->currency_code }}" {{ old('to_wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('to_wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 3. Amount --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="amount" class="kt-form-label max-w-56">Amount <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0.01" placeholder="0.00" class="kt-input" aria-invalid="{{ $errors->has('amount') ? 'true' : 'false' }}" />
                            <p class="text-xs text-muted-foreground mt-1">Currency will match the source wallet.</p>
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <input type="hidden" name="currency_code" id="currency_code" value="{{ old('currency_code', $wallets->first()?->currency_code ?? '') }}" />

                    {{-- 4. Date --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="transfer_date" class="kt-form-label max-w-56">Transfer date <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="date" name="transfer_date" id="transfer_date" value="{{ old('transfer_date', now()->format('Y-m-d')) }}" required class="kt-input" aria-invalid="{{ $errors->has('transfer_date') ? 'true' : 'false' }}" />
                            @error('transfer_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 5. Description --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="description" class="kt-form-label max-w-56">Description</label>
                        <div class="grow">
                            <input type="text" name="description" id="description" value="{{ old('description') }}" placeholder="e.g. Cash withdrawal for groceries" class="kt-input" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" />
                            @error('description')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 6. Reference --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="reference" class="kt-form-label max-w-56">Reference</label>
                        <div class="grow">
                            <input type="text" name="reference" id="reference" value="{{ old('reference') }}" placeholder="Optional" class="kt-input" aria-invalid="{{ $errors->has('reference') ? 'true' : 'false' }}" />
                            @error('reference')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.transfers.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Record transfer
                        </button>
                    </div>
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
