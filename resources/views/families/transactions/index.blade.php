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
            <a href="{{ route('families.incomes.create', $family) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-arrow-up"></i>
                Add income
            </a>
            <a href="{{ route('families.expenses.create', $family) }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-arrow-down"></i>
                Add expense
            </a>
        </div>
    </div>

    <div class="flex flex-wrap gap-x-6 gap-y-4 mb-6">
        <div class="kt-card p-5 flex-1 min-w-[200px]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Income</p>
                    <p class="text-2xl font-bold tabular-nums mt-1 text-green-600">{{ number_format($totalIncome ?? 0, 2) }}</p>
                </div>
                <span class="rounded-full size-12 flex items-center justify-center bg-green-500/10 text-green-600"><i class="ki-filled ki-arrow-up text-xl"></i></span>
            </div>
        </div>
        <div class="kt-card p-5 flex-1 min-w-[200px]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Expenses</p>
                    <p class="text-2xl font-bold tabular-nums mt-1 text-red-600">{{ number_format($totalExpense ?? 0, 2) }}</p>
                </div>
                <span class="rounded-full size-12 flex items-center justify-center bg-red-500/10 text-red-600"><i class="ki-filled ki-arrow-down text-xl"></i></span>
            </div>
        </div>
        <div class="kt-card p-5 flex-1 min-w-[200px]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Net Balance</p>
                    <p class="text-2xl font-bold tabular-nums mt-1 {{ ($netBalance ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($netBalance ?? 0, 2) }}</p>
                </div>
                <span class="rounded-full size-12 flex items-center justify-center bg-blue-500/10 text-blue-600"><i class="ki-filled ki-wallet text-xl"></i></span>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 flex items-center gap-3 text-red-800 dark:text-red-200">
            <i class="ki-filled ki-information-2 text-xl shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="h-6"></div>

    <div class="kt-card kt-card-grid min-w-full mt-8">
        <div class="kt-card-header flex-wrap gap-2">
            <h3 class="kt-card-title text-sm">All transactions</h3>
            <form method="get" class="flex items-center gap-2 justify-end">
                <label for="type" class="text-sm text-muted-foreground">Type</label>
                <select name="type" id="type" class="kt-select kt-select-sm w-auto" onchange="this.form.submit()">
                    <option value="" {{ ($type ?? '') === '' ? 'selected' : '' }}>All</option>
                    <option value="income" {{ ($type ?? '') === 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ ($type ?? '') === 'expense' ? 'selected' : '' }}>Expense</option>
                </select>

                <label for="wallet_id" class="text-sm text-muted-foreground">Wallet</label>
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
                        <a href="{{ route('families.incomes.create', $family) }}" class="kt-btn kt-btn-outline">Add income</a>
                        <a href="{{ route('families.expenses.create', $family) }}" class="kt-btn kt-btn-outline">Add expense</a>
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
@endsection

