@extends('layouts.metronic')

@section('title', 'Record Expense')
@section('page_title', 'Record Expense')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
        <x-fin-back-link href="{{ route('families.expenses.index') }}">
        Back to expenses
    </x-fin-back-link>

    <style>
    .expense-main-row {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .expense-main-row .expense-main-col {
        width: 100%;
    }
    @media (min-width: 900px) {
        .expense-main-row {
            flex-direction: row;
        }
        .expense-main-row .expense-main-col {
            flex: 1 1 0;
        }
    }
    </style>

    @php
        $groupToSubcategoryIds = [];
        $categoryIdToGroupSub = [];

        // Derive groups/subcategories from seeded category names of form: "Group - Subcategory".
        // Ignore legacy "flat" categories without " - " so the dropdown stays consistent.
        foreach ($categories as $cat) {
            $parts = explode(' - ', (string) $cat->name, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $group = trim($parts[0] ?? '');
            $sub = trim($parts[1] ?? '');

            if ($group === '' || $sub === '') {
                continue;
            }

            $groupToSubcategoryIds[$group][$sub] = $cat->id;
            $categoryIdToGroupSub[$cat->id] = ['group' => $group, 'sub' => $sub];
        }

        $groupLabels = array_keys($groupToSubcategoryIds);
        sort($groupLabels, SORT_NATURAL | SORT_FLAG_CASE);
    @endphp

    <form action="{{ route('families.expenses.store') }}" method="POST" id="expense-form">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Record expense</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Every expense reduces a wallet. Select the wallet first, then enter amount and details.</p>

                    {{-- Row 1: Wallet, Amount, Category, Subcategory --}}
                    <div class="expense-main-row">
                        <div class="expense-main-col grid gap-1.5">
                            <label for="wallet_id" class="kt-form-label">Wallet <span class="text-destructive">*</span></label>
                            <select name="wallet_id" id="wallet_id" required class="kt-select" aria-invalid="{{ $errors->has('wallet_id') ? 'true' : 'false' }}">
                                <option value="">Select a wallet</option>
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" data-currency="{{ $w->currency_code }}" {{ old('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                                @endforeach
                            </select>
                            @error('wallet_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="amount" class="kt-form-label">Amount <span class="text-destructive">*</span></label>
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0.01" placeholder="0.00" class="kt-input" aria-invalid="{{ $errors->has('amount') ? 'true' : 'false' }}" />
                            @error('amount')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="category_group" class="kt-form-label">Category <span class="text-destructive">*</span></label>
                            <select name="category_group" id="category_group" class="kt-select" required>
                                <option value="">Select category</option>
                                @foreach ($groupLabels as $groupLabel)
                                    <option value="{{ $groupLabel }}">{{ $groupLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="category_id" class="kt-form-label">Subcategory <span class="text-destructive">*</span></label>
                            <select name="category_id" id="category_id" class="kt-select" aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}" required>
                                <option value="">Select subcategory</option>
                            </select>
                            @error('category_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="expense_date" class="kt-form-label">Expense date <span class="text-destructive">*</span></label>
                            <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required class="kt-input" aria-invalid="{{ $errors->has('expense_date') ? 'true' : 'false' }}" />
                            @error('expense_date')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <input type="hidden" name="currency_code" id="currency_code" value="{{ old('currency_code', $wallets->first()?->currency_code ?? '') }}" />

                    {{-- Row 2: Budget source, Project, Liability, Paid by --}}
                    <div class="expense-main-row">
                        <div class="expense-main-col grid gap-1.5">
                            <label for="budget_id" class="kt-form-label">Budget source</label>
                            <select name="budget_id" id="budget_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach($budgets as $b)
                                    <option
                                        value="{{ $b->id }}"
                                        data-budget-type="{{ $b->type }}"
                                        data-project-id="{{ $b->project_id }}"
                                        data-wallet-id="{{ $b->wallets->first()->id ?? '' }}"
                                        data-category-id="{{ $b->categories->first()->id ?? '' }}"
                                        {{ old('budget_id') == $b->id ? 'selected' : '' }}
                                    >
                                        {{ $b->name }} ({{ \App\Models\Budget::types()[$b->type] ?? $b->type }}, {{ number_format($b->amount, 0) }} {{ $b->currency_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('budget_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="project_id" class="kt-form-label">Project</label>
                            <select name="project_id" id="project_id" class="kt-select">
                                <option value="">— None —</option>
                                @if(isset($projects) && $projects->isNotEmpty())
                                    @foreach ($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ (string) old('project_id', request('project_id')) === (string) $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('project_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        @php
                            $liabilities = $family->liabilities()->orderBy('name')->get();
                        @endphp
                        <div class="expense-main-col grid gap-1.5">
                            <label for="family_liability_id" class="kt-form-label">Linked liability</label>
                            <select name="family_liability_id" id="family_liability_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach($liabilities as $liab)
                                    <option value="{{ $liab->id }}" {{ old('family_liability_id') == $liab->id ? 'selected' : '' }}>
                                        {{ $liab->name }} ({{ ucfirst($liab->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @if($liabilities->isNotEmpty())
                                <p class="text-xs text-muted-foreground mt-1">
                                    Use when this expense is a repayment of a loan or debt.
                                </p>
                            @endif
                            @error('family_liability_id')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="paid_by" class="kt-form-label">Paid by</label>
                            <select name="paid_by" id="paid_by" class="kt-select" aria-invalid="{{ $errors->has('paid_by') ? 'true' : 'false' }}">
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" {{ old('paid_by', auth()->id()) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                @endforeach
                            </select>
                            @error('paid_by')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Row 3: Description, Merchant, Payment method, Reference / Recurring --}}
                    <div class="expense-main-row">
                        <div class="expense-main-col grid gap-1.5">
                            <label for="description" class="kt-form-label">Description</label>
                            <input type="text" name="description" id="description" value="{{ old('description') }}" placeholder="e.g. Groceries, Transport" class="kt-input" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" />
                            @error('description')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="merchant" class="kt-form-label">Merchant</label>
                            <input type="text" name="merchant" id="merchant" value="{{ old('merchant') }}" placeholder="e.g. Shop name, vendor" class="kt-input" aria-invalid="{{ $errors->has('merchant') ? 'true' : 'false' }}" />
                            @error('merchant')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="payment_method" class="kt-form-label">Payment method</label>
                            <select name="payment_method" id="payment_method" class="kt-select" aria-invalid="{{ $errors->has('payment_method') ? 'true' : 'false' }}">
                                <option value="">— Optional —</option>
                                @foreach (App\Models\Expense::paymentMethods() as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="reference" class="kt-form-label">Reference / Receipt no.</label>
                            <input type="text" name="reference" id="reference" value="{{ old('reference') }}" placeholder="Optional" class="kt-input" aria-invalid="{{ $errors->has('reference') ? 'true' : 'false' }}" />
                            @error('reference')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="expense-main-col grid gap-1.5">
                            <label for="is_recurring" class="kt-form-label">Recurring</label>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }} class="kt-checkbox" />
                                <span class="text-sm text-muted-foreground">This is a recurring expense (e.g. rent, subscription)</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.expenses.index') }}" class="kt-btn kt-btn-outline">Cancel</a>
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

    // Link Budget source <-> Project (for project-scoped budgets only)
    var budgetSelect = document.getElementById('budget_id');
    var projectSelect = document.getElementById('project_id');

    function getSelectedOption(selectEl) {
        if (!selectEl || !selectEl.selectedOptions || selectEl.selectedOptions.length === 0) return null;
        return selectEl.selectedOptions[0];
    }

    function syncProjectFromBudget() {
        if (!budgetSelect || !projectSelect) return;

        var opt = getSelectedOption(budgetSelect);
        if (!opt || !opt.value) return;

        if ((opt.dataset && opt.dataset.budgetType) === 'project' && opt.dataset.projectId) {
            projectSelect.value = opt.dataset.projectId;
        }
    }

    function syncBudgetFromProject() {
        if (!budgetSelect || !projectSelect) return;

        var pid = projectSelect.value;
        var currentBudgetOpt = getSelectedOption(budgetSelect);
        var currentBudgetType = currentBudgetOpt && currentBudgetOpt.dataset ? currentBudgetOpt.dataset.budgetType : '';
        var currentBudgetProjectId = currentBudgetOpt && currentBudgetOpt.dataset ? currentBudgetOpt.dataset.projectId : '';

        // If user cleared project, clear only project-linked budgets.
        if (!pid) {
            if (currentBudgetType === 'project') {
                budgetSelect.value = '';
            }
            return;
        }

        // Find first matching project budget for this project.
        var candidateValue = '';
        for (var i = 0; i < budgetSelect.options.length; i++) {
            var o = budgetSelect.options[i];
            if (!o.value) continue;
            if (o.dataset && o.dataset.budgetType === 'project' && o.dataset.projectId === pid) {
                candidateValue = o.value;
                break;
            }
        }

        if (candidateValue) {
            budgetSelect.value = candidateValue;
            return;
        }

        // No project budget found: clear only if current selection is a project budget.
        if (currentBudgetType === 'project' && currentBudgetProjectId !== pid) {
            budgetSelect.value = '';
        }
    }

    if (budgetSelect && projectSelect) {
        budgetSelect.addEventListener('change', function () {
            syncProjectFromBudget();
        });
        projectSelect.addEventListener('change', function () {
            syncBudgetFromProject();
        });

        // Initial sync for validation errors / query-string state.
        syncProjectFromBudget();
        syncBudgetFromProject();
    }

    function syncCurrency() {
        if (!walletSelect || !currencyInput) return;
        var opt = walletSelect.options[walletSelect.selectedIndex];
        if (opt && opt.value && opt.getAttribute('data-currency')) {
            currencyInput.value = opt.getAttribute('data-currency');
        }
    }
    if (walletSelect) {
        walletSelect.addEventListener('change', syncCurrency);
        syncCurrency();
    }

    // Dynamic subcategory population based on selected category group
    var groupSelect = document.getElementById('category_group');
    var subcategorySelect = document.getElementById('category_id');
    var groupMap = @json($groupToSubcategoryIds);
    var idToGroupSub = @json($categoryIdToGroupSub);
    var oldCategoryId = "{{ old('category_id') }}";

    function populateSubcategories(group) {
        if (!subcategorySelect) return;
        subcategorySelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select subcategory';
        subcategorySelect.appendChild(placeholder);

        var subs = groupMap[group] || {};
        Object.keys(subs).sort().forEach(function (subLabel) {
            var opt = document.createElement('option');
            opt.value = subs[subLabel];
            opt.textContent = subLabel;
            subcategorySelect.appendChild(opt);
        });
    }

    if (groupSelect) {
        groupSelect.addEventListener('change', function () {
            populateSubcategories(this.value);
        });
    }

    // Restore previous selection on validation error
    if (oldCategoryId && idToGroupSub[oldCategoryId]) {
        var info = idToGroupSub[oldCategoryId];
        if (groupSelect) {
            groupSelect.value = info.group;
            populateSubcategories(info.group);
        }
        if (subcategorySelect) {
            subcategorySelect.value = oldCategoryId;
        }
    }

    function syncExpenseFieldsFromBudgetSource() {
        if (!budgetSelect || !walletSelect || !groupSelect || !subcategorySelect) return;

        var opt = getSelectedOption(budgetSelect);
        if (!opt || !opt.value) return;

        var budgetType = opt.dataset ? opt.dataset.budgetType : '';
        var walletId = opt.dataset ? opt.dataset.walletId : '';
        var categoryId = opt.dataset ? opt.dataset.categoryId : '';

        if (budgetType === 'wallet' && walletId) {
            walletSelect.value = walletId;
            syncCurrency();
        }

        if (budgetType === 'category' && categoryId) {
            var cid = String(categoryId);
            if (idToGroupSub[cid]) {
                var catInfo = idToGroupSub[cid];
                groupSelect.value = catInfo.group;
                populateSubcategories(catInfo.group);
                subcategorySelect.value = cid;
            }
        }
    }

    if (budgetSelect) {
        budgetSelect.addEventListener('change', syncExpenseFieldsFromBudgetSource);
        syncExpenseFieldsFromBudgetSource();
    }
})();
</script>
@endpush
@endsection
