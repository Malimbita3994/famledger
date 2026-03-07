@extends('layouts.metronic')

@section('title', 'Role Permissions')
@section('page_title', 'Role Permissions')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8 pb-6">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-xl font-medium leading-none text-mono">
                Permissions — Toggle
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Role-level access for this workspace.
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.roles.index') }}" class="kt-btn kt-btn-outline">
                View Roles
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-10">
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header flex flex-wrap items-center justify-between gap-3">
                <h3 class="kt-card-title">
                    Role Permissions for
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-primary hover:underline">
                        {{ $role->name }}
                    </a>
                </h3>
                @if (session('success'))
                    <span class="text-xs font-medium text-green-600 dark:text-green-300">
                        {{ session('success') }}
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}">
                @csrf
                @method('PUT')

                <div class="kt-card-content grid grid-cols-1 lg:grid-cols-2 gap-5 py-5 lg:py-7.5">
                    @foreach ($permissions as $group => $perms)
                        <div class="rounded-xl border border-border p-4 flex flex-col gap-4 bg-card">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-sm font-semibold text-foreground text-mono">
                                        {{ \Illuminate\Support\Str::headline($group) }}
                                    </span>
                                    <span class="text-xs font-medium text-muted-foreground">
                                        {{ $perms->count() }}
                                        {{ \Illuminate\Support\Str::plural('permission', $perms->count()) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="inline-flex items-center gap-1.5">
                                        <span class="text-[11px] font-medium text-muted-foreground">
                                            All
                                        </span>
                                        <input
                                            type="checkbox"
                                            class="kt-switch kt-switch-sm js-module-toggle"
                                            data-module="module-{{ $group }}"
                                            @php
                                                $allChecked = $perms->every(function ($permission) use ($role) {
                                                    return $role->permissions->contains('id', $permission->id);
                                                });
                                            @endphp
                                            @checked($allChecked)
                                        >
                                    </label>
                                    <span class="text-primary text-lg shrink-0">
                                        <i class="ki-filled ki-shield-tick"></i>
                                    </span>
                                </div>
                            </div>

                            @php
                                $initialVisible = 5;
                                $permList = $perms->values();
                                $totalPerms = $permList->count();
                                $hasMore = $totalPerms > $initialVisible;
                                $visiblePerms = $hasMore ? $permList->take($initialVisible) : $permList;
                                $morePerms = $hasMore ? $permList->slice($initialVisible) : collect();
                            @endphp
                            <div class="flex flex-col gap-3" data-module-container="module-{{ $group }}">
                                @foreach ($visiblePerms as $permission)
                                    @php
                                        $rawName = $permission->name;
                                        $prefix = $group . '_';
                                        $action = \Illuminate\Support\Str::startsWith($rawName, $prefix)
                                            ? \Illuminate\Support\Str::after($rawName, $prefix)
                                            : $rawName;
                                        $label = \Illuminate\Support\Str::headline($action);
                                    @endphp
                                    <label class="rounded-lg border border-border bg-muted/40 px-3 py-2.5 flex items-center justify-between gap-3 cursor-pointer hover:border-primary/60 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center rounded-full bg-primary/5 text-primary border border-primary/20 size-8 shrink-0">
                                                <i class="ki-filled ki-key-square text-base"></i>
                                            </span>
                                            <span class="text-sm font-medium text-mono">
                                                {{ $label }}
                                            </span>
                                        </div>
                                        <span class="inline-flex items-center">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->name }}"
                                                class="kt-switch kt-switch-sm"
                                                @checked($role->permissions->contains('id', $permission->id))
                                            >
                                        </span>
                                    </label>
                                @endforeach

                                @if ($hasMore)
                                    <div id="perms-more-{{ $group }}" class="perms-more-content hidden flex flex-col gap-3" aria-hidden="true">
                                        @foreach ($morePerms as $permission)
                                            @php
                                                $rawName = $permission->name;
                                                $prefix = $group . '_';
                                                $action = \Illuminate\Support\Str::startsWith($rawName, $prefix)
                                                    ? \Illuminate\Support\Str::after($rawName, $prefix)
                                                    : $rawName;
                                                $label = \Illuminate\Support\Str::headline($action);
                                            @endphp
                                            <label class="rounded-lg border border-border bg-muted/40 px-3 py-2.5 flex items-center justify-between gap-3 cursor-pointer hover:border-primary/60 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center justify-center rounded-full bg-primary/5 text-primary border border-primary/20 size-8 shrink-0">
                                                        <i class="ki-filled ki-key-square text-base"></i>
                                                    </span>
                                                    <span class="text-sm font-medium text-mono">
                                                        {{ $label }}
                                                    </span>
                                                </div>
                                                <span class="inline-flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $permission->name }}"
                                                        class="kt-switch kt-switch-sm"
                                                        @checked($role->permissions->contains('id', $permission->id))
                                                    >
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <button
                                        type="button"
                                        class="js-view-more-perms kt-btn kt-btn-ghost kt-btn-sm w-full justify-center gap-1.5 text-muted-foreground hover:text-foreground"
                                        data-target="perms-more-{{ $group }}"
                                        data-more-label="View more ({{ $morePerms->count() }} more)"
                                        data-less-label="View less"
                                    >
                                        <i class="ki-filled ki-arrow-down text-base js-view-more-icon"></i>
                                        <span class="js-view-more-text">View more ({{ $morePerms->count() }} more)</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="kt-card-footer justify-end gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // View more/less per module
    document.querySelectorAll('.js-view-more-perms').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-target');
            var block = id ? document.getElementById(id) : null;
            var textEl = this.querySelector('.js-view-more-text');
            var iconEl = this.querySelector('.js-view-more-icon');
            if (!block || !textEl) return;
            var isHidden = block.classList.contains('hidden');
            block.classList.toggle('hidden');
            block.setAttribute('aria-hidden', isHidden ? 'false' : 'true');
            this.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            if (isHidden) {
                textEl.textContent = this.getAttribute('data-less-label') || 'View less';
                if (iconEl) { iconEl.classList.remove('ki-arrow-down'); iconEl.classList.add('ki-arrow-up'); }
            } else {
                textEl.textContent = this.getAttribute('data-more-label') || 'View more';
                if (iconEl) { iconEl.classList.remove('ki-arrow-up'); iconEl.classList.add('ki-arrow-down'); }
            }
        });
    });

    // Module-level toggle: select/unselect all permissions in a module
    document.querySelectorAll('.js-module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var moduleKey = this.getAttribute('data-module');
            if (!moduleKey) return;
            var container = document.querySelector('[data-module-container="' + moduleKey + '"]');
            if (!container) return;
            var checked = this.checked;
            container.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(function (cb) {
                cb.checked = checked;
            });
        });
    });
})();
</script>
@endpush
@endsection

