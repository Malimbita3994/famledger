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

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get();
        if ($wallets->isEmpty()) {
            return redirect()
                ->route('families.wallets.index', $family)
                ->with('error', 'Create at least one wallet before recording income. All income must go into a wallet.');
        }
        $categories = IncomeCategory::defaults();

        return view('families.incomes.create', compact('family', 'wallets', 'categories'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallet = $family->wallets()->findOrFail($request->input('wallet_id'));

        $validated = $request->validate([
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($wallet->currency_code)])],
            'category_id' => ['nullable', Rule::exists('income_categories', 'id')],
            'source' => ['nullable', 'string', 'max:255'],
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'wallet_id.required' => 'Please select a wallet. All income must go into a wallet.',
            'amount.min' => 'Amount must be greater than zero.',
            'currency_code.in' => 'Currency must match the selected wallet.',
        ]);

        $family->incomes()->create([
            'wallet_id' => $validated['wallet_id'],
            'category_id' => $validated['category_id'] ?? null,
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
