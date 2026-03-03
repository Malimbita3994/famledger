@extends('layouts.metronic')

@section('title', 'Add Wallet')
@section('page_title', 'Add Wallet')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-6 lg:py-8 pb-12">
    <a href="{{ route('families.wallets.index', $family) }}" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground transition-colors mb-6">
        <i class="ki-filled ki-left text-base mr-1"></i>
        Back to {{ $family->name }} wallets
    </a>

    <form action="{{ route('families.wallets.store', $family) }}" method="POST">
        @csrf

        <div class="grid gap-5 lg:gap-7.5 xl:w-[38.75rem] mx-auto">
            <div class="kt-card pb-2.5">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Add wallet</h3>
                </div>
                <div class="kt-card-content grid gap-5">
                    <p class="text-sm text-muted-foreground -mt-1">A wallet is where family money lives. Stand-alone—no bank link. Use it for cash, savings, daily spending, or project funds.</p>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="name" class="kt-form-label max-w-56">Wallet name <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Home Cash, Emergency Fund" class="kt-input" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" />
                            @error('name')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="type" class="kt-form-label max-w-56">Type <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="type" id="type" required class="kt-select" aria-invalid="{{ $errors->has('type') ? 'true' : 'false' }}">
                                @foreach (App\Models\Wallet::types() as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="currency_code" class="kt-form-label max-w-56">Currency <span class="text-destructive">*</span></label>
                        <div class="grow">
                            <select name="currency_code" id="currency_code" required class="kt-select" aria-invalid="{{ $errors->has('currency_code') ? 'true' : 'false' }}">
                                @foreach ($currencies ?? [] as $code => $label)
                                    <option value="{{ $code }}" {{ old('currency_code', $family->currency_code) === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('currency_code')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="description" class="kt-form-label max-w-56">Description <span class="text-muted-foreground font-normal">(optional)</span></label>
                        <div class="grow">
                            <textarea name="description" id="description" rows="3" placeholder="Short description" class="kt-textarea resize-y" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}">{{ old('description') }}</textarea>
                            @error('description')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="initial_balance" class="kt-form-label max-w-56">Initial balance <span class="text-muted-foreground font-normal">(optional)</span></label>
                        <div class="grow">
                            <input type="number" name="initial_balance" id="initial_balance" value="{{ old('initial_balance', '0') }}" step="0.01" min="0" placeholder="0" class="kt-input" />
                            @error('initial_balance')<p class="kt-form-message mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                        <label for="is_shared" class="kt-form-label max-w-56">Shared wallet</label>
                        <div class="grow flex items-center gap-2">
                            <input type="checkbox" name="is_shared" id="is_shared" value="1" {{ old('is_shared', true) ? 'checked' : '' }} class="kt-checkbox" />
                            <span class="text-sm text-muted-foreground">All family members can use this wallet</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                        <a href="{{ route('families.wallets.index', $family) }}" class="kt-btn kt-btn-outline">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
                            <i class="ki-filled ki-plus"></i>
                            Add wallet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
