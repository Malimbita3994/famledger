<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
