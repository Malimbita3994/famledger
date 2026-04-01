<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserSettingsController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $user->fill($validated);
        if (array_key_exists('email', $validated) && $user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return response()->json([
            'message' => 'Profile updated.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->values(),
                'is_admin' => $user->hasRole('Super Admin') || $user->hasRole('Admin'),
            ],
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        return response()->json([
            'message' => 'Password updated.',
            'must_change_password' => false,
        ]);
    }
}
