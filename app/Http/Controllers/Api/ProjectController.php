<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $query = $family->projects()
            ->with(['wallet:id,name,currency_code'])
            ->withSum('fundings', 'amount')
            ->withSum('expenses', 'amount')
            ->orderByDesc('created_at');

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

        $perPage = min((int) $request->get('per_page', 20), 50);
        $projects = $query->paginate($perPage);

        return response()->json([
            'projects' => $projects->getCollection()->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'type' => $p->type,
                'status' => $p->status,
                'priority' => $p->priority,
                'planned_budget' => (float) $p->planned_budget,
                'currency_code' => $p->currency_code,
                'start_date' => $p->start_date?->format('Y-m-d'),
                'target_end_date' => $p->target_end_date?->format('Y-m-d'),
                'actual_end_date' => $p->actual_end_date?->format('Y-m-d'),
                'funded' => (float) ($p->fundings_sum_amount ?? 0),
                'spent' => (float) ($p->expenses_sum_amount ?? 0),
                'wallet' => $p->wallet ? ['id' => $p->wallet->id, 'name' => $p->wallet->name] : null,
            ]),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    public function show(Family $family, Project $project): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($project->family_id !== $family->id) {
            abort(404);
        }

        $project->load(['wallet:id,name,currency_code'])
            ->loadSum('fundings', 'amount')
            ->loadSum('expenses', 'amount');

        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'type' => $project->type,
            'status' => $project->status,
            'priority' => $project->priority,
            'planned_budget' => (float) $project->planned_budget,
            'currency_code' => $project->currency_code,
            'start_date' => $project->start_date?->format('Y-m-d'),
            'target_end_date' => $project->target_end_date?->format('Y-m-d'),
            'actual_end_date' => $project->actual_end_date?->format('Y-m-d'),
            'funded' => (float) ($project->fundings_sum_amount ?? 0),
            'spent' => (float) ($project->expenses_sum_amount ?? 0),
            'wallet' => $project->wallet ? ['id' => $project->wallet->id, 'name' => $project->wallet->name] : null,
        ]);
    }

    public function store(Request $request, Family $family): JsonResponse
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
        ]);

        $project = $family->projects()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? null,
            'planned_budget' => $validated['planned_budget'],
            'currency_code' => strtoupper($validated['currency_code']),
            'start_date' => $validated['start_date'] ?? null,
            'target_end_date' => $validated['target_end_date'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Project created.',
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'type' => $project->type,
                'status' => $project->status,
                'priority' => $project->priority,
                'planned_budget' => (float) $project->planned_budget,
                'currency_code' => $project->currency_code,
                'start_date' => $project->start_date?->format('Y-m-d'),
                'target_end_date' => $project->target_end_date?->format('Y-m-d'),
            ],
        ], 201);
    }

    public function update(Request $request, Family $family, Project $project): JsonResponse
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

        return response()->json([
            'message' => 'Project updated.',
        ]);
    }

    public function destroy(Family $family, Project $project): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        if ($project->family_id !== $family->id) {
            abort(404);
        }

        if ($project->fundings()->exists() || $project->expenses()->exists()) {
            return response()->json([
                'message' => 'Cannot delete project with existing funding or expenses. Consider marking it as Cancelled.',
            ], 422);
        }

        if ($project->wallet_id) {
            $project->wallet()->delete();
        }
        $project->delete();

        return response()->json([
            'message' => 'Project removed.',
        ]);
    }
}
