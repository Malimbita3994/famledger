<?php

use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PropertyConfigController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController as MainDashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FamilyInvitationController;
use App\Http\Controllers\FamilyLiabilityController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\FamilyTreeController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\InviteJoinController;
use App\Http\Controllers\LandingSearchController;
use App\Http\Controllers\LegacyFamilyScopedUrlRedirectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFundingController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\VisionBoardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WealthController;
use App\Models\NotificationFaq;
use App\Models\NotificationSupportContact;
use App\Support\CurrentFamilyResolver;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['web', 'auth']]);

Route::get('/favicon.ico', function () {
    $png = public_path('images/logo.png');
    if (is_file($png)) {
        return response()->file($png, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    $svg = public_path('metronic/assets/media/app/logo-32.svg');
    if (is_file($svg)) {
        return response()->file($svg, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    abort(404);
})->name('favicon');

// Pulse UI Kit (Lottie helpers) — serve only the JS bundle files we need.
// These files live under resources/views/components/dist and are not publicly
// accessible by default, so we expose them via this narrow read-only route.
Route::get('/pulse-ui-kit/{file}', function (string $file) {
    $allowed = ['min.js', 'profile-lottie-data.js', 'profile.js'];
    if (! in_array($file, $allowed, true)) {
        abort(404);
    }

    $path = resource_path('views/components/dist/pulse-ui-kit/'.$file);
    if (! is_file($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('file', '[A-Za-z0-9_.-]+')->name('pulse-ui-kit.asset');

Route::get('/', function () {
    if (config('services.contact_captcha.driver') === 'math') {
        if (! session()->has('contact_math_a')) {
            session([
                'contact_math_a' => random_int(1, 9),
                'contact_math_b' => random_int(1, 9),
            ]);
        }
    }

    $useRecaptcha = config('services.contact_captcha.driver') === 'recaptcha';

    $landingFaqs = NotificationFaq::query()->active()->ordered()->get();
    $landingFaqGroups = $landingFaqs->groupBy(function (NotificationFaq $faq) {
        return trim((string) ($faq->group_label ?? ''));
    })->sortBy(fn ($items) => $items->min('sort_order'));

    $req = request();

    return view('marketing.landing', [
        'landingFaqs' => $landingFaqs,
        'landingFaqGroups' => $landingFaqGroups,
        'landingSupportContacts' => NotificationSupportContact::query()->active()->ordered()->get(),
        'contactCaptchaDriver' => config('services.contact_captcha.driver'),
        'recaptchaSiteKey' => $useRecaptcha ? config('services.recaptcha.site_key') : null,
        'currentFamily' => $req->user() ? CurrentFamilyResolver::family($req) : null,
    ]);
})->name('landing');

Route::get('/landing/search/suggestions', [LandingSearchController::class, 'suggestions'])->name('landing.search.suggestions');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Public invite join (by email token or family link token)
Route::get('invite/join', [InviteJoinController::class, 'show'])->name('invite.join');
Route::post('invite/join', [InviteJoinController::class, 'accept'])->name('invite.accept');

Route::get('/dashboard', [MainDashboardController::class, 'index'])->middleware(['auth', 'sync.current.family', 'must.change.password'])->name('dashboard');

Route::middleware(['auth', 'bind.account.family', 'sync.current.family', 'must.change.password'])->group(function () {
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

        // Notifications page: FAQ & contact / DND copy (shown to all users on /settings/notifications)
        Route::post('/settings/notifications/faqs', [SettingsController::class, 'storeNotificationFaq'])->name('settings.notifications.faqs.store');
        Route::put('/settings/notifications/faqs/{notification_faq}', [SettingsController::class, 'updateNotificationFaq'])->name('settings.notifications.faqs.update');
        Route::delete('/settings/notifications/faqs/{notification_faq}', [SettingsController::class, 'destroyNotificationFaq'])->name('settings.notifications.faqs.destroy');
        Route::post('/settings/notifications/support-contacts', [SettingsController::class, 'storeNotificationSupportContact'])->name('settings.notifications.support-contacts.store');
        Route::put('/settings/notifications/support-contacts/{notification_support_contact}', [SettingsController::class, 'updateNotificationSupportContact'])->name('settings.notifications.support-contacts.update');
        Route::delete('/settings/notifications/support-contacts/{notification_support_contact}', [SettingsController::class, 'destroyNotificationSupportContact'])->name('settings.notifications.support-contacts.destroy');
        Route::put('/settings/notifications/page-content', [SettingsController::class, 'updateNotificationPageContent'])->name('settings.notifications.page-content.update');
    });

    Route::get('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');

    // Global audit log: Super Admin and Auditor only (all platform activity)
    Route::middleware('role:Super Admin|Auditor')->group(function () {
        Route::get('/settings/audit-log', [SettingsController::class, 'auditLog'])->name('settings.audit-log');
        Route::get('/settings/audit-log/export-pdf', [SettingsController::class, 'auditLogExportPdf'])->name('settings.audit-log.export-pdf');
        Route::get('/settings/audit-log/export-csv', [SettingsController::class, 'auditLogExportCsv'])->name('settings.audit-log.export-csv');
    });

    // Family Management (CRUD)
    Route::resource('families', FamilyController::class);

    // Family currency switcher (updates display currency for this family)
    Route::patch('families/{family}/currency', [FamilyController::class, 'switchCurrency'])->name('families.currency.switch');

    // Session family: /family/... (members, invitations, profile)
    Route::prefix('family')->name('families.')->group(function () {
        Route::get('profile', [FamilyController::class, 'overview'])->name('profile');
        Route::get('overview', [FamilyController::class, 'overview'])->name('overview');

        Route::get('timeline', [TimelineController::class, 'index'])->name('timeline.index');
        Route::get('timeline/create', [TimelineController::class, 'create'])->name('timeline.create');
        Route::post('timeline', [TimelineController::class, 'store'])->name('timeline.store');
        Route::get('timeline/{milestone}', [TimelineController::class, 'show'])->name('timeline.show');
        Route::get('timeline/{milestone}/edit', [TimelineController::class, 'edit'])->name('timeline.edit');
        Route::put('timeline/{milestone}', [TimelineController::class, 'update'])->name('timeline.update');
        Route::delete('timeline/{milestone}', [TimelineController::class, 'destroy'])->name('timeline.destroy');
        Route::get('tree', [FamilyTreeController::class, 'index'])->name('tree.index');
        Route::post('tree/relationships', [FamilyTreeController::class, 'store'])->name('tree.relationships.store');
        Route::delete('tree/relationships/{relationship}', [FamilyTreeController::class, 'destroy'])->name('tree.relationships.destroy');
        Route::get('vision-board', [VisionBoardController::class, 'index'])->name('goals.index');
        Route::get('vision-board/create', [VisionBoardController::class, 'create'])->name('goals.create');
        Route::post('vision-board', [VisionBoardController::class, 'store'])->name('goals.store');
        Route::get('vision-board/{goal}/edit', [VisionBoardController::class, 'edit'])->name('goals.edit');
        Route::get('vision-board/{goal}', [VisionBoardController::class, 'show'])->name('goals.show');
        Route::put('vision-board/{goal}', [VisionBoardController::class, 'update'])->name('goals.update');
        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');

        Route::get('members', [FamilyMemberController::class, 'index'])->name('members.index');
        Route::get('members/create', [FamilyMemberController::class, 'create'])->name('members.create');
        Route::post('members', [FamilyMemberController::class, 'store'])->name('members.store');
        Route::get('members/{member}/edit', [FamilyMemberController::class, 'edit'])->name('members.edit');
        Route::put('members/{member}', [FamilyMemberController::class, 'update'])->name('members.update');
        Route::patch('members/{member}/deactivate', [FamilyMemberController::class, 'deactivate'])->name('members.deactivate');
        Route::patch('members/{member}/activate', [FamilyMemberController::class, 'activate'])->name('members.activate');
        Route::patch('members/{member}/transfer-ownership', [FamilyMemberController::class, 'transferOwnership'])->name('members.transfer-ownership');
        Route::delete('members/{member}', [FamilyMemberController::class, 'destroy'])->name('members.destroy');

        Route::get('invitation', [FamilyInvitationController::class, 'index'])->name('invites.index');
        Route::post('invitation', [FamilyInvitationController::class, 'store'])->name('invites.store');
        Route::post('invitation/reset-link', [FamilyInvitationController::class, 'resetLink'])->name('invites.reset-link');
        Route::delete('invitation/{invitation}', [FamilyInvitationController::class, 'destroy'])->name('invites.destroy');
    });

    // Ledger shortcuts: /accounts/...
    Route::prefix('accounts')->name('families.')->group(function () {
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');

        Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
        Route::get('transfers/create', [TransferController::class, 'create'])->name('transfers.create');
        Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');

        Route::get('savings', [SavingsGoalController::class, 'index'])->name('accounts.savings');
        Route::get('projects-funding', [ProjectFundingController::class, 'index'])->name('accounts.projects-funding');
        Route::get('reconciliation', [ReconciliationController::class, 'index'])->name('accounts.reconciliation');
    });

    // Current account (session family): /account/...
    Route::prefix('account')->name('families.')->group(function () {
        Route::get('wealth', [WealthController::class, 'index'])->name('wealth.index');
        Route::get('wealth/export-pdf', [WealthController::class, 'exportPdf'])->name('wealth.export-pdf');

        // Family Wallets (stand-alone internal ledger)
        Route::resource('wallets', WalletController::class)->names('wallets');

        // Family Liabilities (loans, debts, credit purchases)
        Route::get('liabilities', [FamilyLiabilityController::class, 'index'])->name('liabilities.index');
        Route::get('liabilities/create', [FamilyLiabilityController::class, 'create'])->name('liabilities.create');
        Route::post('liabilities', [FamilyLiabilityController::class, 'store'])->name('liabilities.store');
        Route::get('liabilities/{liability}', [FamilyLiabilityController::class, 'show'])->name('liabilities.show');
        Route::get('liabilities/{liability}/edit', [FamilyLiabilityController::class, 'edit'])->name('liabilities.edit');
        Route::put('liabilities/{liability}', [FamilyLiabilityController::class, 'update'])->name('liabilities.update');
        Route::delete('liabilities/{liability}', [FamilyLiabilityController::class, 'destroy'])->name('liabilities.destroy');

        // Income (all income goes into a wallet)
        Route::get('incomes', [IncomeController::class, 'index'])->name('incomes.index');
        Route::get('incomes/create', [IncomeController::class, 'create'])->name('incomes.create');
        Route::post('incomes', [IncomeController::class, 'store'])->name('incomes.store');
        Route::get('incomes/{income}', [IncomeController::class, 'show'])->name('incomes.show');
        Route::get('incomes/{income}/edit', [IncomeController::class, 'edit'])->name('incomes.edit');
        Route::put('incomes/{income}', [IncomeController::class, 'update'])->name('incomes.update');
        Route::delete('incomes/{income}', [IncomeController::class, 'destroy'])->name('incomes.destroy');

        // Expenses (every expense reduces a wallet)
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');

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
        Route::get('savings-goals/{savings_goal}/allocate', [SavingsGoalController::class, 'allocateForm'])->name('savings-goals.allocate');
        Route::post('savings-goals/{savings_goal}/allocate', [SavingsGoalController::class, 'allocateStore'])->name('savings-goals.allocate.store');

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

        // PDF Exports
        Route::get('reports/export-pdf', [ReportController::class, 'exportOverviewPdf'])->name('reports.export-pdf');
        Route::get('reports/cash-flow/export-pdf', [ReportController::class, 'exportCashFlowPdf'])->name('reports.cash-flow.export-pdf');
        Route::get('reports/finance/export-pdf', [ReportController::class, 'exportFinanceReportPdf'])->name('reports.finance.export-pdf');
        Route::get('reports/budget-vs-actual/export-pdf', [ReportController::class, 'exportBudgetVsActualPdf'])->name('reports.budget-vs-actual.export-pdf');
        Route::get('reports/project-summary/export-pdf', [ReportController::class, 'exportProjectSummaryPdf'])->name('reports.project-summary.export-pdf');
        Route::get('reports/property/export-pdf', [ReportController::class, 'exportPropertyPdf'])->name('reports.property.export-pdf');

        // Elasticsearch-backed transaction search
        Route::get('search', [SearchController::class, 'index'])->name('search.index');
        Route::get('search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

        // Audit trail (application + database) for this family
        Route::get('audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
        Route::get('audit-trail/export', [AuditTrailController::class, 'exportPdf'])->name('audit-trail.export');
    });

    // Old /account/... paths → /family/... or /accounts/...
    $redirectAccountPath = function (string $targetBase) {
        return function () use ($targetBase) {
            $q = request()->getQueryString();
            $suffix = request()->route('tail');
            $path = $targetBase.($suffix ? '/'.$suffix : '');

            return redirect()->to($path.($q ? '?'.$q : ''), 301);
        };
    };

    Route::any('account/members', $redirectAccountPath('/family/members'));
    Route::any('account/members/{tail}', $redirectAccountPath('/family/members'))->where('tail', '.*');

    Route::any('account/invites', $redirectAccountPath('/family/invitation'));
    Route::any('account/invites/{tail}', $redirectAccountPath('/family/invitation'))->where('tail', '.*');

    Route::any('account/transactions', $redirectAccountPath('/accounts/transactions'));
    Route::any('account/transactions/{tail}', $redirectAccountPath('/accounts/transactions'))->where('tail', '.*');

    Route::any('account/transfers', $redirectAccountPath('/accounts/transfers'));
    Route::any('account/transfers/{tail}', $redirectAccountPath('/accounts/transfers'))->where('tail', '.*');

    Route::any('account/accounts/{tail?}', function (?string $tail = null) {
        $q = request()->getQueryString();
        $path = '/accounts'.($tail ? '/'.$tail : '');

        return redirect()->to($path.($q ? '?'.$q : ''), 301);
    })->where('tail', '.*');

    Route::get('families/{family}/{path}', LegacyFamilyScopedUrlRedirectController::class)
        ->where('path', '[A-Za-z0-9][A-Za-z0-9_\-/]*')
        ->name('legacy.family-scoped');

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
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])
            ->name('roles.destroy')
            ->middleware('permission:roles_delete');
        Route::get('roles/{role}/permissions', [RoleController::class, 'editPermissions'])
            ->name('roles.permissions.edit')
            ->middleware('permission:roles_view|roles_assign');
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
            ->name('contact-messages.index')
            ->middleware('permission:contact_messages_view');
        Route::get('contact-messages/{contact_message}/modal', [ContactMessageController::class, 'modal'])
            ->name('contact-messages.modal')
            ->middleware('permission:contact_messages_view');
        Route::get('contact-messages/{contact_message}', [ContactMessageController::class, 'show'])
            ->name('contact-messages.show')
            ->middleware('permission:contact_messages_view');
        Route::patch('contact-messages/{contact_message}/read-status', [ContactMessageController::class, 'updateReadStatus'])
            ->name('contact-messages.read-status')
            ->middleware('permission:contact_messages_mark_read');
        Route::delete('contact-messages/{contact_message}', [ContactMessageController::class, 'destroy'])
            ->name('contact-messages.destroy')
            ->middleware('permission:contact_messages_delete');
    });
});

require __DIR__.'/auth.php';
