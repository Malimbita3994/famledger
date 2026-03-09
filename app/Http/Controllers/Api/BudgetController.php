<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Family;
use Illuminate\Http\JsonResponse;

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
}
