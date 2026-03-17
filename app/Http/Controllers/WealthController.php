<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyWealthTrend;
use App\Models\FamilyLiability;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDepreciation;
use App\Models\PropertyValuation;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WealthController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    public function index(Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        // Wallet totals (real cash only, split between general wallets and project wallets)
        $wallets = Wallet::where('family_id', $family->id)->get(['id', 'balance', 'type']);

        // Project wallets hold funds reserved for projects (type = project_fund).
        $projectWallets = $wallets->where('type', 'project_fund');
        $otherWallets = $wallets->where('type', '!=', 'project_fund');

        // Real cash in normal family wallets
        $walletTotal = (float) $otherWallets->sum('balance');

        // Property total (latest valuation or estimated value or purchase price)
        $properties = Property::where('family_id', $family->id)->get(['id', 'purchase_price', 'current_estimated_value']);
        $propertyIds = $properties->pluck('id')->all();

        $latestValuations = PropertyValuation::whereIn('property_id', $propertyIds)
            ->select('property_id', 'estimated_value', 'valuation_date')
            ->orderBy('valuation_date', 'desc')
            ->get()
            ->groupBy('property_id')
            ->map->first();

        $latestDepreciations = PropertyDepreciation::whereIn('property_id', $propertyIds)
            ->select('property_id', 'year', 'book_value')
            ->orderBy('year', 'desc')
            ->get()
            ->groupBy('property_id')
            ->map->first();

        $propertyTotal = 0.0;
        foreach ($properties as $property) {
            $latestVal = $latestValuations[$property->id] ?? null;
            $latestDep = $latestDepreciations[$property->id] ?? null;
            $purchase = (float) ($property->purchase_price ?? 0);
            $valuation = $latestVal ? (float) $latestVal->estimated_value : (float) ($property->current_estimated_value ?? 0);
            $book = $latestDep ? (float) $latestDep->book_value : ($valuation ?: $purchase);
            $propertyTotal += $book;
        }

        // Projects: use real funded cash sitting in project wallets (not planned budgets)
        $projectTotal = (float) $projectWallets->sum(function (Wallet $w) {
            return $w->balance;
        });

        // Liabilities: sum outstanding balance for this family
        $liabilityTotal = (float) FamilyLiability::where('family_id', $family->id)
            ->where('status', '!=', 'closed')
            ->sum('outstanding_balance');

        $netWealth = $walletTotal + $propertyTotal + $projectTotal - $liabilityTotal;

        // Asset allocation percentages (based on total assets, not net wealth)
        $totalAssets = $walletTotal + $propertyTotal + $projectTotal;
        if ($totalAssets > 0) {
            $walletPct = round(($walletTotal / $totalAssets) * 100, 1);
            $propertyPct = round(($propertyTotal / $totalAssets) * 100, 1);
            $projectPct = round(($projectTotal / $totalAssets) * 100, 1);
        } else {
            $walletPct = $propertyPct = $projectPct = 0.0;
        }

        // Store or update today's snapshot for trend
        $snapshotDate = now()->toDateString();
        FamilyWealthTrend::updateOrCreate(
            ['family_id' => $family->id, 'snapshot_date' => $snapshotDate],
            [
                'wallet_total' => $walletTotal,
                'property_total' => $propertyTotal,
                'project_total' => $projectTotal,
                'liability_total' => $liabilityTotal,
                'net_wealth' => $netWealth,
            ]
        );

        $trend = FamilyWealthTrend::where('family_id', $family->id)
            ->orderBy('snapshot_date')
            ->get(['snapshot_date', 'wallet_total', 'property_total', 'project_total', 'liability_total', 'net_wealth']);

        return view('families.wealth.index', [
            'family' => $family,
            'currency' => $currency,
            'overview' => [
                'wallet_total' => $walletTotal,
                'property_total' => $propertyTotal,
                'project_total' => $projectTotal,
                'liability_total' => $liabilityTotal,
                'net_wealth' => $netWealth,
            ],
            'allocation' => [
                'wallet_pct' => $walletPct,
                'property_pct' => $propertyPct,
                'project_pct' => $projectPct,
            ],
            'trend' => $trend,
        ]);
    }
}
