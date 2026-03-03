@extends('layouts.metronic')

@section('title', __('Notifications'))
@section('page_title', __('Notifications'))

@section('content')
 <div class="pb-5">
  <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
   <div class="flex items-center flex-wrap gap-1 lg:gap-5">
    <h1 class="font-medium text-lg text-mono">
     {{ __('Notifications') }}
    </h1>
    <div class="flex items-center gap-1 text-sm font-normal">
     <a href="{{ route('dashboard') }}" class="text-secondary-foreground hover:text-primary">
      {{ __('Home') }}
     </a>
     <span class="text-muted-foreground text-sm">
      /
     </span>
     <span class="text-secondary-foreground">
      {{ __('Settings') }}
     </span>
     <span class="text-muted-foreground text-sm">
      /
     </span>
     <span class="text-mono">
      {{ __('Notifications') }}
     </span>
    </div>
   </div>
   <div class="flex items-center flex-wrap gap-1.5 lg:gap-3.5">
    <a href="{{ route('settings.index') }}" class="kt-btn kt-btn-outline">
     <i class="ki-filled ki-left text-base"></i>
     <span>{{ __('Back to settings') }}</span>
    </a>
   </div>
  </div>
 </div>

 <div class="kt-container-fixed pb-6">
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
   <div class="col-span-2">
    <div class="flex flex-col gap-5 lg:gap-7.5">
     {{-- Notification channels --}}
     <div class="kt-card">
      <div class="kt-card-header gap-2">
       <h3 class="kt-card-title">
        {{ __('Notification channels') }}
       </h3>
       <div class="flex items-center gap-2">
        <label class="kt-label">
         {{ __('Family alerts') }}
         <input class="kt-switch kt-switch-sm" name="team_wide_alerts" type="checkbox" value="1" checked />
        </label>
       </div>
      </div>
      <div id="notifications_channels">
       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-sms text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Email') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ auth()->user()->email }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <a href="#" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-primary kt-btn-ghost">
          <i class="ki-filled ki-notepad-edit"></i>
         </a>
         <div class="flex items-center gap-2.5">
          <input checked class="kt-switch" name="channel_email_enabled" type="checkbox" value="1" />
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-phone text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Mobile') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Add a mobile number in your profile to receive SMS alerts.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <a href="#" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-primary kt-btn-ghost">
          <i class="ki-filled ki-notepad-edit"></i>
         </a>
         <div class="flex items-center gap-2.5">
          <input class="kt-switch" name="channel_mobile_enabled" type="checkbox" value="1" />
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-slack text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Slack') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Receive FamLedger alerts for budgets, invoices and activity directly in Slack.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <a href="#" class="kt-btn kt-btn-outline text-center">
           {{ __('Connect Slack') }}
          </a>
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-screen text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Desktop') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Enable browser notifications for real-time desktop alerts.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <input checked class="kt-switch" name="channel_desktop_enabled" type="checkbox" value="1" />
         </div>
        </div>
       </div>
      </div>
     </div>

     {{-- Other notifications --}}
     <div class="kt-card">
      <div class="kt-card-header gap-2">
       <h3 class="kt-card-title">
        {{ __('Other notifications') }}
       </h3>
       <div class="flex items-center gap-2">
        <label class="kt-label">
         {{ __('Family alerts') }}
         <input class="kt-switch kt-switch-sm" name="family_wide_alerts" type="checkbox" value="1" checked />
        </label>
       </div>
      </div>
      <div id="notifications_other">
       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-tab-tablet text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Task alert') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Notification when a task is assigned to you.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <input checked class="kt-switch kt-switch-sm" name="notify_task_assigned" type="checkbox" value="1" />
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-dollar text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Budget warning') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Get notified when nearing a budget limit.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <input checked class="kt-switch kt-switch-sm" name="notify_budget_warning" type="checkbox" value="1" />
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-cheque text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Invoice alert') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Alert for new and unpaid invoices.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <a href="#" class="kt-btn kt-btn-outline text-center">
           {{ __('View invoices') }}
          </a>
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-message-text text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Feedback alert') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('When a member or client submits new feedback.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <input checked class="kt-switch kt-switch-sm" name="notify_feedback" type="checkbox" value="1" />
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-people text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Collaboration request') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Invite to collaborate on a new document or budget.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <input checked class="kt-switch kt-switch-sm" name="notify_collaboration" type="checkbox" value="1" />
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-abstract-42 text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Meeting reminder') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Reminder of scheduled meetings for the day.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <a href="#" class="kt-btn kt-btn-outline">
           {{ __('Show meetings') }}
          </a>
         </div>
        </div>
       </div>

       <div class="kt-card-group flex items-center justify-between py-4 gap-2.5">
        <div class="flex items-center gap-3.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-tablet-ok text-xl text-muted-foreground"></i>
          </div>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
           {{ __('Status change') }}
          </span>
          <span class="text-sm text-secondary-foreground">
           {{ __('Notifies you when a project or task status changes.') }}
          </span>
         </div>
        </div>
        <div class="flex items-center gap-2 lg:gap-5">
         <div class="flex items-center gap-2.5">
          <input checked class="kt-switch kt-switch-sm" name="notify_status_change" type="checkbox" value="1" />
         </div>
        </div>
       </div>
      </div>
     </div>

     {{-- FAQ --}}
     <div class="kt-card">
      <div class="kt-card-header">
       <h3 class="kt-card-title">
        {{ __('FAQ') }}
       </h3>
      </div>
      <div class="kt-card-content py-3">
       <div data-kt-accordion="true" data-kt-accordion-expand-all="true">
        <div class="kt-accordion-item not-last:border-b border-b-border" data-kt-accordion-item="true">
         <button aria-controls="faq_1_content" class="kt-accordion-toggle py-4" data-kt-accordion-toggle="#faq_1_content">
          <span class="text-base text-mono">
           {{ __('How are notification emails batched?') }}
          </span>
          <span class="kt-accordion-active:hidden inline-flex">
           <i class="ki-filled ki-plus text-muted-foreground text-sm"></i>
          </span>
          <span class="kt-accordion-active:inline-flex hidden">
           <i class="ki-filled ki-minus text-muted-foreground text-sm"></i>
          </span>
         </button>
         <div class="kt-accordion-content hidden" id="faq_1_content">
          <div class="text-secondary-foreground text-base pb-4">
           {{ __('FamLedger groups low‑priority notifications into summary emails to avoid inbox noise, while critical alerts (like failed payments or budget breaches) are sent immediately.') }}
          </div>
         </div>
        </div>
        <div class="kt-accordion-item not-last:border-b border-b-border" data-kt-accordion-item="true">
         <button aria-controls="faq_2_content" class="kt-accordion-toggle py-4" data-kt-accordion-toggle="#faq_2_content">
          <span class="text-base text-mono">
           {{ __('Can I disable all notifications temporarily?') }}
          </span>
          <span class="kt-accordion-active:hidden inline-flex">
           <i class="ki-filled ki-plus text-muted-foreground text-sm"></i>
          </span>
          <span class="kt-accordion-active:inline-flex hidden">
           <i class="ki-filled ki-minus text-muted-foreground text-sm"></i>
          </span>
         </button>
         <div class="kt-accordion-content hidden" id="faq_2_content">
          <div class="text-secondary-foreground text-base pb-4">
           {{ __('Use the “Do not disturb” card on the right to pause all notifications for a period of time without changing your saved preferences.') }}
          </div>
         </div>
        </div>
        <div class="kt-accordion-item not-last:border-b border-b-border" data-kt-accordion-item="true">
         <button aria-controls="faq_3_content" class="kt-accordion-toggle py-4" data-kt-accordion-toggle="#faq_3_content">
          <span class="text-base text-mono">
           {{ __('Do notification settings apply per family or per account?') }}
          </span>
          <span class="kt-accordion-active:hidden inline-flex">
           <i class="ki-filled ki-plus text-muted-foreground text-sm"></i>
          </span>
          <span class="kt-accordion-active:inline-flex hidden">
           <i class="ki-filled ki-minus text-muted-foreground text-sm"></i>
          </span>
         </button>
         <div class="kt-accordion-content hidden" id="faq_3_content">
          <div class="text-secondary-foreground text-base pb-4">
           {{ __('Most settings are per account, but some alerts (like family budget thresholds) apply only to the currently active family.') }}
          </div>
         </div>
        </div>
        <div class="kt-accordion-item not-last:border-b border-b-border" data-kt-accordion-item="true">
         <button aria-controls="faq_4_content" class="kt-accordion-toggle py-4" data-kt-accordion-toggle="#faq_4_content">
          <span class="text-base text-mono">
           {{ __('Will changes here affect existing email rules in my inbox?') }}
          </span>
          <span class="kt-accordion-active:hidden inline-flex">
           <i class="ki-filled ki-plus text-muted-foreground text-sm"></i>
          </span>
          <span class="kt-accordion-active:inline-flex hidden">
           <i class="ki-filled ki-minus text-muted-foreground text-sm"></i>
          </span>
         </button>
         <div class="kt-accordion-content hidden" id="faq_4_content">
          <div class="text-secondary-foreground text-base pb-4">
           {{ __('No. FamLedger only controls what we send; any filters or rules in your email client will continue to work as you configured them.') }}
          </div>
         </div>
        </div>
        <div class="kt-accordion-item not-last:border-b border-b-border" data-kt-accordion-item="true">
         <button aria-controls="faq_5_content" class="kt-accordion-toggle py-4" data-kt-accordion-toggle="#faq_5_content">
          <span class="text-base text-mono">
           {{ __('Can owners enforce minimum alerts for all members?') }}
          </span>
          <span class="kt-accordion-active:hidden inline-flex">
           <i class="ki-filled ki-plus text-muted-foreground text-sm"></i>
          </span>
          <span class="kt-accordion-active:inline-flex hidden">
           <i class="ki-filled ki-minus text-muted-foreground text-sm"></i>
          </span>
         </button>
         <div class="kt-accordion-content hidden" id="faq_5_content">
          <div class="text-secondary-foreground text-base pb-4">
           {{ __('Primary owners can require certain high‑risk alerts (for example, large withdrawals) to remain enabled for all members.') }}
          </div>
         </div>
        </div>
        <div class="kt-accordion-item not-last:border-b border-b-border" data-kt-accordion-item="true">
         <button aria-controls="faq_6_content" class="kt-accordion-toggle py-4" data-kt-accordion-toggle="#faq_6_content">
          <span class="text-base text-mono">
           {{ __('Where can I see a history of notifications sent?') }}
          </span>
          <span class="kt-accordion-active:hidden inline-flex">
           <i class="ki-filled ki-plus text-muted-foreground text-sm"></i>
          </span>
          <span class="kt-accordion-active:inline-flex hidden">
           <i class="ki-filled ki-minus text-muted-foreground text-sm"></i>
          </span>
         </button>
         <div class="kt-accordion-content hidden" id="faq_6_content">
          <div class="text-secondary-foreground text-base pb-4">
           {{ __('The audit log (under Settings → Audit log) will gradually surface more notification activity as that feature is expanded.') }}
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>

     {{-- Contact support --}}
     <div class="kt-card">
      <div class="kt-card-content px-10 py-7.5 lg:pr-12.5">
       <div class="flex flex-wrap md:flex-nowrap items-center gap-6 md:gap-10">
        <div class="flex flex-col items-start gap-3">
         <h2 class="text-xl font-medium text-mono">
          {{ __('Contact support') }}
         </h2>
         <p class="text-sm text-foreground leading-5.5 mb-2.5">
          {{ __('Need assistance with alerts or delivery issues? Contact our support team for prompt, personalised help.') }}
         </p>
        </div>
        <img alt="image" class="dark:hidden max-h-[150px]" src="{{ asset('assets/media/illustrations/31.svg') }}" />
        <img alt="image" class="light:hidden max-h-[150px]" src="{{ asset('assets/media/illustrations/31-dark.svg') }}" />
       </div>
      </div>
      <div class="kt-card-footer justify-center">
       <a class="kt-link kt-link-underlined kt-link-dashed" href="#">
        {{ __('Contact support') }}
       </a>
      </div>
     </div>
    </div>
   </div>

   <div class="col-span-1">
    <div class="flex flex-col gap-5 lg:gap-7.5">
     {{-- Do not disturb --}}
     <div class="kt-card">
      <div class="kt-card-header">
       <h3 class="kt-card-title">
        {{ __('Do not disturb') }}
       </h3>
      </div>
      <div class="kt-card-content flex flex-col gap-2.5">
       <p class="text-sm text-secondary-foreground">
        {{ __('Activate “Do not disturb” to temporarily silence all notifications and focus without interruptions during specified hours or tasks.') }}
       </p>
       <div>
        <a class="kt-link kt-link-underlined kt-link-dashed" href="#">
         {{ __('Learn more') }}
        </a>
       </div>
      </div>
      <div class="kt-card-footer justify-center">
       <a class="kt-btn kt-btn-outline" href="#">
        <i class="ki-filled ki-notification-bing"></i>
        {{ __('Pause notifications') }}
       </a>
      </div>
     </div>

     {{-- Educational cards --}}
     <div class="kt-card">
      <div class="kt-card-content py-10 flex flex-col gap-5 lg:gap-7.5">
       <div class="flex flex-col items-start gap-2.5">
        <div class="mb-2.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-primary/10 fill-primary-soft" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-notification-on text-xl ps-px text-primary"></i>
          </div>
         </div>
        </div>
        <a class="text-base font-semibold text-mono hover:text-primary" href="#">
         {{ __('Streamlined alerts setup: custom notification preferences') }}
        </a>
        <p class="text-sm text-secondary-foreground">
         {{ __('Tailor your alert preferences so you only receive the notifications that matter most to your family and budgets.') }}
        </p>
        <a class="kt-link kt-link-underlined kt-link-dashed" href="#">
         {{ __('Learn more') }}
        </a>
       </div>
       <span class="hidden not-last:block not-last:border-b border-b-border"></span>
       <div class="flex flex-col items-start gap-2.5">
        <div class="mb-2.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-primary/10 fill-primary-soft" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-message-notify text-xl ps-px text-primary"></i>
          </div>
         </div>
        </div>
        <a class="text-base font-semibold text-mono hover:text-primary" href="#">
         {{ __('Effective communication: instant notification tools') }}
        </a>
        <p class="text-sm text-secondary-foreground">
         {{ __('Ensure timely communication with instant alerts across email, mobile, Slack and desktop.') }}
        </p>
        <a class="kt-link kt-link-underlined kt-link-dashed" href="#">
         {{ __('Learn more') }}
        </a>
       </div>
       <span class="hidden not-last:block not-last:border-b border-b-border"></span>
       <div class="flex flex-col items-start gap-2.5">
        <div class="mb-2.5">
         <div class="relative size-[50px] shrink-0">
          <svg class="w-full h-full stroke-primary/10 fill-primary-soft" fill="none" height="48" viewBox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
           <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
           <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
          </svg>
          <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
           <i class="ki-filled ki-notification-status text-xl ps-px text-primary"></i>
          </div>
         </div>
        </div>
        <a class="text-base font-semibold text-mono hover:text-primary" href="#">
         {{ __('Personalised updates: smart alert system') }}
        </a>
        <p class="text-sm text-secondary-foreground">
         {{ __('Control how you receive updates with a smart alert system tuned to your financial routines.') }}
        </p>
        <a class="kt-link kt-link-underlined kt-link-dashed" href="#">
         {{ __('Learn more') }}
        </a>
       </div>
       <span class="hidden not-last:block not-last:border-b border-b-border"></span>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
@endsection

