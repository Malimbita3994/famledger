@extends('layouts.metronic')

@section('title', __('Profile'))
@section('page_title', __('Profile'))

@section('content')
<style>
  /* Ensure profile avatar never overflows */
  .profile-avatar-wrap {
    width: 80px;
    height: 80px;
    min-width: 80px;
    max-width: 80px;
    min-height: 80px;
    max-height: 80px;
    overflow: hidden;
    border-radius: 50%;
    flex-shrink: 0;
  }
  .profile-avatar-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
  }
</style>
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
      <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid gap-4 lg:gap-5 max-w-xl">
       @csrf
       @method('patch')

       {{-- Profile picture --}}
       <div class="flex items-center gap-4 flex-wrap">
        <div class="profile-avatar-wrap relative border-2 border-border bg-muted/30" style="width:80px;height:80px;min-width:80px;max-width:80px;min-height:80px;max-height:80px;overflow:hidden;border-radius:50%;">
         @if($user->avatar_url)
          <img src="{{ $user->avatar_url }}" alt="" id="profile-avatar-preview"/>
         @else
          <div class="absolute inset-0 flex items-center justify-center rounded-full bg-primary/10 text-primary font-semibold" id="profile-avatar-initials">
           <span class="text-lg">{{ strtoupper(mb_substr($user->name, 0, 2)) }}</span>
          </div>
         @endif
        </div>
        <div class="flex flex-col gap-2 min-w-0 flex-1">
         <span class="text-sm font-medium text-foreground">{{ $user->name }}</span>
         <label for="avatar-input" class="kt-btn kt-btn-sm kt-btn-outline cursor-pointer inline-flex items-center gap-1.5 w-fit">
          <i class="ki-filled ki-picture text-base"></i>
          <span>{{ __('Change photo') }}</span>
         </label>
         <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif" class="hidden" id="avatar-input" aria-label="{{ __('Change profile photo') }}"/>
         <span class="text-xs text-muted-foreground">{{ __('JPG, PNG or GIF. Max 2 MB.') }}</span>
         @if($user->avatar_url)
          <button type="submit" form="profile-form-remove-avatar" class="kt-link kt-link-underlined kt-link-dashed text-xs text-muted-foreground hover:text-destructive w-fit text-left">
           {{ __('Remove photo') }}
          </button>
         @endif
         @error('avatar')
          <p class="text-xs text-destructive">{{ $message }}</p>
         @enderror
        </div>
       </div>
       @if($user->avatar_url)
       <form id="profile-form-remove-avatar" method="post" action="{{ route('profile.avatar.destroy') }}" class="hidden">
        @csrf
        @method('delete')
       </form>
       @endif

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

@push('scripts')
<script>
(function() {
  var input = document.getElementById('avatar-input');
  var preview = document.getElementById('profile-avatar-preview');
  var initials = document.getElementById('profile-avatar-initials');
  if (!input) return;
  input.addEventListener('change', function(e) {
    var file = e.target.files && e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function() {
      if (preview) {
        preview.src = reader.result;
      } else if (initials) {
        var img = document.createElement('img');
        img.id = 'profile-avatar-preview';
        img.className = 'w-full h-full object-cover object-center';
        img.alt = '';
        img.src = reader.result;
        initials.parentNode.replaceChild(img, initials);
      }
    };
    reader.readAsDataURL(file);
  });
})();
</script>
@endpush
@endsection

