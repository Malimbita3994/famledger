<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyLiability;
use Illuminate\Http\JsonResponse;

class LiabilityController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $liabilities = FamilyLiability::with(['wallet:id,name,currency_code'])
            ->where('family_id', $family->id)
            ->orderByRaw("FIELD(status, 'active','overdue','closed')")
            ->orderBy('due_date')
            ->get();

        return response()->json([
            'liabilities' => $liabilities->map(fn (FamilyLiability $l) => [
                'id' => $l->id,
                'name' => $l->name,
                'type' => $l->type,
                'status' => $l->status,
                'principal_amount' => (float) $l->principal_amount,
                'outstanding_balance' => (float) $l->outstanding_balance,
                'due_date' => $l->due_date?->format('Y-m-d'),
                'wallet' => $l->wallet ? ['id' => $l->wallet->id, 'name' => $l->wallet->name] : null,
            ]),
        ]);
    }
}
