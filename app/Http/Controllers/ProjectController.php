<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Project;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    public function index(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $query = $family->projects()->with(['wallet:id,name,currency_code']);

        $filter = $request->get('filter', 'all');
        if ($filter === 'active') {
            $query->where('status', Project::STATUS_ACTIVE);
        } elseif ($filter === 'completed') {
            $query->where('status', Project::STATUS_COMPLETED);
        } elseif ($filter === 'planning') {
            $query->where('status', Project::STATUS_PLANNING);
        } elseif ($filter === 'on_hold') {
            $query->where('status', Project::STATUS_ON_HOLD);
        } elseif ($filter === 'cancelled') {
            $query->where('status', Project::STATUS_CANCELLED);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $projects = $query->withSum('fundings', 'amount')
            ->withSum('expenses', 'amount')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');
        $projectTypes = Project::types();
        $projectStatuses = Project::statuses();
        $priorities = Project::priorities();

        return view('families.projects.index', compact(
            'family', 'projects', 'currency', 'projectTypes', 'projectStatuses', 'priorities', 'filter'
        ));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $currencies = array_filter(
            (array) config('currencies', []),
            fn ($k) => $k !== 'default',
            ARRAY_FILTER_USE_KEY
        );
        if ($family->currency_code && ! isset($currencies[$family->currency_code])) {
            $currencies = [$family->currency_code => $family->currency_code] + $currencies;
        }

        return view('families.projects.create', [
            'family' => $family,
            'currencies' => $currencies,
            'projectTypes' => Project::types(),
            'projectStatuses' => Project::statuses(),
            'priorities' => Project::priorities(),
        ]);
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['nullable', Rule::in(array_keys(Project::types()))],
            'planned_budget' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'size:3'],
            'start_date' => ['nullable', 'date'],
            'target_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(array_keys(Project::statuses()))],
            'priority' => ['nullable', Rule::in(array_keys(Project::priorities()))],
        ], [
            'name.required' => 'Project name is required.',
            'planned_budget.min' => 'Planned budget must be zero or greater.',
        ]);

        $family->projects()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? null,
            'planned_budget' => $validated['planned_budget'],
            'currency_code' => strtoupper($validated['currency_code']),
            'start_date' => $validated['start_date'] ?? null,
            'target_end_date' => $validated['target_end_date'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('families.projects.index', [$family, 'filter' => 'all'])
            ->with('success', 'Project created successfully.');
    }

    public function show(Family $family, Project $project)
    {
        $this->authorizeFamilyMember($family);
        if ($project->family_id !== $family->id) {
            abort(404);
        }

        $project->load(['wallet', 'fundings.wallet:id,name,currency_code', 'expenses.category:id,name', 'budget']);
        $project->loadSum('fundings', 'amount');
        $project->loadSum('expenses', 'amount');

        return view('families.projects.show', compact('family', 'project'));
    }

    public function edit(Family $family, Project $project)
    {
        $this->authorizeFamilyMember($family);
        if ($project->family_id !== $family->id) {
            abort(404);
        }

        $currencies = array_filter(
            (array) config('currencies', []),
            fn ($k) => $k !== 'default',
            ARRAY_FILTER_USE_KEY
        );
        if ($family->currency_code && ! isset($currencies[$family->currency_code])) {
            $currencies = [$family->currency_code => $family->currency_code] + $currencies;
        }

        return view('families.projects.edit', [
            'family' => $family,
            'project' => $project,
            'currencies' => $currencies,
            'projectTypes' => Project::types(),
            'projectStatuses' => Project::statuses(),
            'priorities' => Project::priorities(),
        ]);
    }

    public function update(Request $request, Family $family, Project $project)
    {
        $this->authorizeFamilyMember($family);
        if ($project->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['nullable', Rule::in(array_keys(Project::types()))],
            'planned_budget' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'size:3'],
            'start_date' => ['nullable', 'date'],
            'target_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'actual_end_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(Project::statuses()))],
            'priority' => ['nullable', Rule::in(array_keys(Project::priorities()))],
        ]);

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? null,
            'planned_budget' => $validated['planned_budget'],
            'currency_code' => strtoupper($validated['currency_code']),
            'start_date' => $validated['start_date'] ?? null,
            'target_end_date' => $validated['target_end_date'] ?? null,
            'actual_end_date' => $validated['actual_end_date'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'] ?? null,
        ]);

        return redirect()
            ->route('families.projects.show', [$family, $project])
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Family $family, Project $project)
    {
        $this->authorizeFamilyMember($family);
        if ($project->family_id !== $family->id) {
            abort(404);
        }

        if ($project->fundings()->exists() || $project->expenses()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete project with existing funding or expenses. Consider marking it as Cancelled.');
        }

        if ($project->wallet_id) {
            $project->wallet()->delete();
        }
        $project->delete();

        return redirect()
            ->route('families.projects.index', $family)
            ->with('success', 'Project removed.');
    }
}
