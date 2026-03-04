<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    public function index(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->with('creator:id,name')
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->orderBy('name')->get();
        $walletTypes = Wallet::types();

        return view('families.wallets.index', compact('family', 'wallets', 'walletTypes'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $currencies = config('currencies', []);
        if ($family->currency_code && ! isset($currencies[$family->currency_code])) {
            $currencies = [$family->currency_code => $family->currency_code] + $currencies;
        }

        return view('families.wallets.create', compact('family', 'currencies'));
    }

    public function store(Request $request, Family $family)
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
        ], [
            'name.required' => 'Wallet name is required.',
            'type.required' => 'Please select a wallet type.',
            'currency_code.required' => 'Currency is required.',
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

        return redirect()
            ->route('families.wallets.index', $family)
            ->with('success', 'Wallet created successfully.');
    }

    public function show(Family $family, Wallet $wallet)
    {
        $this->authorizeFamilyMember($family);

        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $wallet->load('creator:id,name');
        $walletTypes = Wallet::types();
        $outgoingTransfers = $wallet->outgoingTransfers()->with('toWallet:id,name,currency_code')->orderByDesc('transfer_date')->limit(10)->get();
        $incomingTransfers = $wallet->incomingTransfers()->with('fromWallet:id,name,currency_code')->orderByDesc('transfer_date')->limit(10)->get();

        $recentIncomes = $wallet->incomes()->orderByDesc('received_date')->limit(10)->get();
        $recentExpenses = $wallet->expenses()->orderByDesc('expense_date')->limit(10)->get();

        return view('families.wallets.show', compact(
            'family',
            'wallet',
            'walletTypes',
            'outgoingTransfers',
            'incomingTransfers',
            'recentIncomes',
            'recentExpenses',
        ));
    }

    public function edit(Family $family, Wallet $wallet)
    {
        $this->authorizeFamilyMember($family);

        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $currencies = config('currencies', []);
        if ($wallet->currency_code && ! isset($currencies[$wallet->currency_code])) {
            $currencies = [$wallet->currency_code => $wallet->currency_code] + $currencies;
        }

        return view('families.wallets.edit', compact('family', 'wallet', 'currencies'));
    }

    public function update(Request $request, Family $family, Wallet $wallet)
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

        return redirect()
            ->route('families.wallets.index', $family)
            ->with('success', 'Wallet updated successfully.');
    }

    public function destroy(Family $family, Wallet $wallet)
    {
        $this->authorizeFamilyMember($family);

        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $wallet->delete();

        return redirect()
            ->route('families.wallets.index', $family)
            ->with('success', 'Wallet removed.');
    }
}
