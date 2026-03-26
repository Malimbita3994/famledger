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
<style>
    .cm-pulse-page.admin-pulse-page {
        --ap-accent: #009ef7;
        --ap-accent-2: #0ea5e9;
        --ap-soft: #f0f9ff;
        --ap-ring: rgba(0, 158, 247, 0.28);
    }
    .cm-pulse-page .admin-pulse-frame {
        max-width: none;
        margin-left: 0;
        margin-right: 0;
    }
    /* Vertical rhythm between main pulse sections (overview / filter / table) */
    .cm-pulse-page .cm-pulse-card-stack {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    @media (min-width: 768px) {
        .cm-pulse-page .cm-pulse-card-stack {
            gap: 2.5rem;
        }
    }
    .admin-pulse-eyebrow {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .admin-pulse-title {
        font-size: clamp(1.35rem, 2.8vw, 1.7rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: var(--ap-accent);
    }
    .admin-pulse-breadcrumb a {
        color: #64748b;
        transition: color 0.2s ease;
    }
    .admin-pulse-breadcrumb a:hover {
        color: var(--ap-accent);
    }
    .admin-pulse-frame {
        padding: 3px;
        border-radius: 24px;
        background: linear-gradient(
            135deg,
            rgba(0, 158, 247, 0.42) 0%,
            rgba(255, 255, 255, 0.96) 46%,
            rgba(14, 165, 233, 0.3) 100%
        );
        box-shadow:
            0 4px 24px rgba(0, 158, 247, 0.12),
            0 24px 48px rgba(15, 23, 42, 0.08);
        width: 100%;
        max-width: min(56rem, 100%);
        margin-left: auto;
        margin-right: auto;
    }
    .admin-pulse-card-inner {
        background: #fff;
        border-radius: 21px;
        padding: 1.75rem 1.5rem 2rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }
    @media (min-width: 640px) {
        .admin-pulse-card-inner {
            padding: 2rem 1.85rem 2.25rem;
        }
    }
    .dark .admin-pulse-card-inner {
        background: rgb(15 23 42 / 0.96);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    .admin-pulse-section-title {
        font-size: clamp(1.05rem, 2vw, 1.2rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--ap-accent);
    }
    .admin-pulse-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 1.25rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 12px;
        color: #fff !important;
        border: none;
        cursor: pointer;
        background: linear-gradient(135deg, var(--ap-accent) 0%, var(--ap-accent-2) 100%);
        box-shadow: 0 4px 14px rgba(0, 158, 247, 0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .admin-pulse-btn-primary:hover {
        filter: brightness(1.05);
        box-shadow: 0 6px 20px rgba(0, 158, 247, 0.42);
        transform: translateY(-1px);
    }
    .admin-pulse-btn-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 1.15rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        background: rgba(255, 255, 255, 0.95);
        color: #334155 !important;
        text-decoration: none !important;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }
    .admin-pulse-btn-outline:hover {
        border-color: var(--ap-accent);
        background: rgba(0, 158, 247, 0.06);
        box-shadow: 0 0 0 1px rgba(0, 158, 247, 0.12);
    }
    .dark .admin-pulse-btn-outline {
        background: rgba(30, 41, 59, 0.9);
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.35);
    }
    .admin-pulse-btn-outline-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        background: rgba(255, 255, 255, 0.95);
        color: #334155 !important;
        text-decoration: none !important;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .admin-pulse-btn-outline-sm:hover {
        border-color: var(--ap-accent);
        background: rgba(0, 158, 247, 0.08);
    }
    .dark .admin-pulse-btn-outline-sm {
        background: rgba(30, 41, 59, 0.9);
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.35);
    }
    .cm-pulse-page .admin-pulse-create .kt-form-label,
    .cm-pulse-page .admin-pulse-create label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #475569;
    }
    .dark .cm-pulse-page .admin-pulse-create .kt-form-label,
    .dark .cm-pulse-page .admin-pulse-create label {
        color: #94a3b8;
    }
    .cm-pulse-page .admin-pulse-create .kt-input {
        width: 100%;
        padding: 0.8rem 1rem;
        font-size: 0.9375rem;
        border-radius: 12px;
        background: var(--ap-soft) !important;
        border: 1px solid transparent !important;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
    }
    .cm-pulse-page .admin-pulse-create select.kt-select {
        width: 100%;
        font-size: 0.9375rem;
        border-radius: 12px;
        border: 1px solid transparent !important;
        background-color: var(--ap-soft) !important;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
    }
    .cm-pulse-page .admin-pulse-create .kt-input:hover {
        background: #e0f2fe !important;
    }
    .cm-pulse-page .admin-pulse-create select.kt-select:hover {
        background-color: #e0f2fe !important;
    }
    .cm-pulse-page .admin-pulse-create .kt-input:focus {
        outline: none;
        border-color: var(--ap-accent) !important;
        box-shadow: 0 0 0 3px var(--ap-ring) !important;
        background: #fff !important;
    }
    .cm-pulse-page .admin-pulse-create select.kt-select:focus {
        outline: none;
        border-color: var(--ap-accent) !important;
        box-shadow: 0 0 0 3px var(--ap-ring) !important;
        background-color: #fff !important;
    }
    .famledger-contact-stats-row {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        gap: 0.5rem;
        width: 100%;
    }
    @media (min-width: 640px) {
        .famledger-contact-stats-row {
            gap: 1rem;
        }
    }
    .famledger-contact-stats-row > .kt-card,
    .famledger-contact-stats-row > a.kt-card {
        flex: 1 1 0;
        min-width: 0;
    }
    .cm-pulse-page .cm-pulse-stat-card {
        border-radius: 16px !important;
        border: 1px solid rgba(14, 165, 233, 0.2) !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        text-decoration: none !important;
        color: inherit !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .cm-pulse-page .cm-pulse-stat-card:hover {
        border-color: rgba(0, 158, 247, 0.35) !important;
        box-shadow: 0 8px 24px rgba(0, 158, 247, 0.1);
        transform: translateY(-1px);
    }
    .dark .cm-pulse-page .cm-pulse-stat-card {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.55) 0%, rgb(15 23 42 / 0.72) 100%) !important;
        border-color: rgba(14, 165, 233, 0.22) !important;
    }
    .cm-pulse-page .cm-pulse-inner-table .admin-pulse-card-inner {
        padding-left: 0;
        padding-right: 0;
    }
    .cm-pulse-page .cm-pulse-table-surface {
        border-radius: 16px;
        border: 1px solid rgba(14, 165, 233, 0.14);
        overflow: hidden;
        background: rgba(255, 255, 255, 0.5);
    }
    .dark .cm-pulse-page .cm-pulse-table-surface {
        border-color: rgba(14, 165, 233, 0.2);
        background: rgba(30, 41, 59, 0.25);
    }
    .cm-pulse-page .cm-pulse-mobile-card {
        border-radius: 16px;
        border: 1px solid rgba(14, 165, 233, 0.2);
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .dark .cm-pulse-page .cm-pulse-mobile-card {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.55) 0%, rgb(15 23 42 / 0.72) 100%);
        border-color: rgba(14, 165, 233, 0.22);
    }
    .cm-pulse-page .cm-pulse-mobile-card--new {
        border-color: rgba(245, 158, 11, 0.4);
        background: linear-gradient(180deg, #fffbeb 0%, #fff7ed 100%);
    }
    .dark .cm-pulse-page .cm-pulse-mobile-card--new {
        border-color: rgba(245, 158, 11, 0.35);
        background: linear-gradient(180deg, rgba(120, 53, 15, 0.25) 0%, rgba(30, 41, 59, 0.85) 100%);
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-pulse-btn-primary:hover,
        .cm-pulse-page .cm-pulse-stat-card:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
@php
    $hasListFilters = request()->filled('search') || request()->filled('read');
    $totalContactMessages = $contactStats['total'];
@endphp
<div class="contact-messages-pulse-page cm-pulse-page admin-pulse-page min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="admin-pulse-eyebrow mb-1.5">{{ __('Platform') }}</p>
                <h1 class="admin-pulse-title">{{ __('Contact messages') }}</h1>
                <div class="admin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('admin.dashboard') }}">{{ __('Admin') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium">{{ __('Contact messages') }}</span>
                </div>
            </div>
            <div class="shrink-0 ms-auto">
                <a href="{{ route('admin.dashboard') }}" class="admin-pulse-btn-outline inline-flex">
                    <i class="ki-filled ki-left text-base"></i>
                    {{ __('Back to admin') }}
                </a>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed cm-pulse-card-stack px-4 sm:px-6 lg:px-8 pb-14 min-w-0 max-w-full">
        <div class="admin-pulse-frame shrink-0 min-w-0 max-w-full">
            <div class="admin-pulse-card-inner min-w-0 max-w-full overflow-hidden">
                <h2 class="admin-pulse-section-title mb-4">{{ __('Inbox overview') }}</h2>
                <div class="famledger-contact-stats-row">
                    <a href="{{ route('admin.contact-messages.index') }}" class="kt-card p-3 cm-pulse-stat-card">
                        <div class="flex items-center justify-between gap-2 min-w-0">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-muted-foreground truncate">{{ __('Total messages') }}</p>
                                <p class="text-xl font-bold tabular-nums mt-1">{{ number_format($contactStats['total']) }}</p>
                                <p class="text-xs text-muted-foreground mt-1 truncate">{{ __('All time') }}</p>
                            </div>
                            <span class="rounded-full size-10 flex items-center justify-center bg-primary/10 text-primary shrink-0">
                                <i class="ki-filled ki-sms text-lg"></i>
                            </span>
                        </div>
                    </a>
                    <a href="{{ route('admin.contact-messages.index', ['read' => 'unread']) }}" class="kt-card p-3 cm-pulse-stat-card">
                        <div class="flex items-center justify-between gap-2 min-w-0">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-muted-foreground truncate">{{ __('Unread') }}</p>
                                <p class="text-xl font-bold tabular-nums mt-1 text-warning-600">{{ number_format($contactStats['unread']) }}</p>
                                <p class="text-xs text-muted-foreground mt-1 truncate">{{ __('Needs attention') }}</p>
                            </div>
                            <span class="rounded-full size-10 flex items-center justify-center bg-warning-500/10 text-warning-600 shrink-0">
                                <i class="ki-filled ki-notification-bing text-lg"></i>
                            </span>
                        </div>
                    </a>
                    <a href="{{ route('admin.contact-messages.index', ['read' => 'read']) }}" class="kt-card p-3 cm-pulse-stat-card">
                        <div class="flex items-center justify-between gap-2 min-w-0">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-muted-foreground truncate">{{ __('Read') }}</p>
                                <p class="text-xl font-bold tabular-nums mt-1 text-emerald-600">{{ number_format($contactStats['read']) }}</p>
                                <p class="text-xs text-muted-foreground mt-1 truncate">{{ __('Marked as read') }}</p>
                            </div>
                            <span class="rounded-full size-10 flex items-center justify-center bg-emerald-500/10 text-emerald-600 shrink-0">
                                <i class="ki-filled ki-check-circle text-lg"></i>
                            </span>
                        </div>
                    </a>
                    <div class="kt-card p-3 cm-pulse-stat-card">
                        <div class="flex items-center justify-between gap-2 min-w-0">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-muted-foreground truncate">{{ __('Last 7 days') }}</p>
                                <p class="text-xl font-bold tabular-nums mt-1">{{ number_format($contactStats['last_7_days']) }}</p>
                                <p class="text-xs text-muted-foreground mt-1 truncate">{{ __('New submissions') }}</p>
                            </div>
                            <span class="rounded-full size-10 flex items-center justify-center bg-blue-500/10 text-blue-600 shrink-0">
                                <i class="ki-filled ki-calendar-tick text-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($messages->isEmpty() && $hasListFilters && $totalContactMessages > 0)
            <div class="rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-amber-900 dark:text-amber-100 text-sm">
                <p class="font-medium">{{ __('No rows match your current filters.') }}</p>
                <p class="mt-1 text-amber-800/90 dark:text-amber-200/90">
                    There {{ $totalContactMessages === 1 ? 'is' : 'are' }} <strong>{{ $totalContactMessages }}</strong> message(s) saved, but none match this search or read/unread filter.
                    <a href="{{ route('admin.contact-messages.index') }}" class="text-primary font-medium hover:underline ml-1">{{ __('Show all messages') }}</a>
                </p>
            </div>
        @endif

        @if (session('success'))
            <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-green-800 dark:text-green-200">{{ session('success') }}</div>
        @endif

        <div class="admin-pulse-frame shrink-0 min-w-0 max-w-full">
            <div class="admin-pulse-card-inner admin-pulse-create min-w-0 max-w-full overflow-x-auto">
                <h2 class="admin-pulse-section-title mb-4">{{ __('Search & filter') }}</h2>
                <form method="get" class="flex flex-row flex-nowrap items-end gap-3 w-full min-w-0">
                    <div class="flex flex-col gap-1.5 flex-1 min-w-[12rem]">
                        <label for="cm_contact_search">{{ __('Search') }}</label>
                        <input
                            id="cm_contact_search"
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('Name, email, phone or message') }}"
                            class="kt-input w-full min-w-0"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5 shrink-0 w-[160px]">
                        <label for="cm_contact_read">{{ __('Read status') }}</label>
                        <select id="cm_contact_read" name="read" class="kt-select w-full" style="width: 160px;">
                            <option value="">{{ __('All') }}</option>
                            <option value="unread" {{ request('read') === 'unread' ? 'selected' : '' }}>{{ __('Unread') }}</option>
                            <option value="read" {{ request('read') === 'read' ? 'selected' : '' }}>{{ __('Read') }}</option>
                        </select>
                    </div>
                    <div class="flex items-end shrink-0 pt-0">
                        <button type="submit" class="admin-pulse-btn-primary">{{ __('Filter') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="admin-pulse-frame cm-pulse-inner-table min-w-0 max-w-full overflow-hidden">
            <div class="admin-pulse-card-inner min-w-0 max-w-full overflow-hidden pb-3">
                <div class="px-5 sm:px-6 mb-4">
                    <h2 class="admin-pulse-section-title mb-0">{{ __('Messages') }}</h2>
                </div>
                <div class="mx-4 sm:mx-6 cm-pulse-table-surface">
                    {{-- Desktop / tablet table --}}
                    <div class="kt-scrollable-x-auto hidden md:block">
                        <table class="kt-table table-auto kt-table-border">
                            <thead>
                                <tr>
                                    <th class="min-w-[140px]">{{ __('Name') }}</th>
                                    <th class="min-w-[180px]">{{ __('Email') }}</th>
                                    <th class="min-w-[120px]">{{ __('Phone') }}</th>
                                    <th class="min-w-[200px]">{{ __('Message') }}</th>
                                    <th class="min-w-[120px]">{{ __('Date') }}</th>
                                    <th class="min-w-[80px]">{{ __('Read') }}</th>
                                    <th class="w-24">{{ __('Actions') }}</th>
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
                                            <span class="kt-badge kt-badge-sm kt-badge-success">{{ __('Read') }}</span>
                                        @else
                                            <span class="kt-badge kt-badge-sm kt-badge-warning">{{ __('New') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="admin-pulse-btn-outline-sm"
                                            data-famledger-contact-message-modal-url="{{ route('admin.contact-messages.modal', $msg, false) }}"
                                        >{{ __('View') }}</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted-foreground py-8">
                                        @if ($hasListFilters && $totalContactMessages > 0)
                                            {{ __('No messages match these filters.') }}
                                        @elseif ($totalContactMessages === 0)
                                            {{ __('No contact messages yet. Submit the form on the public landing page (Talk to the FamLedger team).') }}
                                        @else
                                            {{ __('No messages on this page.') }}
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
                            <div class="cm-pulse-mobile-card px-5 py-4 flex flex-col gap-3 {{ $msg->read_at ? '' : 'cm-pulse-mobile-card--new' }}">
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
                                            <span class="kt-badge kt-badge-sm kt-badge-success">{{ __('Read') }}</span>
                                        @else
                                            <span class="kt-badge kt-badge-sm kt-badge-warning">{{ __('New') }}</span>
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
                                        class="admin-pulse-btn-outline-sm"
                                        data-famledger-contact-message-modal-url="{{ route('admin.contact-messages.modal', $msg, false) }}"
                                    >{{ __('View') }}</button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted-foreground py-8 text-sm">
                                @if ($hasListFilters && $totalContactMessages > 0)
                                    {{ __('No messages match these filters.') }}
                                @elseif ($totalContactMessages === 0)
                                    {{ __('No contact messages yet. Use the landing page contact form to create one.') }}
                                @else
                                    {{ __('No messages on this page.') }}
                                @endif
                            </div>
                        @endforelse
                    </div>

                    <div class="px-4 py-3 border-t border-border">{{ $messages->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
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
