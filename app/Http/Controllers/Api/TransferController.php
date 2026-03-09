<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Transfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransferController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = $family->transfers()
            ->with(['fromWallet:id,name,currency_code', 'toWallet:id,name,currency_code'])
            ->orderByDesc('transfer_date');
        if ($request->filled('wallet_id')) {
            $wid = $request->wallet_id;
            $query->where(fn ($q) => $q->where('from_wallet_id', $wid)->orWhere('to_wallet_id', $wid));
        }
        $perPage = min((int) $request->get('per_page', 20), 50);
        $transfers = $query->paginate($perPage);

        return response()->json([
            'transfers' => $transfers->getCollection()->map(fn ($t) => [
                'id' => $t->id,
                'amount' => (float) $t->amount,
                'currency_code' => $t->currency_code,
                'transfer_date' => $t->transfer_date?->format('Y-m-d'),
                'description' => $t->description,
                'from_wallet' => $t->fromWallet ? ['id' => $t->fromWallet->id, 'name' => $t->fromWallet->name] : null,
                'to_wallet' => $t->toWallet ? ['id' => $t->toWallet->id, 'name' => $t->toWallet->name] : null,
            ]),
            'meta' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'per_page' => $transfers->perPage(),
                'total' => $transfers->total(),
            ],
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $fromWallet = $family->wallets()->find($request->input('from_wallet_id'));
        $toWallet = $family->wallets()->find($request->input('to_wallet_id'));
        if (! $fromWallet || ! $toWallet) {
            return response()->json(['message' => 'Invalid wallet.'], 422);
        }

        $validated = $request->validate([
            'from_wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'to_wallet_id' => [
                'required',
                'different:from_wallet_id',
                Rule::exists('wallets', 'id')->where('family_id', $family->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($fromWallet->currency_code)])],
            'transfer_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        if (strtoupper($fromWallet->currency_code) !== strtoupper($toWallet->currency_code)) {
            return response()->json(['message' => 'Both wallets must use the same currency.'], 422);
        }

        $available = $fromWallet->balance;
        if ($validated['amount'] > $available) {
            return response()->json([
                'message' => 'Insufficient funds in the source wallet. Available: ' . number_format($available, 2) . ' ' . $fromWallet->currency_code,
            ], 422);
        }

        $transfer = $family->transfers()->create([
            'from_wallet_id' => $validated['from_wallet_id'],
            'to_wallet_id' => $validated['to_wallet_id'],
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'transfer_date' => $validated['transfer_date'],
            'description' => $validated['description'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $transfer->load(['fromWallet:id,name,currency_code', 'toWallet:id,name,currency_code']);

        return response()->json([
            'message' => 'Transfer recorded.',
            'transfer' => [
                'id' => $transfer->id,
                'amount' => (float) $transfer->amount,
                'currency_code' => $transfer->currency_code,
                'transfer_date' => $transfer->transfer_date?->format('Y-m-d'),
                'from_wallet' => ['id' => $transfer->fromWallet->id, 'name' => $transfer->fromWallet->name],
                'to_wallet' => ['id' => $transfer->toWallet->id, 'name' => $transfer->toWallet->name],
                'description' => $transfer->description,
                'reference' => $transfer->reference,
            ],
        ], 201);
    }

    /**
     * Update an existing transfer.
     * We allow editing metadata (description, reference, date) but not amount or wallets.
     */
    public function update(Request $request, Family $family, Transfer $transfer): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($transfer->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'transfer_date' => ['sometimes', 'date'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'reference' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $transfer->fill($validated);
        $transfer->save();

        $transfer->load(['fromWallet:id,name,currency_code', 'toWallet:id,name,currency_code']);

        return response()->json([
            'message' => 'Transfer updated.',
            'transfer' => [
                'id' => $transfer->id,
                'amount' => (float) $transfer->amount,
                'currency_code' => $transfer->currency_code,
                'transfer_date' => $transfer->transfer_date?->format('Y-m-d'),
                'from_wallet' => $transfer->fromWallet ? ['id' => $transfer->fromWallet->id, 'name' => $transfer->fromWallet->name] : null,
                'to_wallet' => $transfer->toWallet ? ['id' => $transfer->toWallet->id, 'name' => $transfer->toWallet->name] : null,
                'description' => $transfer->description,
                'reference' => $transfer->reference,
            ],
        ]);
    }

    public function destroy(Family $family, Transfer $transfer): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($transfer->family_id !== $family->id) {
            abort(404);
        }

        $transfer->delete();

        return response()->json([
            'message' => 'Transfer deleted.',
        ]);
    }
}
