<?php

namespace App\Http\Controllers;

use App\Models\NotificationFaq;
use App\Models\NotificationSupportContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Public search suggestions for the marketing landing page (same JSON shape as family search API).
 * Ranks by relevance so specific FAQs and sections surface above generic “browse all” links.
 */
class LandingSearchController extends Controller
{
    private const MAX_SUGGESTIONS = 24;

    private const MIN_SCORE = 8.0;

    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'settings' => [],
                'categories' => [],
                'persons' => [],
                'transactions' => [],
                'recent_searches' => [],
            ]);
        }

        $base = rtrim((string) route('landing'), '/');
        $faqHubUrl = $base.'#faq';

        $scored = [];

        foreach ($this->accountLinksCatalog() as $item) {
            $score = $this->scoreAccountLinkMatch($q, $item['labels']);
            if ($score >= self::MIN_SCORE) {
                $scored[] = ['score' => $score, 'row' => $item['row']];
            }
        }

        foreach ($this->faqCatalog($base) as $item) {
            $score = $this->scoreFaqMatch($q, $item['question'], $item['answer']);
            if ($score >= self::MIN_SCORE) {
                $scored[] = ['score' => $score, 'row' => $item['row']];
            }
        }

        foreach ($this->sectionCatalog($base) as $item) {
            $score = $this->scoreSectionMatch($q, $item['labels']);
            if ($score >= self::MIN_SCORE) {
                $scored[] = ['score' => $score, 'row' => $item['row']];
            }
        }

        foreach ($this->supportCatalog($base) as $item) {
            $score = $this->scoreTextMatch($q, $item['title'], $item['body']);
            if ($score >= self::MIN_SCORE) {
                $scored[] = ['score' => $score, 'row' => $item['row']];
            }
        }

        $hubScore = $this->scoreFaqHubMatch($q);
        if ($hubScore >= self::MIN_SCORE) {
            $scored[] = [
                'score' => $hubScore,
                'row' => [
                    'title' => __('Frequently Asked Questions'),
                    'subtitle' => __('Browse all topics when you want the full list — or pick a matching question above.'),
                    'url' => $faqHubUrl,
                ],
            ];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $seenUrls = [];
        $out = [];
        foreach ($scored as $item) {
            $url = $item['row']['url'] ?? '';
            if ($url === '' || isset($seenUrls[$url])) {
                continue;
            }
            $seenUrls[$url] = true;
            $out[] = $item['row'];
            if (count($out) >= self::MAX_SUGGESTIONS) {
                break;
            }
        }

        if ($out === []) {
            $out[] = [
                'title' => __('Frequently Asked Questions'),
                'subtitle' => __('No exact match — open the FAQ to explore by topic.'),
                'url' => $faqHubUrl,
            ];
        }

        $settings = array_map(static fn (array $row) => [
            'title' => $row['title'],
            'subtitle' => $row['subtitle'] ?? null,
            'url' => $row['url'],
        ], $out);

        return response()->json([
            'settings' => $settings,
            'categories' => [],
            'persons' => [],
            'transactions' => [],
            'recent_searches' => [],
        ]);
    }

    /**
     * @return list<array{question: string, answer: string, row: array{title: string, subtitle: ?string, url: string}}>
     */
    private function faqCatalog(string $base): array
    {
        $out = [];
        $landingFaqs = NotificationFaq::query()->active()->ordered()->get();
        $groups = $landingFaqs->groupBy(function (NotificationFaq $faq) {
            return trim((string) ($faq->group_label ?? ''));
        })->sortBy(fn ($items) => $items->min('sort_order'));

        foreach ($groups as $groupFaqs) {
            foreach ($groupFaqs as $faq) {
                $qPlain = strip_tags((string) $faq->question);
                $aPlain = strip_tags((string) $faq->answer);
                $out[] = [
                    'question' => $qPlain,
                    'answer' => $aPlain,
                    'row' => [
                        'title' => Str::limit($qPlain, 240),
                        'subtitle' => Str::limit($aPlain, 200),
                        'url' => $base.'#faqCollapse'.$faq->id,
                    ],
                ];
            }
        }

        return $out;
    }

    /**
     * Sign-in / registration — rank above FAQs that only mention words like “login” in passing.
     *
     * @return list<array{labels: list<string>, row: array{title: string, subtitle: ?string, url: string}}>
     */
    private function accountLinksCatalog(): array
    {
        return [
            [
                'labels' => [
                    'login', 'log in', 'sign in', 'signin', 'sign-in', 'sign on',
                    __('Sign in'), __('Log in'), __('Sign In'),
                ],
                'row' => [
                    'title' => __('Sign in to FamLedger'),
                    'subtitle' => __('Open the secure login page for your account.'),
                    'url' => route('login'),
                ],
            ],
            [
                'labels' => [
                    'register', 'sign up', 'signup', 'sign-up', 'create account', 'new account',
                    __('Register'), __('Sign up'),
                ],
                'row' => [
                    'title' => __('Create an account'),
                    'subtitle' => __('Register to start family accounting.'),
                    'url' => route('register'),
                ],
            ],
        ];
    }

    /**
     * @return list<array{labels: list<string>, row: array{title: string, subtitle: ?string, url: string}}>
     */
    private function sectionCatalog(string $base): array
    {
        return [
            [
                'labels' => [__('Home'), __('Hero and sign in'), 'home', 'hero', 'sign in', 'start'],
                'row' => [
                    'title' => __('Home'),
                    'subtitle' => __('Hero and sign in'),
                    'url' => $base.'#home',
                ],
            ],
            [
                'labels' => [
                    __('Accounting & features'),
                    __('Product overview'),
                    'accounting',
                    'features',
                    'wallets',
                    'ledger',
                    'budget',
                    'reports',
                    'products',
                ],
                'row' => [
                    'title' => __('Accounting & features'),
                    'subtitle' => __('Product overview'),
                    'url' => $base.'#feature',
                ],
            ],
            [
                'labels' => [__('About us'), __('Who we are'), 'about', 'team', 'company', 'story'],
                'row' => [
                    'title' => __('About us'),
                    'subtitle' => __('Who we are'),
                    'url' => $base.'#about',
                ],
            ],
            [
                'labels' => [__('Contact'), __('Get in touch'), 'contact', 'email', 'demo', 'reach', 'message'],
                'row' => [
                    'title' => __('Contact'),
                    'subtitle' => __('Get in touch'),
                    'url' => $base.'#contact',
                ],
            ],
        ];
    }

    /**
     * @return list<array{title: string, body: string, row: array{title: string, subtitle: ?string, url: string}}>
     */
    private function supportCatalog(string $base): array
    {
        $out = [];
        foreach (NotificationSupportContact::query()->active()->ordered()->get() as $sc) {
            $body = strip_tags((string) $sc->body);
            $out[] = [
                'title' => (string) $sc->title,
                'body' => $body,
                'row' => [
                    'title' => (string) $sc->title,
                    'subtitle' => Str::limit($body, 180),
                    'url' => $base.'#contact',
                ],
            ];
        }

        return $out;
    }

    private function scoreFaqMatch(string $query, string $questionPlain, string $answerPlain): float
    {
        $q = mb_strtolower(trim($query));
        $ques = mb_strtolower($questionPlain);
        $ans = mb_strtolower($answerPlain);

        $score = 0.0;

        if ($q !== '' && mb_strpos($ques, $q) !== false) {
            $score += 130.0;
            if (mb_strpos($ques, $q) === 0) {
                $score += 35.0;
            }
        } elseif ($q !== '' && mb_strpos($ans, $q) !== false) {
            $score += 48.0;
        }

        $tokens = $this->significantTokens($q);
        foreach ($tokens as $tok) {
            if (mb_strpos($ques, $tok) !== false) {
                $score += 22.0;
            } elseif (mb_strpos($ans, $tok) !== false) {
                $score += 9.0;
            }
        }

        if (count($tokens) >= 2 && $this->allTokensIn($tokens, $ques)) {
            $score += 55.0;
        } elseif (count($tokens) >= 2 && $this->allTokensIn($tokens, $ques.' '.$ans)) {
            $score += 28.0;
        }

        // Do not promote “login” / account words that appear only in an answer (e.g. “keep your login safe” under privacy).
        if ($this->shouldSuppressAnswerOnlyNavMatch($q, $ques, $ans)) {
            return 0.0;
        }

        return $score;
    }

    /**
     * When the user is clearly searching for sign-in / account actions, ignore FAQ rows unless the question is on-topic.
     */
    private function shouldSuppressAnswerOnlyNavMatch(string $qLower, string $ques, string $ans): bool
    {
        if (! $this->queryLooksLikeAccountNavigation($qLower)) {
            return false;
        }

        if (mb_strpos($ques, $qLower) !== false) {
            return false;
        }

        if (preg_match('/\b(login|sign\s*in|log\s*in|password|account|auth|register|sign\s*up|credentials)\b/u', $ques)) {
            return false;
        }

        foreach ($this->significantTokens($qLower) as $tok) {
            if (mb_strpos($ques, $tok) !== false) {
                return false;
            }
        }

        // Matched mainly from answer body (e.g. incidental “login” in a privacy answer).
        return mb_strpos($ans, $qLower) !== false
            || (count($this->significantTokens($qLower)) === 1 && mb_strpos($ans, $this->significantTokens($qLower)[0] ?? '') !== false);
    }

    private function queryLooksLikeAccountNavigation(string $qLower): bool
    {
        if (preg_match('/\b(login|log\s*in|sign\s*in|signin|password|register|sign\s*up|signup|credentials|sign\s*on)\b/u', $qLower)) {
            return true;
        }

        return false;
    }

    /**
     * @param  list<string>  $labels
     */
    private function scoreAccountLinkMatch(string $query, array $labels): float
    {
        $q = mb_strtolower(trim($query));
        if ($q === '') {
            return 0.0;
        }

        $best = 0.0;
        foreach ($labels as $label) {
            $l = mb_strtolower((string) $label);
            if ($l === '') {
                continue;
            }
            if ($q === $l) {
                return 280.0;
            }
            if (mb_strlen($l) >= 3 && mb_strpos($q, $l) !== false) {
                $best = max($best, 250.0);
            }
            if (mb_strlen($q) >= 3 && mb_strpos($l, $q) !== false) {
                $best = max($best, 245.0);
            }
        }

        $combined = mb_strtolower(implode("\n", array_map(static fn ($x) => (string) $x, $labels)));
        foreach ($this->significantTokens($q) as $tok) {
            if (mb_strpos($combined, $tok) !== false) {
                $best = max($best, 230.0);
            }
        }

        return $best;
    }

    /**
     * @param  list<string>  $labels
     */
    private function scoreSectionMatch(string $query, array $labels): float
    {
        $q = mb_strtolower(trim($query));
        $score = 0.0;

        foreach ($labels as $label) {
            $l = mb_strtolower((string) $label);
            if ($q !== '' && $l !== '' && mb_strpos($l, $q) !== false) {
                $score += 55.0;
            }
            if ($q !== '' && $l !== '' && mb_strpos($q, $l) !== false && mb_strlen($l) >= 3) {
                $score += 45.0;
            }
        }

        $combined = mb_strtolower(implode(' ', array_map(static fn ($x) => (string) $x, $labels)));
        foreach ($this->significantTokens($q) as $tok) {
            if (mb_strpos($combined, $tok) !== false) {
                $score += 14.0;
            }
        }

        return $score;
    }

    private function scoreTextMatch(string $query, string $title, string $body): float
    {
        return $this->scoreFaqMatch($query, $title, $body);
    }

    private function scoreFaqHubMatch(string $query): float
    {
        $q = mb_strtolower(trim($query));
        $score = 0.0;

        $needles = [
            'faq', 'faqs', 'question', 'questions', 'ask', 'answer', 'answers', 'help', 'how do', 'what is', 'why ',
            'explain', 'clarif', 'guide', 'detail', 'details', 'more info', 'learn', 'understand', 'documentation',
        ];
        foreach ($needles as $n) {
            if ($n !== '' && mb_strpos($q, $n) !== false) {
                $score += 28.0;
            }
        }

        if (preg_match('/\b(how|what|why|when|where|who)\b/u', $q)) {
            $score += 22.0;
        }

        return min($score, 72.0);
    }

    /**
     * @return list<string>
     */
    private function significantTokens(string $query): array
    {
        $parts = preg_split('/\s+/u', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($parts, fn ($t) => mb_strlen($t) >= 2));
    }

    /**
     * @param  list<string>  $tokens
     */
    private function allTokensIn(array $tokens, string $haystackLower): bool
    {
        foreach ($tokens as $tok) {
            if (mb_strpos($haystackLower, $tok) === false) {
                return false;
            }
        }

        return true;
    }
}
