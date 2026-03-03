<?php

namespace App\Http\Controllers;

use App\Mail\MemberCredentialsMail;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FamilyMemberController extends Controller
{
    /**
     * Only Owner or Co-Owner can manage members (add, edit role, remove).
     * The owner has the role to create other family members; co-owner shares this permission.
     */
    protected function authorizeManageMembers(Family $family): void
    {
        $membership = FamilyMember::where('family_id', $family->id)
            ->where('user_id', auth()->id())
            ->with('role')
            ->first();

        if (! $membership || ! in_array($membership->role->name ?? '', ['Owner', 'Co-Owner'], true)) {
            abort(403, 'Only the owner or co-owner can manage members.');
        }
    }

    /**
     * Show form to add a member (by email).
     */
    public function create(Family $family)
    {
        $this->authorizeManageMembers($family);

        $family->load('familyMembers.user');
        $roles = FamilyRole::orderBy('id')->where('name', '!=', 'Child')->get();

        return view('families.members.create', compact('family', 'roles'));
    }

    /**
     * Add a member to the family. If the email has no account, create one and send credentials by email.
     */
    public function store(Request $request, Family $family)
    {
        $this->authorizeManageMembers($family);

        $validated = $request->validate([
            'email'       => ['required', 'email'],
            'member_name' => ['nullable', 'string', 'max:255'],
            'sex'         => ['nullable', Rule::in(['male', 'female'])],
            'member_type' => ['nullable', Rule::in(['adult', 'child'])],
            'role_id'     => ['required', 'exists:family_roles,id'],
        ], [
            'email.required' => 'Please enter an email address.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            $plainPassword = Str::password(12);
            $user = User::create([
                'name'     => $validated['member_name'] ?? Str::before($validated['email'], '@'),
                'email'    => $validated['email'],
                'password' => Hash::make($plainPassword),
            ]);

            Mail::to($user->email)->send(new MemberCredentialsMail(
                family: $family,
                email: $user->email,
                plainPassword: $plainPassword,
                memberName: $validated['member_name'] ?? ''
            ));
        } else {
            if ($family->members()->where('user_id', $user->id)->exists()) {
                return back()
                    ->withInput()
                    ->withErrors(['email' => 'This user is already a member of this family.'])
                    ->with('error', 'This user is already a member of this family.');
            }
        }

        $role = FamilyRole::findOrFail($validated['role_id']);
        $isPrimary = false;
        if ($role->name === 'Owner') {
            $existingPrimary = FamilyMember::where('family_id', $family->id)->where('is_primary', true)->first();
            $isPrimary = ! $existingPrimary;
        }

        FamilyMember::create([
            'family_id'   => $family->id,
            'user_id'    => $user->id,
            'role_id'    => $validated['role_id'],
            'member_name'=> $validated['member_name'] ?? null,
            'sex'        => $validated['sex'] ?? null,
            'member_type'=> $validated['member_type'] ?? null,
            'is_primary' => $isPrimary,
            'status'     => 'active',
            'joined_at'  => now(),
        ]);

        $message = $user->wasRecentlyCreated
            ? 'Member added. They will receive an email with login credentials.'
            : 'Member added successfully.';

        return redirect()
            ->route('families.show', $family)
            ->with('success', $message);
    }

    /**
     * Show form to edit a member's role.
     */
    public function edit(Family $family, int $member)
    {
        $this->authorizeManageMembers($family);

        $familyMember = FamilyMember::where('family_id', $family->id)
            ->with(['user', 'role'])
            ->findOrFail($member);

        $roles = FamilyRole::orderBy('id')->where('name', '!=', 'Child')->get();

        return view('families.members.edit', compact('family', 'familyMember', 'roles'));
    }

    /**
     * Update a member's role and primary flag.
     */
    public function update(Request $request, Family $family, int $member)
    {
        $this->authorizeManageMembers($family);

        $familyMember = FamilyMember::where('family_id', $family->id)->findOrFail($member);

        $validated = $request->validate([
            'member_name' => ['nullable', 'string', 'max:255'],
            'sex'         => ['nullable', Rule::in(['male', 'female'])],
            'member_type' => ['nullable', Rule::in(['adult', 'child'])],
            'role_id'     => ['required', 'exists:family_roles,id'],
            'is_primary'  => ['nullable', 'boolean'],
        ]);

        $newRole = FamilyRole::findOrFail($validated['role_id']);
        $isPrimary = (bool) ($validated['is_primary'] ?? false);

        if ($isPrimary && ! $familyMember->is_primary) {
            FamilyMember::where('family_id', $family->id)->update(['is_primary' => false]);
        }

        $familyMember->update([
            'member_name' => $validated['member_name'] ?? null,
            'sex'         => $validated['sex'] ?? null,
            'member_type' => $validated['member_type'] ?? null,
            'role_id'     => $validated['role_id'],
            'is_primary'  => $newRole->name === 'Owner' ? $isPrimary : false,
        ]);

        return redirect()
            ->route('families.show', $family)
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Deactivate a member (keep history but mark as inactive).
     */
    public function deactivate(Family $family, int $member)
    {
        $this->authorizeManageMembers($family);

        $familyMember = FamilyMember::where('family_id', $family->id)->findOrFail($member);

        // Do not allow deactivating the only primary owner.
        $primaryCount = FamilyMember::where('family_id', $family->id)->where('is_primary', true)->count();
        if ($familyMember->is_primary && $primaryCount <= 1) {
            return redirect()
                ->route('families.show', $family)
                ->with('error', 'Cannot deactivate the only primary owner. Assign another owner as primary first.');
        }

        $familyMember->update(['status' => 'inactive']);

        return redirect()
            ->route('families.show', $family)
            ->with('success', 'Member deactivated.');
    }

    /**
     * Reactivate a previously deactivated member.
     */
    public function activate(Family $family, int $member)
    {
        $this->authorizeManageMembers($family);

        $familyMember = FamilyMember::where('family_id', $family->id)->findOrFail($member);
        $familyMember->update(['status' => 'active']);

        return redirect()
            ->route('families.show', $family)
            ->with('success', 'Member activated.');
    }

    /**
     * Transfer primary ownership to another member.
     */
    public function transferOwnership(Family $family, int $member)
    {
        $this->authorizeManageMembers($family);

        $target = FamilyMember::where('family_id', $family->id)
            ->with('role')
            ->findOrFail($member);

        $ownerRole = FamilyRole::where('name', 'Owner')->firstOrFail();

        // Make target the primary owner (there can be only one).
        FamilyMember::where('family_id', $family->id)->update(['is_primary' => false]);

        $target->update([
            'role_id'    => $ownerRole->id,
            'is_primary' => true,
            'status'     => 'active',
        ]);

        return redirect()
            ->route('families.show', $family)
            ->with('success', 'Family ownership has been transferred.');
    }

    /**
     * Remove a member from the family.
     */
    public function destroy(Family $family, int $member)
    {
        $this->authorizeManageMembers($family);

        $familyMember = FamilyMember::where('family_id', $family->id)->findOrFail($member);

        if ($familyMember->user_id === auth()->id()) {
            return redirect()
                ->route('families.show', $family)
                ->with('error', 'You cannot remove yourself. Leave the family from account settings or transfer ownership first.');
        }

        $primaryCount = FamilyMember::where('family_id', $family->id)->where('is_primary', true)->count();
        if ($familyMember->is_primary && $primaryCount <= 1) {
            return redirect()
                ->route('families.show', $family)
                ->with('error', 'Cannot remove the only primary owner. Assign another owner as primary first.');
        }

        $familyMember->delete();

        return redirect()
            ->route('families.show', $family)
            ->with('success', 'Member removed.');
    }
}
