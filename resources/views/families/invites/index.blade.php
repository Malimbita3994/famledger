@extends('layouts.metronic')

@section('title', 'Invite members')
@section('page_title', 'Invite members')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6">
        <i class="ki-filled ki-left mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Invite members</h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Invite people by email or share a link to join {{ $family->name }}.
            </p>
        </div>
    </div>

    {{-- Success / error flashes are handled by SweetAlert2 in the main layout --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-200">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-5">
    {{-- Invite by email --}}
    <div class="kt-card rounded-xl border border-border bg-card">
        <div class="kt-card-header border-b border-border px-4 lg:px-5 py-3">
            <h2 class="kt-card-title text-sm font-medium">Invite people by email</h2>
        </div>
        <div class="kt-card-content p-4 lg:p-5">
            <form action="{{ route('families.invites.store', $family) }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <div class="min-w-[200px] flex-1">
                    <label for="email" class="block text-xs font-medium text-muted-foreground mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="kt-input w-full" placeholder="friend@example.com" required>
                </div>
                <div class="min-w-[160px]">
                    <label for="role_id" class="block text-xs font-medium text-muted-foreground mb-1">Role</label>
                    <select name="role_id" id="role_id" class="kt-input w-full" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ (int) old('role_id') === (int) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-send"></i>
                    Invite
                </button>
            </form>
        </div>
    </div>

    {{-- Invite with link --}}
    <div class="kt-card rounded-xl border border-border bg-card">
        <div class="kt-card-header border-b border-border px-4 lg:px-5 py-3">
            <h2 class="kt-card-title text-sm font-medium">Invite with link</h2>
        </div>
        <div class="kt-card-content p-4 lg:p-5">
            <p class="text-sm text-muted-foreground mb-3">
                Anyone with this link can join {{ $family->name }} as a member. You can reset the link to disable the old one.
            </p>
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" id="invite-link-input" readonly
                       value="{{ $family->invite_link }}"
                       class="kt-input flex-1 min-w-[200px] font-mono text-sm">
                <button type="button" id="copy-invite-link" class="kt-btn kt-btn-secondary" data-copied="Copy">
                    <i class="ki-filled ki-copy"></i>
                    <span class="copy-text">Copy link</span>
                </button>
                <form action="{{ route('families.invites.reset-link', $family) }}"
                      method="POST"
                      class="inline js-confirm-delete"
                      data-confirm-title="Reset invite link?"
                      data-confirm-message="Reset the invite link? The previous link will stop working.">
                    @csrf
                    <button type="submit" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrows-circle"></i>
                        Reset link
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Pending / past invites --}}
    <div class="kt-card kt-card-grid min-w-full h-full">
        <div class="kt-card-header flex-wrap gap-2">
            <h3 class="kt-card-title text-sm">Members invitations ({{ $invitations->total() }})</h3>
        </div>
        <div class="kt-card-content">
            @if ($invitations->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                    <span class="flex items-center justify-center size-12 rounded-full bg-muted text-muted-foreground mb-3">
                        <i class="ki-filled ki-message-text text-2xl"></i>
                    </span>
                    <p class="text-sm font-medium text-foreground">No invitations yet</p>
                    <p class="text-sm text-secondary-foreground mt-1">
                        Invite people by email above or share the invite link.
                    </p>
                </div>
            @else
                {{-- Desktop / tablet table --}}
                <div class="kt-scrollable-x-auto hidden md:block">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[220px]">Email</th>
                                <th class="min-w-[140px]">Role</th>
                                <th class="min-w-[120px]">Status</th>
                                <th class="min-w-[120px]">Sent</th>
                                <th class="min-w-[80px] text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitations as $invitation)
                                <tr>
                                    <td>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-sm font-medium text-mono truncate">{{ $invitation->email }}</span>
                                            <span class="text-xs text-secondary-foreground truncate">
                                                Invited by {{ $invitation->inviter?->name ?? '—' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-foreground font-normal">{{ $invitation->role?->name ?? '—' }}</td>
                                    <td>
                                        @php
                                            $badgeColor = $invitation->status === 'pending'
                                                ? 'kt-badge-warning'
                                                : ($invitation->status === 'accepted' ? 'kt-badge-success' : 'kt-badge-secondary');
                                        @endphp
                                        <span class="kt-badge kt-badge-sm {{ $badgeColor }} kt-badge-outline rounded-[30px]">
                                            <span class="kt-badge-dot size-1.5"></span>
                                            {{ ucfirst($invitation->status) }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-muted-foreground">
                                        {{ $invitation->created_at?->format('M j, Y') }}
                                    </td>
                                    <td class="text-right">
                                        @if ($invitation->status === 'pending')
                                            <form action="{{ route('families.invites.destroy', [$family, $invitation]) }}" method="POST" class="inline-block js-confirm-delete" data-confirm-title="Remove this invitation?" data-confirm-message="They will no longer be able to use this invite link.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-destructive">
                                                    Remove
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-muted-foreground">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden space-y-3">
                    @foreach ($invitations as $invitation)
                        <div class="rounded-2xl border border-border bg-background shadow-sm p-4 flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-medium text-mono truncate">
                                        {{ $invitation->email }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground truncate">
                                        {{ $invitation->role?->name ?? '—' }}
                                    </span>
                                </div>
                                @php
                                    $badgeColor = $invitation->status === 'pending'
                                        ? 'kt-badge-warning'
                                        : ($invitation->status === 'accepted' ? 'kt-badge-success' : 'kt-badge-secondary');
                                @endphp
                                <span class="kt-badge kt-badge-sm {{ $badgeColor }} kt-badge-outline rounded-[30px] shrink-0">
                                    <span class="kt-badge-dot size-1.5"></span>
                                    {{ ucfirst($invitation->status) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-muted-foreground">
                                <span>
                                    Sent
                                    <span class="font-medium text-foreground">{{ $invitation->created_at?->format('M j, Y') }}</span>
                                </span>
                                <span class="truncate">
                                    Invited by
                                    <span class="font-medium text-foreground">{{ $invitation->inviter?->name ?? '—' }}</span>
                                </span>
                            </div>

                            @if ($invitation->status === 'pending')
                                <div class="flex flex-wrap justify-end gap-2 pt-1">
                                    <form action="{{ route('families.invites.destroy', [$family, $invitation]) }}" method="POST" class="js-confirm-delete inline-block" data-confirm-title="Remove this invitation?" data-confirm-message="They will no longer be able to use this invite link.">
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

                @if ($invitations->hasPages())
                    <div class="mt-4">
                        {{ $invitations->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('copy-invite-link');
    var input = document.getElementById('invite-link-input');
    var span = btn && btn.querySelector('.copy-text');
    if (!btn || !input) return;
    btn.addEventListener('click', function () {
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            navigator.clipboard.writeText(input.value);
            if (span) span.textContent = 'Copied!';
            setTimeout(function () { if (span) span.textContent = 'Copy link'; }, 2000);
        } catch (e) {}
    });
})();
</script>
@endpush
@endsection
