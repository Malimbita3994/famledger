<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesFamilyMember;
use App\Http\Controllers\Controller;
use App\Mail\MemberCredentialsMail;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

    /**
     * Create a new family (and its first owner + main wallet).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->families()->exists()) {
            return response()->json([
                'message' => 'You can only belong to one family. Leave your existing family before creating a new one.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency_code' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $ownerRole = FamilyRole::where('name', 'Owner')->firstOrFail();

        $family = DB::transaction(function () use ($validated, $user, $ownerRole) {
            $family = Family::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'currency_code' => strtoupper($validated['currency_code']),
                'timezone' => $validated['timezone'],
                'country' => $validated['country'] ?? null,
                'created_by' => $user->id,
                'status' => 'active',
            ]);

            FamilyMember::create([
                'family_id' => $family->id,
                'user_id' => $user->id,
                'role_id' => $ownerRole->id,
                'is_primary' => true,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $family->wallets()->create([
                'name' => 'Main account',
                'type' => 'cash',
                'currency_code' => $family->currency_code,
                'description' => 'Primary family wallet (central account).',
                'initial_balance' => 0,
                'is_primary' => true,
                'is_shared' => true,
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            return $family;
        });

        return response()->json([
            'message' => 'Family created.',
            'family' => [
                'id' => $family->id,
                'name' => $family->name,
                'currency_code' => $family->currency_code,
                'status' => $family->status,
            ],
        ], 201);
    }

    public function update(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);
        $this->authorizeManageFamilySettings($family);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency_code' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'archived'])],
        ]);

        $family->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'currency_code' => strtoupper($validated['currency_code']),
            'timezone' => $validated['timezone'],
            'country' => $validated['country'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Family updated.',
            'family' => [
                'id' => $family->id,
                'name' => $family->name,
                'currency_code' => $family->currency_code,
                'status' => $family->status,
            ],
        ]);
    }

    /**
     * Ensure only owners / co-owners can change core family settings (including currency).
     */
    private function authorizeManageFamilySettings(Family $family): void
    {
        $membership = FamilyMember::where('family_id', $family->id)
            ->where('user_id', auth()->id())
            ->with('role')
            ->first();

        if (! $membership || ! in_array($membership->role->name ?? '', ['Owner', 'Co-Owner', 'Co-owner'], true)) {
            abort(403, 'Only the owner or co-owner can update family settings.');
        }
    }

    public function destroy(Family $family): JsonResponse
    {
        $this->authorizeFamilyMember($family);

        $family->delete();

        return response()->json([
            'message' => 'Family deleted.',
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
            'members' => $members->map(fn (FamilyMember $m) => $this->formatMember($m)),
        ]);
    }

    /**
     * Available roles for managing members (Owner/Co-owner only).
     */
    public function memberRoles(Family $family): JsonResponse
    {
        $this->authorizeManageMembers($family);

        $roles = FamilyRole::orderBy('id')->where('name', '!=', 'Child')->get(['id', 'name', 'description']);

        return response()->json([
            'roles' => $roles->map(fn (FamilyRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
            ]),
        ]);
    }

    /**
     * Add a member (invite or attach existing user).
     */
    public function addMember(Request $request, Family $family): JsonResponse
    {
        $this->authorizeManageMembers($family);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'member_name' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'member_type' => ['nullable', Rule::in(['adult', 'child'])],
            'role_id' => ['nullable', 'exists:family_roles,id'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        $justCreated = false;

        if (! $user) {
            $plainPassword = Str::password(12);
            $user = User::create([
                'name' => $validated['member_name'] ?? Str::before($validated['email'], '@'),
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
            ]);
            $justCreated = true;

            try {
                Mail::to($user->email)->send(new MemberCredentialsMail(
                    family: $family,
                    email: $user->email,
                    plainPassword: $plainPassword,
                    memberName: $validated['member_name'] ?? ''
                ));
            } catch (\Throwable $e) {
                // Ignore mail failures for API; account is still created.
            }
        } else {
            if ($family->members()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'message' => 'This user is already a member of this family.',
                ], 422);
            }
        }

        $role = isset($validated['role_id'])
            ? FamilyRole::findOrFail($validated['role_id'])
            : (FamilyRole::where('name', 'Member')->first()
                ?? FamilyRole::whereNotIn('name', ['Owner', 'Child'])->orderBy('id')->first());

        $isPrimary = false;
        if ($role->name === 'Owner') {
            $existingPrimary = FamilyMember::where('family_id', $family->id)->where('is_primary', true)->first();
            $isPrimary = ! $existingPrimary;
        }

        $member = FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'member_name' => $validated['member_name'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'member_type' => $validated['member_type'] ?? null,
            'is_primary' => $isPrimary,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $member->load(['user:id,name,email', 'role:id,name']);

        return response()->json([
            'message' => $justCreated
                ? 'Member added. They will receive an email with login credentials.'
                : 'Member added successfully.',
            'member' => $this->formatMember($member),
        ], 201);
    }

    public function updateMember(Request $request, Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeManageMembers($family);
        if ($member->family_id !== $family->id) {
            abort(404);
        }

        $validated = $request->validate([
            'member_name' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'member_type' => ['nullable', Rule::in(['adult', 'child'])],
            'role_id' => ['required', 'exists:family_roles,id'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $newRole = FamilyRole::findOrFail($validated['role_id']);
        $isPrimary = (bool) ($validated['is_primary'] ?? false);

        if ($isPrimary && ! $member->is_primary) {
            FamilyMember::where('family_id', $family->id)->update(['is_primary' => false]);
        }

        $member->update([
            'member_name' => $validated['member_name'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'member_type' => $validated['member_type'] ?? null,
            'role_id' => $validated['role_id'],
            'is_primary' => $newRole->name === 'Owner' ? $isPrimary : false,
        ]);

        $member->load(['user:id,name,email', 'role:id,name']);

        return response()->json([
            'message' => 'Member updated.',
            'member' => $this->formatMember($member),
        ]);
    }

    public function deactivateMember(Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeManageMembers($family);
        if ($member->family_id !== $family->id) {
            abort(404);
        }

        $primaryCount = FamilyMember::where('family_id', $family->id)->where('is_primary', true)->count();
        if ($member->is_primary && $primaryCount <= 1) {
            return response()->json([
                'message' => 'Cannot deactivate the only primary owner. Assign another owner as primary first.',
            ], 422);
        }

        $member->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Member deactivated.',
            'member' => $this->formatMember($member->fresh(['user:id,name,email', 'role:id,name'])),
        ]);
    }

    public function activateMember(Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeManageMembers($family);
        if ($member->family_id !== $family->id) {
            abort(404);
        }

        $member->update(['status' => 'active']);

        return response()->json([
            'message' => 'Member activated.',
            'member' => $this->formatMember($member->fresh(['user:id,name,email', 'role:id,name'])),
        ]);
    }

    public function destroyMember(Family $family, FamilyMember $member): JsonResponse
    {
        $this->authorizeManageMembers($family);
        if ($member->family_id !== $family->id) {
            abort(404);
        }

        if ($member->user_id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot remove yourself. Leave the family from account settings or transfer ownership first.',
            ], 422);
        }

        $primaryCount = FamilyMember::where('family_id', $family->id)->where('is_primary', true)->count();
        if ($member->is_primary && $primaryCount <= 1) {
            return response()->json([
                'message' => 'Cannot remove the only primary owner. Assign another owner as primary first.',
            ], 422);
        }

        $member->delete();

        return response()->json([
            'message' => 'Member removed.',
        ]);
    }

    private function authorizeManageMembers(Family $family): void
    {
        $membership = FamilyMember::where('family_id', $family->id)
            ->where('user_id', auth()->id())
            ->with('role')
            ->first();

        if (! $membership || ! in_array($membership->role->name ?? '', ['Owner', 'Co-Owner', 'Co-owner'], true)) {
            abort(403, 'Only the owner or co-owner can manage members.');
        }
    }

    private function formatMember(FamilyMember $m): array
    {
        return [
            'id' => $m->id,
            'user_id' => $m->user_id,
            'name' => $m->member_name ?: $m->user?->name,
            'email' => $m->user?->email,
            'role' => $m->role ? ['id' => $m->role->id, 'name' => $m->role->name] : null,
            'status' => $m->status,
            'is_primary' => (bool) $m->is_primary,
            'joined_at' => $m->joined_at?->format('Y-m-d'),
        ];
    }
}
