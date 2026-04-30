<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /** @var list<string> */
    private const PROTECTED_ROLE_NAMES = ['Super Admin', 'Admin'];

    public function index()
    {
        $guard = config('auth.defaults.guard');

        $roles = Role::query()
            ->where('guard_name', $guard)
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        $pivot = config('permission.table_names.model_has_roles', 'model_has_roles');
        $morphType = (new User)->getMorphClass();

        $counts = DB::table($pivot)
            ->where('model_type', $morphType)
            ->whereIn('role_id', $roles->pluck('id'))
            ->selectRaw('role_id, COUNT(*) as assigned_users')
            ->groupBy('role_id')
            ->pluck('assigned_users', 'role_id');

        foreach ($roles as $role) {
            $role->setAttribute('users_count', (int) ($counts[$role->id] ?? 0));
        }

        return view('admin.roles.index', [
            'roles' => $roles,
            'protectedRoleNames' => self::PROTECTED_ROLE_NAMES,
        ]);
    }

    public function create()
    {
        // Role creation now only captures the basic role metadata (name).
        // Permissions are managed separately on the role permissions page.
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => config('auth.defaults.guard'),
            'display_name' => $validated['display_name'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // After creating a role, take the user directly to the permissions UI
        // so they can manage module permissions there.
        return redirect()
            ->route('admin.roles.permissions.edit', $role)
            ->with('success', 'Role created. You can now configure its permissions.');
    }

    public function edit(Role $role)
    {
        // Edit page only handles role name, display_name, description.
        // Permissions are managed on the role permissions page.
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $guard = config('auth.defaults.guard');
        if ($role->guard_name !== $guard) {
            abort(404);
        }

        if ($this->isProtectedSystemRole($role)) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'This core system role cannot be deleted.');
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted.');
    }

    private function isProtectedSystemRole(Role $role): bool
    {
        return in_array($role->name, self::PROTECTED_ROLE_NAMES, true);
    }

    public function editPermissions(Role $role)
    {
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
            'audit_trail',
            'contact_messages',
            'liabilities',
            'invitations',
            'access',
            'manage',
            'view',
        ];

        $permissions = Permission::where('guard_name', $role->guard_name)
            ->orderBy('name')
            ->get()
            ->groupBy(function ($p) use ($modules) {
                $name = $p->name ?? '';

                foreach ($modules as $module) {
                    if (Str::startsWith($name, $module.'_') || $name === $module) {
                        return $module;
                    }
                }

                return 'other';
            });

        $role->load('permissions');

        return view('admin.roles.permissions-toggle', compact('role', 'permissions'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $permissionNames = $validated['permissions'] ?? [];

        $permissions = Permission::where('guard_name', $role->guard_name)
            ->whereIn('name', $permissionNames)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.permissions.edit', $role)
            ->with('success', 'Role permissions updated.');
    }
}
