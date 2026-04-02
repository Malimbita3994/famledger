@extends('layouts.metronic')

@section('title', 'Settings')
@section('page_title', 'Settings')

@push('styles')
<style>
    /*
     * Use Metronic / FamLedger tokens so this page follows Theme panel → UI Color (data-fl-accent).
     * famledger-accent-themes.css maps each accent to --primary and --ring on <html>.
     */
    .admin-roles-page {
        --ar-accent: var(--primary);
        --ar-accent-2: var(--ring, var(--primary));
    }
    .admin-roles-eyebrow {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }
    .admin-roles-title {
        font-size: clamp(1.35rem, 2.8vw, 1.7rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: var(--primary);
    }
    .admin-roles-sub {
        font-size: 0.875rem;
        line-height: 1.5;
        margin-top: 0.35rem;
    }
    .admin-roles-pulse-card {
        border-radius: 16px;
        border: 1px solid color-mix(in oklab, var(--primary) 22%, var(--border));
        background: linear-gradient(
            180deg,
            var(--card) 0%,
            color-mix(in oklab, var(--primary) 6%, var(--card)) 100%
        );
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .admin-roles-pulse-card:hover {
        border-color: color-mix(in oklab, var(--primary) 42%, var(--border));
        box-shadow: 0 12px 32px color-mix(in oklab, var(--primary) 14%, transparent);
        transform: translateY(-2px);
    }
    .dark .admin-roles-pulse-card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .dark .admin-roles-pulse-card:hover {
        box-shadow: 0 12px 32px color-mix(in oklab, var(--primary) 18%, transparent);
    }
    .admin-roles-pulse-card .admin-roles-card-name {
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--primary);
    }
    .admin-roles-pulse-card .admin-roles-card-name:hover {
        color: var(--ring, var(--primary));
    }
    .admin-roles-pulse-card .admin-roles-card-icon {
        color: var(--primary);
    }
    .settings-hub-pulse-tile {
        padding: 1.25rem 1.5rem;
    }
    @media (min-width: 1024px) {
        .settings-hub-pulse-tile {
            padding: 1.35rem 1.65rem;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-roles-pulse-card:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-pulse admin-roles-page">
    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8 pb-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between pb-2">
            <div>
                <p class="admin-roles-eyebrow mb-1.5 text-muted-foreground">{{ __('Workspace') }}</p>
                <h1 class="admin-roles-title">{{ __('Settings') }}</h1>
                <p class="admin-roles-sub max-w-2xl text-muted-foreground">
                    {{ __('Manage your FamLedger profile, family configuration, categories, notifications and audit log from one place.') }}
                </p>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-12">
        <div class="settings-grid-3">
            {{-- Profile --}}
            <div class="admin-roles-pulse-card flex flex-col overflow-hidden settings-hub-pulse-tile">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-2.5 min-w-0 flex-1">
                        <a href="{{ route('profile.edit') }}" class="admin-roles-card-name text-base leading-tight">
                            {{ __('Profile') }}
                        </a>
                        <span class="text-sm text-secondary-foreground leading-relaxed">
                            {{ __('Update your personal details, email and password used to access FamLedger.') }}
                        </span>
                    </div>
                    <span class="admin-roles-card-icon text-2xl shrink-0 mt-0.5">
                        <i class="ki-filled ki-profile-circle"></i>
                    </span>
                </div>
            </div>

            {{-- Family profile --}}
            <div class="admin-roles-pulse-card flex flex-col overflow-hidden settings-hub-pulse-tile">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-2.5 min-w-0 flex-1">
                        <a
                            href="{{ isset($currentFamily) ? route('families.edit', $currentFamily) : route('families.index') }}"
                            class="admin-roles-card-name text-base leading-tight"
                        >
                            {{ __('Family profile') }}
                        </a>
                        <span class="text-sm text-secondary-foreground leading-relaxed">
                            {{ __('Configure your family name, default currency, timezone and other core preferences.') }}
                        </span>
                    </div>
                    <span class="admin-roles-card-icon text-2xl shrink-0 mt-0.5">
                        <i class="ki-filled ki-setting-2"></i>
                    </span>
                </div>
            </div>

            {{-- Categories (System admin only) --}}
            @if (auth()->user() && auth()->user()->hasRole('Super Admin'))
            <div class="admin-roles-pulse-card flex flex-col overflow-hidden settings-hub-pulse-tile">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-2.5 min-w-0 flex-1">
                        <a href="{{ route('settings.categories') }}" class="admin-roles-card-name text-base leading-tight">
                            {{ __('Categories') }}
                        </a>
                        <span class="text-sm text-secondary-foreground leading-relaxed">
                            {{ __('Define income and expense categories to keep reports, budgets and savings goals organised.') }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{ __('Detailed category management coming soon.') }}
                        </span>
                    </div>
                    <span class="admin-roles-card-icon text-2xl shrink-0 mt-0.5">
                        <i class="ki-filled ki-category"></i>
                    </span>
                </div>
            </div>
            @endif

            {{-- Notifications --}}
            <div class="admin-roles-pulse-card flex flex-col overflow-hidden settings-hub-pulse-tile">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-2.5 min-w-0 flex-1">
                        <a href="{{ route('settings.notifications') }}" class="admin-roles-card-name text-base leading-tight">
                            {{ __('Notifications') }}
                        </a>
                        <span class="text-sm text-secondary-foreground leading-relaxed">
                            {{ __('Control email alerts about new members, project updates, budget thresholds and savings progress.') }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{ __('Notification channels and rules will be configurable here.') }}
                        </span>
                    </div>
                    <span class="admin-roles-card-icon text-2xl shrink-0 mt-0.5">
                        <i class="ki-filled ki-notification-on"></i>
                    </span>
                </div>
            </div>

            {{-- Audit log (Super Admin & Auditor: full platform audit) --}}
            @if (auth()->user() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Auditor')))
            <div class="admin-roles-pulse-card flex flex-col overflow-hidden settings-hub-pulse-tile">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-2.5 min-w-0 flex-1">
                        <a href="{{ route('settings.audit-log') }}" class="admin-roles-card-name text-base leading-tight">
                            {{ __('Audit log') }}
                        </a>
                        <span class="text-sm text-secondary-foreground leading-relaxed">
                            {{ __('View all platform activity across families. Filter by date and type.') }}
                        </span>
                    </div>
                    <span class="admin-roles-card-icon text-2xl shrink-0 mt-0.5">
                        <i class="ki-filled ki-document"></i>
                    </span>
                </div>
            </div>
            @endif

            {{-- Property configuration (System admin only) --}}
            @if (auth()->user() && auth()->user()->hasRole('Super Admin'))
            <div class="admin-roles-pulse-card flex flex-col overflow-hidden settings-hub-pulse-tile">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-2.5 min-w-0 flex-1">
                        <a href="{{ route('settings.property.index') }}" class="admin-roles-card-name text-base leading-tight">
                            {{ __('Property configuration') }}
                        </a>
                        <span class="text-sm text-secondary-foreground leading-relaxed">
                            {{ __('Define categories and attributes that appear when families add or edit properties (Finance → Properties → Add property).') }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{ __('System-wide template; actual assets are created per family. Super Admin only.') }}
                        </span>
                    </div>
                    <span class="admin-roles-card-icon text-2xl shrink-0 mt-0.5">
                        <i class="ki-filled ki-home-3"></i>
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
