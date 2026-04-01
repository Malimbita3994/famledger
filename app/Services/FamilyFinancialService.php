<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Family;
use App\Models\Income;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for family financial aggregates used by dashboards and APIs.
 */
class FamilyFinancialService
{
    public function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    public function monthBucketExpression(string $column): string
    {
        return match ($this->driver()) {
            'sqlite' => "strftime('%Y-%m-01', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m-01')",
        };
    }

    /**
     * Sum of all wallet computed balances for the given families.
     *
     * @param  array<int>  $familyIds
     */
    public function totalWalletBalance(array $familyIds): float
    {
        if ($familyIds === []) {
            return 0.0;
        }

        $wallets = Wallet::whereIn('family_id', $familyIds)
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->get();

        return (float) $wallets->sum(fn (Wallet $w) => $this->walletComputedBalance($w));
    }

    public function walletComputedBalance(Wallet $w): float
    {
        return (float) $w->initial_balance
            + (float) ($w->incomes_sum_amount ?? 0)
            - (float) ($w->expenses_sum_amount ?? 0)
            + (float) ($w->incoming_transfers_sum_amount ?? 0)
            - (float) ($w->outgoing_transfers_sum_amount ?? 0);
    }

    /**
     * @param  array<int>  $familyIds
     */
    public function totalIncomeBetween(array $familyIds, Carbon $from, Carbon $to): float
    {
        if ($familyIds === []) {
            return 0.0;
        }

        return (float) Income::whereIn('family_id', $familyIds)
            ->whereBetween('received_date', [$from, $to])
            ->sum('amount');
    }

    /**
     * @param  array<int>  $familyIds
     */
    public function totalExpensesBetween(array $familyIds, Carbon $from, Carbon $to): float
    {
        if ($familyIds === []) {
            return 0.0;
        }

        return (float) Expense::whereIn('family_id', $familyIds)
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');
    }

