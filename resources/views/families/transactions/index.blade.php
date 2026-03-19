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

