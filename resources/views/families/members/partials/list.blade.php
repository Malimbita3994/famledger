@php
    $members = $family->familyMembers ?? collect();
    $memberDetailsPayload = $members->map(function ($m) {
        $sex = $m->sex;
        $sexLabel = $sex === 'male' ? __('Male') : ($sex === 'female' ? __('Female') : null);
        $type = $m->member_type;
        $typeLabel = $type === 'adult' ? __('Adult') : ($type === 'child' ? __('Child') : null);

        return [
            'id' => $m->id,
            'displayName' => $m->user?->name ?? $m->member_name ?? '—',
            'email' => $m->user?->email ?? '—',
            'memberName' => $m->member_name,
            'role' => $m->role?->name ?? '—',
            'status' => $m->status ? ucfirst((string) $m->status) : '—',
            'isPrimary' => (bool) $m->is_primary,
            'sex' => $sexLabel,
            'memberType' => $typeLabel,
            'joinedAt' => $m->joined_at
                ? $m->joined_at->timezone(config('app.timezone'))->format('M j, Y g:i A')
                : '—',
        ];
    })->keyBy('id');

    $memberModalLabels = [
        'name' => __('Name'),
        'email' => __('Email'),
        'memberName' => __('Member name'),
        'role' => __('Role'),
        'status' => __('Status'),
        'primary' => __('Primary'),
        'sex' => __('Sex'),
        'memberType' => __('Adult or child'),
        'joined' => __('Joined'),
        'yes' => __('Yes'),
        'no' => __('No'),
    ];
@endphp

