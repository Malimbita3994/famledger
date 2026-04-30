<?php

namespace App\Providers;

use App\Events\FamilyFinancialDataChanged;
use App\Models\Announcement;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\FamilyLiability;
use App\Models\FamilyMember;
use App\Models\FamilyRelationship;
use App\Models\Income;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectFunding;
use App\Models\Property;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Models\Transfer;
use App\Models\Wallet;
use App\Models\WalletReconciliation;
use App\Observers\AuditLogObserver;
use App\Observers\ExpenseSearchObserver;
use App\Observers\IncomeSearchObserver;
use App\Policies\FamilyPolicy;
use App\Services\FirebaseClient;
use App\Services\Search\ElasticsearchClientFactory;
use App\Services\Search\FamilyEntitySearchService;
use App\Services\Search\QueryParserService;
use App\Services\Search\SearchService;
use App\Services\Search\TransactionDocumentFactory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use SocialiteProviders\Apple\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Factory::class, function () {
            $factory = new Factory;

            $credentialsPath = config('firebase.credentials.path');
            $credentialsJson = config('firebase.credentials.json');

            if (is_string($credentialsPath) && $credentialsPath !== '') {
                $factory = $factory->withServiceAccount($credentialsPath);
            } elseif (is_string($credentialsJson) && $credentialsJson !== '') {
                $decoded = json_decode($credentialsJson, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $factory = $factory->withServiceAccount($decoded);
                }
            }

            $projectId = config('firebase.project_id');
            if (is_string($projectId) && $projectId !== '') {
                $factory = $factory->withProjectId($projectId);
            }

            $databaseUrl = config('firebase.database_url');
            if (is_string($databaseUrl) && $databaseUrl !== '') {
                $factory = $factory->withDatabaseUri($databaseUrl);
            }

            $storageBucket = config('firebase.storage_bucket');
            if (is_string($storageBucket) && $storageBucket !== '') {
                $factory = $factory->withDefaultStorageBucket($storageBucket);
            }

            return $factory;
        });

        $this->app->singleton(FirebaseClient::class, function ($app) {
            return new FirebaseClient($app->make(Factory::class));
        });

        $this->app->singleton(ElasticsearchClientFactory::class);
        $this->app->singleton(TransactionDocumentFactory::class);
        $this->app->singleton(QueryParserService::class);
        $this->app->singleton(SearchService::class);
        $this->app->singleton(FamilyEntitySearchService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('apple', Provider::class);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth-api', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        Gate::policy(Family::class, FamilyPolicy::class);

        View::composer('layouts.metronic', function ($view) {
            $key = 'metronic_layout_data';
            $data = request()->attributes->get($key);
            if ($data !== null) {
                $view->with($data);

                return;
            }

            $currentFamily = null;
            $canViewFamilyAuditTrail = false;
            $currentFamilyMembership = null;
            $canManageInvites = false;
            $canManageProperty = false;
            $roleLabelForTopbar = '';

            if (auth()->check()) {
                $user = auth()->user();
                $currentFamily = null;
                $sessionFamilyId = request()->session()->get('current_family_id');
                if ($sessionFamilyId) {
                    $candidate = Family::query()->whereKey($sessionFamilyId)->first();
                    if ($candidate && $candidate->members()->where('user_id', $user->id)->exists()) {
                        $currentFamily = $candidate;
                    }
                }
                if (! $currentFamily) {
                    $currentFamily = $user->families()->first();
                }
                if ($currentFamily) {
                    $currentFamilyMembership = FamilyMember::where('family_id', $currentFamily->id)
                        ->where('user_id', $user->id)
                        ->with('role')
                        ->first();
                    $roleName = $currentFamilyMembership && $currentFamilyMembership->role
                        ? mb_strtolower($currentFamilyMembership->role->name)
                        : null;
                    $isOwnerOrCoOwner = in_array($roleName, ['owner', 'co-owner'], true);
                    $canViewFamilyAuditTrail = $user->hasRole('Auditor') || $isOwnerOrCoOwner;
                    $canManageInvites = $isOwnerOrCoOwner;
                    /* Any active family member may use family Property (assets); owners/co-owner only for invites/audit, etc. */
                    $canManageProperty = $currentFamilyMembership !== null;
                    $roleLabelForTopbar = $currentFamilyMembership && $currentFamilyMembership->role
                        ? $currentFamilyMembership->role->name
                        : '';
                }
                if ($roleLabelForTopbar === '') {
                    $user->load('roles');
                    $userRole = $user->roles->first();
                    $roleLabelForTopbar = $userRole ? ($userRole->display_name ?? $userRole->name) : '';
                }
                if ($roleLabelForTopbar === '' && $user->familyMemberships()->exists()) {
                    $fallback = $user->familyMemberships()->with('role')->first();
                    $roleLabelForTopbar = $fallback && $fallback->role ? $fallback->role->name : '';
                }
            }

            $data = [
                'currentFamily' => $currentFamily,
                'canViewFamilyAuditTrail' => $canViewFamilyAuditTrail,
                'currentFamilyMembership' => $currentFamilyMembership,
                'canManageInvites' => $canManageInvites,
                'canManageProperty' => $canManageProperty,
                'roleLabelForTopbar' => $roleLabelForTopbar,
            ];
            request()->attributes->set($key, $data);
            $view->with($data);
        });

        // Database audit trail: log model changes
        Expense::observe(AuditLogObserver::class);
        Income::observe(AuditLogObserver::class);
        Income::observe(IncomeSearchObserver::class);
        Expense::observe(ExpenseSearchObserver::class);
        Transfer::observe(AuditLogObserver::class);
        Budget::observe(AuditLogObserver::class);
        Wallet::observe(AuditLogObserver::class);
        Family::observe(AuditLogObserver::class);
        FamilyMember::observe(AuditLogObserver::class);
        Project::observe(AuditLogObserver::class);
        ProjectFunding::observe(AuditLogObserver::class);
        Property::observe(AuditLogObserver::class);
        SavingsGoal::observe(AuditLogObserver::class);
        SavingsContribution::observe(AuditLogObserver::class);
        FamilyLiability::observe(AuditLogObserver::class);
        FamilyInvitation::observe(AuditLogObserver::class);
        WalletReconciliation::observe(AuditLogObserver::class);
        Milestone::observe(AuditLogObserver::class);
        Announcement::observe(AuditLogObserver::class);
        FamilyRelationship::observe(AuditLogObserver::class);

        Expense::saved(function (Expense $expense): void {
            FamilyFinancialDataChanged::dispatch($expense->family_id, 'expense');
        });
        Expense::deleted(function (Expense $expense): void {
            FamilyFinancialDataChanged::dispatch($expense->family_id, 'expense_deleted');
        });
        Income::saved(function (Income $income): void {
            FamilyFinancialDataChanged::dispatch($income->family_id, 'income');
        });
        Income::deleted(function (Income $income): void {
            FamilyFinancialDataChanged::dispatch($income->family_id, 'income_deleted');
        });
        Transfer::saved(function (Transfer $transfer): void {
            FamilyFinancialDataChanged::dispatch($transfer->family_id, 'transfer');
        });
        Transfer::deleted(function (Transfer $transfer): void {
            FamilyFinancialDataChanged::dispatch($transfer->family_id, 'transfer_deleted');
        });

        Budget::saved(function (Budget $budget): void {
            FamilyFinancialDataChanged::dispatch($budget->family_id, 'budget');
        });
        Project::saved(function (Project $project): void {
            FamilyFinancialDataChanged::dispatch($project->family_id, 'project');
        });
        Project::deleted(function (Project $project): void {
            FamilyFinancialDataChanged::dispatch($project->family_id, 'project_deleted');
        });
    }
}
