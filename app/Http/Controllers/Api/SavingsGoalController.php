<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\SavingsGoal;
use Illuminate\Http\JsonResponse;

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
}
