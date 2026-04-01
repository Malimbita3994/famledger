<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Forgot Password') }} - {{ config('app.name') }}</x-slot>
    <x-slot name="brandedDescription">{{ __('We\'ll send you a link to reset your password.') }}</x-slot>

    <form x-data="{ loading: false }" x-on:submit="loading = true" method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4 sm:gap-5">
        @csrf

        <div class="text-center mb-1">
            <h3 class="font-semibold leading-none mb-2">
                {{ __('Forgot password') }}
            </h3>
            <p class="text-xs text-slate-500 font-medium tracking-wide uppercase opacity-90 mb-0">
                {{ __('Password recovery') }}
            </p>
            <p class="text-sm text-secondary-foreground mt-3 mb-0">
                {{ __('Enter your email to reset your password.') }}
            </p>
        </div>

        <div class="flex flex-col gap-1">
            <label class="kt-form-label font-normal text-mono" for="email">{{ __('Email') }}</label>
            <input id="email" class="kt-input auth-text-input @error('email') border-danger @enderror" type="email" name="email"
                   placeholder="email@email.com" value="{{ old('email') }}" required autofocus/>
            @error('email')
                <span class="text-sm text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit"
                class="kt-btn kt-btn-primary"
                x-bind:aria-busy="loading">
            <span x-show="!loading" class="inline-flex items-center">{{ __('Continue') }}</span>
            <span x-show="loading" class="inline-flex items-center justify-center" aria-hidden="true">
                <svg class="auth-pulse-btn-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        </button>

        <a href="{{ route('login') }}"
           class="kt-btn kt-btn-outline justify-center">
            {{ __('Back to sign in') }}
        </a>
    </form>

    @push('scripts')
        <script>
            (function () {
                var statusMessage = @json(session('status'));
                var emailErrors = @json($errors->get('email') ?? []);

                if (typeof Swal === 'undefined' || typeof Swal.fire !== 'function') {
                    return;
                }

                if (statusMessage) {
                    Swal.fire({
                        icon: 'success',
                        title: statusMessage,
                        showConfirmButton: true,
                        confirmButtonText: 'Great, thanks',
                        width: 420,
                        padding: '1.75rem 2rem',
                    });
                } else if (emailErrors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to send reset link',
                        text: emailErrors[0],
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        width: 420,
                        padding: '1.75rem 2rem',
                    });
                }
            })();
        </script>
    @endpush
</x-guest-metronic-layout>