    /**
     * Map month keys (Y-m-01) to totals for income in range.
     *
     * @param  array<int>  $familyIds
     * @param  array<string>  $monthKeys
     * @return array<float>
     */
    public function incomeTotalsByMonthKeys(array $familyIds, array $monthKeys, Carbon $rangeEnd): array
    {
        if ($familyIds === [] || $monthKeys === []) {
            return array_map(fn () => 0.0, $monthKeys);
        }

        $first = $monthKeys[0];
        $raw = Income::whereIn('family_id', $familyIds)
            ->where('received_date', '>=', $first)
            ->where('received_date', '<=', $rangeEnd)
            ->selectRaw($this->monthBucketExpression('received_date').' as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();

        return array_map(fn ($m) => (float) ($raw[$m] ?? 0), $monthKeys);
    }

    /**
     * @param  array<int>  $familyIds
     * @param  array<string>  $monthKeys
     * @return array<float>
     */
    public function expenseTotalsByMonthKeys(array $familyIds, array $monthKeys, Carbon $rangeEnd): array
    {
        if ($familyIds === [] || $monthKeys === []) {
            return array_map(fn () => 0.0, $monthKeys);
        }

        $first = $monthKeys[0];
        $raw = Expense::whereIn('family_id', $familyIds)
            ->where('expense_date', '>=', $first)
            ->where('expense_date', '<=', $rangeEnd)
            ->selectRaw($this->monthBucketExpression('expense_date').' as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();

        return array_map(fn ($m) => (float) ($raw[$m] ?? 0), $monthKeys);
    }

    /**
     * @param  array<int>  $familyIds
     */
    public function expensesByCategoryForMonth(array $familyIds, Carbon $monthStart, Carbon $monthEnd, int $limit = 8): Collection
    {
        if ($familyIds === []) {
            return collect();
        }

        return Expense::whereIn('expenses.family_id', $familyIds)
            ->whereBetween('expenses.expense_date', [$monthStart, $monthEnd])
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category_name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Budget summary for budgets overlapping the given month window.
     *
     * @return array{percent: int, label: string}
     */
    public function budgetSummaryForMonth(Family $family, Carbon $monthStart, Carbon $monthEnd): array
    {
        $budgets = $family->budgets()
            ->with(['wallets', 'categories'])
            ->where('start_date', '<=', $monthEnd)
            ->where('end_date', '>=', $monthStart)
            ->get();
        $totalBudget = $budgets->sum('amount');
        $totalBudgetUsed = $budgets->sum('used_amount');
        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        if ($totalBudget <= 0) {
            return ['percent' => 0, 'label' => '—'];
        }

        $percent = min(100, (int) round(($totalBudgetUsed / $totalBudget) * 100));

        return [
            'percent' => $percent,
            'label' => number_format($totalBudgetUsed, 0).' '.$currency.' of '.number_format($totalBudget, 0),
        ];
    }

    /**
     * Lifetime income/expense sums and aggregate wallet balance for the family profile financial overview.
     *
     * @return array{total_income: float, total_expenses: float, net_flow: float, wallet_balance_total: float}
     */
    public function getProfileLedgerSummary(int $familyId): array
    {
        $totalIncome = (float) (Income::where('family_id', $familyId)->sum('amount') ?? 0);
        $totalExpenses = (float) (Expense::where('family_id', $familyId)->sum('amount') ?? 0);

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_flow' => $totalIncome - $totalExpenses,
            'wallet_balance_total' => $this->totalWalletBalance([$familyId]),
        ];
    }

    /**
     * True if any overlapping budget for the month has used > planned (in the red).
     */
    public function familyHasOverBudgetThisMonth(Family $family, ?Carbon $at = null): bool
    {
        $at = $at ?? Carbon::now();
        $monthStart = $at->copy()->startOfMonth();
        $monthEnd = $at->copy()->endOfMonth();

        $budgets = $family->budgets()
            ->where('start_date', '<=', $monthEnd)
            ->where('end_date', '>=', $monthStart)
            ->get(['amount', 'used_amount']);

        foreach ($budgets as $b) {
            if ((float) $b->used_amount > (float) $b->amount) {
                return true;
            }
        }

        return false;
    }

    /**
     * Savings-like destination wallets (transfer rules).
     */
    public function isSavingsLikeWallet(Wallet $wallet): bool
    {
        return in_array($wallet->type, ['savings', 'emergency_fund'], true);
    }

    /**
     * Block transfers into savings when any budget line is over plan for the current month.
     */
    public function transferToSavingsBlockedByBudget(Family $family, Wallet $toWallet, ?Carbon $at = null): bool
    {
        if (! $this->isSavingsLikeWallet($toWallet)) {
            return false;
        }

        return $this->familyHasOverBudgetThisMonth($family, $at);
    }

    /**
     * @param  array<int>  $familyIds
     */
    public function mergeRecentActivityForApi(array $familyIds, int $take = 10): Collection
    {
        $recentIncomes = Income::whereIn('family_id', $familyIds)
            ->with([
                'category:id,name',
                'linkedProject:id,name,family_id',
                'linkedProperty:id,name,family_id',
            ])
            ->orderByDesc('received_date')
            ->limit(5)
            ->get();

        $recentExpenses = Expense::whereIn('family_id', $familyIds)
            ->with([
                'category:id,name',
                'project:id,name,family_id',
            ])
            ->orderByDesc('expense_date')
            ->limit(5)
            ->get();

        return $recentIncomes->map(function ($i) {
            $related = [];
            if ($i->linkedProject) {
                $related[] = [
                    'label' => __('Project').': '.$i->linkedProject->name,
                    'url' => route('families.projects.show', $i->linkedProject),
                ];
            }
            if ($i->linkedProperty) {
                $related[] = [
                    'label' => __('Property').': '.$i->linkedProperty->name,
                    'url' => route('families.properties.show', $i->linkedProperty),
                ];
            }

            return [
                'id' => $i->id,
                'type' => 'income',
                'description' => $i->source ?: 'Income',
                'category' => $i->category->name ?? 'Income',
                'amount' => (float) $i->amount,
                'currency_code' => $i->currency_code,
                'date' => $i->received_date?->format('Y-m-d'),
                'url' => route('families.incomes.show', $i),
                'related_links' => $related,
            ];
        })->concat($recentExpenses->map(function ($e) {
            $related = [];
            if ($e->project) {
                $related[] = [
                    'label' => __('Project').': '.$e->project->name,
                    'url' => route('families.projects.show', $e->project),
                ];
            }

            return [
                'id' => $e->id,
                'type' => 'expense',
                'description' => $e->description ?: 'Expense',
                'category' => $e->category->name ?? 'Expense',
                'amount' => (float) $e->amount,
                'currency_code' => $e->currency_code,
                'date' => $e->expense_date?->format('Y-m-d'),
                'url' => route('families.transactions.index', ['type' => 'expense']),
                'related_links' => $related,
            ];
        }))->sortByDesc('date')->take($take)->values();
    }

    /**
     * @param  array<int>  $familyIds
     * @return Collection<int, object>
     */
    public function mergeRecentActivityForWeb(array $familyIds, int $perSource = 15, int $take = 10): Collection
    {
        $recentIncomes = Income::whereIn('family_id', $familyIds)
            ->with([
                'category',
                'linkedProject:id,name,family_id',
                'linkedProperty:id,name,family_id',
            ])
            ->orderByDesc('received_date')
            ->limit($perSource)
            ->get();

        $recentExpenses = Expense::whereIn('family_id', $familyIds)
            ->with([
                'category',
                'project:id,name,family_id',
            ])
            ->orderByDesc('expense_date')
            ->limit($perSource)
            ->get();

        return $recentIncomes->map(function ($i) {
            $related = [];
            if ($i->linkedProject) {
                $related[] = [
                    'label' => __('Project').': '.$i->linkedProject->name,
                    'url' => route('families.projects.show', $i->linkedProject),
                ];
            }
            if ($i->linkedProperty) {
                $related[] = [
                    'label' => __('Property').': '.$i->linkedProperty->name,
                    'url' => route('families.properties.show', $i->linkedProperty),
                ];
            }

            return (object) [
                'description' => $i->source ?: 'Income',
                'category' => $i->category->name ?? 'Income',
                'amount' => $i->amount,
                'currency_code' => $i->currency_code,
                'date' => $i->received_date,
                'type' => 'income',
                'url' => route('families.incomes.show', $i),
                'related_links' => $related,
            ];
        })->concat($recentExpenses->map(function ($e) {
            $related = [];
            if ($e->project) {
                $related[] = [
                    'label' => __('Project').': '.$e->project->name,
                    'url' => route('families.projects.show', $e->project),
                ];
            }

            return (object) [
                'description' => $e->description ?: 'Expense',
                'category' => $e->category->name ?? 'Expense',
                'amount' => $e->amount,
                'currency_code' => $e->currency_code,
                'date' => $e->expense_date,
                'type' => 'expense',
                'url' => route('families.transactions.index', ['type' => 'expense']),
                'related_links' => $related,
            ];
        }))->sortByDesc('date')->take($take)->values();
    }

    /**
     * @return array{projectCount: int, activeProjectCount: int, propertyCount: int, propertyTotalValue: float}
     */
    public function projectAndPropertyStats(?Family $currentFamily): array
    {
        if (! $currentFamily) {
            return [
                'projectCount' => 0,
                'activeProjectCount' => 0,
                'propertyCount' => 0,
                'propertyTotalValue' => 0.0,
            ];
        }

        $projectCounts = Project::where('family_id', $currentFamily->id)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active', ['active'])
            ->first();

        $propertyTotalValue = (float) Property::where('family_id', $currentFamily->id)
            ->selectRaw('COALESCE(SUM(COALESCE(current_estimated_value, purchase_price, 0)), 0) as total')
            ->value('total');

        return [
            'projectCount' => (int) ($projectCounts->total ?? 0),
            'activeProjectCount' => (int) ($projectCounts->active ?? 0),
            'propertyCount' => Property::where('family_id', $currentFamily->id)->count(),
            'propertyTotalValue' => $propertyTotalValue,
        ];
    }

    /**
     * JSON payload for the mobile API dashboard.
     *
     * @return array<string, mixed>
     */
    public function apiDashboardPayload(User $user): array
    {
        $families = $user->families()->get();
        $familyIds = $families->pluck('id')->toArray();
        $currentFamily = $families->first();
        $currency = $currentFamily?->currency_code ?? config('currencies.default', 'TZS');

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $totalIncome = $this->totalIncomeBetween($familyIds, $startOfMonth, $endOfMonth);
        $totalExpenses = $this->totalExpensesBetween($familyIds, $startOfMonth, $endOfMonth);
        $totalSavings = $this->totalWalletBalance($familyIds);

        $chartMonthKeys = [];
        for ($i = 5; $i >= 0; $i--) {
            $chartMonthKeys[] = $now->copy()->subMonths($i)->format('Y-m-01');
        }

        $incomeByMonth = $this->incomeTotalsByMonthKeys($familyIds, $chartMonthKeys, $endOfMonth);
        $expenseByMonth = $this->expenseTotalsByMonthKeys($familyIds, $chartMonthKeys, $endOfMonth);

        $recentActivity = $this->mergeRecentActivityForApi($familyIds, 10);

        return [
            'currency' => $currency,
            'current_family' => $currentFamily ? [
                'id' => $currentFamily->id,
                'name' => $currentFamily->name,
                'currency_code' => $currentFamily->currency_code,
            ] : null,
            'total_income_this_month' => $totalIncome,
            'total_expenses_this_month' => $totalExpenses,
            'total_savings' => round($totalSavings, 2),
            'chart_months' => array_map(fn ($k) => Carbon::parse($k)->format('M'), $chartMonthKeys),
            'chart_income' => $incomeByMonth,
            'chart_expense' => $expenseByMonth,
            'recent_activity' => $recentActivity,
        ];
    }

    /**
     * View data for the web dashboard (Blade).
     *
     * @return array<string, mixed>
     */
    public function webDashboardPayload(User $user): array
    {
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

        $totalIncome = $this->totalIncomeBetween($familyIds, $startOfMonth, $endOfMonth);
        $totalExpenses = $this->totalExpensesBetween($familyIds, $startOfMonth, $endOfMonth);
        $totalSavings = $this->totalWalletBalance($familyIds);

        $budget = $currentFamily
            ? $this->budgetSummaryForMonth($currentFamily, $startOfMonth, $endOfMonth)
            : ['percent' => 0, 'label' => '—'];

        $months = [];
        $chartMonthKeys = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i)->startOfMonth();
            $months[] = $d->format('M');
            $chartMonthKeys[] = $d->format('Y-m-01');
        }

        $incomeByMonth = $this->incomeTotalsByMonthKeys($familyIds, $chartMonthKeys, $endOfMonth);
        $expenseByMonth = $this->expenseTotalsByMonthKeys($familyIds, $chartMonthKeys, $endOfMonth);

        $expensesByCategory = $currentFamily
            ? $this->expensesByCategoryForMonth($familyIds, $startOfMonth, $endOfMonth, 8)
            : collect();

        $recentActivity = $this->mergeRecentActivityForWeb($familyIds, 15, 10);

        $stats = $this->projectAndPropertyStats($currentFamily);

        return [
            'families' => $families,
            'currentFamily' => $currentFamily,
            'currency' => $currency,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'totalSavings' => $totalSavings,
            'budgetUsedPercent' => $budget['percent'],
            'budgetUsedLabel' => $budget['label'],
            'chartMonths' => $months,
            'chartIncome' => $incomeByMonth,
            'chartExpense' => $expenseByMonth,
            'expensesByCategory' => $expensesByCategory,
            'recentActivity' => $recentActivity,
            'projectCount' => $stats['projectCount'],
            'activeProjectCount' => $stats['activeProjectCount'],
            'propertyCount' => $stats['propertyCount'],
            'propertyTotalValue' => $stats['propertyTotalValue'],
        ];
    }

