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

  .audit-summary-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
  }
 </style>

 <div class="kt-container-fixed pb-6">
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
   <div class="col-span-2">
    <div class="flex flex-col gap-5 lg:gap-7.5">
     {{-- Scope & filters (Super Admin / Auditor: whole system or a family you belong to) --}}
     <div class="kt-card audit-filter-card">
      <div class="kt-card-header flex-col md:flex-row gap-4 md:gap-6 items-start md:items-center">
       <div class="flex flex-col gap-1">
        <h3 class="kt-card-title">
         {{ __('Filter activity') }}
        </h3>
       </div>
      </div>
      <div class="kt-card-content px-5 pb-5 pt-1">
       <form method="get" action="{{ route('settings.audit-log') }}" class="audit-filter-row">
        <div class="flex flex-col gap-1.5 audit-filter-field" style="min-width: 180px;">
         <label for="audit_scope" class="text-xs font-medium text-secondary-foreground">{{ __('Scope') }}</label>
         <select id="audit_scope" name="family_id" class="kt-select w-full">
          <option value="">{{ __('Whole system') }}</option>
          @foreach($families ?? [] as $f)
           <option value="{{ $f->id }}" {{ request('family_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
          @endforeach
         </select>
        </div>
        <div class="flex flex-col gap-1.5 audit-filter-field">
         <label for="audit_from" class="text-xs font-medium text-secondary-foreground">{{ __('From') }}</label>
         <input id="audit_from" type="date" name="from" value="{{ request('from') }}" class="kt-input w-full" />
        </div>
        <div class="flex flex-col gap-1.5 audit-filter-field">
         <label for="audit_to" class="text-xs font-medium text-secondary-foreground">{{ __('To') }}</label>
         <input id="audit_to" type="date" name="to" value="{{ request('to') }}" class="kt-input w-full" />
        </div>
        <div class="flex flex-col gap-1.5 audit-filter-field">
         <label for="audit_type" class="text-xs font-medium text-secondary-foreground">{{ __('Type') }}</label>
         <select id="audit_type" name="type" class="kt-select w-full">
          <option value="">{{ __('All') }}</option>
          <option value="application" {{ request('type') === 'application' ? 'selected' : '' }}>{{ __('Application') }}</option>
          <option value="database" {{ request('type') === 'database' ? 'selected' : '' }}>{{ __('Database') }}</option>
         </select>
        </div>
        <div class="flex flex-col gap-1.5 audit-filter-field flex items-end">
         <button type="submit" class="kt-btn kt-btn-primary">{{ __('Apply') }}</button>
        </div>
       </form>
      </div>
     </div>

     {{-- Audit table (desktop / tablet) --}}
     <div class="kt-card">
      <div class="kt-card-header items-center justify-between gap-3">
       <h3 class="kt-card-title text-sm">
        @if(request('family_id') && isset($families))
         @php $selectedFamily = $families->firstWhere('id', (int) request('family_id')); @endphp
         {{ $selectedFamily ? __('Recent activity') . ' – ' . $selectedFamily->name : __('Recent activity (platform-wide)') }}
        @else
         {{ __('Recent activity (platform-wide)') }}
        @endif
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
           <th class="px-4 py-2 text-start font-medium min-w-40">{{ __('When') }}</th>
           <th class="px-4 py-2 text-start font-medium min-w-40">{{ __('Who') }}</th>
           <th class="px-4 py-2 text-start font-medium min-w-32">{{ __('Family') }}</th>
           <th class="px-4 py-2 text-start font-medium min-w-32">{{ __('Type') }}</th>
           <th class="px-4 py-2 text-start font-medium min-w-44">{{ __('Area') }}</th>
           <th class="px-4 py-2 text-start font-medium min-w-64">{{ __('Action / Description') }}</th>
           <th class="px-4 py-2 text-end font-medium min-w-20">{{ __('IP') }}</th>
          </tr>
         </thead>
         <tbody>
          @forelse($logs as $log)
          <tr class="border-b border-border last:border-0 hover:bg-accent/20">
           <td class="px-4 py-2 align-top">
            <div class="flex flex-col">
             <span class="font-medium text-mono text-xs">{{ $log->created_at->format('Y-m-d · H:i') }}</span>
             <span class="text-[11px] text-muted-foreground">{{ $log->created_at->diffForHumans() }}</span>
            </div>
           </td>
           <td class="px-4 py-2 align-top">
            @if($log->user)
             <div class="flex flex-col">
              <span class="text-xs font-medium text-mono">{{ $log->user->name }}</span>
              <span class="text-[11px] text-muted-foreground">{{ $log->user->email }}</span>
             </div>
            @else
             <span class="text-[11px] text-muted-foreground">—</span>
            @endif
           </td>
           <td class="px-4 py-2 align-top">
            @if($log->family)
             <span class="text-xs text-muted-foreground">{{ $log->family->name }}</span>
            @else
             <span class="text-[11px] text-muted-foreground">—</span>
            @endif
           </td>
           <td class="px-4 py-2 align-top">
            <span class="kt-badge kt-badge-xs {{ $log->type === 'database' ? 'kt-badge-outline kt-badge-primary' : 'kt-badge-outline kt-badge-secondary' }}">
             {{ $log->type === 'database' ? __('Database') : __('Application') }}
            </span>
           </td>
           <td class="px-4 py-2 align-top">
            <span class="kt-badge kt-badge-xs kt-badge-outline kt-badge-primary">{{ $log->area }}</span>
           </td>
           <td class="px-4 py-2 align-top">
            <span class="text-xs font-medium text-foreground">{{ $log->action }}</span>
            <span class="text-[11px] text-muted-foreground block mt-0.5">{{ $log->description ?? '—' }}</span>
           </td>
           <td class="px-4 py-2 align-top text-end">
            <span class="text-[11px] text-muted-foreground">{{ $log->ip ?? '—' }}</span>
           </td>
          </tr>
          @empty
          <tr>
           <td colspan="7" class="px-4 py-8 text-center text-muted-foreground text-sm">{{ __('No audit entries yet.') }}</td>
          </tr>
          @endforelse
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
      @if(isset($logs) && $logs->hasPages())
      <div class="kt-card-footer justify-between items-center flex-wrap gap-3">
       <span class="text-xs text-muted-foreground">{{ __('Showing') }} {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} {{ __('of') }} {{ $logs->total() }}</span>
       <div class="flex items-center gap-2">
        @if($logs->onFirstPage())
         <span class="kt-btn kt-btn-xs kt-btn-ghost opacity-50">{{ __('Previous') }}</span>
        @else
         <a href="{{ $logs->previousPageUrl() }}" class="kt-btn kt-btn-xs kt-btn-outline">{{ __('Previous') }}</a>
        @endif
        @if($logs->hasMorePages())
         <a href="{{ $logs->nextPageUrl() }}" class="kt-btn kt-btn-xs kt-btn-outline">{{ __('Next') }}</a>
        @else
         <span class="kt-btn kt-btn-xs kt-btn-ghost opacity-50">{{ __('Next') }}</span>
        @endif
       </div>
      </div>
      @endif
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
      <div class="kt-card-content px-4 py-4">
       <div class="audit-summary-grid grid gap-3">
        <button
         type="button"
         class="audit-summary-card min-w-0 rounded-xl border border-border bg-muted/40 px-3.5 py-3 flex flex-col gap-1 text-left cursor-pointer hover:bg-muted/70 transition-colors"
         data-title="{{ __('Total events') }}"
         data-value="128"
         data-description="{{ __('Total number of audit events recorded across the selected scope in the last 30 days.') }}"
        >
         <span class="text-[11px] text-secondary-foreground uppercase tracking-wide">
          {{ __('Total events') }}
         </span>
         <span class="text-sm font-semibold text-mono">
          128
         </span>
        </button>
        <button
         type="button"
         class="audit-summary-card min-w-0 rounded-xl border border-border bg-muted/40 px-3.5 py-3 flex flex-col gap-1 text-left cursor-pointer hover:bg-muted/70 transition-colors"
         data-title="{{ __('Critical changes') }}"
         data-value="4"
         data-description="{{ __('Important configuration and permission changes that may require review.') }}"
        >
         <span class="text-[11px] text-secondary-foreground uppercase tracking-wide">
          {{ __('Critical changes') }}
         </span>
         <span class="text-sm font-semibold text-destructive">
          4
         </span>
        </button>
        <button
         type="button"
         class="audit-summary-card min-w-0 rounded-xl border border-border bg-muted/40 px-3.5 py-3 flex flex-col gap-1 text-left cursor-pointer hover:bg-muted/70 transition-colors"
         data-title="{{ __('Member updates') }}"
         data-value="9"
         data-description="{{ __('Invitations, role changes and member removals performed in the last 30 days.') }}"
        >
         <span class="text-[11px] text-secondary-foreground uppercase tracking-wide">
          {{ __('Member updates') }}
         </span>
         <span class="text-sm font-semibold text-mono">
          9
         </span>
        </button>
        <button
         type="button"
         class="audit-summary-card min-w-0 rounded-xl border border-border bg-muted/40 px-3.5 py-3 flex flex-col gap-1 text-left cursor-pointer hover:bg-muted/70 transition-colors"
         data-title="{{ __('Budget & wallet changes') }}"
         data-value="21"
         data-description="{{ __('Budget adjustments, wallet openings and key balance changes over the last 30 days.') }}"
        >
         <span class="text-[11px] text-secondary-foreground uppercase tracking-wide">
          {{ __('Budget & wallet changes') }}
         </span>
         <span class="text-sm font-semibold text-mono">
          21
         </span>
        </button>
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

@push('scripts')
<script>
 document.addEventListener('DOMContentLoaded', function () {
     if (typeof Swal === 'undefined') {
         return;
     }

     var cards = document.querySelectorAll('.audit-summary-card');
     cards.forEach(function (card) {
         card.addEventListener('click', function () {
             var title = card.getAttribute('data-title') || '';
             var value = card.getAttribute('data-value') || '';
             var description = card.getAttribute('data-description') || '';

             var html =
                 '<div style="text-align:left;font-size:13px;line-height:1.5;">' +
                     '<p style="margin-bottom:6px;"><strong>Count:</strong> ' + value + '</p>' +
                     (description
                         ? '<p style="font-size:12px;color:#64748b;margin:0;">' + description + '</p>'
                         : '') +
                 '</div>';

             Swal.fire({
                 title: title,
                 html: html,
                 icon: 'info',
                 confirmButtonText: '{{ __('Close') }}',
                 customClass: {
                     popup: 'swal2-rounded',
                     title: 'text-sm font-semibold text-foreground',
                     confirmButton: 'kt-btn kt-btn-sm kt-btn-primary'
                 }
             });
         });
     });
 });
</script>
@endpush

