<?php

namespace App\Services\Search;

use App\Models\Expense;
use App\Models\Income;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Elasticsearch operations for famledger_transactions: index, delete, search, suggestions.
 * Every search MUST filter by household_id (family id string) — enforced here.
 */
class SearchService
{
    public function __construct(
        private readonly ElasticsearchClientFactory $factory,
        private readonly TransactionDocumentFactory $documents,
    ) {}

    public function isEnabled(): bool
    {
        return (bool) config('elasticsearch.enabled', false);
    }

    public function client(): Client
    {
        return $this->factory->make();
    }

    public function indexName(): string
    {
        return (string) config('elasticsearch.index_transactions', 'famledger_transactions');
    }

    /**
     * Create index from database/elasticsearch JSON if it does not exist.
     *
     * @throws Throwable
     */
    public function ensureIndexExists(): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $client = $this->client();
        $index = $this->indexName();

        $exists = $client->indices()->exists(['index' => $index])->asBool();
        if ($exists) {
            return true;
        }

        $path = database_path('elasticsearch/famledger_transactions.json');
        $body = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $client->indices()->create([
            'index' => $index,
            'body' => $body,
        ]);

        return true;
    }

    public function indexDocument(string $id, array $body): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            $this->ensureIndexExists();
            $this->client()->index([
                'index' => $this->indexName(),
                'id' => $id,
                'body' => $body,
                'refresh' => false,
            ]);
        } catch (ClientResponseException|ServerResponseException $e) {
            Log::error('Elasticsearch index failed', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteDocument(string $id): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            $this->client()->delete([
                'index' => $this->indexName(),
                'id' => $id,
                'refresh' => false,
            ]);
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return;
            }
            Log::warning('Elasticsearch delete failed', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $filters  merged API + NL parser
     * @return array{hits: array<int, array<string, mixed>>, total: int, aggregations: array<string, mixed>, suggest: array<string, mixed>, took: int}
     */
    public function searchTransactions(string $householdId, string $queryText, array $filters = []): array
    {
        if (! $this->isEnabled()) {
            return [
                'hits' => [],
                'total' => 0,
                'aggregations' => [],
                'suggest' => [],
                'took' => 0,
            ];
        }

        $page = max(1, (int) ($filters['page'] ?? 1));
        $size = min(
            (int) config('elasticsearch.search_size_max', 50),
            max(1, (int) ($filters['per_page'] ?? config('elasticsearch.search_size_default', 15)))
        );
        $from = ($page - 1) * $size;

        $sort = $filters['sort'] ?? 'date_desc';
        $sortClause = $this->buildSort($sort);

        $must = [];
        $filter = [];

        // CRITICAL: household isolation
        $filter[] = ['term' => ['household_id' => (string) $householdId]];

        if (! empty($filters['type'])) {
            $filter[] = ['term' => ['type' => (string) $filters['type']]];
        }
        if (! empty($filters['category'])) {
            $filter[] = ['term' => ['category' => (string) $filters['category']]];
        }
        if (! empty($filters['person'])) {
            $filter[] = ['term' => ['person_keyword' => (string) $filters['person']]];
        }
        if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
            $range = ['date' => []];
            if (! empty($filters['date_from'])) {
                $range['date']['gte'] = (string) $filters['date_from'];
            }
            if (! empty($filters['date_to'])) {
                $range['date']['lte'] = (string) $filters['date_to'];
            }
            $filter[] = ['range' => $range];
        }
        if (isset($filters['amount_gte']) || isset($filters['amount_lte'])) {
            $range = ['amount' => []];
            if (isset($filters['amount_gte'])) {
                $range['amount']['gte'] = (float) $filters['amount_gte'];
            }
            if (isset($filters['amount_lte'])) {
                $range['amount']['lte'] = (float) $filters['amount_lte'];
            }
            $filter[] = ['range' => $range];
        }

        $text = trim($queryText);
        $cleanText = str_replace([',', ' '], '', $text);
        if ($text !== '') {
            $should = $this->buildTransactionTextShouldClauses($text, $cleanText);

            $must[] = [
                'bool' => [
                    'should' => $should,
                    'minimum_should_match' => 1,
                ],
            ];
        }

        if (! empty($filters['category_hint'])) {
            $hint = mb_strtolower((string) $filters['category_hint']);
            $must[] = [
                'wildcard' => [
                    'category' => [
                        'value' => '*'.$hint.'*',
                        'case_insensitive' => true,
                    ],
                ],
            ];
        }
        if (! empty($filters['person_hint'])) {
            $hint = mb_strtolower((string) $filters['person_hint']);
            $must[] = [
                'wildcard' => [
                    'person_keyword' => [
                        'value' => '*'.str_replace(['*', '?'], '', $hint).'*',
                        'case_insensitive' => true,
                    ],
                ],
            ];
        }

        $body = [
            'from' => $from,
            'size' => $size,
            'track_total_hits' => true,
            'sort' => $sortClause,
            'query' => [
                'bool' => [
                    'must' => $must !== [] ? $must : [['match_all' => (object) []]],
                    'filter' => $filter,
                ],
            ],
            'highlight' => [
                'pre_tags' => ['<mark>'],
                'post_tags' => ['</mark>'],
                'fields' => [
                    'title' => new \stdClass,
                    'description' => new \stdClass,
                    'category' => new \stdClass,
                    'person' => new \stdClass,
                ],
            ],
            'aggs' => [
                'total_amount' => ['sum' => ['field' => 'amount']],
                'income_total' => [
                    'filter' => ['term' => ['type' => 'income']],
                    'aggs' => [
                        'sum_amount' => ['sum' => ['field' => 'amount']],
                    ],
                ],
                'expense_total' => [
                    'filter' => ['term' => ['type' => 'expense']],
                    'aggs' => [
                        'sum_amount' => ['sum' => ['field' => 'amount']],
                    ],
                ],
            ],
        ];

        if ($text !== '') {
            $body['suggest'] = [
                'did_you_mean' => [
                    'text' => $text,
                    'phrase' => [
                        'field' => 'title',
                        'size' => 1,
                        'gram_size' => 2,
                        'direct_generator' => [
                            [
                                'field' => 'title',
                                'suggest_mode' => 'popular',
                            ],
                        ],
                    ],
                ],
            ];
        }

        $cacheKey = null;
        if (! empty($filters['use_cache']) && config('elasticsearch.cache_ttl', 0) > 0) {
            $cacheKey = 'es_search:'.md5(json_encode([$householdId, $body], JSON_THROW_ON_ERROR));
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        try {
            $this->ensureIndexExists();
            $response = $this->client()->search([
                'index' => $this->indexName(),
                'body' => $body,
            ]);
        } catch (Throwable $e) {
            Log::error('Elasticsearch search failed', ['error' => $e->getMessage()]);

            return $this->databaseFallbackSearch($householdId, $queryText, $filters, $from, $size, $e->getMessage());
        }

        $raw = $response->asArray();
        $hits = [];
        foreach ($raw['hits']['hits'] ?? [] as $hit) {
            $row = $hit['_source'] ?? [];
            $row['_id'] = $hit['_id'] ?? null;
            $row['_score'] = $hit['_score'] ?? null;
            $row['highlight'] = $hit['highlight'] ?? [];
            $hits[] = $row;
        }

        $total = (int) (($raw['hits']['total']['value'] ?? $raw['hits']['total']) ?? 0);
        $aggs = $raw['aggregations'] ?? [];
        $suggest = $raw['suggest'] ?? [];

        $out = [
            'hits' => $hits,
            'total' => $total,
            'aggregations' => $aggs,
            'suggest' => $suggest,
            'took' => (int) ($raw['took'] ?? 0),
        ];

        if ($cacheKey !== null) {
            Cache::put($cacheKey, $out, (int) config('elasticsearch.cache_ttl', 60));
        }

        return $out;
    }

    /**
     * Lightweight autocomplete: edge_ngram field + category/person/tags/currency + wildcards.
     *
     * @return array<int, array{text: string, type: string}>
     */
    public function suggestDocuments(string $householdId, string $prefix, int $limit = 8): array
    {
        if (! $this->isEnabled() || mb_strlen(trim($prefix)) < 2) {
            return [];
        }

        $prefix = trim($prefix);

        $filter = [['term' => ['household_id' => (string) $householdId]]];

        $should = [
            [
                'multi_match' => [
                    'query' => $prefix,
                    'type' => 'bool_prefix',
                    'fields' => [
                        'title.autocomplete^2',
                        'title^1.5',
                        'description',
                    ],
                ],
            ],
            [
                'multi_match' => [
                    'query' => $prefix,
                    'fields' => ['description'],
                    'type' => 'phrase_prefix',
                ],
            ],
        ];

        if (mb_strlen($prefix) <= 96) {
            $wc = $this->elasticsearchWildcardContains($prefix);
            $should[] = ['wildcard' => ['person_keyword' => ['value' => $wc, 'case_insensitive' => true]]];
            $should[] = ['wildcard' => ['person' => ['value' => $wc, 'case_insensitive' => true]]];
            $should[] = ['wildcard' => ['category' => ['value' => $wc, 'case_insensitive' => true]]];
            $should[] = ['wildcard' => ['tags' => ['value' => $wc, 'case_insensitive' => true]]];
        }
        if (mb_strlen($prefix) === 3 && ctype_alpha($prefix)) {
            $should[] = ['term' => ['currency_code' => ['value' => mb_strtoupper($prefix), 'case_insensitive' => true]]];
        }

        $body = [
            'size' => $limit,
            '_source' => ['title', 'category', 'person', 'type', 'amount', 'date'],
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'bool' => [
                                'should' => $should,
                                'minimum_should_match' => 1,
                            ],
                        ],
                    ],
                    'filter' => $filter,
                ],
            ],
        ];

        try {
            $this->ensureIndexExists();
            $response = $this->client()->search([
                'index' => $this->indexName(),
                'body' => $body,
            ]);
        } catch (Throwable $e) {
            Log::warning('Elasticsearch suggest failed', ['error' => $e->getMessage()]);

            return [];
        }

        $raw = $response->asArray();
        $out = [];
        foreach ($raw['hits']['hits'] ?? [] as $hit) {
            $s = $hit['_source'] ?? [];
            $text = (string) ($s['title'] ?? '');
            if ($text === '') {
                continue;
            }
            $out[] = [
                'text' => $text,
                'type' => 'transaction',
                'meta' => $s,
            ];
        }

        return $out;
    }

    /**
     * Full-text search clauses for transaction documents: analyzed fields + keyword wildcards
     * + optional amount / record id / currency matches.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildTransactionTextShouldClauses(string $text, string $cleanText): array
    {
        // category / person / tags are keyword fields: analyzed multi_match does not match
        // substrings (e.g. "User" inside a full name). Wildcard should clauses fix that.
        $should = [
            [
                'multi_match' => [
                    'query' => $text,
                    'fields' => [
                        'title^4',
                        'title.autocomplete^2.5',
                        'description^2',
                    ],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                    'operator' => 'or',
                ],
            ],
            [
                'multi_match' => [
                    'query' => $text,
                    'fields' => ['title^2', 'description'],
                    'type' => 'cross_fields',
                    'operator' => 'or',
                ],
            ],
            [
                'multi_match' => [
                    'query' => $text,
                    'fields' => ['title', 'description'],
                    'type' => 'phrase_prefix',
                ],
            ],
            [
                'multi_match' => [
                    'query' => $text,
                    'fields' => ['description'],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                ],
            ],
            [
                'simple_query_string' => [
                    'query' => $text,
                    'fields' => ['title^2', 'description'],
                    'default_operator' => 'and',
                    'flags' => 'OR|AND|NOT|PREFIX|PHRASE',
                    'lenient' => true,
                ],
            ],
        ];

        if (mb_strlen($text) <= 120) {
            $wc = $this->elasticsearchWildcardContains($text);
            $should[] = [
                'wildcard' => [
                    'person_keyword' => [
                        'value' => $wc,
                        'case_insensitive' => true,
                    ],
                ],
            ];
            $should[] = [
                'wildcard' => [
                    'person' => [
                        'value' => $wc,
                        'case_insensitive' => true,
                    ],
                ],
            ];
            $should[] = [
                'wildcard' => [
                    'category' => [
                        'value' => $wc,
                        'case_insensitive' => true,
                    ],
                ],
            ];
            $should[] = [
                'wildcard' => [
                    'tags' => [
                        'value' => $wc,
                        'case_insensitive' => true,
                    ],
                ],
            ];
        }

        if (mb_strlen($text) === 3 && ctype_alpha($text)) {
            $should[] = [
                'term' => [
                    'currency_code' => [
                        'value' => mb_strtoupper($text),
                        'case_insensitive' => true,
                    ],
                ],
            ];
        }

        // Pure numeric: match amount (exact-ish) and/or internal record id
        if (preg_match('/^-?\d+(\.\d{1,4})?$/', $cleanText)) {
            $n = (float) $cleanText;
            if ($n > 0 && $n < 1e15) {
                // Expanding search window slightly for better fuzzy finding of numbers
                $eps = max(1.0, abs($n) * 0.005);
                $should[] = [
                    'range' => [
                        'amount' => [
                            'gte' => $n - $eps,
                            'lte' => $n + $eps,
                        ],
                    ],
                ];
            }
        }
        if (preg_match('/^\d{1,9}$/', $cleanText)) {
            $id = (int) $cleanText;
            $len = strlen($cleanText);
            // Avoid treating calendar years (e.g. 2024) as internal record ids
            if ($id > 0 && ! ($len === 4 && $id >= 1900 && $id <= 2100)) {
                $should[] = ['term' => ['record_id' => $id]];
            }
        }

        return $should;
    }

    /**
     * Substring match on keyword-mapped fields (escapes Elasticsearch wildcard metacharacters).
     */
    private function elasticsearchWildcardContains(string $term): string
    {
        $escaped = addcslashes($term, '\\*?');

        return '*'.$escaped.'*';
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildSort(string $sort): array
    {
        return match ($sort) {
            'date_asc' => [['date' => 'asc'], ['_score' => 'desc']],
            'amount_desc' => [['amount' => 'desc'], ['date' => 'desc']],
            'amount_asc' => [['amount' => 'asc'], ['date' => 'desc']],
            default => [['date' => 'desc'], ['_score' => 'desc']],
        };
    }

    /**
     * Fallback algorithm using MySQL querying if Elasticsearch is unreachable.
     */
    private function databaseFallbackSearch(string $householdId, string $queryText, array $filters, int $from, int $size, string $errorObj): array
    {
        $text = trim($queryText);
        $cleanText = str_replace([',', ' '], '', $text);
        
        $incQ = Income::query()->where('family_id', $householdId);
        $expQ = Expense::query()->where('family_id', $householdId);
        
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'expense') $incQ->whereRaw('1 = 0');
            if ($filters['type'] === 'income') $expQ->whereRaw('1 = 0');
        }
        
        // Date filters
        if (!empty($filters['date_from'])) {
            $incQ->where(function($q) use ($filters) {
                $q->where('received_date', '>=', $filters['date_from'])->orWhere(function($sq) use ($filters) {
                    $sq->whereNull('received_date')->whereDate('created_at', '>=', $filters['date_from']);
                });
            });
            $expQ->where(function($q) use ($filters) {
                $q->where('expense_date', '>=', $filters['date_from'])->orWhere(function($sq) use ($filters) {
                    $sq->whereNull('expense_date')->whereDate('created_at', '>=', $filters['date_from']);
                });
            });
        }
        if (!empty($filters['date_to'])) {
            $incQ->where(function($q) use ($filters) {
                $q->where('received_date', '<=', $filters['date_to'])->orWhere(function($sq) use ($filters) {
                    $sq->whereNull('received_date')->whereDate('created_at', '<=', $filters['date_to']);
                });
            });
            $expQ->where(function($q) use ($filters) {
                $q->where('expense_date', '<=', $filters['date_to'])->orWhere(function($sq) use ($filters) {
                    $sq->whereNull('expense_date')->whereDate('created_at', '<=', $filters['date_to']);
                });
            });
        }
        
        // Amount filters
        if (isset($filters['amount_gte'])) {
            $incQ->where('amount', '>=', $filters['amount_gte']);
            $expQ->where('amount', '>=', $filters['amount_gte']);
        }
        if (isset($filters['amount_lte'])) {
            $incQ->where('amount', '<=', $filters['amount_lte']);
            $expQ->where('amount', '<=', $filters['amount_lte']);
        }

        // Textual fallback conditions
        if ($text !== '') {
            $dbLike = '%' . strtolower($text) . '%';
            $isNum = preg_match('/^-?\d+(\.\d{1,4})?$/', $cleanText);
            $n = $isNum ? (float) $cleanText : null;
            
            $eps = $n !== null ? max(1.0, abs($n) * 0.005) : 0;
            
            $incQ->where(function($q) use ($dbLike, $n, $eps) {
                $q->whereRaw('LOWER(source) LIKE ?', [$dbLike])
                  ->orWhereRaw('LOWER(notes) LIKE ?', [$dbLike]);
                if ($n !== null) {
                    $q->orWhereBetween('amount', [$n - $eps, $n + $eps]);
                }
            });
            
            $expQ->where(function($q) use ($dbLike, $n, $eps) {
                $q->whereRaw('LOWER(merchant) LIKE ?', [$dbLike])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$dbLike])
                  ->orWhereRaw('LOWER(reference) LIKE ?', [$dbLike]);
                if ($n !== null) {
                    $q->orWhereBetween('amount', [$n - $eps, $n + $eps]);
                }
            });
        }
        
        $incRows = clone $incQ;
        $expRows = clone $expQ;
        
        $incomes = $incRows->with(['category', 'receivedBy'])->get();
        $expenses = $expRows->with(['category', 'paidBy'])->get();
        
        $factory = app(TransactionDocumentFactory::class);
        $all = collect();
        foreach ($incomes as $i) $all->push($factory->fromIncome($i));
        foreach ($expenses as $e) $all->push($factory->fromExpense($e));
        
        // Sorting
        $sort = $filters['sort'] ?? 'date_desc';
        $all = match($sort) {
            'date_asc' => $all->sortBy('date'),
            'amount_desc' => $all->sortByDesc('amount'),
            'amount_asc' => $all->sortBy('amount'),
            default => $all->sortByDesc('date')
        };
        
        $total = $all->count();
        $paginated = $all->slice($from, $size)->values()->all();
        
        return [
            'hits' => $paginated,
            'total' => $total,
            'aggregations' => [
                'total_amount' => ['value' => $all->sum('amount')],
                'income_total' => ['sum_amount' => ['value' => $all->where('type', 'income')->sum('amount')]],
                'expense_total' => ['sum_amount' => ['value' => $all->where('type', 'expense')->sum('amount')]]
            ],
            'suggest' => [],
            'took' => 10,
            'error' => $errorObj . ' (Fell back to SQL search temporarily)',
        ];
    }
}
