<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                DB::raw("COALESCE(expenses.description, expenses.merchant) as description"),
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
            ->paginate(20)
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

        // Transaction count (for subtitle)
        $transactionCount = DB::table('incomes')->where('family_id', $family->id)->when($walletId, fn ($q) => $q->where('wallet_id', $walletId))->count()
            + DB::table('expenses')->where('family_id', $family->id)->when($walletId, fn ($q) => $q->where('wallet_id', $walletId))->count();

        return view('families.transactions.index', compact(
            'family', 'transactions', 'wallets', 'type',
            'totalIncome', 'totalExpenses', 'balance', 'transactionCount'
        ));
    }
}

