<?php

namespace App\Services\Search;

use Carbon\Carbon;

/**
 * Heuristic natural-language → structured filters (regex + keyword mapping).
 * Does not call the database; callers may refine category/person against live data.
 */
class QueryParserService
{
    /** @var array<string, string> month name => 01-12 */
    private const MONTHS = [
        'january' => '01', 'jan' => '01',
        'february' => '02', 'feb' => '02',
        'march' => '03', 'mar' => '03',
        'april' => '04', 'apr' => '04',
        'may' => '05',
        'june' => '06', 'jun' => '06',
        'july' => '07', 'jul' => '07',
        'august' => '08', 'aug' => '08',
        'september' => '09', 'sep' => '09', 'sept' => '09',
        'october' => '10', 'oct' => '10',
        'november' => '11', 'nov' => '11',
        'december' => '12', 'dec' => '12',
    ];

    /**
     * @return array{
     *   text_for_match: string,
     *   type: ?string,
     *   category_hint: ?string,
     *   person_hint: ?string,
     *   date_from: ?string,
     *   date_to: ?string,
     *   amount_gte: ?float,
     *   amount_lte: ?float
     * }
     */
    public function parse(string $input, ?Carbon $now = null): array
    {
        $now = $now ?? Carbon::now();

        $working = mb_strtolower(trim($input));

        $result = [
            'text_for_match' => $working,
            'type' => null,
            'category_hint' => null,
            'person_hint' => null,
            'date_from' => null,
            'date_to' => null,
            'amount_gte' => null,
            'amount_lte' => null,
        ];

        // Transaction type
        if (preg_match('/\b(income|salary|deposit|received|earning|wage|payroll|credit\s+note|refund\s+received)\b/u', $working)) {
            $result['type'] = 'income';
        }
        if (preg_match('/\b(expense|expenses|spent|spending|purchase|paid|payment|bought|bill|bills|debit|withdrawal|transfer\s+out)\b/u', $working)) {
            $result['type'] = 'expense';
        }

        // Amount: above / below / between
        if (preg_match('/\b(?:above|over|more\s+than|greater\s+than|>)\s*([\d\s,\.]+)\b/u', $working, $m)) {
            $result['amount_gte'] = $this->parseAmount($m[1]);
            $working = preg_replace('/\b(?:above|over|more\s+than|greater\s+than|>)\s*[\d\s,\.]+\b/u', ' ', $working);
        }
        if (preg_match('/\b(?:below|under|less\s+than|<)\s*([\d\s,\.]+)\b/u', $working, $m)) {
            $result['amount_lte'] = $this->parseAmount($m[1]);
            $working = preg_replace('/\b(?:below|under|less\s+than|<)\s*[\d\s,\.]+\b/u', ' ', $working);
        }
        if (preg_match('/\b(?:between|from)\s*([\d\s,\.]+)\s+(?:and|to)\s*([\d\s,\.]+)\b/u', $working, $m)) {
            $result['amount_gte'] = $this->parseAmount($m[1]);
            $result['amount_lte'] = $this->parseAmount($m[2]);
            $working = preg_replace('/\b(?:between|from)\s*[\d\s,\.]+\s+(?:and|to)\s*[\d\s,\.]+\b/u', ' ', $working);
        }

        // Relative date ranges
        if (preg_match('/\blast\s+week\b/u', $working)) {
            $start = $now->copy()->subWeek()->startOfWeek();
            $end = $now->copy()->subWeek()->endOfWeek();
            $result['date_from'] = $start->toDateString();
            $result['date_to'] = $end->toDateString();
            $working = preg_replace('/\blast\s+week\b/u', ' ', $working);
        } elseif (preg_match('/\bthis\s+week\b/u', $working)) {
            $result['date_from'] = $now->copy()->startOfWeek()->toDateString();
            $result['date_to'] = $now->copy()->endOfWeek()->toDateString();
            $working = preg_replace('/\bthis\s+week\b/u', ' ', $working);
        } elseif (preg_match('/\blast\s+month\b/u', $working)) {
            $start = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $end = $now->copy()->subMonthNoOverflow()->endOfMonth();
            $result['date_from'] = $start->toDateString();
            $result['date_to'] = $end->toDateString();
            $working = preg_replace('/\blast\s+month\b/u', ' ', $working);
        } elseif (preg_match('/\bthis\s+month\b/u', $working)) {
            $result['date_from'] = $now->copy()->startOfMonth()->toDateString();
            $result['date_to'] = $now->copy()->endOfMonth()->toDateString();
            $working = preg_replace('/\bthis\s+month\b/u', ' ', $working);
        } elseif (preg_match('/\blast\s+year\b/u', $working)) {
            $y = (int) $now->year - 1;
            $result['date_from'] = sprintf('%d-01-01', $y);
            $result['date_to'] = sprintf('%d-12-31', $y);
            $working = preg_replace('/\blast\s+year\b/u', ' ', $working);
        }

        // Named month (current year), e.g. "in january"
        if (preg_match('/\b(?:in|during)\s+('.implode('|', array_keys(self::MONTHS)).')\b/u', $working, $m)) {
            $monthNum = self::MONTHS[$m[1]] ?? null;
            if ($monthNum !== null) {
                $y = (int) $now->year;
                $result['date_from'] = sprintf('%d-%s-01', $y, $monthNum);
                $last = Carbon::createFromDate($y, (int) $monthNum, 1)->endOfMonth()->toDateString();
                $result['date_to'] = $last;
            }
            $working = preg_replace('/\b(?:in|during)\s+('.implode('|', array_keys(self::MONTHS)).')\b/u', ' ', $working);
        }

        // Person: first name after to/for/from (single token; avoids swallowing "in January")
        if (preg_match('/\b(?:money\s+)?(?:sent\s+)?to\s+([a-z][a-z\-]{1,40})\b/u', $working, $m)) {
            $result['person_hint'] = trim($m[1]);
            $working = preg_replace('/\b(?:money\s+)?(?:sent\s+)?to\s+[a-z][a-z\-]{1,40}\b/u', ' ', $working);
        } elseif (preg_match('/\b(?:for|from)\s+([a-z][a-z\-]{1,40})\b/u', $working, $m)) {
            $result['person_hint'] = trim($m[1]);
            $working = preg_replace('/\b(?:for|from)\s+[a-z][a-z\-]{1,40}\b/u', ' ', $working);
        }

        // Category hints (common words → loose hint for match/query_string)
        $categoryKeywords = [
            'food' => 'food',
            'groceries' => 'food',
            'grocery' => 'food',
            'rent' => 'rent',
            'housing' => 'rent',
            'education' => 'education',
            'school' => 'education',
            'tuition' => 'education',
            'transport' => 'transport',
            'fuel' => 'transport',
            'medical' => 'medical',
            'health' => 'medical',
            'pharmacy' => 'medical',
            'utilities' => 'utilities',
            'electric' => 'utilities',
            'water' => 'utilities',
            'internet' => 'utilities',
            'insurance' => 'insurance',
            'entertainment' => 'entertainment',
            'subscription' => 'subscription',
            'childcare' => 'childcare',
            'charity' => 'charity',
            'tax' => 'tax',
            'fees' => 'fees',
        ];
        foreach ($categoryKeywords as $word => $hint) {
            if (preg_match('/\b'.preg_quote($word, '/').'\b/u', $working)) {
                $result['category_hint'] = $hint;
                $working = preg_replace('/\b'.preg_quote($word, '/').'\b/u', ' ', $working);
                break;
            }
        }

        $working = preg_replace('/\s+/u', ' ', trim($working));
        $working = trim($working, " \t\n\r\0\x0B,");

        // Drop empty noise tokens
        $working = preg_replace('/\b(transactions?|entries|records?|items?)\b/u', ' ', $working);
        $working = preg_replace('/\s+/u', ' ', trim($working));

        $result['text_for_match'] = $working;

        return $result;
    }

    private function parseAmount(string $raw): float
    {
        $n = str_replace([',', ' ', "\u{00a0}"], '', $raw);

        return (float) $n;
    }
}
