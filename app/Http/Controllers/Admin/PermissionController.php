<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $guard = config('auth.defaults.guard');

        $roles = Role::where('guard_name', $guard)
            ->orderBy('name')
            ->get();

        $currentRole = $roles->firstWhere('id', $request->integer('role_id'))
            ?? $roles->first();

        $modules = [
            'dashboard',
            'families',
            'accounts',
            'family_projects',
            'administration',
            'reports',
            'settings',
            'account',
            'permissions',
            'roles',
            'users',
            'wallets',
            'income',
            'expenses',
            'transfers',
            'budgets',
            'savings',
            'reconciliation',
            'global',
        ];

        $search = trim((string) $request->query('search', ''));

        $permissionsQuery = Permission::where('guard_name', $guard);

        if ($search !== '') {
            $permissionsQuery->where('name', 'like', '%'.$search.'%');
        }

        $permissions = $permissionsQuery
            ->orderBy('name')
            ->get()
            ->groupBy(function ($p) use ($modules) {
                $name = $p->name ?? '';

                foreach ($modules as $module) {
                    if (\Illuminate\Support\Str::startsWith($name, $module . '_') || $name === $module) {
                        return $module;
                    }
                }

                return 'other';
            });

        // Paginate by module group to avoid very long pages
        $allGroups = $permissions->filter(function ($perms, $group) {
            return $group !== 'other';
        });

        $perPage = 6;
        $page = max(1, (int) $request->query('page', 1));
        $sliced = $allGroups->slice(($page - 1) * $perPage, $perPage);

        $permissionsPaginator = new LengthAwarePaginator(
            $sliced,
            $allGroups->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        if ($currentRole) {
            $currentRole->load('permissions');
        }

        return view('admin.permissions.index', [
            'roles' => $roles,
            'currentRole' => $currentRole,
            'permissions' => $sliced,
            'permissionsPaginator' => $permissionsPaginator,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $guard = config('auth.defaults.guard');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => $guard,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted.');
    }

    public function destroyModule(string $module)
    {
        $guard = config('auth.defaults.guard');

        Permission::where('guard_name', $guard)
            ->where(function ($query) use ($module) {
                $query->where('name', $module)
                    ->orWhere('name', 'like', $module . '_%');
            })
            ->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Module permissions deleted.');
    }
}
