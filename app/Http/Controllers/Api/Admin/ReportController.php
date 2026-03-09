<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use AuthorizesAdmin;

    /**
     * Families report — list families with owner info.
     */
    public function families(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $families = Family::with(['familyMembers' => fn ($q) => $q->where('is_primary', true)->with('user:id,name,email')])
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'currency_code', 'country', 'created_at']);

        $items = $families->map(function (Family $f) {
            $primary = $f->familyMembers->first();
            return [
                'id' => $f->id,
                'name' => $f->name,
                'status' => $f->status,
                'currency_code' => $f->currency_code,
                'country' => $f->country,
                'owner' => $primary?->user ? [
                    'id' => $primary->user->id,
                    'name' => $primary->user->name,
                    'email' => $primary->user->email,
                ] : null,
                'created_at' => $f->created_at?->toIso8601String(),
            ];
        });

        return response()->json(['families' => $items]);
    }
}
