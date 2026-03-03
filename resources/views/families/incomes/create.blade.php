@extends('layouts.metronic')

@section('title', 'Record Income')
@section('page_title', 'Record Income')

@section('content')
<style>
@media (min-width: 1024px) {
    .income-main-row {
        display: flex;
        gap: 1.75rem;
    }
    .income-main-row .income-main-col {
        flex: 1 1 0;
    }
}
</style>
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.incomes.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to income
    </a>

    <form action="{{ route('families.incomes.store', $family) }}" method="POST" id="income-form">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[60rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Record income</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">All income must go into a wallet. Select the wallet first, then enter amount and details.</p>

                    <div>
                        <h3 class="text-sm font-semibold text-foreground mb-3">
                            Basic Information
                        </h3>
                        <div class="income-main-row">
                            {{-- Wallet --}}
                            <div class="income-main-col grid gap-1.5">
                                <label for="wallet_id" class="kt-form-label">Wallet <span class="text-destructive">*</span></label>
                                <select name="wallet_id" id="wallet_id" required class="kt-select w-full" aria-invalid="{{ $errors->has('wallet_id') ? 'true' : 'false' }}">
                                    <option value="">Select a wallet</option>
                                    @foreach ($wallets as $w)
                                        <option value="{{ $w->id }}" data-currency="{{ $w->currency_code }}" {{ old('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                    @endforeach
                                </select>
                                @error('wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Amount --}}
                            <div class="income-main-col grid gap-1.5">
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
                                <p class="text-xs text-muted-foreground mt-1">Currency will match the selected wallet.</p>
                                @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Category --}}
                            <div class="income-main-col grid gap-1.5">
                                <label for="category_id" class="kt-form-label">Category</label>
                                <select name="category_id" id="category_id" class="kt-select w-full" aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}">
                                    <option value="">— Optional —</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Received date --}}
                            <div class="income-main-col grid gap-1.5">
                                <label for="received_date" class="kt-form-label">Received date <span class="text-destructive">*</span></label>
                                <input
                                    type="date"
                                    name="received_date"
                                    id="received_date"
                                    value="{{ old('received_date', now()->format('Y-m-d')) }}"
                                    required
                                    class="kt-input"
                                    aria-invalid="{{ $errors->has('received_date') ? 'true' : 'false' }}"
                                />
                                @error('received_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="currency_code" id="currency_code" value="{{ old('currency_code', $wallets->first()?->currency_code ?? '') }}" />

                    {{-- Additional details --}}
                    <div class="grid gap-5">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label for="source" class="kt-form-label max-w-56">Source</label>
                            <div class="grow">
                                <input type="text" name="source" id="source" value="{{ old('source') }}" placeholder="e.g. Company XYZ, Gift from relative" class="kt-input" aria-invalid="{{ $errors->has('source') ? 'true' : 'false' }}" />
                                @error('source')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label for="notes" class="kt-form-label max-w-56">Notes</label>
                            <div class="grow">
                                <textarea name="notes" id="notes" rows="3" placeholder="Optional notes" class="kt-textarea resize-y" aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}">{{ old('notes') }}</textarea>
                                @error('notes')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.incomes.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Record income
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
    var walletSelect = document.getElementById('wallet_id');
    var currencyInput = document.getElementById('currency_code');
    if (!walletSelect || !currencyInput) return;
    function syncCurrency() {
        var opt = walletSelect.options[walletSelect.selectedIndex];
        if (opt && opt.value && opt.getAttribute('data-currency')) {
            currencyInput.value = opt.getAttribute('data-currency');
        }
    }
    walletSelect.addEventListener('change', syncCurrency);
    syncCurrency();
})();
</script>
@endpush
@endsection
