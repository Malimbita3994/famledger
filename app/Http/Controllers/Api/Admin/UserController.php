<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $families = $user->familyMemberships->map(fn ($m) => [
            'family_id' => $m->family_id,
            'family_name' => $m->family?->name,
            'role' => $m->role?->name,
            'status' => $m->status,
            'is_primary' => $m->is_primary,
        ])->values();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'roles' => $user->roles->pluck('name')->values()->all(),
            'families' => $families,
            'created_at' => $user->created_at?->toIso8601String(),
            'last_login_at' => $user->last_login_at?->toIso8601String(),
        ]);
    }
}
