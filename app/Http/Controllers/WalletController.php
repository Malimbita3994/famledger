<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Family;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $family->ensureDefaultMainWallet(auth()->id());

        $wallets = $family->wallets()->with('creator:id,name')
            ->with('dedicatedProject:id,name,wallet_id')
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();
        $walletTypes = Wallet::types();

        $chartCurrencyLabel = '';
        $chartWalletNames = [];
        $chartWalletBalances = [];
        $chartTypeLabels = [];
        $chartTypeBalances = [];
        $chartShareLabels = [];
        $chartShareValues = [];

        if ($wallets->isNotEmpty()) {
            $chartCurrency = $family->currency_code ? strtoupper($family->currency_code) : null;
            $walletsForChart = $chartCurrency
                ? $wallets->where('currency_code', $chartCurrency)->values()
                : $wallets->values();
            if ($walletsForChart->isEmpty()) {
                $walletsForChart = $wallets->values();
            }
            $chartCurrencyLabel = (string) ($walletsForChart->first()->currency_code ?? $chartCurrency ?? '');

            $chartWalletNames = $walletsForChart->map(fn (Wallet $w) => $w->display_name)->values()->all();
            $chartWalletBalances = $walletsForChart->map(fn (Wallet $w) => round((float) $w->balance, 2))->values()->all();

            $byType = $walletsForChart->groupBy('type')->map(
                fn ($group) => round((float) $group->sum(fn (Wallet $w) => $w->balance), 2)
            );
            $chartTypeLabels = $byType->keys()->map(fn ($type) => $walletTypes[$type] ?? $type)->values()->all();
            $chartTypeBalances = $byType->values()->all();

            $positiveWallets = $walletsForChart->filter(fn (Wallet $w) => (float) $w->balance > 0)->values();
            $chartShareLabels = $positiveWallets->map(fn (Wallet $w) => $w->display_name)->values()->all();
            $chartShareValues = $positiveWallets->map(fn (Wallet $w) => round((float) $w->balance, 2))->values()->all();
        }

        return view('families.wallets.index', compact(
            'family',
            'wallets',
            'walletTypes',
            'chartCurrencyLabel',
            'chartWalletNames',
            'chartWalletBalances',
            'chartTypeLabels',
            'chartTypeBalances',
            'chartShareLabels',
            'chartShareValues',
        ));
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
            ->route('families.wallets.index')
            ->with('success', 'Wallet created successfully.');
    }

    /**
     * Route params order matches array_values(): {wallet} from URI, then session "family" from BindAccountFamilyFromSession.
     */
    public function show(Wallet $wallet, Family $family)
    {
        $this->authorizeFamilyMember($family);

        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $wallet->load([
            'creator:id,name',
            'dedicatedProject:id,name,wallet_id',
        ]);
        $walletTypes = Wallet::types();
        $walletLinkScope = fn ($q) => $q->select('id', 'name', 'currency_code')->with('dedicatedProject:id,name,wallet_id');
        $outgoingTransfers = $wallet->outgoingTransfers()->with(['toWallet' => $walletLinkScope])->orderByDesc('transfer_date')->limit(10)->get();
        $incomingTransfers = $wallet->incomingTransfers()->with(['fromWallet' => $walletLinkScope])->orderByDesc('transfer_date')->limit(10)->get();

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

    public function edit(Wallet $wallet, Family $family)
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

    public function update(Request $request, Wallet $wallet, Family $family)
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

        return redirect()
            ->route('families.wallets.index')
            ->with('success', 'Wallet updated successfully.');
    }

    public function destroy(Wallet $wallet, Family $family)
    {
        $this->authorizeFamilyMember($family);

        if ($wallet->family_id !== $family->id) {
            abort(404);
        }

        $wallet->delete();

        return redirect()
            ->route('families.wallets.index')
            ->with('success', 'Wallet removed.');
    }
}
