<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AuthorizesAdmin;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $roles = Role::where('guard_name', config('auth.defaults.guard'))
            ->withCount(['permissions'])
            ->orderBy('name')
            ->get(['id', 'name', 'display_name', 'description']);

        $modelHasRolesTable = config('permission.table_names.model_has_roles', 'model_has_roles');

        $items = $roles->map(fn (Role $r) => [
            'id' => $r->id,
            'name' => $r->name,
            'display_name' => $r->display_name,
            'description' => $r->description,
            'permissions_count' => $r->permissions_count,
            'users_count' => DB::table($modelHasRolesTable)->where('role_id', $r->id)->count(),
        ]);

        return response()->json(['roles' => $items]);
    }
}
