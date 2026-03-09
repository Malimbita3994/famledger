<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\WalletReconciliation;
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

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'wallet_id' => ['required', 'integer', 'exists:wallets,id'],
            'system_balance' => ['required', 'numeric'],
            'actual_balance' => ['required', 'numeric'],
            'reconciled_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $difference = (float) $validated['actual_balance'] - (float) $validated['system_balance'];

        $rec = WalletReconciliation::create([
            'family_id' => $family->id,
            'wallet_id' => $validated['wallet_id'],
            'system_balance' => $validated['system_balance'],
            'actual_balance' => $validated['actual_balance'],
            'difference' => $difference,
            'reconciled_at' => $validated['reconciled_at'] ?? now(),
            'status' => $validated['status'] ?? 'pending',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Reconciliation recorded.',
            'reconciliation' => ['id' => $rec->id],
        ], 201);
    }

    public function update(Request $request, Family $family, WalletReconciliation $reconciliation): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($reconciliation->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'system_balance' => ['required', 'numeric'],
            'actual_balance' => ['required', 'numeric'],
            'reconciled_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $difference = (float) $validated['actual_balance'] - (float) $validated['system_balance'];

        $reconciliation->update([
            'system_balance' => $validated['system_balance'],
            'actual_balance' => $validated['actual_balance'],
            'difference' => $difference,
            'reconciled_at' => $validated['reconciled_at'] ?? $reconciliation->reconciled_at,
            'status' => $validated['status'] ?? $reconciliation->status,
        ]);

        return response()->json([
            'message' => 'Reconciliation updated.',
        ]);
    }

    public function destroy(Family $family, WalletReconciliation $reconciliation): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($reconciliation->family_id !== $family->id) {
            abort(404);
        }

        $reconciliation->delete();

        return response()->json([
            'message' => 'Reconciliation deleted.',
        ]);
    }
}
