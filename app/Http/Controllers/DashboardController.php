<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDepreciation;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->can('access_admin_panel')) {
            return redirect()->route('admin.dashboard');
        }

        $user = $request->user();
        $families = $user->families()->get();
        $familyIds = $families->pluck('id')->toArray();

        $currency = config('currencies.default', 'TZS');
        $currentFamily = $families->first();
        if ($currentFamily) {
            $currency = $currentFamily->currency_code ?? $currency;
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Totals for user's families (current month)
        $totalIncome = Income::whereIn('family_id', $familyIds)
            ->whereBetween('received_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalExpenses = Expense::whereIn('family_id', $familyIds)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Total wallet balance across all user's family wallets
        $wallets = Wallet::whereIn('family_id', $familyIds)->get();
        $totalSavings = $wallets->sum(fn ($w) => $w->balance);

        // Budget: total planned vs used this month (simplified – sum of budgets that have expenses)
        $budgetUsedPercent = 0;
        $budgetUsedLabel = '—';
        if ($currentFamily) {
            $budgets = $currentFamily->budgets()
                ->with(['wallets', 'categories'])
                ->where('start_date', '<=', $endOfMonth)
                ->where('end_date', '>=', $startOfMonth)
                ->get();
            $totalBudget = $budgets->sum('amount');
            $totalBudgetUsed = $budgets->sum('used_amount');
            if ($totalBudget > 0) {
                $budgetUsedPercent = min(100, round(($totalBudgetUsed / $totalBudget) * 100));
                $budgetUsedLabel = number_format($totalBudgetUsed, 0) . ' ' . $currency . ' of ' . number_format($totalBudget, 0);
            }
        }

        // Last 6 months for income vs expense chart (2 queries instead of 12)
        $months = [];
        $chartMonthKeys = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i)->startOfMonth();
            $months[] = $d->format('M');
            $chartMonthKeys[] = $d->format('Y-m-01');
        }
        $firstChartMonth = $chartMonthKeys[0];
        $incomeByMonthRaw = Income::whereIn('family_id', $familyIds)
            ->where('received_date', '>=', $firstChartMonth)
            ->where('received_date', '<=', $endOfMonth)
            ->selectRaw('DATE_FORMAT(received_date, "%Y-%m-01") as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();
        $expenseByMonthRaw = Expense::whereIn('family_id', $familyIds)
            ->where('expense_date', '>=', $firstChartMonth)
            ->where('expense_date', '<=', $endOfMonth)
            ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m-01") as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();
        $incomeByMonth = array_map(fn ($m) => (float) ($incomeByMonthRaw[$m] ?? 0), $chartMonthKeys);
        $expenseByMonth = array_map(fn ($m) => (float) ($expenseByMonthRaw[$m] ?? 0), $chartMonthKeys);

        // Expenses by category (current month)
        $expensesByCategory = Expense::whereIn('expenses.family_id', $familyIds)
            ->whereBetween('expenses.expense_date', [$startOfMonth, $endOfMonth])
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category_name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Recent activity: incomes and expenses combined, sorted by date
        $recentIncomes = Income::whereIn('family_id', $familyIds)
            ->with('category')
            ->orderByDesc('received_date')
            ->limit(15)
            ->get();

        $recentExpenses = Expense::whereIn('family_id', $familyIds)
            ->with('category')
            ->orderByDesc('expense_date')
            ->limit(15)
            ->get();

        $recentActivity = $recentIncomes->map(fn ($i) => (object)[
            'description' => $i->source ?: 'Income',
            'category' => $i->category->name ?? 'Income',
            'amount' => $i->amount,
            'currency_code' => $i->currency_code,
            'date' => $i->received_date,
            'type' => 'income',
        ])->concat($recentExpenses->map(fn ($e) => (object)[
            'description' => $e->description ?: 'Expense',
            'category' => $e->category->name ?? 'Expense',
            'amount' => $e->amount,
            'currency_code' => $e->currency_code,
            'date' => $e->expense_date,
            'type' => 'expense',
        ]))->sortByDesc('date')->take(10)->values();

        // Project and property statistics for the current family (if any)
        $projectCount = 0;
        $activeProjectCount = 0;
        $propertyCount = 0;
        $propertyTotalValue = 0.0;

        if ($currentFamily) {
            $projectCounts = Project::where('family_id', $currentFamily->id)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active', ['active'])
                ->first();
            $projectCount = (int) ($projectCounts->total ?? 0);
            $activeProjectCount = (int) ($projectCounts->active ?? 0);

            $propertyTotalValue = (float) Property::where('family_id', $currentFamily->id)
                ->selectRaw('COALESCE(SUM(COALESCE(current_estimated_value, purchase_price, 0)), 0) as total')
                ->value('total');
            $propertyCount = Property::where('family_id', $currentFamily->id)->count();
        }

        // Alerts (for current family only)
        $alerts = [
            'mainWalletLow' => ['active' => false, 'message' => null, 'balance' => null, 'wallet' => null],
            'overExpenses' => ['active' => false, 'budgets' => []],
            'projectDelays' => ['active' => false, 'projects' => []],
            'propertyDepreciation' => ['active' => false, 'properties' => []],
        ];
        $lowBalanceThreshold = (float) config('famledger.low_balance_threshold', 100000);

        if ($currentFamily) {
            $mainWallet = $currentFamily->wallets()->where('is_primary', true)->first();
            if ($mainWallet) {
                $mainBalance = (float) $mainWallet->balance;
                if ($mainBalance < $lowBalanceThreshold) {
                    $alerts['mainWalletLow'] = [
                        'active' => true,
                        'message' => 'Main wallet balance is low.',
                        'balance' => $mainBalance,
                        'wallet' => $mainWallet,
                    ];
                }
            }

            $overBudgetBudgets = $currentFamily->budgets()
                ->with(['wallets', 'categories'])
                ->where('start_date', '<=', $endOfMonth)
                ->where('end_date', '>=', $startOfMonth)
                ->get()
                ->filter(fn ($b) => $b->is_exceeded);
            if ($overBudgetBudgets->isNotEmpty()) {
                $alerts['overExpenses'] = [
                    'active' => true,
                    'budgets' => $overBudgetBudgets->values()->all(),
                ];
            }

            $delayedProjects = Project::where('family_id', $currentFamily->id)
                ->where('status', Project::STATUS_ACTIVE)
                ->whereNotNull('target_end_date')
                ->where('target_end_date', '<', $now->toDateString())
                ->orderBy('target_end_date')
                ->get(['id', 'name', 'target_end_date', 'status']);
            if ($delayedProjects->isNotEmpty()) {
                $alerts['projectDelays'] = [
                    'active' => true,
                    'projects' => $delayedProjects->all(),
                ];
            }

            $propertyIds = Property::where('family_id', $currentFamily->id)->pluck('id')->toArray();
            $depreciatedProperties = collect();
            if (! empty($propertyIds)) {
                $latestDepreciation = PropertyDepreciation::whereIn('property_id', $propertyIds)
                    ->orderBy('year', 'desc')
                    ->get()
                    ->groupBy('property_id')
                    ->map->first();
                foreach (Property::where('family_id', $currentFamily->id)->get() as $prop) {
                    $purchase = (float) ($prop->purchase_price ?? 0);
                    $currentVal = (float) ($prop->current_estimated_value ?? 0);
                    $latestDep = $latestDepreciation[$prop->id] ?? null;
                    $bookVal = $latestDep ? (float) $latestDep->book_value : ($currentVal ?: $purchase);
                    if ($purchase > 0 && $bookVal < $purchase) {
                        $depreciatedProperties->push((object) [
                            'id' => $prop->id,
                            'name' => $prop->name,
                            'purchase_price' => $purchase,
                            'book_value' => $bookVal,
                        ]);
                    }
                }
            }
            if ($depreciatedProperties->isNotEmpty()) {
                $alerts['propertyDepreciation'] = [
                    'active' => true,
                    'properties' => $depreciatedProperties->all(),
                ];
            }
        }

        return view('dashboard', [
            'families' => $families,
            'currentFamily' => $currentFamily,
            'currency' => $currency,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'totalSavings' => $totalSavings,
            'budgetUsedPercent' => $budgetUsedPercent,
            'budgetUsedLabel' => $budgetUsedLabel,
            'chartMonths' => $months,
            'chartIncome' => $incomeByMonth,
            'chartExpense' => $expenseByMonth,
            'expensesByCategory' => $expensesByCategory,
            'recentActivity' => $recentActivity,
            'projectCount' => $projectCount,
            'activeProjectCount' => $activeProjectCount,
            'propertyCount' => $propertyCount,
            'propertyTotalValue' => $propertyTotalValue,
            'alerts' => $alerts,
        ]);
    }
}
