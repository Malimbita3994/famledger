<?php

namespace App\Http\Controllers;

use App\Services\FamilyFinancialService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, FamilyFinancialService $financials)
    {
        if ($request->user()->can('access_admin_panel')) {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard', $financials->webDashboardPayload($request->user()));
    }
}
