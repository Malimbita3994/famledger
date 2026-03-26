@extends('layouts.metronic')

@section('title', __('Notifications'))
@section('page_title', __('Notifications'))

@push('styles')
<style>
    .notif-pulse-page.admin-pulse-page {
        --ap-accent: #009ef7;
        --ap-accent-2: #0ea5e9;
    }
    .admin-pulse-eyebrow {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .admin-pulse-title {
        font-size: clamp(1.35rem, 2.8vw, 1.7rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: var(--ap-accent);
    }
    .admin-pulse-breadcrumb a {
        color: #64748b;
        transition: color 0.2s ease;
    }
    .admin-pulse-breadcrumb a:hover {
        color: var(--ap-accent);
    }
    .notif-pulse-page .notif-pulse-shell.admin-pulse-frame {
        max-width: none;
        margin-left: 0;
        margin-right: 0;
    }
    .admin-pulse-frame {
        padding: 3px;
        border-radius: 24px;
        background: linear-gradient(
            135deg,
            rgba(0, 158, 247, 0.42) 0%,
            rgba(255, 255, 255, 0.96) 46%,
            rgba(14, 165, 233, 0.3) 100%
        );
        box-shadow:
            0 4px 24px rgba(0, 158, 247, 0.12),
            0 24px 48px rgba(15, 23, 42, 0.08);
        width: 100%;
    }
    .admin-pulse-card-inner {
        background: #fff;
        border-radius: 21px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }
    .dark .admin-pulse-card-inner {
        background: rgb(15 23 42 / 0.96);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    .admin-pulse-btn-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 1.15rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        background: rgba(255, 255, 255, 0.95);
        color: #334155 !important;
        text-decoration: none !important;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }
    .admin-pulse-btn-outline:hover {
        border-color: var(--ap-accent);
        background: rgba(0, 158, 247, 0.06);
        box-shadow: 0 0 0 1px rgba(0, 158, 247, 0.12);
    }
    .dark .admin-pulse-btn-outline {
        background: rgba(30, 41, 59, 0.9);
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.35);
    }
    .admin-pulse-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 1.25rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 12px;
        color: #fff !important;
        border: none;
        cursor: pointer;
        background: linear-gradient(135deg, var(--ap-accent) 0%, var(--ap-accent-2) 100%);
        box-shadow: 0 4px 14px rgba(0, 158, 247, 0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .admin-pulse-btn-primary:hover {
        filter: brightness(1.05);
        box-shadow: 0 6px 20px rgba(0, 158, 247, 0.42);
        transform: translateY(-1px);
    }
    .settings-notifications-page.notif-pulse-page #notification_settings_tabs.kt-tabs-line .notif-settings-tab.active,
    .settings-notifications-page.notif-pulse-page #notification_settings_tabs.kt-tabs-line .notif-settings-tab[aria-selected='true'] {
        color: var(--ap-accent);
        border-bottom-color: var(--ap-accent);
    }
    .settings-notifications-page.notif-pulse-page #notif_faq_subtabs_tabs.kt-tabs-line .notif-faq-subtab.active,
    .settings-notifications-page.notif-pulse-page #notif_faq_subtabs_tabs.kt-tabs-line .notif-faq-subtab[aria-selected='true'] {
        color: var(--ap-accent);
        border-bottom-color: var(--ap-accent);
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-pulse-btn-primary:hover {
            transform: none;
        }
    }
