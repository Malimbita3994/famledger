@extends('layouts.metronic')

@section('title', 'Platform roles')
@section('page_title', 'Platform roles')

@push('styles')
<style>
    /* Match FamLedger Adaptive UI (html[data-fl-accent] → --primary in famledger-accent-themes.css) */
    .admin-roles-page {
        --ar-accent: var(--primary);
        --ar-accent-2: color-mix(in oklab, var(--primary) 68%, #ffffff);
    }
    html.dark .admin-roles-page {
        --ar-accent-2: color-mix(in oklab, var(--primary) 74%, #020617);
    }
    /* Title + primary action: always place "New role" on the right (grid, not flex — avoids Tailwind/layout conflicts). */
    .admin-roles-hero-bar {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
        gap: 1rem 1.5rem;
        width: 100%;
        box-sizing: border-box;
    }
    .admin-roles-hero-actions {
        justify-self: end;
    }
    @media (max-width: 639px) {
        .admin-roles-hero-bar {
            grid-template-columns: minmax(0, 1fr);
        }
        .admin-roles-hero-actions {
            justify-self: end;
        }
    }
    .admin-roles-eyebrow {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .admin-roles-title {
        font-size: clamp(1.35rem, 2.8vw, 1.7rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: var(--ar-accent);
    }
    .admin-roles-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 1.15rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 12px;
        color: var(--primary-foreground, #fff) !important;
        background: linear-gradient(135deg, var(--ar-accent) 0%, var(--ar-accent-2) 100%);
        border: none;
        box-shadow: 0 4px 14px color-mix(in oklab, var(--primary) 38%, transparent);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .admin-roles-btn-primary:hover {
        filter: brightness(1.05);
        box-shadow: 0 6px 20px color-mix(in oklab, var(--primary) 45%, transparent);
        transform: translateY(-1px);
    }
    .admin-roles-alert {
        border-radius: 14px;
        border: 1px solid rgba(34, 197, 94, 0.35);
        background: linear-gradient(135deg, rgba(240, 253, 244, 0.95) 0%, rgba(236, 253, 245, 0.9) 100%);
        color: #166534;
    }
    .dark .admin-roles-alert {
        border-color: rgba(34, 197, 94, 0.3);
        background: rgba(22, 101, 52, 0.2);
        color: #bbf7d0;
    }
    .admin-roles-pulse-card {
        border-radius: 16px;
        border: 1px solid color-mix(in oklab, var(--primary) 22%, transparent);
        background: linear-gradient(
            180deg,
            var(--card) 0%,
            color-mix(in oklab, var(--primary) 6%, var(--card)) 100%
        );
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .admin-roles-pulse-card:hover {
        border-color: color-mix(in oklab, var(--primary) 42%, transparent);
        box-shadow: 0 12px 32px color-mix(in oklab, var(--primary) 16%, transparent);
        transform: translateY(-2px);
    }
    .dark .admin-roles-pulse-card {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.9) 0%, rgb(15 23 42 / 0.95) 100%);
        border-color: color-mix(in oklab, var(--primary) 28%, transparent);
    }
    .admin-roles-pulse-card .admin-roles-card-name {
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--ar-accent);
    }
    .admin-roles-pulse-card .admin-roles-card-icon {
        color: var(--ar-accent);
    }
    .admin-roles-add-card {
        border-radius: 16px;
        border: 2px dashed color-mix(in oklab, var(--primary) 34%, transparent);
        background: color-mix(in oklab, var(--primary) 9%, var(--background));
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .admin-roles-add-card:hover {
        border-color: color-mix(in oklab, var(--primary) 52%, transparent);
        background: color-mix(in oklab, var(--primary) 14%, var(--background));
        box-shadow: 0 8px 28px color-mix(in oklab, var(--primary) 14%, transparent);
        transform: translateY(-2px);
    }
    .dark .admin-roles-add-card {
        background: color-mix(in oklab, var(--primary) 11%, transparent);
        border-color: color-mix(in oklab, var(--primary) 38%, transparent);
    }
    .admin-roles-btn-outline-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        background: rgba(255, 255, 255, 0.95);
        color: #334155 !important;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .admin-roles-btn-outline-sm:hover {
        border-color: var(--ar-accent);
        background: color-mix(in oklab, var(--primary) 12%, transparent);
    }
    .dark .admin-roles-btn-outline-sm {
        background: rgba(30, 41, 59, 0.9);
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.35);
    }
    .admin-roles-empty-frame {
        padding: 3px;
        border-radius: 20px;
        background: linear-gradient(
            135deg,
            color-mix(in oklab, var(--primary) 38%, transparent) 0%,
            rgba(255, 255, 255, 0.96) 50%,
            color-mix(in oklab, var(--primary) 30%, transparent) 100%
        );
        max-width: 26rem;
        margin-left: auto;
        margin-right: auto;
    }
    .admin-roles-empty-inner {
        border-radius: 17px;
        background: #fff;
        padding: 2rem 1.5rem;
    }
    .dark .admin-roles-empty-inner {
        background: rgb(15 23 42 / 0.96);
    }
    .admin-roles-empty-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--ar-accent);
        letter-spacing: -0.02em;
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-roles-btn-primary:hover,
        .admin-roles-pulse-card:hover,
        .admin-roles-add-card:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-roles-page">
    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8 pb-4">
        <div class="admin-roles-hero-bar pb-6">
            <div class="min-w-0">
                <p class="admin-roles-eyebrow mb-1.5">Access control</p>
                <h1 class="admin-roles-title">Platform roles</h1>
            </div>
            <div class="admin-roles-hero-actions shrink-0">
                <a href="{{ route('admin.roles.create') }}" class="admin-roles-btn-primary">
                    <i class="ki-filled ki-plus text-base"></i>
                    New role
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="admin-roles-alert mb-6 px-4 py-3.5 flex items-start gap-3">
                <i class="ki-filled ki-check-circle text-xl shrink-0 mt-0.5 text-emerald-600 dark:text-emerald-400"></i>
                <span class="text-sm font-medium leading-relaxed">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 px-4 py-3.5 flex items-start gap-3 rounded-xl border border-destructive/35 bg-destructive/10 text-destructive">
                <i class="ki-filled ki-information text-xl shrink-0 mt-0.5"></i>
                <span class="text-sm font-medium leading-relaxed">{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-12">
        <div class="report-kpi-grid">
            @forelse ($roles as $role)
                <div class="report-kpi-card admin-roles-pulse-card flex flex-col overflow-hidden p-5">
                    <a href="{{ route('admin.roles.permissions.edit', $role) }}" class="block flex-1 min-h-0 text-start">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex flex-col min-w-0">
                                <span class="admin-roles-card-name text-base truncate" title="{{ $role->name }}">
                                    {{ $role->display_name ?: $role->name }}
                                </span>
                            </div>
                            <span class="admin-roles-card-icon text-xl shrink-0 mt-0.5">
                                <i class="ki-filled ki-setting-2"></i>
                            </span>
                        </div>

                        <p class="text-sm text-secondary-foreground mt-4 leading-relaxed tabular-nums">
                            {{ number_format($role->permissions_count) }}
                            {{ \Illuminate\Support\Str::plural('permission', $role->permissions_count) }}
                        </p>

                        @php $assignedUserCount = (int) $role->users_count; @endphp
                        <div class="flex items-center justify-between mt-4 gap-2 text-xs min-w-0">
                            <span
                                class="inline-flex items-center gap-1.5 text-foreground shrink-0 whitespace-nowrap"
                                title="Users with this platform role"
                            >
                                <i class="ki-filled ki-people text-base text-muted-foreground"></i>
                                <span class="font-semibold tabular-nums">{{ number_format($assignedUserCount) }}</span>
                                <span class="text-muted-foreground">{{ \Illuminate\Support\Str::plural('user', $assignedUserCount) }}</span>
                            </span>
                            <span class="font-semibold shrink-0" style="color: var(--ar-accent);">
                                Permissions →
                            </span>
                        </div>
                    </a>
                    <div class="mt-3 pt-3 border-t border-border flex flex-wrap items-center justify-end gap-2">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="admin-roles-btn-outline-sm">
                            <i class="ki-filled ki-pencil text-sm"></i>
                            Edit
                        </a>
                        @can('roles_delete')
                            @unless (in_array($role->name, $protectedRoleNames, true))
                                <form
                                    method="post"
                                    action="{{ route('admin.roles.destroy', $role) }}"
                                    class="inline"
                                    onsubmit="return confirm({{ \Illuminate\Support\Js::from('Delete this role? Assigned users will lose it. This cannot be undone.') }})"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline border-destructive/40 text-destructive hover:bg-destructive/10">
                                        <i class="ki-filled ki-trash text-sm"></i>
                                        Delete
                                    </button>
                                </form>
                            @endunless
                        @endcan
                    </div>
                </div>
            @empty
                <div class="report-kpi-card col-span-full flex justify-center py-8">
                    <div class="admin-roles-empty-frame w-full">
                        <div class="admin-roles-empty-inner text-center">
                            <div class="inline-flex items-center justify-center size-14 rounded-full bg-primary/10 border border-primary/20 mb-4 mx-auto">
                                <i class="ki-filled ki-shield-tick text-2xl" style="color: var(--ar-accent);"></i>
                            </div>
                            <h2 class="admin-roles-empty-title mb-2">No roles found</h2>
                            <p class="text-sm text-muted-foreground mb-6 max-w-xs mx-auto leading-relaxed">
                                Create a role, then attach permissions.
                            </p>
                            <a href="{{ route('admin.roles.create') }}" class="admin-roles-btn-primary">
                                <i class="ki-filled ki-plus text-base"></i>
                                Create your first role
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse

            <a href="{{ route('admin.roles.create') }}" class="report-kpi-card admin-roles-add-card flex flex-col justify-center p-5">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <span class="text-sm font-bold" style="color: var(--ar-accent);">
                            Add new role
                        </span>
                        <span class="text-xs font-medium text-muted-foreground">
                            Custom role for your organization
                        </span>
                    </div>
                    <span class="text-xl shrink-0" style="color: var(--ar-accent);">
                        <i class="ki-filled ki-rocket"></i>
                    </span>
                </div>
                <p class="text-sm text-secondary-foreground leading-relaxed">
                    Define permissions from the next screens.
                </p>
            </a>
        </div>
    </div>
</div>
@endsection
