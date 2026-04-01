@extends('layouts.metronic')

@section('title', $family->name.' — '.__('Family profile'))
@section('page_title', $family->name)

@section('content')
<div class="min-w-0 w-full max-w-full overflow-x-hidden">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="fin-pulse-eyebrow mb-1.5">{{ __('Family') }}</p>
                <h1 class="fin-pulse-title truncate">{{ $family->name }}</h1>
                <div class="fin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('families.index') }}">{{ __('Families') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium truncate">{{ __('Profile') }}</span>
                </div>
            </div>
            <div class="shrink-0 w-full sm:w-auto sm:ms-auto flex flex-wrap items-center justify-end gap-2 max-w-full">
                <a href="{{ route('families.wallets.index') }}" class="kt-btn kt-btn-primary kt-btn-sm">
                    <i class="ki-filled ki-wallet"></i>
                    {{ __('Wallets') }}
                </a>
                <a href="{{ route('families.projects.index') }}" class="kt-btn kt-btn-primary kt-btn-sm">
                    <i class="ki-filled ki-briefcase"></i>
                    {{ __('Projects') }}
                </a>
                <a href="{{ route('families.edit', $family) }}" class="kt-btn kt-btn-light kt-btn-sm border border-border">
                    <i class="ki-filled ki-pencil"></i>
                    {{ __('Edit family') }}
                </a>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-success/15 bg-success/10 px-4 py-3 text-sm text-foreground" role="status">
            {{ session('success') }}
        </div>
    @endif
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
    .famledger-family-profile-tabs .nav-link {
        border: none !important;
        border-radius: 0.5rem 0.5rem 0 0;
        color: var(--tw-muted-foreground, #64748b);
        font-weight: 600;
        font-size: 0.8125rem;
        padding: 0.75rem 1rem;
        white-space: nowrap;
    }
    @media (min-width: 640px) {
        .famledger-family-profile-tabs .nav-link {
            font-size: 0.875rem;
            padding: 0.875rem 1.25rem;
        }
    }
    .famledger-family-profile-tabs .nav-link:hover {
        color: var(--tw-foreground, #0f172a);
        background: color-mix(in srgb, var(--tw-muted, #f1f5f9) 50%, transparent);
    }
    .famledger-family-profile-tabs .nav-link.active {
        color: var(--tw-primary, #2563eb) !important;
        background: var(--tw-card, #fff);
        border-bottom: 2px solid var(--tw-primary, #2563eb) !important;
    }
    .famledger-family-profile-tabs .tab-content {
        min-height: 12rem;
    }
    /* Only one tab panel visible (theme CSS sometimes shows all .tab-pane) */
    .famledger-family-profile-tabs .tab-content > .tab-pane {
        display: none !important;
    }
    .famledger-family-profile-tabs .tab-content > .tab-pane.active {
        display: block !important;
    }
    /* Force horizontal tab row (Metronic / theme may stack .nav vertically) */
    .famledger-family-profile-tabs ul.nav.nav-tabs {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: flex-end !important;
        justify-content: flex-start !important;
        width: 100%;
        gap: 0.125rem;
    }
    .famledger-family-profile-tabs ul.nav.nav-tabs .nav-item {
        flex: 0 0 auto;
    }
    .famledger-family-profile-tabs ul.nav.nav-tabs .nav-item .nav-link {
        width: auto;
        display: inline-flex;
        align-items: center;
    }
    @media (max-width: 639px) {
        .famledger-family-profile-tabs ul.nav.nav-tabs {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
    }
    /* Tab strip above panel content (Metronic has no Bootstrap JS for data-bs-toggle="tab") */
    .famledger-family-profile-tabs > .border-b {
        position: relative;
        z-index: 2;
    }
    .famledger-family-profile-tabs .tab-content {
        position: relative;
        z-index: 1;
    }
    /* Financial overview: soften Metronic card chrome */
    .famledger-fin-overview .fin-soft-card {
        border: none !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .dark .famledger-fin-overview .fin-soft-card {
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
    }
    /* Force soft edges + generous padding (theme often adds heavy borders to .rounded-2xl cards) */
    .famledger-fin-overview .fin-stat-tile {
        border: none !important;
        outline: none !important;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.05),
            0 0 0 1px rgba(148, 163, 184, 0.22);
        padding: 1.25rem 1.375rem !important;
    }
    @media (min-width: 640px) {
        .famledger-fin-overview .fin-stat-tile {
            padding: 1.375rem 1.5rem !important;
        }
    }
    .dark .famledger-fin-overview .fin-stat-tile {
        box-shadow:
            0 1px 2px rgba(0, 0, 0, 0.2),
            0 0 0 1px rgba(255, 255, 255, 0.08);
    }
    .famledger-fin-overview .fin-curr-btn {
        border: none !important;
        outline: none !important;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.06),
            0 0 0 1px rgba(148, 163, 184, 0.25);
    }
    .dark .famledger-fin-overview .fin-curr-btn {
        box-shadow:
            0 1px 2px rgba(0, 0, 0, 0.25),
            0 0 0 1px rgba(255, 255, 255, 0.1);
    }
    /* Extra inset inside Financial tab so copy never hugs the tab/card edges */
    .famledger-fin-overview .fin-overview-stack {
        padding: 0.75rem 1rem 1.25rem;
    }
    @media (min-width: 640px) {
        .famledger-fin-overview .fin-overview-stack {
            padding: 1rem 1.5rem 1.5rem;
        }
    }
    @media (min-width: 1024px) {
        .famledger-fin-overview .fin-overview-stack {
            padding: 1.125rem 1.75rem 1.75rem;
        }
    }
    .famledger-fin-overview .fin-ledger-panel {
        padding: 1.25rem 1.375rem !important;
        border: none !important;
        outline: none !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    @media (min-width: 640px) {
        .famledger-fin-overview .fin-ledger-panel {
            padding: 1.5rem 1.75rem !important;
        }
    }
    @media (min-width: 1024px) {
        .famledger-fin-overview .fin-ledger-panel {
            padding: 1.625rem 2rem !important;
        }
    }
    .dark .famledger-fin-overview .fin-ledger-panel {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
    }
    .famledger-fin-overview .fin-totals-lede {
        padding: 0.75rem 1rem 0.5rem !important;
    }
    @media (min-width: 640px) {
        .famledger-fin-overview .fin-totals-lede {
            padding: 0.875rem 1.25rem 0.625rem !important;
        }
    }
    .famledger-fin-overview .fin-contrib-card {
        border-radius: 0.75rem;
        border: none !important;
        outline: none !important;
        background: var(--tw-card, #fff);
        box-shadow:
            0 4px 6px -1px rgb(15 23 42 / 0.06),
            0 2px 4px -2px rgb(15 23 42 / 0.04);
    }
    .dark .famledger-fin-overview .fin-contrib-card {
        box-shadow:
            0 4px 6px -1px rgb(0 0 0 / 0.35),
            0 2px 4px -2px rgb(0 0 0 / 0.2);
    }
    .famledger-fin-overview .fin-contrib-row:hover {
        background: color-mix(in srgb, var(--tw-muted, #f1f5f9) 52%, transparent);
    }
    .dark .famledger-fin-overview .fin-contrib-row:hover {
        background: rgba(255, 255, 255, 0.04);
    }
    </style>

    <div class="famledger-family-profile-tabs kt-card rounded-2xl border border-border/80 shadow-sm overflow-hidden mb-6">
        <div class="border-b border-border bg-muted/15 px-2 sm:px-4">
            <ul class="nav nav-tabs border-0 -mb-px" role="tablist" id="family_profile_tabs" aria-label="{{ __('Family profile sections') }}">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="fam_tab_details" data-bs-toggle="tab" data-bs-target="#fam_panel_details" type="button" role="tab" aria-controls="fam_panel_details" aria-selected="true">
                        <i class="ki-filled ki-home-2 me-1.5 text-base opacity-80"></i>{{ __('Family details') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fam_tab_finance" data-bs-toggle="tab" data-bs-target="#fam_panel_finance" type="button" role="tab" aria-controls="fam_panel_finance" aria-selected="false">
                        <i class="ki-filled ki-chart-line me-1.5 text-base opacity-80"></i>{{ __('Financial overview') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fam_tab_highlights" data-bs-toggle="tab" data-bs-target="#fam_panel_highlights" type="button" role="tab" aria-controls="fam_panel_highlights" aria-selected="false">
                        <i class="ki-filled ki-star me-1.5 text-base opacity-80"></i>{{ __('Family highlights') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fam_tab_members" data-bs-toggle="tab" data-bs-target="#fam_panel_members" type="button" role="tab" aria-controls="fam_panel_members" aria-selected="false">
                        <i class="ki-filled ki-people me-1.5 text-base opacity-80"></i>{{ __('Members') }}
                        <span class="ms-1 text-xs font-normal opacity-70">({{ $family->familyMembers->count() }})</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-4 sm:p-6 lg:p-8 bg-card">
    {{-- TAB: Family details --}}
    <div class="tab-pane fade show active" id="fam_panel_details" role="tabpanel" aria-labelledby="fam_tab_details" tabindex="0">
    {{-- Summary / hero --}}
    <div class="kt-card mb-6 rounded-2xl border border-border/80 bg-gradient-to-br from-card via-card to-muted/25 shadow-sm overflow-visible">
        <div class="kt-card-content p-5 sm:p-7 lg:p-9 overflow-visible">

            <div class="flex flex-col sm:flex-row items-start gap-5 sm:gap-7">

                <div class="shrink-0 self-start flex h-14 w-14 sm:h-[4.5rem] sm:w-[4.5rem] items-center justify-center rounded-2xl bg-gradient-to-br from-primary/20 via-primary/8 to-primary/5 text-primary shadow-[inset_0_1px_0_rgba(255,255,255,0.35)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] ring-1 ring-inset ring-primary/15">
                    <i class="ki-filled ki-people text-[1.65rem] sm:text-[2rem] leading-none opacity-95" aria-hidden="true"></i>
                </div>

                <div class="flex-1 min-w-0 flex flex-col gap-2.5 sm:gap-3 pt-0.5">
                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1.5">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-muted-foreground">{{ __('Overview') }}</span>
                        <span class="kt-badge {{ $family->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline rounded-[30px] shrink-0">
                            <span class="kt-badge-dot size-1.5"></span>
                            {{ ucfirst($family->status) }}
                        </span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-semibold tracking-tight text-foreground leading-snug">
                        {{ $family->name }}
                    </h2>

                    {{-- Description --}}
                    @if ($family->description)
                        <p class="text-secondary-foreground text-sm leading-relaxed max-w-2xl">{{ Str::limit($family->description, 220) }}</p>
                    @endif

                    @php $metaSep = false; @endphp
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted-foreground @if($family->description) mt-0.5 @endif">
                        @if ($family->creator)
                            <span class="inline-flex items-center gap-1">
                                <i class="ki-filled ki-user text-sm"></i>
                                {{ __('Created by') }} <span class="text-foreground font-medium ml-0.5">{{ $family->creator->name }}</span>
                            </span>
                            @php $metaSep = true; @endphp
                        @endif
                        @if ($family->created_at)
                            @if ($metaSep)<span class="text-border select-none" aria-hidden="true">·</span>@endif
                            <span class="inline-flex items-center gap-1">
                                <i class="ki-filled ki-calendar text-sm"></i>
                                {{ $family->created_at->format('M j, Y') }}
                            </span>
                            @php $metaSep = true; @endphp
                        @endif
                        @if($family->country)
                            @if ($metaSep)<span class="text-border select-none" aria-hidden="true">·</span>@endif
                            <span class="inline-flex items-center gap-1">
                                <i class="ki-filled ki-geolocation text-sm"></i>
                                {{ $family->country }}
                            </span>
                            @php $metaSep = true; @endphp
                        @endif
                        @if($family->timezone)
                            @if ($metaSep)<span class="text-border select-none" aria-hidden="true">·</span>@endif
                            <span>{{ $family->timezone }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-sm font-semibold text-foreground mb-4">{{ __('Details') }}</h2>
        <div class="kt-card rounded-xl border border-border/80 shadow-sm">
            <div class="kt-card-content p-4 sm:p-6">
                <dl class="family-details-grid text-sm">
                    <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                        <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Timezone') }}</dt>
                        <dd class="text-foreground">{{ $family->timezone }}</dd>
                    </div>
                    <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                        <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Country') }}</dt>
                        <dd class="text-foreground">{{ $family->country ?: '—' }}</dd>
                    </div>
                    @if ($family->creator)
                    <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                        <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Owner') }}</dt>
                        <dd class="text-foreground">{{ $family->creator->name }}</dd>
                    </div>
                    @endif
                    @if ($family->created_at)
                    <div class="family-details-item flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                        <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Created') }}</dt>
                        <dd class="text-foreground">{{ $family->created_at->format('M j, Y') }}</dd>
                    </div>
                    @endif
                    <div class="flex flex-col gap-0.5 p-3 rounded-lg border border-border bg-muted/40">
                        <dt class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Status') }}</dt>
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
                        <h4 class="text-xs text-muted-foreground uppercase tracking-wide mb-1.5">{{ __('Full description') }}</h4>
                        <p class="text-sm text-foreground leading-relaxed">
                            {{ $family->description }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    </div>{{-- /fam_panel_details --}}

    <div class="tab-pane fade famledger-fin-overview" id="fam_panel_finance" role="tabpanel" aria-labelledby="fam_tab_finance" tabindex="0">
    <div class="fin-overview-stack space-y-6 sm:space-y-7">
    <div class="fin-ledger-panel rounded-2xl bg-muted/20">
    <div class="flex w-full flex-col gap-5 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
        <div class="min-w-0 flex-1 space-y-2">
            <h2 class="text-sm font-semibold text-foreground tracking-tight">{{ __('Ledger currency') }}</h2>
            <p class="text-xs text-muted-foreground leading-relaxed max-w-lg">{{ __('Financial figures and totals use this currency.') }}</p>
        </div>
        <div class="flex w-full shrink-0 flex-wrap items-center justify-end gap-3 sm:w-auto sm:gap-3.5 sm:pl-4">
            <span class="text-xs font-medium text-muted-foreground">{{ __('Currency') }}</span>
            @if($canManageMembers)
            <div class="relative">
                <button type="button" id="curr_btn"
                    class="fin-curr-btn flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-muted/45 hover:bg-muted/65 text-sm font-semibold text-foreground transition-colors">
                    <i class="ki-filled ki-finance-calculator text-xs text-muted-foreground"></i>
                    <span id="curr_label">{{ $family->currency_code }}</span>
                    <i class="ki-filled ki-down text-[10px] text-muted-foreground" id="curr_chevron"></i>
                </button>
            </div>
            <div id="curr_menu"
                 style="display:none; position:fixed; z-index:99999; width:224px; max-height:288px;
                        background:var(--color-card, #fff); border:1px solid var(--color-border,#e5e7eb);
                        border-radius:0.5rem; box-shadow:0 10px 25px -5px rgba(0,0,0,.18); overflow:hidden; flex-direction:column;">
                <div style="padding:8px 12px; border-bottom:1px solid var(--color-border,#e5e7eb); flex-shrink:0;">
                    <input type="text" id="curr_search" placeholder="Search currency…"
                        style="width:100%; font-size:12px; background:transparent; border:none; outline:none; color:inherit;"
                        autocomplete="off">
                </div>
                <ul id="curr_list" style="overflow-y:auto; flex:1; list-style:none; margin:0; padding:4px 0;">
                    @foreach($currencies as $code => $label)
                    <li data-lbl="{{ strtolower($label) }}">
                        <form method="POST" action="{{ route('families.currency.switch', $family) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="currency_code" value="{{ $code }}">
                            <button type="submit" style="display:flex; align-items:center; gap:6px; width:100%; text-align:left;
                                padding:7px 14px; font-size:13px; background:none; border:none; cursor:pointer;
                                color:{{ $family->currency_code === $code ? 'var(--color-primary,#2563eb)' : 'inherit' }};
                                font-weight:{{ $family->currency_code === $code ? '600' : '400' }};">
                                <span style="font-family:monospace; font-size:11px; opacity:.6; min-width:34px;">{{ $code }}</span>
                                {{ explode(' – ', $label)[1] ?? $label }}
                                @if($family->currency_code === $code)
                                <i class="ki-filled ki-check" style="margin-left:auto; font-size:12px;"></i>
                                @endif
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>
            @else
            <span class="fin-curr-btn inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-muted/45 text-sm font-semibold text-foreground">
                <i class="ki-filled ki-finance-calculator text-xs text-muted-foreground"></i>
                {{ $family->currency_code }}
            </span>
            @endif
        </div>
    </div>
    </div>

    {{-- Financial Summary & Health Index --}}
    <style>
        .family-fin-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            width: 100%;
            margin-bottom: 1.75rem;
        }
        @media (min-width: 768px) {
            .family-fin-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 1.125rem;
            }
        }
    </style>
    <div class="fin-totals-block space-y-4 sm:space-y-5" data-fam-currency-code="{{ $family->currency_code }}">
    <div class="family-fin-grid px-0.5 sm:px-1">
        <div class="fin-stat-tile rounded-2xl bg-gradient-to-b from-card to-muted/15 flex flex-col gap-2 min-w-0">
            <div class="flex items-center gap-2.5 text-xs font-medium text-muted-foreground uppercase tracking-wide pr-1">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600"><i class="ki-filled ki-arrow-up text-sm leading-none"></i></span>
                <span class="leading-snug">{{ __('Total income') }}</span>
            </div>
            <div id="fam-profile-total-income" class="text-lg font-semibold tabular-nums text-green-600 truncate pl-0.5">{{ $family->currency_code }} {{ number_format((float) $totalIncome, 2) }}</div>
        </div>
        <div class="fin-stat-tile rounded-2xl bg-gradient-to-b from-card to-muted/15 flex flex-col gap-2 min-w-0">
            <div class="flex items-center gap-2.5 text-xs font-medium text-muted-foreground uppercase tracking-wide pr-1">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600"><i class="ki-filled ki-arrow-down text-sm leading-none"></i></span>
                <span class="leading-snug">{{ __('Total expenses') }}</span>
            </div>
            <div id="fam-profile-total-expenses" class="text-lg font-semibold tabular-nums text-destructive truncate pl-0.5">{{ $family->currency_code }} {{ number_format((float) $totalExpenses, 2) }}</div>
        </div>
        <div class="fin-stat-tile rounded-2xl bg-gradient-to-b from-card to-muted/15 flex flex-col gap-2 min-w-0">
            <div class="flex items-center gap-2.5 text-xs font-medium text-muted-foreground uppercase tracking-wide pr-1">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600"><i class="ki-filled ki-wallet text-sm leading-none"></i></span>
                <span class="leading-snug">{{ __('Balance') }}</span>
            </div>
            <div id="fam-profile-balance" class="text-lg font-semibold tabular-nums {{ $balance >= 0 ? 'text-green-600' : 'text-destructive' }} truncate pl-0.5">{{ $family->currency_code }} {{ number_format((float) $balance, 2) }}</div>
        </div>
        <div class="fin-stat-tile rounded-2xl bg-gradient-to-b from-card to-muted/15 flex flex-col justify-center gap-2 min-w-0">
            <div class="flex items-center gap-2.5 text-xs font-medium text-muted-foreground uppercase tracking-wide pr-1">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-600"><i class="ki-filled ki-heart text-sm leading-none"></i></span>
                <span class="leading-snug">{{ __('Health index') }}</span>
            </div>
            <div id="family-health-index" class="text-base font-semibold transition-all duration-300 leading-snug">
                {{ $healthIndex['emoji'] }} {{ $healthIndex['text'] }}
            </div>
            <div class="text-[11px] text-muted-foreground leading-relaxed line-clamp-2 pt-0.5" id="family-health-desc">{{ $healthIndex['description'] }}</div>
        </div>
    </div>
    </div>

    {{-- Contributor leaderboard --}}
    @if(isset($leaderboard) && count($leaderboard) > 0)
    @php
        $contribPtsTotal = max(1, (int) collect($leaderboard)->sum('points'));
    @endphp
    <div class="fin-contrib-card overflow-hidden">
        <div class="bg-gradient-to-r from-primary/[0.06] via-transparent to-transparent px-5 py-5 sm:px-7 sm:py-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <h3 class="flex items-center gap-2.5 text-base font-semibold tracking-tight text-foreground sm:text-lg">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-amber-500/12 text-amber-600 dark:text-amber-400">
                            <i class="ki-filled ki-crown text-lg leading-none"></i>
                        </span>
                        {{ __('Top contributors') }}
                    </h3>
                </div>
                <div class="flex shrink-0 items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-800 dark:text-emerald-300/90">
                    <span class="size-2 shrink-0 rounded-full bg-emerald-500 shadow-[0_0_0_2px_rgba(16,185,129,0.25)]"></span>
                    {{ __('Live') }}
                </div>
            </div>
        </div>
        <ul class="space-y-1 px-2 pb-3 pt-1 sm:px-3 sm:pb-4">
            @foreach($leaderboard as $user)
            @php
                $contribPct = (int) round(($user['points'] / $contribPtsTotal) * 100);
                $rank = $loop->iteration;
                $rankClass = match ($rank) {
                    1 => 'bg-amber-500/15 text-amber-900 dark:text-amber-100',
                    2 => 'bg-slate-400/15 text-slate-800 dark:text-slate-100',
                    3 => 'bg-orange-500/12 text-orange-950 dark:text-orange-100',
                    default => 'bg-muted/90 text-muted-foreground',
                };
            @endphp
            <li class="fin-contrib-row rounded-lg">
                <div class="flex items-center gap-3.5 px-3 py-3.5 sm:gap-4 sm:px-5 sm:py-4">
                    <span class="{{ $rankClass }} flex size-9 shrink-0 items-center justify-center rounded-lg text-xs font-bold tabular-nums" aria-hidden="true">{{ $rank }}</span>
                    <div class="relative shrink-0">
                        @if($user['avatar'])
                            <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}" class="user-avatar size-12 rounded-full object-cover shadow-sm sm:size-14" data-id="{{ $user['id'] }}">
                        @else
                            <div class="user-avatar flex size-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary/30 to-primary/5 text-sm font-semibold text-primary shadow-sm sm:size-14 sm:text-base" data-id="{{ $user['id'] }}">
                                <span class="select-none">{{ strtoupper(substr($user['name'], 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1 space-y-2.5">
                        <div class="truncate text-sm font-semibold leading-snug text-foreground sm:text-[15px]">{{ explode(' ', $user['name'])[0] }}</div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                            <div class="h-2.5 min-w-0 flex-1 overflow-hidden rounded-full bg-muted/90 sm:max-w-xs">
                                <div class="h-full rounded-full bg-gradient-to-r from-primary to-primary/55 transition-all" style="width: {{ $contribPct }}%"></div>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-baseline gap-x-1.5 text-xs tabular-nums sm:text-[13px]">
                                <span class="user-points font-bold text-foreground" data-id="{{ $user['id'] }}">{{ $user['points'] }}</span>
                                <span class="text-muted-foreground">{{ __('pts') }}</span>
                                <span class="text-muted-foreground/80">·</span>
                                <span class="font-semibold text-muted-foreground">{{ $contribPct }}%</span>
                            </div>
                        </div>
                    </div>
                    @if($rank <= 3)
                    <span class="hidden shrink-0 sm:flex sm:items-center sm:justify-center" aria-hidden="true">
                        <i class="ki-filled ki-medal-star text-2xl {{ $rank === 1 ? 'text-amber-500' : ($rank === 2 ? 'text-slate-400' : 'text-orange-400') }}"></i>
                    </span>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    </div>{{-- /fin-overview-stack --}}
    </div>{{-- /fam_panel_finance --}}

    <div class="tab-pane fade" id="fam_panel_highlights" role="tabpanel" aria-labelledby="fam_tab_highlights" tabindex="0">
    <p class="text-xs text-muted-foreground mb-4">{{ __('Jump into memories, relationships, and goals.') }}</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6">
        <div class="col-span-1 kt-card border border-border rounded-xl shadow-sm">
            <div class="kt-card-header min-h-[50px] flex items-center justify-between px-5">
                <h3 class="kt-card-title text-base font-semibold text-foreground flex items-center gap-2">
                    <i class="ki-filled ki-watch text-primary"></i>
                    {{ __('Recent memories') }}
                </h3>
                <a href="{{ route('families.timeline.index') }}" class="text-xs font-semibold text-primary hover:text-primary/80">{{ __('View all') }}</a>
            </div>
            <div class="kt-card-content p-4 lg:p-5">
                @if(isset($recentMilestones) && $recentMilestones->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentMilestones->take(2) as $milestone)
                        <div class="relative pl-4 border-l-2 border-primary/20">
                            <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-primary ring-2 ring-background"></div>
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-foreground">{{ $milestone->title }}</h4>
                                <span class="text-[10px] text-muted-foreground">{{ $milestone->date->format('M d') }}</span>
                            </div>
                            @if($milestone->media_url)
                            <div class="mt-2 text-xs text-primary flex items-center gap-1"><i class="ki-filled ki-picture"></i> {{ __('Photo attached') }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 px-3 bg-muted/25 rounded-xl border border-dashed border-border text-xs text-muted-foreground">
                        {{ __('No memories yet.') }}<br>
                        <a href="{{ route('families.timeline.create') }}" class="text-primary font-medium hover:underline mt-2 inline-block">{{ __('Add a memory') }}</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-span-1 kt-card border border-border rounded-xl shadow-sm">
            <div class="kt-card-header min-h-[50px] flex items-center justify-between px-5">
                <h3 class="kt-card-title text-base font-semibold text-foreground flex items-center gap-2">
                    <i class="ki-filled ki-route text-primary"></i>
                    {{ __('Family tree') }}
                </h3>
                <a href="{{ route('families.tree.index') }}" class="text-xs font-semibold text-primary hover:text-primary/80">{{ __('Open tree') }}</a>
            </div>
            <div class="kt-card-content p-4 lg:p-5">
                @if(count($treePreview) > 0)
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary mb-1">{{ $family->familyMembers->count() }}</div>
                        <div class="text-xs text-muted-foreground">{{ __('Members') }}</div>
                        <div class="mt-3 text-xs text-foreground">
                            @if(count($treePreview) === 1)
                                {{ $treePreview[0]['name'] }}
                            @else
                                {{ count($treePreview) }} {{ __('roots') }}
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 px-3 bg-muted/25 rounded-xl border border-dashed border-border text-xs text-muted-foreground">
                        {{ __('No tree data yet.') }}<br>
                        <a href="{{ route('families.tree.index') }}" class="text-primary font-medium hover:underline mt-2 inline-block">{{ __('Add relationships') }}</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-span-1 kt-card border border-border rounded-xl shadow-sm">
            <div class="kt-card-header min-h-[50px] flex items-center justify-between px-5">
                <h3 class="kt-card-title text-base font-semibold text-foreground flex items-center gap-2">
                    <i class="ki-filled ki-compass text-primary"></i>
                    {{ __('Vision board') }}
                </h3>
                <a href="{{ route('families.goals.index') }}" class="text-xs font-semibold text-primary hover:text-primary/80">{{ __('View all') }}</a>
            </div>
            <div class="kt-card-content p-4 lg:p-5">
                @if(isset($activeGoals) && $activeGoals->count() > 0)
                    <div class="space-y-4">
                        @foreach($activeGoals->take(3) as $goal)
                        <div class="border border-border/70 rounded-xl p-3 bg-muted/20">
                            <h4 class="text-sm font-semibold text-foreground truncate mb-2">{{ $goal->title }}</h4>
                            <div class="w-full bg-secondary rounded-full h-2 overflow-hidden">
                                <div class="bg-primary h-2 rounded-full transition-all" style="width: {{ min(100, max(0, (int) $goal->progress)) }}%"></div>
                            </div>
                            <div class="text-[10px] text-muted-foreground flex justify-between mt-1.5">
                                <span>{{ __('Progress') }}</span>
                                <span>{{ number_format((int) $goal->progress) }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 px-3 bg-muted/25 rounded-xl border border-dashed border-border text-xs text-muted-foreground">
                        {{ __('No goals yet.') }}<br>
                        <a href="{{ route('families.goals.create') }}" class="text-primary font-medium hover:underline mt-2 inline-block">{{ __('Create a goal') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>{{-- /fam_panel_highlights --}}

    <div class="tab-pane fade" id="fam_panel_members" role="tabpanel" aria-labelledby="fam_tab_members" tabindex="0">
    <p class="text-xs text-muted-foreground mb-4">{{ __('Everyone with access to this family.') }}</p>
        <div class="w-full min-w-0" id="family-members">
            <div class="kt-card kt-card-grid min-w-full h-full rounded-xl border border-border/80 shadow-sm">
                <div class="kt-card-header flex-wrap gap-2">
                    <h3 class="kt-card-title text-sm font-semibold">{{ __('Members') }} ({{ $family->familyMembers->count() }})</h3>
                    @if ($canManageMembers ?? false)
                        <a href="{{ route('families.members.create') }}" class="kt-btn kt-btn-sm kt-btn-primary" title="{{ __('Owners and co-owners can add members') }}">
                            <i class="ki-filled ki-plus"></i>
                            {{ __('Add member') }}
                        </a>
                    @endif
                </div>
                <div class="kt-card-content">
                    @if ($family->familyMembers->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                            <span class="flex items-center justify-center size-12 rounded-full bg-muted text-muted-foreground mb-3">
                                <i class="ki-filled ki-people text-2xl"></i>
                            </span>
                            <p class="text-sm font-medium text-foreground">{{ __('No members yet') }}</p>
                            <p class="text-sm text-secondary-foreground mt-1">@if ($canManageMembers ?? false)<a href="{{ route('families.members.create') }}" class="text-primary hover:underline">{{ __('Add a member') }}</a>@else {{ __('Invitations are managed from family settings.') }}@endif</p>
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
                                                            <a class="kt-menu-link" href="{{ route('families.members.edit', $member) }}">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                                <span class="kt-menu-title">Edit</span>
                                                            </a>
                                                        </div>
                                                        <div class="kt-menu-item">
                                                            @if (! $member->is_primary)
                                                            <form action="{{ route('families.members.transfer-ownership', $member) }}" method="POST" class="inline-block w-full">
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
                                                            <form action="{{ route('families.members.deactivate', $member) }}" method="POST" class="inline-block w-full">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="kt-menu-link w-full text-start border-0 bg-transparent cursor-pointer">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-moon"></i></span>
                                                                    <span class="kt-menu-title">Deactivate member</span>
                                                                </button>
                                                            </form>
                                                            @else
                                                            <form action="{{ route('families.members.activate', $member) }}" method="POST" class="inline-block w-full">
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
                                                            <form action="{{ route('families.members.destroy', $member) }}" method="POST" class="js-confirm-delete inline-block w-full" data-confirm-title="Delete this member?" data-confirm-message="They will be removed from this family and lose access.">
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
                                            <a href="{{ route('families.members.edit', $member) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                                                Edit
                                            </a>
                                            @if (! $member->is_primary)
                                            <form action="{{ route('families.members.transfer-ownership', $member) }}" method="POST" class="inline-block js-confirm-delete" data-confirm-title="Transfer ownership?" data-confirm-message="Current owner role will be downgraded.">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="kt-btn kt-btn-xs kt-btn-outline">
                                                    Transfer ownership
                                                </button>
                                            </form>
                                            @endif
                                            <form action="{{ route('families.members.destroy', $member) }}" method="POST" class="js-confirm-delete inline-block" data-confirm-title="Delete this member?" data-confirm-message="They will be removed from this family and lose access.">
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
                        <p class="text-xs text-muted-foreground mt-3">{{ __('Use “Add member” to invite people by email.') }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
    </div>
</div>

<div
    id="famledger-profile-broadcast"
    style="display:none"
    data-enabled="{{ config('broadcasting.default') === 'reverb' ? '1' : '0' }}"
    data-family-id="{{ $family->id }}"
></div>
@endsection

@push('scripts')
@vite(['resources/js/dashboard-realtime.js'])
<script>
(function(){
    var btn    = document.getElementById('curr_btn');
    var menu   = document.getElementById('curr_menu');
    var search = document.getElementById('curr_search');
    var list   = document.getElementById('curr_list');
    if (!btn || !menu) return;

    document.body.appendChild(menu);

    function openMenu() {
        var r = btn.getBoundingClientRect();
        menu.style.display = 'flex';
        var menuW = 224;
        var left  = r.right - menuW;
        var top   = r.bottom + 6;
        if (left < 4) left = 4;
        menu.style.left = left + 'px';
        menu.style.top  = top + 'px';
        if (search) { search.value = ''; search.focus(); filterList(''); }
    }

    function closeMenu() {
        menu.style.display = 'none';
    }

    function isOpen() {
        return menu.style.display !== 'none';
    }

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        isOpen() ? closeMenu() : openMenu();
    });

    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && e.target !== btn) closeMenu();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMenu();
    });

    window.addEventListener('scroll', function() { if (isOpen()) openMenu(); }, true);
    window.addEventListener('resize', function() { if (isOpen()) openMenu(); });

    function filterList(q) {
        list.querySelectorAll('li').forEach(function(li) {
            li.style.display = li.dataset.lbl.includes(q) ? '' : 'none';
        });
    }
    if (search) {
        search.addEventListener('input', function() {
            filterList(this.value.toLowerCase());
        });
    }

    list.addEventListener('mouseover', function(e) {
        var b = e.target.closest('button');
        if (b) b.style.background = 'rgba(0,0,0,.05)';
    });
    list.addEventListener('mouseout', function(e) {
        var b = e.target.closest('button');
        if (b) b.style.background = '';
    });
})();

/**
 * Family profile tabs: Metronic layout does not load Bootstrap 5 JS, so data-bs-toggle="tab" does nothing.
 * Wire clicks + optional URL hash (#financial, #members, …) here.
 */
document.addEventListener('DOMContentLoaded', function() {
    var root = document.querySelector('.famledger-family-profile-tabs');
    if (!root) return;

    var tabButtons = root.querySelectorAll('#family_profile_tabs [data-bs-target][role="tab"]');
    var panes = root.querySelectorAll('.tab-content > .tab-pane');
    if (!tabButtons.length || !panes.length) return;

    function showTabByTargetSelector(targetSel) {
        if (!targetSel || targetSel.charAt(0) !== '#') return;
        var pane = root.querySelector(targetSel);
        if (!pane || !pane.classList.contains('tab-pane')) return;

        tabButtons.forEach(function(btn) {
            var on = btn.getAttribute('data-bs-target') === targetSel;
            btn.classList.toggle('active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panes.forEach(function(p) {
            var on = p === pane;
            p.classList.toggle('active', on);
            p.classList.toggle('show', on);
        });
    }

    tabButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var sel = btn.getAttribute('data-bs-target');
            showTabByTargetSelector(sel);
        });
    });

    var hashMap = {
        'family-details': 'fam_tab_details',
        'financial': 'fam_tab_finance',
        'highlights': 'fam_tab_highlights',
        'members': 'fam_tab_members'
    };
    var hash = (window.location.hash || '').replace(/^#/, '');
    var tabBtnId = hashMap[hash];
    if (tabBtnId) {
        var trigger = document.getElementById(tabBtnId);
        if (trigger) {
            var sel = trigger.getAttribute('data-bs-target');
            if (sel) showTabByTargetSelector(sel);
        }
    }
});
</script>
@endpush
