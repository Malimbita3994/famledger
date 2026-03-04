@extends('layouts.metronic')

@section('title', $family->name)
@section('page_title', $family->name)

@section('content')
{{-- Toolbar --}}
<div class="pb-5">
    <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center flex-wrap gap-1 lg:gap-5">
            <h1 class="font-medium text-lg text-mono">{{ $family->name }}</h1>
            <div class="flex items-center gap-1 text-sm font-normal">
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Home</a>
                <span class="text-muted-foreground text-sm">/</span>
                <a class="text-secondary-foreground hover:text-primary" href="{{ route('families.index') }}">Families</a>
                <span class="text-muted-foreground text-sm">/</span>
                <span class="text-mono">{{ $family->name }}</span>
            </div>
        </div>
        <div class="flex items-center flex-wrap gap-1.5 lg:gap-3.5">
            <a href="{{ route('families.wallets.index', $family) }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-wallet"></i>
                Wallets
            </a>
            <a href="{{ route('families.projects.index', $family) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-briefcase"></i>
                Projects
            </a>
            <a href="{{ route('families.edit', $family) }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-pencil"></i>
                Edit
            </a>
        </div>
    </div>
</div>

<div class="kt-container-fixed">
    <style>
    @media (min-width: 1024px) {
        .family-details-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
    }
    @media (min-width: 768px) and (max-width: 1023px) {
        .family-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
            width: 100%;
        }
    }
    @media (max-width: 767px) {
        .family-details-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
            width: 100%;
        }
    }
    .family-details-item {
        min-width: 0;
        box-sizing: border-box;
    }
    </style>
    {{-- Summary / hero --}}
    <div class="kt-card mb-5 lg:mb-7.5">
        <div class="kt-card-content py-6 lg:py-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 class="text-xl lg:text-2xl font-semibold text-foreground truncate">{{ $family->name }}</h2>
                        <span class="kt-badge {{ $family->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px] shrink-0">
                            <span class="kt-badge-dot size-1.5"></span>
                            {{ ucfirst($family->status) }}
                        </span>
                    </div>
                    @if ($family->description)
                        <p class="text-secondary-foreground text-sm leading-relaxed max-w-2xl mt-1">{{ Str::limit($family->description, 160) }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-4 text-sm text-muted-foreground">
                        <span class="inline-flex items-center gap-1.5">
                            <i class="ki-filled ki-people text-base text-muted-foreground"></i>
                            {{ $family->familyMembers->count() }} {{ Str::plural('member', $family->familyMembers->count()) }}
                        </span>
                        <span>{{ $family->currency_code }}</span>
                        @if ($family->creator)
                            <span>Created by {{ $family->creator->name }}</span>
                        @endif
                        @if ($family->created_at)
                            <span>{{ $family->created_at->format('M j, Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-5 lg:gap-7.5 lg:grid-cols-3">
        {{-- Details card --}}
        <div class="lg:col-span-1">
            <div class="kt-card h-full">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Details</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex items-center justify-center size-10 rounded-full bg-muted text-muted-foreground shrink-0">
                            <i class="ki-filled ki-home-2 text-lg"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground truncate">
                                {{ $family->name }}
                            </div>
                            <div class="text-xs text-muted-foreground mt-0.5 truncate">
                                {{ $family->currency_code }}
                                @if($family->timezone)
                                    · {{ $family->timezone }}
                                @endif
                                @if($family->country)
                                    · {{ $family->country }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <dl class="family-details-grid text-sm">
                        <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Primary currency</dt>
                            <dd class="text-foreground font-medium">{{ $family->currency_code }}</dd>
                        </div>
                        <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Timezone</dt>
                            <dd class="text-foreground">{{ $family->timezone }}</dd>
                        </div>
                        <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Country</dt>
                            <dd class="text-foreground">{{ $family->country ?: '—' }}</dd>
                        </div>
                        <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Members</dt>
                            <dd class="text-foreground">
                                {{ $family->familyMembers->count() }} {{ Str::plural('member', $family->familyMembers->count()) }}
                            </dd>
                        </div>
                        @if ($family->creator)
                        <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Owner</dt>
                            <dd class="text-foreground">{{ $family->creator->name }}</dd>
                        </div>
                        @endif
                        @if ($family->created_at)
                        <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Created</dt>
                            <dd class="text-foreground">{{ $family->created_at->format('M j, Y') }}</dd>
                        </div>
                        @endif
                        <div class="flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                            <dt class="text-xs text-muted-foreground uppercase tracking-wide">Status</dt>
                            <dd>
                                <span class="kt-badge kt-badge-sm {{ $family->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px]">
                                    <span class="kt-badge-dot size-1.5"></span>
                                    {{ ucfirst($family->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>

                    @if ($family->description)
                        <div class="mt-5 pt-4 border-t border-border">
                            <h4 class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5">Description</h4>
                            <p class="text-sm text-foreground leading-relaxed">
                                {{ $family->description }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Members card --}}
        <div class="lg:col-span-2">
            <div class="kt-card kt-card-grid min-w-full h-full">
                <div class="kt-card-header flex-wrap gap-2">
                    <h3 class="kt-card-title text-sm">Members ({{ $family->familyMembers->count() }})</h3>
                    @if ($canManageMembers ?? false)
                        <a href="{{ route('families.members.create', $family) }}" class="kt-btn kt-btn-sm kt-btn-primary" title="Owner and co-owner can add members">
                            <i class="ki-filled ki-plus"></i>
                            Add member
                        </a>
                    @endif
                </div>
                <div class="kt-card-content">
                    @if ($family->familyMembers->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                            <span class="flex items-center justify-center size-12 rounded-full bg-muted text-muted-foreground mb-3">
                                <i class="ki-filled ki-people text-2xl"></i>
                            </span>
                            <p class="text-sm font-medium text-foreground">No members yet</p>
                            <p class="text-sm text-secondary-foreground mt-1">@if ($canManageMembers ?? false)<a href="{{ route('families.members.create', $family) }}" class="text-primary hover:underline">Add a member</a>@else Invite members from Settings (coming soon).@endif</p>
                        </div>
                    @else
                        {{-- Desktop / tablet table --}}
                        <div class="kt-scrollable-x-auto hidden md:block">
                            <table class="kt-table table-auto kt-table-border">
                                <thead>
                                    <tr>
                                        <th class="min-w-[260px]">Member</th>
                                        <th class="min-w-[120px]">Role</th>
                                        <th class="min-w-[120px]">Status</th>
                                        <th class="min-w-[100px]">Primary</th>
                                        @if ($canManageMembers ?? false)
                                        <th class="w-[60px]">ACTIONS</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($family->familyMembers as $member)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground font-medium text-sm">
                                                    {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                                                </span>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-sm font-medium text-mono">{{ $member->member_name ?? $member->user->name ?? 'Unknown' }}</span>
                                                    <span class="text-sm text-secondary-foreground font-normal truncate">{{ $member->user->email ?? '—' }}</span>
                                                    @if ($member->sex || $member->member_type)
                                                        <span class="text-xs text-muted-foreground mt-0.5">{{ ucfirst($member->sex ?? '') }}{{ $member->sex && $member->member_type ? ' · ' : '' }}{{ ucfirst($member->member_type ?? '') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-foreground font-normal">{{ $member->role->name ?? '—' }}</td>
                                        <td>
                                            <span class="kt-badge kt-badge-sm {{ ($member->status ?? 'active') === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px]">
                                                <span class="kt-badge-dot size-1.5"></span>
                                                {{ ucfirst($member->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td class="text-foreground font-normal">
                                            @if ($member->is_primary)
                                                <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">Primary</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        @if ($canManageMembers ?? false)
                                        <td class="text-center">
                                            <div class="kt-menu flex-inline" data-kt-menu="true">
                                                <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button">
                                                        <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                    </button>
                                                    <div class="kt-menu-dropdown kt-menu-default w-[220px]" data-kt-menu-dismiss="true">
                                                        <div class="kt-menu-item">
                                                            <a class="kt-menu-link" href="{{ route('families.members.edit', [$family, $member]) }}">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                                <span class="kt-menu-title">Edit</span>
                                                            </a>
                                                        </div>
                                                        <div class="kt-menu-item">
                                                            @if (! $member->is_primary)
                                                            <form action="{{ route('families.members.transfer-ownership', [$family, $member]) }}" method="POST" class="inline-block w-full">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-arrow-right"></i></span>
                                                                    <span class="kt-menu-title">Transfer ownership here</span>
                                                                </button>
                                                            </form>
                                                            @else
                                                            <span class="kt-menu-link opacity-60 cursor-default">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-crown"></i></span>
                                                                <span class="kt-menu-title">Current owner</span>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="kt-menu-item">
                                                            @if (($member->status ?? 'active') === 'active')
                                                            <form action="{{ route('families.members.deactivate', [$family, $member]) }}" method="POST" class="inline-block w-full">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-moon"></i></span>
                                                                    <span class="kt-menu-title">Deactivate member</span>
                                                                </button>
                                                            </form>
                                                            @else
                                                            <form action="{{ route('families.members.activate', [$family, $member]) }}" method="POST" class="inline-block w-full">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-sun"></i></span>
                                                                    <span class="kt-menu-title">Activate member</span>
                                                                </button>
                                                            </form>
                                                            @endif
                                                        </div>
                                                        <div class="kt-menu-separator"></div>
                                                        <div class="kt-menu-item">
                                                            <form action="{{ route('families.members.destroy', [$family, $member]) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="Delete this member?" data-confirm-message="They will be removed from this family and lose access.">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-trash"></i></span>
                                                                    <span class="kt-menu-title">Delete member</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach ($family->familyMembers as $member)
                                <div class="rounded-2xl border border-border bg-background shadow-sm p-4 flex flex-col gap-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="flex items-center justify-center rounded-full size-9 shrink-0 bg-muted text-foreground font-medium text-sm">
                                                {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                                            </span>
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-sm font-medium text-mono truncate">
                                                    {{ $member->member_name ?? $member->user->name ?? 'Unknown' }}
                                                </span>
                                                <span class="text-xs text-secondary-foreground truncate">
                                                    {{ $member->user->email ?? '—' }}
                                                </span>
                                                @if ($member->sex || $member->member_type)
                                                    <span class="text-[11px] text-muted-foreground mt-0.5">
                                                        {{ ucfirst($member->sex ?? '') }}{{ $member->sex && $member->member_type ? ' · ' : '' }}{{ ucfirst($member->member_type ?? '') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="kt-badge kt-badge-sm {{ ($member->status ?? 'active') === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px] shrink-0">
                                            <span class="kt-badge-dot size-1.5"></span>
                                            {{ ucfirst($member->status ?? 'active') }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-[11px] text-muted-foreground">
                                        <span>
                                            {{ __('Role:') }}
                                            <span class="font-medium text-foreground">{{ $member->role->name ?? '—' }}</span>
                                        </span>
                                        <span>
                                            @if ($member->is_primary)
                                                <span class="kt-badge kt-badge-xs kt-badge-primary kt-badge-outline">Primary</span>
                                            @endif
                                        </span>
                                    </div>

                                    @if ($canManageMembers ?? false)
                                        <div class="flex flex-wrap justify-end gap-2 pt-1">
                                            <a href="{{ route('families.members.edit', [$family, $member]) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                                                Edit
                                            </a>
                                            @if (! $member->is_primary)
                                            <form action="{{ route('families.members.transfer-ownership', [$family, $member]) }}" method="POST" class="inline-block js-confirm-delete" data-confirm-title="Transfer ownership?" data-confirm-message="Current owner role will be downgraded.">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="kt-btn kt-btn-xs kt-btn-outline">
                                                    Transfer ownership
                                                </button>
                                            </form>
                                            @endif
                                            <form action="{{ route('families.members.destroy', [$family, $member]) }}" method="POST" class="js-confirm-delete inline-block" data-confirm-title="Delete this member?" data-confirm-message="They will be removed from this family and lose access.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-destructive">
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if ($canManageMembers ?? false)
                        <p class="text-xs text-muted-foreground mt-3">Add members by email from the button above.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