<div class="kt-card fin-pulse-kt-card overflow-hidden">
    <div class="kt-card-header flex-wrap gap-2 border-b border-border">
        <h3 class="kt-card-title text-sm">{{ __('People in this family') }}</h3>
        <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $members->count() }}</span>
        @if ($canManageMembers)
            <x-famledger.pulse-button variant="primary" size="sm" class="shrink-0 ms-auto" :href="route('families.members.create')" :title="__('Owner and co-owner can add members')">
                <i class="ki-filled ki-plus text-sm"></i>
                {{ __('Add member') }}
            </x-famledger.pulse-button>
        @endif
    </div>
    <div class="kt-card-content p-0">
        @if ($members->isEmpty())
            <div class="py-12 px-4 text-center text-muted-foreground text-sm">
                {{ __('No members found.') }}
                @if ($canManageMembers)
                    <div class="mt-3">
                        <a href="{{ route('families.members.create') }}" class="text-primary hover:underline">{{ __('Add a member') }}</a>
                    </div>
                @endif
            </div>
        @else
            <div class="kt-scrollable-x-auto hidden md:block">
                <table class="kt-table table-auto kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">{{ __('Member') }}</th>
                            <th class="min-w-[120px]">{{ __('Role') }}</th>
                            <th class="min-w-[100px]">{{ __('Status') }}</th>
                            <th class="min-w-[80px] text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-medium text-foreground">{{ $member->user?->name ?? $member->member_name ?? '—' }}</span>
                                        <span class="text-xs text-muted-foreground truncate">{{ $member->user?->email ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="text-sm">{{ $member->role?->name ?? '—' }}</td>
                                <td>
                                    <span class="kt-badge kt-badge-sm kt-badge-outline {{ ($member->status ?? '') === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                        {{ ucfirst($member->status ?? '—') }}
                                    </span>
                                    @if (!empty($member->is_primary))
                                        <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline ms-1">{{ __('Primary') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="kt-menu flex-inline justify-end" data-kt-menu="true">
                                        <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                            <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button" aria-label="{{ __('Actions') }}">
                                                <i class="ki-filled ki-dots-vertical text-lg"></i>
                                            </button>
                                            <div class="kt-menu-dropdown kt-menu-default w-full max-w-[220px]" data-kt-menu-dismiss="true">
                                                <div class="kt-menu-item">
                                                    <button type="button" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer js-member-details-view" data-member-id="{{ $member->id }}">
                                                        <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                        <span class="kt-menu-title">{{ __('View') }}</span>
                                                    </button>
                                                </div>
                                                @if ($canManageMembers)
                                                    <div class="kt-menu-item">
                                                        <a class="kt-menu-link" href="{{ route('families.members.edit', $member) }}">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                            <span class="kt-menu-title">{{ __('Edit') }}</span>
                                                        </a>
                                                    </div>
                                                    @if ($member->user_id !== auth()->id())
                                                        <div class="kt-menu-item">
                                                            <form action="{{ route('families.members.transfer-ownership', $member) }}" method="POST" class="inline-block w-full js-confirm-delete" data-confirm-title="{{ __('Transfer ownership?') }}" data-confirm-message="{{ __('They will become the primary owner.') }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-crown"></i></span>
                                                                    <span class="kt-menu-title">{{ __('Transfer ownership') }}</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                    @if (($member->status ?? '') === 'active' && $member->user_id !== auth()->id())
                                                        <div class="kt-menu-item">
                                                            <form action="{{ route('families.members.deactivate', $member) }}" method="POST" class="inline-block w-full">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">{{ __('Deactivate') }}</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                    @if (($member->status ?? '') === 'inactive')
                                                        <div class="kt-menu-item">
                                                            <form action="{{ route('families.members.activate', $member) }}" method="POST" class="inline-block w-full">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">{{ __('Activate') }}</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                    <div class="kt-menu-separator"></div>
                                                    <div class="kt-menu-item">
                                                        <form action="{{ route('families.members.destroy', $member) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="{{ __('Delete this member?') }}" data-confirm-message="{{ __('They will be removed from this family and lose access.') }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-trash"></i></span>
                                                                <span class="kt-menu-title">{{ __('Remove') }}</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="md:hidden p-4 space-y-4">
                @foreach ($members as $member)
                    <div class="rounded-2xl border border-border bg-background shadow-sm p-4 flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-foreground">{{ $member->user?->name ?? $member->member_name ?? '—' }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ $member->user?->email ?? '—' }}</p>
                            </div>
                            <span class="kt-badge kt-badge-sm kt-badge-outline shrink-0 {{ ($member->status ?? '') === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                {{ ucfirst($member->status ?? '—') }}
                            </span>
                        </div>
                        <div class="text-sm text-muted-foreground">{{ __('Role') }}: <span class="text-foreground font-medium">{{ $member->role?->name ?? '—' }}</span></div>
                        <div class="flex flex-wrap gap-2 pt-1 justify-end">
                            <div class="kt-menu flex-inline justify-end" data-kt-menu="true">
                                <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                    <button class="kt-menu-toggle kt-btn kt-btn-xs kt-btn-icon kt-btn-outline" type="button" aria-label="{{ __('Actions') }}">
                                        <i class="ki-filled ki-dots-vertical text-base"></i>
                                    </button>
                                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[220px]" data-kt-menu-dismiss="true">
                                        <div class="kt-menu-item">
                                            <button type="button" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer js-member-details-view" data-member-id="{{ $member->id }}">
                                                <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                <span class="kt-menu-title">{{ __('View') }}</span>
                                            </button>
                                        </div>
                                        @if ($canManageMembers)
                                            <div class="kt-menu-item">
                                                <a class="kt-menu-link" href="{{ route('families.members.edit', $member) }}">
                                                    <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                    <span class="kt-menu-title">{{ __('Edit') }}</span>
                                                </a>
                                            </div>
                                            @if ($member->user_id !== auth()->id())
                                                <div class="kt-menu-item">
                                                    <form action="{{ route('families.members.transfer-ownership', $member) }}" method="POST" class="inline-block w-full js-confirm-delete" data-confirm-title="{{ __('Transfer ownership?') }}" data-confirm-message="{{ __('They will become the primary owner.') }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">
                                                            <span class="kt-menu-icon"><i class="ki-filled ki-crown"></i></span>
                                                            <span class="kt-menu-title">{{ __('Transfer ownership') }}</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                            @if (($member->status ?? '') === 'active' && $member->user_id !== auth()->id())
                                                <div class="kt-menu-item">
                                                    <form action="{{ route('families.members.deactivate', $member) }}" method="POST" class="inline-block w-full">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">{{ __('Deactivate') }}</button>
                                                    </form>
                                                </div>
                                            @endif
                                            @if (($member->status ?? '') === 'inactive')
                                                <div class="kt-menu-item">
                                                    <form action="{{ route('families.members.activate', $member) }}" method="POST" class="inline-block w-full">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">{{ __('Activate') }}</button>
                                                    </form>
                                                </div>
                                            @endif
                                            <div class="kt-menu-separator"></div>
                                            <div class="kt-menu-item">
                                                <form action="{{ route('families.members.destroy', $member) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="{{ __('Delete this member?') }}" data-confirm-message="{{ __('They will be removed from this family and lose access.') }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer text-destructive hover:!bg-destructive/10">
                                                        <span class="kt-menu-icon"><i class="ki-filled ki-trash"></i></span>
                                                        <span class="kt-menu-title">{{ __('Remove') }}</span>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@if ($members->isNotEmpty())
    <x-famledger.detail-modal
        id="member_details_modal"
        :title="__('Member details')"
        title-id="member_details_modal_title"
        body-id="member_details_modal_body"
        :close-label="__('Close')"
    />

@push('scripts')
<script>
(function () {
    var detailsById = @json($memberDetailsPayload);
    var defaultTitle = @json(__('Member details'));
    var L = @json($memberModalLabels);
    var modalId = 'member_details_modal';

    function openMemberModal(memberId) {
        var D = window.FamLedgerDetailModal;
        if (!D) return;

        var data = detailsById[String(memberId)] || detailsById[memberId];
        if (!data) return;

        var body = document.getElementById('member_details_modal_body');
        var titleEl = document.getElementById('member_details_modal_title');
        if (!body || !titleEl) return;

        titleEl.textContent = data.displayName || defaultTitle;
        body.innerHTML = '';

        body.appendChild(D.row(L.name, D.fmt(data.displayName)));
        body.appendChild(D.row(L.email, D.fmt(data.email)));
        body.appendChild(D.row(L.memberName, D.fmt(data.memberName)));
        body.appendChild(D.row(L.role, D.fmt(data.role)));
        body.appendChild(D.row(L.status, D.fmt(data.status)));
        body.appendChild(D.row(L.primary, data.isPrimary ? L.yes : L.no));
        body.appendChild(D.row(L.sex, D.fmt(data.sex)));
        body.appendChild(D.row(L.memberType, D.fmt(data.memberType)));
        body.appendChild(D.row(L.joined, D.fmt(data.joinedAt)));

        D.show(modalId);
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-member-details-view');
        if (!btn) return;
        var id = btn.getAttribute('data-member-id');
        if (id) openMemberModal(id);
    });
})();
</script>
@endpush
@endif
