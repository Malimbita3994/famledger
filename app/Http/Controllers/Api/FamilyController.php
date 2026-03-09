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

    /**
     * List members of the family (for mobile app).
     */
    public function members(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $members = $family->familyMembers()
            ->with(['user:id,name,email', 'role:id,name'])
            ->orderByDesc('is_primary')
            ->orderBy('member_name')
            ->get();

        return response()->json([
            'members' => $members->map(fn ($m) => [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'name' => $m->member_name ?: $m->user?->name,
                'email' => $m->user?->email,
                'role' => $m->role ? ['id' => $m->role->id, 'name' => $m->role->name] : null,
                'status' => $m->status,
                'is_primary' => (bool) $m->is_primary,
                'joined_at' => $m->joined_at?->format('Y-m-d'),
            ]),
        ]);
    }
}
