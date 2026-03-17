<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
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

        $query = $family->incomes()->with(['wallet:id,family_id,name,currency_code', 'category:id,name', 'createdBy:id,name']);
        if ($request->filled('wallet_id')) {
            $wallet = $family->wallets()->find($request->wallet_id);
            if ($wallet) {
                $query->where('wallet_id', $wallet->id);
            }
        }
        $incomes = $query->orderByDesc('received_date')->orderByDesc('id')->paginate(20);
        $wallets = $family->wallets()
            ->where('status', 'active')
            ->orderBy('name')
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->get(['id', 'name', 'currency_code', 'initial_balance']);

        return view('families.incomes.index', compact('family', 'incomes', 'wallets'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $mainWallet = $family->mainWallet();
        if (! $mainWallet || $mainWallet->status !== 'active') {
            return redirect()
                ->route('families.wallets.index', $family)
                ->with('error', 'Set up an active main wallet before recording income. All income is recorded into the main wallet.');
        }
        $categories = IncomeCategory::defaults();

        return view('families.incomes.create', compact('family', 'mainWallet', 'categories'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallet = $family->mainWallet();
        if (! $wallet || $wallet->status !== 'active') {
            return redirect()
                ->route('families.wallets.index', $family)
                ->with('error', 'Main wallet is missing or inactive. Please fix it before recording income.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($wallet->currency_code)])],
            'category_id' => ['required', Rule::exists('income_categories', 'id')],
            'family_liability_id' => ['nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'source' => ['nullable', 'string', 'max:255'],
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'amount.min' => 'Amount must be greater than zero.',
            'currency_code.in' => 'Currency must match the main wallet.',
            'category_id.required' => 'Please choose a category for this income.',
        ]);

        $family->incomes()->create([
            'wallet_id' => $wallet->id,
            'category_id' => $validated['category_id'],
            'family_liability_id' => $validated['family_liability_id'] ?? null,
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'source' => $validated['source'] ?? null,
            'received_date' => $validated['received_date'],
            'notes' => $validated['notes'] ?? null,
            'received_by' => auth()->id(),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.incomes.index', $family)
            ->with('success', 'Income recorded. Wallet balance updated.');
    }
}
