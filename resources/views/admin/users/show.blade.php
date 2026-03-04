@extends('layouts.metronic')

@section('title', $user->name)
@section('page_title', 'User')

@section('content')
<style>
.user-detail-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.user-detail-row .user-detail-col {
    width: 100%;
    transition: transform 160ms ease-out, box-shadow 160ms ease-out, border-color 160ms ease-out, background-color 160ms ease-out;
}

.user-detail-row .user-detail-col:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.10);
    border-color: rgba(59, 130, 246, 0.6);
    background-color: rgba(249, 250, 251, 0.95);
}

@media (min-width: 900px) {
    .user-detail-row {
        flex-direction: row;
    }

    .user-detail-row .user-detail-col {
        flex: 1 1 0;
    }
}
</style>

<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i> Back to users
    </a>

    <div class="kt-card p-5 lg:p-7.5">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-lg font-semibold text-mono">{{ $user->name }}</h1>
                <p class="text-sm text-muted-foreground mt-0.5">
                    {{ $user->email }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="kt-badge kt-badge-lg kt-badge-outline {{ $user->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                    {{ \App\Models\User::statuses()[$user->status] ?? ucfirst($user->status) }}
                </span>
                <a href="{{ route('admin.users.edit', $user) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-pencil me-1"></i>
                    Edit
                </a>
                @if ($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Deactivate this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost text-destructive">
                            Deactivate
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-5 lg:gap-7.5">
            <div class="user-detail-row">
                <div class="user-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Full name</div>
                    <div class="text-sm font-medium text-foreground">{{ $user->name }}</div>
                </div>
                <div class="user-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Email</div>
                    <div class="text-sm font-medium text-foreground">{{ $user->email }}</div>
                </div>
                <div class="user-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Phone</div>
                    <div class="text-sm font-medium text-foreground">{{ $user->phone ?? '—' }}</div>
                </div>
                <div class="user-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">Status</div>
                    <div class="text-sm font-medium text-foreground">
                        <span class="kt-badge kt-badge-sm {{ $user->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">
                            {{ \App\Models\User::statuses()[$user->status] ?? $user->status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="user-detail-row">
                <div class="user-detail-col p-3 rounded-lg border border-border bg-muted/40">
                    <div class="text-xs text-muted-foreground mb-1">System / family roles</div>
                    <div class="text-sm font-medium text-foreground">
                        @php
                            $systemNames = $user->roles->pluck('name');
                            $roleText = $systemNames->isNotEmpty()
                                ? $systemNames->join(', ')
                                : ($user->familyMemberships->first()?->role?->name ?? '—');
                        @endphp
                        {{ $roleText }}
                    </div>
                </div>
                @if ($user->last_login_at)
                    <div class="user-detail-col p-3 rounded-lg border border-border bg-muted/40">
                        <div class="text-xs text-muted-foreground mb-1">Last login</div>
                        <div class="text-sm font-medium text-foreground">
                            {{ $user->last_login_at->format('M j, Y H:i') }}
                        </div>
                    </div>
                @endif
                @if ($user->creator)
                    <div class="user-detail-col p-3 rounded-lg border border-border bg-muted/40">
                        <div class="text-xs text-muted-foreground mb-1">Created by</div>
                        <div class="text-sm font-medium text-foreground">
                            {{ $user->creator->name }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
