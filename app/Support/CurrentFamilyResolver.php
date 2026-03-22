<?php

namespace App\Support;

use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Http\Request;

/**
 * Resolves the user's active family (session switcher, then primary membership).
 */
final class CurrentFamilyResolver
{
    public static function family(Request $request): ?Family
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        $currentFamilyId = (int) $request->session()->get('current_family_id');
        if ($currentFamilyId > 0) {
            $family = $user->families()
                ->where('families.id', $currentFamilyId)
                ->first();
            if ($family) {
                return $family;
            }
        }

        return $user->families()
            ->wherePivot('status', 'active')
            ->orderByDesc('family_user.is_primary')
            ->orderBy('family_user.created_at')
            ->first();
    }

    public static function canManageProperties(Request $request, ?Family $family): bool
    {
        if (! $family || ! $request->user()) {
            return false;
        }

        $member = FamilyMember::query()
            ->where('family_id', $family->id)
            ->where('user_id', $request->user()->id)
            ->with('role')
            ->first();

        $roleName = $member && $member->role
            ? mb_strtolower($member->role->name)
            : null;

        return in_array($roleName, ['owner', 'co-owner'], true);
    }
}
