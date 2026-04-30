<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Family;
use App\Models\Project;
use App\Models\ProjectFunding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectFundingController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $projects = $family->projects()
            ->with(['wallet:id,name,currency_code'])
            ->withSum('fundings', 'amount')
            ->withSum('expenses', 'amount')
            ->orderBy('name')
            ->get();

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);
        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        $projectFundingIndexModalPayloads = $projects->isEmpty()
            ? []
            : $projects->mapWithKeys(fn (Project $p) => [
                $p->id => [
                    'title' => $p->name,
                    'rows' => $p->fundingFormDetailRows(),
                ],
            ])->all();

        return view('families.projects.funding-index', compact(
            'family',
            'projects',
            'wallets',
            'currency',
            'projectFundingIndexModalPayloads',
        ));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $projects = $family->projects()
            ->whereIn('status', [Project::STATUS_PLANNING, Project::STATUS_ACTIVE])
            ->with('wallet:id,name,currency_code')
            ->orderBy('name')
            ->get();

        $projects->each(fn (Project $p) => $p->loadSum('fundings', 'amount'));

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get();

        if ($projects->isEmpty()) {
            return redirect()
                ->route('families.projects.index')
                ->with('error', 'Create at least one project (Planning or Active) before adding funding.');
        }

        if ($wallets->count() < 1) {
            return redirect()
                ->route('families.wallets.index')
                ->with('error', 'Create at least one wallet before funding a project.');
        }

        $projectId = request()->query('project_id');
        $selectedProject = $projectId
            ? $projects->firstWhere('id', (int) $projectId)
            : null;

        $projectFundingModalPayloads = $projects->mapWithKeys(fn (Project $p) => [
            $p->id => [
                'title' => $p->name,
                'rows' => $p->fundingFormDetailRows(),
            ],
        ])->all();

        return view('families.projects.funding-create', [
            'family' => $family,
            'projects' => $projects,
            'wallets' => $wallets,
            'selectedProject' => $selectedProject,
            'projectFundingModalPayloads' => $projectFundingModalPayloads,
            'sourceTypes' => ProjectFunding::sourceTypes(),
        ]);
    }

    public function store(Request $request, Family $family)
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
        ], [
            'amount.min' => 'Amount must be greater than zero.',
        ]);

        if (strtoupper($sourceWallet->currency_code) !== strtoupper($project->currency_code)) {
            return back()->withInput()->withErrors(['wallet_id' => 'Source wallet currency must match project currency.']);
        }

        $currency = strtoupper($project->currency_code);

        DB::beginTransaction();
        try {
            $projectWallet = $project->wallet;
            if (! $projectWallet) {
                $projectWallet = $family->wallets()->create([
                    'name' => $project->name,
                    'type' => 'project_fund',
                    'currency_code' => $currency,
                    'description' => 'Fund bucket for: '.$project->name,
                    'initial_balance' => 0,
                    'is_shared' => true,
                    'status' => 'active',
                    'created_by' => auth()->id(),
                ]);
                $project->update(['wallet_id' => $projectWallet->id]);
            }

            if ($sourceWallet->id === $projectWallet->id) {
                DB::rollBack();

                return back()->withInput()->withErrors(['wallet_id' => 'Source wallet cannot be the project wallet. Choose another family wallet.']);
            }

            if (strtoupper($sourceWallet->currency_code) !== strtoupper($projectWallet->currency_code)) {
                DB::rollBack();

                return back()->withInput()->withErrors(['wallet_id' => 'Source wallet currency must match project currency.']);
            }

            $family->transfers()->create([
                'from_wallet_id' => $sourceWallet->id,
                'to_wallet_id' => $projectWallet->id,
                'amount' => $validated['amount'],
                'currency_code' => $currency,
                'transfer_date' => $validated['funding_date'],
                'description' => 'Project funding: '.$project->name,
                'reference' => $validated['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $project->fundings()->create([
                'wallet_id' => $sourceWallet->id,
                'amount' => $validated['amount'],
                'currency_code' => $currency,
                'funding_date' => $validated['funding_date'],
                'source_type' => $validated['source_type'] ?? ProjectFunding::SOURCE_TRANSFER,
                'reference' => $validated['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()
            ->route('families.projects.funding.index')
            ->with('success', 'Project funded successfully. Funds have been transferred to the project.');
    }
}
