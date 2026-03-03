@extends('layouts.metronic')

@section('title', 'New Budget')
@section('page_title', 'New Budget')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.budgets.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to budgets
    </a>

    <form action="{{ route('families.budgets.store', $family) }}" method="POST" id="budget-form">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">New budget</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Set a spending limit for a period. Budgets monitor expenses only; they do not move money.</p>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="name" class="kt-form-label max-w-56">Name <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Monthly household" class="kt-input" />
                            @error('name')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="type" class="kt-form-label max-w-56">Type <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="type" id="type" required class="kt-select">
                                @foreach (\App\Models\Budget::types() as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', 'family') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div id="scope-wallets" class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5 hidden">
                        <label for="wallet_ids" class="kt-form-label max-w-56">Wallets</label>
                        <div class="grow">
                            <select name="wallet_ids[]" id="wallet_ids" class="kt-select" multiple size="4">
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" {{ in_array($w->id, old('wallet_ids', [])) ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Spending from selected wallets counts toward this budget.</p>
                            @error('wallet_ids')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div id="scope-categories" class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5 hidden">
                        <label for="category_ids" class="kt-form-label max-w-56">Categories</label>
                        <div class="grow">
                            <select name="category_ids[]" id="category_ids" class="kt-select" multiple size="4">
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" {{ in_array($c->id, old('category_ids', [])) ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Spending in these categories counts toward this budget.</p>
                            @error('category_ids')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="amount" class="kt-form-label max-w-56">Amount <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0.01" placeholder="0.00" class="kt-input" />
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="currency_code" class="kt-form-label max-w-56">Currency <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="currency_code" id="currency_code" required class="kt-select">
                                @foreach ($currencies as $code => $label)
                                    <option value="{{ $code }}" {{ old('currency_code', $family->currency_code) === $code ? 'selected' : '' }}>{{ $code }} – {{ $label }}</option>
                                @endforeach
                            </select>
                            @error('currency_code')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="start_date" class="kt-form-label max-w-56">Start date <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->startOfMonth()->format('Y-m-d')) }}" required class="kt-input" />
                            @error('start_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="end_date" class="kt-form-label max-w-56">End date <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', now()->endOfMonth()->format('Y-m-d')) }}" required class="kt-input" />
                            @error('end_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="recurrence" class="kt-form-label max-w-56">Recurrence</label>
                        <div class="grow">
                            <select name="recurrence" id="recurrence" class="kt-select">
                                @foreach (\App\Models\Budget::recurrences() as $value => $label)
                                    <option value="{{ $value }}" {{ old('recurrence', 'none') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('recurrence')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.budgets.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Create budget
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
    var typeSelect = document.getElementById('type');
    var scopeWallets = document.getElementById('scope-wallets');
    var scopeCategories = document.getElementById('scope-categories');
    function toggleScope() {
        var type = typeSelect && typeSelect.value;
        if (scopeWallets) scopeWallets.classList.toggle('hidden', type !== 'wallet');
        if (scopeCategories) scopeCategories.classList.toggle('hidden', type !== 'category');
    }
    if (typeSelect) {
        typeSelect.addEventListener('change', toggleScope);
        toggleScope();
    }
})();
</script>
@endpush
@endsection
