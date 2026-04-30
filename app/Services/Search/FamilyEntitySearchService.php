<?php

namespace App\Services\Search;

use App\Models\Budget;
use App\Models\Family;
use App\Models\FamilyLiability;
use App\Models\FamilyMember;
use App\Models\Project;
use App\Models\Property;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Database-backed search across family-scoped records (people, projects, assets, etc.).
 * Complements Elasticsearch transaction search; always household-scoped.
 */
class FamilyEntitySearchService
{
    private const LIMIT = 12;

    /**
     * @return array{groups: array<string, list<array{kind: string, title: string, subtitle: ?string, url: string}>>, total: int}
     */
    public function search(Family $family, string $term, bool $canManageMembers, ?User $user = null): array
    {
        $term = trim($term);
        if ($term === '' || mb_strlen($term) < 2) {
            return ['groups' => [], 'total' => 0];
        }

        $pattern = $this->patternForLike($term);
        $tokens = $this->significantTokens($term);

        $groups = [];

        $settingsRows = $this->matchSettingsHubEntries($family, $user, $term);
        if ($settingsRows !== []) {
            $groups['settings'] = $settingsRows;
        }

        $members = FamilyMember::query()
            ->where('family_id', $family->id)
            ->where(function ($q) use ($pattern, $tokens) {
                $q->where(function ($inner) use ($pattern) {
                    $inner->where('member_name', 'like', $pattern)
                        ->orWhere('member_type', 'like', $pattern)
                        ->orWhereHas('user', function ($uq) use ($pattern) {
                            $uq->where('name', 'like', $pattern)
                                ->orWhere('email', 'like', $pattern);
                        })
                        ->orWhereHas('role', function ($rq) use ($pattern) {
                            $rq->where('name', 'like', $pattern)
                                ->orWhere('description', 'like', $pattern);
                        });
                });
                $this->orWhereTokenAndName($q, 'member_name', $tokens);
            })
            ->with(['user:id,name,email', 'role:id,name'])
            ->limit(self::LIMIT)
            ->get();

        if ($members->isNotEmpty()) {
            $groups['people'] = $members->map(function (FamilyMember $m) use ($canManageMembers) {
                $name = $m->user?->name ?? $m->member_name ?? __('Member');
                $email = $m->user?->email ?? '';
                $role = $m->role?->name ?? '';
                $subtitle = trim($role.($email !== '' ? ' · '.$email : ''));

                $url = $canManageMembers
                    ? route('families.members.edit', ['member' => $m->id])
                    : route('families.members.index');

                return [
                    'kind' => 'person',
                    'title' => $name,
                    'subtitle' => $subtitle !== '' ? $subtitle : null,
                    'url' => $url,
                ];
            })->all();
        }

        $projects = Project::query()
            ->where('family_id', $family->id)
            ->where(function ($q) use ($pattern, $tokens) {
                $q->where(function ($inner) use ($pattern) {
                    $inner->where('name', 'like', $pattern)
                        ->orWhere('description', 'like', $pattern)
                        ->orWhere('type', 'like', $pattern)
                        ->orWhere('currency_code', 'like', $pattern);
                });
                $this->orWhereTokenAndName($q, 'name', $tokens);
            })
            ->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'description']);

        if ($projects->isNotEmpty()) {
            $groups['projects'] = $projects->map(fn (Project $p) => [
                'kind' => 'project',
                'title' => $p->name,
                'subtitle' => $p->description ? Str::limit(strip_tags($p->description), 120) : null,
                'url' => route('families.projects.show', $p),
            ])->all();
        }

        $properties = Property::query()
            ->where('family_id', $family->id)
            ->where(function ($q) use ($pattern, $tokens) {
                $q->where(function ($inner) use ($pattern) {
                    $inner->where('name', 'like', $pattern)
                        ->orWhere('property_code', 'like', $pattern)
                        ->orWhere('address', 'like', $pattern)
                        ->orWhere('region_city', 'like', $pattern);
                });
                $this->orWhereTokenAndName($q, 'name', $tokens);
            })
            ->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'property_code', 'region_city']);

        if ($properties->isNotEmpty()) {
            $groups['properties'] = $properties->map(fn (Property $p) => [
                'kind' => 'property',
                'title' => $p->name,
                'subtitle' => collect([$p->property_code, $p->region_city])->filter()->implode(' · ') ?: null,
                'url' => route('families.properties.show', $p),
            ])->all();
        }

        $wallets = Wallet::query()
            ->where('family_id', $family->id)
            ->where(function ($q) use ($pattern, $tokens) {
                $q->where(function ($inner) use ($pattern) {
                    $inner->where('name', 'like', $pattern)
                        ->orWhere('description', 'like', $pattern)
                        ->orWhere('type', 'like', $pattern)
                        ->orWhere('currency_code', 'like', $pattern);
                });
                $this->orWhereTokenAndName($q, 'name', $tokens);
            })
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'currency_code']);

        if ($wallets->isNotEmpty()) {
            $groups['wallets'] = $wallets->map(fn (Wallet $w) => [
                'kind' => 'wallet',
                'title' => $w->name,
                'subtitle' => strtoupper((string) ($w->currency_code ?? '')) ?: null,
                'url' => route('families.wallets.show', $w),
            ])->all();
        }

        $liabilities = FamilyLiability::query()
            ->where('family_id', $family->id)
            ->where(function ($q) use ($pattern, $tokens) {
                $q->where(function ($inner) use ($pattern) {
                    $inner->where('name', 'like', $pattern)
                        ->orWhere('type', 'like', $pattern);
                });
                $this->orWhereTokenAndName($q, 'name', $tokens);
            })
            ->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'type']);

        if ($liabilities->isNotEmpty()) {
            $groups['liabilities'] = $liabilities->map(fn (FamilyLiability $l) => [
                'kind' => 'liability',
                'title' => $l->name,
                'subtitle' => $l->type,
                'url' => route('families.liabilities.show', $l),
            ])->all();
        }

        $budgets = Budget::query()
            ->where('family_id', $family->id)
            ->where(function ($q) use ($pattern, $tokens) {
                $q->where('name', 'like', $pattern);
                $this->orWhereTokenAndName($q, 'name', $tokens);
            })
            ->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'type']);

        if ($budgets->isNotEmpty()) {
            $budgetTypes = Budget::types();
            $groups['budgets'] = $budgets->map(function (Budget $b) use ($budgetTypes) {
                return [
                    'kind' => 'budget',
                    'title' => $b->name,
                    'subtitle' => $budgetTypes[$b->type] ?? $b->type,
                    'url' => route('families.budgets.show', $b),
                ];
            })->all();
        }

        $goals = SavingsGoal::query()
            ->where('family_id', $family->id)
            ->where(function ($q) use ($pattern, $tokens) {
                $q->where(function ($inner) use ($pattern) {
                    $inner->where('name', 'like', $pattern)
                        ->orWhere('description', 'like', $pattern);
                });
                $this->orWhereTokenAndName($q, 'name', $tokens);
            })
            ->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'currency_code']);

        if ($goals->isNotEmpty()) {
            $groups['savings_goals'] = $goals->map(fn (SavingsGoal $g) => [
                'kind' => 'savings_goal',
                'title' => $g->name,
                'subtitle' => strtoupper((string) ($g->currency_code ?? '')) ?: null,
                'url' => route('families.savings-goals.show', $g),
            ])->all();
        }

        $total = 0;
        foreach ($groups as $rows) {
            $total += count($rows);
        }

        return ['groups' => $groups, 'total' => $total];
    }

    /**
     * Settings & workspace destinations from the /settings hub (same visibility as settings/index).
     *
     * @return list<array{kind: string, title: string, subtitle: ?string, url: string}>
     */
    public function matchSettingsHubEntries(Family $family, ?User $user, string $term): array
    {
        $term = trim($term);
        if ($term === '' || mb_strlen($term) < 2) {
            return [];
        }

        $out = [];
        foreach ($this->settingsHubCatalog($family, $user) as $row) {
            if ($this->textMatchesSearch($term, $row['match_text'])) {
                unset($row['match_text']);
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @return list<array{kind: string, title: string, subtitle: ?string, url: string, match_text: string}>
     */
    private function settingsHubCatalog(Family $family, ?User $user): array
    {
        $rows = [];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Settings'),
            'subtitle' => __('Manage your FamLedger profile, family configuration, categories, notifications and audit log from one place.'),
            'url' => route('settings.index'),
            'match_text' => $this->settingsMatchBlob(
                __('Settings'),
                __('Manage your FamLedger profile, family configuration, categories, notifications and audit log from one place.'),
                'settings workspace hub preferences configuration account menu'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Profile'),
            'subtitle' => __('Update your personal details, email and password used to access FamLedger.'),
            'url' => route('profile.edit'),
            'match_text' => $this->settingsMatchBlob(
                __('Profile'),
                __('Update your personal details, email and password used to access FamLedger.'),
                'profile user account password email personal login security'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Family profile'),
            'subtitle' => __('Configure your family name, default currency, timezone and other core preferences.'),
            'url' => route('families.edit', $family),
            'match_text' => $this->settingsMatchBlob(
                __('Family profile'),
                __('Configure your family name, default currency, timezone and other core preferences.'),
                'family household currency timezone preferences name'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Notifications'),
            'subtitle' => __('Control email alerts about new members, project updates, budget thresholds and savings progress.'),
            'url' => route('settings.notifications'),
            'match_text' => $this->settingsMatchBlob(
                __('Notifications'),
                __('Control email alerts about new members, project updates, budget thresholds and savings progress.'),
                'notifications email alerts reminders dnd quiet faq support'
            ),
        ];

        // Accounts menu (sidebar): wealth overview, wallets, ledger, budgets, savings, liabilities, reconciliation
        $rows[] = [
            'kind' => 'settings',
            'title' => __('Accounts'),
            'subtitle' => __('Family wealth overview: wallets, balances, and consolidated totals.'),
            'url' => route('families.wealth.index'),
            'match_text' => $this->settingsMatchBlob(
                __('Accounts'),
                __('Family wealth overview: wallets, balances, and consolidated totals.'),
                'accounts account banking finances overview wealth net worth balances ledger money household'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Wallets'),
            'subtitle' => __('Manage family wallets, currencies, and where income and spending flow.'),
            'url' => route('families.wallets.index'),
            'match_text' => $this->settingsMatchBlob(
                __('Wallets'),
                __('Manage family wallets, currencies, and where income and spending flow.'),
                'wallets wallet cash bank banking pocket currency balances'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Transactions'),
            'subtitle' => __('Income, expenses, and ledger activity across wallets.'),
            'url' => route('families.transactions.index'),
            'match_text' => $this->settingsMatchBlob(
                __('Transactions'),
                __('Income, expenses, and ledger activity across wallets.'),
                'transactions transaction ledger entries income expense history activity feed'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Transfers'),
            'subtitle' => __('Move money between wallets or accounts.'),
            'url' => route('families.transfers.index'),
            'match_text' => $this->settingsMatchBlob(
                __('Transfers'),
                __('Move money between wallets or accounts.'),
                'transfers transfer move money between wallets internal'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Budgets'),
            'subtitle' => __('Plan spending and compare actuals to your budgets.'),
            'url' => route('families.budgets.index'),
            'match_text' => $this->settingsMatchBlob(
                __('Budgets'),
                __('Plan spending and compare actuals to your budgets.'),
                'budgets budget planning forecast spending limits actuals'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Savings'),
            'subtitle' => __('Savings goals: balances, contributions, and progress.'),
            'url' => route('families.accounts.savings'),
            'match_text' => $this->settingsMatchBlob(
                __('Savings'),
                __('Savings goals: balances, contributions, and progress.'),
                'savings saving goals piggy bank contribute progress targets'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Liabilities'),
            'subtitle' => __('Loans, debts, and obligations tracked for the family.'),
            'url' => route('families.liabilities.index'),
            'match_text' => $this->settingsMatchBlob(
                __('Liabilities'),
                __('Loans, debts, and obligations tracked for the family.'),
                'liabilities liability debt loans credit mortgage owe borrowing'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Reconciliation'),
            'subtitle' => __('Reconcile wallet balances with real-world statements and balances.'),
            'url' => route('families.accounts.reconciliation'),
            'match_text' => $this->settingsMatchBlob(
                __('Reconciliation'),
                __('Reconcile wallet balances with real-world statements and balances.'),
                'reconciliation reconcile matching statements verify balance check true up'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Property assets'),
            'subtitle' => __('View and manage all family properties: value, status, filters, and links to detail.'),
            'url' => route('families.properties.assets'),
            'match_text' => $this->settingsMatchBlob(
                __('Property assets'),
                __('View and manage all family properties: value, status, filters, and links to detail.'),
                'property properties assets real estate homes houses land portfolio rentals owned buildings finance properties list'
            ),
        ];

        $rows[] = [
            'kind' => 'settings',
            'title' => __('Projects'),
            'subtitle' => __('Open the projects hub: browse all initiatives, filter by status, and jump into a project.'),
            'url' => route('families.projects.index', ['filter' => 'all']),
            'match_text' => $this->settingsMatchBlob(
                __('Projects'),
                __('Open the projects hub: browse all initiatives, filter by status, and jump into a project.'),
                'project projects initiative initiatives all projects planning active completed funding work tasks list hub'
            ),
        ];

        if ($user && $user->can('access_admin_panel')) {
            $rows[] = [
                'kind' => 'settings',
                'title' => __('Users'),
                'subtitle' => __('User list: search, create, and manage platform accounts and profiles.'),
                'url' => route('admin.users.index'),
                'match_text' => $this->settingsMatchBlob(
                    __('Users'),
                    __('User list: search, create, and manage platform accounts and profiles.'),
                    'users user user list list platform accounts administration admin directory people staff members'
                ),
            ];

            $rows[] = [
                'kind' => 'settings',
                'title' => __('Roles'),
                'subtitle' => __('Define roles and assign capabilities across the platform.'),
                'url' => route('admin.roles.index'),
                'match_text' => $this->settingsMatchBlob(
                    __('Roles'),
                    __('Define roles and assign capabilities across the platform.'),
                    'roles role rbac access groups permissions assignment'
                ),
            ];

            $rows[] = [
                'kind' => 'settings',
                'title' => __('Permissions'),
                'subtitle' => __('View and maintain permission keys used by roles and policies.'),
                'url' => route('admin.permissions.index'),
                'match_text' => $this->settingsMatchBlob(
                    __('Permissions'),
                    __('View and maintain permission keys used by roles and policies.'),
                    'permissions permission acl capabilities access rights policies keys modules'
                ),
            ];

            $rows[] = [
                'kind' => 'settings',
                'title' => __('Contact'),
                'subtitle' => __('Inbound messages from the marketing site and contact forms.'),
                'url' => route('admin.contact-messages.index'),
                'match_text' => $this->settingsMatchBlob(
                    __('Contact'),
                    __('Inbound messages from the marketing site and contact forms.'),
                    'contact contact messages inquiries leads support forms landing email team'
                ),
            ];
        }

        if ($user?->hasRole('Super Admin')) {
            $rows[] = [
                'kind' => 'settings',
                'title' => __('Categories'),
                'subtitle' => __('Define income and expense categories to keep reports, budgets and savings goals organised.'),
                'url' => route('settings.categories'),
                'match_text' => $this->settingsMatchBlob(
                    __('Categories'),
                    __('Define income and expense categories to keep reports, budgets and savings goals organised.'),
                    'categories income expense roles lookups system custom'
                ),
            ];

            $rows[] = [
                'kind' => 'settings',
                'title' => __('Property configuration'),
                'subtitle' => __('Define categories and attributes that appear when families add or edit properties (Finance → Properties → Add property).'),
                'url' => route('settings.property.index'),
                'match_text' => $this->settingsMatchBlob(
                    __('Property configuration'),
                    __('Define categories and attributes that appear when families add or edit properties (Finance → Properties → Add property).'),
                    'property properties real estate assets attributes template fields'
                ),
            ];
        }

        if ($user && ($user->hasRole('Super Admin') || $user->hasRole('Auditor'))) {
            $rows[] = [
                'kind' => 'settings',
                'title' => __('Audit log'),
                'subtitle' => __('View all platform activity across families. Filter by date and type.'),
                'url' => route('settings.audit-log'),
                'match_text' => $this->settingsMatchBlob(
                    __('Audit log'),
                    __('View all platform activity across families. Filter by date and type.'),
                    'audit log activity history export compliance'
                ),
            ];
        }

        return $rows;
    }

    private function settingsMatchBlob(string $title, string $subtitle, string $extraKeywords): string
    {
        return mb_strtolower($title.' '.$subtitle.' '.$extraKeywords);
    }

    /**
     * Substring match, or every significant token present (order-independent).
     */
    private function textMatchesSearch(string $term, string $haystackLower): bool
    {
        $t = mb_strtolower(trim($term));
        if ($t === '') {
            return false;
        }
        if (mb_strpos($haystackLower, $t) !== false) {
            return true;
        }
        $tokens = $this->significantTokens($term);
        if (count($tokens) < 2) {
            return false;
        }
        foreach ($tokens as $tok) {
            if (mb_strpos($haystackLower, mb_strtolower($tok)) === false) {
                return false;
            }
        }

        return true;
    }

    private function patternForLike(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }

    /**
     * @return list<string>
     */
    private function significantTokens(string $term): array
    {
        $parts = preg_split('/\s+/u', trim($term), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($parts, fn ($t) => mb_strlen($t) >= 2));
    }

    /**
     * Extra recall: when the user types several words, also match rows whose `column`
     * contains every token (order-independent).
     *
     * @param  Builder<Model>  $q
     */
    private function orWhereTokenAndName(Builder $q, string $column, array $tokens): void
    {
        if (count($tokens) < 2) {
            return;
        }
        $q->orWhere(function ($inner) use ($column, $tokens) {
            foreach ($tokens as $t) {
                $inner->where($column, 'like', $this->patternForLike($t));
            }
        });
    }
}
