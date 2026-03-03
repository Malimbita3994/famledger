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

        return view('families.budgets.index', compact('family', 'budgets'));
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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Budget::types()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'recurrence' => ['nullable', Rule::in(array_keys(Budget::recurrences()))],
            'wallet_ids' => ['nullable', 'array'],
            'wallet_ids.*' => [Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [Rule::exists('expense_categories', 'id')],
        ], [
            'amount.min' => 'Budget amount must be greater than zero.',
        ]);

        if ($validated['type'] === Budget::TYPE_WALLET && empty(array_filter($validated['wallet_ids'] ?? []))) {
            return back()->withInput()->withErrors(['wallet_ids' => 'Select at least one wallet for a wallet budget.']);
        }
        if ($validated['type'] === Budget::TYPE_CATEGORY && empty(array_filter($validated['category_ids'] ?? []))) {
            return back()->withInput()->withErrors(['category_ids' => 'Select at least one category for a category budget.']);
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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Budget::types()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'recurrence' => ['nullable', Rule::in(array_keys(Budget::recurrences()))],
            'status' => ['nullable', Rule::in(['active', 'archived'])],
            'wallet_ids' => ['nullable', 'array'],
            'wallet_ids.*' => [Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [Rule::exists('expense_categories', 'id')],
        ]);

        if ($validated['type'] === Budget::TYPE_WALLET && empty(array_filter($validated['wallet_ids'] ?? []))) {
            return back()->withInput()->withErrors(['wallet_ids' => 'Select at least one wallet for a wallet budget.']);
        }
        if ($validated['type'] === Budget::TYPE_CATEGORY && empty(array_filter($validated['category_ids'] ?? []))) {
            return back()->withInput()->withErrors(['category_ids' => 'Select at least one category for a category budget.']);
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
