<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Family;
use App\Models\FamilyLiability;
use App\Models\Income;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDepreciation;
use App\Models\PropertyValuation;
use App\Models\SavingsGoal;
use App\Models\Transfer;
use App\Models\Wallet;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AuthorizesFamilyMember;

    protected function currency(Family $family): string
    {
        return $family->currency_code ?? config('currencies.default', 'TZS');
    }

    protected function defaultDates(): array
    {
        $from = FinancialYear::start()->format('Y-m-d');
        $to = FinancialYear::end()->format('Y-m-d');
        return [$from, $to];
    }

    /**
     * Wealth / General report — summary overview.
     */
    public function summary(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        [$dateFrom, $dateTo] = $this->defaultDates();
        $dateFrom = $request->input('from', $dateFrom);
        $dateTo = $request->input('to', $dateTo);
        $walletId = $request->input('wallet_id');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $wallets = $family->wallets()->orderBy('name')->get(['id', 'name', 'currency_code']);
        $walletIds = $walletId ? [(int) $walletId] : $wallets->pluck('id')->toArray();

        $totalIncome = Income::where('family_id', $family->id)
            ->whereIn('wallet_id', $walletIds)
            ->whereBetween('received_date', [$from, $to])
            ->sum('amount');

        $totalExpenses = Expense::where('family_id', $family->id)
            ->whereIn('wallet_id', $walletIds)
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        $savings = $totalIncome - $totalExpenses;
        $activeProjects = Project::where('family_id', $family->id)->where('status', 'active')->count();
        $totalLiabilities = (float) FamilyLiability::where('family_id', $family->id)->sum('outstanding_balance');

        $budgets = $family->budgets()
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->get();
        $totalBudget = $budgets->sum('amount');
        $totalBudgetUsed = $budgets->sum('used_amount');
        $budgetUsedPercent = $totalBudget > 0 ? min(100, round(($totalBudgetUsed / $totalBudget) * 100)) : 0;

        $budgetRows = $budgets->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'planned' => (float) $b->amount,
            'used' => (float) $b->used_amount,
            'over' => (float) $b->used_amount > (float) $b->amount,
        ])->values()->all();

        return response()->json([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'wallet_id' => $walletId ? (int) $walletId : null,
            'wallets' => $wallets->map(fn ($w) => ['id' => $w->id, 'name' => $w->name]),
            'total_income' => (float) $totalIncome,
            'total_expenses' => (float) $totalExpenses,
            'savings' => (float) $savings,
            'active_projects' => $activeProjects,
            'total_liabilities' => $totalLiabilities,
            'total_budget' => (float) $totalBudget,
            'total_budget_used' => (float) $totalBudgetUsed,
            'budget_used_percent' => $budgetUsedPercent,
            'budget_rows' => $budgetRows,
            'currency' => $this->currency($family),
        ]);
    }

    /**
     * Wallet statement — date, description, income, expense, balance.
     */
    public function walletStatement(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        [$dateFrom, $dateTo] = $this->defaultDates();
        $dateFrom = $request->input('from', $dateFrom);
        $dateTo = $request->input('to', $dateTo);
        $walletId = $request->input('wallet_id');

        $wallets = $family->wallets()->orderBy('name')->get(['id', 'name', 'currency_code']);
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
                    'date' => $e['date']->format('Y-m-d'),
                    'description' => $e['desc'],
                    'income' => $e['income'],
                    'expense' => $e['expense'],
                    'balance' => (float) $runningBalance,
                    'type' => $e['type'],
                ];
            }
        }

        return response()->json([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'wallet' => $wallet ? ['id' => $wallet->id, 'name' => $wallet->name] : null,
            'wallets' => $wallets->map(fn ($w) => ['id' => $w->id, 'name' => $w->name]),
            'rows' => $rows,
            'currency' => $wallet ? ($wallet->currency_code ?? $this->currency($family)) : $this->currency($family),
        ]);
    }

    /**
     * Expense report — by category, trend.
     */
    public function expense(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        [$dateFrom, $dateTo] = $this->defaultDates();
        $from = Carbon::parse($request->input('from', $dateFrom))->startOfDay();
        $to = Carbon::parse($request->input('to', $dateTo))->endOfDay();
        $walletId = $request->input('wallet_id');

        $wallets = $family->wallets()->orderBy('name')->get(['id', 'name']);
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
                return ['name' => $row->category->name ?? 'Uncategorized', 'total' => (float) $row->total, 'percent' => $pct];
            })
            ->sortByDesc('total')
            ->values();

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

        return response()->json([
            'date_from' => $from->format('Y-m-d'),
            'date_to' => $to->format('Y-m-d'),
            'wallet_id' => $walletId ? (int) $walletId : null,
            'wallets' => $wallets->map(fn ($w) => ['id' => $w->id, 'name' => $w->name]),
            'total_expenses' => (float) $totalExpenses,
            'by_category' => $byCategory,
            'months' => $months,
            'trend' => $trend,
            'currency' => $this->currency($family),
        ]);
    }

    /**
     * Income report — by source, trend.
     */
    public function income(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        [$dateFrom, $dateTo] = $this->defaultDates();
        $from = Carbon::parse($request->input('from', $dateFrom))->startOfDay();
        $to = Carbon::parse($request->input('to', $dateTo))->endOfDay();
        $walletId = $request->input('wallet_id');

        $wallets = $family->wallets()->orderBy('name')->get(['id', 'name']);
        $walletIds = $walletId ? [(int) $walletId] : $wallets->pluck('id')->toArray();

        $query = Income::where('family_id', $family->id)
            ->whereIn('wallet_id', $walletIds)
            ->whereBetween('received_date', [$from, $to])
            ->with('category:id,name');

        $totalIncome = (clone $query)->sum('amount');
        $perCategory = (clone $query)->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->get();

        $groupTotals = [];
        foreach ($perCategory as $row) {
            $categoryName = $row->category->name ?? 'Uncategorized';
            $parts = explode(' - ', $categoryName, 2);
            $group = trim($parts[0]) !== '' ? trim($parts[0]) : 'Uncategorized';
            $groupTotals[$group] = ($groupTotals[$group] ?? 0) + (float) $row->total;
        }
        $bySource = collect($groupTotals)
            ->map(function ($total, $name) use ($totalIncome) {
                $pct = $totalIncome > 0 ? round(($total / $totalIncome) * 100, 1) : 0;
                return ['name' => $name, 'total' => (float) $total, 'percent' => $pct];
            })
            ->sortByDesc('total')
            ->values();

        $months = [];
        $chartMonthKeys = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i)->startOfMonth();
            $months[] = $d->format('M Y');
            $chartMonthKeys[] = $d->format('Y-m-01');
        }
        $lastMonth = now()->endOfMonth()->format('Y-m-t');
        $trendRaw = Income::where('family_id', $family->id)
            ->whereIn('wallet_id', $walletIds)
            ->where('received_date', '>=', $chartMonthKeys[0])
            ->where('received_date', '<=', $lastMonth)
            ->selectRaw('DATE_FORMAT(received_date, "%Y-%m-01") as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();
        $trend = array_map(fn ($m) => (float) ($trendRaw[$m] ?? 0), $chartMonthKeys);

        return response()->json([
            'date_from' => $from->format('Y-m-d'),
            'date_to' => $to->format('Y-m-d'),
            'wallet_id' => $walletId ? (int) $walletId : null,
            'wallets' => $wallets->map(fn ($w) => ['id' => $w->id, 'name' => $w->name]),
            'total_income' => (float) $totalIncome,
            'by_source' => $bySource,
            'months' => $months,
            'trend' => $trend,
            'currency' => $this->currency($family),
        ]);
    }

    /**
     * Cash flow summary — opening, income, expense, closing.
     */
    public function cashFlow(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        [$dateFrom, $dateTo] = $this->defaultDates();
        $from = Carbon::parse($request->input('from', $dateFrom))->startOfDay();
        $to = Carbon::parse($request->input('to', $dateTo))->endOfDay();
        $walletId = $request->input('wallet_id');

        $wallets = $family->wallets()->orderBy('name')->get(['id', 'name']);
        $wallet = $walletId ? $wallets->firstWhere('id', (int) $walletId) : null;
        $walletIds = $wallet ? [$wallet->id] : $wallets->pluck('id')->toArray();

        $openingBalance = 0;
        foreach ($wallets->whereIn('id', $walletIds) as $w) {
            $openingBalance += $w->balanceAsOf($from->copy()->subDay());
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
        $totalLiabilities = (float) FamilyLiability::where('family_id', $family->id)->sum('outstanding_balance');

        return response()->json([
            'date_from' => $from->format('Y-m-d'),
            'date_to' => $to->format('Y-m-d'),
            'wallet_id' => $walletId ? (int) $walletId : null,
            'wallets' => $wallets->map(fn ($w) => ['id' => $w->id, 'name' => $w->name]),
            'opening_balance' => (float) $openingBalance,
            'total_income' => (float) $totalIncome,
            'total_expenses' => (float) $totalExpenses,
            'net_flow' => (float) $netFlow,
            'closing_balance' => (float) $closingBalance,
            'total_liabilities' => $totalLiabilities,
            'currency' => $this->currency($family),
        ]);
    }

    /**
     * Budget vs actual.
     */
    public function budgetVsActual(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        [$dateFrom, $dateTo] = $this->defaultDates();
        $from = Carbon::parse($request->input('from', $dateFrom))->startOfDay();
        $to = Carbon::parse($request->input('to', $dateTo))->endOfDay();
        $type = $request->input('type');
        $status = $request->input('status');

        $query = $family->budgets()
            ->with(['wallets:id,name', 'categories:id,name'])
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
        $rows = $budgets->map(function ($b) {
            $planned = (float) $b->amount;
            $used = (float) $b->used_amount;
            $pct = $planned > 0 ? min(100, round(($used / $planned) * 100, 1)) : 0;
            return [
                'id' => $b->id,
                'name' => $b->name,
                'type' => $b->type,
                'planned' => $planned,
                'used' => $used,
                'percent' => $pct,
                'remaining' => $planned - $used,
                'over' => $used > $planned,
            ];
        })->values();

        return response()->json([
            'date_from' => $from->format('Y-m-d'),
            'date_to' => $to->format('Y-m-d'),
            'filter_type' => $type,
            'filter_status' => $status,
            'rows' => $rows,
            'currency' => $this->currency($family),
        ]);
    }

    /**
     * Savings goals progress.
     */
    public function savings(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $goals = $family->savingsGoals()->with('wallet:id,name')->orderBy('name')->get();
        $rows = $goals->map(function ($g) {
            $target = (float) $g->target_amount;
            $saved = (float) $g->saved_amount;
            $pct = $target > 0 ? min(100, round(($saved / $target) * 100, 1)) : 0;
            return [
                'id' => $g->id,
                'name' => $g->name,
                'target' => $target,
                'saved' => $saved,
                'percent' => $pct,
                'remaining' => $target - $saved,
                'wallet' => $g->wallet ? ['id' => $g->wallet->id, 'name' => $g->wallet->name] : null,
            ];
        })->values();

        return response()->json([
            'rows' => $rows,
            'currency' => $this->currency($family),
        ]);
    }

    /**
     * Project summary — projects with funded/spent, counts.
     */
    public function projectSummary(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $tab = $request->input('tab', 'all');
        if (! in_array($tab, ['all', 'active', 'completed', 'funding'], true)) {
            $tab = 'all';
        }
        $search = $request->input('search');

        $baseQuery = $family->projects()
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

        $projects = $query->get()->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'status' => $p->status,
            'planned_budget' => (float) $p->planned_budget,
            'funded' => (float) ($p->fundings_sum_amount ?? 0),
            'spent' => (float) ($p->expenses_sum_amount ?? 0),
        ]);

        $counts = $family->projects()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', ['active', 'completed'])
            ->first();
        $fundedCount = $family->projects()->whereHas('fundings')->count();

        return response()->json([
            'tab' => $tab,
            'search' => $search,
            'projects' => $projects,
            'total_projects' => (int) ($counts->total ?? 0),
            'active_count' => (int) ($counts->active ?? 0),
            'completed_count' => (int) ($counts->completed ?? 0),
            'funded_count' => $fundedCount,
            'currency' => $this->currency($family),
        ]);
    }

    /**
     * Property report — properties with value and book value.
     */
    public function property(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = Property::where('family_id', $family->id)
            ->with(['category:id,name'])
            ->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->category_id);
        }

        $properties = $query->get();
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

        $items = $properties->map(function ($p) use ($latestValuations, $latestDepreciations) {
            $val = $latestValuations->get($p->id);
            $dep = $latestDepreciations->get($p->id);
            return [
                'id' => $p->id,
                'name' => $p->name,
                'property_code' => $p->property_code,
                'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                'current_estimated_value' => (float) ($p->current_estimated_value ?? 0),
                'purchase_price' => (float) ($p->purchase_price ?? 0),
                'latest_valuation' => $val ? ['estimated_value' => (float) $val->estimated_value, 'date' => $val->valuation_date?->format('Y-m-d')] : null,
                'latest_book_value' => $dep ? ['year' => $dep->year, 'book_value' => (float) $dep->book_value] : null,
            ];
        })->values();

        return response()->json([
            'properties' => $items,
            'currency' => $this->currency($family),
        ]);
    }
}
