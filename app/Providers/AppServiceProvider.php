<?php

namespace App\Providers;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyInvitation;
use App\Models\FamilyLiability;
use App\Models\Income;
use App\Models\Project;
use App\Models\Property;
use App\Models\SavingsGoal;
use App\Models\SavingsContribution;
use App\Models\Transfer;
use App\Models\Wallet;
use App\Models\WalletReconciliation;
use App\Models\ProjectFunding;
use App\Observers\AuditLogObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
                $currentFamily = $user->families()->first();
                if ($currentFamily) {
                    $currentFamilyMembership = FamilyMember::where('family_id', $currentFamily->id)
                        ->where('user_id', $user->id)
                        ->with('role')
                        ->first();
                    $roleName = $currentFamilyMembership && $currentFamilyMembership->role
                        ? mb_strtolower($currentFamilyMembership->role->name)
                        : null;
                    $isOwnerOrCoOwner = in_array($roleName, ['owner', 'co-owner'], true);
                    $canViewFamilyAuditTrail = $user->hasRole('Super Admin') || $user->hasRole('Auditor') || $isOwnerOrCoOwner;
                    $canManageInvites = $isOwnerOrCoOwner;
                    $canManageProperty = $isOwnerOrCoOwner;
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
    }
}
