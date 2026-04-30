@extends('layouts.metronic')

@section('title', 'User List')
@section('page_title', 'User List')

@section('content')
@push('styles')
<style>
    .admin-users-hero {
        border: 1px solid rgba(14, 165, 233, 0.2);
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(0, 158, 247, 0.04) 55%, rgba(255, 255, 255, 0.9) 100%);
        border-radius: 1rem;
        box-shadow: 0 8px 26px rgba(15, 23, 42, 0.06);
    }
    .admin-users-filters-row {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.15rem;
        margin-left: auto;
    }
    .admin-users-filters-row .kt-input,
    .admin-users-filters-row .kt-select,
    .admin-users-filters-row .fin-pulse-btn-outline {
        flex: 0 0 auto;
    }
    .admin-users-hero-left {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }
    /* Pulse title is tuned for regular pages; admin header needs smaller scale to avoid wrap. */
    .admin-users-hero-title {
        font-size: 1.25rem !important;
        line-height: 1.2 !important;
        letter-spacing: -0.02em !important;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .admin-users-filters-card {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        overflow: hidden;
    }
    .admin-users-filters-row .kt-input {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 1px solid rgba(148, 163, 184, 0.45) !important;
        border-radius: 12px !important;
        padding: 0.65rem 1rem !important;
        min-height: 2.875rem;
    }
    .admin-users-filters-row .kt-select {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 1px solid rgba(148, 163, 184, 0.45) !important;
        border-radius: 12px !important;
        padding: 0.65rem 0.95rem !important;
        min-height: 2.875rem;
        color: #0f172a;
    }
    .admin-users-filters-row .kt-input:focus,
    .admin-users-filters-row .kt-select:focus {
        outline: none;
        border-color: rgba(0, 158, 247, 0.85) !important;
        box-shadow: 0 0 0 3px rgba(0, 158, 247, 0.22) !important;
        background: #fff !important;
    }
    .dark .admin-users-filters-row .kt-input,
    .dark .admin-users-filters-row .kt-select {
        background: rgba(30, 41, 59, 0.9) !important;
        border-color: rgba(148, 163, 184, 0.35) !important;
        color: #e2e8f0;
    }
    .dark .admin-users-filters-row .kt-input:focus,
    .dark .admin-users-filters-row .kt-select:focus {
        border-color: rgba(56, 189, 248, 0.7) !important;
        box-shadow: 0 0 0 3px rgba(0, 158, 247, 0.22) !important;
        background: rgba(30, 41, 59, 1) !important;
    }
    /* Use Metronic's expected containers for padding (.kt-card-content / .kt-card-footer).
       Tailwind p-*/px-*/py-* utilities are not present in this Metronic build, so without these wrappers
       content appears "stuck" to the card borders. */
    .admin-users-hero-content {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 1rem;
        width: 100% !important;
        min-width: 0;
    }
    .admin-users-filters-content {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: 100% !important;
        min-width: 0;
    }
    .admin-users-table-content {
        overflow: hidden;
    }
    .admin-users-pagination {
        border-top: 1px solid var(--border);
    }
    .admin-users-hero-left {
        flex: 0 1 auto;
        min-width: 0;
    }
</style>
@endpush
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="kt-card fin-pulse-kt-card famledger-page-toolbar overflow-hidden">
        <div class="kt-card-content" style="display:flex; flex-direction:row; flex-wrap:nowrap; align-items:center; justify-content:space-between; gap:1rem; width:100%; min-width:0;">
            <div class="admin-users-hero-left flex-1 min-w-0">
                <p class="fin-pulse-eyebrow mb-1">Admin Console</p>
                <h1 class="fin-pulse-title admin-users-hero-title truncate">User List</h1>
                <p class="text-sm text-muted-foreground mt-0.5">Platform user accounts.</p>
            </div>
            <div style="flex:0 0 auto; min-width:0;">
                <x-famledger.pulse-button variant="primary" :href="route('admin.users.create')">Add user</x-famledger.pulse-button>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-green-800 dark:text-green-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-red-800 dark:text-red-200">{{ session('error') }}</div>
    @endif

    <div class="mb-4 mt-4 w-full">
        <div class="kt-card fin-pulse-kt-card admin-users-filters-card overflow-hidden">
            <div class="kt-card-content admin-users-filters-content">
                <form method="get" class="admin-users-filters-row">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email" class="kt-input w-[250px] min-w-0" />
                    <select name="status" class="kt-select !w-[140px] shrink-0" style="width: 140px;">
                    <option value="">All statuses</option>
                    @foreach (App\Models\User::statuses() as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                    <x-famledger.pulse-button variant="outline" type="submit">Filter</x-famledger.pulse-button>
                </form>
            </div>
        </div>
    </div>

    <div class="kt-card fin-pulse-kt-card overflow-hidden">
        <div class="kt-card-content admin-users-table-content">
            {{-- Desktop / tablet table --}}
            <div class="kt-scrollable-x-auto hidden md:block">
                <div class="rounded-xl overflow-hidden">
                    <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[160px]">Name</th>
                            <th class="min-w-[180px]">Email</th>
                            <th class="min-w-[100px]">Status</th>
                            <th class="min-w-[160px]" title="{{ __('Shows platform (Spatie) roles when assigned. If the user has none, shows their first family membership role (e.g. Owner)—that is the household role, not /admin/roles.') }}">
                                {{ __('Roles') }}
                                <span class="block text-[10px] font-normal text-muted-foreground normal-case tracking-normal">{{ __('platform or family') }}</span>
                            </th>
                            <th class="min-w-[180px]">{{ __('Families') }}</th>
                            <th class="w-14 text-end">Actions</th>
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
                            <td class="text-sm">
                                @php
                                    $familyRows = $u->familyMemberships->filter(fn ($m) => $m->family);
                                @endphp
                                @if ($familyRows->isEmpty())
                                    —
                                @else
                                    @foreach ($familyRows as $m)
                                        <span class="block">
                                            <a href="{{ route('families.show', $m->family) }}" class="text-primary hover:underline">{{ $m->family->name }}</a>@if ($m->role)<span class="text-muted-foreground"> ({{ $m->role->name }})</span>@endif
                                        </span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-end align-middle">
                                <x-famledger.dots-actions-menu :label="__('User actions')">
                                    <div class="kt-menu-item">
                                        <a class="kt-menu-link cursor-pointer" href="{{ route('admin.users.show', $u) }}">
                                            <span class="kt-menu-icon"><i class="ki-filled ki-eye fl-dots-action-icon--primary"></i></span>
                                            <span class="kt-menu-title">{{ __('View') }}</span>
                                        </a>
                                    </div>
                                    <div class="kt-menu-item">
                                        <a class="kt-menu-link cursor-pointer" href="{{ route('admin.users.edit', $u) }}">
                                            <span class="kt-menu-icon"><i class="ki-filled ki-pencil fl-dots-action-icon--warning"></i></span>
                                            <span class="kt-menu-title">{{ __('Edit') }}</span>
                                        </a>
                                    </div>
                                    @if (auth()->user()?->hasRole('Super Admin') && auth()->id() !== $u->id)
                                        <div class="kt-menu-separator"></div>
                                        <div class="kt-menu-item">
                                            <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="{{ __('Delete this user?') }}" data-confirm-message="{{ __('This permanently removes the account, roles, and sessions. Families they created stay on the platform with you recorded as creator.') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                    <span class="kt-menu-icon"><i class="ki-filled ki-trash fl-dots-action-icon--danger"></i></span>
                                                    <span class="kt-menu-title">{{ __('Delete') }}</span>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </x-famledger.dots-actions-menu>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden space-y-4">
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
                        <span class="uppercase tracking-wide">{{ __('Roles (platform or family):') }}</span>
                        <span class="font-medium text-foreground">{{ $roleText }}</span>
                    </div>

                    @php
                        $familyRowsMobile = $u->familyMemberships->filter(fn ($m) => $m->family);
                    @endphp
                    <div class="text-[11px] text-muted-foreground">
                        <span class="uppercase tracking-wide">Families:</span>
                        @if ($familyRowsMobile->isEmpty())
                            <span class="font-medium text-foreground">—</span>
                        @else
                            <span class="font-medium text-foreground block mt-0.5">
                                @foreach ($familyRowsMobile as $m)
                                    <span class="block">
                                        <a href="{{ route('families.show', $m->family) }}" class="text-primary hover:underline">{{ $m->family->name }}</a>@if ($m->role) ({{ $m->role->name }})@endif
                                    </span>
                                @endforeach
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap justify-end gap-2 pt-1">
                        <a href="{{ route('admin.users.show', $u) }}" class="fin-pulse-btn-outline fin-pulse-btn-sm">
                            View
                        </a>
                        <a href="{{ route('admin.users.edit', $u) }}" class="fin-pulse-btn-outline fin-pulse-btn-sm">
                            Edit
                        </a>
                        @if (auth()->user()?->hasRole('Super Admin') && auth()->id() !== $u->id)
                            <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="js-confirm-delete inline-block" data-confirm-title="{{ __('Delete this user?') }}" data-confirm-message="{{ __('This permanently removes the account. Families they created remain; you will be recorded as creator.') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="fin-pulse-btn-outline fin-pulse-btn-sm fin-pulse-btn-outline-danger">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="kt-card-footer admin-users-pagination">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
