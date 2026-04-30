<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('roles', 'familyMemberships.role', 'familyMemberships.family:id,name', 'creator:id,name');
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $users = $query->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', config('auth.defaults.guard'))->orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(array_keys(User::statuses()))],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'created_by' => auth()->id(),
        ]);

        if (! empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.show', $user)->with('success', 'User created.');
    }

    public function show(User $user)
    {
        // Include both system roles and family roles for display
        $user->load('roles', 'familyMemberships.role', 'creator:id,name');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::where('guard_name', config('auth.defaults.guard'))->orderBy('name')->get();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(array_keys(User::statuses()))],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ];

        // Only allow Super Admins to reset another user's password
        if (auth()->user()?->hasRole('Super Admin')) {
            $rules['password'] = ['nullable', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (isset($validated['password']) && $validated['password']) {
            $user->forceFill([
                'password' => Hash::make($validated['password']),
            ])->save();
        }

        if (array_key_exists('roles', $validated)) {
            $user->syncRoles($validated['roles'] ?? []);
        }

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()?->hasRole('Super Admin'), 403);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', __('You cannot delete your own account.'));
        }

        if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            return redirect()->route('admin.users.index')->with('error', __('Cannot delete the only Super Admin account.'));
        }

        DB::transaction(function () use ($user) {
            $actorId = auth()->id();
            $familyIds = $user->familyMemberships()
                ->pluck('family_id')
                ->merge(Family::where('created_by', $user->id)->pluck('id'))
                ->unique()
                ->filter()
                ->values();
            $deletedName = $user->name;
            $deletedEmail = $user->email;
            $deletedUserId = $user->id;

            Family::where('created_by', $user->id)->update(['created_by' => $actorId]);
            FamilyInvitation::where('invited_by', $user->id)->update(['invited_by' => $actorId]);

            $user->tokens()->delete();
            $user->syncRoles([]);

            $user->delete();

            foreach ($familyIds as $familyId) {
                AuditLogger::application(
                    AuditLog::ACTION_DELETED,
                    __('User :name (:email) was permanently removed from the platform.', [
                        'name' => $deletedName,
                        'email' => $deletedEmail,
                    ]),
                    [
                        'context' => 'admin_user_delete',
                        'deleted_user_id' => $deletedUserId,
                    ],
                    (int) $familyId
                );
            }
        });

        return redirect()->route('admin.users.index')->with('success', __('User deleted permanently.'));
    }
}
