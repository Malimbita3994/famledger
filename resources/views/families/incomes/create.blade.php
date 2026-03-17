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

    @php
        // Structured income categories and subcategories
        $incomeGroupDefinitions = [
            'Wages' => [
                'Paycheck',
                'Tips',
                'Bonus',
                'Commission',
                'Other',
            ],
            'Other income' => [
                'Transfer from savings',
                'Interest income',
                'Dividends',
                'Gifts',
                'Refunds',
                'Other',
            ],
        ];

        // Map: group -> sub -> income_category_id (based on names like "Wages - Paycheck")
        $incomeGroupToSubIds = [];
        $incomeCategoryIdToGroupSub = [];
        foreach ($categories as $cat) {
            foreach ($incomeGroupDefinitions as $group => $subs) {
                foreach ($subs as $sub) {
                    $expected = $group . ' - ' . $sub;
                    if ($cat->name === $expected) {
                        $incomeGroupToSubIds[$group][$sub] = $cat->id;
                        $incomeCategoryIdToGroupSub[$cat->id] = ['group' => $group, 'sub' => $sub];
                    }
                }
            }
        }
    @endphp

    <form action="{{ route('families.incomes.store', $family) }}" method="POST" id="income-form">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[60rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Record income</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">
                        All income is recorded into the main wallet:
                        <span class="font-medium text-foreground">{{ $mainWallet->name }} ({{ $mainWallet->currency_code }})</span>.
                    </p>

                    <div>
                        <h3 class="text-sm font-semibold text-foreground mb-3">
                            Basic Information
                        </h3>
                        <div class="income-main-row">
                            {{-- Wallet (main wallet, read-only) --}}
                            <div class="income-main-col grid gap-1.5">
                                <label class="kt-form-label">Wallet</label>
                                <div class="kt-input flex items-center justify-between">
                                    <span class="text-sm font-medium text-foreground">{{ $mainWallet->name }}</span>
                                    <span class="text-xs text-muted-foreground uppercase tracking-wide">{{ $mainWallet->currency_code }}</span>
                                </div>
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
                                @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Category group --}}
                            <div class="income-main-col grid gap-1.5">
                                <label for="income_category_group" class="kt-form-label">Category <span class="text-destructive">*</span></label>
                                <select name="income_category_group" id="income_category_group" class="kt-select w-full" required>
                                    <option value="">Select category</option>
                                    @foreach ($incomeGroupDefinitions as $groupLabel => $subs)
                                        @if(isset($incomeGroupToSubIds[$groupLabel]))
                                            <option value="{{ $groupLabel }}">{{ $groupLabel }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- Income subcategory --}}
                            <div class="income-main-col grid gap-1.5">
                                <label for="category_id" class="kt-form-label">Subcategory <span class="text-destructive">*</span></label>
                                <select name="category_id" id="category_id" class="kt-select w-full" aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}" required>
                                    <option value="">Select subcategory</option>
                                </select>
                                @error('category_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="currency_code" id="currency_code" value="{{ old('currency_code', $mainWallet->currency_code ?? '') }}" />

                    {{-- Received date --}}
                    <div class="income-main-row">
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

                    {{-- Liability linkage (optional) --}}
                    @php
                        $liabilities = $family->liabilities()->orderBy('name')->get();
                    @endphp
                    @if($liabilities->isNotEmpty())
                    <div class="grid gap-5">
                        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                            <label for="family_liability_id" class="kt-form-label max-w-56">Linked liability</label>
                            <div class="grow">
                                <select name="family_liability_id" id="family_liability_id" class="kt-select w-full">
                                    <option value="">— None —</option>
                                    @foreach($liabilities as $liab)
                                        <option value="{{ $liab->id }}" {{ old('family_liability_id') == $liab->id ? 'selected' : '' }}>
                                            {{ $liab->name }} ({{ ucfirst($liab->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Use when this income represents a loan drawdown or additional borrowing.
                                </p>
                                @error('family_liability_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    @endif

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
    // All income goes to main wallet: no wallet selector JS needed.

    // Dynamic income category subcategories
    var groupSelect = document.getElementById('income_category_group');
    var incomeSubSelect = document.getElementById('category_id');
    var incomeGroupMap = @json($incomeGroupToSubIds);
    var incomeIdToGroupSub = @json($incomeCategoryIdToGroupSub);
    var oldIncomeCategoryId = "{{ old('category_id') }}";

    function populateIncomeSubcategories(group) {
        if (!incomeSubSelect) return;
        incomeSubSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '— Optional —';
        incomeSubSelect.appendChild(placeholder);

        var subs = incomeGroupMap[group] || {};
        Object.keys(subs).forEach(function (subLabel) {
            var opt = document.createElement('option');
            opt.value = subs[subLabel];
            opt.textContent = subLabel;
            incomeSubSelect.appendChild(opt);
        });
    }

    if (groupSelect) {
        groupSelect.addEventListener('change', function () {
            populateIncomeSubcategories(this.value);
        });
    }

    if (oldIncomeCategoryId && incomeIdToGroupSub[oldIncomeCategoryId]) {
        var info = incomeIdToGroupSub[oldIncomeCategoryId];
        if (groupSelect) {
            groupSelect.value = info.group;
            populateIncomeSubcategories(info.group);
        }
        if (incomeSubSelect) {
            incomeSubSelect.value = oldIncomeCategoryId;
        }
    }
})();
</script>
@endpush
@endsection
