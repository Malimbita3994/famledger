@extends('layouts.metronic')

@section('title', __('Family profile'))
@section('page_title', __('Family profile'))

@section('content')
 <div class="pb-5">
  <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
   <div class="flex flex-col gap-1">
    <h1 class="font-medium text-lg text-foreground">
     {{ __('Family profile') }}
    </h1>
    <p class="text-sm text-secondary-foreground">
     {{ __('Manage your family’s details, status and overview.') }}
    </p>
   </div>
   <div class="flex items-center gap-2">
    <x-fin-back-link href="{{ route('settings.index') }}" class="!mb-0">{{ __('Back to settings') }}</x-fin-back-link>
   </div>
  </div>
 </div>

 <style>
  .family-main-row {
      display: flex;
      flex-direction: column;
      gap: 1.25rem; /* ~gap-5 */
  }

  .family-main-row .family-main-col {
      width: 100%;
  }

  @media (min-width: 900px) {
      .family-main-row {
          flex-direction: row;
      }

      .family-main-row .family-main-col {
          flex: 1 1 0;
      }
  }
 </style>

 <div class="kt-container-fixed pb-6">
  <form method="POST" action="{{ route('families.update', $family) }}">
   @csrf
   @method('PUT')

   <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
    {{-- Left: main family details --}}
    <div class="xl:col-span-2 flex flex-col gap-5 lg:gap-7.5">
     {{-- General info --}}
     <div class="kt-card min-w-full">
      <div class="kt-card-header items-center justify-between">
       <h3 class="kt-card-title">
        {{ __('General info') }}
       </h3>
       <span class="text-xs text-muted-foreground">
        {{ __('Core information about this family.') }}
       </span>
      </div>
      <div class="kt-card-content px-5 pb-5">
       <div class="grid gap-5 lg:gap-7.5">
        {{-- Top row: 3-column layout for name, currency, timezone --}}
        <div class="family-main-row">
         {{-- Family name --}}
         <div class="family-main-col grid gap-1.5">
          <label for="name" class="kt-form-label text-sm text-secondary-foreground">
           {{ __('Family name') }}
           <span class="text-destructive">*</span>
          </label>
          <input
           type="text"
           name="name"
           id="name"
           value="{{ old('name', $family->name) }}"
           required
           placeholder="{{ __('e.g. Smith family') }}"
           class="kt-input w-full"
           aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
          />
          @error('name')
           <p class="kt-form-message mt-1">{{ $message }}</p>
          @enderror
         </div>

         {{-- Currency --}}
         <div class="family-main-col grid gap-1.5">
          <label for="currency_code" class="kt-form-label text-sm text-secondary-foreground">
           {{ __('Currency') }}
           <span class="text-destructive">*</span>
          </label>
          <select
           name="currency_code"
           id="currency_code"
           required
           class="kt-select w-full"
           aria-invalid="{{ $errors->has('currency_code') ? 'true' : 'false' }}"
          >
           @foreach ($currencies ?? [] as $code => $label)
            <option value="{{ $code }}" {{ old('currency_code', $family->currency_code) === $code ? 'selected' : '' }}>
             {{ $label }}
            </option>
           @endforeach
          </select>
          @error('currency_code')
           <p class="kt-form-message mt-1">{{ $message }}</p>
          @enderror
         </div>

         {{-- Timezone --}}
         <div class="family-main-col grid gap-1.5">
          <label for="timezone" class="kt-form-label text-sm text-secondary-foreground">
           {{ __('Timezone') }}
           <span class="text-destructive">*</span>
          </label>
          <input
           type="text"
           name="timezone"
           id="timezone"
           value="{{ old('timezone', $family->timezone) }}"
           required
           placeholder="{{ __('e.g. UTC or America/New_York') }}"
           class="kt-input w-full"
           aria-invalid="{{ $errors->has('timezone') ? 'true' : 'false' }}"
          />
          @error('timezone')
           <p class="kt-form-message mt-1">{{ $message }}</p>
          @enderror
         </div>
        </div>

        {{-- Country --}}
        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
         <label for="country" class="kt-form-label max-w-56">
          {{ __('Country') }}
          <span class="text-muted-foreground font-normal">
           ({{ __('optional') }})
          </span>
         </label>
         <div class="grow">
          <input
           type="text"
           name="country"
           id="country"
           value="{{ old('country', $family->country) }}"
           placeholder="{{ __('e.g. United States') }}"
           class="kt-input w-full"
           aria-invalid="{{ $errors->has('country') ? 'true' : 'false' }}"
          />
          @error('country')
           <p class="kt-form-message mt-1">{{ $message }}</p>
          @enderror
         </div>
        </div>

        {{-- Description --}}
        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
         <label for="description" class="kt-form-label max-w-56">
          {{ __('Description') }}
          <span class="text-muted-foreground font-normal">
           ({{ __('optional') }})
          </span>
         </label>
         <div class="grow">
          <textarea
           name="description"
           id="description"
           rows="4"
           placeholder="{{ __('Short description of the family') }}"
           class="kt-textarea min-h-[120px] resize-y w-full"
           aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
          >{{ old('description', $family->description) }}</textarea>
          @error('description')
           <p class="kt-form-message mt-1">{{ $message }}</p>
          @enderror
         </div>
        </div>

        {{-- Status --}}
        <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
         <label for="status" class="kt-form-label max-w-56">
          {{ __('Status') }}
         </label>
         <div class="grow">
          <select
           name="status"
           id="status"
           class="kt-select w-full"
           aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
          >
           <option value="active" {{ old('status', $family->status) === 'active' ? 'selected' : '' }}>
            {{ __('Active') }}
           </option>
           <option value="archived" {{ old('status', $family->status) === 'archived' ? 'selected' : '' }}>
            {{ __('Archived') }}
           </option>
          </select>
          @error('status')
           <p class="kt-form-message mt-1">{{ $message }}</p>
          @enderror
         </div>
        </div>

        <div class="flex justify-end pt-2 gap-2">
         <x-fin-back-link href="{{ route('families.index') }}" class="!mb-0">{{ __('Back to families') }}</x-fin-back-link>
         <button type="submit" class="kt-btn kt-btn-primary inline-flex items-center gap-2">
          <i class="ki-filled ki-check"></i>
          <span>{{ __('Save changes') }}</span>
         </button>
        </div>
       </div>
      </div>
     </div>
    </div>

    {{-- Right: family overview / help --}}
    <div class="flex flex-col gap-5 lg:gap-7.5">
     <div class="kt-card">
      <div class="kt-card-content py-7.5 px-5 flex flex-col gap-3">
       <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary" aria-hidden="true">
         <i class="ki-filled ki-family text-xl leading-none"></i>
        </span>
        <div class="flex flex-col gap-0.5">
         <span class="text-sm font-semibold text-mono">
          {{ __('Family overview') }}
         </span>
         <span class="text-xs text-secondary-foreground">
          {{ __('High level information about this family workspace.') }}
         </span>
        </div>
       </div>

       <div class="border-b border-border my-2"></div>

       <dl class="grid gap-2 text-xs text-secondary-foreground">
        <div class="flex items-center justify-between gap-2">
         <dt class="font-medium">
          {{ __('Current status') }}
         </dt>
         <dd>
          @if ($family->status === 'archived')
           <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-destructive">
            {{ __('Archived') }}
           </span>
          @else
           <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">
            {{ __('Active') }}
           </span>
          @endif
         </dd>
        </div>
        <div class="flex items-center justify-between gap-2">
         <dt class="font-medium">
          {{ __('Currency') }}
         </dt>
         <dd class="text-foreground">
          {{ $family->currency_code }}
         </dd>
        </div>
        <div class="flex items-center justify-between gap-2">
         <dt class="font-medium">
          {{ __('Timezone') }}
         </dt>
         <dd class="text-foreground truncate max-w-[180px] text-right">
          {{ $family->timezone }}
         </dd>
        </div>
       </dl>
      </div>
     </div>

     <div class="kt-card">
      <div class="kt-card-content py-7.5 px-5 flex flex-col gap-3">
       <span class="text-sm font-semibold text-mono">
        {{ __('Tips for a clear family profile') }}
       </span>
       <ul class="list-disc ps-5 text-xs text-secondary-foreground space-y-1.5">
        <li>{{ __('Use a descriptive family name so members can easily recognise it.') }}</li>
        <li>{{ __('Set the correct currency and timezone to keep reports and budgets accurate.') }}</li>
        <li>{{ __('Add a short description to explain how this family workspace is used.') }}</li>
       </ul>
      </div>
     </div>
    </div>
   </div>
  </form>
 </div>
@endsection
