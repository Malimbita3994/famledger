<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Request $request): JsonResponse
    {
        $families = $request->user()
            ->families()
            ->orderByPivot('created_at', 'desc')
            ->get(['families.id', 'families.name', 'families.currency_code', 'families.status']);

        return response()->json([
            'families' => $families->map(fn (Family $f) => [
                'id' => $f->id,
                'name' => $f->name,
                'currency_code' => $f->currency_code,
                'status' => $f->status,
            ]),
        ]);
    }

    public function show(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        return response()->json([
            'id' => $family->id,
            'name' => $family->name,
            'currency_code' => $family->currency_code,
            'status' => $family->status,
        ]);
    }
}
