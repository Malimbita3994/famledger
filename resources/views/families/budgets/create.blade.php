@extends('layouts.metronic')

@section('title', 'New Budget')
@section('page_title', 'New Budget')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    @php
        // Build category groups and subcategories from expense category names ("Group - Subcategory")
        $budgetCategoryGroups = [];
        $budgetGroupToSubIds = [];
        $budgetCategoryIdToGroupSub = [];
        foreach ($categories as $cat) {
            $name = $cat->name;
            $parts = explode(' - ', $name, 2);
            $group = trim($parts[0] ?? '');
            $sub = isset($parts[1]) ? trim($parts[1]) : $group;
            if ($group === '') {
                $group = 'Other';
            }
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

        /* Category + Subcategory row: always 2 columns with gap */
        .budget-category-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 1024px) {
            .budget-main-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
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

                    {{-- Row 1: four columns --}}
                    <div class="budget-main-row">
                        <div class="budget-main-col">
                            <label for="name" class="kt-form-label">Name <span class="text-destructive">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Monthly household" class="kt-input" />
                            @error('name')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="type" class="kt-form-label">Budget type <span class="text-destructive">*</span></label>
                            <select name="type" id="type" required class="kt-select">
                                @foreach (\App\Models\Budget::types() as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', \App\Models\Budget::TYPE_FAMILY) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" id="is_main_budget" class="mt-0.5" {{ old('type', 'family') === \App\Models\Budget::TYPE_FAMILY ? 'checked' : '' }}>
                                <span class="kt-form-label m-0">Main budget</span>
                            </label>
                        </div>

                        <div class="budget-main-col">
                            <label for="amount" class="kt-form-label">Amount <span class="text-destructive">*</span></label>
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0.01" placeholder="0.00" class="kt-input" />
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Row 2: four columns --}}
                    <div class="budget-main-row">
                        <div class="budget-main-col">
                            <label for="currency_code" class="kt-form-label">Currency <span class="text-destructive">*</span></label>
                            <select name="currency_code" id="currency_code" required class="kt-select">
                                @foreach ($currencies as $code => $label)
                                    <option value="{{ $code }}" {{ old('currency_code', $family->currency_code) === $code ? 'selected' : '' }}>{{ $code }} – {{ $label }}</option>
                                @endforeach
                            </select>
                            @error('currency_code')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="start_date" class="kt-form-label">Start date <span class="text-destructive">*</span></label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->startOfMonth()->format('Y-m-d')) }}" required class="kt-input" />
                            @error('start_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="end_date" class="kt-form-label">End date <span class="text-destructive">*</span></label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', now()->endOfMonth()->format('Y-m-d')) }}" required class="kt-input" />
                            @error('end_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="budget-main-col">
                            <label for="recurrence" class="kt-form-label">Recurrence</label>
                            <select name="recurrence" id="recurrence" class="kt-select">
                                @foreach (\App\Models\Budget::recurrences() as $value => $label)
                                    <option value="{{ $value }}" {{ old('recurrence', 'none') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('recurrence')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Row 3: scope-specific controls (wallet / categories) --}}
                    <div class="budget-main-row">
                        {{-- Column 1: Wallet (for wallet budgets) --}}
                        <div id="scope-wallets" class="budget-main-col" style="display: none;">
                            <label for="wallet_ids" class="kt-form-label">Wallet</label>
                            <select name="wallet_ids[]" id="wallet_ids" class="kt-select">
                                <option value="">Select wallet</option>
                                @foreach ($wallets as $w)
                                    @php
                                        $oldWalletIds = old('wallet_ids', []);
                                        $isSelected = ! empty($oldWalletIds)
                                            ? in_array($w->id, $oldWalletIds)
                                            : ($mainWalletId && $w->id === $mainWalletId);
                                    @endphp
                                    <option value="{{ $w->id }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $w->name }} ({{ $w->currency_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('wallet_ids')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Column 2: Category (for expenses budgets) --}}
                        <div id="scope-category-group" class="budget-main-col" style="display: none;">
                            <label for="budget_category_group" class="kt-form-label">Category</label>
                            <select id="budget_category_group" class="kt-select">
                                <option value="">Select category</option>
                                @foreach ($budgetCategoryGroups as $groupLabel)
                                    <option value="{{ $groupLabel }}" {{ old('budget_category_group') === $groupLabel ? 'selected' : '' }}>
                                        {{ $groupLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Column 3: Subcategory (for expenses budgets) --}}
                        <div id="scope-category-sub" class="budget-main-col" style="display: none;">
                            <label for="category_ids" class="kt-form-label">Subcategory</label>
                            <select name="category_ids[]" id="category_ids" class="kt-select">
                                <option value="">Select subcategory</option>
                            </select>
                            @error('category_ids')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
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
    var scopeCategoryGroup = document.getElementById('scope-category-group');
    var scopeCategorySub = document.getElementById('scope-category-sub');
    var isMainCheckbox = document.getElementById('is_main_budget');
    var categoryGroupSelect = document.getElementById('budget_category_group');
    var categorySubSelect = document.getElementById('category_ids');
    var groupToSubs = @json($budgetGroupToSubIds);
    var categoryIdToGroupSub = @json($budgetCategoryIdToGroupSub);
    var oldCategoryIds = @json(old('category_ids', []));
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

        // Wallet always visible, with main wallet preselected by default.
        setVisible(scopeWallets, true);

        // Category/Subcategory only for non-main (Expenses/Project) budgets.
        var showCat = !isMain;
        setVisible(scopeCategoryGroup, showCat);
        setVisible(scopeCategorySub, showCat);
        if (isMainCheckbox) {
            // Keep checkbox in sync with "family" type
            isMainCheckbox.checked = isMain;
        }
    }

    function syncMainCheckbox() {
        if (!typeSelect || !isMainCheckbox) return;
        if (isMainCheckbox.checked) {
            typeSelect.value = 'family';
        } else if (typeSelect.value === 'family') {
            // Default non-main type when unchecking
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
    // Populate category subcategories when group changes
    function populateCategorySubs(group) {
        if (!categorySubSelect) return;
        categorySubSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select subcategory';
        categorySubSelect.appendChild(placeholder);

        if (!group || !groupToSubs[group]) {
            return;
        }
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

    // Restore previous selection on validation error
    if (oldCategoryIds.length > 0) {
        var firstId = String(oldCategoryIds[0]);
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

    // Keep start date, end date, and recurrence in sync:
    // dates drive the recurrence value.
    function updateRecurrenceFromDates() {
        if (!startDateInput || !endDateInput || !recurrenceSelect) return;
        if (!startDateInput.value || !endDateInput.value) return;

        var start = new Date(startDateInput.value + 'T00:00:00');
        var end = new Date(endDateInput.value + 'T00:00:00');
        if (isNaN(start.getTime()) || isNaN(end.getTime())) return;

        // Normalize so end is not before start
        if (end < start) {
            recurrenceSelect.value = 'none';
            return;
        }

        var diffDays = Math.round((end - start) / (1000 * 60 * 60 * 24));

        // Weekly: exactly 7-day window (6 days difference)
        if (diffDays === 6) {
            recurrenceSelect.value = 'weekly';
            return;
        }

        // Monthly: start is 1st of month, end is last day of same month
        var sameMonth = start.getFullYear() === end.getFullYear() && start.getMonth() === end.getMonth();
        var startIsFirst = start.getDate() === 1;
        var temp = new Date(start.getFullYear(), start.getMonth() + 1, 0); // last day of start month
        var endIsLastOfMonth = sameMonth && end.getDate() === temp.getDate();
        if (sameMonth && startIsFirst && endIsLastOfMonth) {
            recurrenceSelect.value = 'monthly';
            return;
        }

        // Yearly: full calendar year (Jan 1 to Dec 31)
        var startIsJan1 = start.getMonth() === 0 && start.getDate() === 1;
        var endIsDec31 = end.getMonth() === 11 && end.getDate() === 31 && start.getFullYear() === end.getFullYear();
        if (startIsJan1 && endIsDec31) {
            recurrenceSelect.value = 'yearly';
            return;
        }

        // Otherwise treat as single/custom period
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
