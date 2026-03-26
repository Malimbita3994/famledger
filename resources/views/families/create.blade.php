@extends('layouts.metronic')

@section('title', 'Create Family')
@section('page_title', 'Create Family')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.index') }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to families
    </a>

    <form action="{{ route('families.store') }}" method="POST">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 w-full max-w-5xl mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header" id="family_details">
                    <h3 class="kt-card-title">Family registration</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">Owner and status are set automatically. Only the fields below are needed.</p>

                    {{-- Metronic styles.css omits most grid-cols-* utilities; use famledger-form-grids.css --}}
                    <div class="famledger-form-row-3">
                        {{-- md+: name | currency | timezone on one row; country starts next row --}}
                        <div class="famledger-form-field flex flex-col gap-2">
                            <label for="name" class="kt-form-label">Family name <span class="text-destructive">*</span></label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g. Smith Family"
                                class="kt-input w-full"
                                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                            />
                            @error('name')
                                <p class="kt-form-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="famledger-form-field flex flex-col gap-2">
                            <label for="currency_code" class="kt-form-label">Currency <span class="text-destructive">*</span></label>
                            <select
                                name="currency_code"
                                id="currency_code"
                                required
                                class="kt-select w-full"
                                aria-invalid="{{ $errors->has('currency_code') ? 'true' : 'false' }}"
                            >
                                @foreach ($currencies ?? [] as $code => $label)
                                    <option value="{{ $code }}" {{ old('currency_code', config('currencies.default', 'TZS')) === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('currency_code')
                                <p class="kt-form-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="famledger-form-field flex flex-col gap-2">
                            <label for="timezone" class="kt-form-label">Timezone <span class="text-destructive">*</span></label>
                            <input
                                type="text"
                                name="timezone"
                                id="timezone"
                                value="{{ old('timezone', 'UTC') }}"
                                required
                                placeholder="e.g. UTC or America/New_York"
                                class="kt-input w-full"
                                aria-invalid="{{ $errors->has('timezone') ? 'true' : 'false' }}"
                            />
                            @error('timezone')
                                <p class="kt-form-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="famledger-form-field flex flex-col gap-2">
                            <label for="country" class="kt-form-label">Country <span class="text-muted-foreground font-normal">(optional)</span></label>
                            <input
                                type="text"
                                name="country"
                                id="country"
                                value="{{ old('country') }}"
                                placeholder="e.g. United States"
                                class="kt-input w-full"
                                aria-invalid="{{ $errors->has('country') ? 'true' : 'false' }}"
                            />
                            @error('country')
                                <p class="kt-form-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Optional: full width --}}
                    <div class="flex flex-col gap-2">
                        <label for="description" class="kt-form-label">Description <span class="text-muted-foreground font-normal">(optional)</span></label>
                        <textarea
                            name="description"
                            id="description"
                            rows="4"
                            placeholder="Short description of the family"
                            class="kt-textarea min-h-[120px] resize-y w-full"
                            aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="kt-form-message mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-2">
                        <a href="{{ route('families.index') }}" class="kt-btn kt-btn-outline me-2">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-plus"></i>
                            Create Family
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
