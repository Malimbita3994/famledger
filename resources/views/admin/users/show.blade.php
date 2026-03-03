@extends('layouts.metronic')

@section('title', $user->name)
@section('page_title', 'User')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6"><i class="ki-filled ki-left mr-1"></i> Back to users</a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="text-xl font-semibold">{{ $user->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="kt-btn kt-btn-outline kt-btn-sm">Edit</a>
            @if ($user->id !== auth()->id())
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Deactivate this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="kt-btn kt-btn-ghost kt-btn-sm text-destructive">Deactivate</button>
                </form>
            @endif
        </div>
    </div>

    <div class="kt-card p-5 max-w-2xl">
        <dl class="grid gap-3 sm:grid-cols-2">
            <div><dt class="text-xs text-muted-foreground uppercase">Email</dt><dd>{{ $user->email }}</dd></div>
            <div><dt class="text-xs text-muted-foreground uppercase">Phone</dt><dd>{{ $user->phone ?? '—' }}</dd></div>
            <div><dt class="text-xs text-muted-foreground uppercase">Status</dt><dd><span class="kt-badge kt-badge-sm {{ $user->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">{{ \App\Models\User::statuses()[$user->status] ?? $user->status }}</span></dd></div>
            <div>
                <dt class="text-xs text-muted-foreground uppercase">Roles</dt>
                <dd>
                    @php
                        $systemNames = $user->roles->pluck('name');
                        $roleText = $systemNames->isNotEmpty()
                            ? $systemNames->join(', ')
                            : ($user->familyMemberships->first()?->role?->name ?? '—');
                    @endphp
                    {{ $roleText }}
                </dd>
            </div>
            @if ($user->last_login_at)
                <div><dt class="text-xs text-muted-foreground uppercase">Last login</dt><dd>{{ $user->last_login_at->format('M j, Y H:i') }}</dd></div>
            @endif
            @if ($user->creator)
                <div><dt class="text-xs text-muted-foreground uppercase">Created by</dt><dd>{{ $user->creator->name }}</dd></div>
            @endif
        </dl>
    </div>
</div>
@endsection
