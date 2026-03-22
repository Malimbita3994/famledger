<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Family;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    public function index(Family $family, Request $request)
    {
        $this->authorizeFamilyMember($family);

        $type = $request->string('type')->toString(); // income|expense|''
        $walletId = $request->input('wallet_id');

        $incomeQuery = DB::table('incomes')
            ->where('incomes.family_id', $family->id)
            ->when($walletId, fn ($q) => $q->where('incomes.wallet_id', $walletId))
            ->select([
                DB::raw("'income' as type"),
                'incomes.id as id',
                'incomes.received_date as date',
                'incomes.amount as amount',
                'incomes.currency_code as currency_code',
                'wallets.id as wallet_id',
                'wallets.name as wallet_name',
                'wallets.currency_code as wallet_currency',
                'income_categories.name as category_name',
                'incomes.source as description',
                'users.name as user_name',
            ])
            ->join('wallets', 'wallets.id', '=', 'incomes.wallet_id')
            ->leftJoin('income_categories', 'income_categories.id', '=', 'incomes.category_id')
            ->leftJoin('users', 'users.id', '=', 'incomes.created_by');

        $expenseQuery = DB::table('expenses')
            ->where('expenses.family_id', $family->id)
            ->when($walletId, fn ($q) => $q->where('expenses.wallet_id', $walletId))
            ->select([
                DB::raw("'expense' as type"),
                'expenses.id as id',
                'expenses.expense_date as date',
                'expenses.amount as amount',
                'expenses.currency_code as currency_code',
                'wallets.id as wallet_id',
                'wallets.name as wallet_name',
                'wallets.currency_code as wallet_currency',
                'expense_categories.name as category_name',
                DB::raw('COALESCE(expenses.description, expenses.merchant) as description'),
                'users.name as user_name',
            ])
            ->join('wallets', 'wallets.id', '=', 'expenses.wallet_id')
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category_id')
            ->leftJoin('users', 'users.id', '=', 'expenses.created_by');

        $base = null;
        if ($type === 'income') {
            $base = $incomeQuery;
        } elseif ($type === 'expense') {
            $base = $expenseQuery;
        } else {
            $base = $incomeQuery->unionAll($expenseQuery);
        }

        $transactions = DB::query()
            ->fromSub($base, 't')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $wallets = $family->wallets()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'currency_code']);

        // Summary totals (respect wallet filter but ignore type filter)
        $totalIncome = DB::table('incomes')
            ->where('family_id', $family->id)
            ->when($walletId, fn ($q) => $q->where('wallet_id', $walletId))
            ->sum('amount');

        $totalExpenses = DB::table('expenses')
            ->where('family_id', $family->id)
            ->when($walletId, fn ($q) => $q->where('wallet_id', $walletId))
            ->sum('amount');

        $balance = $totalIncome - $totalExpenses;

        $chartCurrency = $family->currency_code ?? config('currencies.default', 'TZS');
        $now = Carbon::now();
        $chartMonthKeys = [];
        $chartMonthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i)->startOfMonth();
            $chartMonthKeys[] = $d->format('Y-m-d');
            $chartMonthLabels[] = $d->format('M');
        }
        $chartStart = $chartMonthKeys[0];
        $chartEnd = $now->copy()->endOfMonth()->toDateString();

        $incomeBucket = array_fill_keys($chartMonthKeys, 0.0);
        $expenseBucket = array_fill_keys($chartMonthKeys, 0.0);
        $incomeCountBucket = array_fill_keys($chartMonthKeys, 0);
        $expenseCountBucket = array_fill_keys($chartMonthKeys, 0);

        $incomeRows = DB::table('incomes')
            ->where('family_id', $family->id)
            ->when($walletId, fn ($q) => $q->where('wallet_id', $walletId))
            ->where('received_date', '>=', $chartStart)
            ->where('received_date', '<=', $chartEnd)
            ->get(['received_date', 'amount']);

        foreach ($incomeRows as $row) {
            $k = Carbon::parse($row->received_date)->startOfMonth()->format('Y-m-d');
            if (isset($incomeBucket[$k])) {
                $incomeBucket[$k] += (float) $row->amount;
                $incomeCountBucket[$k]++;
            }
        }

        $expenseRows = DB::table('expenses')
            ->where('family_id', $family->id)
            ->when($walletId, fn ($q) => $q->where('wallet_id', $walletId))
            ->where('expense_date', '>=', $chartStart)
            ->where('expense_date', '<=', $chartEnd)
            ->get(['expense_date', 'amount']);

        foreach ($expenseRows as $row) {
            $k = Carbon::parse($row->expense_date)->startOfMonth()->format('Y-m-d');
            if (isset($expenseBucket[$k])) {
                $expenseBucket[$k] += (float) $row->amount;
                $expenseCountBucket[$k]++;
            }
        }

        $chartIncomeByMonth = array_map(fn ($v) => round($v, 2), array_values($incomeBucket));
        $chartExpenseByMonth = array_map(fn ($v) => round($v, 2), array_values($expenseBucket));
        $chartIncomeCountByMonth = array_values($incomeCountBucket);
        $chartExpenseCountByMonth = array_values($expenseCountBucket);

        $expenseByCategory = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->where('expenses.family_id', $family->id)
            ->when($walletId, fn ($q) => $q->where('expenses.wallet_id', $walletId))
            ->where('expenses.expense_date', '>=', $chartStart)
            ->where('expenses.expense_date', '<=', $chartEnd)
            ->select(
                DB::raw('COALESCE(expense_categories.name, \'Uncategorized\') as cat_name'),
                DB::raw('SUM(expenses.amount) as total')
            )
            ->groupBy(DB::raw('COALESCE(expense_categories.name, \'Uncategorized\')'))
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $chartCategoryNames = $expenseByCategory->pluck('cat_name')->values()->all();
        $chartCategoryTotals = $expenseByCategory->pluck('total')->map(fn ($v) => round((float) $v, 2))->values()->all();

        return view('families.transactions.index', compact(
            'family', 'transactions', 'wallets', 'type',
            'totalIncome', 'totalExpenses', 'balance',
            'chartMonthLabels', 'chartIncomeByMonth', 'chartExpenseByMonth',
            'chartIncomeCountByMonth', 'chartExpenseCountByMonth',
            'chartCategoryNames', 'chartCategoryTotals', 'chartCurrency',
        ));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $request->validate([
            'transaction_type' => ['required', Rule::in(['income', 'expense'])],
        ], [
            'transaction_type.required' => 'Choose whether this is income or an expense.',
        ]);

        return match ($request->input('transaction_type')) {
            'income' => $this->storeIncome($request, $family),
            'expense' => $this->storeExpense($request, $family),
            default => redirect()
                ->route('families.transactions.index', $family)
                ->withInput($request->except(['_token']))
                ->withErrors(['transaction_type' => 'Invalid transaction type.']),
        };
    }

    protected function storeIncome(Request $request, Family $family)
    {
        $wallet = $family->mainWallet();
        if (! $wallet || $wallet->status !== 'active') {
            return redirect()
                ->route('families.transactions.index', $family)
                ->with('error', 'Main wallet is missing or inactive. Set up an active main wallet before recording income.')
                ->withInput($request->except(['_token', '_method']));
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($wallet->currency_code)])],
            'category_id' => ['required', Rule::exists('income_categories', 'id')],
            'family_liability_id' => ['nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'source' => ['nullable', 'string', 'max:255'],
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'amount.min' => 'Amount must be greater than zero.',
            'currency_code.in' => 'Currency must match the main wallet.',
            'category_id.required' => 'Please choose a category for this income.',
        ]);

        $family->incomes()->create([
            'wallet_id' => $wallet->id,
            'category_id' => $validated['category_id'],
            'family_liability_id' => $validated['family_liability_id'] ?? null,
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'source' => $validated['source'] ?? null,
            'received_date' => $validated['received_date'],
            'notes' => $validated['notes'] ?? null,
            'received_by' => $request->user()->id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('families.transactions.index', $family)
            ->with('success', 'Income recorded.');
    }

    protected function storeExpense(Request $request, Family $family)
    {
        $validated = $request->validate([
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'],
            'category_id' => ['required', Rule::exists('expense_categories', 'id')],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'merchant' => ['nullable', 'string', 'max:255'],
            'paid_by' => [
                'nullable',
                Rule::exists('users', 'id'),
                Rule::in($family->members()->pluck('users.id')->toArray()),
            ],
            'payment_method' => ['nullable', 'string', 'max:50', Rule::in(array_keys(Expense::paymentMethods()))],
            'reference' => ['nullable', 'string', 'max:100'],
            'is_recurring' => ['nullable', 'boolean'],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('family_id', $family->id)],
            'family_liability_id' => ['nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'budget_id' => ['nullable', Rule::exists('budgets', 'id')->where('family_id', $family->id)],
        ], [
            'wallet_id.required' => 'Please select a wallet. Every expense must reduce a wallet.',
            'amount.min' => 'Amount must be greater than zero.',
        ]);

        $wallet = $family->wallets()->whereKey($validated['wallet_id'])->first();
        if (! $wallet || $wallet->status !== 'active') {
            return redirect()
                ->route('families.transactions.index', $family)
                ->with('error', 'Selected wallet is missing or inactive.')
                ->withInput($request->except(['_token', '_method']));
        }

        if (strtoupper($validated['currency_code']) !== strtoupper($wallet->currency_code)) {
            return redirect()
                ->route('families.transactions.index', $family)
                ->withInput($request->except(['_token', '_method']))
                ->withErrors(['currency_code' => 'Currency must match the selected wallet.']);
        }

        if (! $wallet->canAffordDebit((float) $validated['amount'])) {
            $message = 'Insufficient funds in the selected wallet. Available balance is '
                .number_format($wallet->balance, 2).' '.strtoupper($wallet->currency_code).'.';

            return redirect()
                ->route('families.transactions.index', $family)
                ->withInput($request->except(['_token', '_method']))
                ->withErrors(['amount' => $message])
                ->with('error', $message);
        }

        $family->expenses()->create([
            'wallet_id' => $validated['wallet_id'],
            'category_id' => $validated['category_id'] ?? null,
            'subcategory' => $validated['subcategory'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'family_liability_id' => $validated['family_liability_id'] ?? null,
            'budget_id' => $validated['budget_id'] ?? null,
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'description' => $validated['description'] ?? null,
            'expense_date' => $validated['expense_date'],
            'merchant' => $validated['merchant'] ?? null,
            'paid_by' => $validated['paid_by'] ?? $request->user()->id,
            'payment_method' => $validated['payment_method'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'is_recurring' => (bool) ($validated['is_recurring'] ?? false),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('families.transactions.index', $family)
            ->with('success', 'Expense recorded.');
    }
}
