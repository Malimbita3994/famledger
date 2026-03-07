<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    public function incomeCategories(): JsonResponse
    {
        $categories = IncomeCategory::defaults();
        return response()->json([
            'categories' => $categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]),
        ]);
    }

    public function expenseCategories(): JsonResponse
    {
        $categories = ExpenseCategory::defaults();
        return response()->json([
            'categories' => $categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]),
        ]);
    }
}
