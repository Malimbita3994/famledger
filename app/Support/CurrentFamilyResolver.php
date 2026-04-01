<?php

namespace App\Support;

use App\Models\Family;
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

    /**
     * Whether the user may open this family’s property workspace (assets list, add/edit asset, etc.).
     * Any active family member — aligned with {@see FamilyPolicy::view()}.
     */
    public static function canManageProperties(Request $request, ?Family $family): bool
    {
        if (! $family || ! $request->user()) {
            return false;
        }

        $user = $request->user();

        return $family->members()->where('user_id', $user->id)->exists();
    }
}
