@extends('layouts.metronic')

@section('title', __('Audit log'))
@section('page_title', __('Audit log'))

@push('styles')
<style>
    .admin-pulse-page.audit-pulse-page {
        --ap-accent: #009ef7;
        --ap-accent-2: #0ea5e9;
        --ap-soft: #f0f9ff;
        --ap-ring: rgba(0, 158, 247, 0.28);
    }
    .audit-pulse-page .admin-pulse-frame {
        max-width: none;
        margin-left: 0;
        margin-right: 0;
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
    .admin-pulse-hint {
        font-size: 0.75rem;
        line-height: 1.45;
        color: #64748b;
    }
    .dark .admin-pulse-hint {
        color: #94a3b8;
    }
    .admin-pulse-create .kt-form-label,
    .admin-pulse-create label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #475569;
    }
    .dark .admin-pulse-create .kt-form-label,
    .dark .admin-pulse-create label {
        color: #94a3b8;
    }
    /* Native <select>: layout + chevron in public/css/famledger-native-select.css — use background-color only here. */
    .admin-pulse-create .kt-input,
    .admin-pulse-create .kt-textarea {
        width: 100%;
        padding: 0.8rem 1rem;
        font-size: 0.9375rem;
        border-radius: 12px;
        background: var(--ap-soft) !important;
        border: 1px solid transparent !important;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
    }
    /* Native <select>: layout + chevron in famledger-native-select.css; colors only here. */
    .admin-pulse-create select.kt-select {
        width: 100%;
        font-size: 0.9375rem;
        border-radius: 12px;
        border: 1px solid transparent !important;
        background-color: var(--ap-soft) !important;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
    }
    .admin-pulse-create .kt-input:hover,
    .admin-pulse-create .kt-textarea:hover {
        background: #e0f2fe !important;
    }
    .admin-pulse-create select.kt-select:hover {
        background-color: #e0f2fe !important;
    }
    .admin-pulse-create .kt-input:focus,
    .admin-pulse-create .kt-textarea:focus {
        outline: none;
        border-color: var(--ap-accent) !important;
        box-shadow: 0 0 0 3px var(--ap-ring) !important;
        background: #fff !important;
    }
    .admin-pulse-create select.kt-select:focus {
        outline: none;
        border-color: var(--ap-accent) !important;
        box-shadow: 0 0 0 3px var(--ap-ring) !important;
        background-color: #fff !important;
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
    .admin-pulse-link-secondary {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--ap-accent);
    }
    .admin-pulse-link-secondary:hover {
        color: var(--ap-accent-2);
        text-decoration: underline;
    }
    .audit-pulse-page .audit-summary-card {
        border-radius: 12px;
        border: 1px solid rgba(14, 165, 233, 0.18);
        background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .audit-pulse-page .audit-summary-card:hover {
        border-color: rgba(0, 158, 247, 0.35);
        box-shadow: 0 8px 24px rgba(0, 158, 247, 0.1);
        transform: translateY(-1px);
    }
    .dark .audit-pulse-page .audit-summary-card {
        background: linear-gradient(180deg, rgb(30 41 59 / 0.9) 0%, rgb(15 23 42 / 0.95) 100%);
        border-color: rgba(14, 165, 233, 0.22);
    }
    .audit-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .audit-filter-field {
        flex: 1 1 100%;
    }
    @media (min-width: 900px) {
        .audit-filter-field {
            flex: 0 0 calc(25% - 12px);
            max-width: calc(25% - 12px);
        }
    }
    .audit-filter-card {
        position: relative;
        z-index: 10;
        overflow: visible;
    }
    .audit-log-table-wrap {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .audit-log-table {
        width: 100%;
        table-layout: fixed;
    }
    .audit-log-table th,
    .audit-log-table td {
        vertical-align: top;
    }
    .audit-log-col-desc {
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .audit-pulse-inner-table .admin-pulse-card-inner {
        padding-left: 0;
        padding-right: 0;
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-pulse-btn-primary:hover,
        .audit-pulse-page .audit-summary-card:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="audit-log-page audit-pulse-page admin-pulse-page min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0">
            <div class="min-w-0 flex-1">
                <p class="admin-pulse-eyebrow mb-1.5">{{ __('Compliance') }}</p>
                <h1 class="admin-pulse-title">{{ __('Audit log') }}</h1>
                <div class="admin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('settings.index') }}">{{ __('Settings') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium">{{ __('Audit log') }}</span>
                </div>
            </div>
            <div class="shrink-0 ms-auto">
                <x-fin-back-link href="{{ route('settings.index') }}" class="!mb-0">{{ __('Back to settings') }}</x-fin-back-link>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14 min-w-0 max-w-full audit-main-stack">
        {{-- Summary metrics --}}
        <div class="admin-pulse-frame shrink-0 min-w-0 max-w-full">
            <div class="admin-pulse-card-inner min-w-0 max-w-full overflow-hidden">
                <h2 class="admin-pulse-section-title mb-1">{{ __('Summary (last 30 days)') }}</h2>
                <p class="admin-pulse-hint mb-5">{{ __('Placeholder metrics — click a tile for details.') }}</p>
                <div class="audit-summary-content min-w-0">
                    <div class="audit-summary-grid min-w-0">
                        <button
                            type="button"
                            class="audit-summary-card min-w-0 flex flex-col gap-1 text-left cursor-pointer p-3.5"
                            data-title="{{ __('Total events') }}"
                            data-value="128"
                            data-description="{{ __('Total number of audit events recorded across the selected scope in the last 30 days.') }}"
                        >
                            <span class="text-[11px] text-secondary-foreground uppercase tracking-wide">
                                {{ __('Total events') }}
                            </span>
                            <span class="text-sm font-semibold text-mono" style="color: var(--ap-accent);">
                                128
                            </span>
                        </button>
                        <button
                            type="button"
                            class="audit-summary-card min-w-0 flex flex-col gap-1 text-left cursor-pointer p-3.5"
                            data-title="{{ __('Critical changes') }}"
                            data-value="4"
                            data-description="{{ __('Important configuration and permission changes that may require review.') }}"
                        >
                            <span class="text-[11px] text-secondary-foreground uppercase tracking-wide">
                                {{ __('Critical changes') }}
                            </span>
                            <span class="text-sm font-semibold text-destructive">
                                4
                            </span>
                        </button>
                        <button
                            type="button"
                            class="audit-summary-card min-w-0 flex flex-col gap-1 text-left cursor-pointer p-3.5"
                            data-title="{{ __('Member updates') }}"
                            data-value="9"
                            data-description="{{ __('Invitations, role changes and member removals performed in the last 30 days.') }}"
                        >
                            <span class="text-[11px] text-secondary-foreground uppercase tracking-wide">
                                {{ __('Member updates') }}
                            </span>
                            <span class="text-sm font-semibold text-mono" style="color: var(--ap-accent);">
                                9
                            </span>
                        </button>
                        <button
                            type="button"
                            class="audit-summary-card min-w-0 flex flex-col gap-1 text-left cursor-pointer p-3.5"
                            data-title="{{ __('Budget & wallet changes') }}"
                            data-value="21"
                            data-description="{{ __('Budget adjustments, wallet openings and key balance changes over the last 30 days.') }}"
                        >
                            <span class="text-[11px] text-secondary-foreground uppercase tracking-wide">
                                {{ __('Budget & wallet changes') }}
                            </span>
                            <span class="text-sm font-semibold text-mono" style="color: var(--ap-accent);">
                                21
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="audit-col-stack min-w-0 max-w-full">
                    {{-- Filters --}}
                    <div class="admin-pulse-frame audit-filter-card">
                        <div class="admin-pulse-card-inner">
                            <h2 class="admin-pulse-section-title mb-5">{{ __('Filter activity') }}</h2>
                            <form method="get" action="{{ route('settings.audit-log') }}" class="audit-filter-row admin-pulse-create">
                                <div class="flex flex-col gap-1.5 audit-filter-field" style="min-width: 180px;">
                                    <label for="audit_scope">{{ __('Scope') }}</label>
                                    <select id="audit_scope" name="family_id" class="kt-select w-full">
                                        <option value="">{{ __('Whole system') }}</option>
                                        @foreach($families ?? [] as $f)
                                            <option value="{{ $f->id }}" {{ request('family_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1.5 audit-filter-field">
                                    <label for="audit_from">{{ __('From') }}</label>
                                    <input id="audit_from" type="date" name="from" value="{{ request('from') }}" class="kt-input w-full" />
                                </div>
                                <div class="flex flex-col gap-1.5 audit-filter-field">
                                    <label for="audit_to">{{ __('To') }}</label>
                                    <input id="audit_to" type="date" name="to" value="{{ request('to') }}" class="kt-input w-full" />
                                </div>
                                <div class="flex flex-col gap-1.5 audit-filter-field">
                                    <label for="audit_type">{{ __('Type') }}</label>
                                    <select id="audit_type" name="type" class="kt-select w-full">
                                        <option value="">{{ __('All') }}</option>
                                        <option value="application" {{ request('type') === 'application' ? 'selected' : '' }}>{{ __('Application') }}</option>
                                        <option value="database" {{ request('type') === 'database' ? 'selected' : '' }}>{{ __('Database') }}</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1.5 audit-filter-field flex items-end">
                                    <button type="submit" class="admin-pulse-btn-primary w-full sm:w-auto">{{ __('Apply') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

            {{-- Audit table --}}
                    <div class="admin-pulse-frame audit-pulse-inner-table min-w-0 max-w-full overflow-hidden">
                        <div class="admin-pulse-card-inner min-w-0 max-w-full overflow-hidden pb-3">
                            <div class="px-5 sm:px-6 flex flex-col sm:flex-row sm:items-center gap-3 w-full min-w-0 mb-4">
                                <h2 class="admin-pulse-section-title text-sm sm:text-base mb-0 min-w-0 sm:flex-1">
                                    @if(request('family_id') && isset($families))
                                        @php $selectedFamily = $families->firstWhere('id', (int) request('family_id')); @endphp
                                        {{ $selectedFamily ? __('Recent activity') . ' – ' . $selectedFamily->name : __('Recent activity (platform-wide)') }}
                                    @else
                                        {{ __('Recent activity (platform-wide)') }}
                                    @endif
                                </h2>
                                <div class="flex items-center gap-2 flex-wrap shrink-0 ms-auto">
                                    <x-famledger.export-pdf-button :href="route('settings.audit-log.export-pdf', array_filter(request()->only(['family_id', 'type', 'from', 'to'])))" />
                                    <a
                                        href="{{ route('settings.audit-log.export-csv', array_filter(request()->only(['family_id', 'type', 'from', 'to']))) }}"
                                        class="admin-pulse-btn-outline-sm inline-flex items-center gap-1"
                                    >
                                        <i class="ki-filled ki-notification-status text-sm"></i>
                                        {{ __('Export CSV') }}
                                    </a>
                                </div>
                            </div>
                            <div class="audit-log-table-wrap kt-scrollable-x-auto hidden md:block">
                                <table class="audit-log-table kt-table align-middle text-xs text-secondary-foreground">
                                    <thead>
                                        <tr class="bg-accent/40">
                                            <th class="px-4 py-2 text-start font-medium w-[11%]">{{ __('When') }}</th>
                                            <th class="px-4 py-2 text-start font-medium w-[14%]">{{ __('Who') }}</th>
                                            <th class="px-4 py-2 text-start font-medium w-[10%]">{{ __('Family') }}</th>
                                            <th class="px-4 py-2 text-start font-medium w-[9%]">{{ __('Type') }}</th>
                                            <th class="px-4 py-2 text-start font-medium w-[11%]">{{ __('Area') }}</th>
                                            <th class="px-4 py-2 text-start font-medium w-[35%]">{{ __('Action / Description') }}</th>
                                            <th class="px-4 py-2 text-end font-medium w-[10%] whitespace-nowrap">{{ __('IP') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $log)
                                            <tr class="border-b border-border last:border-0 hover:bg-accent/20">
                                                <td class="px-4 py-2 align-top">
                                                    <div class="flex flex-col">
                                                        <span class="font-medium text-mono text-xs">{{ $log->created_at->format('Y-m-d · H:i') }}</span>
                                                        <span class="text-[11px] text-muted-foreground">{{ $log->created_at->diffForHumans() }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2 align-top">
                                                    @if($log->user)
                                                        <div class="flex flex-col">
                                                            <span class="text-xs font-medium text-mono">{{ $log->user->name }}</span>
                                                            <span class="text-[11px] text-muted-foreground">{{ $log->user->email }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-[11px] text-muted-foreground">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 align-top">
                                                    @if($log->family)
                                                        <span class="text-xs text-muted-foreground">{{ $log->family->name }}</span>
                                                    @else
                                                        <span class="text-[11px] text-muted-foreground">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="kt-badge kt-badge-xs {{ $log->type === 'database' ? 'kt-badge-outline kt-badge-primary' : 'kt-badge-outline kt-badge-secondary' }}">
                                                        {{ $log->type === 'database' ? __('Database') : __('Application') }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-primary">{{ $log->area }}</span>
                                                </td>
                                                <td class="px-4 py-2 align-top audit-log-col-desc">
                                                    <span class="text-xs font-medium text-foreground">{{ $log->action }}</span>
                                                    <span class="text-[11px] text-muted-foreground block mt-0.5">{{ $log->description ?? '—' }}</span>
                                                </td>
                                                <td class="px-4 py-2 align-top text-end whitespace-nowrap">
                                                    <span class="text-[11px] text-muted-foreground font-mono">{{ $log->ip ?? '—' }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground text-sm">{{ __('No audit entries yet.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Mobile cards (demo) --}}
                            <div class="md:hidden px-4 space-y-3 text-xs text-secondary-foreground">
                                <div class="audit-summary-card rounded-xl p-3 flex flex-col gap-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-medium text-mono text-[11px]">
                                                {{ __('Today · 09:14') }}
                                            </div>
                                            <div class="text-[11px] text-muted-foreground">
                                                {{ __('2 minutes ago') }}
                                            </div>
                                        </div>
                                        <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-primary">
                                            {{ __('Budgets') }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-muted-foreground">
                                        <span class="font-semibold text-foreground">{{ __('You') }}</span>
                                        <span class="mx-1">·</span>
                                        <span>{{ auth()->user()->email ?? 'user@example.com' }}</span>
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs font-medium text-foreground">
                                            {{ __('Updated monthly grocery budget') }}
                                        </span>
                                        <span class="text-[11px] text-muted-foreground">
                                            {{ __('Limit changed from 450,000 to 500,000 · Wallet: Household · Currency: TZS') }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-muted-foreground flex justify-between">
                                        <span>{{ __('IP: 192.168.0.10') }}</span>
                                    </div>
                                </div>

                                <div class="audit-summary-card rounded-xl p-3 flex flex-col gap-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-medium text-mono text-[11px]">
                                                {{ __('Yesterday · 21:03') }}
                                            </div>
                                            <div class="text-[11px] text-muted-foreground">
                                                {{ __('1 day ago') }}
                                            </div>
                                        </div>
                                        <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-secondary">
                                            {{ __('Members & roles') }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-muted-foreground">
                                        <span class="font-semibold text-foreground">{{ __('Family member') }}</span>
                                        <span class="mx-1">·</span>
                                        <span>{{ __('Invited user') }}</span>
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs font-medium text-foreground">
                                            {{ __('Changed role from Viewer to Owner') }}
                                        </span>
                                        <span class="text-[11px] text-muted-foreground">
                                            {{ __('Ownership transferred · Family: Main household') }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-muted-foreground flex justify-between">
                                        <span>{{ __('IP: 102.89.14.23') }}</span>
                                    </div>
                                </div>

                                <div class="audit-summary-card rounded-xl p-3 flex flex-col gap-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-medium text-mono text-[11px]">
                                                {{ __('2 days ago · 08:27') }}
                                            </div>
                                            <div class="text-[11px] text-muted-foreground">
                                                {{ __('2 days ago') }}
                                            </div>
                                        </div>
                                        <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-success">
                                            {{ __('Security') }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-muted-foreground">
                                        <span class="font-semibold text-foreground">{{ __('You') }}</span>
                                        <span class="mx-1">·</span>
                                        <span>{{ __('Login') }}</span>
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs font-medium text-foreground">
                                            {{ __('Signed in to FamLedger') }}
                                        </span>
                                        <span class="text-[11px] text-muted-foreground">
                                            {{ __('2FA verified via email code · Device: Chrome · Windows') }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-muted-foreground flex justify-between">
                                        <span>{{ __('IP: 41.59.192.5') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if(isset($logs) && $logs->hasPages())
                                <div class="px-5 sm:px-6 flex flex-col sm:flex-row justify-between items-center flex-wrap gap-3 pt-4 mt-2 border-t border-sky-100/80 dark:border-slate-600/50">
                                    <span class="text-xs text-muted-foreground">{{ __('Showing') }} {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} {{ __('of') }} {{ $logs->total() }}</span>
                                    <div class="flex items-center gap-2">
                                        @if($logs->onFirstPage())
                                            <span class="kt-btn kt-btn-xs kt-btn-ghost opacity-50">{{ __('Previous') }}</span>
                                        @else
                                            <a href="{{ $logs->previousPageUrl() }}" class="admin-pulse-btn-outline-sm">{{ __('Previous') }}</a>
                                        @endif
                                        @if($logs->hasMorePages())
                                            <a href="{{ $logs->nextPageUrl() }}" class="admin-pulse-btn-outline-sm">{{ __('Next') }}</a>
                                        @else
                                            <span class="kt-btn kt-btn-xs kt-btn-ghost opacity-50">{{ __('Next') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
 document.addEventListener('DOMContentLoaded', function () {
     if (typeof Swal === 'undefined') {
         return;
     }

     var cards = document.querySelectorAll('.audit-summary-grid .audit-summary-card[data-title]');
     cards.forEach(function (card) {
         card.addEventListener('click', function () {
             var title = card.getAttribute('data-title') || '';
             var value = card.getAttribute('data-value') || '';
             var description = card.getAttribute('data-description') || '';

             var html =
                 '<div style="text-align:left;font-size:13px;line-height:1.5;">' +
                     '<p style="margin-bottom:6px;"><strong>Count:</strong> ' + value + '</p>' +
                     (description
                         ? '<p style="font-size:12px;color:#64748b;margin:0;">' + description + '</p>'
                         : '') +
                 '</div>';

             Swal.fire({
                 title: title,
                 html: html,
                 icon: 'info',
                 confirmButtonText: '{{ __('Close') }}',
                 customClass: {
                     popup: 'swal2-rounded',
                     title: 'text-sm font-semibold text-foreground',
                     confirmButton: 'kt-btn kt-btn-sm kt-btn-primary'
                 }
             });
         });
     });
 });
</script>
@endpush
