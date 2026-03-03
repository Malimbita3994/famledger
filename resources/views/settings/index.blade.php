@extends('layouts.metronic')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('content')
 <div class="pb-5">
  <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
   <div class="flex flex-col gap-1">
    <h1 class="font-medium text-lg text-foreground">
     Settings overview
    </h1>
    <p class="text-sm text-secondary-foreground">
     Manage your FamLedger profile, family configuration, categories, notifications and audit log from one place.
    </p>
   </div>
  </div>
 </div>

 <style>
  @media (min-width: 0px) {
      .settings-grid {
          display: grid;
          grid-template-columns: repeat(1, minmax(0, 1fr));
      }
  }

  @media (min-width: 900px) {
      .settings-grid {
          grid-template-columns: repeat(3, minmax(0, 1fr));
      }
  }
 </style>

 <div class="kt-container-fixed pb-6">
  <div class="settings-grid gap-5 lg:gap-7.5">
  {{-- Profile --}}
  <div class="kt-card p-5 lg:p-7.5 lg:pt-7">
    <div class="flex flex-col gap-4">
     <div class="flex items-center justify-between gap-2">
      <i class="ki-filled ki-profile-circle text-2xl text-primary"></i>
     </div>
     <div class="flex flex-col gap-3">
      <a href="{{ route('profile.edit') }}" class="text-base font-medium leading-none text-foreground hover:text-primary">
       Profile
      </a>
      <span class="text-sm text-secondary-foreground leading-5">
       Update your personal details, email and password used to access FamLedger.
      </span>
     </div>
    </div>
   </div>

  {{-- Family profile --}}
  <div class="kt-card p-5 lg:p-7.5 lg:pt-7">
    <div class="flex flex-col gap-4">
     <div class="flex items-center justify-between gap-2">
      <i class="ki-filled ki-setting-2 text-2xl text-primary"></i>
     </div>
     <div class="flex flex-col gap-3">
      <a
       href="{{ isset($currentFamily) ? route('families.edit', $currentFamily) : route('families.index') }}"
       class="text-base font-medium leading-none text-foreground hover:text-primary"
      >
       Family profile
      </a>
      <span class="text-sm text-secondary-foreground leading-5">
       Configure your family name, default currency, timezone and other core preferences.
      </span>
     </div>
    </div>
   </div>

  {{-- Categories (System admin only) --}}
  @if (auth()->user() && auth()->user()->hasRole('Super Admin'))
  <div class="kt-card p-5 lg:p-7.5 lg:pt-7">
    <div class="flex flex-col gap-4">
     <div class="flex items-center justify-between gap-2">
      <i class="ki-filled ki-category text-2xl text-primary"></i>
     </div>
     <div class="flex flex-col gap-3">
      <a href="{{ route('settings.categories') }}" class="text-base font-medium leading-none text-foreground hover:text-primary">
       Categories
      </a>
      <span class="text-sm text-secondary-foreground leading-5">
       Define income and expense categories to keep reports, budgets and savings goals organised.
      </span>
      <span class="text-xs text-muted-foreground">
       Detailed category management coming soon.
      </span>
     </div>
    </div>
   </div>
  @endif

  {{-- Notifications --}}
  <div class="kt-card p-5 lg:p-7.5 lg:pt-7">
    <div class="flex flex-col gap-4">
     <div class="flex items-center justify-between gap-2">
      <i class="ki-filled ki-notification-on text-2xl text-primary"></i>
     </div>
     <div class="flex flex-col gap-3">
      <a href="{{ route('settings.notifications') }}" class="text-base font-medium leading-none text-foreground hover:text-primary">
       Notifications
      </a>
      <span class="text-sm text-secondary-foreground leading-5">
       Control email alerts about new members, project updates, budget thresholds and savings progress.
      </span>
      <span class="text-xs text-muted-foreground">
       Notification channels and rules will be configurable here.
      </span>
     </div>
    </div>
   </div>

  {{-- Audit log (System admin only) --}}
  @if (auth()->user() && auth()->user()->hasRole('Super Admin'))
  <div class="kt-card p-5 lg:p-7.5 lg:pt-7">
    <div class="flex flex-col gap-4">
     <div class="flex items-center justify-between gap-2">
      <i class="ki-filled ki-document text-2xl text-primary"></i>
     </div>
     <div class="flex flex-col gap-3">
      <a href="{{ route('settings.audit-log') }}" class="text-base font-medium leading-none text-foreground hover:text-primary">
       Audit log
      </a>
      <span class="text-sm text-secondary-foreground leading-5">
       Review who changed what across wallets, budgets, projects and members for this family.
      </span>
      <span class="text-xs text-muted-foreground">
       Detailed audit views will appear here as the feature is implemented.
      </span>
     </div>
    </div>
   </div>

   {{-- Property configuration (System admin only) --}}
   <div class="kt-card p-5 lg:p-7.5 lg:pt-7">
    <div class="flex flex-col gap-4">
     <div class="flex items-center justify-between gap-2">
      <i class="ki-filled ki-home-3 text-2xl text-primary"></i>
     </div>
     <div class="flex flex-col gap-3">
      <a href="{{ route('settings.property.index') }}" class="text-base font-medium leading-none text-foreground hover:text-primary">
       Property configuration
      </a>
      <span class="text-sm text-secondary-foreground leading-5">
       Manage property categories and dynamic attributes used across all family properties.
      </span>
      <span class="text-xs text-muted-foreground">
       System-wide configuration, available only to platform administrators.
      </span>
     </div>
    </div>
   </div>
  @endif
  </div>
 </div>
@endsection

