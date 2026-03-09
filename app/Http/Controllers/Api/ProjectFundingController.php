<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\ProjectFunding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectFundingController extends Controller
{
    use AuthorizesFamilyMember;

    /**
     * List projects with funding totals (for Projects Funding screen).
     * Optionally include recent fundings.
     */
    public function index(Family $family, Request $request): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $projects = $family->projects()
            ->with(['wallet:id,name,currency_code'])
            ->withSum('fundings', 'amount')
            ->withSum('expenses', 'amount')
            ->orderBy('name')
            ->get();

        $includeFundings = $request->boolean('include_fundings', false);
        $fundings = [];
        if ($includeFundings) {
            $projectIds = $family->projects()->pluck('id');
            $fundings = ProjectFunding::whereIn('project_id', $projectIds)
                ->with(['project:id,name', 'wallet:id,name,currency_code'])
                ->orderByDesc('funding_date')
                ->limit(50)
                ->get()
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'amount' => (float) $f->amount,
                    'currency_code' => $f->currency_code,
                    'funding_date' => $f->funding_date?->format('Y-m-d'),
                    'source_type' => $f->source_type,
                    'project' => $f->project ? ['id' => $f->project->id, 'name' => $f->project->name] : null,
                    'wallet' => $f->wallet ? ['id' => $f->wallet->id, 'name' => $f->wallet->name] : null,
                ])
                ->values()
                ->all();
        }

        return response()->json([
            'projects' => $projects->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'planned_budget' => (float) $p->planned_budget,
                'currency_code' => $p->currency_code,
                'funded' => (float) ($p->fundings_sum_amount ?? 0),
                'spent' => (float) ($p->expenses_sum_amount ?? 0),
                'wallet' => $p->wallet ? ['id' => $p->wallet->id, 'name' => $p->wallet->name] : null,
            ]),
            'fundings' => $fundings,
        ]);
    }
}
