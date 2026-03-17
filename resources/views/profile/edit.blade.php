@extends('layouts.metronic')

@section('title', __('Profile'))
@section('page_title', __('Profile'))

@section('content')
 <div class="pb-5">
  <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
   <div class="flex flex-col gap-1">
    <h1 class="font-medium text-lg text-foreground">
     {{ __('User profile') }}
    </h1>
    <p class="text-sm text-secondary-foreground">
     {{ __('Manage your FamLedger account information, password and account safety from one place.') }}
    </p>
   </div>
   <div class="flex items-center gap-2">
    <a href="{{ route('settings.index') }}" class="kt-btn kt-btn-outline">
     <i class="ki-filled ki-left text-base"></i>
     <span>{{ __('Back to settings') }}</span>
    </a>
   </div>
  </div>
 </div>

<div class="kt-container-fixed pb-6">
 <div class="grid gap-5 lg:gap-7.5">
  {{-- Top row: personal info & password side by side on large screens --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
   {{-- Personal info --}}
   <div class="kt-card">
     <div class="kt-card-header items-center justify-between">
      <h3 class="kt-card-title">
       {{ __('Personal info') }}
      </h3>
      <span class="text-xs text-muted-foreground">
       {{ __('Basic details about your account.') }}
      </span>
     </div>
     <div class="kt-card-content px-5 pb-5">
      <form method="post" action="{{ route('profile.update') }}" class="grid gap-4 lg:gap-5 max-w-xl">
       @csrf
       @method('patch')

       {{-- Avatar placeholder --}}
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
         <span class="text-xs text-secondary-foreground">
          {{ __('Avatar or profile image upload coming soon') }}
         </span>
        </div>
       </div>

       {{-- Name --}}
       <div class="grid gap-1.5">
        <label for="name" class="kt-form-label text-sm text-secondary-foreground">
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

       {{-- Email --}}
       <div class="grid gap-1.5">
        <label for="email" class="kt-form-label text-sm text-secondary-foreground">
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
          <p class="text-xs text-secondary-foreground">
           {{ __('Your email address is unverified.') }}
          </p>
          <form id="send-verification" method="post" action="{{ route('verification.send') }}">
           @csrf
           <button
            type="submit"
            class="kt-link kt-link-underlined kt-link-dashed text-xs"
           >
            {{ __('Click here to re-send the verification email.') }}
           </button>
          </form>
          @if (session('status') === 'verification-link-sent')
           <p class="text-xs text-green-600">
            {{ __('A new verification link has been sent to your email address.') }}
           </p>
          @endif
         </div>
        @endif
       </div>

       <div class="flex items-center gap-3 pt-1">
        <button type="submit" class="kt-btn kt-btn-primary">
         {{ __('Save changes') }}
        </button>
        @if (session('status') === 'profile-updated')
         <span class="text-xs text-muted-foreground">
          {{ __('Saved.') }}
         </span>
        @endif
       </div>
      </form>
     </div>
    </div>

   {{-- Password --}}
   <div class="kt-card">
     <div class="kt-card-header items-center justify-between">
      <h3 class="kt-card-title">
       {{ __('Password & security') }}
      </h3>
      <span class="text-xs text-muted-foreground">
       {{ __('Keep your account secured with a strong password.') }}
      </span>
     </div>
     <div class="kt-card-content px-5 pb-5">
      <form method="post" action="{{ route('password.update') }}" class="grid gap-4 lg:gap-5 max-w-xl">
       @csrf
       @method('put')

       <div class="grid gap-1.5">
        <label for="current_password" class="kt-form-label text-sm text-secondary-foreground">
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
        <label for="password" class="kt-form-label text-sm text-secondary-foreground">
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
        <label for="password_confirmation" class="kt-form-label text-sm text-secondary-foreground">
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

       <div class="flex items-center gap-3 pt-1">
        <button type="submit" class="kt-btn kt-btn-primary">
         {{ __('Update password') }}
        </button>
        @if (session('status') === 'password-updated')
         <span class="text-xs text-muted-foreground">
          {{ __('Saved.') }}
         </span>
        @endif
       </div>
      </form>
     </div>
    </div>
   </div>
  </div>

  {{-- Bottom row: account danger zone --}}
  <div class="kt-card border-destructive/40">
   <div class="kt-card-header items-center justify-between">
    <h3 class="kt-card-title text-destructive">
     {{ __('Danger zone') }}
    </h3>
    <span class="text-xs text-muted-foreground">
     {{ __('Delete your account and all related data.') }}
    </span>
   </div>
   <div class="kt-card-content px-5 pb-5">
    @include('profile.partials.delete-user-form')
   </div>
  </div>
 </div>
</div>
@endsection

