<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AuthorizesAdmin;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $roles = Role::where('guard_name', config('auth.defaults.guard'))
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get(['id', 'name', 'display_name', 'description']);

        $items = $roles->map(fn (Role $r) => [
            'id' => $r->id,
            'name' => $r->name,
            'display_name' => $r->display_name,
            'description' => $r->description,
            'permissions_count' => $r->permissions_count,
            'users_count' => $r->users_count,
        ]);

        return response()->json(['roles' => $items]);
    }
}
