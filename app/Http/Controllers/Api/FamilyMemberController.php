<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FamilyMemberController extends Controller
{
    use AuthorizesFamilyMember;

    public function memberRoles(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $roles = FamilyRole::query()
            ->orderBy('id')
            ->get(['id', 'name']);

        return response()->json(['roles' => $roles]);
    }

    public function index(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $members = FamilyMember::query()
            ->where('family_id', $family->id)
            ->with(['user:id,name,email', 'role:id,name'])
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get()
            ->map(function (FamilyMember $member) {
                return [
                    'id' => $member->id,
                    'user_id' => $member->user_id,
                    'name' => $member->member_name ?: $member->user?->name,
                    'email' => $member->user?->email,
                    'status' => $member->status,
                    'is_primary' => (bool) $member->is_primary,
                    'role' => $member->role ? [
                        'id' => $member->role->id,
                        'name' => $member->role->name,
                    ] : null,
                    'leave_reason' => $member->leave_reason,
                    'leave_notes' => $member->leave_notes,
                ];
            });

        return response()->json(['members' => $members]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'member_name' => ['nullable', 'string', 'max:255'],
            'role_id' => ['required', Rule::exists('family_roles', 'id')],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();
        if (! $user) {
            $user = User::create([
                'name' => $validated['member_name'] ?: Str::before($validated['email'], '@'),
                'email' => $validated['email'],
                'password' => Str::password(16),
                'status' => User::STATUS_ACTIVE,
                'created_by' => auth()->id(),
            ]);
        }

        $existing = FamilyMember::query()
            ->where('family_id', $family->id)
            ->where('user_id', $user->id)
            ->first();
        if ($existing) {
            return response()->json(['message' => 'This user is already in this family.'], 422);
        }

        $member = FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'role_id' => $validated['role_id'],
            'member_name' => $validated['member_name'] ?? null,
            'joined_at' => now(),
            'status' => 'pending',
            'is_primary' => false,
        ]);

        $member->load(['user:id,name,email', 'role:id,name']);

        return response()->json([
            'message' => 'Member invited.',
            'member' => [
                'id' => $member->id,
                'name' => $member->member_name ?: $member->user?->name,
                'email' => $member->user?->email,
                'status' => $member->status,
                'is_primary' => (bool) $member->is_primary,
                'role' => $member->role ? ['id' => $member->role->id, 'name' => $member->role->name] : null,
            ],
        ], 201);
    }

    public function update(Request $request, Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        $this->ensureMemberBelongsToFamily($family, $member);

        $validated = $request->validate([
            'member_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'role_id' => ['sometimes', 'required', Rule::exists('family_roles', 'id')],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('member_name', $validated)) {
            $member->member_name = $validated['member_name'];
        }
        if (array_key_exists('role_id', $validated)) {
            $member->role_id = $validated['role_id'];
        }
        if (array_key_exists('is_primary', $validated)) {
            if ($validated['is_primary']) {
                FamilyMember::query()->where('family_id', $family->id)->update(['is_primary' => false]);
            }
            $member->is_primary = (bool) $validated['is_primary'];
        }
        $member->save();

        return response()->json(['message' => 'Member updated.']);
    }

    public function activate(Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        $this->ensureMemberBelongsToFamily($family, $member);

        $member->update(['status' => 'active']);

        return response()->json(['message' => 'Member activated.']);
    }

    public function deactivate(Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        $this->ensureMemberBelongsToFamily($family, $member);

        if ($member->is_primary) {
            return response()->json(['message' => 'Cannot deactivate the primary owner.'], 422);
        }

        $member->update(['status' => 'inactive']);

        return response()->json(['message' => 'Member deactivated.']);
    }

    public function approveLeave(Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        $this->ensureMemberBelongsToFamily($family, $member);

        if ($member->is_primary) {
            return response()->json(['message' => 'Primary owner cannot leave without ownership transfer.'], 422);
        }

        $member->delete();

        return response()->json(['message' => 'Leave request approved.']);
    }

    public function rejectLeave(Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        $this->ensureMemberBelongsToFamily($family, $member);

        $member->update([
            'status' => 'active',
            'leave_reason' => null,
            'leave_notes' => null,
            'leave_requested_at' => null,
        ]);

        return response()->json(['message' => 'Leave request rejected.']);
    }

    public function leave(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $membership = FamilyMember::query()
            ->where('family_id', $family->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($membership->is_primary) {
            return response()->json([
                'message' => 'Primary owner cannot request leave without ownership transfer.',
            ], 422);
        }

        $membership->update([
            'status' => 'pending_leave',
            'leave_reason' => $validated['reason'],
            'leave_notes' => $validated['notes'] ?? null,
            'leave_requested_at' => now(),
        ]);

        return response()->json(['message' => 'Leave request submitted.']);
    }

    public function destroy(Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        $this->ensureMemberBelongsToFamily($family, $member);

        if ($member->is_primary) {
            return response()->json(['message' => 'Cannot remove the primary owner.'], 422);
        }
        if ($member->user_id === auth()->id()) {
            return response()->json(['message' => 'Use leave request to remove yourself.'], 422);
        }

        $member->delete();

        return response()->json(['message' => 'Member removed.']);
    }

    private function ensureMemberBelongsToFamily(Family $family, FamilyMember $member): void
    {
        if ($member->family_id !== $family->id) {
            abort(404);
        }
    }
}

