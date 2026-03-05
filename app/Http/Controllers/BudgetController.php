<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\Family;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    public function index(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $budgets = $family->budgets()
            ->with(['wallets:id,name,currency_code', 'categories:id,name'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(15);

        $mainBudget = $family->budgets()
            ->where('type', Budget::TYPE_FAMILY)
            ->first();

        $subBudgetsTotal = $family->budgets()
            ->where('type', '!=', Budget::TYPE_FAMILY)
            ->sum('amount');

        $unplannedAmount = null;
        if ($mainBudget) {
            $unplannedAmount = max(0, (float) $mainBudget->amount - (float) $subBudgetsTotal);
        }

        return view('families.budgets.index', [
            'family' => $family,
            'budgets' => $budgets,
            'mainBudget' => $mainBudget,
            'unplannedAmount' => $unplannedAmount,
        ]);
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);
        $categories = ExpenseCategory::defaults();
        $currencies = config('currencies', []);
        if ($family->currency_code && ! isset($currencies[$family->currency_code])) {
            $currencies = [$family->currency_code => $family->currency_code] + $currencies;
        }

        return view('families.budgets.create', compact('family', 'wallets', 'categories', 'currencies'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Budget::types()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'recurrence' => ['nullable', Rule::in(array_keys(Budget::recurrences()))],
            'wallet_ids' => ['nullable', 'array'],
            'wallet_ids.*' => ['nullable', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['nullable', Rule::exists('expense_categories', 'id')],
        ];

        // Only require category when type = Expenses (category)
        if ($request->input('type') === Budget::TYPE_CATEGORY) {
            $rules['category_ids'] = ['required', 'array'];
            $rules['category_ids.*'] = ['required', Rule::exists('expense_categories', 'id')];
        }

        $validated = $request->validate($rules, [
            'amount.min' => 'Budget amount must be greater than zero.',
        ]);

        // If this is the very first budget for the family, force it to be the main family budget.
        if ($family->budgets()->count() === 0) {
            $validated['type'] = Budget::TYPE_FAMILY;
        }

        // Only one main family budget is allowed
        if ($validated['type'] === Budget::TYPE_FAMILY) {
            $existingMain = $family->budgets()
                ->where('type', Budget::TYPE_FAMILY)
                ->exists();
            if ($existingMain) {
                return back()
                    ->withInput()
                    ->withErrors(['type' => 'There is already a main family budget. Edit that budget instead of creating another one.'])
                    ->with('error', 'Only one main family budget is allowed per family.');
            }
        }

        if ($validated['type'] === Budget::TYPE_WALLET && empty(array_filter($validated['wallet_ids'] ?? []))) {
            return back()->withInput()->withErrors(['wallet_ids' => 'Select at least one wallet for a wallet budget.']);
        }
        if ($validated['type'] === Budget::TYPE_CATEGORY && empty(array_filter($validated['category_ids'] ?? []))) {
            return back()->withInput()->withErrors(['category_ids' => 'Select at least one category for a category budget.']);
        }

        // Enforce that all non-family budgets depend on the main family budget
        if ($validated['type'] !== Budget::TYPE_FAMILY) {
            $mainBudget = $family->budgets()
                ->where('type', Budget::TYPE_FAMILY)
                ->where('status', 'active')
                ->orderBy('start_date')
                ->first();

            if (! $mainBudget) {
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'Create a main family budget first. Other budgets must sit under the main budget.'])
                    ->with('error', 'Create a main family budget before adding wallet, category, or project budgets.');
            }

            $childAmount = (float) $validated['amount'];
            $existingChildrenTotal = (float) $family->budgets()
                ->where('type', '!=', Budget::TYPE_FAMILY)
                ->sum('amount');
            $newChildrenTotal = $existingChildrenTotal + $childAmount;

            if ($childAmount > (float) $mainBudget->amount) {
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'This budget amount cannot exceed the main family budget amount.'])
                    ->with('error', 'Each sub-budget must be smaller than the main family budget.');
            }

            if ($newChildrenTotal > (float) $mainBudget->amount) {
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'Total of all sub-budgets would exceed the main family budget. Reduce this amount or adjust other budgets.'])
                    ->with('error', 'All sub-budgets together must be less than or equal to the main family budget.');
            }
        }

        $budget = $family->budgets()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'recurrence' => $validated['recurrence'] ?? 'none',
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        if ($budget->type === Budget::TYPE_WALLET && ! empty($validated['wallet_ids'])) {
            $budget->wallets()->sync($validated['wallet_ids']);
        }
        if ($budget->type === Budget::TYPE_CATEGORY && ! empty($validated['category_ids'])) {
            $budget->categories()->sync($validated['category_ids']);
        }

        return redirect()
            ->route('families.budgets.show', [$family, $budget])
            ->with('success', 'Budget created. You can now track spending against it.');
    }

    public function show(Family $family, Budget $budget)
    {
        $this->authorizeFamilyMember($family);
        if ($budget->family_id !== $family->id) {
            abort(404);
        }

        $budget->load(['wallets:id,name,currency_code', 'categories:id,name', 'createdBy:id,name']);

        return view('families.budgets.show', compact('family', 'budget'));
    }

    public function edit(Family $family, Budget $budget)
    {
        $this->authorizeFamilyMember($family);
        if ($budget->family_id !== $family->id) {
            abort(404);
        }

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);
        $categories = ExpenseCategory::defaults();
        $currencies = config('currencies', []);
        if ($family->currency_code && ! isset($currencies[$family->currency_code])) {
            $currencies = [$family->currency_code => $family->currency_code] + $currencies;
        }
        $budget->load(['wallets', 'categories']);

        return view('families.budgets.edit', compact('family', 'budget', 'wallets', 'categories', 'currencies'));
    }

    public function update(Request $request, Family $family, Budget $budget)
    {
        $this->authorizeFamilyMember($family);
        if ($budget->family_id !== $family->id) {
            abort(404);
        }

        $updateRules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Budget::types()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'recurrence' => ['nullable', Rule::in(array_keys(Budget::recurrences()))],
            'status' => ['nullable', Rule::in(['active', 'archived'])],
            'wallet_ids' => ['nullable', 'array'],
            'wallet_ids.*' => ['nullable', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['nullable', Rule::exists('expense_categories', 'id')],
        ];

        if ($request->input('type') === Budget::TYPE_CATEGORY) {
            $updateRules['category_ids'] = ['required', 'array'];
            $updateRules['category_ids.*'] = ['required', Rule::exists('expense_categories', 'id')];
        }

        $validated = $request->validate($updateRules);

        // Only one main family budget is allowed
        if ($validated['type'] === Budget::TYPE_FAMILY) {
            $otherMain = $family->budgets()
                ->where('id', '!=', $budget->id)
                ->where('type', Budget::TYPE_FAMILY)
                ->exists();
            if ($otherMain) {
                return back()
                    ->withInput()
                    ->withErrors(['type' => 'There is already a main family budget for this family.'])
                    ->with('error', 'Only one main family budget is allowed per family.');
            }
        }

        if ($validated['type'] === Budget::TYPE_WALLET && empty(array_filter($validated['wallet_ids'] ?? []))) {
            return back()->withInput()->withErrors(['wallet_ids' => 'Select at least one wallet for a wallet budget.']);
        }
        if ($validated['type'] === Budget::TYPE_CATEGORY && empty(array_filter($validated['category_ids'] ?? []))) {
            return back()->withInput()->withErrors(['category_ids' => 'Select at least one category for a category budget.']);
        }

        // Enforce main vs sub-budget relationship on update
        $newAmount = (float) $validated['amount'];

        if ($validated['type'] === Budget::TYPE_FAMILY) {
            // If this is (or becomes) the main family budget, make sure all other budgets fit under it
            $otherBudgetsTotal = (float) $family->budgets()
                ->where('id', '!=', $budget->id)
                ->where('type', '!=', Budget::TYPE_FAMILY)
                ->sum('amount');

            if ($otherBudgetsTotal > $newAmount) {
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'Main family budget is too small. Its amount must be at least the total of all other budgets.'])
                    ->with('error', 'Increase the main family budget amount or reduce other budgets so they fit under it.');
            }
        } else {
            // Updating a sub-budget: keep it smaller than main and keep total <= main
            $mainBudget = $family->budgets()
                ->where('type', Budget::TYPE_FAMILY)
                ->where('status', 'active')
                ->orderBy('start_date')
                ->first();

            if (! $mainBudget) {
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'Create a main family budget first. Other budgets must sit under the main budget.'])
                    ->with('error', 'Create a main family budget before adjusting wallet, category, or project budgets.');
            }

            $existingChildrenTotal = (float) $family->budgets()
                ->where('id', '!=', $budget->id)
                ->where('type', '!=', Budget::TYPE_FAMILY)
                ->sum('amount');
            $newChildrenTotal = $existingChildrenTotal + $newAmount;

            if ($newAmount > (float) $mainBudget->amount) {
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'This budget amount cannot exceed the main family budget amount.'])
                    ->with('error', 'Each sub-budget must be smaller than the main family budget.');
            }

            if ($newChildrenTotal > (float) $mainBudget->amount) {
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'Total of all sub-budgets would exceed the main family budget. Reduce this amount or adjust other budgets.'])
                    ->with('error', 'All sub-budgets together must be less than or equal to the main family budget.');
            }
        }

        $budget->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'recurrence' => $validated['recurrence'] ?? 'none',
            'status' => $validated['status'] ?? $budget->status,
        ]);

        if ($budget->type === Budget::TYPE_WALLET) {
            $budget->wallets()->sync($validated['wallet_ids'] ?? []);
        } else {
            $budget->wallets()->detach();
        }
        if ($budget->type === Budget::TYPE_CATEGORY) {
            $budget->categories()->sync($validated['category_ids'] ?? []);
        } else {
            $budget->categories()->detach();
        }

        return redirect()
            ->route('families.budgets.show', [$family, $budget])
            ->with('success', 'Budget updated.');
    }

    public function destroy(Family $family, Budget $budget)
    {
        $this->authorizeFamilyMember($family);
        if ($budget->family_id !== $family->id) {
            abort(404);
        }

        $budget->wallets()->detach();
        $budget->categories()->detach();
        $budget->delete();

        return redirect()
            ->route('families.budgets.index', $family)
            ->with('success', 'Budget removed.');
    }
}
