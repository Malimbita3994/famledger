<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyLiability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'principal_amount' => ['required', 'numeric', 'min:0'],
            'outstanding_balance' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'wallet_id' => ['nullable', 'integer', 'exists:wallets,id'],
        ]);

        $liability = FamilyLiability::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
            'principal_amount' => $validated['principal_amount'],
            'outstanding_balance' => $validated['outstanding_balance'] ?? $validated['principal_amount'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'wallet_id' => $validated['wallet_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Liability created.',
            'liability' => ['id' => $liability->id],
        ], 201);
    }

    public function update(Request $request, Family $family, FamilyLiability $liability): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($liability->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'principal_amount' => ['required', 'numeric', 'min:0'],
            'outstanding_balance' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'wallet_id' => ['nullable', 'integer', 'exists:wallets,id'],
        ]);

        $liability->update([
            'name' => $validated['name'],
            'type' => $validated['type'] ?? $liability->type,
            'principal_amount' => $validated['principal_amount'],
            'outstanding_balance' => $validated['outstanding_balance'] ?? $liability->outstanding_balance,
            'due_date' => $validated['due_date'] ?? $liability->due_date,
            'status' => $validated['status'] ?? $liability->status,
            'wallet_id' => $validated['wallet_id'] ?? $liability->wallet_id,
        ]);

        return response()->json([
            'message' => 'Liability updated.',
        ]);
    }

    public function destroy(Family $family, FamilyLiability $liability): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($liability->family_id !== $family->id) {
            abort(404);
        }

        $liability->delete();

        return response()->json([
            'message' => 'Liability deleted.',
        ]);
    }
}
