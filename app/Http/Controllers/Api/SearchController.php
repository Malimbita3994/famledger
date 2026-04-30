<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\BuildsSearchFilters;
use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\IncomeCategory;
use App\Models\User;
use App\Services\Search\FamilyEntitySearchService;
use App\Services\Search\QueryParserService;
use App\Services\Search\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    use BuildsSearchFilters;

    /**
     * GET /api/families/{family}/search
     */
    public function index(Request $request, Family $family, SearchService $search, QueryParserService $parser): JsonResponse
    {
        $this->authorize('view', $family);

        [$text, $filters] = $this->buildSearchFilters($request, $parser);

        $householdId = (string) $family->id;
        $result = $search->searchTransactions($householdId, $text, $filters);

        if (! $search->isEnabled()) {
            return response()->json([
                'enabled' => false,
                'message' => 'Elasticsearch is disabled. Set ELASTICSEARCH_ENABLED=true and configure hosts.',
                'data' => [
                    'hits' => [],
                    'total' => 0,
                    'page' => (int) $filters['page'],
                    'per_page' => (int) $filters['per_page'],
                ],
            ]);
        }

        $this->rememberRecentSearch($request, $family->id, (string) $request->query('q', ''));

        return response()->json([
            'enabled' => true,
            'data' => [
                'hits' => $result['hits'],
                'total' => $result['total'],
                'took_ms' => $result['took'],
                'page' => (int) $filters['page'],
                'per_page' => (int) $filters['per_page'],
                'aggregations' => [
                    'total_amount' => $result['aggregations']['total_amount']['value'] ?? null,
                    'income_total' => $result['aggregations']['income_total']['sum_amount']['value'] ?? null,
                    'expense_total' => $result['aggregations']['expense_total']['sum_amount']['value'] ?? null,
                ],
                'did_you_mean' => $this->extractDidYouMean($result['suggest'] ?? []),
            ],
            'parsed' => $request->boolean('nl', true) ? $parser->parse((string) $request->query('q', '')) : null,
        ]);
    }

    /**
     * Spec-friendly alias: GET /api/search?family_id=&q=
     */
    public function globalSearch(Request $request, SearchService $search, QueryParserService $parser): JsonResponse
    {
        $familyId = $request->query('family_id', $request->query('household_id'));
        if ($familyId === null || $familyId === '') {
            return response()->json([
                'message' => 'Query parameter family_id or household_id is required for scoped search.',
            ], 422);
        }

        $family = Family::query()->findOrFail((int) $familyId);

        return $this->index($request, $family, $search, $parser);
    }

    /**
     * GET /api/families/{family}/search/suggestions
     */
    public function suggestions(Request $request, Family $family, SearchService $search, FamilyEntitySearchService $entitySearch): JsonResponse
    {
        $this->authorize('view', $family);

        $q = trim((string) $request->query('q', ''));
        $householdId = (string) $family->id;

        $categories = $this->familyCategoryNames($family->id);
        $persons = $this->familyMemberNames($family->id);

        $docSuggestions = $search->isEnabled()
            ? $search->suggestDocuments($householdId, $q, 6)
            : [];

        $recent = $this->recentSearches($request, $family->id);

        $catMatches = $q === '' ? [] : array_values(array_filter($categories, fn ($c) => mb_stripos($c, $q) !== false));
        $personMatches = $q === '' ? [] : array_values(array_filter($persons, fn ($p) => mb_stripos($p, $q) !== false));

        $settingsRows = mb_strlen($q) >= 2
            ? $entitySearch->matchSettingsHubEntries($family, $request->user(), $q)
            : [];
        $settings = array_map(static fn (array $row) => [
            'title' => $row['title'],
            'subtitle' => $row['subtitle'] ?? null,
            'url' => $row['url'],
        ], array_slice($settingsRows, 0, 8));

        return response()->json([
            'settings' => $settings,
            'categories' => array_slice($catMatches, 0, 8),
            'persons' => array_slice($personMatches, 0, 8),
            'transactions' => $docSuggestions,
            'recent_searches' => $recent,
        ]);
    }

    /**
     * @param  array<string, mixed>  $suggest
     */
    private function extractDidYouMean(array $suggest): ?string
    {
        $opt = $suggest['did_you_mean'][0]['options'][0]['text'] ?? null;

        return is_string($opt) && $opt !== '' ? $opt : null;
    }

    private function rememberRecentSearch(Request $request, int $familyId, string $q): void
    {
        $user = $request->user();
        if (! $user || mb_strlen(trim($q)) < 2) {
            return;
        }

        $key = $this->recentKey($user->id, $familyId);
        $list = Cache::get($key, []);
        if (! is_array($list)) {
            $list = [];
        }
        $q = trim($q);
        $list = array_values(array_unique(array_merge([$q], $list)));
        $list = array_slice($list, 0, 15);
        Cache::put($key, $list, now()->addDays(30));
    }

    /**
     * @return array<int, string>
     */
    private function recentSearches(Request $request, int $familyId): array
    {
        $user = $request->user();
        if (! $user) {
            return [];
        }

        $key = $this->recentKey($user->id, $familyId);
        $list = Cache::get($key, []);

        return is_array($list) ? array_slice($list, 0, 10) : [];
    }

    private function recentKey(int $userId, int $familyId): string
    {
        return "search_recent:{$userId}:family:{$familyId}";
    }

    /**
     * @return array<int, string>
     */
    private function familyCategoryNames(int $familyId): array
    {
        $income = IncomeCategory::query()->where('family_id', $familyId)->pluck('name')->all();
        $incomeDefaults = IncomeCategory::query()->whereNull('family_id')->pluck('name')->all();
        $expense = ExpenseCategory::query()->where('family_id', $familyId)->pluck('name')->all();
        $expenseDefaults = ExpenseCategory::query()->whereNull('family_id')->pluck('name')->all();

        return array_values(array_unique(array_merge($income, $incomeDefaults, $expense, $expenseDefaults)));
    }

    /**
     * @return array<int, string>
     */
    private function familyMemberNames(int $familyId): array
    {
        $members = FamilyMember::query()
            ->where('family_id', $familyId)
            ->with('user')
            ->get();

        $names = [];
        foreach ($members as $m) {
            if (is_string($m->member_name) && $m->member_name !== '') {
                $names[] = $m->member_name;
            } elseif ($m->user instanceof User) {
                $names[] = $m->user->name;
            }
        }

        return array_values(array_unique(array_filter($names)));
    }
}
