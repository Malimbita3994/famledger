<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
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
            'initial_balance' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(array_keys(Wallet::statuses()))],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        if (($validated['is_primary'] ?? false) === true) {
            $family->wallets()->update(['is_primary' => false]);
        }

        $wallet = $family->wallets()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency_code' => strtoupper($validated['currency_code']),
            'description' => $validated['description'] ?? null,
            'initial_balance' => $validated['initial_balance'] ?? 0,
            'status' => $validated['status'] ?? Wallet::STATUS_ACTIVE,
            'is_primary' => (bool) ($validated['is_primary'] ?? false),
            'is_shared' => true,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Wallet created.',
            'wallet' => $wallet,
        ], 201);
    }

    public function update(Request $request, Family $family, Wallet $wallet): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::in(array_keys(Wallet::types()))],
            'currency_code' => ['sometimes', 'required', 'string', 'size:3'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'initial_balance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(array_keys(Wallet::statuses()))],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        if (($validated['is_primary'] ?? false) === true) {
            $family->wallets()->where('id', '!=', $wallet->id)->update(['is_primary' => false]);
        }

        foreach (['name', 'type', 'description', 'initial_balance', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $wallet->{$field} = $validated[$field];
            }
        }
        if (array_key_exists('currency_code', $validated)) {
            $wallet->currency_code = strtoupper($validated['currency_code']);
        }
        if (array_key_exists('is_primary', $validated)) {
            $wallet->is_primary = (bool) $validated['is_primary'];
        }
        $wallet->save();

        return response()->json([
            'message' => 'Wallet updated.',
            'wallet' => $wallet,
        ]);
    }

    public function destroy(Family $family, Wallet $wallet): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $wallet->delete();

        return response()->json(['message' => 'Wallet deleted.']);
    }
}
