<!--
Metronic-style auth layout for FamLedger
Based on Metronic Tailwind CSS branded sign-in (v9.4.5)
-->
<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="robots" content="follow, index"/>

    <title>{{ $title ?? config('app.name', 'FamLedger') }}</title>

    <link href="{{ asset('metronic/assets/media/app/favicon.ico') }}" rel="shortcut icon"/>
    <link href="{{ asset('metronic/assets/media/app/favicon-32x32.png') }}" rel="icon" sizes="32x32" type="image/png"/>
    <link href="{{ asset('metronic/assets/media/app/favicon-16x16.png') }}" rel="icon" sizes="16x16" type="image/png"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="{{ asset('metronic/assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('metronic/assets/css/styles.css') }}" rel="stylesheet"/>
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
    <!-- Theme Mode -->
    <script>
        const defaultThemeMode = 'light';
        let themeMode;
        if (document.documentElement) {
            if (localStorage.getItem('kt-theme')) {
                themeMode = localStorage.getItem('kt-theme');
            } else if (document.documentElement.hasAttribute('data-kt-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-kt-theme-mode');
            } else {
                themeMode = defaultThemeMode;
            }
            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.classList.add(themeMode);
        }
    </script>

    <style>
        /* Branding area: soft sky band at top, neutral below to avoid tall empty blue block */
        .auth-branded-bg {
            background-color: #e5f3ff;
            background-image: radial-gradient(circle at top, #ffffff 0%, #e5f3ff 32%, #d6ecff 60%, #c0e0ff 85%, #a5d4ff 100%);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: top center;
        }
        .dark .auth-branded-bg {
            background: linear-gradient(135deg, #020617 0%, #020617 100%);
        }
        .auth-branded-bg .auth-panel-text { color: #0f172a; }
        .auth-branded-bg .auth-panel-text.opacity-90 { color: rgba(15,23,42,0.9); }
        .auth-glass {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(148,163,184,0.25);
            box-shadow: 0 24px 60px rgba(15,23,42,0.22);
        }
        .dark .auth-glass {
            background: rgba(15,23,42,0.96);
            border-color: rgba(148,163,184,0.35);
        }
        @keyframes auth-fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes auth-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .auth-animate-fade-up {
            animation: auth-fade-up 0.6s ease-out forwards;
        }
        .auth-animate-float {
            animation: auth-float 4s ease-in-out infinite;
        }
        @keyframes auth-logo-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        .auth-logo-animate {
            animation: auth-logo-float 4s ease-in-out infinite;
        }
        /* Login card entrance + error shake */
        @keyframes auth-login-fade-up {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes auth-login-shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }
        .auth-login-card {
            opacity: 0;
            transform: translateY(24px);
            animation: auth-login-fade-up 0.5s ease-out forwards;
        }
        .auth-login-card-error {
            animation: auth-login-shake 0.3s ease;
        }
        .auth-feature-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.25rem;
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
            box-sizing: border-box;
        }
        .auth-feature-card {
            border-radius: 1.75rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .auth-feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
        }
        .auth-feature-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            width: 3rem;
            height: 3rem;
            color: #ffffff;
        }
        .auth-feature-icon--secure {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        .auth-feature-icon--clarity {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
        }
        .auth-feature-icon--projects {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }
        /* Shared auth text input focus style */
        .auth-text-input {
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .auth-text-input:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25);
            background-color: #ffffff;
        }
        /* Logo size: change height (e.g. 2rem, 3rem, 4rem) to control branding logo size */
        .auth-branded-logo {
            height: 7.5rem;
            width: auto;
            max-width: 540px;
            object-fit: contain;
        }
    </style>

    @php
        $isLoginRoute = request()->routeIs('login');
        $loginHasErrors = $isLoginRoute && $errors->any();
    @endphp

    <div class="grid lg:grid-cols-2 w-full">
        <!-- Form column -->
        <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
            <div class="kt-card max-w-[390px] w-auto auth-login-card {{ $loginHasErrors ? 'auth-login-card-error' : '' }}">
                <div class="kt-card-content flex flex-col gap-5 p-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
        <!-- Branded panel -->
        <div class="lg:rounded-xl lg:border lg:border-border lg:m-5 order-1 lg:order-2 auth-branded-bg overflow-hidden">
            <div class="flex flex-col justify-between items-center p-8 lg:p-12 max-w-5xl mx-auto h-full">
                <div class="w-full flex flex-col items-center">
                    {{-- TOP: Logo --}}
                    <div class="w-full flex flex-col items-center mb-6">
                        <a href="{{ url('/') }}" class="inline-flex items-center">
                            <img class="auth-branded-logo auth-logo-animate" src="{{ asset('images/logo.png') }}" alt="FamLedger logo"/>
                        </a>
                    </div>

                    {{-- MIDDLE: Headline + description + illustration --}}
                    <h2 class="text-xl lg:text-2xl font-semibold text-slate-900 text-center mb-2">
                        {{ $brandedHeading ?? __('Family Finances Manager') }}
                    </h2>
                    <p class="text-sm lg:text-base text-slate-600 text-center max-w-xl mx-auto mb-6">
                        {{ $brandedDescription ?? __('Efficiently manage your family\'s finances, projects, and goals – all in one secure platform.') }}
                    </p>
                    <div class="flex justify-center my-6">
                        <div class="w-auto max-w-[220px] lg:max-w-[260px] auth-animate-float">
                            <svg viewBox="0 0 240 160" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto" style="color:#059669;">
                                <path d="M120 20L40 70v50h40V90h80v30h40V70L120 20z" fill="currentColor" opacity="0.9"/>
                                <rect x="70" y="75" width="30" height="25" rx="2" fill="currentColor" opacity="0.7"/>
                                <rect x="140" y="75" width="30" height="25" rx="2" fill="currentColor" opacity="0.7"/>
                                <path d="M50 100h20v20H50z" fill="currentColor" opacity="0.5"/>
                                <path d="M170 100h20v20h-20z" fill="currentColor" opacity="0.5"/>
                                <rect x="95" y="55" width="50" height="8" rx="2" fill="currentColor" opacity="0.6"/>
                                <rect x="95" y="68" width="35" height="6" rx="1" fill="currentColor" opacity="0.4"/>
                                <rect x="95" y="78" width="45" height="6" rx="1" fill="currentColor" opacity="0.4"/>
                                <circle cx="200" cy="35" r="18" stroke="currentColor" stroke-width="2" fill="none" opacity="0.8"/>
                                <path d="M195 35h10l-5-8v8z" fill="currentColor" opacity="0.8"/>
                            </svg>
                        </div>
                    </div>

                    {{-- BELOW: 3 feature highlight cards --}}
                    <div class="auth-feature-row auth-animate-fade-up" style="animation-delay: 0.3s;">
                        <div class="auth-feature-card rounded-3xl bg-white/95 border border-sky-100 px-5 py-5 flex items-center gap-4">
                            <span class="auth-feature-icon auth-feature-icon--secure">
                                <i class="ki-filled ki-shield-tick text-2xl"></i>
                            </span>
                            <div class="text-left">
                                <div class="font-semibold text-slate-900">{{ __('Secure by design') }}</div>
                                <div class="text-sm text-slate-600">{{ __('Role-based access for every member.') }}</div>
                            </div>
                        </div>
                        <div class="auth-feature-card rounded-3xl bg-white/95 border border-sky-100 px-5 py-5 flex items-center gap-4">
                            <span class="auth-feature-icon auth-feature-icon--clarity">
                                <i class="ki-filled ki-graph-up text-2xl"></i>
                            </span>
                            <div class="text-left">
                                <div class="font-semibold text-slate-900">{{ __('Financial clarity') }}</div>
                                <div class="text-sm text-slate-600">{{ __('Track balances and contributions.') }}</div>
                            </div>
                        </div>
                        <div class="auth-feature-card rounded-3xl bg-white/95 border border-sky-100 px-5 py-5 flex items-center gap-4">
                            <span class="auth-feature-icon auth-feature-icon--projects">
                                <i class="ki-filled ki-home-2 text-2xl"></i>
                            </span>
                            <div class="text-left">
                                <div class="font-semibold text-slate-900">{{ __('Family projects') }}</div>
                                <div class="text-sm text-slate-600">{{ __('Plan construction, education, goals.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BOTTOM: Trust signals + version anchored to bottom --}}
                <div class="w-full mt-8">
                    <div class="pt-4 border-t border-slate-200/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 max-w-[720px] mx-auto text-xs">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-600">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="size-5 inline-flex items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                    <i class="ki-filled ki-lock text-[10px]"></i>
                                </span>
                                <span class="font-medium">{{ __('End-to-end security') }}</span>
                            </span>

                            <span class="inline-flex items-center gap-1.5">
                                <span class="size-5 inline-flex items-center justify-center rounded-full bg-sky-50 text-sky-600">
                                    <i class="ki-filled ki-devices text-[10px]"></i>
                                </span>
                                <span class="font-medium">{{ __('Works on all devices') }}</span>
                            </span>
                        </div>

                        <div class="text-slate-500 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center rounded-full bg-slate-100 text-slate-700 px-2 py-0.5">
                                <span class="font-medium">FamLedger v1.0</span>
                            </span>
                            <span>· {{ __('Private Family System') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('metronic/assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('metronic/assets/vendors/ktui/ktui.min.js') }}"></script>
</body>
</html>
