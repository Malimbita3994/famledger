<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\SavingsGoalController;
use App\Http\Controllers\Api\LiabilityController;
use App\Http\Controllers\Api\ReconciliationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectFundingController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AuditTrailController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard summary (uses first family or optional family_id)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Families the user belongs to
    Route::get('/families', [FamilyController::class, 'index']);
    Route::get('/families/{family}', [FamilyController::class, 'show']);
    Route::get('/families/{family}/members', [FamilyController::class, 'members']);

    // Family-scoped resources (user must be member)
    Route::get('/families/{family}/wallets', [WalletController::class, 'index']);
    Route::get('/families/{family}/wallets/{wallet}', [WalletController::class, 'show']);

    Route::get('/families/{family}/incomes', [IncomeController::class, 'index']);
    Route::post('/families/{family}/incomes', [IncomeController::class, 'store']);

    Route::get('/families/{family}/expenses', [ExpenseController::class, 'index']);
    Route::post('/families/{family}/expenses', [ExpenseController::class, 'store']);

    Route::get('/families/{family}/transfers', [TransferController::class, 'index']);
    Route::post('/families/{family}/transfers', [TransferController::class, 'store']);

    Route::get('/families/{family}/budgets', [BudgetController::class, 'index']);
    Route::get('/families/{family}/savings-goals', [SavingsGoalController::class, 'index']);
    Route::get('/families/{family}/liabilities', [LiabilityController::class, 'index']);
    Route::get('/families/{family}/reconciliations', [ReconciliationController::class, 'index']);

    Route::get('/families/{family}/projects-funding', [ProjectFundingController::class, 'index']);
    Route::get('/families/{family}/projects', [ProjectController::class, 'index']);
    Route::get('/families/{family}/projects/{project}', [ProjectController::class, 'show']);

    // Property (assets, maintenance, valuations, documents, depreciation)
    Route::get('/families/{family}/properties/maintenance', [PropertyController::class, 'maintenance']);
    Route::get('/families/{family}/properties/valuations', [PropertyController::class, 'valuations']);
    Route::get('/families/{family}/properties/documents', [PropertyController::class, 'documents']);
    Route::get('/families/{family}/properties/depreciation', [PropertyController::class, 'depreciation']);
    Route::get('/families/{family}/properties', [PropertyController::class, 'index']);
    Route::get('/families/{family}/properties/{property}', [PropertyController::class, 'show']);

    // Reports
    Route::get('/families/{family}/reports/summary', [ReportController::class, 'summary']);
    Route::get('/families/{family}/reports/wallet-statement', [ReportController::class, 'walletStatement']);
    Route::get('/families/{family}/reports/expense', [ReportController::class, 'expense']);
    Route::get('/families/{family}/reports/income', [ReportController::class, 'income']);
    Route::get('/families/{family}/reports/cash-flow', [ReportController::class, 'cashFlow']);
    Route::get('/families/{family}/reports/budget-vs-actual', [ReportController::class, 'budgetVsActual']);
    Route::get('/families/{family}/reports/savings', [ReportController::class, 'savings']);
    Route::get('/families/{family}/reports/project-summary', [ReportController::class, 'projectSummary']);
    Route::get('/families/{family}/reports/property', [ReportController::class, 'property']);

    // Audit trail (family-scoped; Owner/Co-owner or Super Admin/Auditor only)
    Route::get('/families/{family}/audit-trail', [AuditTrailController::class, 'index']);

    // Lookups for forms (income/expense categories)
    Route::get('/income-categories', [LookupController::class, 'incomeCategories']);
    Route::get('/expense-categories', [LookupController::class, 'expenseCategories']);

    // Administration (Super Admin / Admin / access_admin_panel only)
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::get('/roles', [AdminRoleController::class, 'index']);
        Route::get('/reports/families', [AdminReportController::class, 'families']);
        Route::get('/contact-messages', [AdminContactMessageController::class, 'index']);
        Route::get('/contact-messages/{contact_message}', [AdminContactMessageController::class, 'show']);
        Route::patch('/contact-messages/{contact_message}/read-status', [AdminContactMessageController::class, 'updateReadStatus']);
    });
});
