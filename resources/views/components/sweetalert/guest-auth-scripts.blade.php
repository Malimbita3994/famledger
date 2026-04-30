{{-- Guest auth pages: login/register errors, logout confirmation toast, session flash. Requires x-sweetalert.cdn in layout. --}}
@php
    $isLoginRoute = request()->routeIs('login');
    $errorBag = view()->shared('errors');
    $loginErrorMessage = null;
    $registerErrorMessage = null;
    if ($errorBag instanceof \Illuminate\Support\ViewErrorBag) {
        if ($isLoginRoute) {
            $loginErrorMessage = $errorBag->first('email') ?: $errorBag->first('password') ?: null;
        }
        if (request()->routeIs('register')) {
            $registerErrorMessage = $errorBag->first('email') ?: $errorBag->first('password') ?: $errorBag->first('name') ?: null;
        }
    }
    $isRegisterRoute = request()->routeIs('register');
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
                confirmButtonColor: '#2563eb',
                customClass: { popup: 'rounded-2xl' },
            });
        });
    </script>
@endif

@if ($isLoginRoute && request()->query('logged_out'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal === 'undefined' || typeof Swal.fire !== 'function') {
                return;
            }
            Swal.fire({
                icon: 'success',
                title: @json(__('Welcome again!')),
                text: @json(__('You have been signed out successfully.')),
                confirmButtonText: @json(__('OK')),
                confirmButtonColor: '#16a34a',
                customClass: {
                    popup: 'rounded-2xl',
                    title: 'text-base font-semibold',
                },
            }).then(function () {
                try {
                    var u = new URL(window.location.href);
                    if (u.searchParams.has('logged_out')) {
                        u.searchParams.delete('logged_out');
                        var q = u.searchParams.toString();
                        window.history.replaceState({}, '', u.pathname + (q ? '?' + q : '') + u.hash);
                    }
                } catch (e) {}
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
                confirmButtonColor: '#2563eb',
                customClass: { popup: 'rounded-2xl' },
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
                backdrop: true,
                customClass: {
                    popup: 'rounded-2xl',
                    title: 'text-base font-semibold',
                },
            });
        });
    </script>
@endif
