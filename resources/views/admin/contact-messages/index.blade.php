@extends('layouts.metronic')

@section('title', 'Contact messages')
@section('page_title', 'Contact messages')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/famledger-bootstrap3-modal-slim.css') }}">
<link rel="stylesheet" href="{{ asset('css/famledger-contact-form-modal.css') }}">
<style>
    .famledger-contact-form-modal--view.modal { z-index: 10050 !important; }
    body > .modal-backdrop.in { z-index: 10040 !important; }
</style>
{{-- Metronic’s bundled CSS omits utilities like grid-cols-4; use explicit flex so four stats stay one row. --}}
<style>
    .famledger-contact-stats-row {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        gap: 0.5rem;
        width: 100%;
        margin-bottom: 2rem;
    }
    @media (min-width: 640px) {
        .famledger-contact-stats-row {
            gap: 1rem;
        }
    }
    .famledger-contact-stats-row > .kt-card {
        flex: 1 1 0;
        min-width: 0;
    }
</style>
@endpush

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-medium text-lg text-mono">Contact messages</h1>
            <p class="text-sm text-muted-foreground mt-0.5">Messages from the landing page “Talk to the FamLedger team” form.</p>
            @if (request()->filled('search') || request()->filled('read'))
                <p class="text-xs text-muted-foreground mt-1">
                    <span class="text-amber-700 dark:text-amber-300 font-medium">List below is filtered</span>
                    — stats above are for all messages in the database.
                </p>
            @endif
        </div>
    </div>

    @php
        $hasListFilters = request()->filled('search') || request()->filled('read');
        $totalContactMessages = $contactStats['total'];
    @endphp

    <div class="famledger-contact-stats-row">
        <a href="{{ route('admin.contact-messages.index') }}" class="kt-card p-3 hover:border-primary transition-colors">
            <div class="flex items-center justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-muted-foreground truncate">Total messages</p>
                    <p class="text-xl font-bold tabular-nums mt-1">{{ number_format($contactStats['total']) }}</p>
                    <p class="text-xs text-muted-foreground mt-1 truncate">All time</p>
                </div>
                <span class="rounded-full size-10 flex items-center justify-center bg-primary/10 text-primary shrink-0">
                    <i class="ki-filled ki-sms text-lg"></i>
                </span>
            </div>
        </a>
        <a href="{{ route('admin.contact-messages.index', ['read' => 'unread']) }}" class="kt-card p-3 hover:border-warning transition-colors">
            <div class="flex items-center justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-muted-foreground truncate">Unread</p>
                    <p class="text-xl font-bold tabular-nums mt-1 text-warning-600">{{ number_format($contactStats['unread']) }}</p>
                    <p class="text-xs text-muted-foreground mt-1 truncate">Needs attention</p>
                </div>
                <span class="rounded-full size-10 flex items-center justify-center bg-warning-500/10 text-warning-600 shrink-0">
                    <i class="ki-filled ki-notification-bing text-lg"></i>
                </span>
            </div>
        </a>
        <a href="{{ route('admin.contact-messages.index', ['read' => 'read']) }}" class="kt-card p-3 hover:border-emerald-500/40 transition-colors">
            <div class="flex items-center justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-muted-foreground truncate">Read</p>
                    <p class="text-xl font-bold tabular-nums mt-1 text-emerald-600">{{ number_format($contactStats['read']) }}</p>
                    <p class="text-xs text-muted-foreground mt-1 truncate">Marked as read</p>
                </div>
                <span class="rounded-full size-10 flex items-center justify-center bg-emerald-500/10 text-emerald-600 shrink-0">
                    <i class="ki-filled ki-check-circle text-lg"></i>
                </span>
            </div>
        </a>
        <div class="kt-card p-3">
            <div class="flex items-center justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-muted-foreground truncate">Last 7 days</p>
                    <p class="text-xl font-bold tabular-nums mt-1">{{ number_format($contactStats['last_7_days']) }}</p>
                    <p class="text-xs text-muted-foreground mt-1 truncate">New submissions</p>
                </div>
                <span class="rounded-full size-10 flex items-center justify-center bg-blue-500/10 text-blue-600 shrink-0">
                    <i class="ki-filled ki-calendar-tick text-lg"></i>
                </span>
            </div>
        </div>
    </div>

    @if ($messages->isEmpty() && $hasListFilters && $totalContactMessages > 0)
        <div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-amber-900 dark:text-amber-100 text-sm">
            <p class="font-medium">No rows match your current filters.</p>
            <p class="mt-1 text-amber-800/90 dark:text-amber-200/90">
                There {{ $totalContactMessages === 1 ? 'is' : 'are' }} <strong>{{ $totalContactMessages }}</strong> message(s) saved, but none match this search or read/unread filter.
                <a href="{{ route('admin.contact-messages.index') }}" class="text-primary font-medium hover:underline ml-1">Show all messages</a>
            </p>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-green-800 dark:text-green-200">{{ session('success') }}</div>
    @endif

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <form method="get" class="flex flex-nowrap items-center gap-2 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone or message" class="kt-input w-48 sm:w-64 min-w-0 shrink-0" />
            <select name="read" class="kt-select !w-[140px] shrink-0" style="width: 140px;">
                <option value="">All</option>
                <option value="unread" {{ request('read') === 'unread' ? 'selected' : '' }}>Unread</option>
                <option value="read" {{ request('read') === 'read' ? 'selected' : '' }}>Read</option>
            </select>
            <button type="submit" class="kt-btn kt-btn-outline shrink-0">Filter</button>
        </form>
    </div>

    <div class="kt-card p-0">
        {{-- Desktop / tablet table --}}
        <div class="kt-scrollable-x-auto hidden md:block">
            <table class="kt-table table-auto kt-table-border">
                <thead>
                    <tr>
                        <th class="min-w-[140px]">Name</th>
                        <th class="min-w-[180px]">Email</th>
                        <th class="min-w-[120px]">Phone</th>
                        <th class="min-w-[200px]">Message</th>
                        <th class="min-w-[120px]">Date</th>
                        <th class="min-w-[80px]">Read</th>
                        <th class="w-24">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $msg)
                    <tr class="{{ $msg->read_at ? '' : 'bg-muted/40' }}">
                        <td class="font-medium flex items-center gap-1.5">
                            @if ($msg->read_at)
                                <span class="inline-flex items-center justify-center size-4 rounded-full bg-emerald-50 text-emerald-600">
                                    <i class="ki-filled ki-check text-[11px]"></i>
                                </span>
                            @else
                                <span class="inline-block size-1.5 rounded-full bg-warning-500"></span>
                            @endif
                            <span>{{ $msg->name }}</span>
                        </td>
                        <td><a href="mailto:{{ $msg->email }}" class="text-primary hover:underline">{{ $msg->email }}</a></td>
                        <td class="text-sm text-muted-foreground">{{ $msg->phone ? Str::limit($msg->phone, 24) : '—' }}</td>
                        <td class="text-sm text-muted-foreground">{{ Str::limit(str_replace('&nbsp;', ' ', strip_tags($msg->message)), 60) }}</td>
                        <td class="text-sm">{{ $msg->created_at->format('M j, Y H:i') }}</td>
                        <td>
                            @if ($msg->read_at)
                                <span class="kt-badge kt-badge-sm kt-badge-success">Read</span>
                            @else
                                <span class="kt-badge kt-badge-sm kt-badge-warning">New</span>
                            @endif
                        </td>
                        <td>
                            <button
                                type="button"
                                class="kt-btn kt-btn-ghost kt-btn-sm"
                                data-famledger-contact-message-modal-url="{{ route('admin.contact-messages.modal', $msg, false) }}"
                            >{{ __('View') }}</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted-foreground py-8">
                            @if ($hasListFilters && $totalContactMessages > 0)
                                No messages match these filters.
                            @elseif ($totalContactMessages === 0)
                                No contact messages yet. Submit the form on the public landing page (Talk to the FamLedger team).
                            @else
                                No messages on this page.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
                <div class="md:hidden p-4 space-y-4">
            @forelse ($messages as $msg)
                <div class="rounded-2xl border border-border bg-background shadow-sm px-5 py-4 flex flex-col gap-3 {{ $msg->read_at ? '' : 'bg-warning-50 border-warning-200' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-medium text-foreground truncate">
                                {{ $msg->name }}
                            </span>
                            <a href="mailto:{{ $msg->email }}" class="text-xs text-primary hover:underline truncate">
                                {{ $msg->email }}
                            </a>
                            @if ($msg->phone)
                                <span class="text-xs text-muted-foreground truncate">{{ $msg->phone }}</span>
                            @endif
                        </div>
                        <span class="shrink-0">
                            @if ($msg->read_at)
                                <span class="kt-badge kt-badge-sm kt-badge-success">Read</span>
                            @else
                                <span class="kt-badge kt-badge-sm kt-badge-warning">New</span>
                            @endif
                        </span>
                    </div>
                    <div class="text-[11px] text-muted-foreground">
                        {{ Str::limit(str_replace('&nbsp;', ' ', strip_tags($msg->message)), 90) }}
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-muted-foreground">
                        <span>{{ $msg->created_at->format('M j, Y H:i') }}</span>
                        <button
                            type="button"
                            class="kt-btn kt-btn-xs kt-btn-outline"
                            data-famledger-contact-message-modal-url="{{ route('admin.contact-messages.modal', $msg, false) }}"
                        >{{ __('View') }}</button>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted-foreground py-8 text-sm">
                    @if ($hasListFilters && $totalContactMessages > 0)
                        No messages match these filters.
                    @elseif ($totalContactMessages === 0)
                        No contact messages yet. Use the landing page contact form to create one.
                    @else
                        No messages on this page.
                    @endif
                </div>
            @endforelse
        </div>

        <div class="px-4 py-3 border-t border-border">{{ $messages->withQueryString()->links() }}</div>
    </div>
</div>
@endsection

@push('famledger_bootstrap_modals')
{{-- Injection point only; modal markup is moved to document.body after load (a11y: avoids aria-hidden ancestor of focused dialog). --}}
<div id="famledger-admin-contact-modal-container"></div>
@endpush

@push('scripts')
<script>
window.FAMLEDGER_CONTACT_MESSAGES_INDEX = {
    container: '#famledger-admin-contact-modal-container',
    indexUrl: @json(route('admin.contact-messages.index', [], false)),
    openOnLoadId: @json($openContactMessageId),
    modalUrlTemplate: @json($contactModalUrlTemplate),
};
</script>
<script src="{{ asset('metronic/assets/js/jquery.js') }}"></script>
<script src="{{ asset('metronic/assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/famledger-contact-form-modal.js') }}"></script>
<script src="{{ asset('js/admin-contact-messages-modal.js') }}"></script>
@endpush