    /**
     * Get a simple summary mapping for the family.
     */
    public function getFamilySummary(int $familyId): object
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return (object) [
            'total_income' => $this->totalIncomeBetween([$familyId], $startOfMonth, $endOfMonth),
            'total_expenses' => $this->totalExpensesBetween([$familyId], $startOfMonth, $endOfMonth),
            'total_savings' => $this->totalWalletBalance([$familyId]), // Total balances conceptually
        ];
    }

    /**
     * Get health index for the family profile.
     */
    public function getFamilyHealthIndex(int $familyId): array
    {
        $summary = $this->getFamilySummary($familyId);
        $savingsRate = ($summary->total_income > 0) ? ($summary->total_savings / $summary->total_income) * 100 : 0;

        if ($summary->total_expenses > $summary->total_income) {
            return ['emoji' => '😟', 'text' => 'Hatarini', 'description' => 'Matumizi yamezidi mapato, tafuta njia ya kupunguza gharama.'];
        }
        if ($savingsRate > 20) {
            return ['emoji' => '😊', 'text' => 'Safi Sana', 'description' => 'Akiba inaongezeka kwa kasi, endelea hivyo!'];
        }

        return ['emoji' => '😐', 'text' => 'Inaridhisha', 'description' => 'Mapato na matumizi yanakaribiana, weka lengo la kuongeza akiba.'];
    }

    /**
     * Leaderboard for top contributors in the given family.
     */
    public function getContributionLeaderboard(int $familyId): array
    {
        $users = User::whereHas('families', function ($q) use ($familyId) {
            $q->where('family_id', $familyId);
        })
            ->withCount([
                'incomes as incomes_count' => function ($q) use ($familyId) {
                    $q->where('family_id', $familyId);
                },
                'expenses as expenses_count' => function ($q) use ($familyId) {
                    $q->where('family_id', $familyId);
                },
            ])
            ->get()
            ->map(function ($user) {
                $points = $user->incomes_count + $user->expenses_count;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar_url,
                    'points' => $points,
                    'budget_status' => $this->getUserBudgetStatus($user),
                ];
            })
            ->sortByDesc('points')
            ->values()
            ->take(5);

        return $users->toArray();
    }

    protected function getUserBudgetStatus(User $user): string
    {
        return 'success';
    }
}
