<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()
            ->where('status', 'active')
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->orderBy('name')
            ->get();

        return response()->json([
            'wallets' => $wallets->map(fn (Wallet $w) => [
                'id' => $w->id,
                'name' => $w->name,
                'type' => $w->type,
                'currency_code' => $w->currency_code,
                'is_primary' => $w->is_primary,
                'balance' => round($w->balance, 2),
            ]),
        ]);
    }

    public function show(Family $family, Wallet $wallet): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $wallet->loadSum('incomes', 'amount')
            ->loadSum('expenses', 'amount')
            ->loadSum('incomingTransfers', 'amount')
            ->loadSum('outgoingTransfers', 'amount');

        return response()->json([
            'id' => $wallet->id,
            'name' => $wallet->name,
            'type' => $wallet->type,
            'currency_code' => $wallet->currency_code,
            'is_primary' => $wallet->is_primary,
            'balance' => round($wallet->balance, 2),
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Wallet::types()))],
            'currency_code' => ['required', 'string', 'size:3'],
            'description' => ['nullable', 'string', 'max:1000'],
            'initial_balance' => ['nullable', 'numeric'],
            'is_shared' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $wallet = $family->wallets()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency_code' => strtoupper($validated['currency_code']),
            'description' => $validated['description'] ?? null,
            'initial_balance' => $validated['initial_balance'] ?? 0,
            'is_shared' => (bool) ($validated['is_shared'] ?? true),
            'status' => $validated['status'] ?? 'active',
            'created_by' => auth()->id(),
        ]);

        // If family has no primary wallet yet, mark this one as primary
        if (! $family->wallets()->where('is_primary', true)->exists()) {
            $wallet->update(['is_primary' => true]);
        }

        return response()->json([
            'message' => 'Wallet created.',
            'wallet' => [
                'id' => $wallet->id,
                'name' => $wallet->name,
                'type' => $wallet->type,
                'currency_code' => $wallet->currency_code,
                'is_primary' => $wallet->is_primary,
                'balance' => round($wallet->balance, 2),
            ],
        ], 201);
    }

    public function update(Request $request, Family $family, Wallet $wallet): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Wallet::types()))],
            'currency_code' => ['required', 'string', 'size:3'],
            'description' => ['nullable', 'string', 'max:1000'],
            'initial_balance' => ['nullable', 'numeric'],
            'is_shared' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $wallet->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency_code' => strtoupper($validated['currency_code']),
            'description' => $validated['description'] ?? null,
            'initial_balance' => $validated['initial_balance'] ?? $wallet->initial_balance,
            'is_shared' => (bool) ($validated['is_shared'] ?? true),
            'status' => $validated['status'],
        ]);

        // If marked as primary, ensure it's the only primary wallet for this family
        if ($request->boolean('is_primary')) {
            $family->wallets()
                ->where('id', '!=', $wallet->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            if (! $wallet->is_primary) {
                $wallet->update(['is_primary' => true]);
            }
        }

        $wallet->refresh();

        return response()->json([
            'message' => 'Wallet updated.',
            'wallet' => [
                'id' => $wallet->id,
                'name' => $wallet->name,
                'type' => $wallet->type,
                'currency_code' => $wallet->currency_code,
                'is_primary' => $wallet->is_primary,
                'balance' => round($wallet->balance, 2),
            ],
        ]);
    }

    public function destroy(Family $family, Wallet $wallet): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $wallet->delete();

        return response()->json([
            'message' => 'Wallet deleted.',
        ]);
    }
}
