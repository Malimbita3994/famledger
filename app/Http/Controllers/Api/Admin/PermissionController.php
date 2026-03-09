<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use AuthorizesAdmin;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $permissions = Permission::where('guard_name', config('auth.defaults.guard'))
            ->withCount(['roles'])
            ->orderBy('name')
            ->get(['id', 'name', 'display_name', 'description']);

        $modelHasPermissionsTable = config('permission.table_names.model_has_permissions', 'model_has_permissions');

        $items = $permissions->map(fn (Permission $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'display_name' => $p->display_name,
            'description' => $p->description,
            'roles_count' => $p->roles_count,
            'users_count' => DB::table($modelHasPermissionsTable)->where('permission_id', $p->id)->count(),
        ]);

        return response()->json(['permissions' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $guard = config('auth.defaults.guard');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $guard,
            'display_name' => $validated['display_name'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Permission created.',
            'permission' => $this->formatPermission($permission),
        ], 201);
    }

    public function update(Request $request, Permission $permission): JsonResponse
    {
        $this->authorizeAdmin();

        $guard = config('auth.defaults.guard');
        if ($permission->guard_name !== $guard) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'display_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        if (array_key_exists('name', $validated)) {
            $permission->name = $validated['name'];
        }
        if (array_key_exists('display_name', $validated)) {
            $permission->display_name = $validated['display_name'];
        }
        if (array_key_exists('description', $validated)) {
            $permission->description = $validated['description'];
        }

        $permission->save();

        return response()->json([
            'message' => 'Permission updated.',
            'permission' => $this->formatPermission($permission),
        ]);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorizeAdmin();

        $guard = config('auth.defaults.guard');
        if ($permission->guard_name !== $guard) {
            abort(404);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted.',
        ]);
    }

    private function formatPermission(Permission $p): array
    {
        $modelHasPermissionsTable = config('permission.table_names.model_has_permissions', 'model_has_permissions');

        return [
            'id' => $p->id,
            'name' => $p->name,
            'display_name' => $p->display_name,
            'description' => $p->description,
            'roles_count' => $p->roles()->count(),
            'users_count' => DB::table($modelHasPermissionsTable)->where('permission_id', $p->id)->count(),
        ];
    }
}

