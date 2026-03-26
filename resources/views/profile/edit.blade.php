@extends('layouts.metronic')

@section('title', __('Profile'))
@section('page_title', __('Profile'))

@push('styles')
<style>
    .admin-pulse-page.profile-pulse-page {
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
    .admin-pulse-back {
        color: #64748b;
        transition: color 0.2s ease;
    }
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
    .profile-pulse-page .profile-pulse-grid .admin-pulse-frame {
        max-width: none;
        margin-left: 0;
        margin-right: 0;
    }
    .profile-pulse-danger-frame {
        padding: 3px;
        border-radius: 24px;
        background: linear-gradient(
            135deg,
            rgba(239, 68, 68, 0.38) 0%,
            rgba(255, 255, 255, 0.96) 46%,
            rgba(248, 113, 113, 0.28) 100%
        );
        box-shadow:
            0 4px 24px rgba(239, 68, 68, 0.1),
            0 24px 48px rgba(15, 23, 42, 0.08);
        width: 100%;
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
    .profile-pulse-section-title-danger {
        font-size: clamp(1.05rem, 2vw, 1.2rem);
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    .admin-pulse-create .kt-form-label,
    .admin-pulse-create .admin-pulse-field-label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #475569;
    }
    .dark .admin-pulse-create .kt-form-label,
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
    /* Native select: use background-color only (see famledger-native-select.css). */
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
    .admin-pulse-link-secondary {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--ap-accent);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        text-align: left;
    }
    .admin-pulse-link-secondary:hover {
        color: var(--ap-accent-2);
        text-decoration: underline;
    }
    .admin-pulse-hint {
        font-size: 0.75rem;
        line-height: 1.45;
        color: #64748b;
    }
    .dark .admin-pulse-hint {
        color: #94a3b8;
    }
    .profile-pulse-page .profile-pulse-intro {
        font-size: 0.875rem;
        line-height: 1.55;
        color: #64748b;
        margin-top: 0.35rem;
        max-width: 42rem;
    }
    .dark .profile-pulse-page .profile-pulse-intro {
        color: #94a3b8;
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-pulse-btn-primary:hover {
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-pulse-page profile-pulse-page">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0">
            <div class="min-w-0 flex-1">
                <p class="admin-pulse-eyebrow mb-1.5">{{ __('Account') }}</p>
                <h1 class="admin-pulse-title">{{ __('User profile') }}</h1>
                <p class="profile-pulse-intro">
                    {{ __('Manage your FamLedger account information, password and account safety from one place.') }}
                </p>
            </div>
            <div class="shrink-0 ms-auto">
                <a href="{{ route('settings.index') }}" class="admin-pulse-btn-outline inline-flex">
                    <i class="ki-filled ki-left text-base"></i>
                    {{ __('Back to settings') }}
                </a>
            </div>
        </div>
    </div>

    <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14">
        <div class="profile-pulse-grid grid gap-6 lg:gap-7.5">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-7.5">
                {{-- Personal info --}}
                <div class="admin-pulse-frame">
                    <div class="admin-pulse-card-inner">
                        <h2 class="admin-pulse-section-title mb-1">{{ __('Personal info') }}</h2>
                        <p class="admin-pulse-hint mb-6">{{ __('Basic details about your account.') }}</p>

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <form id="send-verification" method="post" action="{{ route('verification.send') }}" hidden>
                                @csrf
                            </form>
                        @endif

                        <form method="post" action="{{ route('profile.update') }}" class="admin-pulse-create grid gap-4 lg:gap-5 max-w-xl">
                            @csrf
                            @method('patch')

                            <div class="flex items-center gap-4">
                                <div class="relative inline-flex items-center justify-center rounded-full bg-primary/10 text-primary font-semibold size-12">
                                    <span class="text-sm">
                                        {{ strtoupper(mb_substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium text-foreground">
                                        {{ $user->name }}
                                    </span>
                                    <span class="admin-pulse-hint">
                                        {{ __('Avatar or profile image upload coming soon') }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid gap-1.5">
                                <label for="name" class="kt-form-label">
                                    {{ __('Full name') }}
                                </label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name', $user->name) }}"
                                    required
                                    autocomplete="name"
                                    class="kt-input w-full"
                                />
                                @error('name')
                                    <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-1.5">
                                <label for="email" class="kt-form-label">
                                    {{ __('Email address') }}
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email', $user->email) }}"
                                    required
                                    autocomplete="username"
                                    class="kt-input w-full"
                                />
                                @error('email')
                                    <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                                @enderror

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-1.5 space-y-1.5">
                                        <p class="admin-pulse-hint">
                                            {{ __('Your email address is unverified.') }}
                                        </p>
                                        <button
                                            type="submit"
                                            form="send-verification"
                                            class="admin-pulse-link-secondary text-left"
                                        >
                                            {{ __('Click here to re-send the verification email.') }}
                                        </button>
                                        @if (session('status') === 'verification-link-sent')
                                            <p class="text-xs text-green-600 dark:text-green-400">
                                                {{ __('A new verification link has been sent to your email address.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-3 pt-1">
                                <button type="submit" class="admin-pulse-btn-primary">
                                    {{ __('Save changes') }}
                                </button>
                                @if (session('status') === 'profile-updated')
                                    <span class="admin-pulse-hint">{{ __('Saved.') }}</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Password --}}
                <div class="admin-pulse-frame">
                    <div class="admin-pulse-card-inner">
                        <h2 class="admin-pulse-section-title mb-1">{{ __('Password & security') }}</h2>
                        <p class="admin-pulse-hint mb-6">{{ __('Keep your account secured with a strong password.') }}</p>

                        <form method="post" action="{{ route('password.update') }}" class="admin-pulse-create grid gap-4 lg:gap-5 max-w-xl">
                            @csrf
                            @method('put')

                            <div class="grid gap-1.5">
                                <label for="current_password" class="kt-form-label">
                                    {{ __('Current password') }}
                                </label>
                                <input
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    autocomplete="current-password"
                                    class="kt-input w-full"
                                />
                                @if ($errors->updatePassword->has('current_password'))
                                    <p class="text-xs text-destructive mt-1">
                                        {{ $errors->updatePassword->first('current_password') }}
                                    </p>
                                @endif
                            </div>

                            <div class="grid gap-1.5">
                                <label for="password" class="kt-form-label">
                                    {{ __('New password') }}
                                </label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="new-password"
                                    class="kt-input w-full"
                                />
                                @if ($errors->updatePassword->has('password'))
                                    <p class="text-xs text-destructive mt-1">
                                        {{ $errors->updatePassword->first('password') }}
                                    </p>
                                @endif
                            </div>

                            <div class="grid gap-1.5">
                                <label for="password_confirmation" class="kt-form-label">
                                    {{ __('Confirm new password') }}
                                </label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    class="kt-input w-full"
                                />
                                @if ($errors->updatePassword->has('password_confirmation'))
                                    <p class="text-xs text-destructive mt-1">
                                        {{ $errors->updatePassword->first('password_confirmation') }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-3 pt-1">
                                <button type="submit" class="admin-pulse-btn-primary">
                                    {{ __('Update password') }}
                                </button>
                                @if (session('status') === 'password-updated')
                                    <span class="admin-pulse-hint">{{ __('Saved.') }}</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Danger zone --}}
            <div class="profile-pulse-danger-frame">
                <div class="admin-pulse-card-inner border border-destructive/25 dark:border-destructive/35">
                    <h2 class="profile-pulse-section-title-danger text-destructive mb-1">
                        {{ __('Danger zone') }}
                    </h2>
                    <p class="admin-pulse-hint mb-6">
                        {{ __('Delete your account and all related data.') }}
                    </p>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
