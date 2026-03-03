@extends('layouts.metronic')

@section('title', 'Roles')
@section('page_title', 'Roles')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8 pb-6">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-5 lg:pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-xl font-medium leading-none text-mono">Roles</h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Overview of all system roles and their permissions.
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.roles.create') }}" class="kt-btn kt-btn-outline">
                New Role
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif
</div>

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-10">
    <div class="report-kpi-grid">
        @forelse ($roles as $role)
            <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card hover:border-primary/60 transition-colors flex flex-col" style="padding: 1.25rem 1.5rem;">
                <a href="{{ route('admin.roles.permissions.edit', $role) }}" class="block flex-1 min-h-0">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-sm font-semibold text-foreground">
                                {{ $role->name }}
                            </span>
                            <span class="text-xs font-medium text-muted-foreground">
                                System role
                            </span>
                        </div>
                        <span class="text-primary text-lg shrink-0">
                            <i class="ki-filled ki-setting-2"></i>
                        </span>
                    </div>

                    <p class="text-sm text-secondary-foreground mt-3">
                        Manages access via {{ $role->permissions_count }}
                        {{ \Illuminate\Support\Str::plural('permission', $role->permissions_count) }}.
                    </p>

                    <div class="flex items-center justify-between mt-4 text-xs text-muted-foreground">
                        <span>
                            {{ $role->users_count }} {{ \Illuminate\Support\Str::plural('user', $role->users_count) }}
                        </span>
                        <span class="kt-link kt-link-sm font-medium">
                            View details
                        </span>
                    </div>
                </a>
                <div class="mt-3 pt-3 border-t border-border flex justify-end">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                        <i class="ki-filled ki-pencil text-sm"></i>
                        Edit
                    </a>
                </div>
            </div>
        @empty
            <div class="report-kpi-card kt-card rounded-xl border border-border shadow-sm overflow-hidden bg-card col-span-full" style="padding: 1.25rem 1.5rem;">
                <div class="flex flex-col items-center text-center gap-3">
                    <h2 class="text-base font-medium text-mono">
                        No roles found
                    </h2>
                    <p class="text-sm text-secondary-foreground">
                        Start by creating a role and assigning permissions to it.
                    </p>
                    <a href="{{ route('admin.roles.create') }}" class="kt-btn kt-btn-primary">
                        Create your first role
                    </a>
                </div>
            </div>
        @endforelse

        <a href="{{ route('admin.roles.create') }}" class="report-kpi-card kt-card rounded-xl border border-dashed border-primary/30 shadow-sm overflow-hidden bg-card hover:border-primary/60 transition-colors" style="padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between gap-3 mb-2">
                <div class="flex flex-col gap-0.5">
                    <span class="text-sm font-semibold text-foreground">
                        Add New Role
                    </span>
                    <span class="text-xs font-medium text-muted-foreground">
                        Create a custom system role.
                    </span>
                </div>
                <span class="text-primary text-lg shrink-0">
                    <i class="ki-filled ki-rocket"></i>
                </span>
            </div>
            <p class="text-sm text-secondary-foreground mt-1">
                Define permissions and assign them to users.
            </p>
        </a>
    </div>
</div>
@endsection