</style>
 @if ($canManageNotificationPage)
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet"/>
  <style>
   .faq-quill-editor .ql-toolbar { border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; border-color: var(--tw-border-opacity, 1); }
   .faq-quill-editor .ql-container { border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; font-size: 0.875rem; border-color: var(--tw-border-opacity, 1); }
   .faq-quill-editor .ql-editor { min-height: 5.5rem; line-height: 1.55; }
   .faq-quill-editor.faq-quill-question .ql-editor { min-height: 3.25rem; }
   .faq-quill-editor.faq-quill-answer .ql-editor { min-height: 10rem; }
   .dark .faq-quill-editor .ql-stroke { stroke: #cbd5e1; }
   .dark .faq-quill-editor .ql-fill { fill: #cbd5e1; }
   .dark .faq-quill-editor .ql-picker { color: #e2e8f0; }
   .dark .faq-quill-editor .ql-toolbar, .dark .faq-quill-editor .ql-container { border-color: rgba(148, 163, 184, 0.35); background: rgba(15, 23, 42, 0.35); }
   /* Metronic may not include Tailwind `sr-only`; Quill writes `<p><br></p>` into the sync field when empty — hide it. */
   textarea.faq-quill-sync-field {
    display: none !important;
   }
  </style>
 @endif
@endpush

@section('content')
 @if (session('success'))
  <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-3">
   <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-sm text-green-800 dark:text-green-200">
    {{ session('success') }}
   </div>
  </div>
 @endif
 @if (session('error'))
  <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-3">
   <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm text-red-800 dark:text-red-200">
    {{ session('error') }}
   </div>
  </div>
 @endif
 @if ($errors->any())
  <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-3">
   <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-4 py-3 text-sm text-amber-900 dark:text-amber-100">
    <p class="font-medium mb-1">{{ __('Please fix the following:') }}</p>
    <ul class="list-disc ps-5 space-y-0.5">
     @foreach ($errors->all() as $message)
      <li>{{ $message }}</li>
     @endforeach
    </ul>
   </div>
  </div>
 @endif
 <div class="settings-pulse settings-notifications-page admin-pulse-page notif-pulse-page">
    <div class="famledger-page-header">
        <div class="kt-container-fixed flex flex-row flex-wrap items-start gap-4 w-full min-w-0 px-4 sm:px-6 lg:px-8">
            <div class="min-w-0 flex-1">
                <p class="admin-pulse-eyebrow mb-1.5">{{ __('Settings') }}</p>
                <h1 class="admin-pulse-title">{{ __('Notifications') }}</h1>
                <div class="admin-pulse-breadcrumb flex items-center flex-wrap gap-1 text-sm font-normal mt-2">
                    <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <a href="{{ route('settings.index') }}">{{ __('Settings') }}</a>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground font-medium">{{ __('Notifications') }}</span>
                </div>
            </div>
            <div class="shrink-0 ms-auto">
                <a href="{{ route('settings.index') }}" class="admin-pulse-btn-outline inline-flex">
                    <i class="ki-filled ki-left text-base"></i>
                    {{ __('Back to settings') }}
                </a>
            </div>
        </div>
    </div>

 <div class="kt-container-fixed px-4 sm:px-6 lg:px-8 pb-14 settings-notifications-main">
  {{-- Standalone form (inputs use form= attribute) so FAQ / page-content forms can live inside tabs --}}
  <form method="post" action="{{ route('settings.notifications.update') }}" id="notification_settings_form" class="absolute start-0 top-0 h-px w-px overflow-hidden opacity-0 pointer-events-none" tabindex="-1" aria-hidden="true">
   @csrf
   @method('PUT')
   <button type="submit" tabindex="-1">{{ __('Save preferences') }}</button>
  </form>
   @php
    $dndUntilInput = '';
    if (old('dnd_until') !== null && old('dnd_until') !== '') {
        $dndUntilInput = old('dnd_until');
    } elseif (! empty($prefs['dnd_until'] ?? null)) {
        try {
            $dndUntilInput = \Illuminate\Support\Carbon::parse($prefs['dnd_until'])->timezone(config('app.timezone'))->format('Y-m-d\TH:i');
        } catch (\Throwable $e) {
            $dndUntilInput = '';
        }
    }
    $dndScheduled = false;
    if (! empty($prefs['dnd_until'] ?? null)) {
        try {
            $dndScheduled = \Illuminate\Support\Carbon::parse($prefs['dnd_until'])->isFuture();
        } catch (\Throwable $e) {
            $dndScheduled = false;
        }
    }
    $dndOn = ($prefs['dnd_enabled'] ?? false) && $dndScheduled;
   @endphp
  <style>
   /* Notification settings: own tab logic (avoid data-kt-tabs — conflicts with header drawer tabs) */
   /* Descendant (not only direct child): tab panels must show/hide even if markup nesting shifts */
   #notification_settings_tabs_root .notifications-settings-tab-panel[hidden] {
    display: none !important;
   }
   #notification_settings_tabs_root .notifications-settings-tab-panel:not([hidden]) {
    display: flex !important;
    flex-direction: column;
   }
   /* Line-tab look without .kt-tab-toggle (avoids KTUI binding clicks → blank panels). */
   #notification_settings_tabs.kt-tabs-line .notif-settings-tab {
    display: inline-flex;
    cursor: pointer;
    align-items: center;
    gap: calc(var(--spacing) * 2);
    border-bottom-style: var(--tw-border-style);
    border-bottom-width: 2px;
    border-bottom-color: transparent;
    padding-block: calc(var(--spacing) * 2);
    font-size: var(--text-sm);
    line-height: var(--tw-leading, var(--text-sm--line-height));
    --tw-font-weight: var(--font-weight-medium);
    font-weight: var(--font-weight-medium);
    color: var(--secondary-foreground);
    background: transparent;
   }
   @media (hover: hover) {
    #notification_settings_tabs.kt-tabs-line .notif-settings-tab:hover {
     color: var(--primary);
    }
   }
   #notification_settings_tabs.kt-tabs-line .notif-settings-tab.active,
   #notification_settings_tabs.kt-tabs-line .notif-settings-tab[aria-selected="true"] {
    color: var(--primary);
    border-bottom-color: var(--primary);
    --tw-font-weight: var(--font-weight-semibold);
    font-weight: var(--font-weight-semibold);
   }
   /* FAQ tab: inner horizontal subtabs (same line style as main settings tabs) */
   #notif_faq_subtabs_tabs.kt-tabs-line .notif-faq-subtab {
    display: inline-flex;
    cursor: pointer;
    align-items: center;
    gap: calc(var(--spacing) * 2);
    border-bottom-style: var(--tw-border-style);
    border-bottom-width: 2px;
    border-bottom-color: transparent;
    padding-block: calc(var(--spacing) * 2);
    font-size: var(--text-sm);
    line-height: var(--tw-leading, var(--text-sm--line-height));
    --tw-font-weight: var(--font-weight-medium);
    font-weight: var(--font-weight-medium);
    color: var(--secondary-foreground);
    background: transparent;
   }
   @media (hover: hover) {
    #notif_faq_subtabs_tabs.kt-tabs-line .notif-faq-subtab:hover {
     color: var(--primary);
    }
   }
   #notif_faq_subtabs_tabs.kt-tabs-line .notif-faq-subtab.active,
   #notif_faq_subtabs_tabs.kt-tabs-line .notif-faq-subtab[aria-selected="true"] {
    color: var(--primary);
    border-bottom-color: var(--primary);
    --tw-font-weight: var(--font-weight-semibold);
    font-weight: var(--font-weight-semibold);
   }
   #notif_faq_subtabs_root .notifications-faq-subpanel[hidden] {
    display: none !important;
   }
   #notif_faq_subtabs_root .notifications-faq-subpanel:not([hidden]) {
    display: flex !important;
    flex-direction: column;
   }
   /* Public FAQ accordion (read-only): calmer highlights, readable marks */
   #notif_tab_faq .notif-faq-accordion .faq-rich-content mark {
    padding: 0.12em 0.35em;
    border-radius: 0.25rem;
    background: color-mix(in srgb, var(--primary) 16%, transparent);
    color: inherit;
   }
   .dark #notif_tab_faq .notif-faq-accordion .faq-rich-content mark {
    background: color-mix(in srgb, var(--primary) 26%, transparent);
   }
  </style>
  <div class="admin-pulse-frame notif-pulse-shell min-w-0 max-w-full">
   <div class="admin-pulse-card-inner notif-pulse-card-inner min-w-0 overflow-hidden p-0">
   <div class="kt-card-content border-0 shadow-none bg-transparent rounded-none" id="notification_settings_tabs_root">
    <div class="kt-tabs kt-tabs-line w-full border-b border-border pb-1 -mb-1" id="notification_settings_tabs" role="tablist">
     <div class="settings-notif-tab-row">
      <button type="button" role="tab" id="notif_tab_btn_channels" class="notif-settings-tab py-2.5 px-3 text-sm active" data-notif-tab-target="notif_tab_channels" aria-selected="true" aria-controls="notif_tab_channels">
       {{ __('Notification channels') }}
      </button>
      <button type="button" role="tab" id="notif_tab_btn_other" class="notif-settings-tab py-2.5 px-3 text-sm" data-notif-tab-target="notif_tab_other" aria-selected="false" aria-controls="notif_tab_other">
       {{ __('Other notifications') }}
      </button>
      <button type="button" role="tab" id="notif_tab_btn_faq" class="notif-settings-tab py-2.5 px-3 text-sm" data-notif-tab-target="notif_tab_faq" aria-selected="false" aria-controls="notif_tab_faq">
       {{ __('FAQ') }}
      </button>
      <button type="button" role="tab" id="notif_tab_btn_contact" class="notif-settings-tab py-2.5 px-3 text-sm" data-notif-tab-target="notif_tab_contact" aria-selected="false" aria-controls="notif_tab_contact">
       {{ __('Contact support') }}
      </button>
      <button type="button" role="tab" id="notif_tab_btn_dnd" class="notif-settings-tab py-2.5 px-3 text-sm" data-notif-tab-target="notif_tab_dnd" aria-selected="false" aria-controls="notif_tab_dnd">
       {{ __('Do not disturb') }}
      </button>
     </div>
    </div>
    <div role="tabpanel" class="kt-tab-content notifications-settings-tab-panel active" id="notif_tab_channels" aria-labelledby="notif_tab_btn_channels">
     {{-- Notification channels --}}
     <div class="kt-card">
      <div class="kt-card-header gap-2">
       <h3 class="kt-card-title">
        {{ __('Notification channels') }}
       </h3>
       <div class="flex items-center gap-2">
        <label class="kt-label">
         {{ __('Family alerts') }}
         <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="team_wide_alerts" type="checkbox" value="1" @checked(old('team_wide_alerts', $prefs['team_wide_alerts'] ?? true)) />
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
         <a href="{{ route('profile.edit') }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-primary kt-btn-ghost" title="{{ __('Edit profile & email') }}">
          <i class="ki-filled ki-notepad-edit"></i>
         </a>
         <div class="flex items-center gap-2.5">
          <input form="notification_settings_form" class="kt-switch" name="channel_email_enabled" type="checkbox" value="1" @checked(old('channel_email_enabled', $prefs['channel_email_enabled'] ?? true)) />
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
         <a href="{{ route('profile.edit') }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-primary kt-btn-ghost" title="{{ __('Add phone in profile') }}">
          <i class="ki-filled ki-notepad-edit"></i>
         </a>
         <div class="flex items-center gap-2.5">
          <input form="notification_settings_form" class="kt-switch" name="channel_mobile_enabled" type="checkbox" value="1" @checked(old('channel_mobile_enabled', $prefs['channel_mobile_enabled'] ?? false)) />
         </div>
        </div>
       </div>

       <div class="kt-card-group flex flex-col gap-3 py-4 px-1 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3.5 min-w-0">
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
        <div class="flex flex-col gap-3 items-stretch sm:items-end w-full sm:w-auto sm:min-w-[min(100%,320px)]">
         <div class="flex flex-col gap-1 w-full">
          <label class="text-xs font-medium text-secondary-foreground" for="slack_webhook_url">{{ __('Incoming webhook URL') }}</label>
          <input
           form="notification_settings_form"
           id="slack_webhook_url"
           type="url"
           name="slack_webhook_url"
           class="kt-input text-sm"
           placeholder="https://hooks.slack.com/services/…"
           value="{{ old('slack_webhook_url', $prefs['slack_webhook_url'] ?? '') }}"
           autocomplete="off"
          />
          @error('slack_webhook_url')
           <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
          @enderror
         </div>
         <label class="kt-label justify-end">
          {{ __('Slack alerts') }}
          <input form="notification_settings_form" class="kt-switch" name="channel_slack_enabled" type="checkbox" value="1" @checked(old('channel_slack_enabled', $prefs['channel_slack_enabled'] ?? false)) />
         </label>
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
          <input form="notification_settings_form" class="kt-switch" name="channel_desktop_enabled" type="checkbox" value="1" @checked(old('channel_desktop_enabled', $prefs['channel_desktop_enabled'] ?? true)) />
         </div>
        </div>
       </div>
      </div>
     </div>
    </div>
    <div role="tabpanel" hidden class="kt-tab-content notifications-settings-tab-panel" id="notif_tab_other" aria-labelledby="notif_tab_btn_other">
     {{-- Other notifications --}}
     <div class="kt-card">
      <div class="kt-card-header gap-2">
       <h3 class="kt-card-title">
        {{ __('Other notifications') }}
       </h3>
       <div class="flex items-center gap-2">
        <label class="kt-label">
         {{ __('Family alerts') }}
         <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="family_wide_alerts" type="checkbox" value="1" @checked(old('family_wide_alerts', $prefs['family_wide_alerts'] ?? true)) />
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
          <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="notify_task_assigned" type="checkbox" value="1" @checked(old('notify_task_assigned', $prefs['notify_task_assigned'] ?? true)) />
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
          <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="notify_budget_warning" type="checkbox" value="1" @checked(old('notify_budget_warning', $prefs['notify_budget_warning'] ?? true)) />
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
          <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="notify_invoice" type="checkbox" value="1" @checked(old('notify_invoice', $prefs['notify_invoice'] ?? true)) />
         </div>
         @if ($currentFamily)
          <a href="{{ route('families.expenses.index') }}" class="kt-btn kt-btn-outline text-center shrink-0">
           {{ __('View expenses') }}
          </a>
         @endif
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
          <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="notify_feedback" type="checkbox" value="1" @checked(old('notify_feedback', $prefs['notify_feedback'] ?? true)) />
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
          <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="notify_collaboration" type="checkbox" value="1" @checked(old('notify_collaboration', $prefs['notify_collaboration'] ?? true)) />
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
          <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="notify_meeting_reminder" type="checkbox" value="1" @checked(old('notify_meeting_reminder', $prefs['notify_meeting_reminder'] ?? true)) />
         </div>
         @if ($currentFamily)
          <a href="{{ route('families.projects.index') }}" class="kt-btn kt-btn-outline shrink-0">
           {{ __('View projects') }}
          </a>
         @endif
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
          <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="notify_status_change" type="checkbox" value="1" @checked(old('notify_status_change', $prefs['notify_status_change'] ?? true)) />
         </div>
        </div>
       </div>
      </div>
     </div>
    </div>
    <div role="tabpanel" hidden class="kt-tab-content notifications-settings-tab-panel" id="notif_tab_faq" aria-labelledby="notif_tab_btn_faq">
     @php
      $notifFaqOpenManageSub = $canManageNotificationPage && (
       filled(old('_faq_id'))
       || $errors->has('question')
       || $errors->has('answer')
       || $errors->has('sort_order')
      );
     @endphp
     <div id="notif_faq_subtabs_root" class="flex flex-col gap-4 min-w-0 w-full">
      @if ($canManageNotificationPage)
       <div class="kt-tabs kt-tabs-line w-full border-b border-border pb-1 -mb-1 shrink-0" id="notif_faq_subtabs_tabs" role="tablist" aria-label="{{ __('FAQ tab sections') }}">
        <div class="settings-notif-tab-row">
         <button
          type="button"
          role="tab"
          id="notif_faq_sub_btn_help"
          class="notif-faq-subtab py-2.5 px-3 text-sm {{ $notifFaqOpenManageSub ? '' : 'active' }}"
          data-notif-faq-sub-target="notif_faq_sub_help"
          aria-selected="{{ $notifFaqOpenManageSub ? 'false' : 'true' }}"
          aria-controls="notif_faq_sub_help"
         >
          {{ __('Help') }}
         </button>
         <button
          type="button"
          role="tab"
          id="notif_faq_sub_btn_manage"
          class="notif-faq-subtab py-2.5 px-3 text-sm {{ $notifFaqOpenManageSub ? 'active' : '' }}"
          data-notif-faq-sub-target="notif_faq_sub_manage"
          aria-selected="{{ $notifFaqOpenManageSub ? 'true' : 'false' }}"
          aria-controls="notif_faq_sub_manage"
         >
          {{ __('Manage FAQ') }}
         </button>
        </div>
       </div>
      @endif

      <div
       role="tabpanel"
       id="notif_faq_sub_help"
       class="notifications-faq-subpanel min-w-0"
       @if ($canManageNotificationPage && $notifFaqOpenManageSub) hidden @endif
       aria-labelledby="{{ $canManageNotificationPage ? 'notif_faq_sub_btn_help' : 'notif_tab_btn_faq' }}"
      >

     {{-- FAQ (visible to all users) --}}
     <div class="kt-card border-border/80 shadow-sm overflow-hidden">
      <div class="kt-card-header flex flex-col gap-2 border-b border-border/70 px-5 py-5 lg:px-7.5 lg:py-6">
       <h3 class="kt-card-title text-lg sm:text-xl font-semibold text-mono tracking-tight mb-0">
        {{ __('Frequently asked questions') }}
       </h3>
       <p class="text-sm text-secondary-foreground leading-relaxed max-w-2xl mb-0">
        {{ __('Short answers about how FamLedger sends, batches, and prioritizes notification emails.') }}
       </p>
      </div>
      <div class="kt-card-content p-4 sm:p-6 lg:p-8">
       @if ($notificationFaqsPublic->isEmpty())
        <p class="text-sm text-secondary-foreground">{{ __('No FAQ entries yet.') }}</p>
       @else
        <div class="notif-faq-accordion flex flex-col gap-3 max-w-3xl" data-kt-accordion="true">
         @foreach ($notificationFaqsPublic as $faq)
          <div class="rounded-xl border border-border bg-card shadow-sm transition-[border-color,box-shadow] hover:border-primary/20 hover:shadow-md overflow-hidden" data-kt-accordion-item="true">
           <button
            type="button"
            class="kt-accordion-toggle group/faq-q flex w-full items-start gap-3 px-4 py-4 sm:px-5 sm:py-4 text-start cursor-pointer select-none border-0 bg-transparent hover:bg-muted/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary/35 !justify-start !items-start !py-4 !gap-3"
            data-kt-accordion-toggle="#faq_content_{{ $faq->id }}"
            aria-expanded="false"
            aria-controls="faq_content_{{ $faq->id }}"
           >
            <div class="faq-rich-content min-w-0 flex-1 text-base sm:text-[1.0625rem] font-semibold text-mono leading-snug [&_p]:mb-1 [&_p:last-child]:mb-0 [&_ul]:my-1 [&_ul]:list-disc [&_ul]:ps-5 [&_ol]:my-1 [&_ol]:list-decimal [&_ol]:ps-5 [&_h1]:text-base [&_h1]:font-semibold [&_h2]:text-base [&_h2]:font-semibold [&_h3]:text-base [&_a]:text-primary [&_a]:underline">
             {!! Purify::config('notification_faq')->clean($faq->question) !!}
            </div>
            <span class="relative inline-flex size-10 shrink-0 items-center justify-center rounded-full border border-border/80 bg-muted/50 text-muted-foreground transition-colors group-hover/faq-q:bg-muted group-hover/faq-q:text-foreground" aria-hidden="true">
             <span class="kt-accordion-indicator-on absolute inset-0 flex items-center justify-center">
              <i class="ki-filled ki-plus text-lg leading-none"></i>
             </span>
             <span class="kt-accordion-indicator-off absolute inset-0 flex items-center justify-center">
              <i class="ki-filled ki-minus text-lg leading-none"></i>
             </span>
            </span>
           </button>
           <div class="kt-accordion-content hidden border-t border-border/70 bg-muted/10" id="faq_content_{{ $faq->id }}">
            <div class="faq-rich-content text-secondary-foreground px-4 py-4 sm:px-5 sm:py-5 text-sm sm:text-[0.9375rem] leading-relaxed [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:my-2 [&_ul]:list-disc [&_ul]:ps-5 [&_ol]:my-2 [&_ol]:list-decimal [&_ol]:ps-5 [&_h1]:text-base [&_h1]:font-semibold [&_h2]:text-sm [&_h2]:font-semibold [&_h3]:text-sm [&_a]:text-primary [&_a]:underline [&_blockquote]:border-s-2 [&_blockquote]:border-border [&_blockquote]:ps-3 [&_blockquote]:italic">
             {!! Purify::config('notification_faq')->clean($faq->answer) !!}
            </div>
           </div>
          </div>
         @endforeach
        </div>
       @endif
      </div>
     </div>
      </div>

     @if ($canManageNotificationPage)
      <div
       role="tabpanel"
       id="notif_faq_sub_manage"
       class="notifications-faq-subpanel min-w-0"
       @unless ($notifFaqOpenManageSub) hidden @endunless
       aria-labelledby="notif_faq_sub_btn_manage"
      >
       <div class="kt-card border-primary/25 shadow-sm">
        <div class="kt-card-header flex flex-col items-stretch gap-0 px-5 py-4 lg:px-8 lg:py-5 border-b border-border/80">
         <h3 class="kt-card-title text-base lg:text-lg">
          {{ __('Manage FAQ (Super Admin)') }}
         </h3>
         <p class="text-sm text-secondary-foreground font-normal mt-2 max-w-3xl leading-relaxed pe-2">
          {{ __('Create, reorder, publish or remove questions shown to everyone under the Help tab.') }}
         </p>
        </div>
        <div class="kt-card-content settings-notif-section-pad">
         <form method="post" action="{{ route('settings.notifications.faqs.store') }}" class="flex flex-col gap-5 p-5 lg:p-6 rounded-xl border border-border bg-muted/25 shadow-sm">
          @csrf
          <p class="text-base font-semibold text-mono pb-3 mb-0 border-b border-border/70">{{ __('Add FAQ entry') }}</p>
          <div class="flex flex-col gap-2">
           <label id="lbl_new_faq_question" class="text-sm font-medium text-foreground ps-0.5 pe-1">{{ __('Question') }}</label>
           <div id="quill_new_faq_question" class="faq-quill-editor faq-quill-question rounded-lg border border-border bg-background overflow-hidden" data-faq-quill data-sync="hidden_new_faq_question" aria-labelledby="lbl_new_faq_question"></div>
           <textarea id="hidden_new_faq_question" name="question" class="faq-quill-sync-field" tabindex="-1" aria-hidden="true">{{ old('question') }}</textarea>
           <p id="new_faq_question_hint" class="text-xs text-muted-foreground leading-relaxed ps-0.5 pe-1">
            {{ __('Rich text for the accordion title: bold, color, headings, links, etc. Empty text is not allowed.') }}
           </p>
          </div>
          <div class="flex flex-col gap-2">
           <label id="lbl_new_faq_answer" class="text-sm font-medium text-foreground ps-0.5 pe-1">{{ __('Answer') }}</label>
           <div id="quill_new_faq_answer" class="faq-quill-editor faq-quill-answer rounded-lg border border-border bg-background overflow-hidden" data-faq-quill data-sync="hidden_new_faq_answer" aria-labelledby="lbl_new_faq_answer"></div>
           <textarea id="hidden_new_faq_answer" name="answer" class="faq-quill-sync-field" tabindex="-1" aria-hidden="true">{{ old('answer') }}</textarea>
           <p id="new_faq_answer_hint" class="text-xs text-muted-foreground leading-relaxed ps-0.5 pe-1">
            {{ __('Rich text: lists, numbered lists, headings, colors, links, and more. Content is sanitised when saved.') }}
           </p>
          </div>
          <div class="flex flex-col gap-2 max-w-md">
           <label class="text-sm font-medium text-foreground ps-0.5" for="new_faq_group">{{ __('Group (landing sidebar)') }}</label>
           <input id="new_faq_group" type="text" name="group_label" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ old('group_label') }}" maxlength="120" placeholder="{{ __('e.g. Notifications') }}" />
           <p class="text-xs text-muted-foreground leading-relaxed ps-0.5 pe-1">{{ __('Optional. FAQs with the same group label are grouped on the public landing page.') }}</p>
          </div>
          <div class="flex flex-wrap items-end gap-6 pt-1">
           <div class="flex flex-col gap-2 min-w-[8rem]">
            <label class="text-sm font-medium text-foreground ps-0.5" for="new_faq_sort">{{ __('Sort order') }}</label>
            <input id="new_faq_sort" type="number" name="sort_order" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ old('sort_order', 0) }}" min="0" />
           </div>
           <label class="kt-label text-sm font-medium text-foreground items-center gap-2.5 pb-1">
            {{ __('Published') }}
            <input class="kt-switch kt-switch-sm shrink-0" name="is_active" type="checkbox" value="1" @checked(old('is_active', true)) />
           </label>
          </div>
          <div class="pt-2">
           <button type="submit" class="kt-btn kt-btn-primary">{{ __('Add FAQ') }}</button>
          </div>
         </form>

         @foreach ($notificationFaqs as $faq)
          @php
           $faqUseOld = (string) old('_faq_id') === (string) $faq->id;
          @endphp
          <div class="rounded-xl border border-border bg-card/80 p-5 lg:p-7 flex flex-col gap-5 shadow-sm">
           <form method="post" action="{{ route('settings.notifications.faqs.update', $faq) }}" class="flex flex-col gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="_faq_id" value="{{ $faq->id }}" />
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-dashed border-border/90">
             <span class="text-sm font-semibold text-muted-foreground tracking-tight">{{ __('Entry #:id', ['id' => $faq->id]) }}</span>
             <label class="kt-label text-sm font-medium text-foreground items-center gap-2.5">
              {{ __('Published') }}
              <input class="kt-switch kt-switch-sm shrink-0" name="is_active" type="checkbox" value="1" @checked($faqUseOld ? (bool) old('is_active') : $faq->is_active) />
             </label>
            </div>
            @php
             $faqQuestionVal = $faqUseOld ? old('question', $faq->question) : $faq->question;
             $faqAnswerVal = $faqUseOld ? old('answer', $faq->answer) : $faq->answer;
             $faqGroupVal = $faqUseOld ? old('group_label', $faq->group_label) : $faq->group_label;
            @endphp
            <div class="flex flex-col gap-2">
             <label id="lbl_faq_q_{{ $faq->id }}" class="text-sm font-medium text-foreground ps-0.5 pe-1">{{ __('Question') }}</label>
             <div id="quill_faq_q_{{ $faq->id }}" class="faq-quill-editor faq-quill-question rounded-lg border border-border bg-background overflow-hidden" data-faq-quill data-sync="hidden_faq_q_{{ $faq->id }}" aria-labelledby="lbl_faq_q_{{ $faq->id }}"></div>
             <textarea id="hidden_faq_q_{{ $faq->id }}" name="question" class="faq-quill-sync-field" tabindex="-1" aria-hidden="true">{{ $faqQuestionVal }}</textarea>
             <p id="faq_q_hint_{{ $faq->id }}" class="text-xs text-muted-foreground leading-relaxed ps-0.5 pe-1">
              {{ __('Rich text for the accordion title. Empty text is not allowed.') }}
             </p>
            </div>
            <div class="flex flex-col gap-2">
             <label id="lbl_faq_a_{{ $faq->id }}" class="text-sm font-medium text-foreground ps-0.5 pe-1">{{ __('Answer') }}</label>
             <div id="quill_faq_a_{{ $faq->id }}" class="faq-quill-editor faq-quill-answer rounded-lg border border-border bg-background overflow-hidden" data-faq-quill data-sync="hidden_faq_a_{{ $faq->id }}" aria-labelledby="lbl_faq_a_{{ $faq->id }}"></div>
             <textarea id="hidden_faq_a_{{ $faq->id }}" name="answer" class="faq-quill-sync-field" tabindex="-1" aria-hidden="true">{{ $faqAnswerVal }}</textarea>
             <p id="faq_a_hint_{{ $faq->id }}" class="text-xs text-muted-foreground leading-relaxed ps-0.5 pe-1">
              {{ __('Rich answer text; sanitised when saved.') }}
             </p>
            </div>
            <div class="flex flex-col gap-2 max-w-md">
             <label class="text-sm font-medium text-foreground ps-0.5" for="faq_group_{{ $faq->id }}">{{ __('Group (landing sidebar)') }}</label>
             <input id="faq_group_{{ $faq->id }}" type="text" name="group_label" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ $faqGroupVal }}" maxlength="120" placeholder="{{ __('e.g. Notifications') }}" />
            </div>
            <div class="flex flex-col gap-2 max-w-xs">
             <label class="text-sm font-medium text-foreground ps-0.5" for="faq_sort_{{ $faq->id }}">{{ __('Sort order') }}</label>
             <input id="faq_sort_{{ $faq->id }}" type="number" name="sort_order" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ $faqUseOld ? old('sort_order', $faq->sort_order) : $faq->sort_order }}" min="0" />
            </div>
            <div class="flex flex-wrap items-center gap-3 pt-4 mt-1 border-t border-border/70">
             <button type="submit" class="kt-btn kt-btn-primary">{{ __('Save') }}</button>
            </div>
           </form>
           <form method="post" action="{{ route('settings.notifications.faqs.destroy', $faq) }}" class="inline" onsubmit="return confirm(@json(__('Delete this FAQ entry?')))">
            @csrf
            @method('DELETE')
            <button type="submit" class="kt-btn kt-btn-outline text-destructive border-destructive/35 hover:bg-destructive/10 px-4 py-2.5">{{ __('Delete') }}</button>
           </form>
          </div>
         @endforeach
        </div>
       </div>
      </div>
     @endif
     </div>
    </div>
    <div role="tabpanel" hidden class="kt-tab-content notifications-settings-tab-panel" id="notif_tab_contact" aria-labelledby="notif_tab_btn_contact">
     @if ($canManageNotificationPage)
      <div class="kt-card border-primary/25 shadow-sm">
       <div class="kt-card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 lg:px-8 lg:py-5 border-b border-border/80">
        <h3 class="kt-card-title text-base lg:text-lg mb-0">
         {{ __('Contact support') }}
        </h3>
        @can('contact_messages_view')
         <a href="{{ route('admin.contact-messages.index') }}" class="inline-flex items-center gap-2 text-sm text-primary font-medium hover:underline shrink-0">
          <i class="ki-filled ki-message-text text-base"></i>
          {{ __('Contact messages') }}
         </a>
        @endcan
       </div>
       <div class="kt-card-content settings-notif-section-pad">
        <form method="post" action="{{ route('settings.notifications.support-contacts.store') }}" class="flex flex-col gap-5 p-5 lg:p-6 rounded-xl border border-border bg-muted/25 shadow-sm">
         @csrf
         <input type="hidden" name="_support_from" value="create" />
         <input type="hidden" name="sort_order" value="0" />
         <p class="text-base font-semibold text-mono pb-3 mb-0 border-b border-border/70">{{ __('Add contact entry') }}</p>
         <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-foreground ps-0.5" for="new_support_title">{{ __('Title') }}</label>
          <input id="new_support_title" type="text" name="title" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ old('_support_contact_id') ? '' : old('title', '') }}" maxlength="255" required autocomplete="off" />
         </div>
         <div class="flex flex-col gap-2">
          <label id="lbl_new_support_body" class="text-sm font-medium text-foreground ps-0.5 pe-1">{{ __('Description') }}</label>
          <div id="quill_new_support_body" class="faq-quill-editor faq-quill-answer rounded-lg border border-border bg-background overflow-hidden" data-faq-quill data-sync="hidden_new_support_body" aria-labelledby="lbl_new_support_body"></div>
          <textarea id="hidden_new_support_body" name="body" class="faq-quill-sync-field" tabindex="-1" aria-hidden="true">{{ old('_support_contact_id') ? '' : old('body', '') }}</textarea>
         </div>
         <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-foreground ps-0.5" for="new_support_link_url">{{ __('Button / link URL') }}</label>
          <input id="new_support_link_url" type="text" name="link_url" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ old('_support_contact_id') ? '' : old('link_url', '') }}" maxlength="2048" />
         </div>
         <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-foreground ps-0.5" for="new_support_link_label">{{ __('Button label') }}</label>
          <input id="new_support_link_label" type="text" name="link_label" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ old('_support_contact_id') ? '' : old('link_label', '') }}" maxlength="255" />
         </div>
         <label class="kt-label text-sm font-medium text-foreground items-center gap-2.5">
          {{ __('Published') }}
          <input type="hidden" name="is_active" value="0" />
          <input class="kt-switch kt-switch-sm shrink-0" name="is_active" type="checkbox" value="1" @checked(old('_support_contact_id') ? true : old('is_active') !== '0') />
         </label>
         <div class="pt-2">
          <button type="submit" class="kt-btn kt-btn-primary">{{ __('Add contact entry') }}</button>
         </div>
        </form>

        @foreach ($notificationSupportContacts as $supportContact)
         @php
          $supportUseOld = (string) old('_support_contact_id') === (string) $supportContact->id;
         @endphp
         <div class="rounded-xl border border-border bg-card/80 p-5 lg:p-7 flex flex-col gap-5 shadow-sm">
          <form method="post" action="{{ route('settings.notifications.support-contacts.update', $supportContact) }}" class="flex flex-col gap-4">
           @csrf
           @method('PUT')
           <input type="hidden" name="_support_contact_id" value="{{ $supportContact->id }}" />
           <input type="hidden" name="sort_order" value="{{ $supportUseOld ? old('sort_order', $supportContact->sort_order) : $supportContact->sort_order }}" />
           <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-dashed border-border/90">
            <span class="text-sm font-semibold text-muted-foreground tracking-tight">{{ __('Entry #:id', ['id' => $supportContact->id]) }}</span>
            <label class="kt-label text-sm font-medium text-foreground items-center gap-2.5">
             {{ __('Published') }}
             <input type="hidden" name="is_active" value="0" />
             <input class="kt-switch kt-switch-sm shrink-0" name="is_active" type="checkbox" value="1" @checked($supportUseOld ? (old('is_active') === '1' || old('is_active') === 1 || old('is_active') === true) : $supportContact->is_active) />
            </label>
           </div>
           <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-foreground ps-0.5" for="support_title_{{ $supportContact->id }}">{{ __('Title') }}</label>
            <input id="support_title_{{ $supportContact->id }}" type="text" name="title" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ $supportUseOld ? old('title', $supportContact->title) : $supportContact->title }}" maxlength="255" required autocomplete="off" />
           </div>
           <div class="flex flex-col gap-2">
            <label id="lbl_support_body_{{ $supportContact->id }}" class="text-sm font-medium text-foreground ps-0.5 pe-1">{{ __('Description') }}</label>
            <div id="quill_support_body_{{ $supportContact->id }}" class="faq-quill-editor faq-quill-answer rounded-lg border border-border bg-background overflow-hidden" data-faq-quill data-sync="hidden_support_body_{{ $supportContact->id }}" aria-labelledby="lbl_support_body_{{ $supportContact->id }}"></div>
            <textarea id="hidden_support_body_{{ $supportContact->id }}" name="body" class="faq-quill-sync-field" tabindex="-1" aria-hidden="true">{{ $supportUseOld ? old('body', $supportContact->body) : $supportContact->body }}</textarea>
           </div>
           <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-foreground ps-0.5" for="support_link_url_{{ $supportContact->id }}">{{ __('Button / link URL') }}</label>
            <input id="support_link_url_{{ $supportContact->id }}" type="text" name="link_url" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ $supportUseOld ? old('link_url', $supportContact->link_url) : $supportContact->link_url }}" maxlength="2048" />
           </div>
           <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-foreground ps-0.5" for="support_link_label_{{ $supportContact->id }}">{{ __('Button label') }}</label>
            <input id="support_link_label_{{ $supportContact->id }}" type="text" name="link_label" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ $supportUseOld ? old('link_label', $supportContact->link_label) : $supportContact->link_label }}" maxlength="255" />
           </div>
           <div class="flex flex-wrap items-center gap-3 pt-4 mt-1 border-t border-border/70">
            <button type="submit" class="kt-btn kt-btn-primary">{{ __('Save') }}</button>
           </div>
          </form>
          <form method="post" action="{{ route('settings.notifications.support-contacts.destroy', $supportContact) }}" class="inline" onsubmit="return confirm(@json(__('Delete this contact entry?')))">
           @csrf
           @method('DELETE')
           <button type="submit" class="kt-btn kt-btn-outline text-destructive border-destructive/35 hover:bg-destructive/10 px-4 py-2.5">{{ __('Delete') }}</button>
          </form>
         </div>
        @endforeach
       </div>
      </div>
     @else
      <p class="text-sm text-secondary-foreground px-0.5">{{ __('Contact support is managed by a Super Admin and appears on the public FamLedger page.') }}</p>
     @endif
    </div>
    <div role="tabpanel" hidden class="kt-tab-content notifications-settings-tab-panel" id="notif_tab_dnd" aria-labelledby="notif_tab_btn_dnd">
     {{-- Do not disturb --}}
     <div class="kt-card">
      <div class="kt-card-header">
       <h3 class="kt-card-title">
        {{ __('Do not disturb') }}
       </h3>
      </div>
      <div class="kt-card-content flex flex-col gap-3.5">
       @if ($dndOn)
        <div class="rounded-lg border border-border bg-muted/30 px-3 py-2 text-sm text-secondary-foreground">
         {{ __('Do not disturb is on until :time.', ['time' => \Illuminate\Support\Carbon::parse($prefs['dnd_until'])->timezone(config('app.timezone'))->format('Y-m-d H:i')]) }}
        </div>
       @endif
       <p class="text-sm text-secondary-foreground whitespace-pre-wrap">
        {{ $notificationPageContent->dnd_intro }}
       </p>
       <label class="kt-label">
        {{ __('Enable scheduled quiet hours') }}
        <input form="notification_settings_form" class="kt-switch kt-switch-sm" name="dnd_enabled" type="checkbox" value="1" @checked(old('dnd_enabled', $prefs['dnd_enabled'] ?? false)) />
       </label>
       <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-secondary-foreground" for="dnd_until">{{ __('Quiet until (optional)') }}</label>
        <input
         form="notification_settings_form"
         id="dnd_until"
         type="datetime-local"
         name="dnd_until"
         class="kt-input text-sm"
         value="{{ $dndUntilInput }}"
        />
        @error('dnd_until')
         <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
        @enderror
       </div>
       @if ($notificationPageContent->dnd_learn_more_url)
        <div>
         <a class="kt-link kt-link-underlined kt-link-dashed" href="{{ $notificationPageContent->dnd_learn_more_url }}">
          {{ $notificationPageContent->dnd_learn_more_label ?: __('Learn more') }}
         </a>
        </div>
       @endif
      </div>
      <div class="kt-card-footer flex flex-col gap-2">
       <button type="submit" form="notification_settings_form" name="action" value="snooze_8" class="kt-btn kt-btn-outline w-full justify-center">
        <i class="ki-filled ki-notification-bing"></i>
        {{ __('Pause notifications (8 hours)') }}
       </button>
       @if ($dndOn || ($prefs['dnd_enabled'] ?? false))
        <button type="submit" form="notification_settings_form" name="action" value="dnd_clear" class="kt-btn kt-btn-sm kt-btn-ghost w-full justify-center">
         {{ __('Turn off do not disturb') }}
        </button>
       @endif
      </div>
     </div>

     @if ($canManageNotificationPage)
      <div class="kt-card border-primary/25 shadow-sm">
       <div class="kt-card-header flex flex-col items-stretch gap-0 px-5 py-4 lg:px-8 lg:py-5 border-b border-border/80">
        <h3 class="kt-card-title text-base lg:text-lg">
         {{ __('Edit Do not disturb copy (Super Admin)') }}
        </h3>
        <p class="text-sm text-secondary-foreground font-normal mt-2 max-w-3xl leading-relaxed pe-2">
         {{ __('Intro text and optional “learn more” link shown above to all users on this tab.') }}
        </p>
       </div>
       <div class="kt-card-content settings-notif-section-pad">
        <form method="post" action="{{ route('settings.notifications.page-content.update') }}" class="settings-notif-form-grid max-w-3xl w-full">
         @csrf
         @method('PUT')
         <input type="hidden" name="page_content_section" value="dnd" />
         <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-foreground ps-0.5" for="dnd_intro">{{ __('Intro paragraph') }}</label>
          <textarea id="dnd_intro" name="dnd_intro" class="kt-input text-sm w-full min-h-[6.5rem] px-4 py-3.5 leading-relaxed" rows="3" required>{{ old('dnd_intro', $notificationPageContent->dnd_intro) }}</textarea>
         </div>
         <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-foreground ps-0.5" for="dnd_learn_more_url">{{ __('Learn more URL') }}</label>
          <input id="dnd_learn_more_url" type="text" name="dnd_learn_more_url" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ old('dnd_learn_more_url', $notificationPageContent->dnd_learn_more_url) }}" maxlength="2048" />
         </div>
         <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-foreground ps-0.5" for="dnd_learn_more_label">{{ __('Learn more label') }}</label>
          <input id="dnd_learn_more_label" type="text" name="dnd_learn_more_label" class="kt-input text-sm w-full min-h-11 px-4 py-3" value="{{ old('dnd_learn_more_label', $notificationPageContent->dnd_learn_more_label) }}" maxlength="255" />
         </div>
         <div class="pt-2">
          <button type="submit" class="kt-btn kt-btn-primary px-6">{{ __('Save Do not disturb copy') }}</button>
         </div>
        </form>
       </div>
      </div>
     @endif

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
        <a class="text-base font-semibold text-mono hover:text-primary" href="{{ route('settings.notifications') }}">
         {{ __('Streamlined alerts setup: custom notification preferences') }}
        </a>
        <p class="text-sm text-secondary-foreground">
         {{ __('Tailor your alert preferences so you only receive the notifications that matter most to your family and budgets.') }}
        </p>
        <a class="kt-link kt-link-underlined kt-link-dashed" href="{{ route('settings.index') }}">
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
        <a class="text-base font-semibold text-mono hover:text-primary" href="{{ route('profile.edit') }}">
         {{ __('Effective communication: instant notification tools') }}
        </a>
        <p class="text-sm text-secondary-foreground">
         {{ __('Ensure timely communication with instant alerts across email, mobile, Slack and desktop.') }}
        </p>
        <a class="kt-link kt-link-underlined kt-link-dashed" href="{{ route('profile.edit') }}">
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
        <a class="text-base font-semibold text-mono hover:text-primary" href="{{ route('dashboard') }}">
         {{ __('Personalised updates: smart alert system') }}
        </a>
        <p class="text-sm text-secondary-foreground">
         {{ __('Control how you receive updates with a smart alert system tuned to your financial routines.') }}
        </p>
        <a class="kt-link kt-link-underlined kt-link-dashed" href="{{ route('dashboard') }}">
         {{ __('Learn more') }}
        </a>
       </div>
       <span class="hidden not-last:block not-last:border-b border-b-border"></span>
      </div>
     </div>
    </div>
   </div>
  <div class="flex flex-wrap items-center justify-end gap-2.5 px-5 sm:px-6 lg:px-8 py-4 border-t border-sky-100/80 dark:border-slate-600/50">
   <button type="submit" form="notification_settings_form" class="admin-pulse-btn-primary">
    <i class="ki-filled ki-check text-base"></i>
    {{ __('Save preferences') }}
   </button>
  </div>
   </div>
  </div>
 </div>
