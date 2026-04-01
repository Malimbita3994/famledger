<?php

use App\Http\Controllers\Api\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuditTrailController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\FamilyMemberController;
use App\Http\Controllers\Api\FamilyTreeController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\LiabilityController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectFundingController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReconciliationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SavingsGoalController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\UserSettingsController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth-api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['auth:sanctum', 'throttle:api', 'must.change.password'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [UserSettingsController::class, 'updateProfile']);
    Route::patch('/user/profile', [UserSettingsController::class, 'updateProfile']);
    Route::post('/user/password', [UserSettingsController::class, 'updatePassword']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/families', [FamilyController::class, 'index']);
    Route::post('/families', [FamilyController::class, 'store']);
    Route::get('/families/{family}', [FamilyController::class, 'show']);
    Route::put('/families/{family}', [FamilyController::class, 'update']);
    Route::patch('/families/{family}', [FamilyController::class, 'update']);
    Route::delete('/families/{family}', [FamilyController::class, 'destroy']);

    Route::get('/families/{family}/members', [FamilyMemberController::class, 'index']);
    Route::post('/families/{family}/members', [FamilyMemberController::class, 'store']);
    Route::put('/families/{family}/members/{member}', [FamilyMemberController::class, 'update']);
    Route::patch('/families/{family}/members/{member}', [FamilyMemberController::class, 'update']);
    Route::patch('/families/{family}/members/{member}/activate', [FamilyMemberController::class, 'activate']);
    Route::patch('/families/{family}/members/{member}/deactivate', [FamilyMemberController::class, 'deactivate']);
    Route::patch('/families/{family}/members/{member}/approve-leave', [FamilyMemberController::class, 'approveLeave']);
    Route::patch('/families/{family}/members/{member}/reject-leave', [FamilyMemberController::class, 'rejectLeave']);
    Route::delete('/families/{family}/members/{member}', [FamilyMemberController::class, 'destroy']);
    Route::get('/families/{family}/member-roles', [FamilyMemberController::class, 'memberRoles']);
    Route::post('/families/{family}/leave', [FamilyMemberController::class, 'leave']);

    Route::get('/families/{family}/wallets', [WalletController::class, 'index']);
    Route::post('/families/{family}/wallets', [WalletController::class, 'store']);
    Route::get('/families/{family}/wallets/{wallet}', [WalletController::class, 'show']);
    Route::put('/families/{family}/wallets/{wallet}', [WalletController::class, 'update']);
    Route::patch('/families/{family}/wallets/{wallet}', [WalletController::class, 'update']);
    Route::delete('/families/{family}/wallets/{wallet}', [WalletController::class, 'destroy']);

    Route::get('/families/{family}/incomes', [IncomeController::class, 'index']);
    Route::post('/families/{family}/incomes', [IncomeController::class, 'store']);
    Route::put('/families/{family}/incomes/{income}', [IncomeController::class, 'update']);
    Route::patch('/families/{family}/incomes/{income}', [IncomeController::class, 'update']);
    Route::delete('/families/{family}/incomes/{income}', [IncomeController::class, 'destroy']);

    Route::get('/families/{family}/expenses', [ExpenseController::class, 'index']);
    Route::post('/families/{family}/expenses', [ExpenseController::class, 'store']);
    Route::put('/families/{family}/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::patch('/families/{family}/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/families/{family}/expenses/{expense}', [ExpenseController::class, 'destroy']);

    Route::get('/families/{family}/transfers', [TransferController::class, 'index']);
    Route::post('/families/{family}/transfers', [TransferController::class, 'store']);
    Route::put('/families/{family}/transfers/{transfer}', [TransferController::class, 'update']);
    Route::patch('/families/{family}/transfers/{transfer}', [TransferController::class, 'update']);
    Route::delete('/families/{family}/transfers/{transfer}', [TransferController::class, 'destroy']);

    Route::get('/families/{family}/budgets', [BudgetController::class, 'index']);
    Route::post('/families/{family}/budgets', [BudgetController::class, 'store']);
    Route::put('/families/{family}/budgets/{budget}', [BudgetController::class, 'update']);
    Route::patch('/families/{family}/budgets/{budget}', [BudgetController::class, 'update']);
    Route::delete('/families/{family}/budgets/{budget}', [BudgetController::class, 'destroy']);

    Route::get('/families/{family}/savings-goals', [SavingsGoalController::class, 'index']);
    Route::post('/families/{family}/savings-goals', [SavingsGoalController::class, 'store']);
    Route::put('/families/{family}/savings-goals/{goal}', [SavingsGoalController::class, 'update']);
    Route::patch('/families/{family}/savings-goals/{goal}', [SavingsGoalController::class, 'update']);
    Route::delete('/families/{family}/savings-goals/{goal}', [SavingsGoalController::class, 'destroy']);

    Route::get('/families/{family}/liabilities', [LiabilityController::class, 'index']);
    Route::post('/families/{family}/liabilities', [LiabilityController::class, 'store']);
    Route::put('/families/{family}/liabilities/{liability}', [LiabilityController::class, 'update']);
    Route::patch('/families/{family}/liabilities/{liability}', [LiabilityController::class, 'update']);
    Route::delete('/families/{family}/liabilities/{liability}', [LiabilityController::class, 'destroy']);

    Route::get('/families/{family}/reconciliations', [ReconciliationController::class, 'index']);
    Route::post('/families/{family}/reconciliations', [ReconciliationController::class, 'store']);
    Route::put('/families/{family}/reconciliations/{reconciliation}', [ReconciliationController::class, 'update']);
    Route::patch('/families/{family}/reconciliations/{reconciliation}', [ReconciliationController::class, 'update']);
    Route::delete('/families/{family}/reconciliations/{reconciliation}', [ReconciliationController::class, 'destroy']);

    Route::get('/families/{family}/projects', [ProjectController::class, 'index']);
    Route::post('/families/{family}/projects', [ProjectController::class, 'store']);
    Route::get('/families/{family}/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/families/{family}/projects/{project}', [ProjectController::class, 'update']);
    Route::patch('/families/{family}/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/families/{family}/projects/{project}', [ProjectController::class, 'destroy']);

    Route::get('/families/{family}/projects-funding', [ProjectFundingController::class, 'index']);
    Route::post('/families/{family}/projects-funding', [ProjectFundingController::class, 'store']);

    Route::get('/families/{family}/properties', [PropertyController::class, 'index']);
    Route::post('/families/{family}/properties', [PropertyController::class, 'store']);
    Route::get('/families/{family}/properties/{property}', [PropertyController::class, 'show']);
    Route::put('/families/{family}/properties/{property}', [PropertyController::class, 'update']);
    Route::patch('/families/{family}/properties/{property}', [PropertyController::class, 'update']);
    Route::delete('/families/{family}/properties/{property}', [PropertyController::class, 'destroy']);
    Route::get('/families/{family}/properties/maintenance', [PropertyController::class, 'maintenance']);
    Route::post('/families/{family}/properties/maintenance', [PropertyController::class, 'storeMaintenance']);
    Route::put('/families/{family}/properties/maintenance/{maintenance}', [PropertyController::class, 'updateMaintenance']);
    Route::patch('/families/{family}/properties/maintenance/{maintenance}', [PropertyController::class, 'updateMaintenance']);
    Route::delete('/families/{family}/properties/maintenance/{maintenance}', [PropertyController::class, 'destroyMaintenance']);
    Route::get('/families/{family}/properties/valuations', [PropertyController::class, 'valuations']);
    Route::get('/families/{family}/properties/documents', [PropertyController::class, 'documents']);
    Route::post('/families/{family}/properties/documents', [PropertyController::class, 'storeDocument']);
    Route::put('/families/{family}/properties/documents/{document}', [PropertyController::class, 'updateDocument']);
    Route::patch('/families/{family}/properties/documents/{document}', [PropertyController::class, 'updateDocument']);
    Route::delete('/families/{family}/properties/documents/{document}', [PropertyController::class, 'destroyDocument']);
    Route::get('/families/{family}/properties/depreciation', [PropertyController::class, 'depreciation']);
    Route::post('/families/{family}/properties/depreciation', [PropertyController::class, 'storeDepreciation']);
    Route::put('/families/{family}/properties/depreciation/{depreciation}', [PropertyController::class, 'updateDepreciation']);
    Route::patch('/families/{family}/properties/depreciation/{depreciation}', [PropertyController::class, 'updateDepreciation']);
    Route::delete('/families/{family}/properties/depreciation/{depreciation}', [PropertyController::class, 'destroyDepreciation']);

    Route::get('/families/{family}/reports/summary', [ReportController::class, 'summary']);
    Route::get('/families/{family}/reports/wallet-statement', [ReportController::class, 'walletStatement']);
    Route::get('/families/{family}/reports/expense', [ReportController::class, 'expense']);
    Route::get('/families/{family}/reports/income', [ReportController::class, 'income']);
    Route::get('/families/{family}/reports/cash-flow', [ReportController::class, 'cashFlow']);
    Route::get('/families/{family}/reports/budget-vs-actual', [ReportController::class, 'budgetVsActual']);
    Route::get('/families/{family}/reports/savings', [ReportController::class, 'savings']);
    Route::get('/families/{family}/reports/project-summary', [ReportController::class, 'projectSummary']);
    Route::get('/families/{family}/audit-trail', [AuditTrailController::class, 'index']);

    // Family Tree routes
    Route::get('/families/{family}/tree', [FamilyTreeController::class, 'index'])->name('api.families.tree.index');
    Route::post('/families/{family}/relationships', [FamilyTreeController::class, 'store'])->name('api.families.relationships.store');
    Route::delete('/families/{family}/relationships/{relationship}', [FamilyTreeController::class, 'destroy'])->name('api.families.relationships.destroy');

    Route::prefix('/admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::patch('/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

        Route::get('/roles', [AdminRoleController::class, 'index']);
        Route::post('/roles', [AdminRoleController::class, 'store']);
        Route::get('/roles/{role}', [AdminRoleController::class, 'show']);
        Route::put('/roles/{role}', [AdminRoleController::class, 'update']);
        Route::patch('/roles/{role}', [AdminRoleController::class, 'update']);
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy']);
        Route::put('/roles/{role}/permissions', [AdminRoleController::class, 'syncPermissions']);
        Route::patch('/roles/{role}/permissions', [AdminRoleController::class, 'syncPermissions']);

        Route::get('/permissions', [AdminPermissionController::class, 'index']);
        Route::post('/permissions', [AdminPermissionController::class, 'store']);
        Route::put('/permissions/{permission}', [AdminPermissionController::class, 'update']);
        Route::patch('/permissions/{permission}', [AdminPermissionController::class, 'update']);
        Route::delete('/permissions/{permission}', [AdminPermissionController::class, 'destroy']);

        Route::get('/reports/families', [AdminReportController::class, 'families']);

        Route::get('/contact-messages', [AdminContactMessageController::class, 'index']);
        Route::get('/contact-messages/{contact_message}', [AdminContactMessageController::class, 'show']);
        Route::patch('/contact-messages/{contact_message}/read-status', [AdminContactMessageController::class, 'updateReadStatus']);
    });

    Route::get('/income-categories', [LookupController::class, 'incomeCategories']);
    Route::get('/expense-categories', [LookupController::class, 'expenseCategories']);
});
