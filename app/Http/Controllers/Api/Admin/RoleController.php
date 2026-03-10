<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    use AuthorizesAdmin;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        // Return all roles (regardless of guard) so that API/mobile
        // always see the same set of system roles as the web admin UI.
        $roles = Role::query()
            ->withCount(['permissions'])
            ->orderBy('name')
            ->get(['id', 'name', 'display_name', 'description', 'guard_name']);

        $modelHasRolesTable = config('permission.table_names.model_has_roles', 'model_has_roles');

        $items = $roles->map(fn (Role $r) => [
            'id' => $r->id,
            'name' => $r->name,
            'display_name' => $r->display_name,
            'description' => $r->description,
            'permissions_count' => $r->permissions_count,
            'users_count' => DB::table($modelHasRolesTable)->where('role_id', $r->id)->count(),
            'guard_name' => $r->guard_name,
        ]);

        return response()->json(['roles' => $items]);
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorizeAdmin();

        $guard = config('auth.defaults.guard');
        if ($role->guard_name !== $guard) {
            abort(404);
        }

        $role->load('permissions:id,name,display_name,description', 'users:id');

        return response()->json($this->formatRoleDetail($role));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $guard = config('auth.defaults.guard');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $guard,
            'display_name' => $validated['display_name'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['permissions'])) {
            $this->syncRolePermissionsByName($role, $validated['permissions']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role->load('permissions:id,name,display_name,description', 'users:id');

        return response()->json([
            'message' => 'Role created.',
            'role' => $this->formatRoleDetail($role),
        ], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $this->authorizeAdmin();

        $guard = config('auth.defaults.guard');
        if ($role->guard_name !== $guard) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'display_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string'],
        ]);

        if (array_key_exists('name', $validated)) {
            $role->name = $validated['name'];
        }
        if (array_key_exists('display_name', $validated)) {
            $role->display_name = $validated['display_name'];
        }
        if (array_key_exists('description', $validated)) {
            $role->description = $validated['description'];
        }

        $role->save();

        if (array_key_exists('permissions', $validated)) {
            $this->syncRolePermissionsByName($role, $validated['permissions'] ?? []);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $role->load('permissions:id,name,display_name,description', 'users:id');

        return response()->json([
            'message' => 'Role updated.',
            'role' => $this->formatRoleDetail($role),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorizeAdmin();

        $guard = config('auth.defaults.guard');
        if ($role->guard_name !== $guard) {
            abort(404);
        }

        if (in_array($role->name, ['Super Admin', 'Admin'], true)) {
            return response()->json([
                'message' => 'This core system role cannot be deleted.',
            ], 422);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'message' => 'Role deleted.',
        ]);
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $this->authorizeAdmin();

        $guard = config('auth.defaults.guard');
        if ($role->guard_name !== $guard) {
            abort(404);
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $this->syncRolePermissionsByName($role, $validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role->load('permissions:id,name,display_name,description', 'users:id');

        return response()->json([
            'message' => 'Role permissions updated.',
            'role' => $this->formatRoleDetail($role),
        ]);
    }

    private function syncRolePermissionsByName(Role $role, array $names): void
    {
        $guard = config('auth.defaults.guard');
        $permissions = Permission::where('guard_name', $guard)
            ->whereIn('name', $names)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissions);
    }

    private function formatRoleDetail(Role $role): array
    {
        $modelHasRolesTable = config('permission.table_names.model_has_roles', 'model_has_roles');

        return [
            'id' => $role->id,
            'name' => $role->name,
            'display_name' => $role->display_name,
            'description' => $role->description,
            'permissions' => $role->permissions->map(function (Permission $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'display_name' => $p->display_name,
                    'description' => $p->description,
                ];
            })->values(),
            'users_count' => DB::table($modelHasRolesTable)->where('role_id', $role->id)->count(),
        ];
    }
}
