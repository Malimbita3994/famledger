<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use AuthorizesAdmin;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $query = User::query()->with('roles:id,name')->select(['id', 'name', 'email', 'status', 'created_at']);
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $perPage = min((int) $request->get('per_page', 20), 50);
        $users = $query->orderBy('name')->paginate($perPage);

        $items = $users->getCollection()->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'status' => $u->status,
            'roles' => $u->roles->pluck('name')->values()->all(),
            'created_at' => $u->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'users' => $items,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorizeAdmin();

        $user->load('roles:id,name', 'familyMemberships.role:id,name', 'familyMemberships.family:id,name');
        return response()->json($this->formatUserDetail($user));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(array_keys(User::statuses()))],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'] ?? User::STATUS_ACTIVE,
            'password' => $validated['password'],
            'created_by' => auth()->id(),
        ]);

        if (! empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        $user->load('roles:id,name');

        return response()->json([
            'message' => 'User created.',
            'user' => $this->formatUserSummary($user),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'status' => ['sometimes', Rule::in(array_keys(User::statuses()))],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string'],
        ]);

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }
        if (array_key_exists('status', $validated)) {
            $user->status = $validated['status'];
        }
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        if (array_key_exists('roles', $validated)) {
            $user->syncRoles($validated['roles'] ?? []);
        }

        $user->load('roles:id,name', 'familyMemberships.role:id,name', 'familyMemberships.family:id,name');

        return response()->json($this->formatUserDetail($user));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorizeAdmin();

        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        // Mirror web admin behaviour: "deactivate" rather than hard delete.
        $user->status = User::STATUS_SUSPENDED;
        $user->save();

        return response()->json([
            'message' => 'User deactivated.',
        ]);
    }

    private function formatUserSummary(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'roles' => $user->roles->pluck('name')->values()->all(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    private function formatUserDetail(User $user): array
    {
        $families = $user->familyMemberships->map(function ($m) {
            return [
                'family_id' => $m->family_id,
                'family_name' => $m->family?->name,
                'role' => $m->role?->name,
                'status' => $m->status,
                'is_primary' => $m->is_primary,
            ];
        })->values();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'roles' => $user->roles->pluck('name')->values()->all(),
            'families' => $families,
            'created_at' => $user->created_at?->toIso8601String(),
            'last_login_at' => $user->last_login_at?->toIso8601String(),
        ];
    }
}
