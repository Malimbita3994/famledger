<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\SavingsGoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $goals = $family->savingsGoals()
            ->with('wallet:id,name,currency_code')
            ->withSum('contributions', 'amount')
            ->orderByDesc('target_date')
            ->get();

        return response()->json([
            'savings_goals' => $goals->map(fn (SavingsGoal $g) => [
                'id' => $g->id,
                'name' => $g->name,
                'description' => $g->description,
                'target_amount' => (float) $g->target_amount,
                'currency_code' => $g->currency_code,
                'target_date' => $g->target_date?->format('Y-m-d'),
                'status' => $g->status,
                'priority' => $g->priority,
                'contributed' => (float) ($g->contributions_sum_amount ?? 0),
                'wallet' => $g->wallet ? ['id' => $g->wallet->id, 'name' => $g->wallet->name] : null,
            ]),
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'target_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'wallet_id' => ['nullable', 'integer', 'exists:wallets,id'],
        ]);

        $goal = $family->savingsGoals()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'],
            'currency_code' => $validated['currency_code'] ?? $family->currency_code ?? config('currencies.default', 'TZS'),
            'target_date' => $validated['target_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'priority' => $validated['priority'] ?? 3,
            'wallet_id' => $validated['wallet_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Savings goal created.',
            'goal' => ['id' => $goal->id],
        ], 201);
    }

    public function update(Request $request, Family $family, SavingsGoal $goal): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($goal->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'target_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'wallet_id' => ['nullable', 'integer', 'exists:wallets,id'],
        ]);

        $goal->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $goal->description,
            'target_amount' => $validated['target_amount'],
            'currency_code' => $validated['currency_code'] ?? $goal->currency_code,
            'target_date' => $validated['target_date'] ?? $goal->target_date,
            'status' => $validated['status'] ?? $goal->status,
            'priority' => $validated['priority'] ?? $goal->priority,
            'wallet_id' => $validated['wallet_id'] ?? $goal->wallet_id,
        ]);

        return response()->json([
            'message' => 'Savings goal updated.',
        ]);
    }

    public function destroy(Family $family, SavingsGoal $goal): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($goal->family_id !== $family->id) {
            abort(404);
        }

        $goal->delete();

        return response()->json([
            'message' => 'Savings goal deleted.',
        ]);
    }
}
