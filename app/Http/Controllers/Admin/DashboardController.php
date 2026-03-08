<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Family;
use App\Models\Income;
use App\Models\SavingsGoal;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currency = config('currencies.default', 'TZS');
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfDay = $now->copy()->startOfDay();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        // ---- 1) Families overview ----
        $totalFamilies = Family::count();
        $activeFamilies = Family::where('status', 'active')->count();
        $newFamiliesThisMonth = Family::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $familyIds = Family::pluck('id')->toArray();
        $inactiveFamilies = Family::whereDoesntHave('incomes', fn ($q) => $q->where('received_date', '>=', $thirtyDaysAgo))
            ->whereDoesntHave('expenses', fn ($q) => $q->where('expense_date', '>=', $thirtyDaysAgo))
            ->count();

        // ---- 2) Users overview ----
        $totalUsers = User::count();
        $activeUsers = (int) User::where('status', 'active')->count();
        $newUsersThisMonth = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $suspendedUsers = (int) User::whereIn('status', ['suspended', 'locked'])->count();
        $totalMemberships = (int) DB::table('family_user')->count();
        $usersPerFamily = $totalFamilies > 0 ? round($totalMemberships / $totalFamilies, 1) : 0;

        // ---- 3) Financial activity (aggregated) ----
        $totalIncomePlatform = (float) Income::sum('amount');
        $totalExpensesPlatform = (float) Expense::sum('amount');
        $netFlow = $totalIncomePlatform - $totalExpensesPlatform;
        $incomeThisMonth = (float) Income::whereBetween('received_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $expensesThisMonth = (float) Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount');

        $wallets = Wallet::all();
        $totalWalletBalance = $wallets->sum(fn ($w) => $w->balance);

        // ---- 4) Wallet statistics ----
        $totalWallets = Wallet::count();
        $avgWalletsPerFamily = $totalFamilies > 0 ? round($totalWallets / $totalFamilies, 1) : 0;
        $activeWallets = $totalWallets; // consider active if exists; could refine by recent activity
        $dormantWallets = 0; // placeholder: wallets with no transaction in 90 days

        // ---- 5) Transaction activity ----
        $totalIncomeTransactions = Income::count();
        $totalExpenseTransactions = Expense::count();
        $totalTransfers = Transfer::count();
        $transactionsToday = Income::where('received_date', $now->toDateString())->count()
            + Expense::where('expense_date', $now->toDateString())->count()
            + Transfer::whereDate('transfer_date', $now->toDateString())->count();
        $transactionsThisMonth = Income::whereBetween('received_date', [$startOfMonth, $endOfMonth])->count()
            + Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->count()
            + Transfer::whereBetween('transfer_date', [$startOfMonth, $endOfMonth])->count();

        // ---- 6) Budget statistics ----
        $activeBudgets = Budget::where('start_date', '<=', $endOfMonth)->where('end_date', '>=', $startOfMonth)->count();
        $overBudgetCount = Budget::where('start_date', '<=', $endOfMonth)->where('end_date', '>=', $startOfMonth)->with(['family', 'wallets', 'categories'])->get()->filter(fn ($b) => $b->is_exceeded)->count();
        $avgBudgetAmount = (float) Budget::avg('amount');

        // ---- 7) Savings goals ----
        $totalSavingsGoals = SavingsGoal::count();
        $completedGoals = SavingsGoal::where('status', 'completed')->count();
        $overdueGoals = SavingsGoal::where('status', 'overdue')->count();
        $totalSavingsAccumulated = (float) DB::table('savings_contributions')->sum('amount');

        // ---- 8) Growth trends (last 6 months) ----
        $userGrowthMonths = [];
        $userGrowthData = [];
        $familyGrowthData = [];
        $txnGrowthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $userGrowthMonths[] = $d->format('M Y');
            $end = $d->copy()->endOfMonth();
            $userGrowthData[] = User::where('created_at', '<=', $end)->count();
            $familyGrowthData[] = Family::where('created_at', '<=', $end)->count();
            $start = $d->copy()->startOfMonth();
            $txnGrowthData[] = Income::whereBetween('received_date', [$start, $end])->count()
                + Expense::whereBetween('expense_date', [$start, $end])->count()
                + Transfer::whereBetween('transfer_date', [$start, $end])->count();
        }

        // ---- 9) Recent events (placeholder: recent families, users) ----
        $recentFamilies = Family::latest()->take(5)->get(['id', 'name', 'created_at']);
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']);

        return view('admin.dashboard', [
            'currency' => $currency,
            'totalFamilies' => $totalFamilies,
            'activeFamilies' => $activeFamilies,
            'newFamiliesThisMonth' => $newFamiliesThisMonth,
            'inactiveFamilies' => $inactiveFamilies,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'newUsersThisMonth' => $newUsersThisMonth,
            'suspendedUsers' => $suspendedUsers,
            'usersPerFamily' => $usersPerFamily,
            'totalIncomePlatform' => $totalIncomePlatform,
            'totalExpensesPlatform' => $totalExpensesPlatform,
            'netFlow' => $netFlow,
            'incomeThisMonth' => $incomeThisMonth,
            'expensesThisMonth' => $expensesThisMonth,
            'totalWalletBalance' => $totalWalletBalance,
            'totalWallets' => $totalWallets,
            'avgWalletsPerFamily' => $avgWalletsPerFamily,
            'activeWallets' => $activeWallets,
            'totalIncomeTransactions' => $totalIncomeTransactions,
            'totalExpenseTransactions' => $totalExpenseTransactions,
            'totalTransfers' => $totalTransfers,
            'transactionsToday' => $transactionsToday,
            'transactionsThisMonth' => $transactionsThisMonth,
            'activeBudgets' => $activeBudgets,
            'overBudgetCount' => $overBudgetCount,
            'avgBudgetAmount' => $avgBudgetAmount,
            'totalSavingsGoals' => $totalSavingsGoals,
            'completedGoals' => $completedGoals,
            'overdueGoals' => $overdueGoals,
            'totalSavingsAccumulated' => $totalSavingsAccumulated,
            'chartMonths' => $userGrowthMonths,
            'userGrowthData' => $userGrowthData,
            'familyGrowthData' => $familyGrowthData,
            'txnGrowthData' => $txnGrowthData,
            'recentFamilies' => $recentFamilies,
            'recentUsers' => $recentUsers,
        ]);
    }
}
