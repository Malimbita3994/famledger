<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Search\QueryParserService;
use Illuminate\Http\Request;

trait BuildsSearchFilters
{
    /**
     * Single query string; duplicate keys or [] params can make $request->query('x') an array — never cast that to string.
     */
    protected function scalarQuery(Request $request, string $key, string $default = ''): string
    {
        if (! $request->has($key)) {
            return $default;
        }
        $v = $request->query($key);
        if (is_array($v)) {
            return $default;
        }

        return (string) ($v ?? $default);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function buildSearchFilters(Request $request, QueryParserService $parser): array
    {
        $q = $this->scalarQuery($request, 'q', '');
        $useNl = $request->boolean('nl', true);
        $parsed = $parser->parse($q);

        $sort = $this->scalarQuery($request, 'sort', 'date_desc');
        $typeReq = $this->scalarQuery($request, 'type', '');
        $categoryReq = $this->scalarQuery($request, 'category', '');
        $personReq = $this->scalarQuery($request, 'person', '');
        $dateFrom = $this->scalarQuery($request, 'date_from', '');
        $dateTo = $this->scalarQuery($request, 'date_to', '');
        $amountGte = $this->scalarQuery($request, 'amount_gte', '');
        $amountLte = $this->scalarQuery($request, 'amount_lte', '');

        $filters = [
            'page' => $request->integer('page', 1),
            'per_page' => min(
                (int) config('elasticsearch.search_size_max', 50),
                max(1, $request->integer('per_page', (int) config('elasticsearch.search_size_default', 15)))
            ),
            'sort' => $sort !== '' ? $sort : 'date_desc',
            'type' => $typeReq !== '' ? $typeReq : ($useNl ? $parsed['type'] : null),
            'category' => $categoryReq !== '' ? $categoryReq : null,
            'person' => $personReq !== '' ? $personReq : null,
            'date_from' => $dateFrom !== '' ? $dateFrom : ($useNl ? $parsed['date_from'] : null),
            'date_to' => $dateTo !== '' ? $dateTo : ($useNl ? $parsed['date_to'] : null),
            'amount_gte' => $amountGte !== ''
                ? (float) $amountGte
                : ($useNl ? $parsed['amount_gte'] : null),
            'amount_lte' => $amountLte !== ''
                ? (float) $amountLte
                : ($useNl ? $parsed['amount_lte'] : null),
            'category_hint' => $useNl ? $parsed['category_hint'] : null,
            'person_hint' => $useNl ? $parsed['person_hint'] : null,
            'use_cache' => $request->boolean('cache', false),
        ];

        $searchText = $useNl ? $parsed['text_for_match'] : $q;

        return [$searchText, $filters];
    }
}
