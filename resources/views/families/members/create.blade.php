@extends('layouts.metronic')

@section('title', 'Add Member')
@section('page_title', 'Add Member')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.show', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }}
    </a>

    <form action="{{ route('families.members.store', $family) }}" method="POST">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            @if ($errors->any())
                <div class="rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-600 dark:text-red-400" role="alert">
                    <p class="font-medium">Please fix the errors below.</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->getBag('default')->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Add member</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">If the person doesn't have an account yet, one will be created and they will receive an email with their login credentials.</p>
                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="member_name" class="kt-form-label max-w-56">Name</label>
                        <div class="grow">
                            <input
                                type="text"
                                name="member_name"
                                id="member_name"
                                value="{{ old('member_name') }}"
                                placeholder="Display name in this family (optional)"
                                class="kt-input"
                                aria-invalid="{{ $errors->has('member_name') ? 'true' : 'false' }}"
                            />
                            @error('member_name')
                                <p class="kt-form-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="email" class="kt-form-label max-w-56">Email <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                required
                                placeholder="e.g. member@example.com"
                                class="kt-input"
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            />
                            @error('email')
                                <p class="kt-form-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="kt-form-label max-w-56">Sex</label>
                        <div class="grow flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="sex" value="male" {{ old('sex') === 'male' ? 'checked' : '' }} class="kt-radio" />
                                <span class="text-sm">Male</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="sex" value="female" {{ old('sex') === 'female' ? 'checked' : '' }} class="kt-radio" />
                                <span class="text-sm">Female</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label class="kt-form-label max-w-56">Adult or child</label>
                        <div class="grow flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="member_type" value="adult" {{ old('member_type', 'adult') === 'adult' ? 'checked' : '' }} class="kt-radio" />
                                <span class="text-sm">Adult</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="member_type" value="child" {{ old('member_type') === 'child' ? 'checked' : '' }} class="kt-radio" />
                                <span class="text-sm">Child</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="role_id" class="kt-form-label max-w-56">Role <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select
                                name="role_id"
                                id="role_id"
                                required
                                class="kt-select"
                                aria-invalid="{{ $errors->has('role_id') ? 'true' : 'false' }}"
                            >
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <p class="kt-form-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <a href="{{ route('families.show', $family) }}" class="kt-btn kt-btn-outline me-2">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-plus"></i>
                            Add member
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var validationErrors = @json($errors->getBag('default')->all());
    if (!validationErrors || validationErrors.length === 0) return;
    var text = validationErrors.length === 1 ? validationErrors[0] : validationErrors.join('\n');
    function showAlert() {
        if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Could not add member', text: text, confirmButtonColor: '#2563eb', showConfirmButton: true });
            return;
        }
        setTimeout(showAlert, 50);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', showAlert);
    else showAlert();
})();
</script>
@endpush
@endsection
