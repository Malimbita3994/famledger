<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('guard_name', config('auth.defaults.guard'))
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
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
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('roles')->ignore($role->id)],
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
                    if (\Illuminate\Support\Str::startsWith($name, $module . '_') || $name === $module) {
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

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.permissions.edit', $role)
            ->with('success', 'Role permissions updated.');
    }
}