</div>
@endsection

@push('scripts')
 @if ($canManageNotificationPage)
  <script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
 @endif
 <script>
  document.addEventListener('DOMContentLoaded', function () {
   var tabBar = document.getElementById('notification_settings_tabs');
   if (!tabBar) return;

   var buttons = tabBar.querySelectorAll('[data-notif-tab-target]');
   var panelIds = ['notif_tab_channels', 'notif_tab_other', 'notif_tab_faq', 'notif_tab_contact', 'notif_tab_dnd'];
   var panelIdSet = {};
   panelIds.forEach(function (id) { panelIdSet[id] = true; });

   function showPanel(panelId) {
    var id = typeof panelId === 'string' ? panelId.trim() : '';
    if (!panelIdSet[id]) {
     id = 'notif_tab_channels';
    }
    panelIds.forEach(function (pid) {
     var panel = document.getElementById(pid);
     if (!panel) return;
     var on = pid === id;
     panel.toggleAttribute('hidden', !on);
     panel.classList.toggle('active', on);
     if (on) {
      panel.style.setProperty('display', 'flex', 'important');
      panel.style.setProperty('flex-direction', 'column', 'important');
     } else {
      panel.style.setProperty('display', 'none', 'important');
     }
    });
    buttons.forEach(function (btn) {
     var target = (btn.getAttribute('data-notif-tab-target') || '').trim();
     var on = target === id;
     btn.classList.toggle('active', on);
     btn.setAttribute('aria-selected', on ? 'true' : 'false');
    });
   }

   buttons.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
     e.preventDefault();
     e.stopPropagation();
     if (typeof e.stopImmediatePropagation === 'function') {
      e.stopImmediatePropagation();
     }
     var target = btn.getAttribute('data-notif-tab-target');
     if (target) showPanel(target);
    }, true);
   });

   function readNotificationsHashTab() {
    var h = (window.location.hash || '').replace(/^#/, '').trim();
    return panelIdSet[h] ? h : null;
   }

   var initialTab = @json(session('open_notifications_tab'));
   @if (old('_support_from') === 'create' || old('_support_contact_id'))
    if (!initialTab) initialTab = 'notif_tab_contact';
   @elseif (old('page_content_section') === 'dnd')
    if (!initialTab) initialTab = 'notif_tab_dnd';
   @endif
   var hashTab = readNotificationsHashTab();
   if (hashTab) {
    initialTab = hashTab;
   }
   showPanel(initialTab || 'notif_tab_channels');

   window.addEventListener('hashchange', function () {
    var t = readNotificationsHashTab();
    if (t) {
     showPanel(t);
    }
   });

   var faqAccordion = document.querySelector('#notif_tab_faq [data-kt-accordion="true"]');
   if (faqAccordion) {
    faqAccordion.querySelectorAll('[data-kt-accordion-toggle]').forEach(function (toggle) {
     toggle.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
       e.preventDefault();
       toggle.click();
      }
     });
    });
   }

   var faqSubRoot = document.getElementById('notif_faq_subtabs_root');
   if (faqSubRoot) {
    var faqSubBtns = faqSubRoot.querySelectorAll('[data-notif-faq-sub-target]');
    var faqSubPanelIds = ['notif_faq_sub_help', 'notif_faq_sub_manage'];
    var initialFaqSub = @json($notifFaqOpenManageSub ? 'notif_faq_sub_manage' : 'notif_faq_sub_help');
    function showFaqSub(panelId) {
     var id = typeof panelId === 'string' ? panelId.trim() : '';
     faqSubPanelIds.forEach(function (pid) {
      var el = document.getElementById(pid);
      if (!el) return;
      var on = pid === id;
      el.toggleAttribute('hidden', !on);
      if (on) {
       el.style.setProperty('display', 'flex', 'important');
       el.style.setProperty('flex-direction', 'column', 'important');
      } else {
       el.style.setProperty('display', 'none', 'important');
      }
     });
     faqSubBtns.forEach(function (btn) {
      var t = (btn.getAttribute('data-notif-faq-sub-target') || '').trim();
      var on = t === id;
      btn.classList.toggle('active', on);
      btn.setAttribute('aria-selected', on ? 'true' : 'false');
     });
    }
    faqSubBtns.forEach(function (btn) {
     btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') {
       e.stopImmediatePropagation();
      }
      var t = (btn.getAttribute('data-notif-faq-sub-target') || '').trim();
      if (t) showFaqSub(t);
     }, true);
    });
    if (faqSubBtns.length) {
     showFaqSub(initialFaqSub);
    }
   }

   @if ($canManageNotificationPage)
   if (typeof Quill !== 'undefined') {
    var toolbarOptions = [
     [{ header: [1, 2, 3, false] }],
     ['bold', 'italic', 'underline', 'strike'],
     [{ color: [] }, { background: [] }],
     [{ list: 'ordered' }, { list: 'bullet' }],
     [{ indent: '-1' }, { indent: '+1' }],
     ['link'],
     ['clean'],
    ];
    document.querySelectorAll('[data-faq-quill]').forEach(function (el) {
     var syncId = el.getAttribute('data-sync');
     var ta = syncId ? document.getElementById(syncId) : null;
     if (!ta) return;
     try {
      var quill = new Quill(el, {
       theme: 'snow',
       modules: { toolbar: toolbarOptions },
      });
      if (ta.value && ta.value.trim() !== '') {
       quill.root.innerHTML = ta.value;
      }
      function sync() {
       ta.value = quill.root.innerHTML;
      }
      quill.on('text-change', sync);
      sync();
      var form = ta.closest('form');
      if (form) {
       form.addEventListener('submit', sync);
      }
     } catch (err) {
      console.warn('Quill init failed for editor', el.id || syncId, err);
     }
    });
   }
   @endif
  });
 </script>
@endpush

