<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;

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
}
