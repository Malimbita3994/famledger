<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $families = $user->families()->get();
        $familyIds = $families->pluck('id')->toArray();
        $currentFamily = $families->first();
        $currency = $currentFamily?->currency_code ?? config('currencies.default', 'TZS');

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $totalIncome = Income::whereIn('family_id', $familyIds)
            ->whereBetween('received_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalExpenses = Expense::whereIn('family_id', $familyIds)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $wallets = Wallet::whereIn('family_id', $familyIds)
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->get();
        $totalSavings = $wallets->sum(fn ($w) => (float) $w->initial_balance
            + (float) ($w->incomes_sum_amount ?? 0)
            - (float) ($w->expenses_sum_amount ?? 0)
            + (float) ($w->incoming_transfers_sum_amount ?? 0)
            - (float) ($w->outgoing_transfers_sum_amount ?? 0));

        $chartMonthKeys = [];
        for ($i = 5; $i >= 0; $i--) {
            $chartMonthKeys[] = $now->copy()->subMonths($i)->format('Y-m-01');
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

        $recentIncomes = Income::whereIn('family_id', $familyIds)
            ->with('category:id,name')
            ->orderByDesc('received_date')
            ->limit(5)
            ->get(['id', 'amount', 'currency_code', 'source', 'received_date', 'category_id']);
        $recentExpenses = Expense::whereIn('family_id', $familyIds)
            ->with('category:id,name')
            ->orderByDesc('expense_date')
            ->limit(5)
            ->get(['id', 'amount', 'currency_code', 'description', 'expense_date', 'category_id']);
        $recentActivity = $recentIncomes->map(fn ($i) => [
            'id' => $i->id,
            'type' => 'income',
            'description' => $i->source ?: 'Income',
            'category' => $i->category->name ?? 'Income',
            'amount' => (float) $i->amount,
            'currency_code' => $i->currency_code,
            'date' => $i->received_date?->format('Y-m-d'),
        ])->concat($recentExpenses->map(fn ($e) => [
            'id' => $e->id,
            'type' => 'expense',
            'description' => $e->description ?: 'Expense',
            'category' => $e->category->name ?? 'Expense',
            'amount' => (float) $e->amount,
            'currency_code' => $e->currency_code,
            'date' => $e->expense_date?->format('Y-m-d'),
        ]))->sortByDesc('date')->take(10)->values();

        return response()->json([
            'currency' => $currency,
            'current_family' => $currentFamily ? [
                'id' => $currentFamily->id,
                'name' => $currentFamily->name,
                'currency_code' => $currentFamily->currency_code,
            ] : null,
            'total_income_this_month' => (float) $totalIncome,
            'total_expenses_this_month' => (float) $totalExpenses,
            'total_savings' => round($totalSavings, 2),
            'chart_months' => array_map(fn ($k) => Carbon::parse($k)->format('M'), $chartMonthKeys),
            'chart_income' => $incomeByMonth,
            'chart_expense' => $expenseByMonth,
            'recent_activity' => $recentActivity,
        ]);
    }
}
