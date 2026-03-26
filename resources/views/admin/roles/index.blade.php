@extends('layouts.metronic')

@section('title', __('Roles'))
@section('page_title', __('Roles'))

@push('styles')
<style>
    .admin-roles-page {
        --ar-accent: #009ef7;
        --ar-accent-2: #0ea5e9;
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
    .admin-roles-sub {
        font-size: 0.875rem;
        line-height: 1.5;
        color: #64748b;
        margin-top: 0.35rem;
    }
    .dark .admin-roles-sub {
        color: #94a3b8;
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
        color: #fff !important;
        background: linear-gradient(135deg, var(--ar-accent) 0%, var(--ar-accent-2) 100%);
        border: none;
        box-shadow: 0 4px 14px rgba(0, 158, 247, 0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .admin-roles-btn-primary:hover {
        filter: brightness(1.05);
        box-shadow: 0 6px 20px rgba(0, 158, 247, 0.42);
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
        border: 1px solid rgba(14, 165, 233, 0.18);
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .admin-roles-pulse-card:hover {
        border-color: rgba(0, 158, 247, 0.35);
        box-shadow: 0 12px 32px rgba(0, 158, 247, 0.12);
        transform: translateY(-2px);
    }
    .dark .admin-roles-pulse-card {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.9) 0%, rgb(15 23 42 / 0.95) 100%);
        border-color: rgba(14, 165, 233, 0.22);
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
        border: 2px dashed rgba(0, 158, 247, 0.28);
        background: rgba(240, 249, 255, 0.35);
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .admin-roles-add-card:hover {
        border-color: rgba(0, 158, 247, 0.55);
        background: rgba(224, 242, 254, 0.5);
        box-shadow: 0 8px 28px rgba(0, 158, 247, 0.1);
        transform: translateY(-2px);
    }
    .dark .admin-roles-add-card {
        background: rgba(14, 165, 233, 0.08);
        border-color: rgba(14, 165, 233, 0.35);
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
        background: rgba(0, 158, 247, 0.08);
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
            rgba(0, 158, 247, 0.35) 0%,
            rgba(255, 255, 255, 0.96) 50%,
            rgba(14, 165, 233, 0.28) 100%
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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between pb-6">
            <div>
                <p class="admin-roles-eyebrow mb-1.5">{{ __('Access control') }}</p>
                <h1 class="admin-roles-title">{{ __('Roles') }}</h1>
                <p class="admin-roles-sub">{{ __('Overview of all system roles, permissions, and assignments.') }}</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('admin.roles.create') }}" class="admin-roles-btn-primary">
                    <i class="ki-filled ki-plus text-base"></i>
                    {{ __('New role') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="admin-roles-alert mb-6 px-4 py-3.5 flex items-start gap-3">
                <i class="ki-filled ki-check-circle text-xl shrink-0 mt-0.5 text-emerald-600 dark:text-emerald-400"></i>
                <span class="text-sm font-medium leading-relaxed">{{ session('success') }}</span>
            </div>
        @endif
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-12">
        <div class="report-kpi-grid">
            @forelse ($roles as $role)
                <div class="report-kpi-card admin-roles-pulse-card flex flex-col overflow-hidden" style="padding: 1.25rem 1.5rem;">
                    <a href="{{ route('admin.roles.permissions.edit', $role) }}" class="block flex-1 min-h-0 text-start">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex flex-col gap-0.5 min-w-0">
                                <span class="admin-roles-card-name text-sm truncate">
                                    {{ $role->name }}
                                </span>
                                <span class="text-xs font-medium text-muted-foreground">
                                    {{ __('System role') }}
                                </span>
                            </div>
                            <span class="admin-roles-card-icon text-xl shrink-0">
                                <i class="ki-filled ki-setting-2"></i>
                            </span>
                        </div>

                        <p class="text-sm text-secondary-foreground mt-3 leading-relaxed">
                            {{ __('Manages access via') }} {{ $role->permissions_count }}
                            {{ \Illuminate\Support\Str::plural(__('permission'), $role->permissions_count) }}.
                        </p>

                        <div class="flex items-center justify-between mt-4 text-xs text-muted-foreground">
                            <span>
                                {{ $role->users_count }} {{ \Illuminate\Support\Str::plural(__('user'), $role->users_count) }}
                            </span>
                            <span class="font-semibold" style="color: var(--ar-accent);">
                                {{ __('Permissions') }} →
                            </span>
                        </div>
                    </a>
                    <div class="mt-3 pt-3 border-t border-sky-100/80 dark:border-slate-600/50 flex justify-end">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="admin-roles-btn-outline-sm">
                            <i class="ki-filled ki-pencil text-sm"></i>
                            {{ __('Edit') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="report-kpi-card col-span-full flex justify-center py-8">
                    <div class="admin-roles-empty-frame w-full">
                        <div class="admin-roles-empty-inner text-center">
                            <div class="inline-flex items-center justify-center size-14 rounded-full bg-sky-50 dark:bg-sky-950/40 border border-sky-100 dark:border-sky-800/50 mb-4 mx-auto">
                                <i class="ki-filled ki-shield-tick text-2xl" style="color: var(--ar-accent);"></i>
                            </div>
                            <h2 class="admin-roles-empty-title mb-2">{{ __('No roles found') }}</h2>
                            <p class="text-sm text-muted-foreground mb-6 max-w-xs mx-auto leading-relaxed">
                                {{ __('Create a role, then attach permissions and assign users.') }}
                            </p>
                            <a href="{{ route('admin.roles.create') }}" class="admin-roles-btn-primary">
                                <i class="ki-filled ki-plus text-base"></i>
                                {{ __('Create your first role') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse

            <a href="{{ route('admin.roles.create') }}" class="report-kpi-card admin-roles-add-card flex flex-col justify-center" style="padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <span class="text-sm font-bold" style="color: var(--ar-accent);">
                            {{ __('Add new role') }}
                        </span>
                        <span class="text-xs font-medium text-muted-foreground">
                            {{ __('Custom role for your organization') }}
                        </span>
                    </div>
                    <span class="text-xl shrink-0" style="color: var(--ar-accent);">
                        <i class="ki-filled ki-rocket"></i>
                    </span>
                </div>
                <p class="text-sm text-secondary-foreground leading-relaxed">
                    {{ __('Define permissions and map them to users from the next screens.') }}
                </p>
            </a>
        </div>
    </div>
</div>
@endsection
