<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Family;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $budgets = $family->budgets()
            ->with(['wallets:id,name,currency_code', 'categories:id,name'])
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'budgets' => $budgets->map(fn (Budget $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'type' => $b->type,
                'amount' => (float) $b->amount,
                'currency_code' => $b->currency_code,
                'start_date' => $b->start_date?->format('Y-m-d'),
                'end_date' => $b->end_date?->format('Y-m-d'),
                'status' => $b->status,
                'wallet' => $b->wallets->first() ? ['id' => $b->wallets->first()->id, 'name' => $b->wallets->first()->name] : null,
                'category' => $b->categories->first() ? ['id' => $b->categories->first()->id, 'name' => $b->categories->first()->name] : null,
            ]),
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $budget = $family->budgets()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency_code' => $validated['currency_code'] ?? $family->currency_code ?? config('currencies.default', 'TZS'),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Budget created.',
            'budget' => ['id' => $budget->id],
        ], 201);
    }

    public function update(Request $request, Family $family, Budget $budget): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($budget->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $budget->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency_code' => $validated['currency_code'] ?? $budget->currency_code,
            'start_date' => $validated['start_date'] ?? $budget->start_date,
            'end_date' => $validated['end_date'] ?? $budget->end_date,
            'status' => $validated['status'] ?? $budget->status,
        ]);

        return response()->json([
            'message' => 'Budget updated.',
        ]);
    }

    public function destroy(Family $family, Budget $budget): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($budget->family_id !== $family->id) {
            abort(404);
        }

        $budget->delete();

        return response()->json([
            'message' => 'Budget deleted.',
        ]);
    }
}
