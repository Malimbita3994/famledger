<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard');

        $permissions = [
            // Legacy / existing permissions
            'access_admin_panel',
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'view_audit_logs',
            'manage_system_settings',

            // Dashboard module
            'dashboard_view',
            'dashboard_refresh',
            'dashboard_customize',
            'dashboard_reset',
            'dashboard_finance_view',
            'dashboard_projects_view',
            'dashboard_alerts_view',
            'dashboard_activity_view',
            'dashboard_analytics_view',
            'dashboard_admin_configure',

            // Permissions module
            'permissions_assign',
            'permissions_create',
            'permissions_delete',
            'permissions_restore',
            'permissions_update',
            'permissions_view',

            // Roles module
            'roles_assign',
            'roles_create',
            'roles_delete',
            'roles_restore',
            'roles_update',
            'roles_view',

            // Reports module
            'reports_view',
            'reports_preview',
            'reports_download',

            // Users module
            'users_create',
            'users_delete',
            'users_restore',
            'users_update',
            'users_view',

            // Families module
            'families_create',
            'families_view',
            'families_update',
            'families_delete',
            'families_add_member',
            'families_remove_member',

            // Accounts module
            'accounts_create',
            'accounts_view',
            'accounts_update',
            'accounts_delete',

            // Family Projects module
            'family_projects_create',
            'family_projects_view',
            'family_projects_update',
            'family_projects_delete',

            // Family Projects - All Projects
            'family_projects_all_view_list',
            'family_projects_all_view_details',
            'family_projects_all_create',
            'family_projects_all_update',
            'family_projects_all_delete',
            'family_projects_all_archive',
            'family_projects_all_restore',
            'family_projects_all_assign_members',
            'family_projects_all_remove_members',
            'family_projects_all_assign_roles',
            'family_projects_all_upload_documents',
            'family_projects_all_delete_documents',
            'family_projects_all_comment',
            'family_projects_all_export_list',
            'family_projects_all_print_details',

            // Family Projects - Active Projects
            'family_projects_active_view',
            'family_projects_active_update_progress',
            'family_projects_active_update_milestones',
            'family_projects_active_add_tasks',
            'family_projects_active_update_tasks',
            'family_projects_active_delete_tasks',
            'family_projects_active_mark_task_completed',
            'family_projects_active_reopen_task',
            'family_projects_active_add_expenses',
            'family_projects_active_approve_expenses',
            'family_projects_active_update_timeline',
            'family_projects_active_extend_deadline',
            'family_projects_active_suspend',
            'family_projects_active_close',
            'family_projects_active_generate_report',

            // Family Projects - Completed Projects
            'family_projects_completed_view',
            'family_projects_completed_view_summary',
            'family_projects_completed_generate_report',
            'family_projects_completed_export_report',
            'family_projects_completed_reopen',
            'family_projects_completed_archive',
            'family_projects_completed_add_notes',
            'family_projects_completed_view_audit_history',

            // Family Projects - Funding
            'family_projects_funding_view_records',
            'family_projects_funding_add_source',
            'family_projects_funding_update_source',
            'family_projects_funding_delete_source',
            'family_projects_funding_allocate',
            'family_projects_funding_adjust_allocation',
            'family_projects_funding_approve_allocation',
            'family_projects_funding_record_disbursement',
            'family_projects_funding_view_utilization',
            'family_projects_funding_generate_report',
            'family_projects_funding_export',
            'family_projects_funding_lock_record',
            'family_projects_funding_reconcile',

            // Family Projects - Governance
            'family_projects_governance_approve_creation',
            'family_projects_governance_approve_closure',
            'family_projects_governance_approve_funding_release',
            'family_projects_governance_access_confidential',
            'family_projects_governance_manage_project_categories',
            'family_projects_governance_manage_funding_categories',

            // Family Projects - Reporting
            'family_projects_reporting_generate_analytics',
            'family_projects_reporting_compare_performance',
            'family_projects_reporting_access_dashboards',

            // Administration module
            'administration_access',
            'administration_manage',

            // Settings module
            'settings_view',
            'settings_update',

            // Settings - User Profile
            'settings_profile_view',
            'settings_profile_update_details',
            'settings_profile_change_email',
            'settings_profile_change_password',
            'settings_profile_update_photo',
            'settings_profile_manage_two_factor',
            'settings_profile_manage_sessions',
            'settings_profile_download_data',
            'settings_profile_delete_account',

            // Settings - Family Profile
            'settings_family_view',
            'settings_family_update_name',
            'settings_family_change_currency',
            'settings_family_change_timezone',
            'settings_family_update_description',
            'settings_family_update_logo',
            'settings_family_manage_preferences',
            'settings_family_manage_privacy',
            'settings_family_transfer_ownership',
            'settings_family_archive_workspace',
            'settings_family_delete_workspace',

            // Settings - Categories
            'settings_categories_view',
            'settings_categories_create',
            'settings_categories_update',
            'settings_categories_delete',
            'settings_categories_toggle_active',
            'settings_categories_reorder',
            'settings_categories_create_subcategories',
            'settings_categories_merge',
            'settings_categories_assign_transactions',
            'settings_categories_import',
            'settings_categories_export',

            // Settings - Notifications
            'settings_notifications_view',
            'settings_notifications_toggle_email',
            'settings_notifications_toggle_in_app',
            'settings_notifications_configure_rules',
            'settings_notifications_manage_thresholds',
            'settings_notifications_subscribe_events',
            'settings_notifications_configure_project',
            'settings_notifications_configure_budget',
            'settings_notifications_configure_savings',
            'settings_notifications_test_delivery',

            // Settings - Audit Log
            'settings_audit_view',
            'settings_audit_filter',
            'settings_audit_search',
            'settings_audit_view_details',
            'settings_audit_export',
            'settings_audit_download_reports',
            'settings_audit_monitor_user_activity',
            'settings_audit_access_security_events',
            'settings_audit_archive_records',
            'settings_audit_delete_logs',

            // Settings - Security & Administration
            'settings_security_manage_roles_permissions',
            'settings_security_manage_system_settings',
            'settings_security_manage_integrations',
            'settings_security_manage_backups',
            'settings_security_access_maintenance_tools',

            // Account module (user's own account)
            'account_view_profile',
            'account_update_profile',

            // Wallets module
            'wallets_view_list',
            'wallets_view_details',
            'wallets_create',
            'wallets_update',
            'wallets_delete',
            'wallets_toggle_active',
            'wallets_view_balance',
            'wallets_adjust_balance',
            'wallets_transfer_between',
            'wallets_assign_to_user',
            'wallets_export',
            'wallets_reconcile',

            // Income module
            'income_view_records',
            'income_view_details',
            'income_create_transaction',
            'income_update_transaction',
            'income_delete_transaction',
            'income_approve',
            'income_categorize',
            'income_attach_documents',
            'income_import',
            'income_export',
            'income_generate_reports',
            'income_mark_received',

            // Expenses module
            'expenses_view_list',
            'expenses_view_details',
            'expenses_create',
            'expenses_update',
            'expenses_delete',
            'expenses_approve',
            'expenses_reject',
            'expenses_categorize',
            'expenses_attach_documents',
            'expenses_import',
            'expenses_export',
            'expenses_generate_reports',
            'expenses_mark_paid',

            // Transfers module
            'transfers_view_list',
            'transfers_view_details',
            'transfers_initiate',
            'transfers_update',
            'transfers_cancel',
            'transfers_approve',
            'transfers_reject',
            'transfers_schedule',
            'transfers_execute',
            'transfers_reverse',
            'transfers_export',

            // Budgets module
            'budgets_view',
            'budgets_view_details',
            'budgets_create',
            'budgets_update',
            'budgets_delete',
            'budgets_toggle_active',
            'budgets_assign_category',
            'budgets_monitor_utilization',
            'budgets_receive_alerts',
            'budgets_adjust_limits',
            'budgets_export',
            'budgets_generate_reports',

            // Savings module
            'savings_view',
            'savings_view_details',
            'savings_create_goal',
            'savings_update_goal',
            'savings_delete_goal',
            'savings_deposit',
            'savings_withdraw',
            'savings_transfer_to',
            'savings_close_account',
            'savings_view_progress',
            'savings_export',

            // Reconciliation module
            'reconciliation_view_records',
            'reconciliation_start',
            'reconciliation_update_entries',
            'reconciliation_approve',
            'reconciliation_reject',
            'reconciliation_resolve_discrepancies',
            'reconciliation_lock_period',
            'reconciliation_reopen',
            'reconciliation_upload_documents',
            'reconciliation_export_report',
            'reconciliation_generate_audit_trail',

            // Reports - General
            'reports_general_view_dashboard',
            'reports_general_generate',
            'reports_general_customize_parameters',
            'reports_general_view_details',
            'reports_general_export',
            'reports_general_print',
            'reports_general_schedule',
            'reports_general_share',
            'reports_general_save_template',
            'reports_general_delete_template',

            // Reports - Finance
            'reports_finance_view',
            'reports_finance_generate_statements',
            'reports_finance_view_income',
            'reports_finance_view_expense',
            'reports_finance_view_cash_flow',
            'reports_finance_view_wallets',
            'reports_finance_view_reconciliation',
            'reports_finance_filter',
            'reports_finance_compare_periods',
            'reports_finance_export',
            'reports_finance_print',
            'reports_finance_schedule',
            'reports_finance_share',
            'reports_finance_access_sensitive_data',

            // Reports - Projects
            'reports_project_view',
            'reports_project_generate_performance',
            'reports_project_view_financial',
            'reports_project_view_status',
            'reports_project_view_resource_utilization',
            'reports_project_filter',
            'reports_project_compare_progress',
            'reports_project_export',
            'reports_project_print',
            'reports_project_schedule',
            'reports_project_share',
            'reports_project_access_archived',

            // Reports - Budgets
            'reports_budget_view',
            'reports_budget_generate_performance',
            'reports_budget_view_vs_actual',
            'reports_budget_view_utilization',
            'reports_budget_view_variance',
            'reports_budget_filter',
            'reports_budget_export',
            'reports_budget_print',
            'reports_budget_schedule',
            'reports_budget_share',
            'reports_budget_access_historical',

            // Reports - Administrative
            'reports_admin_create_custom',
            'reports_admin_modify_templates',
            'reports_admin_delete_templates',
            'reports_admin_access_restricted',
            'reports_admin_audit_access_logs',
            'reports_admin_manage_categories',

            // Reports - Automation
            'reports_automation_schedule_recurring',
            'reports_automation_configure_delivery',
            'reports_automation_subscribe',
            'reports_automation_unsubscribe',

            // Global/system-wide module (cross-report + general system)
            'global_view_reports',
            'global_export_data',
            'global_import_data',
            'global_audit_logs_access',
            'global_manage_categories',
            'global_manage_tags',
            'global_system_settings_access',

            // Audit trail (platform & family audit logs)
            'audit_trail_view',
            'audit_trail_view_platform',
            'audit_trail_view_family',
            'audit_trail_export',
            'audit_trail_filter',

            // Contact messages (admin – landing page messages)
            'contact_messages_view',
            'contact_messages_delete',
            'contact_messages_mark_read',

            // Liabilities (family liabilities)
            'liabilities_view',
            'liabilities_create',
            'liabilities_update',
            'liabilities_delete',

            // Invitations (family invites)
            'invitations_view',
            'invitations_create',
            'invitations_delete',
            'invitations_resend',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard]);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => $guard]);
        $admin->givePermissionTo([
            'access_admin_panel',
            'manage_users',
            'manage_roles',
            'view_audit_logs',
            'contact_messages_view',
            'contact_messages_delete',
            'contact_messages_mark_read',
        ]);

        Role::firstOrCreate(['name' => 'Support', 'guard_name' => $guard])
            ->givePermissionTo(['access_admin_panel', 'manage_users', 'view_audit_logs', 'contact_messages_view', 'contact_messages_mark_read']);

        Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => $guard])
            ->givePermissionTo([
                'access_admin_panel',
                'view_audit_logs',
                'audit_trail_view',
                'audit_trail_view_platform',
                'audit_trail_view_family',
                'audit_trail_export',
                'audit_trail_filter',
            ]);

        // Family/tenant-style roles (assign permissions in admin as needed)
        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'Co-owner', 'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'Member', 'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => $guard]);
    }
}
