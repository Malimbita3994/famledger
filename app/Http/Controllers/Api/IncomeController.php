<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = $family->incomes()
            ->with(['wallet:id,name,currency_code', 'category:id,name'])
            ->orderByDesc('received_date');
        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', $request->wallet_id);
        }
        $perPage = min((int) $request->get('per_page', 20), 50);
        $incomes = $query->paginate($perPage);

        return response()->json([
            'incomes' => $incomes->getCollection()->map(fn ($i) => [
                'id' => $i->id,
                'amount' => (float) $i->amount,
                'currency_code' => $i->currency_code,
                'source' => $i->source,
                'received_date' => $i->received_date?->format('Y-m-d'),
                'notes' => $i->notes,
                'wallet' => $i->wallet ? ['id' => $i->wallet->id, 'name' => $i->wallet->name] : null,
                'category' => $i->category ? ['id' => $i->category->id, 'name' => $i->category->name] : null,
            ]),
            'meta' => [
                'current_page' => $incomes->currentPage(),
                'last_page' => $incomes->lastPage(),
                'per_page' => $incomes->perPage(),
                'total' => $incomes->total(),
            ],
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $wallet = $family->mainWallet();
        if (! $wallet || $wallet->status !== 'active') {
            return response()->json([
                'message' => 'Main wallet is missing or inactive. Set up an active main wallet first.',
            ], 422);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($wallet->currency_code)])],
            'category_id' => ['required', Rule::exists('income_categories', 'id')],
            'family_liability_id' => ['nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'source' => ['nullable', 'string', 'max:255'],
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $income = $family->incomes()->create([
            'wallet_id' => $wallet->id,
            'category_id' => $validated['category_id'],
            'family_liability_id' => $validated['family_liability_id'] ?? null,
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'source' => $validated['source'] ?? null,
            'received_date' => $validated['received_date'],
            'notes' => $validated['notes'] ?? null,
            'received_by' => auth()->id(),
            'created_by' => auth()->id(),
        ]);

        $income->load(['wallet:id,name,currency_code', 'category:id,name']);

        return response()->json([
            'message' => 'Income recorded.',
            'income' => [
                'id' => $income->id,
                'amount' => (float) $income->amount,
                'currency_code' => $income->currency_code,
                'source' => $income->source,
                'received_date' => $income->received_date?->format('Y-m-d'),
                'wallet' => ['id' => $income->wallet->id, 'name' => $income->wallet->name],
                'category' => ['id' => $income->category->id, 'name' => $income->category->name],
                'notes' => $income->notes,
            ],
        ], 201);
    }

    /**
     * Update an existing income.
     * For now we allow editing metadata (category, notes, source, dates) but not amount or wallet.
     */
    public function update(Request $request, Family $family, Income $income): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($income->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'nullable', Rule::exists('income_categories', 'id')],
            'family_liability_id' => ['sometimes', 'nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'received_date' => ['sometimes', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $income->fill($validated);
        $income->save();

        $income->load(['wallet:id,name,currency_code', 'category:id,name']);

        return response()->json([
            'message' => 'Income updated.',
            'income' => [
                'id' => $income->id,
                'amount' => (float) $income->amount,
                'currency_code' => $income->currency_code,
                'source' => $income->source,
                'received_date' => $income->received_date?->format('Y-m-d'),
                'wallet' => ['id' => $income->wallet->id, 'name' => $income->wallet->name],
                'category' => $income->category ? ['id' => $income->category->id, 'name' => $income->category->name] : null,
                'notes' => $income->notes,
            ],
        ]);
    }

    public function destroy(Family $family, Income $income): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($income->family_id !== $family->id) {
            abort(404);
        }

        $income->delete();

        return response()->json([
            'message' => 'Income deleted.',
        ]);
    }
}
