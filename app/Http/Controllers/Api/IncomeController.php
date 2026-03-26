<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = $family->incomes()
            ->with(['wallet:id,name,currency_code', 'category:id,name'])
            ->orderByDesc('received_date');
        if ($request->filled('wallet_id')) {
            $wallet = $family->wallets()->whereKey($request->wallet_id)->first();
            if ($wallet) {
                $query->where('wallet_id', $wallet->id);
            }
        }
        $perPage = min((int) $request->get('per_page', 20), 50);
        $incomes = $query->paginate($perPage);

        return response()->json([
            'incomes' => $incomes->getCollection()->map(fn ($i) => [
                'id' => $i->id,
                'amount' => (float) $i->amount,
                'currency_code' => $i->currency_code,
                'source' => $i->source,
                'source_entity_type' => $i->source_entity_type,
                'received_date' => $i->received_date?->format('Y-m-d'),
                'notes' => $i->notes,
                'is_recurring' => (bool) $i->is_recurring,
                'recurring_frequency' => $i->recurring_frequency,
                'is_taxable' => (bool) $i->is_taxable,
                'wallet' => $i->wallet ? ['id' => $i->wallet->id, 'name' => $i->wallet->name] : null,
                'category' => $i->category ? ['id' => $i->category->id, 'name' => $i->category->name] : null,
            ]),
            'meta' => [
                'current_page' => $incomes->currentPage(),
                'last_page' => $incomes->lastPage(),
                'per_page' => $incomes->perPage(),
                'total' => $incomes->total(),
            ],
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $wallet = $family->mainWallet();
        if (! $wallet || $wallet->status !== 'active') {
            return response()->json([
                'message' => 'Main wallet is missing or inactive. Set up an active main wallet first.',
            ], 422);
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
            'is_taxable' => ['sometimes', 'boolean'],
        ]);

        $income = $family->incomes()->create([
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
            'is_taxable' => $request->boolean('is_taxable', true),
            'received_by' => auth()->id(),
            'created_by' => auth()->id(),
        ]);

        $income->load(['wallet:id,name,currency_code', 'category:id,name']);

        return response()->json([
            'message' => 'Income recorded.',
            'income' => [
                'id' => $income->id,
                'amount' => (float) $income->amount,
                'currency_code' => $income->currency_code,
                'source' => $income->source,
                'source_entity_type' => $income->source_entity_type,
                'received_date' => $income->received_date?->format('Y-m-d'),
                'is_recurring' => (bool) $income->is_recurring,
                'recurring_frequency' => $income->recurring_frequency,
                'is_taxable' => (bool) $income->is_taxable,
                'linked_project_id' => $income->linked_project_id,
                'linked_property_id' => $income->linked_property_id,
                'wallet' => ['id' => $income->wallet->id, 'name' => $income->wallet->name],
                'category' => ['id' => $income->category->id, 'name' => $income->category->name],
            ],
        ], 201);
    }
}
