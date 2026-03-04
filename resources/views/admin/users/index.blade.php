@extends('layouts.metronic')

@section('title', 'Users')
@section('page_title', 'User Management')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Users</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Platform user accounts.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="kt-btn kt-btn-primary">Add user</a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-green-800 dark:text-green-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-red-800 dark:text-red-200">{{ session('error') }}</div>
    @endif

    <div class="mb-4 flex justify-end">
        <form method="get" class="flex flex-wrap items-center gap-2 justify-end w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email" class="kt-input w-full sm:w-64 min-w-0 shrink-0" />
            <select name="status" class="kt-select !w-[140px] shrink-0" style="width: 140px;">
                <option value="">All statuses</option>
                @foreach (App\Models\User::statuses() as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="kt-btn kt-btn-outline shrink-0">Filter</button>
        </form>
    </div>

    <div class="kt-card p-0">
        {{-- Desktop / tablet table --}}
        <div class="kt-scrollable-x-auto hidden md:block">
            <table class="kt-table table-auto kt-table-border">
                <thead>
                    <tr>
                        <th class="min-w-[160px]">Name</th>
                        <th class="min-w-[180px]">Email</th>
                        <th class="min-w-[100px]">Status</th>
                        <th class="min-w-[140px]">Roles</th>
                        <th class="w-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                    <tr>
                        <td class="font-medium">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="kt-badge kt-badge-sm kt-badge-outline">{{ App\Models\User::statuses()[$u->status] ?? $u->status }}</span></td>
                        <td class="text-sm">
                            @php
                                $systemNames = $u->roles->pluck('name');
                                $roleText = $systemNames->isNotEmpty() ? $systemNames->join(', ') : ($u->familyMemberships->first()?->role?->name ?? '—');
                            @endphp
                            {{ $roleText }}
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $u) }}" class="kt-btn kt-btn-ghost kt-btn-sm">View</a>
                            <a href="{{ route('admin.users.edit', $u) }}" class="kt-btn kt-btn-ghost kt-btn-sm">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden p-4 space-y-4">
            @foreach ($users as $u)
                <div class="rounded-2xl border border-border bg-background shadow-sm px-5 py-4 flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground font-medium text-sm">
                                {{ strtoupper(substr($u->name ?? '?', 0, 1)) }}
                            </span>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-medium text-mono truncate">
                                    {{ $u->name }}
                                </span>
                                <span class="text-xs text-secondary-foreground truncate">
                                    {{ $u->email }}
                                </span>
                            </div>
                        </div>
                        <span class="kt-badge kt-badge-sm kt-badge-outline shrink-0">
                            {{ App\Models\User::statuses()[$u->status] ?? $u->status }}
                        </span>
                    </div>

                    <div class="text-[11px] text-muted-foreground">
                        @php
                            $systemNames = $u->roles->pluck('name');
                            $roleText = $systemNames->isNotEmpty() ? $systemNames->join(', ') : ($u->familyMemberships->first()?->role?->name ?? '—');
                        @endphp
                        <span class="uppercase tracking-wide">Roles:</span>
                        <span class="font-medium text-foreground">{{ $roleText }}</span>
                    </div>

                    <div class="flex flex-wrap justify-end gap-2 pt-1">
                        <a href="{{ route('admin.users.show', $u) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                            View
                        </a>
                        <a href="{{ route('admin.users.edit', $u) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                            Edit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="px-4 py-3 border-t border-border">{{ $users->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
