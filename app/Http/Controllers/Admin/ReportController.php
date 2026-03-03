<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Family list report for Super Admin — families with their owners.
     */
    public function families(Request $request): View
    {
        $families = Family::with(['creator:id,name,email', 'familyMembers' => fn ($q) => $q->with('user:id,name,email')])
            ->orderBy('name')
            ->get();

        return view('admin.reports.families', [
            'families' => $families,
        ]);
    }
}
