<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyLiability;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FamilyLiabilityController extends Controller
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

        $liabilities = FamilyLiability::with(['wallet', 'project', 'property'])
            ->where('family_id', $family->id)
            ->orderByRaw("FIELD(status, 'active','overdue','closed') ASC")
            ->orderBy('due_date')
            ->paginate(15);

        $totals = [
            'total_outstanding' => $liabilities->getCollection()->sum->outstanding_balance,
            'active_count' => $liabilities->getCollection()->where('status', 'active')->count(),
            'closed_count' => $liabilities->getCollection()->where('status', 'closed')->count(),
        ];

        return view('families.liabilities.index', compact('family', 'liabilities', 'totals'));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get();
        $projects = $family->projects()->orderBy('name')->get();
        $properties = $family->properties()->orderBy('name')->get();
        $budgets = $family->budgets()->orderBy('name')->get();
        $savingsGoals = $family->savingsGoals()->orderBy('name')->get();

        return view('families.liabilities.create', compact('family', 'wallets', 'projects', 'properties', 'budgets', 'savingsGoals'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'overdue', 'closed'])],
            'principal_amount' => ['required', 'numeric', 'min:0'],
            'interest_rate' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'wallet_id' => ['nullable', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('family_id', $family->id)],
            'property_id' => ['nullable', Rule::exists('properties', 'id')->where('family_id', $family->id)],
            'budget_id' => ['nullable', Rule::exists('budgets', 'id')->where('family_id', $family->id)],
            'savings_goal_id' => ['nullable', Rule::exists('savings_goals', 'id')->where('family_id', $family->id)],
        ]);

        FamilyLiability::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'principal_amount' => $validated['principal_amount'],
            'interest_rate' => $validated['interest_rate'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'wallet_id' => $validated['wallet_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'budget_id' => $validated['budget_id'] ?? null,
            'savings_goal_id' => $validated['savings_goal_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.liabilities.index', $family)
            ->with('success', 'Liability recorded successfully.');
    }

    public function show(Family $family, FamilyLiability $liability)
    {
        $this->authorizeFamilyMember($family);

        abort_unless($liability->family_id === $family->id, 404);

        $liability->load(['wallet', 'project', 'property', 'budget', 'savingsGoal']);

        return view('families.liabilities.show', compact('family', 'liability'));
    }

    public function edit(Family $family, FamilyLiability $liability)
    {
        $this->authorizeFamilyMember($family);

        abort_unless($liability->family_id === $family->id, 404);

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get();
        $projects = $family->projects()->orderBy('name')->get();
        $properties = $family->properties()->orderBy('name')->get();
        $budgets = $family->budgets()->orderBy('name')->get();
        $savingsGoals = $family->savingsGoals()->orderBy('name')->get();

        return view('families.liabilities.edit', compact('family', 'liability', 'wallets', 'projects', 'properties', 'budgets', 'savingsGoals'));
    }

    public function update(Request $request, Family $family, FamilyLiability $liability)
    {
        $this->authorizeFamilyMember($family);

        abort_unless($liability->family_id === $family->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'overdue', 'closed'])],
            'principal_amount' => ['required', 'numeric', 'min:0'],
            'interest_rate' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'wallet_id' => ['nullable', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('family_id', $family->id)],
            'property_id' => ['nullable', Rule::exists('properties', 'id')->where('family_id', $family->id)],
            'budget_id' => ['nullable', Rule::exists('budgets', 'id')->where('family_id', $family->id)],
            'savings_goal_id' => ['nullable', Rule::exists('savings_goals', 'id')->where('family_id', $family->id)],
        ]);

        $liability->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'principal_amount' => $validated['principal_amount'],
            'interest_rate' => $validated['interest_rate'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'wallet_id' => $validated['wallet_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'budget_id' => $validated['budget_id'] ?? null,
            'savings_goal_id' => $validated['savings_goal_id'] ?? null,
        ]);

        return redirect()
            ->route('families.liabilities.show', [$family, $liability])
            ->with('success', 'Liability updated successfully.');
    }

    public function destroy(Family $family, FamilyLiability $liability)
    {
        $this->authorizeFamilyMember($family);

        abort_unless($liability->family_id === $family->id, 404);

        $liability->delete();

        return redirect()
            ->route('families.liabilities.index', $family)
            ->with('success', 'Liability removed.');
    }
}

