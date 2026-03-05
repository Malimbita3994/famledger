<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\FamilyMember;
use App\Models\FamilyRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InviteJoinController extends Controller
{
    public function show(Request $request): View|array
    {
        $token = $request->query('token');
        if (! $token) {
            abort(404, 'Invalid or missing invite link.');
        }

        if (! Auth::check()) {
            session()->put('url.intended', $request->fullUrl());
        }

        $invitation = FamilyInvitation::where('token', $token)->with(['family', 'role'])->first();
        if ($invitation) {
            if (! $invitation->isValid()) {
                return view('invite.expired', ['invitation' => $invitation]);
            }

            return view('invite.join', [
                'type'        => 'email',
                'family'      => $invitation->family,
                'invitation'  => $invitation,
                'token'       => $token,
            ]);
        }

        $family = Family::where('invite_token', $token)->first();
        if ($family) {
            return view('invite.join', [
                'type'   => 'link',
                'family' => $family,
                'token'  => $token,
            ]);
        }

        abort(404, 'Invalid or expired invite link.');
    }

    public function accept(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);
        $token = $validated['token'];

        if (! Auth::check()) {
            session()->put('url.intended', route('invite.join', ['token' => $token]));

            return redirect()->route('login')
                ->with('error', 'Please sign in to accept the invitation.');
        }

        $user = Auth::user();

        $invitation = FamilyInvitation::where('token', $token)->with(['family', 'role'])->first();
        if ($invitation) {
            if (! $invitation->isValid()) {
                return redirect()->route('landing')->with('error', 'This invitation has expired or was cancelled.');
            }
            if (strtolower($user->email) !== strtolower($invitation->email)) {
                return redirect()->route('invite.join', ['token' => $token])
                    ->with('error', 'This invitation was sent to ' . $invitation->email . '. Please sign in with that account.');
            }
            if ($invitation->family->members()->where('user_id', $user->id)->exists()) {
                $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);
                return redirect()->route('families.show', $invitation->family)
                    ->with('success', 'You are already a member of this family.');
            }

            $role = $invitation->role;
            $isPrimary = false;
            FamilyMember::create([
                'family_id'   => $invitation->family_id,
                'user_id'     => $user->id,
                'role_id'     => $role->id,
                'member_name' => $user->name,
                'is_primary'  => $isPrimary,
                'status'      => 'active',
                'joined_at'   => now(),
            ]);
            $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);

            return redirect()->route('families.show', $invitation->family)
                ->with('success', 'You have joined ' . $invitation->family->name . ' as ' . $role->name . '.');
        }

        $family = Family::where('invite_token', $token)->first();
        if ($family) {
            if ($family->members()->where('user_id', $user->id)->exists()) {
                return redirect()->route('families.show', $family)
                    ->with('success', 'You are already a member of this family.');
            }

            $memberRole = FamilyRole::where('name', 'Member')->first()
                ?? FamilyRole::whereNotIn('name', ['Owner', 'Child'])->orderBy('id')->first();
            if (! $memberRole) {
                return redirect()->route('landing')->with('error', 'Unable to assign role. Please contact support.');
            }

            FamilyMember::create([
                'family_id'   => $family->id,
                'user_id'     => $user->id,
                'role_id'     => $memberRole->id,
                'member_name' => $user->name,
                'is_primary'  => false,
                'status'      => 'active',
                'joined_at'   => now(),
            ]);

            return redirect()->route('families.show', $family)
                ->with('success', 'You have joined ' . $family->name . '.');
        }

        return redirect()->route('landing')->with('error', 'Invalid or expired invite link.');
    }
}
