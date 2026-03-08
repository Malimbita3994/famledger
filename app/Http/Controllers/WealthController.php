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

        // Use Family model methods for wealth calculations
        $walletTotal = $family->getWalletWealthTotal();
        $propertyTotal = $family->getPropertyWealthTotal();
        $projectTotal = $family->getProjectWealthTotal();
        $liabilityTotal = $family->getLiabilityTotal();
        $netWealth = $family->getNetWealth();

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
