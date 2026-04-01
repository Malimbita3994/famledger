<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FamilyFinancialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, FamilyFinancialService $financials): JsonResponse
    {
        return response()->json($financials->apiDashboardPayload($request->user()));
    }
}
