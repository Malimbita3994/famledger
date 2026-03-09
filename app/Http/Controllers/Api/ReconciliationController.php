<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = $family->reconciliations()
            ->with(['wallet:id,name,currency_code', 'createdBy:id,name']);
        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', $request->wallet_id);
        }
        $reconciliations = $query->orderByDesc('reconciled_at')->paginate(20);

        return response()->json([
            'reconciliations' => $reconciliations->getCollection()->map(fn ($r) => [
                'id' => $r->id,
                'wallet' => $r->wallet ? ['id' => $r->wallet->id, 'name' => $r->wallet->name] : null,
                'system_balance' => (float) $r->system_balance,
                'actual_balance' => (float) $r->actual_balance,
                'difference' => (float) $r->difference,
                'reconciled_at' => $r->reconciled_at?->toIso8601String(),
                'status' => $r->status,
            ]),
            'meta' => [
                'current_page' => $reconciliations->currentPage(),
                'last_page' => $reconciliations->lastPage(),
                'total' => $reconciliations->total(),
            ],
        ]);
    }
}
