<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\Family;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
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

        $query = $family->expenses()->with(['wallet:id,family_id,name,currency_code', 'category:id,name', 'paidBy:id,name', 'createdBy:id,name', 'budget:id,name,type']);
        if ($request->filled('wallet_id')) {
            $wallet = $family->wallets()->find($request->wallet_id);
            if ($wallet) {
                $query->where('wallet_id', $wallet->id);
            }
        }
        $totalExpenses = (float) (clone $query)->sum('amount');
        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->paginate(20);
        $wallets = $family->wallets()
            ->where('status', 'active')
            ->orderBy('name')
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->get(['id', 'name', 'currency_code', 'initial_balance']);

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        return view('families.expenses.index', compact('family', 'expenses', 'wallets', 'totalExpenses', 'currency'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get();
        if ($wallets->isEmpty()) {
            return redirect()
                ->route('families.wallets.index', $family)
                ->with('error', 'Create at least one wallet before recording expenses. Every expense must reduce a wallet.');
        }
        $categories = ExpenseCategory::defaults();
        $members = $family->members()->orderBy('name')->get(['users.id', 'users.name']);
        $projects = $family->projects()->whereIn('status', ['planning', 'active'])->orderBy('name')->get(['id', 'name']);
        $budgets = $family->budgets()
            ->where('status', 'active')
            ->where('type', '!=', Budget::TYPE_FAMILY)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'amount', 'currency_code']);

        return view('families.expenses.create', compact('family', 'wallets', 'categories', 'members', 'projects', 'budgets'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallet = $family->wallets()->findOrFail($request->input('wallet_id'));

        $validated = $request->validate([
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($wallet->currency_code)])],
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
            'payment_method' => ['nullable', 'string', 'max:50', Rule::in(array_keys(\App\Models\Expense::paymentMethods()))],
            'reference' => ['nullable', 'string', 'max:100'],
            'is_recurring' => ['nullable', 'boolean'],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('family_id', $family->id)],
            'family_liability_id' => ['nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'budget_id' => ['nullable', Rule::exists('budgets', 'id')->where('family_id', $family->id)],
        ], [
            'wallet_id.required' => 'Please select a wallet. Every expense must reduce a wallet.',
            'amount.min' => 'Amount must be greater than zero.',
            'currency_code.in' => 'Currency must match the selected wallet.',
            'category_id.required' => 'Please choose a category for this expense.',
        ]);

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
            'paid_by' => $validated['paid_by'] ?? auth()->id(),
            'payment_method' => $validated['payment_method'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'is_recurring' => (bool) ($validated['is_recurring'] ?? false),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.expenses.index', $family)
            ->with('success', 'Expense recorded. Wallet balance updated.');
    }
}
