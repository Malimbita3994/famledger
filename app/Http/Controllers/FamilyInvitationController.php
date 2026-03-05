<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\FamilyMember;
use App\Models\FamilyRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FamilyInvitationController extends Controller
{
    protected function authorizeManageMembers(Family $family): void
    {
        $membership = \App\Models\FamilyMember::where('family_id', $family->id)
            ->where('user_id', auth()->id())
            ->with('role')
            ->first();

        if (! $membership || ! in_array($membership->role->name ?? '', ['Owner', 'Co-owner', 'Co-Owner'], true)) {
            abort(403, 'Only the family owner or co-owner can invite members.');
        }
    }

    public function index(Request $request, Family $family): View
    {
        $this->authorizeManageMembers($family);

        $family->load('invitations.role', 'invitations.inviter');

        $query = $family->invitations()->with(['role', 'inviter']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }
        $invitations = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $roles = FamilyRole::orderBy('id')->whereNotIn('name', ['Owner'])->get();

        if (! $family->invite_token) {
            $family->update(['invite_token' => Str::random(64)]);
        }
        $family->refresh();

        return view('families.invites.index', compact('family', 'invitations', 'roles'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeManageMembers($family);

        $validated = $request->validate([
            'email'   => ['required', 'email'],
            'role_id' => ['required', 'exists:family_roles,id'],
        ]);

        $role = FamilyRole::findOrFail($validated['role_id']);
        if (strtolower($role->name) === 'owner') {
            return back()->withInput()->withErrors(['role_id' => 'You cannot invite someone as Owner.'])->with('error', 'Invalid role.');
        }

        $user = User::where('email', $validated['email'])->first();
        if ($user && $family->members()->where('user_id', $user->id)->exists()) {
            return back()->withInput()->withErrors(['email' => 'This person is already a member.'])->with('error', 'Already a member.');
        }

        $existing = FamilyInvitation::where('family_id', $family->id)
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
        if ($existing) {
            return back()->withInput()->withErrors(['email' => 'An invitation is already pending for this email.'])->with('error', 'Invitation already sent.');
        }

        FamilyInvitation::create([
            'family_id'   => $family->id,
            'email'       => $validated['email'],
            'role_id'     => $validated['role_id'],
            'token'       => FamilyInvitation::generateToken(),
            'invited_by'  => auth()->id(),
            'expires_at'  => now()->addDays(7),
            'status'      => 'pending',
        ]);

        return redirect()->route('families.invites.index', $family)
            ->with('success', 'Invitation sent to ' . $validated['email']);
    }

    public function resetLink(Family $family)
    {
        $this->authorizeManageMembers($family);

        $family->update(['invite_token' => Str::random(64)]);

        return redirect()->route('families.invites.index', $family)
            ->with('success', 'Invite link has been reset. Previous link no longer works.');
    }

    public function destroy(Family $family, FamilyInvitation $invitation)
    {
        $this->authorizeManageMembers($family);

        if ($invitation->family_id !== $family->id) {
            abort(404);
        }

        $invitation->delete();

        return redirect()->route('families.invites.index', $family)
            ->with('success', 'Invitation removed.');
    }
}
