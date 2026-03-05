<x-guest-metronic-layout>
    <x-slot name="title">{{ __('Forgot Password') }} - {{ config('app.name') }}</x-slot>
    <x-slot name="brandedDescription">{{ __('We\'ll send you a link to reset your password.') }}</x-slot>

    <form x-data="{ loading: false }" x-on:submit="loading = true" method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
        @csrf

        <div class="text-center mb-2.5">
            <h3 class="text-lg font-medium text-mono leading-none mb-1.5">
                {{ __('Your Email') }}
            </h3>
            <p class="text-sm text-secondary-foreground">
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
                class="kt-btn kt-btn-primary flex justify-center items-center gap-2 grow"
                x-bind:disabled="loading">
            <span x-show="!loading">{{ __('Continue') }}</span>
            <div x-show="loading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
        </button>

        <a href="{{ route('login') }}"
           class="kt-btn kt-btn-outline flex justify-center items-center grow">
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
