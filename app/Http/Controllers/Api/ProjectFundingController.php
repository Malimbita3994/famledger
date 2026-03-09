<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Project;
use App\Models\ProjectFunding;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $project = $family->projects()->findOrFail($request->input('project_id'));
        $sourceWallet = $family->wallets()->findOrFail($request->input('wallet_id'));

        $validated = $request->validate([
            'project_id' => ['required', Rule::exists('projects', 'id')->where('family_id', $family->id)],
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'funding_date' => ['required', 'date'],
            'source_type' => ['nullable', Rule::in(array_keys(ProjectFunding::sourceTypes()))],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        if (strtoupper($sourceWallet->currency_code) !== strtoupper($project->currency_code)) {
            return response()->json([
                'message' => 'Source wallet currency must match project currency.',
            ], 422);
        }

        $currency = strtoupper($project->currency_code);

        DB::beginTransaction();
        try {
            $projectWallet = $project->wallet;
            if (! $projectWallet) {
                $projectWallet = $family->wallets()->create([
                    'name' => 'Project: ' . $project->name,
                    'type' => 'project_fund',
                    'currency_code' => $currency,
                    'description' => 'Dedicated wallet for project: ' . $project->name,
                    'initial_balance' => 0,
                    'is_shared' => true,
                    'status' => 'active',
                    'created_by' => $request->user()->id,
                ]);
                $project->update(['wallet_id' => $projectWallet->id]);
            }

            if ($sourceWallet->id === $projectWallet->id) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Source wallet cannot be the project wallet. Choose another family wallet.',
                ], 422);
            }

            if (strtoupper($sourceWallet->currency_code) !== strtoupper($projectWallet->currency_code)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Source wallet currency must match project currency.',
                ], 422);
            }

            $family->transfers()->create([
                'from_wallet_id' => $sourceWallet->id,
                'to_wallet_id' => $projectWallet->id,
                'amount' => $validated['amount'],
                'currency_code' => $currency,
                'transfer_date' => $validated['funding_date'],
                'description' => 'Project funding: ' . $project->name,
                'reference' => $validated['reference'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $funding = $project->fundings()->create([
                'wallet_id' => $sourceWallet->id,
                'amount' => $validated['amount'],
                'currency_code' => $currency,
                'funding_date' => $validated['funding_date'],
                'source_type' => $validated['source_type'] ?? ProjectFunding::SOURCE_TRANSFER,
                'reference' => $validated['reference'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'message' => 'Project funded successfully. Funds have been transferred to the project.',
            'funding' => [
                'id' => $funding->id,
                'amount' => (float) $funding->amount,
                'currency_code' => $funding->currency_code,
                'funding_date' => $funding->funding_date?->format('Y-m-d'),
            ],
        ], 201);
    }
}
