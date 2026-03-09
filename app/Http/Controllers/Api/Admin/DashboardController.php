<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Family;
use App\Models\Income;
use App\Models\SavingsGoal;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use AuthorizesAdmin;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $currency = config('currencies.default', 'TZS');
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $ninetyDaysAgo = $now->copy()->subDays(90);
        [$fyStart, $fyEnd] = FinancialYear::range();

        $totalFamilies = Family::count();
        $activeFamilies = Family::where('status', 'active')->count();
        $newFamiliesThisMonth = Family::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        $totalUsers = User::count();
        $activeUsers = (int) User::where('status', 'active')->count();
        $newUsersThisMonth = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $suspendedUsers = (int) User::whereIn('status', ['suspended', 'locked'])->count();
        $totalMemberships = (int) DB::table('family_user')->count();
        $usersPerFamily = $totalFamilies > 0 ? round($totalMemberships / $totalFamilies, 1) : 0;

        $totalIncomePlatform = (float) Income::sum('amount');
        $totalExpensesPlatform = (float) Expense::sum('amount');
        $netFlow = $totalIncomePlatform - $totalExpensesPlatform;
        $incomeThisFY = (float) Income::whereBetween('received_date', [$fyStart->toDateString(), $fyEnd->toDateString()])->sum('amount');
        $expensesThisFY = (float) Expense::whereBetween('expense_date', [$fyStart->toDateString(), $fyEnd->toDateString()])->sum('amount');
        $incomeThisMonth = (float) Income::whereBetween('received_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $expensesThisMonth = (float) Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount');

        $wallets = Wallet::all();
        $totalWalletBalance = $wallets->sum(fn ($w) => $w->balance);
        $totalWallets = Wallet::count();
        $activeWallets = (int) Wallet::where('status', 'active')->count();

        $totalIncomeTransactions = Income::count();
        $totalExpenseTransactions = Expense::count();
        $totalTransfers = Transfer::count();
        $transactionsThisMonth = Income::whereBetween('received_date', [$startOfMonth, $endOfMonth])->count()
            + Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->count()
            + Transfer::whereBetween('transfer_date', [$startOfMonth, $endOfMonth])->count();

        $activeBudgets = Budget::where('start_date', '<=', $endOfMonth)->where('end_date', '>=', $startOfMonth)->count();
        $totalSavingsGoals = SavingsGoal::count();
        $completedGoals = SavingsGoal::where('status', 'completed')->count();

        $recentFamilies = Family::latest()->take(5)->get(['id', 'name', 'created_at'])
            ->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'created_at' => $f->created_at?->toIso8601String()]);
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'created_at'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'created_at' => $u->created_at?->toIso8601String()]);

        return response()->json([
            'currency' => $currency,
            'financial_year' => FinancialYear::label(),
            'families' => [
                'total' => $totalFamilies,
                'active' => $activeFamilies,
                'new_this_month' => $newFamiliesThisMonth,
            ],
            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'new_this_month' => $newUsersThisMonth,
                'suspended' => $suspendedUsers,
                'users_per_family' => $usersPerFamily,
            ],
            'financial' => [
                'total_income' => $totalIncomePlatform,
                'total_expenses' => $totalExpensesPlatform,
                'net_flow' => $netFlow,
                'income_this_fy' => $incomeThisFY,
                'expenses_this_fy' => $expensesThisFY,
                'income_this_month' => $incomeThisMonth,
                'expenses_this_month' => $expensesThisMonth,
                'total_wallet_balance' => $totalWalletBalance,
            ],
            'wallets' => [
                'total' => $totalWallets,
                'active' => $activeWallets,
            ],
            'transactions' => [
                'total_income' => $totalIncomeTransactions,
                'total_expense' => $totalExpenseTransactions,
                'total_transfers' => $totalTransfers,
                'this_month' => $transactionsThisMonth,
            ],
            'budgets' => ['active' => $activeBudgets],
            'savings_goals' => [
                'total' => $totalSavingsGoals,
                'completed' => $completedGoals,
            ],
            'recent_families' => $recentFamilies,
            'recent_users' => $recentUsers,
        ]);
    }
}
