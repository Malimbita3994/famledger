@extends('layouts.metronic')

@section('title', 'Record Expense')
@section('page_title', 'Record Expense')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.expenses.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to expenses
    </a>

    <form action="{{ route('families.expenses.store', $family) }}" method="POST" id="expense-form">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Record expense</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Every expense reduces a wallet. Select the wallet first, then enter amount and details.</p>

                    {{-- 1. Wallet FIRST --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="wallet_id" class="kt-form-label max-w-56">Wallet <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="wallet_id" id="wallet_id" required class="kt-select" aria-invalid="{{ $errors->has('wallet_id') ? 'true' : 'false' }}">
                                <option value="">Select a wallet</option>
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" data-currency="{{ $w->currency_code }}" {{ old('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 2. Amount --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="amount" class="kt-form-label max-w-56">Amount <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0.01" placeholder="0.00" class="kt-input" aria-invalid="{{ $errors->has('amount') ? 'true' : 'false' }}" />
                            <p class="text-xs text-muted-foreground mt-1">Currency will match the selected wallet.</p>
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <input type="hidden" name="currency_code" id="currency_code" value="{{ old('currency_code', $wallets->first()?->currency_code ?? '') }}" />

                    {{-- Project (optional) --}}
                    @if(isset($projects) && $projects->isNotEmpty())
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="project_id" class="kt-form-label max-w-56">Project</label>
                        <div class="grow">
                            <select name="project_id" id="project_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach ($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Link this expense to a family project for tracking.</p>
                            @error('project_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @endif

                    {{-- 3. Category --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="category_id" class="kt-form-label max-w-56">Category</label>
                        <div class="grow">
                            <select name="category_id" id="category_id" class="kt-select" aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}">
                                <option value="">— Optional —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 4. Date --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="expense_date" class="kt-form-label max-w-56">Expense date <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required class="kt-input" aria-invalid="{{ $errors->has('expense_date') ? 'true' : 'false' }}" />
                            @error('expense_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 5. Description --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="description" class="kt-form-label max-w-56">Description</label>
                        <div class="grow">
                            <input type="text" name="description" id="description" value="{{ old('description') }}" placeholder="e.g. Groceries, Transport" class="kt-input" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" />
                            @error('description')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 6. Merchant --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="merchant" class="kt-form-label max-w-56">Merchant</label>
                        <div class="grow">
                            <input type="text" name="merchant" id="merchant" value="{{ old('merchant') }}" placeholder="e.g. Shop name, vendor" class="kt-input" aria-invalid="{{ $errors->has('merchant') ? 'true' : 'false' }}" />
                            @error('merchant')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 7. Paid by --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="paid_by" class="kt-form-label max-w-56">Paid by</label>
                        <div class="grow">
                            <select name="paid_by" id="paid_by" class="kt-select" aria-invalid="{{ $errors->has('paid_by') ? 'true' : 'false' }}">
                                @foreach ($members as $member)
                                        <option value="{{ $member->id }}" {{ old('paid_by', auth()->id()) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                    @endforeach
                            </select>
                            @error('paid_by')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 8. Payment method --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="payment_method" class="kt-form-label max-w-56">Payment method</label>
                        <div class="grow">
                            <select name="payment_method" id="payment_method" class="kt-select" aria-invalid="{{ $errors->has('payment_method') ? 'true' : 'false' }}">
                                <option value="">— Optional —</option>
                                @foreach (App\Models\Expense::paymentMethods() as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- 9. Reference (receipt) --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="reference" class="kt-form-label max-w-56">Reference / Receipt no.</label>
                        <div class="grow">
                            <input type="text" name="reference" id="reference" value="{{ old('reference') }}" placeholder="Optional" class="kt-input" aria-invalid="{{ $errors->has('reference') ? 'true' : 'false' }}" />
                            @error('reference')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Recurring --}}
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="is_recurring" class="kt-form-label max-w-56">Recurring</label>
                        <div class="grow flex items-center gap-2">
                            <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }} class="kt-checkbox" />
                            <span class="text-sm text-muted-foreground">This is a recurring expense (e.g. rent, subscription)</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.expenses.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Record expense
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
