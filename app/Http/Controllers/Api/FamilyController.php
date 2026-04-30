<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency_code' => ['required', 'string', 'size:3'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'archived'])],
        ]);

        $family = DB::transaction(function () use ($validated, $request) {
            $family = Family::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'currency_code' => strtoupper($validated['currency_code']),
                'timezone' => $validated['timezone'] ?? 'Africa/Nairobi',
                'country' => $validated['country'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'created_by' => $request->user()->id,
            ]);

            $ownerRole = FamilyRole::query()->whereIn('name', ['Owner', 'owner'])->first();
            if ($ownerRole) {
                FamilyMember::create([
                    'family_id' => $family->id,
                    'user_id' => $request->user()->id,
                    'role_id' => $ownerRole->id,
                    'joined_at' => now(),
                    'status' => 'active',
                    'is_primary' => true,
                ]);
            }

            $family->ensureDefaultMainWallet($request->user()->id);

            return $family;
        });

        return response()->json([
            'message' => 'Family created.',
            'family' => [
                'id' => $family->id,
                'name' => $family->name,
                'currency_code' => $family->currency_code,
                'status' => $family->status,
                'description' => $family->description,
                'timezone' => $family->timezone,
                'country' => $family->country,
            ],
        ], 201);
    }

    public function update(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'currency_code' => ['sometimes', 'required', 'string', 'size:3'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:100'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(['active', 'archived'])],
        ]);

        if (array_key_exists('name', $validated)) {
            $family->name = $validated['name'];
        }
        if (array_key_exists('description', $validated)) {
            $family->description = $validated['description'];
        }
        if (array_key_exists('currency_code', $validated)) {
            $family->currency_code = strtoupper($validated['currency_code']);
        }
        if (array_key_exists('timezone', $validated)) {
            $family->timezone = $validated['timezone'];
        }
        if (array_key_exists('country', $validated)) {
            $family->country = $validated['country'];
        }
        if (array_key_exists('status', $validated)) {
            $family->status = $validated['status'];
        }
        $family->save();

        return response()->json([
            'message' => 'Family updated.',
            'family' => [
                'id' => $family->id,
                'name' => $family->name,
                'currency_code' => $family->currency_code,
                'status' => $family->status,
                'description' => $family->description,
                'timezone' => $family->timezone,
                'country' => $family->country,
            ],
        ]);
    }

    public function destroy(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $membership = FamilyMember::where('family_id', $family->id)
            ->where('user_id', $request->user()->id)
            ->with('role')
            ->first();

        if (($membership?->role?->name ?? '') !== 'Owner') {
            return response()->json([
                'message' => 'Only the family owner can delete this family.',
            ], 403);
        }

        $user = $request->user();
        $deletedFamilyId = (int) $family->id;
        $nextFamilyId = FamilyMember::query()
            ->where('user_id', $user->id)
            ->where('family_id', '!=', $deletedFamilyId)
            ->orderByDesc('id')
            ->value('family_id');

        DB::transaction(fn () => $family->delete());

        if ($request->hasSession() && (int) $request->session()->get('current_family_id') === $deletedFamilyId) {
            if ($nextFamilyId) {
                $request->session()->put('current_family_id', $nextFamilyId);
            } else {
                $request->session()->forget('current_family_id');
            }
        }

        return response()->json([
            'message' => 'Family deleted.',
        ]);
    }
}
