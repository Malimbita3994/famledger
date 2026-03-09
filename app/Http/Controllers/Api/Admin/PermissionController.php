<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use AuthorizesAdmin;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $permissions = Permission::where('guard_name', config('auth.defaults.guard'))
            ->withCount(['roles', 'users'])
            ->orderBy('name')
            ->get(['id', 'name', 'display_name', 'description']);

        $items = $permissions->map(fn (Permission $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'display_name' => $p->display_name,
            'description' => $p->description,
            'roles_count' => $p->roles_count,
            'users_count' => $p->users_count,
        ]);

        return response()->json(['permissions' => $items]);
    }
}

