@extends('layouts.metronic')

@section('title', 'Transactions')
@section('page_title', 'Transactions')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Transactions</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Manage income and expenses from one place. Use filters to narrow by type or wallet.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="kt-btn kt-btn-primary inline-flex items-center gap-2" id="btn_open_add_transaction">
                <i class="ki-filled ki-plus"></i>
                Add transaction
            </button>
            <a href="{{ route('families.incomes.create', $family) }}" class="kt-btn kt-btn-outline text-xs">Full income form</a>
            <a href="{{ route('families.expenses.create', $family) }}" class="kt-btn kt-btn-outline text-xs">Full expense form</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center gap-3 text-red-800 dark:text-red-200">
            <i class="ki-filled ki-information-2 text-xl shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Summary Cards --}}
    <style>
        .txn-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            width: 100%;
            margin-bottom: 1.5rem;
        }
    </style>
    <div class="txn-stats-grid">
        <div class="kt-card rounded-xl border border-border bg-card px-4 py-3">
            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Income</div>
            <div class="mt-1.5 text-lg font-semibold tabular-nums text-green-600">{{ $family->currency_code }} {{ number_format((float) $totalIncome, 2) }}</div>
        </div>
        <div class="kt-card rounded-xl border border-border bg-card px-4 py-3">
            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Expenses</div>
            <div class="mt-1.5 text-lg font-semibold tabular-nums text-destructive">{{ $family->currency_code }} {{ number_format((float) $totalExpenses, 2) }}</div>
        </div>
        <div class="kt-card rounded-xl border border-border bg-card px-4 py-3">
            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Balance</div>
            <div class="mt-1.5 text-lg font-semibold tabular-nums {{ $balance >= 0 ? 'text-green-600' : 'text-destructive' }}">{{ $family->currency_code }} {{ number_format((float) $balance, 2) }}</div>
        </div>
    </div>

    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-header flex-wrap gap-3">
            <h3 class="kt-card-title text-sm">All transactions</h3>
            <form method="get" class="flex items-center gap-3">
                <label for="type" class="text-sm text-muted-foreground whitespace-nowrap">Type</label>
                <select name="type" id="type" class="kt-select kt-select-sm w-auto" onchange="this.form.submit()">
                    <option value="" {{ ($type ?? '') === '' ? 'selected' : '' }}>All</option>
                    <option value="income" {{ ($type ?? '') === 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ ($type ?? '') === 'expense' ? 'selected' : '' }}>Expense</option>
                </select>

                <label for="wallet_id" class="text-sm text-muted-foreground whitespace-nowrap">Wallet</label>
                <select name="wallet_id" id="wallet_id" class="kt-select kt-select-sm w-auto" onchange="this.form.submit()">
                    <option value="">All wallets</option>
                    @foreach ($wallets as $w)
                        <option value="{{ $w->id }}" {{ request('wallet_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->currency_code }})</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="kt-card-content p-0">
            @if ($transactions->isEmpty())
                <div class="py-12 text-center text-muted-foreground">
                    <i class="ki-filled ki-arrows-loop text-4xl mb-2"></i>
                    <p class="text-sm">No transactions found.</p>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <button type="button" class="kt-btn kt-btn-primary" data-open-add-transaction="1">Add transaction</button>
                    </div>
                </div>
            @else
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[90px]">Type</th>
                                <th class="min-w-[110px]">Date</th>
                                <th class="min-w-[140px]">Wallet</th>
                                <th class="min-w-[120px]">Category</th>
                                <th class="min-w-[140px]">Description</th>
                                <th class="min-w-[120px]">Amount</th>
                                <th class="min-w-[120px]">Recorded by</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $t)
                                <tr>
                                    <td>
                                        @if ($t->type === 'income')
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/20 px-2 py-1 text-[11px] font-medium text-green-700 dark:text-green-300">
                                                <i class="ki-filled ki-arrow-up text-xs"></i> Income
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 px-2 py-1 text-[11px] font-medium text-red-700 dark:text-red-300">
                                                <i class="ki-filled ki-arrow-down text-xs"></i> Expense
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-foreground">{{ \Carbon\Carbon::parse($t->date)->format('M j, Y') }}</td>
                                    <td class="text-foreground">
                                        {{ $t->wallet_name }}
                                        <span class="text-muted-foreground text-xs">({{ $t->wallet_currency }})</span>
                                    </td>
                                    <td class="text-foreground">{{ $t->category_name ?? '—' }}</td>
                                    <td class="text-foreground">{{ \Illuminate\Support\Str::limit($t->description ?? '—', 40) }}</td>
                                    <td class="font-medium tabular-nums {{ $t->type === 'income' ? 'text-success' : 'text-destructive' }}">
                                        {{ $t->type === 'income' ? '+' : '−' }} {{ number_format((float) $t->amount, 2) }} {{ $t->currency_code }}
                                    </td>
                                    <td class="text-muted-foreground text-sm">{{ $t->user_name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-border">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@php
    $openAddTxnModal = $errors->any() && (old('transaction_type') || $errors->has('transaction_type'));
    $initialTxnTab = old('transaction_type', 'expense');
@endphp

{{-- Add transaction (income / expense) --}}
<div class="kt-modal" data-kt-modal="true" id="add_transaction_modal">
    <div class="kt-modal-content max-w-[640px] top-[8%] max-h-[90vh] flex flex-col">
        <div class="kt-modal-header shrink-0">
            <h3 class="kt-modal-title">Add transaction</h3>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-modal-dismiss="true" aria-label="Close">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body grid gap-4 px-0 py-4 overflow-y-auto">
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 px-3 py-2 text-sm text-red-800 dark:text-red-200">
                    <ul class="list-disc pl-4 space-y-1 m-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="kt-tabs kt-tabs-line justify-between px-1" data-kt-tabs="true" id="add_txn_tabs">
                <div class="flex items-center gap-1 flex-wrap">
                    <button type="button" class="kt-tab-toggle py-3 px-2 {{ $initialTxnTab === 'income' ? 'active' : '' }}" data-kt-tab-toggle="#add_txn_panel_income" id="tab_income">Income</button>
                    <button type="button" class="kt-tab-toggle py-3 px-2 {{ $initialTxnTab === 'expense' ? 'active' : '' }}" data-kt-tab-toggle="#add_txn_panel_expense" id="tab_expense">Expense</button>
                </div>
            </div>

            <div class="kt-tab-content px-1 {{ $initialTxnTab === 'income' ? 'active' : '' }}" id="add_txn_panel_income">
                @if (! $canRecordIncome)
                    <p class="text-sm text-destructive mb-3">Set up an active <strong>main wallet</strong> before recording income.</p>
                    <a href="{{ route('families.wallets.index', $family) }}" class="kt-btn kt-btn-outline kt-btn-sm">Manage wallets</a>
                @else
                    <form method="post" action="{{ route('families.transactions.store', $family) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="transaction_type" value="income" />
                        <p class="text-sm text-muted-foreground">
                            Credited to main wallet:
                            <span class="font-medium text-foreground">{{ $mainWallet->name }} ({{ $mainWallet->currency_code }})</span>
                        </p>
                        <div class="grid gap-1.5">
                            <label for="income_amount" class="kt-form-label">Amount <span class="text-destructive">*</span></label>
                            <input type="number" name="amount" id="income_amount" value="{{ old('transaction_type') === 'income' ? old('amount') : '' }}" required step="0.01" min="0.01" class="kt-input" />
                        </div>
                        <input type="hidden" name="currency_code" value="{{ $mainWallet->currency_code }}" />
                        <div class="grid gap-1.5">
                            <label for="income_category_id" class="kt-form-label">Category <span class="text-destructive">*</span></label>
                            <select name="category_id" id="income_category_id" class="kt-select" required>
                                <option value="">Select category</option>
                                @foreach ($incomeCategories as $cat)
                                    <option value="{{ $cat->id }}" {{ (old('transaction_type') === 'income' && (string) old('category_id') === (string) $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-1.5">
                            <label for="received_date" class="kt-form-label">Received date <span class="text-destructive">*</span></label>
                            <input type="date" name="received_date" id="received_date" value="{{ old('transaction_type') === 'income' ? old('received_date', now()->format('Y-m-d')) : now()->format('Y-m-d') }}" required class="kt-input" />
                        </div>
                        <div class="grid gap-1.5">
                            <label for="source" class="kt-form-label">Source</label>
                            <input type="text" name="source" id="source" value="{{ old('transaction_type') === 'income' ? old('source') : '' }}" class="kt-input" placeholder="e.g. Employer, client" />
                        </div>
                        <div class="grid gap-1.5">
                            <label for="income_notes" class="kt-form-label">Notes</label>
                            <textarea name="notes" id="income_notes" rows="2" class="kt-input">{{ old('transaction_type') === 'income' ? old('notes') : '' }}</textarea>
                        </div>
                        <div class="grid gap-1.5">
                            <label for="income_liability_id" class="kt-form-label">Linked liability</label>
                            <select name="family_liability_id" id="income_liability_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach ($liabilities as $liab)
                                    <option value="{{ $liab->id }}" {{ old('transaction_type') === 'income' && (string) old('family_liability_id') === (string) $liab->id ? 'selected' : '' }}>{{ $liab->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Cancel</button>
                            <button type="submit" class="kt-btn kt-btn-primary">Record income</button>
                        </div>
                    </form>
                @endif
            </div>

            <div class="kt-tab-content px-1 {{ $initialTxnTab === 'expense' ? 'active' : '' }}" id="add_txn_panel_expense">
                @if (! $canRecordExpense)
                    <p class="text-sm text-destructive mb-3">Create an active wallet before recording expenses.</p>
                    <a href="{{ route('families.wallets.create', $family) }}" class="kt-btn kt-btn-outline kt-btn-sm">New wallet</a>
                @else
                    <form method="post" action="{{ route('families.transactions.store', $family) }}" class="grid gap-4" id="expense_txn_form">
                        @csrf
                        <input type="hidden" name="transaction_type" value="expense" />
                        <div class="grid gap-1.5">
                            <label for="exp_wallet_id" class="kt-form-label">Wallet <span class="text-destructive">*</span></label>
                            <select name="wallet_id" id="exp_wallet_id" class="kt-select" required>
                                <option value="">Select wallet</option>
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}" data-currency="{{ $w->currency_code }}" {{ (old('transaction_type') === 'expense' && (string) old('wallet_id') === (string) $w->id) ? 'selected' : '' }}>
                                        {{ $w->name }} ({{ $w->currency_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="currency_code" id="exp_currency_code" value="{{ old('transaction_type') === 'expense' ? old('currency_code', $wallets->first()->currency_code ?? '') : ($wallets->first()->currency_code ?? '') }}" />
                        <div class="grid gap-1.5">
                            <label for="exp_amount" class="kt-form-label">Amount <span class="text-destructive">*</span></label>
                            <input type="number" name="amount" id="exp_amount" value="{{ old('transaction_type') === 'expense' ? old('amount') : '' }}" required step="0.01" min="0.01" class="kt-input" />
                        </div>
                        <div class="grid gap-1.5">
                            <label for="exp_category_id" class="kt-form-label">Category <span class="text-destructive">*</span></label>
                            <select name="category_id" id="exp_category_id" class="kt-select" required>
                                <option value="">Select category</option>
                                @foreach ($expenseCategories as $cat)
                                    <option value="{{ $cat->id }}" {{ (old('transaction_type') === 'expense' && (string) old('category_id') === (string) $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-1.5">
                            <label for="expense_date" class="kt-form-label">Expense date <span class="text-destructive">*</span></label>
                            <input type="date" name="expense_date" id="expense_date" value="{{ old('transaction_type') === 'expense' ? old('expense_date', now()->format('Y-m-d')) : now()->format('Y-m-d') }}" required class="kt-input" />
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <label for="exp_description" class="kt-form-label">Description</label>
                                <input type="text" name="description" id="exp_description" value="{{ old('transaction_type') === 'expense' ? old('description') : '' }}" class="kt-input" />
                            </div>
                            <div class="grid gap-1.5">
                                <label for="merchant" class="kt-form-label">Merchant</label>
                                <input type="text" name="merchant" id="merchant" value="{{ old('transaction_type') === 'expense' ? old('merchant') : '' }}" class="kt-input" />
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <label for="paid_by" class="kt-form-label">Paid by</label>
                                <select name="paid_by" id="paid_by" class="kt-select">
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}" {{ old('transaction_type') === 'expense' ? (old('paid_by', auth()->id()) == $member->id ? 'selected' : '') : (auth()->id() == $member->id ? 'selected' : '') }}>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid gap-1.5">
                                <label for="payment_method" class="kt-form-label">Payment method</label>
                                <select name="payment_method" id="payment_method" class="kt-select">
                                    <option value="">— Optional —</option>
                                    @foreach (\App\Models\Expense::paymentMethods() as $value => $label)
                                        <option value="{{ $value }}" {{ old('transaction_type') === 'expense' && old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <label for="budget_id" class="kt-form-label">Budget source</label>
                                <select name="budget_id" id="budget_id" class="kt-select">
                                    <option value="">— None —</option>
                                    @foreach ($budgets as $b)
                                        <option value="{{ $b->id }}" {{ old('transaction_type') === 'expense' && (string) old('budget_id') === (string) $b->id ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid gap-1.5">
                                <label for="project_id" class="kt-form-label">Project</label>
                                <select name="project_id" id="project_id" class="kt-select">
                                    <option value="">— None —</option>
                                    @foreach ($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ old('transaction_type') === 'expense' && (string) old('project_id') === (string) $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <label for="exp_liability_id" class="kt-form-label">Linked liability</label>
                            <select name="family_liability_id" id="exp_liability_id" class="kt-select">
                                <option value="">— None —</option>
                                @foreach ($liabilities as $liab)
                                    <option value="{{ $liab->id }}" {{ old('transaction_type') === 'expense' && (string) old('family_liability_id') === (string) $liab->id ? 'selected' : '' }}>{{ $liab->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-1.5">
                            <label for="reference" class="kt-form-label">Reference</label>
                            <input type="text" name="reference" id="reference" value="{{ old('transaction_type') === 'expense' ? old('reference') : '' }}" class="kt-input" />
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_recurring" id="is_recurring" value="1" class="kt-checkbox" {{ old('transaction_type') === 'expense' && old('is_recurring') ? 'checked' : '' }} />
                            <label for="is_recurring" class="text-sm text-muted-foreground">Recurring expense</label>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Cancel</button>
                            <button type="submit" class="kt-btn kt-btn-primary">Record expense</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var shouldOpen = @json((bool) $openAddTxnModal);
    var initialTab = @json($initialTxnTab);

    function openAddTransactionModal() {
        var el = document.getElementById('add_transaction_modal');
        if (!el) return;
        if (typeof KTModal !== 'undefined') {
            var modal = KTModal.getInstance(el) || new KTModal(el);
            modal.show();
        } else {
            el.classList.add('open');
        }
    }

    function bindOpeners() {
        var mainBtn = document.getElementById('btn_open_add_transaction');
        if (mainBtn) mainBtn.addEventListener('click', openAddTransactionModal);
        document.querySelectorAll('[data-open-add-transaction]').forEach(function (btn) {
            btn.addEventListener('click', openAddTransactionModal);
        });
    }

    function syncExpenseCurrency() {
        var sel = document.getElementById('exp_wallet_id');
        var hidden = document.getElementById('exp_currency_code');
        if (!sel || !hidden) return;
        var opt = sel.options[sel.selectedIndex];
        var cur = opt && opt.getAttribute('data-currency');
        if (cur) hidden.value = cur;
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindOpeners();
        var wSel = document.getElementById('exp_wallet_id');
        if (wSel) {
            wSel.addEventListener('change', syncExpenseCurrency);
            syncExpenseCurrency();
        }

        if (shouldOpen) {
            openAddTransactionModal();
        }

        // Ensure correct tab is visible when reopening after validation
        if (initialTab === 'income') {
            var ti = document.getElementById('tab_income');
            var te = document.getElementById('tab_expense');
            if (ti && typeof ti.click === 'function') ti.click();
        }
    });
})();
</script>
@endpush
@endsection

