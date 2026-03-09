<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user = $request->user();
        $user->tokens()->where('name', 'mobile')->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => $this->userResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => $this->userResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userResource($request->user())]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Profile updated.',
            'user' => $this->userResource($user),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('The current password is incorrect.')],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return response()->json([
            'message' => 'Password updated.',
        ]);
    }

    private function userResource(User $user): array
    {
        $user->load('families:id,name,currency_code,status');
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('access_admin_panel'),
            'families' => $user->families->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'currency_code' => $f->currency_code,
                'status' => $f->status,
            ]),
        ];
    }
}
