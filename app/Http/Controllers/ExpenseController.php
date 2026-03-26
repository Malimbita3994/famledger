<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\Family;
use App\Services\WalletBalanceGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    use AuthorizesFamilyMember;

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
        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->paginate(20);
        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);

        return view('families.expenses.index', compact('family', 'expenses', 'wallets'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get();
        if ($wallets->isEmpty()) {
            return redirect()
                ->route('families.wallets.index')
                ->with('error', 'Create at least one wallet before recording expenses. Every expense must reduce a wallet.');
        }
        $categories = ExpenseCategory::defaults();
        $members = $family->members()->orderBy('name')->get(['users.id', 'users.name']);
        $projects = $family->projects()->whereIn('status', ['planning', 'active'])->orderBy('name')->get(['id', 'name']);
        $budgets = $family->budgets()
            ->where('status', 'active')
            ->with(['wallets:id', 'categories:id'])
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'amount', 'currency_code', 'project_id']);

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

        // Keep Project <-> Budget source consistent for project budgets.
        if (! empty($validated['budget_id'])) {
            $budget = $family->budgets()->whereKey($validated['budget_id'])->first();
            if ($budget && $budget->type === Budget::TYPE_PROJECT && $budget->project_id) {
                $linkedProjectId = (int) $budget->project_id;
                $selectedProjectId = isset($validated['project_id']) && $validated['project_id'] !== null
                    ? (int) $validated['project_id']
                    : null;

                if ($selectedProjectId === null) {
                    $validated['project_id'] = $linkedProjectId;
                } elseif ($selectedProjectId !== $linkedProjectId) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'budget_id' => 'Selected budget is linked to a different project.',
                        ]);
                }
            }

            // If the chosen budget source is scope-limited (wallet/category), ensure
            // the expense points at something inside that scope. This makes
            // Budget "Used" figures update reliably.
            if ($budget) {
                if ($budget->type === Budget::TYPE_WALLET) {
                    $walletOk = $budget->wallets()->whereKey($validated['wallet_id'])->exists();
                    if (! $walletOk) {
                        return back()
                            ->withInput()
                            ->withErrors([
                                'budget_id' => 'Selected budget source is linked to different wallet(s).',
                            ]);
                    }
                }

                if ($budget->type === Budget::TYPE_CATEGORY) {
                    $categoryOk = $budget->categories()->whereKey($validated['category_id'])->exists();
                    if (! $categoryOk) {
                        return back()
                            ->withInput()
                            ->withErrors([
                                'budget_id' => 'Selected budget source is linked to different expense category/ies.',
                            ]);
                    }
                }
            }
        }

        return DB::transaction(function () use ($family, $validated) {
            $locked = WalletBalanceGuard::lockWalletsForUpdate([(int) $validated['wallet_id']]);
            $w = $locked->get((int) $validated['wallet_id']);
            if (! $w || $w->status !== 'active') {
                return back()
                    ->withInput()
                    ->withErrors(['wallet_id' => 'Selected wallet is missing or inactive.'])
                    ->with('error', 'Selected wallet is missing or inactive.');
            }

            if (! $w->canAffordDebit((float) $validated['amount'])) {
                $message = 'Insufficient funds in the selected wallet. Available balance is '
                    .number_format($w->balance, 2).' '.strtoupper($w->currency_code).'.';

                return back()->withInput()->withErrors(['amount' => $message])->with('error', $message);
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
                'paid_by' => $validated['paid_by'] ?? auth()->id(),
                'payment_method' => $validated['payment_method'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'is_recurring' => (bool) ($validated['is_recurring'] ?? false),
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('families.expenses.index')
                ->with('success', 'Expense recorded. Wallet balance updated.');
        });
    }
}
