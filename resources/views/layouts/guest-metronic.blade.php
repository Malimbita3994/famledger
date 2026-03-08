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

    <title>FamLedger</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/Flavicon.png') }}"/>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/Flavicon.png') }}"/>

    <link rel="preload" href="{{ asset('metronic/assets/css/styles.css') }}" as="style"/>
    <link rel="preload" href="{{ asset('images/logo.png') }}" as="image"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="{{ asset('metronic/assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('metronic/assets/css/styles.css') }}" rel="stylesheet"/>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <style>
        #global-page-loader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.92);
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }
        #global-page-loader.loaded {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        #global-page-loader .global-loader-spinner {
            width: 48px;
            height: 48px;
            border: 3px solid rgba(148, 163, 184, 0.3);
            border-top-color: #38bdf8;
            border-radius: 50%;
            animation: global-loader-spin 0.85s linear infinite;
        }
        @keyframes global-loader-spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
    <!-- Global page loader -->
    <div id="global-page-loader" aria-hidden="true">
        <div class="global-loader-spinner" role="presentation"></div>
    </div>
    <script>
        (function(){var el=document.getElementById('global-page-loader');if(!el)return;var done=false;function hide(){if(done)return;done=true;el.classList.add('loaded');}window.addEventListener('load',hide);setTimeout(hide,2200);})();
    </script>
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
            /* On error, make sure card is fully visible and shake */
            opacity: 1 !important;
            transform: translateY(0) !important;
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
        /* Footer / version strip */
        .auth-footer {
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
            padding-top: 1rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            border-top: 1px solid rgba(148, 163, 184, 0.7);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            font-size: 0.75rem;
            color: #0f172a;
        }
        @media (min-width: 640px) {
            .auth-footer {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .auth-footer-pills {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem 1.25rem;
            color: #1e293b;
        }
        .auth-footer-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.2rem 0.6rem;
            background: rgba(15, 23, 42, 0.04);
            box-shadow: 0 0 0 1px rgba(148, 163, 184, 0.4);
        }
        .auth-footer-mark {
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: linear-gradient(120deg, #0f766e, #0284c7, #4f46e5);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .auth-footer-subtitle {
            font-size: 0.75rem;
            color: #0f172a;
            opacity: 0.85;
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
        $loginErrorMessage = $isLoginRoute ? ($errors->first('email') ?: $errors->first('password') ?: null) : null;
        $isRegisterRoute = request()->routeIs('register');
        $registerHasErrors = $isRegisterRoute && $errors->any();
        $registerErrorMessage = $isRegisterRoute ? ($errors->first('email') ?: $errors->first('password') ?: $errors->first('name') ?: null) : null;
        $guestFlash = [
            'success' => session('success'),
            'error'   => session('error'),
            'warning' => session('warning'),
            'info'    => session('info'),
        ];
    @endphp

    @if ($isLoginRoute && $loginErrorMessage)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') {
                    return;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Login failed',
                    text: @json($loginErrorMessage),
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2563eb'
                });
            });
        </script>
    @endif

    @if ($isRegisterRoute && $registerErrorMessage)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') {
                    return;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Sign up failed',
                    text: @json($registerErrorMessage),
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2563eb'
                });
            });
        </script>
    @endif

    @if ($guestFlash['success'] || $guestFlash['error'] || $guestFlash['warning'] || $guestFlash['info'])
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined' || typeof Swal.fire !== 'function') {
                    return;
                }

                var flash = @json($guestFlash);
                var type = flash.success ? 'success' : flash.error ? 'error' : flash.warning ? 'warning' : flash.info ? 'info' : null;
                var msg = flash.success || flash.error || flash.warning || flash.info;
                if (!type || !msg) {
                    return;
                }

                var isSuccess = type === 'success';

                Swal.fire({
                    icon: type,
                    title: isSuccess ? msg : (type.charAt(0).toUpperCase() + type.slice(1)),
                    text: isSuccess ? '' : msg,
                    showConfirmButton: true,
                    confirmButtonText: isSuccess ? 'Great, thanks' : 'OK',
                    width: 520,
                    padding: '2.5rem 2.75rem',
                    backdrop: true,
                    customClass: {
                        popup: 'rounded-2xl',
                        title: 'text-lg font-semibold',
                    },
                });
            });
        </script>
    @endif

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
                            <img class="auth-branded-logo auth-logo-animate" src="{{ asset('images/logo.png') }}" alt="FamLedger logo" decoding="async"/>
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

                {{-- BOTTOM: Trust signals + modern version strip --}}
                <div class="w-full mt-8">
                    <div class="auth-footer">
                        <div class="auth-footer-pills">
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

                        <div class="flex flex-col items-start sm:items-end text-left sm:text-right gap-0.5">
                            <span class="auth-footer-mark">FamLedger v1.0</span>
                            <span class="auth-footer-subtitle">{{ __('Private Family System') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('metronic/assets/js/core.bundle.js') }}" defer></script>
    <script src="{{ asset('metronic/assets/vendors/ktui/ktui.min.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
