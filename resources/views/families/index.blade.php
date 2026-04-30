@extends('layouts.metronic')

@section('title', 'Families')
@section('page_title', 'Families')

@section('content')
@push('styles')
<style>
    .families-pulse-hero {
        border: 1px solid rgba(14, 165, 233, 0.2);
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(0, 158, 247, 0.04) 55%, rgba(255, 255, 255, 0.9) 100%);
        border-radius: 1rem;
        box-shadow: 0 8px 26px rgba(15, 23, 42, 0.06);
    }
    .families-pulse-metric {
        position: relative;
        overflow: hidden;
    }
    .families-pulse-metric::after {
        content: "";
        position: absolute;
        inset: auto -30% -35% auto;
        width: 90px;
        height: 90px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 72%);
        pointer-events: none;
    }
    .families-grid-card {
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.2s ease;
    }
    .families-grid-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.1);
        border-color: rgba(0, 158, 247, 0.35);
    }
    .families-narrow-wrap {
        width: 100%;
        /* Force a visible shrink even if Metronic sizing utilities try to stretch. */
        max-width: 720px !important;
        margin-left: auto;
        margin-right: auto;
    }
    /* The workspace hero uses a bordered wrapper + a standalone kt-card-content div.
       Increase left/right padding so text/breadcrumb/buttons don't feel glued to the border. */
    .families-pulse-hero .kt-card-content {
        padding-inline: 1.75rem !important;
    }
</style>
@endpush
{{-- Toolbar (Metronic Team Crew style) --}}
<div class="pb-5">
    <div class="kt-container-fixed families-pulse-hero py-4 sm:py-5">
        <div class="kt-card-content flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center flex-wrap gap-1 lg:gap-5">
                <div>
                    <p class="fin-pulse-eyebrow mb-1">Workspace</p>
                    <h1 class="font-semibold text-xl text-mono leading-tight">Families</h1>
                </div>
                <div class="flex items-center gap-1 text-sm font-normal">
                    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Home</a>
                    <span class="text-muted-foreground text-sm">/</span>
                    <span class="text-mono">Families</span>
                </div>
            </div>
            <div class="flex items-center flex-wrap gap-1.5 lg:gap-3.5">
                <x-famledger.pulse-button variant="primary" :href="route('families.create')">
                    <i class="ki-filled ki-plus"></i>
                    Register a family
                </x-famledger.pulse-button>
            </div>
        </div>
    </div>
