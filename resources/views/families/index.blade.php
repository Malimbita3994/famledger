@extends('layouts.metronic')

@section('title', 'Families')
@section('page_title', 'Families')

@section('content')
{{-- Toolbar (Metronic Team Crew style) --}}
<div class="pb-5">
    <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center flex-wrap gap-1 lg:gap-5">
            <h1 class="font-medium text-lg text-mono">Families</h1>
            <div class="flex items-center gap-1 text-sm font-normal">
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Home</a>
                <span class="text-muted-foreground text-sm">/</span>
                <span class="text-mono">Families</span>
            </div>
        </div>
        <div class="flex items-center flex-wrap gap-1.5 lg:gap-3.5">
            <a href="{{ route('families.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i>
                Register a family
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed">
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 flex items-center gap-3 text-green-800 dark:text-green-200">
            <i class="ki-filled ki-check-circle text-xl shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($families->isEmpty())
        <div class="kt-card">
            <div class="kt-card-content py-12 text-center">
                <i class="ki-filled ki-people text-5xl text-muted-foreground mb-4"></i>
                <p class="font-semibold text-foreground">No families yet</p>
                <p class="text-sm text-secondary-foreground mt-1">Create your first household to start managing finances together.</p>
                <a href="{{ route('families.create') }}" class="kt-btn kt-btn-primary mt-6">Create Family</a>
            </div>
        </div>
    @else
        <div class="grid gap-5 lg:gap-7.5">
            <div class="kt-card kt-card-grid min-w-full">
                <div class="kt-card-header flex-wrap gap-2">
                    <h3 class="kt-card-title text-sm">Showing {{ $families->count() }} {{ Str::plural('family', $families->count()) }}</h3>
                    <div class="flex flex-wrap gap-2 lg:gap-5">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-secondary-foreground">View:</span>
                            <div class="kt-menu kt-menu-default" data-kt-menu="true">
                                <div class="kt-menu-item" data-kt-menu-item-offset="0, 4" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                    <button type="button" class="kt-menu-toggle kt-btn kt-btn-outline kt-btn-sm flex-nowrap" id="families_view_toggle" aria-haspopup="true" aria-expanded="false">
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
                    <div id="families_table_view" class="families-view-panel">
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
                                    <tr>
                                        <td>
                                            <a href="{{ route('families.show', $family) }}" class="flex items-center gap-2.5 hover:opacity-90">
                                                <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground font-medium text-sm">{{ strtoupper(substr($family->name, 0, 1)) }}</span>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-sm font-medium text-mono hover:text-primary truncate">{{ $family->name }}</span>
                                                    @if ($family->description)
                                                        <span class="text-sm text-secondary-foreground font-normal truncate max-w-[200px] block">{{ Str::limit($family->description, 40) }}</span>
                                                    @endif
                                                </div>
                                            </a>
                                        </td>
                                        <td class="text-foreground font-normal">{{ $family->family_members_count }} {{ Str::plural('member', $family->family_members_count) }}</td>
                                        <td class="text-foreground font-normal">{{ $family->currency_code }}</td>
                                        <td>
                                            <span class="kt-badge {{ $family->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px]">
                                                <span class="kt-badge-dot size-1.5"></span>
                                                {{ ucfirst($family->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
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
                                                        <div class="kt-menu-separator"></div>
                                                        <div class="kt-menu-item">
                                                            <form action="{{ route('families.destroy', $family) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="Delete this family?" data-confirm-message="This cannot be undone.">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-trash"></i></span>
                                                                    <span class="kt-menu-title">Remove</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Cards view (optional) --}}
                    <div id="families_cards_view" class="families-view-panel hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 families-grid">
                            @foreach ($families as $family)
                            <div class="rounded-2xl border border-border bg-background shadow-sm overflow-hidden hover:shadow-md hover:border-primary/20 transition-all duration-200 relative min-h-[140px] flex flex-col">
                                <div class="absolute top-3 right-3 z-10 flex items-center gap-1">
                                    <a href="{{ route('families.edit', $family) }}" onclick="event.stopPropagation()" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" title="Edit">
                                        <i class="ki-filled ki-pencil text-sm"></i>
                                    </a>
                                    <form action="{{ route('families.destroy', $family) }}" method="POST" class="inline js-confirm-delete" data-confirm-title="Delete this family?" data-confirm-message="This cannot be undone." onclick="event.stopPropagation()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-destructive hover:!bg-destructive/10" title="Delete">
                                            <i class="ki-filled ki-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                                <a href="{{ route('families.show', $family) }}" class="block p-5 pr-14 flex flex-col flex-1 min-h-0">
                                    <h3 class="font-semibold text-foreground truncate pr-2">{{ $family->name }}</h3>
                                    @if ($family->description)
                                        <p class="text-sm text-secondary-foreground mt-1 line-clamp-2">{{ $family->description }}</p>
                                    @endif
                                    <div class="mt-auto pt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                        <span>{{ $family->currency_code }}</span>
                                        <span>·</span>
                                        <span>{{ $family->family_members_count }} {{ Str::plural('member', $family->family_members_count) }}</span>
                                    </div>
                                    <span class="kt-badge kt-badge-sm {{ $family->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline mt-2 w-fit">
                                        {{ $family->status }}
                                    </span>
                                </a>
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
        view = view === 'cards' ? 'cards' : 'table';
        try { localStorage.setItem(KEY, view); } catch (e) {}
        if (tablePanel) tablePanel.classList.toggle('hidden', view !== 'table');
        if (cardsPanel) cardsPanel.classList.toggle('hidden', view !== 'cards');
        if (labelEl) labelEl.textContent = view === 'cards' ? 'Cards' : 'Table';
    }

    function init() {
        var saved = '';
        try { saved = localStorage.getItem(KEY) || ''; } catch (e) {}
        setView(saved === 'cards' ? 'cards' : 'table');
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
