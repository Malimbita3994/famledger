<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Family;
use App\Models\FamilyLiability;
use App\Models\FamilyWealthTrend;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyDepreciation;
use App\Models\PropertyValuation;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class WealthController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family): View
    {
        $this->authorizeFamilyMember($family);

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        // Wallet totals — balance is computed from transactions (no stored balance column)
        $wallets = Wallet::where('family_id', $family->id)
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->get(['id', 'type', 'initial_balance']);

        // Attach computed balance to each wallet
        $wallets->each(function (Wallet $w) {
            $w->balance = (float) ($w->initial_balance ?? 0)
                + (float) ($w->incomes_sum_amount ?? 0)
                - (float) ($w->expenses_sum_amount ?? 0)
                + (float) ($w->incoming_transfers_sum_amount ?? 0)
                - (float) ($w->outgoing_transfers_sum_amount ?? 0);
        });

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
            'wealthCharts' => $this->buildWealthChartsPayload($trend, $currency),
        ]);
    }

    /**
     * Series for ApexCharts: historical snapshots plus optional linear net-wealth projection.
     *
     * @param  Collection<int, FamilyWealthTrend>  $trend
     * @return array<string, mixed>
     */
    private function buildWealthChartsPayload(Collection $trend, string $currency): array
    {
        if ($trend->isEmpty()) {
            return ['hasData' => false];
        }

        $rows = $trend->values();
        $n = $rows->count();

        $historicalCategories = $rows->map(fn (FamilyWealthTrend $r) => $r->snapshot_date->format('M j'))->all();
        $netWealth = $rows->map(fn (FamilyWealthTrend $r) => round((float) $r->net_wealth, 2))->all();
        $wallet = $rows->map(fn (FamilyWealthTrend $r) => round((float) $r->wallet_total, 2))->all();
        $property = $rows->map(fn (FamilyWealthTrend $r) => round((float) $r->property_total, 2))->all();
        $project = $rows->map(fn (FamilyWealthTrend $r) => round((float) $r->project_total, 2))->all();
        $liability = $rows->map(fn (FamilyWealthTrend $r) => round((float) $r->liability_total, 2))->all();

        $projection = [
            'enabled' => false,
            'fullCategories' => $historicalCategories,
            'netActual' => $netWealth,
            'netForecast' => null,
            'daysAhead' => 0,
        ];

        if ($n >= 2) {
            $firstDate = $rows->first()->snapshot_date->copy()->startOfDay();
            $lastDate = $rows->last()->snapshot_date->copy()->startOfDay();
            $lastNet = (float) $rows->last()->net_wealth;

            $xs = $rows->map(fn (FamilyWealthTrend $r) => (float) $firstDate->diffInDays($r->snapshot_date->copy()->startOfDay()))->all();
            $ys = $netWealth;
            $sumX = array_sum($xs);
            $sumY = array_sum($ys);
            $sumXY = 0.0;
            $sumX2 = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $sumXY += $xs[$i] * $ys[$i];
                $sumX2 += $xs[$i] * $xs[$i];
            }
            $denom = $n * $sumX2 - $sumX * $sumX;
            $slope = abs($denom) > 1e-9 ? ($n * $sumXY - $sumX * $sumY) / $denom : 0.0;
            $intercept = ($sumY - $slope * $sumX) / $n;

            $projectionDays = 30;
            $futureCategories = [];
            $futureValues = [];
            for ($d = 1; $d <= $projectionDays; $d++) {
                $futureDate = $lastDate->copy()->addDays($d);
                $x = (float) $firstDate->diffInDays($futureDate->copy()->startOfDay());
                $futureCategories[] = $futureDate->format('M j');
                $futureValues[] = round($intercept + $slope * $x, 2);
            }

            $fullCategories = array_merge($historicalCategories, $futureCategories);
            $netActualPadded = array_merge($netWealth, array_fill(0, $projectionDays, null));
            $forecastPadded = array_merge(
                array_fill(0, $n - 1, null),
                [$lastNet],
                $futureValues
            );

            $projection = [
                'enabled' => true,
                'fullCategories' => $fullCategories,
                'netActual' => $netActualPadded,
                'netForecast' => $forecastPadded,
                'daysAhead' => $projectionDays,
                'slopePerDay' => round($slope, 4),
            ];
        }

        return [
            'hasData' => true,
            'currency' => $currency,
            'categories' => $historicalCategories,
            'netWealth' => $netWealth,
            'wallet' => $wallet,
            'property' => $property,
            'project' => $project,
            'liability' => $liability,
            'projection' => $projection,
        ];
    }

    /**
     * Export wealth overview as PDF.
     */
    public function exportPdf(Request $request, Family $family): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorizeFamilyMember($family);

        $currency = $family->currency_code ?? config('currencies.default', 'TZS');

        $wallets = Wallet::where('family_id', $family->id)
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->get(['id', 'type', 'initial_balance']);

        $wallets->each(function (Wallet $w) {
            $w->balance = (float) ($w->initial_balance ?? 0)
                + (float) ($w->incomes_sum_amount ?? 0)
                - (float) ($w->expenses_sum_amount ?? 0)
                + (float) ($w->incoming_transfers_sum_amount ?? 0)
                - (float) ($w->outgoing_transfers_sum_amount ?? 0);
        });

        $projectWallets = $wallets->where('type', 'project_fund');
        $otherWallets = $wallets->where('type', '!=', 'project_fund');
        $walletTotal = (float) $otherWallets->sum('balance');

        $properties = Property::where('family_id', $family->id)->get(['id', 'purchase_price', 'current_estimated_value']);
        $propertyIds = $properties->pluck('id')->all();

        $latestValuations = PropertyValuation::whereIn('property_id', $propertyIds)
            ->select('property_id', 'estimated_value', 'valuation_date')
            ->orderBy('valuation_date', 'desc')->get()->groupBy('property_id')->map->first();

        $latestDepreciations = PropertyDepreciation::whereIn('property_id', $propertyIds)
            ->select('property_id', 'year', 'book_value')
            ->orderBy('year', 'desc')->get()->groupBy('property_id')->map->first();

        $propertyTotal = 0.0;
        foreach ($properties as $property) {
            $latestVal = $latestValuations[$property->id] ?? null;
            $latestDep = $latestDepreciations[$property->id] ?? null;
            $valuation = $latestVal ? (float) $latestVal->estimated_value : (float) ($property->current_estimated_value ?? 0);
            $propertyTotal += $latestDep ? (float) $latestDep->book_value : ($valuation ?: (float) ($property->purchase_price ?? 0));
        }

        $projectTotal = (float) $projectWallets->sum(fn (Wallet $w) => $w->balance);
        $liabilityTotal = (float) FamilyLiability::where('family_id', $family->id)->where('status', '!=', 'closed')->sum('outstanding_balance');
        $netWealth = $walletTotal + $propertyTotal + $projectTotal - $liabilityTotal;
        $totalAssets = $walletTotal + $propertyTotal + $projectTotal;

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('families.wealth.pdf', [
            'family' => $family,
            'currency' => $currency,
            'overview' => ['wallet_total' => $walletTotal, 'property_total' => $propertyTotal, 'project_total' => $projectTotal, 'liability_total' => $liabilityTotal, 'net_wealth' => $netWealth],
            'allocation' => [
                'wallet_pct' => $totalAssets > 0 ? round(($walletTotal / $totalAssets) * 100, 1) : 0.0,
                'property_pct' => $totalAssets > 0 ? round(($propertyTotal / $totalAssets) * 100, 1) : 0.0,
                'project_pct' => $totalAssets > 0 ? round(($projectTotal / $totalAssets) * 100, 1) : 0.0,
            ],
            'trend' => FamilyWealthTrend::where('family_id', $family->id)->orderBy('snapshot_date')->get(),
            'generatedAt' => now()->format('Y-m-d H:i'),
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('wealth-'.$family->id.'-'.now()->format('Y-m-d').'.pdf');
    }
}
