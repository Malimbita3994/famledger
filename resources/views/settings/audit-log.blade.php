@extends('layouts.metronic')

@section('title', __('Audit log'))
@section('page_title', __('Audit log'))

@section('content')
 <div class="pb-5">
  <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
   <div class="flex items-center flex-wrap gap-1 lg:gap-5">
    <h1 class="font-medium text-lg text-mono">
     {{ __('Audit log') }}
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
      {{ __('Audit log') }}
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

 <style>
  .audit-filter-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
  }

  .audit-filter-field {
      flex: 1 1 100%;
  }

  @media (min-width: 900px) {
      .audit-filter-field {
          flex: 0 0 calc(25% - 12px);
          max-width: calc(25% - 12px);
      }
  }

  .audit-filter-card {
      position: relative;
      z-index: 10;
      overflow: visible;
  }
 </style>

 <div class="kt-container-fixed pb-6">
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
   <div class="col-span-2">
    <div class="flex flex-col gap-5 lg:gap-7.5">
     {{-- Filters --}}
     <div class="kt-card audit-filter-card">
      <div class="kt-card-header flex-col md:flex-row gap-4 md:gap-6 items-start md:items-center">
       <div class="flex flex-col gap-1">
        <h3 class="kt-card-title">
         {{ __('Filter activity') }}
        </h3>
        <span class="text-xs text-secondary-foreground">
         {{ __('Narrow down events by date, member, area and severity.') }}
        </span>
       </div>
      </div>
      <div class="kt-card-content px-5 pb-5 pt-1">
       <form class="audit-filter-row">
        <div class="flex flex-col gap-1.5 audit-filter-field">
         <label for="audit_date_range" class="text-xs font-medium text-secondary-foreground">
          {{ __('Date range') }}
         </label>
         <div class="kt-input">
          <i class="ki-filled ki-calendar"></i>
          <input id="audit_date_range" type="text" placeholder="{{ __('Last 30 days') }}" />
         </div>
        </div>
        <div class="flex flex-col gap-1.5 audit-filter-field">
         <label for="audit_member" class="text-xs font-medium text-secondary-foreground">
          {{ __('Family member') }}
         </label>
         <select id="audit_member" class="kt-select w-full" data-kt-select="true">
          <option value="all">{{ __('All members') }}</option>
          <option value="me">{{ __('Only my activity') }}</option>
         </select>
        </div>
        <div class="flex flex-col gap-1.5 audit-filter-field">
         <label for="audit_area" class="text-xs font-medium text-secondary-foreground">
          {{ __('Area') }}
         </label>
         <select id="audit_area" class="kt-select w-full" data-kt-select="true">
          <option value="all">{{ __('All areas') }}</option>
          <option value="wallets">{{ __('Wallets & accounts') }}</option>
          <option value="budgets">{{ __('Budgets & envelopes') }}</option>
          <option value="transactions">{{ __('Transactions') }}</option>
          <option value="members">{{ __('Members & roles') }}</option>
          <option value="settings">{{ __('Settings & categories') }}</option>
         </select>
        </div>
        <div class="flex flex-col gap-1.5 audit-filter-field">
         <label for="audit_severity" class="text-xs font-medium text-secondary-foreground">
          {{ __('Severity') }}
         </label>
         <select id="audit_severity" class="kt-select w-full" data-kt-select="true">
          <option value="all">{{ __('All severities') }}</option>
          <option value="info">{{ __('Info only') }}</option>
          <option value="warning">{{ __('Warnings') }}</option>
          <option value="critical">{{ __('Critical only') }}</option>
         </select>
        </div>
       </form>
      </div>
     </div>

     {{-- Audit table (desktop / tablet) --}}
     <div class="kt-card">
      <div class="kt-card-header items-center justify-between gap-3">
       <h3 class="kt-card-title text-sm">
        {{ __('Recent activity for this family') }}
       </h3>
       <div class="flex items-center gap-2">
        <button type="button" class="kt-btn kt-btn-xs kt-btn-outline">
         <i class="ki-filled ki-notification-status text-sm"></i>
         {{ __('Export CSV') }}
        </button>
       </div>
      </div>
      <div class="kt-card-content px-0 pb-3">
       <div class="kt-scrollable-x-auto hidden md:block">
        <table class="kt-table align-middle text-xs text-secondary-foreground">
         <thead>
          <tr class="bg-accent/40">
           <th class="px-4 py-2 text-start font-medium min-w-40">
            {{ __('When') }}
           </th>
           <th class="px-4 py-2 text-start font-medium min-w-40">
            {{ __('Who') }}
           </th>
           <th class="px-4 py-2 text-start font-medium min-w-44">
            {{ __('Area') }}
           </th>
           <th class="px-4 py-2 text-start font-medium min-w-64">
            {{ __('Action') }}
           </th>
           <th class="px-4 py-2 text-start font-medium min-w-40">
            {{ __('Details') }}
           </th>
           <th class="px-4 py-2 text-end font-medium min-w-20">
            {{ __('IP') }}
           </th>
          </tr>
         </thead>
         <tbody>
          {{-- Example static rows for now --}}
          <tr>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col">
             <span class="font-medium text-mono text-xs">
              {{ __('Today · 09:14') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ __('2 minutes ago') }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col">
             <span class="text-xs font-medium text-mono">
              {{ __('You') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ auth()->user()->email ?? 'user@example.com' }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-primary">
             {{ __('Budgets') }}
            </span>
           </td>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col gap-0.5">
             <span class="text-xs font-medium text-foreground">
              {{ __('Updated monthly grocery budget') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ __('Limit changed from 450,000 to 500,000') }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <span class="text-[11px] text-muted-foreground">
             {{ __('Wallet: Household · Currency: TZS') }}
            </span>
           </td>
           <td class="px-4 py-2 align-top text-end">
            <span class="text-[11px] text-muted-foreground">
             192.168.0.10
            </span>
           </td>
          </tr>

          <tr>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col">
             <span class="font-medium text-mono text-xs">
              {{ __('Yesterday · 21:03') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ __('1 day ago') }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col">
             <span class="text-xs font-medium text-mono">
              {{ __('Family member') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ __('Invited user') }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-secondary">
             {{ __('Members & roles') }}
            </span>
           </td>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col gap-0.5">
             <span class="text-xs font-medium text-foreground">
              {{ __('Changed role from Viewer to Owner') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ __('Ownership transferred') }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <span class="text-[11px] text-muted-foreground">
             {{ __('Family: Main household') }}
            </span>
           </td>
           <td class="px-4 py-2 align-top text-end">
            <span class="text-[11px] text-muted-foreground">
             102.89.14.23
            </span>
           </td>
          </tr>

          <tr>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col">
             <span class="font-medium text-mono text-xs">
              {{ __('2 days ago · 08:27') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ __('2 days ago') }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col">
             <span class="text-xs font-medium text-mono">
              {{ __('You') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ __('Login') }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-success">
             {{ __('Security') }}
            </span>
           </td>
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col gap-0.5">
             <span class="text-xs font-medium text-foreground">
              {{ __('Signed in to FamLedger') }}
             </span>
             <span class="text-[11px] text-muted-foreground">
              {{ __('2FA verified via email code') }}
             </span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            <span class="text-[11px] text-muted-foreground">
             {{ __('Device: Chrome · Windows') }}
            </span>
           </td>
           <td class="px-4 py-2 align-top text-end">
            <span class="text-[11px] text-muted-foreground">
             41.59.192.5
            </span>
           </td>
          </tr>
         </tbody>
        </table>
       </div>

       {{-- Mobile cards --}}
       <div class="md:hidden px-4 space-y-3 text-xs text-secondary-foreground">
        {{-- First event --}}
        <div class="rounded-xl border border-border bg-background p-3 flex flex-col gap-2">
         <div class="flex items-start justify-between gap-3">
          <div>
           <div class="font-medium text-mono text-[11px]">
            {{ __('Today · 09:14') }}
           </div>
           <div class="text-[11px] text-muted-foreground">
            {{ __('2 minutes ago') }}
           </div>
          </div>
          <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-primary">
           {{ __('Budgets') }}
          </span>
         </div>
         <div class="text-[11px] text-muted-foreground">
          <span class="font-semibold text-foreground">{{ __('You') }}</span>
          <span class="mx-1">·</span>
          <span>{{ auth()->user()->email ?? 'user@example.com' }}</span>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="text-xs font-medium text-foreground">
           {{ __('Updated monthly grocery budget') }}
          </span>
          <span class="text-[11px] text-muted-foreground">
           {{ __('Limit changed from 450,000 to 500,000 · Wallet: Household · Currency: TZS') }}
          </span>
         </div>
         <div class="text-[11px] text-muted-foreground flex justify-between">
          <span>{{ __('IP: 192.168.0.10') }}</span>
         </div>
        </div>

        {{-- Second event --}}
        <div class="rounded-xl border border-border bg-background p-3 flex flex-col gap-2">
         <div class="flex items-start justify-between gap-3">
          <div>
           <div class="font-medium text-mono text-[11px]">
            {{ __('Yesterday · 21:03') }}
           </div>
           <div class="text-[11px] text-muted-foreground">
            {{ __('1 day ago') }}
           </div>
          </div>
          <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-secondary">
           {{ __('Members & roles') }}
          </span>
         </div>
         <div class="text-[11px] text-muted-foreground">
          <span class="font-semibold text-foreground">{{ __('Family member') }}</span>
          <span class="mx-1">·</span>
          <span>{{ __('Invited user') }}</span>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="text-xs font-medium text-foreground">
           {{ __('Changed role from Viewer to Owner') }}
          </span>
          <span class="text-[11px] text-muted-foreground">
           {{ __('Ownership transferred · Family: Main household') }}
          </span>
         </div>
         <div class="text-[11px] text-muted-foreground flex justify-between">
          <span>{{ __('IP: 102.89.14.23') }}</span>
         </div>
        </div>

        {{-- Third event --}}
        <div class="rounded-xl border border-border bg-background p-3 flex flex-col gap-2">
         <div class="flex items-start justify-between gap-3">
          <div>
           <div class="font-medium text-mono text-[11px]">
            {{ __('2 days ago · 08:27') }}
           </div>
           <div class="text-[11px] text-muted-foreground">
            {{ __('2 days ago') }}
           </div>
          </div>
          <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-success">
           {{ __('Security') }}
          </span>
         </div>
         <div class="text-[11px] text-muted-foreground">
          <span class="font-semibold text-foreground">{{ __('You') }}</span>
          <span class="mx-1">·</span>
          <span>{{ __('Login') }}</span>
         </div>
         <div class="flex flex-col gap-0.5">
          <span class="text-xs font-medium text-foreground">
           {{ __('Signed in to FamLedger') }}
          </span>
          <span class="text-[11px] text-muted-foreground">
           {{ __('2FA verified via email code · Device: Chrome · Windows') }}
          </span>
         </div>
         <div class="text-[11px] text-muted-foreground flex justify-between">
          <span>{{ __('IP: 41.59.192.5') }}</span>
         </div>
        </div>
       </div>
      </div>
      <div class="kt-card-footer justify-between items-center flex-wrap gap-3">
       <span class="text-xs text-muted-foreground">
        {{ __('Showing a sample of recent events. This view will be wired to the database as the audit feature evolves.') }}
       </span>
       <div class="flex items-center gap-2">
        <button type="button" class="kt-btn kt-btn-xs kt-btn-outline">
         {{ __('Previous') }}
        </button>
        <button type="button" class="kt-btn kt-btn-xs kt-btn-outline">
         {{ __('Next') }}
        </button>
       </div>
      </div>
     </div>
    </div>
   </div>

   <div class="col-span-1">
    <div class="flex flex-col gap-5 lg:gap-7.5">
     {{-- Summary card --}}
     <div class="kt-card">
      <div class="kt-card-header">
       <h3 class="kt-card-title">
        {{ __('Summary (last 30 days)') }}
       </h3>
      </div>
      <div class="kt-card-content px-5 py-4 flex flex-col gap-4">
       <div class="flex items-center justify-between">
        <span class="text-xs text-secondary-foreground">
         {{ __('Total events') }}
        </span>
        <span class="text-sm font-semibold text-mono">
         128
        </span>
       </div>
       <div class="flex items-center justify-between">
        <span class="text-xs text-secondary-foreground">
         {{ __('Critical changes') }}
        </span>
        <span class="text-sm font-semibold text-destructive">
         4
        </span>
       </div>
       <div class="flex items-center justify-between">
        <span class="text-xs text-secondary-foreground">
         {{ __('Member updates') }}
        </span>
        <span class="text-sm font-semibold text-mono">
         9
        </span>
       </div>
       <div class="flex items-center justify-between">
        <span class="text-xs text-secondary-foreground">
         {{ __('Budget & wallet changes') }}
        </span>
        <span class="text-sm font-semibold text-mono">
         21
        </span>
       </div>
      </div>
     </div>

     {{-- Tips card --}}
     <div class="kt-card">
      <div class="kt-card-content px-6 py-6 flex flex-col gap-4">
       <div class="flex items-start gap-3">
        <div class="relative size-[40px] shrink-0">
         <svg class="w-full h-full stroke-primary/10 fill-primary-soft" fill="none" height="40" viewBox="0 0 44 48" width="40" xmlns="http://www.w3.org/2000/svg">
          <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" />
          <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" />
         </svg>
         <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
          <i class="ki-filled ki-security-user text-base text-primary"></i>
         </div>
        </div>
        <div class="flex flex-col gap-1.5">
         <h3 class="text-sm font-semibold text-mono">
          {{ __('Keep your family safe') }}
         </h3>
         <p class="text-xs text-secondary-foreground">
          {{ __('Regularly review role changes, new members and large withdrawals here to spot anything unexpected early.') }}
         </p>
        </div>
       </div>
       <a href="{{ route('settings.notifications') }}" class="kt-link kt-link-underlined kt-link-dashed text-xs">
        {{ __('Tune alerts in Notifications settings') }}
       </a>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
@endsection

