<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\Family;
use App\Models\Income;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDepreciation;
use App\Models\PropertyValuation;
use App\Models\SavingsGoal;
use App\Models\Transfer;
use App\Models\Wallet;
use App\Models\FamilyLiability;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    /**
     * Reports dashboard (landing) — analytics overview with filters.
     */
    public function index(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $dateFrom = $request->input('from', FinancialYear::start()->format('Y-m-d'));
        $dateTo = $request->input('to', FinancialYear::end()->format('Y-m-d'));
        $walletId = $request->input('wallet_id');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $wallets = $family->wallets()->orderBy('name')->get();
        $wallet = $walletId ? $wallets->firstWhere('id', (int) $walletId) : null;

        $familyIds = [$family->id];
        $walletIds = $wallet ? [$wallet->id] : $wallets->pluck('id')->toArray();

        // Summary metrics for the period
        $totalIncome = Income::whereIn('family_id', $familyIds)
            ->whereIn('wallet_id', $walletIds)
            ->whereBetween('received_date', [$from, $to])
            ->sum('amount');

        $totalExpenses = Expense::whereIn('family_id', $familyIds)
            ->whereIn('wallet_id', $walletIds)
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        $savings = $totalIncome - $totalExpenses;
        $activeProjects = Project::where('family_id', $family->id)->where('status', 'active')->count();

        // Budget usage (current month for selected family)
        $budgetUsedPercent = 0;
        $budgets = $family->budgets()
            ->with(['wallets', 'categories'])
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->get();
        $totalBudget = $budgets->sum('amount');
        $totalBudgetUsed = $budgets->sum('used_amount');
        if ($totalBudget > 0) {
            $budgetUsedPercent = min(100, round(($totalBudgetUsed / $totalBudget) * 100));
        }

        // Build per-budget rows for the General Report budgets snapshot
        $budgetRows = [];
        foreach ($budgets as $b) {
            $planned = (float) $b->amount;
            $used    = (float) $b->used_amount;

            $budgetRows[] = [
                'budget'  => $b,
                'planned' => $planned,
                'used'    => $used,
                'over'    => $used > $planned,
            ];
        }

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');
        $formatAmount = fn ($n) => number_format((float) $n, 0) . ' ' . $currency;

        // Liabilities snapshot for the family (single query)
        $totalLiabilities = (float) FamilyLiability::where('family_id', $family->id)->sum('outstanding_balance');

        return view('families.reports.index', [
            'family' => $family,
            'wallets' => $wallets,
            'wallet' => $wallet,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'savings' => $savings,
            'activeProjects' => $activeProjects,
            'budgetUsedPercent' => $budgetUsedPercent,
            'totalBudget' => $totalBudget,
            'totalBudgetUsed' => $totalBudgetUsed,
            'formatAmount' => $formatAmount,
            'currency' => $currency,
            'budgetRows' => $budgetRows,
            'totalLiabilities' => $totalLiabilities,
        ]);
    }

    /**
     * Wallet statement — bank-like report: Date | Description | Income | Expense | Balance
     */
    public function walletStatement(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $walletId = $request->input('wallet_id');
        $dateFrom = $request->input('from', FinancialYear::start()->format('Y-m-d'));
        $dateTo = $request->input('to', FinancialYear::end()->format('Y-m-d'));

        $wallets = $family->wallets()->orderBy('name')->get();
        $wallet = $wallets->firstWhere('id', (int) $walletId) ?? $wallets->first();

        $rows = [];
        $runningBalance = $wallet ? $wallet->balanceAsOf(Carbon::parse($dateFrom)->subDay()) : 0;

        if ($wallet) {
            $from = Carbon::parse($dateFrom)->startOfDay();
            $to = Carbon::parse($dateTo)->endOfDay();

            $events = [];
            Income::where('wallet_id', $wallet->id)
                ->whereBetween('received_date', [$from, $to])
                ->get(['id', 'received_date', 'amount', 'source', 'notes'])
                ->each(fn ($i) => $events[] = ['date' => $i->received_date, 'income' => (float) $i->amount, 'expense' => null, 'desc' => $i->source ?: $i->notes ?: 'Income', 'type' => 'income']);
            Expense::where('wallet_id', $wallet->id)
                ->whereBetween('expense_date', [$from, $to])
                ->get(['id', 'expense_date', 'amount', 'description', 'merchant'])
                ->each(fn ($e) => $events[] = ['date' => $e->expense_date, 'income' => null, 'expense' => (float) $e->amount, 'desc' => $e->description ?: $e->merchant ?: 'Expense', 'type' => 'expense']);
            Transfer::where('to_wallet_id', $wallet->id)
                ->whereBetween('transfer_date', [$from, $to])
                ->with('fromWallet:id,name')
                ->get()
                ->each(fn ($t) => $events[] = ['date' => $t->transfer_date, 'income' => (float) $t->amount, 'expense' => null, 'desc' => 'Transfer from ' . ($t->fromWallet->name ?? 'Wallet'), 'type' => 'transfer_in']);
            Transfer::where('from_wallet_id', $wallet->id)
                ->whereBetween('transfer_date', [$from, $to])
                ->with('toWallet:id,name')
                ->get()
                ->each(fn ($t) => $events[] = ['date' => $t->transfer_date, 'income' => null, 'expense' => (float) $t->amount, 'desc' => 'Transfer to ' . ($t->toWallet->name ?? 'Wallet'), 'type' => 'transfer_out']);

            usort($events, fn ($a, $b) => $a['date'] <=> $b['date']);
            foreach ($events as $e) {
                $runningBalance += ($e['income'] ?? 0) - ($e['expense'] ?? 0);
                $rows[] = [
                    'date' => $e['date'],
                    'description' => $e['desc'],
                    'income' => $e['income'],
                    'expense' => $e['expense'],
                    'balance' => $runningBalance,
                    'type' => $e['type'],
                ];
            }
        }

        $currency = ($wallet ? $wallet->currency_code : null) ?? $family->currency_code ?? config('currencies.default', 'TZS');

        return view('families.reports.wallet-statement', [
            'family' => $family,
            'wallets' => $wallets,
            'wallet' => $wallet,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'currency' => $currency,
        ]);
    }

    /**
     * Property report — overview of family properties, value and book value.
     */
    public function property(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        $query = Property::where('family_id', $family->id)
            ->with(['category'])
            ->orderBy('name');

        $status = $request->input('status');
        $categoryId = $request->input('category_id');

        if ($status) {
            $query->where('status', $status);
        }
        if ($categoryId) {
            $query->where('category_id', (int) $categoryId);
        }

        $properties = $query->get();

        // Attach latest valuation and depreciation book value per property
        $propertyIds = $properties->pluck('id')->all();

        $latestValuations = PropertyValuation::whereIn('property_id', $propertyIds)
            ->select('property_id', 'estimated_value', 'valuation_date')
            ->orderBy('valuation_date', 'desc')
            ->get()
            ->groupBy('property_id')
            ->map->first();

        $latestDepreciations = PropertyDepreciation::whereIn('property_id', $propertyIds)
            ->select('property_id', 'year', 'book_value')
            ->orderBy('year', 'desc')
            ->get()
            ->groupBy('property_id')
            ->map->first();

        $categories = $properties->pluck('category')->filter()->unique('id')->values();

        return view('families.reports.property', [
            'family' => $family,
            'properties' => $properties,
            'currency' => $currency,
            'latestValuations' => $latestValuations,
            'latestDepreciations' => $latestDepreciations,
            'categories' => $categories,
            'filters' => [
                'status' => $status,
                'category_id' => $categoryId,
            ],
        ]);
    }

    /**
     * Expense report — by category, trend, top categories.
     */
    public function expense(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $dateFrom = $request->input('from', FinancialYear::start()->format('Y-m-d'));
        $dateTo = $request->input('to', FinancialYear::end()->format('Y-m-d'));
        $walletId = $request->input('wallet_id');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $wallets = $family->wallets()->orderBy('name')->get();
        $walletIds = $walletId ? [(int) $walletId] : $wallets->pluck('id')->toArray();

        $query = Expense::where('family_id', $family->id)
            ->whereIn('wallet_id', $walletIds)
            ->whereBetween('expense_date', [$from, $to])
            ->with('category:id,name');

        $totalExpenses = (clone $query)->sum('amount');
        $byCategory = (clone $query)->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->get()
            ->map(function ($row) use ($totalExpenses) {
                $pct = $totalExpenses > 0 ? round(($row->total / $totalExpenses) * 100, 1) : 0;
                return [
                    'name' => $row->category->name ?? 'Uncategorized',
                    'total' => (float) $row->total,
                    'percent' => $pct,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Monthly trend (last 6 months) — single aggregated query
        $months = [];
        $chartMonthKeys = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i)->startOfMonth();
            $months[] = $d->format('M Y');
            $chartMonthKeys[] = $d->format('Y-m-01');
        }
        $firstMonth = $chartMonthKeys[0];
        $lastMonth = now()->endOfMonth()->format('Y-m-t');
        $trendRaw = Expense::where('family_id', $family->id)
            ->whereIn('wallet_id', $walletIds)
            ->where('expense_date', '>=', $firstMonth)
            ->where('expense_date', '<=', $lastMonth)
            ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m-01") as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();
        $trend = array_map(fn ($m) => (float) ($trendRaw[$m] ?? 0), $chartMonthKeys);

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        return view('families.reports.expense', [
            'family' => $family,
            'wallets' => $wallets,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'walletId' => $walletId,
            'totalExpenses' => $totalExpenses,
            'byCategory' => $byCategory,
            'months' => $months,
            'trend' => $trend,
            'currency' => $currency,
        ]);
    }

    /**
     * Income report — by category/source, over time.
     */
    public function income(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $dateFrom = $request->input('from', FinancialYear::start()->format('Y-m-d'));
        $dateTo = $request->input('to', FinancialYear::end()->format('Y-m-d'));
        $walletId = $request->input('wallet_id');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $wallets = $family->wallets()->orderBy('name')->get();
        $walletIds = $walletId ? [(int) $walletId] : $wallets->pluck('id')->toArray();

        $query = Income::where('family_id', $family->id)
            ->whereIn('wallet_id', $walletIds)
            ->whereBetween('received_date', [$from, $to])
            ->with('category:id,name');

        $totalIncome = (clone $query)->sum('amount');

        // Group income by parent category (e.g. "Wages", "Other income")
        $perCategory = (clone $query)->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->get();

        $groupTotals = [];
        foreach ($perCategory as $row) {
            $categoryName = $row->category->name ?? 'Uncategorized';
            // Use the prefix before " - " as the group, falling back to full name
            $parts = explode(' - ', $categoryName, 2);
            $group = trim($parts[0]) !== '' ? trim($parts[0]) : 'Uncategorized';

            if (! isset($groupTotals[$group])) {
                $groupTotals[$group] = 0.0;
            }
            $groupTotals[$group] += (float) $row->total;
        }

        $bySource = collect($groupTotals)
            ->map(function ($total, $name) use ($totalIncome) {
                $pct = $totalIncome > 0 ? round(($total / $totalIncome) * 100, 1) : 0;
                return [
                    'name' => $name,
                    'total' => (float) $total,
                    'percent' => $pct,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Monthly trend (last 6 months) — single aggregated query
        $months = [];
        $chartMonthKeys = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i)->startOfMonth();
            $months[] = $d->format('M Y');
            $chartMonthKeys[] = $d->format('Y-m-01');
        }
        $firstMonth = $chartMonthKeys[0];
        $lastMonth = now()->endOfMonth()->format('Y-m-t');
        $trendRaw = Income::where('family_id', $family->id)
            ->whereIn('wallet_id', $walletIds)
            ->where('received_date', '>=', $firstMonth)
            ->where('received_date', '<=', $lastMonth)
            ->selectRaw('DATE_FORMAT(received_date, "%Y-%m-01") as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();
        $trend = array_map(fn ($m) => (float) ($trendRaw[$m] ?? 0), $chartMonthKeys);

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        return view('families.reports.income', [
            'family' => $family,
            'wallets' => $wallets,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'walletId' => $walletId,
            'totalIncome' => $totalIncome,
            'bySource' => $bySource,
            'months' => $months,
            'trend' => $trend,
            'currency' => $currency,
        ]);
    }

    /**
     * Unified finance reports (Cash flow, Income, Expense, Transfer, Budget, Savings) with tabs.
     */
    public function cashFlow(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $report = $request->input('report', 'cash-flow');
        $validReports = ['cash-flow', 'income', 'expense', 'transfer', 'budget', 'savings'];
        if (! in_array($report, $validReports, true)) {
            $report = 'cash-flow';
        }

        $dateFrom = $request->input('from', FinancialYear::start()->format('Y-m-d'));
        $dateTo = $request->input('to', FinancialYear::end()->format('Y-m-d'));
        $walletId = $request->input('wallet_id');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $wallets = $family->wallets()->orderBy('name')->get();
        $wallet = $walletId ? $wallets->firstWhere('id', (int) $walletId) : null;
        $walletIds = $wallet ? [$wallet->id] : $wallets->pluck('id')->toArray();

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        $openingBalance = 0;
        $totalIncome = 0;
        $totalExpenses = 0;
        $netFlow = 0;
        $closingBalance = 0;
        $liabilityChange = 0;
        $periodLiabilityTotal = 0;
        $bySource = collect();
        $byCategory = collect();
        $months = [];
        $trend = [];
        $transfers = collect();
        $totalTransferred = 0;
        $budgetRows = [];
        $filterType = null;
        $filterStatus = null;
        $budgetTypes = [];
        $savingsRows = [];

        if ($report === 'cash-flow') {
            foreach ($wallets->whereIn('id', $walletIds) as $w) {
                $openingBalance += $this->walletBalanceAsOf($w, $from->copy()->subDay());
            }
            $totalIncome = Income::where('family_id', $family->id)
                ->whereIn('wallet_id', $walletIds)
                ->whereBetween('received_date', [$from, $to])
                ->sum('amount');
            $totalExpenses = Expense::where('family_id', $family->id)
                ->whereIn('wallet_id', $walletIds)
                ->whereBetween('expense_date', [$from, $to])
                ->sum('amount');
            $netFlow = $totalIncome - $totalExpenses;
            $closingBalance = $openingBalance + $netFlow;

            // Liability movement (loan draws minus repayments) and closing outstanding
            $liabilityIn = Income::where('family_id', $family->id)
                ->whereIn('wallet_id', $walletIds)
                ->whereBetween('received_date', [$from, $to])
                ->whereNotNull('family_liability_id')
                ->sum('amount');
            $liabilityOut = Expense::where('family_id', $family->id)
                ->whereIn('wallet_id', $walletIds)
                ->whereBetween('expense_date', [$from, $to])
                ->whereNotNull('family_liability_id')
                ->sum('amount');
            $liabilityChange = $liabilityIn - $liabilityOut;
            $periodLiabilityTotal = (float) FamilyLiability::where('family_id', $family->id)->sum('outstanding_balance');
        }

        if ($report === 'income') {
            $query = Income::where('family_id', $family->id)
                ->whereIn('wallet_id', $walletIds)
                ->whereBetween('received_date', [$from, $to])
                ->with('category:id,name');
            $totalIncome = (clone $query)->sum('amount');

            // Group income by parent category (e.g. "Wages", "Other income")
            $perCategory = (clone $query)->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->get();

            $groupTotals = [];
            foreach ($perCategory as $row) {
                $categoryName = $row->category->name ?? 'Uncategorized';
                $parts = explode(' - ', $categoryName, 2);
                $group = trim($parts[0]) !== '' ? trim($parts[0]) : 'Uncategorized';

                if (! isset($groupTotals[$group])) {
                    $groupTotals[$group] = 0.0;
                }
                $groupTotals[$group] += (float) $row->total;
            }

            $bySource = collect($groupTotals)
                ->map(function ($total, $name) use ($totalIncome) {
                    $pct = $totalIncome > 0 ? round(($total / $totalIncome) * 100, 1) : 0;
                    return ['name' => $name, 'total' => (float) $total, 'percent' => $pct];
                })
                ->sortByDesc('total')
                ->values();
            $incomeMonthKeys = [];
            for ($i = 5; $i >= 0; $i--) {
                $d = now()->subMonths($i)->startOfMonth();
                $months[] = $d->format('M Y');
                $incomeMonthKeys[] = $d->format('Y-m-01');
            }
            $incomeTrendRaw = Income::where('family_id', $family->id)
                ->whereIn('wallet_id', $walletIds)
                ->where('received_date', '>=', $incomeMonthKeys[0])
                ->where('received_date', '<=', now()->endOfMonth()->format('Y-m-t'))
                ->selectRaw('DATE_FORMAT(received_date, "%Y-%m-01") as month, SUM(amount) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->all();
            $trend = array_map(fn ($m) => (float) ($incomeTrendRaw[$m] ?? 0), $incomeMonthKeys);
        }

        if ($report === 'expense') {
            $query = Expense::where('family_id', $family->id)
                ->whereIn('wallet_id', $walletIds)
                ->whereBetween('expense_date', [$from, $to])
                ->with('category:id,name');
            $totalExpenses = (clone $query)->sum('amount');
            $byCategory = (clone $query)->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->get()
                ->map(function ($row) use ($totalExpenses) {
                    $pct = $totalExpenses > 0 ? round(($row->total / $totalExpenses) * 100, 1) : 0;
                    return ['name' => $row->category->name ?? 'Uncategorized', 'total' => (float) $row->total, 'percent' => $pct];
                })
                ->sortByDesc('total')
                ->values();
            $expenseMonthKeys = [];
            for ($i = 5; $i >= 0; $i--) {
                $d = now()->subMonths($i)->startOfMonth();
                $months[] = $d->format('M Y');
                $expenseMonthKeys[] = $d->format('Y-m-01');
            }
            $expenseTrendRaw = Expense::where('family_id', $family->id)
                ->whereIn('wallet_id', $walletIds)
                ->where('expense_date', '>=', $expenseMonthKeys[0])
                ->where('expense_date', '<=', now()->endOfMonth()->format('Y-m-t'))
                ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m-01") as month, SUM(amount) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->all();
            $trend = array_map(fn ($m) => (float) ($expenseTrendRaw[$m] ?? 0), $expenseMonthKeys);
        }

        if ($report === 'transfer') {
            $query = Transfer::where('family_id', $family->id)
                ->whereBetween('transfer_date', [$from, $to])
                ->with(['fromWallet:id,name', 'toWallet:id,name', 'createdBy:id,name']);
            if ($walletId) {
                $query->where(function ($q) use ($walletId) {
                    $q->where('from_wallet_id', $walletId)->orWhere('to_wallet_id', $walletId);
                });
            }
            $transfers = $query->orderBy('transfer_date', 'desc')->get();
            $totalTransferred = $transfers->sum('amount');
        }

        if ($report === 'budget') {
            $filterType = $request->input('type');
            $filterStatus = $request->input('status');
            $dateFrom = $request->input('from', FinancialYear::start()->format('Y-m-d'));
            $dateTo = $request->input('to', FinancialYear::end()->format('Y-m-d'));
            $from = Carbon::parse($dateFrom)->startOfDay();
            $to = Carbon::parse($dateTo)->endOfDay();
            $budgetTypes = Budget::types();
            $query = $family->budgets()
                ->with(['wallets', 'categories'])
                ->where('start_date', '<=', $to)
                ->where('end_date', '>=', $from)
                ->orderBy('name');
            if ($filterType && in_array($filterType, ['family', 'category', 'wallet', 'project'], true)) {
                $query->where('type', $filterType);
            }
            if ($filterStatus && in_array($filterStatus, ['active', 'archived'], true)) {
                $query->where('status', $filterStatus);
            }
            $budgets = $query->get();
            foreach ($budgets as $b) {
                $planned = (float) $b->amount;
                $used = (float) $b->used_amount;
                $pct = $planned > 0 ? min(100, round(($used / $planned) * 100, 1)) : 0;
                $budgetRows[] = [
                    'budget' => $b,
                    'planned' => $planned,
                    'used' => $used,
                    'percent' => $pct,
                    'remaining' => $planned - $used,
                    'over' => $used > $planned,
                ];
            }
        }

        if ($report === 'savings') {
            $goals = $family->savingsGoals()->with('wallet:id,name')->orderBy('name')->get();
            foreach ($goals as $g) {
                $target = (float) $g->target_amount;
                $saved = (float) $g->saved_amount;
                $pct = $target > 0 ? min(100, round(($saved / $target) * 100, 1)) : 0;
                $savingsRows[] = [
                    'goal' => $g,
                    'target' => $target,
                    'saved' => $saved,
                    'percent' => $pct,
                    'remaining' => $target - $saved,
                ];
            }
        }

        return view('families.reports.finance', [
            'family' => $family,
            'wallets' => $wallets,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'walletId' => $walletId,
            'currency' => $currency,
            'report' => $report,
            'openingBalance' => $openingBalance,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netFlow' => $netFlow,
            'closingBalance' => $closingBalance,
            'liabilityChange' => $liabilityChange,
            'periodLiabilityTotal' => $periodLiabilityTotal,
            'bySource' => $bySource,
            'byCategory' => $byCategory,
            'months' => $months,
            'trend' => $trend,
            'transfers' => $transfers,
            'totalTransferred' => $totalTransferred,
            'budgetRows' => $budgetRows,
            'filterType' => $filterType,
            'filterStatus' => $filterStatus,
            'budgetTypes' => $budgetTypes,
            'savingsRows' => $savingsRows,
        ]);
    }

    /**
     * Budget vs Actual — progress bars and variance.
     */
    public function budgetVsActual(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $type = $request->input('type');
        $status = $request->input('status');
        $dateFrom = $request->input('from', FinancialYear::start()->format('Y-m-d'));
        $dateTo = $request->input('to', FinancialYear::end()->format('Y-m-d'));
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $query = $family->budgets()
            ->with(['wallets', 'categories'])
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->orderBy('name');

        if ($type && in_array($type, ['family', 'category', 'wallet', 'project'], true)) {
            $query->where('type', $type);
        }
        if ($status && in_array($status, ['active', 'archived'], true)) {
            $query->where('status', $status);
        }

        $budgets = $query->get();
        $rows = [];
        foreach ($budgets as $b) {
            $planned = (float) $b->amount;
            $used = (float) $b->used_amount;
            $pct = $planned > 0 ? min(100, round(($used / $planned) * 100, 1)) : 0;
            $variance = $planned - $used;
            $rows[] = [
                'budget' => $b,
                'planned' => $planned,
                'used' => $used,
                'percent' => $pct,
                'remaining' => $variance,
                'over' => $used > $planned,
            ];
        }

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        // "Mother" family budget (umbrella plan for the family)
        $motherBudget = $family->budgets()
            ->with(['wallets', 'categories'])
            ->where('type', Budget::TYPE_FAMILY)
            ->orderBy('start_date')
            ->first();

        // Primary family wallet (main wallet other wallets draw from)
        $primaryWallet = $family->wallets()
            ->where('is_primary', true)
            ->orderBy('id')
            ->first();

        // Check if main budget can be supported by main wallet balance
        $budgetFeasibility = null;
        if ($motherBudget && $primaryWallet) {
            $budgetFeasibility = $motherBudget->canBeSupportedByWallet($primaryWallet);
        }

        return view('families.reports.budget-vs-actual', [
            'family' => $family,
            'rows' => $rows,
            'currency' => $currency,
            'filterType' => $type,
            'filterStatus' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'budgetTypes' => Budget::types(),
            'budgetRecurrences' => Budget::recurrences(),
            'motherBudget' => $motherBudget,
            'primaryWallet' => $primaryWallet,
            'budgetFeasibility' => $budgetFeasibility,
        ]);
    }

    /**
     * Savings report — goals progress, contributions over time.
     */
    public function savings(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $goals = $family->savingsGoals()->with('wallet:id,name')->orderBy('name')->get();
        $rows = [];
        foreach ($goals as $g) {
            $target = (float) $g->target_amount;
            $saved = (float) $g->saved_amount;
            $pct = $target > 0 ? min(100, round(($saved / $target) * 100, 1)) : 0;
            $rows[] = [
                'goal' => $g,
                'target' => $target,
                'saved' => $saved,
                'percent' => $pct,
                'remaining' => $target - $saved,
            ];
        }

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        return view('families.reports.savings', [
            'family' => $family,
            'rows' => $rows,
            'currency' => $currency,
        ]);
    }

    /**
     * Project summary report. Tabs: all, active, completed, funding.
     */
    public function projectSummary(Request $request, Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $tab = $request->input('tab', 'all');
        if (! in_array($tab, ['all', 'active', 'completed', 'funding'], true)) {
            $tab = 'all';
        }
        $search = $request->input('search');

        $baseQuery = $family->projects()
            ->with(['budget'])
            ->withSum('fundings', 'amount')
            ->withSum('expenses', 'amount');

        $query = (clone $baseQuery)->orderBy('name');

        if ($tab === 'active') {
            $query->where('status', 'active');
        } elseif ($tab === 'completed') {
            $query->where('status', 'completed');
        } elseif ($tab === 'funding') {
            $query->whereHas('fundings');
        }

        if ($search && trim($search) !== '') {
            $query->where('name', 'like', '%' . trim($search) . '%');
        }

        $projects = $query->get();
        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        // Project counts in one query
        $counts = $family->projects()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', ['active', 'completed'])
            ->first();
        $totalProjects = (int) ($counts->total ?? 0);
        $activeCount = (int) ($counts->active ?? 0);
        $completedCount = (int) ($counts->completed ?? 0);
        $fundedCount = $family->projects()->whereHas('fundings')->count();

        return view('families.reports.project-summary', [
            'family' => $family,
            'projects' => $projects,
            'currency' => $currency,
            'filterTab' => $tab,
            'filterSearch' => $search,
            'projectStatuses' => Project::statuses(),
            'totalProjects' => $totalProjects,
            'activeCount' => $activeCount,
            'completedCount' => $completedCount,
            'fundedCount' => $fundedCount,
        ]);
    }

    private function walletBalanceAsOf(Wallet $wallet, Carbon $asOf): float
    {
        return $wallet->balanceAsOf($asOf);
    }
}
