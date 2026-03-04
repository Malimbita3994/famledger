<?php

use App\Http\Controllers\DashboardController as MainDashboardController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFundingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PropertyConfigController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\ContactController;

Route::get('/', function () {
    return view('marketing.landing');
})->name('landing');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/dashboard', [MainDashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User & family settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Settings: system admin only (Super Admin role)
    Route::middleware('role:Super Admin')->group(function () {
        Route::get('/settings/categories', [SettingsController::class, 'categories'])->name('settings.categories');
        Route::post('/settings/categories/lookup', [SettingsController::class, 'storeLookup'])->name('settings.categories.lookup.store');
        Route::patch('/settings/categories/lookup/{systemLookup}', [SettingsController::class, 'updateLookup'])->name('settings.categories.lookup.update');
        Route::delete('/settings/categories/lookup/{systemLookup}', [SettingsController::class, 'destroyLookup'])->name('settings.categories.lookup.destroy');
        Route::post('/settings/categories/income', [SettingsController::class, 'storeIncomeCategory'])->name('settings.categories.income.store');
        Route::patch('/settings/categories/income/{incomeCategory}', [SettingsController::class, 'updateIncomeCategory'])->name('settings.categories.income.update');
        Route::delete('/settings/categories/income/{incomeCategory}', [SettingsController::class, 'destroyIncomeCategory'])->name('settings.categories.income.destroy');

        Route::post('/settings/categories/expense', [SettingsController::class, 'storeExpenseCategory'])->name('settings.categories.expense.store');
        Route::patch('/settings/categories/expense/{expenseCategory}', [SettingsController::class, 'updateExpenseCategory'])->name('settings.categories.expense.update');
        Route::delete('/settings/categories/expense/{expenseCategory}', [SettingsController::class, 'destroyExpenseCategory'])->name('settings.categories.expense.destroy');

        Route::post('/settings/categories/roles', [SettingsController::class, 'storeFamilyRole'])->name('settings.categories.roles.store');
        Route::patch('/settings/categories/roles/{familyRole}', [SettingsController::class, 'updateFamilyRole'])->name('settings.categories.roles.update');
        Route::delete('/settings/categories/roles/{familyRole}', [SettingsController::class, 'destroyFamilyRole'])->name('settings.categories.roles.destroy');

        Route::get('/settings/audit-log', [SettingsController::class, 'auditLog'])->name('settings.audit-log');

        // Property configuration (categories & attributes)
        Route::get('/settings/property', [PropertyConfigController::class, 'index'])->name('settings.property.index');
        Route::post('/settings/property/categories', [PropertyConfigController::class, 'storeCategory'])->name('settings.property.categories.store');
        Route::patch('/settings/property/categories/{category}', [PropertyConfigController::class, 'updateCategory'])->name('settings.property.categories.update');
        Route::delete('/settings/property/categories/{category}', [PropertyConfigController::class, 'destroyCategory'])->name('settings.property.categories.destroy');

        Route::post('/settings/property/attributes', [PropertyConfigController::class, 'storeAttribute'])->name('settings.property.attributes.store');
        Route::patch('/settings/property/attributes/{attribute}', [PropertyConfigController::class, 'updateAttribute'])->name('settings.property.attributes.update');
        Route::delete('/settings/property/attributes/{attribute}', [PropertyConfigController::class, 'destroyAttribute'])->name('settings.property.attributes.destroy');

        Route::post('/settings/property/attributes/{attribute}/options', [PropertyConfigController::class, 'storeOption'])->name('settings.property.attributes.options.store');
        Route::delete('/settings/property/options/{option}', [PropertyConfigController::class, 'destroyOption'])->name('settings.property.attributes.options.destroy');
    });

    Route::get('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');

    // Family Management (CRUD)
    Route::resource('families', FamilyController::class);

    // Family Members (add, edit role, remove)
    Route::prefix('families/{family}')->name('families.')->group(function () {
        Route::get('members/create', [FamilyMemberController::class, 'create'])->name('members.create');
        Route::post('members', [FamilyMemberController::class, 'store'])->name('members.store');
        Route::get('members/{member}/edit', [FamilyMemberController::class, 'edit'])->name('members.edit');
        Route::put('members/{member}', [FamilyMemberController::class, 'update'])->name('members.update');
        Route::patch('members/{member}/deactivate', [FamilyMemberController::class, 'deactivate'])->name('members.deactivate');
        Route::patch('members/{member}/activate', [FamilyMemberController::class, 'activate'])->name('members.activate');
        Route::patch('members/{member}/transfer-ownership', [FamilyMemberController::class, 'transferOwnership'])->name('members.transfer-ownership');
        Route::delete('members/{member}', [FamilyMemberController::class, 'destroy'])->name('members.destroy');

        // Family Wallets (stand-alone internal ledger)
        Route::resource('wallets', WalletController::class)->names('wallets');

        // Income (all income goes into a wallet)
        Route::get('incomes', [IncomeController::class, 'index'])->name('incomes.index');
        Route::get('incomes/create', [IncomeController::class, 'create'])->name('incomes.create');
        Route::post('incomes', [IncomeController::class, 'store'])->name('incomes.store');

        // Expenses (every expense reduces a wallet)
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');

        // Transfers (move money between wallets; total wealth unchanged)
        Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
        Route::get('transfers/create', [TransferController::class, 'create'])->name('transfers.create');
        Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');

        // Budgets (planning layer; monitor spending vs plan)
        Route::resource('budgets', BudgetController::class)->names('budgets');

        // Reconciliation (verify wallet balance matches actual)
        Route::get('reconciliations', [ReconciliationController::class, 'index'])->name('reconciliations.index');
        Route::get('reconciliations/create', [ReconciliationController::class, 'create'])->name('reconciliations.create');
        Route::post('reconciliations', [ReconciliationController::class, 'store'])->name('reconciliations.store');

        // Savings goals
        Route::resource('savings-goals', SavingsGoalController::class)->names('savings-goals');
        Route::get('savings-goals/{savings_goal}/contribute', [SavingsGoalController::class, 'contributeForm'])->name('savings-goals.contribute');
        Route::post('savings-goals/{savings_goal}/contribute', [SavingsGoalController::class, 'contributeStore'])->name('savings-goals.contribute.store');

        // Family Projects
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::get('projects-funding', [ProjectFundingController::class, 'index'])->name('projects.funding.index');
        Route::get('projects-funding/create', [ProjectFundingController::class, 'create'])->name('projects.funding.create');
        Route::post('projects-funding', [ProjectFundingController::class, 'store'])->name('projects.funding.store');

        // Family Properties
        Route::get('properties/assets', [PropertyController::class, 'index'])->name('properties.assets');
        Route::get('properties/create', [PropertyController::class, 'create'])->name('properties.create');
        Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
        Route::get('properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
        Route::get('properties/maintenance', [PropertyController::class, 'maintenance'])->name('properties.maintenance');
        Route::post('properties/maintenance', [PropertyController::class, 'storeMaintenance'])->name('properties.maintenance.store');
        Route::get('properties/valuations', [PropertyController::class, 'valuations'])->name('properties.valuations');
        Route::post('properties/valuations', [PropertyController::class, 'storeValuation'])->name('properties.valuations.store');
        Route::get('properties/documents', [PropertyController::class, 'documents'])->name('properties.documents');
        Route::post('properties/documents', [PropertyController::class, 'storeDocument'])->name('properties.documents.store');
        Route::get('properties/depreciation', [PropertyController::class, 'depreciation'])->name('properties.depreciation');
        Route::post('properties/depreciation', [PropertyController::class, 'storeDepreciation'])->name('properties.depreciation.store');
        Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show');

        // Financial Accounts (sidebar links)
        Route::get('accounts/income', [IncomeController::class, 'index'])->name('accounts.income');
        Route::get('accounts/expenses', [ExpenseController::class, 'index'])->name('accounts.expenses');
        Route::get('accounts/transfers', [TransferController::class, 'index'])->name('accounts.transfers');
        Route::get('accounts/savings', [SavingsGoalController::class, 'index'])->name('accounts.savings');
        Route::get('accounts/projects-funding', [ProjectFundingController::class, 'index'])->name('accounts.projects-funding');
        Route::get('accounts/reconciliation', [ReconciliationController::class, 'index'])->name('accounts.reconciliation');

        // Reports & Analytics
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/wallet-statement', [ReportController::class, 'walletStatement'])->name('reports.wallet-statement');
        Route::get('reports/expense', [ReportController::class, 'expense'])->name('reports.expense');
        Route::get('reports/income', [ReportController::class, 'income'])->name('reports.income');
        Route::get('reports/cash-flow', [ReportController::class, 'cashFlow'])->name('reports.cash-flow');
        Route::get('reports/budget-vs-actual', [ReportController::class, 'budgetVsActual'])->name('reports.budget-vs-actual');
        Route::get('reports/savings', [ReportController::class, 'savings'])->name('reports.savings');
        Route::get('reports/project-summary', [ReportController::class, 'projectSummary'])->name('reports.project-summary');
        Route::get('reports/property', [ReportController::class, 'property'])->name('reports.property');
    });

    // Platform Administration (Super Admin / Admin only)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Admin dashboard
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('permission:dashboard_view');

        // Users module
        Route::get('users', [UserController::class, 'index'])
            ->name('users.index')
            ->middleware('permission:users_view');
        Route::get('users/create', [UserController::class, 'create'])
            ->name('users.create')
            ->middleware('permission:users_create');
        Route::post('users', [UserController::class, 'store'])
            ->name('users.store')
            ->middleware('permission:users_create');
        Route::get('users/{user}', [UserController::class, 'show'])
            ->name('users.show')
            ->middleware('permission:users_view');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])
            ->name('users.edit')
            ->middleware('permission:users_update');
        Route::put('users/{user}', [UserController::class, 'update'])
            ->name('users.update')
            ->middleware('permission:users_update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy')
            ->middleware('permission:users_delete');

        // Roles module
        Route::get('roles', [RoleController::class, 'index'])
            ->name('roles.index')
            ->middleware('permission:roles_view');
        Route::get('roles/create', [RoleController::class, 'create'])
            ->name('roles.create')
            ->middleware('permission:roles_create');
        Route::post('roles', [RoleController::class, 'store'])
            ->name('roles.store')
            ->middleware('permission:roles_create');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
            ->name('roles.edit')
            ->middleware('permission:roles_update');
        Route::put('roles/{role}', [RoleController::class, 'update'])
            ->name('roles.update')
            ->middleware('permission:roles_update');
        Route::get('roles/{role}/permissions', [RoleController::class, 'editPermissions'])
            ->name('roles.permissions.edit')
            ->middleware('permission:roles_assign');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
            ->name('roles.permissions.update')
            ->middleware('permission:roles_assign');

        // Permissions module
        Route::get('permissions', [PermissionController::class, 'index'])
            ->name('permissions.index')
            ->middleware('permission:permissions_view');
        Route::get('permissions/create', [PermissionController::class, 'create'])
            ->name('permissions.create')
            ->middleware('permission:permissions_create');
        Route::post('permissions', [PermissionController::class, 'store'])
            ->name('permissions.store')
            ->middleware('permission:permissions_create');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])
            ->name('permissions.destroy')
            ->middleware('permission:permissions_delete');
        Route::delete('permissions/module/{module}', [PermissionController::class, 'destroyModule'])
            ->name('permissions.module.destroy')
            ->middleware('permission:permissions_delete');

        // Admin reports
        Route::get('reports/families', [AdminReportController::class, 'families'])
            ->name('reports.families')
            ->middleware('permission:reports_view|reports_general_view_dashboard|reports_finance_view');

        // Contact messages (landing page "Talk to the FamLedger team")
        Route::get('contact-messages', [ContactMessageController::class, 'index'])
            ->name('contact-messages.index');
        Route::get('contact-messages/{contact_message}', [ContactMessageController::class, 'show'])
            ->name('contact-messages.show');
        Route::patch('contact-messages/{contact_message}/read-status', [ContactMessageController::class, 'updateReadStatus'])
            ->name('contact-messages.read-status');
        Route::delete('contact-messages/{contact_message}', [ContactMessageController::class, 'destroy'])
            ->name('contact-messages.destroy');
    });
});

require __DIR__.'/auth.php';
