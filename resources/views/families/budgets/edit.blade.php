@extends('layouts.metronic')

@section('title', 'Edit Budget')
@section('page_title', 'Edit Budget')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    @php
        $budgetCategoryGroups = [];
        $budgetGroupToSubIds = [];
        $budgetCategoryIdToGroupSub = [];
        foreach ($categories as $cat) {
            $name = $cat->name;
            $parts = explode(' - ', $name, 2);
            $group = trim($parts[0] ?? '');
            $sub = isset($parts[1]) ? trim($parts[1]) : $group;
            if ($group === '') { $group = 'Other'; }
            $budgetCategoryGroups[$group] = $group;
            $budgetGroupToSubIds[$group][$sub] = $cat->id;
            $budgetCategoryIdToGroupSub[$cat->id] = ['group' => $group, 'sub' => $sub];
        }
        $mainWallet = $family->mainWallet();
        $mainWalletId = $mainWallet?->id;
    @endphp
    <style>
        .budget-main-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        .budget-main-col {
            display: grid;
            grid-auto-rows: minmax(0, auto);
            gap: 0.375rem;
        }

        @media (min-width: 1024px) {
            .budget-main-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
    <a href="{{ route('families.budgets.show', [$family, $budget]) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to budget
    </a>

    <form action="{{ route('families.budgets.update', [$family, $budget]) }}" method="POST" id="budget-form">
        @csrf
        @method('PUT')

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Edit budget</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    {{-- Row 1 --}}
                    <div class="budget-main-row">
                        <div class="budget-main-col">
                            <label for="name" class="kt-form-label">Name <span class="text-destructive">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $budget->name) }}" required class="kt-input" />
                            @error('name')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="type" class="kt-form-label">Budget type <span class="text-destructive">*</span></label>
                            <select name="type" id="type" required class="kt-select">
                                @foreach (\App\Models\Budget::types() as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $budget->type) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" id="is_main_budget" class="mt-0.5" {{ old('type', $budget->type) === \App\Models\Budget::TYPE_FAMILY ? 'checked' : '' }}>
                                <span class="kt-form-label m-0">Main budget</span>
                            </label>
                        </div>

                        <div class="budget-main-col">
                            <label for="amount" class="kt-form-label">Amount <span class="text-destructive">*</span></label>
                            <input type="number" name="amount" id="amount" value="{{ old('amount', $budget->amount) }}" required step="0.01" min="0.01" class="kt-input" />
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Row 2 --}}
                    <div class="budget-main-row">
                        <div class="budget-main-col">
                            <label for="currency_code" class="kt-form-label">Currency <span class="text-destructive">*</span></label>
                            <select name="currency_code" id="currency_code" required class="kt-select">
                                @foreach ($currencies as $code => $label)
                                    <option value="{{ $code }}" {{ old('currency_code', $budget->currency_code) === $code ? 'selected' : '' }}>{{ $code }} – {{ $label }}</option>
                                @endforeach
                            </select>
                            @error('currency_code')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="start_date" class="kt-form-label">Start date <span class="text-destructive">*</span></label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $budget->start_date->format('Y-m-d')) }}" required class="kt-input" />
                            @error('start_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="end_date" class="kt-form-label">End date <span class="text-destructive">*</span></label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $budget->end_date->format('Y-m-d')) }}" required class="kt-input" />
                            @error('end_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="recurrence" class="kt-form-label">Recurrence</label>
                            <select name="recurrence" id="recurrence" class="kt-select">
                                @foreach (\App\Models\Budget::recurrences() as $value => $label)
                                    <option value="{{ $value }}" {{ old('recurrence', $budget->recurrence) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('recurrence')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Row 3: Wallet / Category / Subcategory / Status --}}
                    @php
                        $selectedWalletIds = old('wallet_ids', $budget->wallets->pluck('id')->toArray());
                        $selectedCategoryIds = old('category_ids', $budget->categories->pluck('id')->toArray());
                    @endphp
                    <div class="budget-main-row">
                        <div id="scope-wallets" class="budget-main-col" style="display: none;">
                            <label for="wallet_ids" class="kt-form-label">Wallet</label>
                            <select name="wallet_ids[]" id="wallet_ids" class="kt-select">
                                <option value="">Select wallet</option>
                                @foreach ($wallets as $w)
                                    @php
                                        $isSelectedWallet = ! empty($selectedWalletIds)
                                            ? in_array($w->id, $selectedWalletIds)
                                            : ($mainWalletId && $w->id === $mainWalletId);
                                    @endphp
                                    <option value="{{ $w->id }}" {{ $isSelectedWallet ? 'selected' : '' }}>
                                        {{ $w->name }} ({{ $w->currency_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('wallet_ids')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div id="scope-category-group" class="budget-main-col" style="display: none;">
                            <label for="budget_category_group" class="kt-form-label">Category</label>
                            <select id="budget_category_group" class="kt-select">
                                <option value="">Select category</option>
                                @foreach ($budgetCategoryGroups as $groupLabel)
                                    <option value="{{ $groupLabel }}">{{ $groupLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="scope-category-sub" class="budget-main-col" style="display: none;">
                            <label for="category_ids" class="kt-form-label">Subcategory</label>
                            <select name="category_ids[]" id="category_ids" class="kt-select">
                                <option value="">Select subcategory</option>
                            </select>
                            @error('category_ids')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="status" class="kt-form-label">Status</label>
                            <select name="status" id="status" class="kt-select">
                                <option value="active" {{ old('status', $budget->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="archived" {{ old('status', $budget->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.budgets.show', [$family, $budget]) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-check"></i>
                            Update budget
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
    var scopeCategoryGroup = document.getElementById('scope-category-group');
    var scopeCategorySub = document.getElementById('scope-category-sub');
    var isMainCheckbox = document.getElementById('is_main_budget');
    var categoryGroupSelect = document.getElementById('budget_category_group');
    var categorySubSelect = document.getElementById('category_ids');
    var groupToSubs = @json($budgetGroupToSubIds);
    var categoryIdToGroupSub = @json($budgetCategoryIdToGroupSub);
    var selectedCategoryIds = @json($selectedCategoryIds);
    var startDateInput = document.getElementById('start_date');
    var endDateInput = document.getElementById('end_date');
    var recurrenceSelect = document.getElementById('recurrence');

    function setVisible(el, show) {
        if (!el) return;
        el.style.display = show ? '' : 'none';
    }

    function toggleScope() {
        var type = typeSelect && typeSelect.value;
        var isMain = type === 'family';

        setVisible(scopeWallets, true);

        var showCat = !isMain;
        setVisible(scopeCategoryGroup, showCat);
        setVisible(scopeCategorySub, showCat);

        if (isMainCheckbox) {
            isMainCheckbox.checked = isMain;
        }
    }

    function syncMainCheckbox() {
        if (!typeSelect || !isMainCheckbox) return;
        if (isMainCheckbox.checked) {
            typeSelect.value = 'family';
        } else if (typeSelect.value === 'family') {
            typeSelect.value = 'wallet';
        }
        toggleScope();
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', toggleScope);
    }
    if (isMainCheckbox) {
        isMainCheckbox.addEventListener('change', syncMainCheckbox);
    }

    function populateCategorySubs(group) {
        if (!categorySubSelect) return;
        categorySubSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select subcategory';
        categorySubSelect.appendChild(placeholder);

        if (!group || !groupToSubs[group]) return;
        var subs = groupToSubs[group];
        Object.keys(subs).forEach(function (subLabel) {
            var opt = document.createElement('option');
            opt.value = subs[subLabel];
            opt.textContent = subLabel;
            categorySubSelect.appendChild(opt);
        });
    }

    if (categoryGroupSelect) {
        categoryGroupSelect.addEventListener('change', function () {
            populateCategorySubs(this.value);
        });
    }

    if (selectedCategoryIds.length > 0) {
        var firstId = String(selectedCategoryIds[0]);
        if (categoryIdToGroupSub[firstId]) {
            var info = categoryIdToGroupSub[firstId];
            if (categoryGroupSelect) {
                categoryGroupSelect.value = info.group;
            }
            populateCategorySubs(info.group);
            if (categorySubSelect) {
                categorySubSelect.value = firstId;
            }
        }
    }

    function updateRecurrenceFromDates() {
        if (!startDateInput || !endDateInput || !recurrenceSelect) return;
        if (!startDateInput.value || !endDateInput.value) return;

        var start = new Date(startDateInput.value + 'T00:00:00');
        var end = new Date(endDateInput.value + 'T00:00:00');
        if (isNaN(start.getTime()) || isNaN(end.getTime())) return;

        if (end < start) {
            recurrenceSelect.value = 'none';
            return;
        }

        var diffDays = Math.round((end - start) / (1000 * 60 * 60 * 24));

        if (diffDays === 6) {
            recurrenceSelect.value = 'weekly';
            return;
        }

        var sameMonth = start.getFullYear() === end.getFullYear() && start.getMonth() === end.getMonth();
        var startIsFirst = start.getDate() === 1;
        var temp = new Date(start.getFullYear(), start.getMonth() + 1, 0);
        var endIsLastOfMonth = sameMonth && end.getDate() === temp.getDate();
        if (sameMonth && startIsFirst && endIsLastOfMonth) {
            recurrenceSelect.value = 'monthly';
            return;
        }

        var startIsJan1 = start.getMonth() === 0 && start.getDate() === 1;
        var endIsDec31 = end.getMonth() === 11 && end.getDate() === 31 && start.getFullYear() === end.getFullYear();
        if (startIsJan1 && endIsDec31) {
            recurrenceSelect.value = 'yearly';
            return;
        }

        recurrenceSelect.value = 'none';
    }

    if (startDateInput) {
        startDateInput.addEventListener('change', updateRecurrenceFromDates);
    }
    if (endDateInput) {
        endDateInput.addEventListener('change', updateRecurrenceFromDates);
    }

    toggleScope();
})();
</script>
@endpush
@endsection