</div>

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($families->isEmpty())
        <div class="kt-card fin-pulse-kt-card families-narrow-wrap">
            <div class="kt-card-content py-16 text-center">
                <div class="mx-auto w-24 h-24 rounded-full bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 flex items-center justify-center mb-6">
                    <i class="ki-filled ki-people text-4xl text-blue-600 dark:text-blue-400"></i>
                </div>
                <h2 class="text-xl font-semibold text-foreground mb-2">No families yet</h2>
                <p class="text-muted-foreground mb-8 max-w-md mx-auto">Create your first household to start managing finances together. Invite family members and track expenses, incomes, and savings goals collaboratively.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <x-famledger.pulse-button variant="primary" :href="route('families.create')">
                        <i class="ki-filled ki-plus"></i>
                        Create Your First Family
                    </x-famledger.pulse-button>
                    <x-famledger.pulse-button variant="outline" :href="route('dashboard')">
                        <i class="ki-filled ki-home"></i>
                        Back to Dashboard
                    </x-famledger.pulse-button>
                </div>
            </div>
        </div>
    @else
        <style>
            .stats-summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.75rem;
                width: 100%;
                margin-bottom: 1.5rem;
            }
        </style>
        <div class="stats-summary-grid">
            <x-famledger.pulse-stat-card label="Total Families" :value="$families->count()" class="rounded-xl" />
            <x-famledger.pulse-stat-card label="Active Families" :value="$families->where('status', 'active')->count()" class="rounded-xl">
                <span class="famledger-pulse-stat-card__extra" style="color:#16a34a; font-weight:600;">Active</span>
            </x-famledger.pulse-stat-card>
            <x-famledger.pulse-stat-card label="Total Members" :value="$families->sum('family_members_count')" class="rounded-xl" />
            <x-famledger.pulse-stat-card label="Currencies Used" :value="$families->pluck('currency_code')->unique()->count()" class="rounded-xl" />
        </div>

        {{-- Families List --}}
            <div class="kt-card fin-pulse-kt-card kt-card-grid min-w-full">
                <div class="kt-card-header flex-wrap gap-2">
                    <h3 class="kt-card-title text-sm">{{ auth()->user()->hasRole(['Super Admin', 'super-admin']) ? __('All families') : __('Your families') }}</h3>
                    <div class="flex flex-wrap gap-2 lg:gap-5">
                        <div class="flex items-center gap-2 families-view-toggle-wrapper hidden md:flex">
                            <span class="text-sm text-secondary-foreground">View:</span>
                            <div class="kt-menu kt-menu-default" data-kt-menu="true">
                                <div class="kt-menu-item" data-kt-menu-item-offset="0, 4" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                    <button type="button" class="kt-menu-toggle fin-pulse-btn-outline fin-pulse-btn-sm flex-nowrap" id="families_view_toggle" aria-haspopup="true" aria-expanded="false">
                                        <span class="families-view-label">Table</span>
                                        <i class="ki-filled ki-down text-xs ms-1"></i>
                                    </button>
                                    <div class="kt-menu-dropdown w-36 py-2" data-kt-menu-dismiss="true">
                                        <div class="kt-menu-item">
                                            <a class="kt-menu-link js-families-view" href="#" data-view="table">
                                                <span class="kt-menu-icon"><i class="ki-filled ki-row-horizontal"></i></span>
                                                <span class="kt-menu-title">Table</span>
                                            </a>
                                        </div>
                                        <div class="kt-menu-item">
                                            <a class="kt-menu-link js-families-view" href="#" data-view="cards">
                                                <span class="kt-menu-icon"><i class="ki-filled ki-element-11"></i></span>
                                                <span class="kt-menu-title">Cards</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-card-content">
                    {{-- Table view (default) --}}
                    <div id="families_table_view" class="families-view-panel hidden md:block">
                        <div class="kt-scrollable-x-auto">
                            <table class="kt-table table-auto kt-table-border">
                                <thead>
                                    <tr>
                                        <th class="min-w-[260px]">Family</th>
                                        <th class="min-w-[100px]">Members</th>
                                        <th class="min-w-[100px]">Currency</th>
                                        <th class="min-w-[120px]">Status</th>
                                        <th class="w-[60px]">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($families as $family)
                                    @php $isMemberOfFamily = in_array($family->id, $memberFamilyIds ?? [], true); @endphp
                                    <tr class="hover:bg-muted/50 transition-colors">
                                        <td>
                                            @if ($isMemberOfFamily)
                                            <a href="{{ route('families.show', $family) }}" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
                                                <span class="flex items-center justify-center rounded-full size-10 shrink-0 bg-gradient-to-br from-blue-500 to-blue-600 text-white font-semibold text-sm">{{ strtoupper(substr($family->name, 0, 1)) }}</span>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-sm font-semibold text-foreground hover:text-primary truncate">{{ $family->name }}</span>
                                                    @if ($family->description)
                                                        <span class="text-sm text-secondary-foreground font-normal truncate max-w-[200px] block">{{ Str::limit($family->description, 40) }}</span>
                                                    @endif
                                                </div>
                                            </a>
                                            @else
                                            <div class="flex items-center gap-3">
                                                <span class="flex items-center justify-center rounded-full size-10 shrink-0 bg-gradient-to-br from-slate-400 to-slate-600 text-white font-semibold text-sm">{{ strtoupper(substr($family->name, 0, 1)) }}</span>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-sm font-semibold text-foreground truncate">{{ $family->name }}</span>
                                                </div>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="text-foreground font-medium">{{ $family->family_members_count }} {{ Str::plural('member', $family->family_members_count) }}</td>
                                        <td class="text-foreground font-medium">{{ $family->currency_code }}</td>
                                        <td>
                                            <span class="kt-badge {{ $family->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px]">
                                                <span class="kt-badge-dot size-1.5"></span>
                                                {{ ucfirst($family->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($isMemberOfFamily)
                                            <div class="kt-menu flex-inline" data-kt-menu="true">
                                                <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button">
                                                        <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                    </button>
                                                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                        <div class="kt-menu-item">
                                                            <a class="kt-menu-link" href="{{ route('families.show', $family) }}">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-search-list"></i></span>
                                                                <span class="kt-menu-title">View</span>
                                                            </a>
                                                        </div>
                                                        <div class="kt-menu-item">
                                                            <a class="kt-menu-link" href="{{ route('families.edit', $family) }}">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                                <span class="kt-menu-title">Edit</span>
                                                            </a>
                                                        </div>
                                                        @if (in_array($family->id, $ownerFamilyIds ?? [], true))
                                                        <div class="kt-menu-separator"></div>
                                                        <div class="kt-menu-item">
                                                            <form action="{{ route('families.destroy', $family) }}" method="POST" class="js-confirm-delete js-confirm-delete-danger inline-block w-full" data-confirm-title="{{ __('Delete this family?') }}" data-confirm-message="{{ __('All data for this family will be removed permanently.') }}" data-confirm-danger-note="{{ __('This cannot be undone.') }}" data-confirm-yes="{{ __('Yes, delete permanently') }}" data-confirm-no="{{ __('Cancel') }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-trash"></i></span>
                                                                    <span class="kt-menu-title">{{ __('Delete family') }}</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <span class="text-xs text-muted-foreground">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Cards view (optional) --}}
                    <div id="families_cards_view" class="families-view-panel md:hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 families-grid">
                            @foreach ($families as $family)
                            @php $isMemberOfFamily = in_array($family->id, $memberFamilyIds ?? [], true); @endphp
                            <div class="group families-grid-card rounded-2xl border border-border bg-background shadow-sm overflow-hidden relative min-h-[180px] flex flex-col">
                                @if ($isMemberOfFamily)
                                <div class="absolute top-4 right-4 z-10 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('families.edit', $family) }}" onclick="event.stopPropagation()" class="fin-pulse-btn-outline fin-pulse-btn-sm" title="Edit">
                                        Edit
                                    </a>
                                    @if (in_array($family->id, $ownerFamilyIds ?? [], true))
                                    <form action="{{ route('families.destroy', $family) }}" method="POST" class="inline js-confirm-delete js-confirm-delete-danger" data-confirm-title="{{ __('Delete this family?') }}" data-confirm-message="{{ __('All data for this family will be removed permanently.') }}" data-confirm-danger-note="{{ __('This cannot be undone.') }}" data-confirm-yes="{{ __('Yes, delete permanently') }}" data-confirm-no="{{ __('Cancel') }}" onclick="event.stopPropagation()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="fin-pulse-btn-outline fin-pulse-btn-sm fin-pulse-btn-outline-danger" title="{{ __('Delete family') }}">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @endif
                                @if ($isMemberOfFamily)
                                <a href="{{ route('families.show', $family) }}" class="block p-6 pr-14 flex flex-col flex-1 min-h-0">
                                @else
                                <div class="block p-6 flex flex-col flex-1 min-h-0">
                                @endif
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="flex items-center justify-center rounded-full size-12 shrink-0 bg-gradient-to-br {{ $isMemberOfFamily ? 'from-blue-500 to-blue-600' : 'from-slate-400 to-slate-600' }} text-white font-semibold text-sm">{{ strtoupper(substr($family->name, 0, 1)) }}</span>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-foreground truncate">{{ $family->name }}</h3>
                                            <span class="kt-badge kt-badge-sm {{ $family->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline mt-1 w-fit">
                                                {{ $family->status }}
                                            </span>
                                        </div>
                                    </div>
                                    @if ($isMemberOfFamily && $family->description)
                                        <p class="text-sm text-secondary-foreground line-clamp-2 mb-4">{{ $family->description }}</p>
                                    @endif
                                    <div class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <i class="ki-filled ki-profile-user text-xs"></i>
                                            <span>{{ $family->family_members_count }} {{ Str::plural('member', $family->family_members_count) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <i class="ki-filled ki-dollar text-xs"></i>
                                            <span>{{ $family->currency_code }}</span>
                                        </div>
                                    </div>
                                @if ($isMemberOfFamily)
                                </a>
                                @else
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@if (!$families->isEmpty())
<script>
(function () {
    var KEY = 'families_view';
    var tablePanel = document.getElementById('families_table_view');
    var cardsPanel = document.getElementById('families_cards_view');
    var toggleBtn = document.getElementById('families_view_toggle');
    var labelEl = toggleBtn ? toggleBtn.querySelector('.families-view-label') : null;

    function setView(view) {
        // On small screens, always show cards for better responsiveness
        if (window.innerWidth < 768) {
            if (tablePanel) tablePanel.classList.add('hidden');
            if (cardsPanel) cardsPanel.classList.remove('hidden');
            if (labelEl) labelEl.textContent = 'Cards';
            return;
        }

        view = view === 'cards' ? 'cards' : 'table';
        try { localStorage.setItem(KEY, view); } catch (e) {}
        if (tablePanel) tablePanel.classList.toggle('hidden', view !== 'table');
        if (cardsPanel) cardsPanel.classList.toggle('hidden', view !== 'cards');
        if (labelEl) labelEl.textContent = view === 'cards' ? 'Cards' : 'Table';
    }

    function init() {
        var saved = '';
        try { saved = localStorage.getItem(KEY) || ''; } catch (e) {}

        if (saved === 'cards' || saved === 'table') {
            setView(saved);
        } else {
            setView('table');
        }
    }

    document.querySelectorAll('.js-families-view').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            setView(a.getAttribute('data-view'));
        });
    });

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
</script>
@endif
@endsection
