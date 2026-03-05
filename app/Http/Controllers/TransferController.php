<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransferController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    public function index(Family $family, Request $request)
    {
        $this->authorizeFamilyMember($family);

        $query = $family->transfers()->with([
            'fromWallet:id,family_id,name,currency_code',
            'toWallet:id,family_id,name,currency_code',
            'createdBy:id,name',
        ]);
        if ($request->filled('wallet_id')) {
            $wallet = $family->wallets()->find($request->wallet_id);
            if ($wallet) {
                $query->where(function ($q) use ($wallet) {
                    $q->where('from_wallet_id', $wallet->id)->orWhere('to_wallet_id', $wallet->id);
                });
            }
        }
        $transfers = $query->orderByDesc('transfer_date')->orderByDesc('id')->paginate(20);
        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);

        return view('families.transfers.index', compact('family', 'transfers', 'wallets'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get();
        if ($wallets->count() < 2) {
            return redirect()
                ->route('families.wallets.index', $family)
                ->with('error', 'You need at least two wallets to make a transfer. Create another wallet first.');
        }

        return view('families.transfers.create', compact('family', 'wallets'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $fromWallet = $family->wallets()->findOrFail($request->input('from_wallet_id'));
        $toWallet = $family->wallets()->findOrFail($request->input('to_wallet_id'));

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
        ], [
            'from_wallet_id.required' => 'Please select the source wallet.',
            'to_wallet_id.required' => 'Please select the destination wallet.',
            'to_wallet_id.different' => 'Source and destination wallets must be different.',
            'amount.min' => 'Amount must be greater than zero.',
            'currency_code.in' => 'Currency must match both wallets.',
        ]);

        if (strtoupper($fromWallet->currency_code) !== strtoupper($toWallet->currency_code)) {
            return back()->withInput()->withErrors(['to_wallet_id' => 'Both wallets must use the same currency. Multi-currency transfers are not supported yet.']);
        }

        // Ensure source wallet has enough balance for this transfer
        $available = $fromWallet->balance;
        if ($validated['amount'] > $available) {
            $message = 'Insufficient funds in the source wallet. Available balance is ' . number_format($available, 2) . ' ' . strtoupper($fromWallet->currency_code) . '.';

            return back()
                ->withInput()
                ->withErrors([
                    'amount' => $message,
                ])
                ->with('error', $message);
        }

        $family->transfers()->create([
            'from_wallet_id' => $validated['from_wallet_id'],
            'to_wallet_id' => $validated['to_wallet_id'],
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'transfer_date' => $validated['transfer_date'],
            'description' => $validated['description'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.transfers.index', $family)
            ->with('success', 'Transfer recorded. Both wallet balances updated.');
    }
}
