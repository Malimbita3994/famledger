<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SavingsGoalController extends Controller
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

        $goals = $family->savingsGoals()
            ->with('wallet:id,name,currency_code')
            ->withSum('contributions', 'amount')
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderByDesc('target_date')
            ->paginate(15);

        return view('families.savings-goals.index', compact('family', 'goals'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);
        if ($wallets->isEmpty()) {
            return redirect()
                ->route('families.wallets.index', $family)
                ->with('error', 'Create at least one wallet before creating a savings goal. Goals accumulate funds in a wallet.');
        }
        $currencies = config('currencies', []);
        if ($family->currency_code && ! isset($currencies[$family->currency_code])) {
            $currencies = [$family->currency_code => $family->currency_code] + $currencies;
        }

        return view('families.savings-goals.create', compact('family', 'wallets', 'currencies'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'],
            'target_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'priority' => ['nullable', Rule::in(array_keys(SavingsGoal::priorities()))],
            'status' => ['nullable', Rule::in(array_keys(SavingsGoal::statuses()))],
        ]);

        $family->savingsGoals()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'target_date' => $validated['target_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'wallet_id' => $validated['wallet_id'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => $validated['status'] ?? 'active',
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.savings-goals.index', $family)
            ->with('success', 'Savings goal created. You can now contribute to it.');
    }

    public function show(Family $family, SavingsGoal $savingsGoal)
    {
        $this->authorizeFamilyMember($family);
        if ($savingsGoal->family_id !== $family->id) {
            abort(404);
        }

        $savingsGoal->load(['wallet:id,name,currency_code', 'createdBy:id,name']);
        $contributions = $savingsGoal->contributions()->with('fromWallet:id,name')->orderByDesc('contribution_date')->orderByDesc('id')->paginate(15);
        $wallets = $family->wallets()->where('status', 'active')->where('id', '!=', $savingsGoal->wallet_id)->orderBy('name')->get(['id', 'name', 'currency_code']);

        return view('families.savings-goals.show', compact('family', 'savingsGoal', 'contributions', 'wallets'));
    }

    public function edit(Family $family, SavingsGoal $savingsGoal)
    {
        $this->authorizeFamilyMember($family);
        if ($savingsGoal->family_id !== $family->id) {
            abort(404);
        }

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);
        $currencies = config('currencies', []);
        if ($family->currency_code && ! isset($currencies[$family->currency_code])) {
            $currencies = [$family->currency_code => $family->currency_code] + $currencies;
        }

        return view('families.savings-goals.edit', compact('family', 'savingsGoal', 'wallets', 'currencies'));
    }

    public function update(Request $request, Family $family, SavingsGoal $savingsGoal)
    {
        $this->authorizeFamilyMember($family);
        if ($savingsGoal->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'],
            'target_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'priority' => ['nullable', Rule::in(array_keys(SavingsGoal::priorities()))],
            'status' => ['nullable', Rule::in(array_keys(SavingsGoal::statuses()))],
        ]);

        $savingsGoal->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'target_date' => $validated['target_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'wallet_id' => $validated['wallet_id'],
            'priority' => $validated['priority'] ?? $savingsGoal->priority,
            'status' => $validated['status'] ?? $savingsGoal->status,
        ]);

        return redirect()
            ->route('families.savings-goals.show', [$family, $savingsGoal])
            ->with('success', 'Savings goal updated.');
    }

    public function destroy(Family $family, SavingsGoal $savingsGoal)
    {
        $this->authorizeFamilyMember($family);
        if ($savingsGoal->family_id !== $family->id) {
            abort(404);
        }

        $savingsGoal->delete();

        return redirect()
            ->route('families.savings-goals.index', $family)
            ->with('success', 'Savings goal removed.');
    }

    public function contributeForm(Family $family, SavingsGoal $savingsGoal)
    {
        $this->authorizeFamilyMember($family);
        if ($savingsGoal->family_id !== $family->id) {
            abort(404);
        }

        $savingsGoal->load('wallet:id,name,currency_code');
        $wallets = $family->wallets()->where('status', 'active')->where('id', '!=', $savingsGoal->wallet_id)->orderBy('name')->get();

        if ($wallets->isEmpty()) {
            return redirect()
                ->route('families.savings-goals.show', [$family, $savingsGoal])
                ->with('error', 'You need another wallet to transfer from. The goal wallet is where funds accumulate.');
        }

        return view('families.savings-goals.contribute', compact('family', 'savingsGoal', 'wallets'));
    }

    public function contributeStore(Request $request, Family $family, SavingsGoal $savingsGoal)
    {
        $this->authorizeFamilyMember($family);
        if ($savingsGoal->family_id !== $family->id) {
            abort(404);
        }

        $fromWallet = $family->wallets()->findOrFail($request->input('from_wallet_id'));

        $validated = $request->validate([
            'from_wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'amount.min' => 'Amount must be greater than zero.',
        ]);

        if ((int) $validated['from_wallet_id'] === (int) $savingsGoal->wallet_id) {
            return back()->withInput()->withErrors(['from_wallet_id' => 'Select a different wallet. The goal wallet is the destination for contributions.']);
        }

        if (strtoupper($fromWallet->currency_code) !== strtoupper($savingsGoal->wallet->currency_code)) {
            return back()->withInput()->withErrors(['from_wallet_id' => 'Source wallet currency must match the goal wallet currency.']);
        }

        // Enforce that the source wallet has funds and contribution does not exceed balance
        $currentBalance = $fromWallet->balance;
        if ($currentBalance <= 0) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Source wallet has no available balance for this contribution.'])
                ->with('error', 'Source wallet balance is zero. Choose another wallet or fund it first.');
        }

        if ($validated['amount'] > $currentBalance) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Contribution cannot be greater than the source wallet balance.'])
                ->with('error', 'Contribution amount is greater than the available balance in the source wallet.');
        }

        DB::beginTransaction();
        try {
            $transfer = $family->transfers()->create([
                'from_wallet_id' => $fromWallet->id,
                'to_wallet_id' => $savingsGoal->wallet_id,
                'amount' => $validated['amount'],
                'currency_code' => strtoupper($savingsGoal->wallet->currency_code),
                'transfer_date' => now()->toDateString(),
                'description' => 'Savings: ' . $savingsGoal->name,
                'reference' => 'goal:' . $savingsGoal->id,
                'created_by' => auth()->id(),
            ]);

            $savingsGoal->contributions()->create([
                'amount' => $validated['amount'],
                'currency_code' => $savingsGoal->wallet->currency_code,
                'contribution_date' => now()->toDateString(),
                'source_type' => SavingsContribution::SOURCE_TRANSFER,
                'reference' => (string) $transfer->id,
                'from_wallet_id' => $fromWallet->id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()
            ->route('families.savings-goals.show', [$family, $savingsGoal])
            ->with('success', 'Contribution recorded. Funds transferred to goal wallet; progress updated.');
    }
}
