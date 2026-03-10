<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Family;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = $family->expenses()
            ->with(['wallet:id,name,currency_code', 'category:id,name'])
            ->orderByDesc('expense_date');
        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', $request->wallet_id);
        }
        $perPage = min((int) $request->get('per_page', 20), 50);
        $expenses = $query->paginate($perPage);

        return response()->json([
            'expenses' => $expenses->getCollection()->map(fn (Expense $e) => [
                'id' => $e->id,
                'amount' => (float) $e->amount,
                'currency_code' => $e->currency_code,
                'description' => $e->description,
                'expense_date' => $e->expense_date?->format('Y-m-d'),
                'is_recurring' => (bool) $e->is_recurring,
                'merchant' => $e->merchant,
                'payment_method' => $e->payment_method,
                'reference' => $e->reference,
                'budget_id' => $e->budget_id,
                'project_id' => $e->project_id,
                'family_liability_id' => $e->family_liability_id,
                'paid_by' => $e->paid_by,
                'wallet' => $e->wallet ? ['id' => $e->wallet->id, 'name' => $e->wallet->name] : null,
                'category' => $e->category ? ['id' => $e->category->id, 'name' => $e->category->name] : null,
            ]),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $walletId = $request->input('wallet_id');
        $wallet = $family->wallets()->find($walletId);
        if (! $wallet) {
            return response()->json(['message' => 'Invalid wallet.'], 422);
        }

        $validated = $request->validate([
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($wallet->currency_code)])],
            'category_id' => ['required', Rule::exists('expense_categories', 'id')],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'merchant' => ['nullable', 'string', 'max:255'],
            'paid_by' => ['nullable', Rule::in($family->members()->pluck('users.id')->toArray())],
            'payment_method' => ['nullable', 'string', 'max:50', Rule::in(array_keys(Expense::paymentMethods()))],
            'reference' => ['nullable', 'string', 'max:100'],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('family_id', $family->id)],
            'budget_id' => ['nullable', Rule::exists('budgets', 'id')->where('family_id', $family->id)],
            'family_liability_id' => ['nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'is_recurring' => ['nullable', 'boolean'],
        ]);

        $expense = $family->expenses()->create([
            'wallet_id' => $validated['wallet_id'],
            'category_id' => $validated['category_id'],
            'subcategory' => $validated['subcategory'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'budget_id' => $validated['budget_id'] ?? null,
            'family_liability_id' => $validated['family_liability_id'] ?? null,
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'expense_date' => $validated['expense_date'],
            'description' => $validated['description'] ?? null,
            'merchant' => $validated['merchant'] ?? null,
            'paid_by' => $validated['paid_by'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'is_recurring' => (bool) ($validated['is_recurring'] ?? false),
            'created_by' => auth()->id(),
        ]);

        $expense->load(['wallet:id,name,currency_code', 'category:id,name']);

        return response()->json([
            'message' => 'Expense recorded.',
            'expense' => [
                'id' => $expense->id,
                'amount' => (float) $expense->amount,
                'currency_code' => $expense->currency_code,
                'description' => $expense->description,
                'expense_date' => $expense->expense_date?->format('Y-m-d'),
                'is_recurring' => (bool) $expense->is_recurring,
                'wallet' => ['id' => $expense->wallet->id, 'name' => $expense->wallet->name],
                'category' => ['id' => $expense->category->id, 'name' => $expense->category->name],
            ],
        ], 201);
    }

    /**
     * Update an existing expense.
     * We allow editing metadata (category, description, dates, links) but not amount or wallet.
     */
    public function update(Request $request, Family $family, Expense $expense): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($expense->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'nullable', Rule::exists('expense_categories', 'id')],
            'subcategory' => ['sometimes', 'nullable', 'string', 'max:100'],
            'expense_date' => ['sometimes', 'date'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'merchant' => ['sometimes', 'nullable', 'string', 'max:255'],
            'paid_by' => ['sometimes', 'nullable', Rule::in($family->members()->pluck('users.id')->toArray())],
            'payment_method' => ['sometimes', 'nullable', 'string', 'max:50', Rule::in(array_keys(Expense::paymentMethods()))],
            'reference' => ['sometimes', 'nullable', 'string', 'max:100'],
            'project_id' => ['sometimes', 'nullable', Rule::exists('projects', 'id')->where('family_id', $family->id)],
            'budget_id' => ['sometimes', 'nullable', Rule::exists('budgets', 'id')->where('family_id', $family->id)],
            'family_liability_id' => ['sometimes', 'nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'is_recurring' => ['sometimes', 'nullable', 'boolean'],
        ]);

        $expense->fill($validated);
        $expense->save();

        $expense->load(['wallet:id,name,currency_code', 'category:id,name']);

        return response()->json([
            'message' => 'Expense updated.',
            'expense' => [
                'id' => $expense->id,
                'amount' => (float) $expense->amount,
                'currency_code' => $expense->currency_code,
                'description' => $expense->description,
                'expense_date' => $expense->expense_date?->format('Y-m-d'),
                'is_recurring' => (bool) $expense->is_recurring,
                'wallet' => $expense->wallet ? ['id' => $expense->wallet->id, 'name' => $expense->wallet->name] : null,
                'category' => $expense->category ? ['id' => $expense->category->id, 'name' => $expense->category->name] : null,
            ],
        ]);
    }

    public function destroy(Family $family, Expense $expense): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($expense->family_id !== $family->id) {
            abort(404);
        }

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted.',
        ]);
    }
}
