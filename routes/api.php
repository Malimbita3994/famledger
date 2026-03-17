<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\LookupController;
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

    // Family-scoped resources (user must be member)
    Route::get('/families/{family}/wallets', [WalletController::class, 'index']);
    Route::get('/families/{family}/wallets/{wallet}', [WalletController::class, 'show']);

    Route::get('/families/{family}/incomes', [IncomeController::class, 'index']);
    Route::post('/families/{family}/incomes', [IncomeController::class, 'store']);

    Route::get('/families/{family}/expenses', [ExpenseController::class, 'index']);
    Route::post('/families/{family}/expenses', [ExpenseController::class, 'store']);

    Route::get('/families/{family}/transfers', [TransferController::class, 'index']);
    Route::post('/families/{family}/transfers', [TransferController::class, 'store']);

    // Lookups for forms (income/expense categories)
    Route::get('/income-categories', [LookupController::class, 'incomeCategories']);
    Route::get('/expense-categories', [LookupController::class, 'expenseCategories']);
});
