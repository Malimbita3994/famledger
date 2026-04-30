<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\SearchController as ApiSearchController;
use App\Http\Controllers\Concerns\BuildsSearchFilters;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Services\Search\FamilyEntitySearchService;
use App\Services\Search\QueryParserService;
use App\Services\Search\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Session-scoped account search UI (/account/search).
 */
class SearchController extends Controller
{
    use BuildsSearchFilters;

    public function index(Request $request, Family $family, SearchService $search, FamilyEntitySearchService $entitySearch, QueryParserService $parser): View
    {
        $this->authorize('view', $family);

        $qString = $this->scalarQuery($request, 'q', '');

        [$text, $filters] = $this->buildSearchFilters($request, $parser);

        $householdId = (string) $family->id;
        $result = $search->searchTransactions($householdId, $text, $filters);

        $currentMembership = FamilyMember::query()
            ->where('family_id', $family->id)
            ->where('user_id', $request->user()->id)
            ->with('role')
            ->first();
        $canManageMembers = $currentMembership && in_array($currentMembership->role->name ?? '', ['Owner', 'Co-owner', 'Co-Owner'], true);

        $entityResult = $entitySearch->search($family, $qString, $canManageMembers, $request->user());

        $didYouMean = null;
        $suggest = $result['suggest'] ?? [];
        if (isset($suggest['did_you_mean'][0]['options'][0]['text'])) {
            $didYouMean = (string) $suggest['did_you_mean'][0]['options'][0]['text'];
        }

        $err = $result['error'] ?? null;
        $errorForView = is_string($err) ? $err : (is_array($err) ? json_encode($err) : (is_scalar($err) && $err !== null ? (string) $err : null));

        $aggs = $result['aggregations'] ?? [];

        return view('search.index', [
            'family' => $family,
            'q' => $qString,
            'hits' => $result['hits'],
            'total' => $result['total'],
            'took' => $result['took'],
            'enabled' => $search->isEnabled(),
            'error' => $errorForView,
            'filters' => $filters,
            'parsed' => $parser->parse($qString),
            'totalAmount' => $aggs['total_amount']['value'] ?? null,
            'incomeTotal' => $aggs['income_total']['sum_amount']['value'] ?? null,
            'expenseTotal' => $aggs['expense_total']['sum_amount']['value'] ?? null,
            'didYouMean' => $didYouMean,
            'entityGroups' => $entityResult['groups'],
            'entityTotal' => $entityResult['total'],
        ]);
    }

    public function suggestions(Request $request, Family $family, SearchService $search, FamilyEntitySearchService $entitySearch): JsonResponse
    {
        $this->authorize('view', $family);

        return app(ApiSearchController::class)
            ->suggestions($request, $family, $search, $entitySearch);
    }
}
