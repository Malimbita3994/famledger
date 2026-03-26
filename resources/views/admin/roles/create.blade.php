@extends('layouts.metronic')

@section('title', __('Add role'))
@section('page_title', __('Add role'))

@push('styles')
<style>
    .admin-pulse-page {
        --ap-accent: #009ef7;
        --ap-accent-2: #0ea5e9;
        --ap-soft: #f0f9ff;
        --ap-ring: rgba(0, 158, 247, 0.28);
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
    .admin-pulse-breadcrumb a,
    .admin-pulse-back {
        color: #64748b;
        transition: color 0.2s ease;
    }
    .admin-pulse-breadcrumb a:hover,
    .admin-pulse-back:hover {
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
    .admin-pulse-subhead {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
        letter-spacing: -0.01em;
    }
    .dark .admin-pulse-subhead {
        color: #e2e8f0;
    }
    .admin-pulse-create .admin-pulse-field-label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #475569;
    }
    .dark .admin-pulse-create .admin-pulse-field-label {
        color: #94a3b8;
    }
    .admin-pulse-create .kt-input,
    .admin-pulse-create .kt-textarea {
        width: 100%;
        padding: 0.8rem 1rem;
        font-size: 0.9375rem;
        border-radius: 12px;
        background: var(--ap-soft) !important;
        border: 1px solid transparent !important;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
    }
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
    .admin-pulse-hint {
        font-size: 0.75rem;
        line-height: 1.45;
        color: #64748b;
    }
    .dark .admin-pulse-hint {
        color: #94a3b8;
    }
    /* Stack on small screens; one row from lg up (same as original role-main-row). */
    .admin-role-main-row {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .admin-role-main-row {
            flex-direction: row;
            gap: 1.75rem;
            align-items: flex-start;
        }
        .admin-role-main-row .admin-role-main-col {
            flex: 1 1 0;
            min-width: 0;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-pulse-btn-primary:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-pulse-page">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="admin-pulse-eyebrow mb-1.5">{{ __('Access control') }}</p>
                <h1 class="admin-pulse-title">{{ __('Add role') }}</h1>
                <div class="admin-pulse-breadcrumb flex items-center gap-1.5 text-sm mt-2">
                    <a href="{{ route('admin.roles.index') }}">{{ __('Roles') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-muted-foreground font-medium">{{ __('Create') }}</span>
                </div>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="admin-pulse-back inline-flex items-center text-sm shrink-0">
                <i class="ki-filled ki-left text-base me-1"></i>
                {{ __('Back to roles') }}
            </a>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        <form action="{{ route('admin.roles.store') }}" method="POST" class="admin-pulse-create space-y-0">
            @csrf

            <div class="admin-pulse-frame">
                <div class="admin-pulse-card-inner">
                    <h2 class="admin-pulse-section-title mb-1">{{ __('Role information') }}</h2>
                    <p class="admin-pulse-hint mb-6">{{ __('Define the role identifier and labels. You can assign permissions on the next step.') }}</p>

                    <div>
                        <h3 class="admin-pulse-subhead mb-4">{{ __('Basic information') }}</h3>
                        <div class="admin-role-main-row">
                            <div class="admin-role-main-col flex flex-col gap-1.5">
                                <label for="name" class="admin-pulse-field-label">
                                    {{ __('Role name') }} <span class="text-red-600 dark:text-red-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name') }}"
                                    required
                                    class="kt-input"
                                    placeholder="{{ __('e.g. content_manager') }}"
                                />
                                <p class="admin-pulse-hint mt-0.5">{{ __('Use lowercase with underscores (e.g. content_manager).') }}</p>
                                @error('name')
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="admin-role-main-col flex flex-col gap-1.5">
                                <label for="display_name" class="admin-pulse-field-label">
                                    {{ __('Display name') }}
                                </label>
                                <input
                                    type="text"
                                    name="display_name"
                                    id="display_name"
                                    value="{{ old('display_name') }}"
                                    class="kt-input"
                                    placeholder="{{ __('e.g. Content Manager') }}"
                                />
                                <p class="admin-pulse-hint mt-0.5">{{ __('Human-readable label (optional).') }}</p>
                                @error('display_name')
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="admin-role-main-col flex flex-col gap-1.5">
                                <label for="description" class="admin-pulse-field-label">
                                    {{ __('Description') }}
                                </label>
                                <textarea
                                    name="description"
                                    id="description"
                                    rows="3"
                                    class="kt-textarea min-h-[88px] resize-y"
                                    placeholder="{{ __('Brief description of this role') }}"
                                >{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row flex-nowrap justify-end items-center gap-3 pt-8 mt-6 border-t border-sky-100/80 dark:border-slate-600/50">
                        <a href="{{ route('admin.roles.index') }}" class="admin-pulse-btn-outline justify-center text-center shrink-0 whitespace-nowrap">{{ __('Cancel') }}</a>
                        <button type="submit" class="admin-pulse-btn-primary justify-center shrink-0 whitespace-nowrap">
                            <i class="ki-filled ki-plus text-base"></i>
                            {{ __('Create role') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
