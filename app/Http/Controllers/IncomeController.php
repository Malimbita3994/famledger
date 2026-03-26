<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Family;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
{
    use AuthorizesFamilyMember;

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
                ->route('families.wallets.index')
                ->with('error', 'Set up an active main wallet before recording income. All income is recorded into the main wallet.');
        }
        $categoryParents = IncomeCategory::hierarchicalDefaultsForForms();
        $flatCategories = $categoryParents->isEmpty()
            ? IncomeCategory::defaults()->sortBy('name')->values()
            : collect();

        $projects = $family->projects()->orderBy('name')->get(['id', 'name']);
        $properties = $family->properties()->orderBy('name')->get(['id', 'name']);

        $incomeGroupToSubIds = [];
        $incomeCategoryIdToGroupSub = [];
        foreach ($categoryParents as $parent) {
            $incomeGroupToSubIds[$parent->name] = [];
            foreach ($parent->children as $child) {
                $incomeGroupToSubIds[$parent->name][$child->name] = $child->id;
                $incomeCategoryIdToGroupSub[$child->id] = ['group' => $parent->name, 'sub' => $child->name];
            }
        }

        return view('families.incomes.create', [
            'family' => $family,
            'mainWallet' => $mainWallet,
            'categoryParents' => $categoryParents,
            'flatCategories' => $flatCategories,
            'incomeGroupToSubIds' => $incomeGroupToSubIds,
            'incomeCategoryIdToGroupSub' => $incomeCategoryIdToGroupSub,
            'projects' => $projects,
            'properties' => $properties,
            'sourceEntityTypes' => Income::sourceEntityTypes(),
            'recurringFrequencies' => Income::recurringFrequencies(),
        ]);
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallet = $family->mainWallet();
        if (! $wallet || $wallet->status !== 'active') {
            return redirect()
                ->route('families.wallets.index')
                ->with('error', 'Main wallet is missing or inactive. Please fix it before recording income.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($wallet->currency_code)])],
            'category_id' => ['required', Rule::exists('income_categories', 'id')->where(fn ($q) => $q->whereNull('family_id'))],
            'family_liability_id' => ['nullable', Rule::exists('family_liabilities', 'id')->where('family_id', $family->id)],
            'linked_project_id' => ['nullable', Rule::exists('projects', 'id')->where('family_id', $family->id)],
            'linked_property_id' => ['nullable', Rule::exists('properties', 'id')->where('family_id', $family->id)->whereNull('deleted_at')],
            'source' => ['nullable', 'string', 'max:255'],
            'source_entity_type' => ['nullable', 'string', Rule::in(array_keys(Income::sourceEntityTypes()))],
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_recurring' => ['sometimes', 'boolean'],
            'recurring_frequency' => [
                'nullable',
                Rule::requiredIf($request->boolean('is_recurring')),
                Rule::in(array_keys(Income::recurringFrequencies())),
            ],
        ], [
            'amount.min' => 'Amount must be greater than zero.',
            'currency_code.in' => 'Currency must match the main wallet.',
            'category_id.required' => 'Please choose a category for this income.',
            'recurring_frequency.required_if' => 'Choose how often this recurring income happens.',
        ]);

        $family->incomes()->create([
            'wallet_id' => $wallet->id,
            'category_id' => $validated['category_id'],
            'family_liability_id' => $validated['family_liability_id'] ?? null,
            'linked_project_id' => $validated['linked_project_id'] ?? null,
            'linked_property_id' => $validated['linked_property_id'] ?? null,
            'amount' => $validated['amount'],
            'currency_code' => strtoupper($validated['currency_code']),
            'source' => $validated['source'] ?? null,
            'source_entity_type' => $validated['source_entity_type'] ?? null,
            'received_date' => $validated['received_date'],
            'notes' => $validated['notes'] ?? null,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_frequency' => $request->boolean('is_recurring') ? ($validated['recurring_frequency'] ?? null) : null,
            'is_taxable' => $request->boolean('is_taxable'),
            'received_by' => auth()->id(),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.incomes.index')
            ->with('success', 'Income recorded. Wallet balance updated.');
    }
}
