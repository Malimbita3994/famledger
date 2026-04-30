@extends('layouts.metronic')

@section('title', __('Session expired'))
@section('page_title', '')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center text-muted-foreground text-sm">
        {{ __('Your session has expired or the security token is no longer valid.') }}
    </div>
</div>

@push('scripts')
<script>
 document.addEventListener('DOMContentLoaded', function () {
  var safeUrl = @json(
      auth()->check()
          ? (auth()->user()->can('access_admin_panel') ? route('admin.dashboard') : route('dashboard'))
          : route('login')
  );

  function goSafe() {
   if (window.history.length > 1) {
    window.history.back();
   } else {
    window.location.href = safeUrl;
   }
  }

  if (!window.Swal) {
   goSafe();
   return;
  }

  Swal.fire({
   icon: 'warning',
   title: @json(__('Session expired')),
   html: @json(__('This page was open too long or the security token is no longer valid. Refresh the page and try again.')),
   confirmButtonText: @json(__('Continue')),
   confirmButtonColor: '#2563eb',
   backdrop: true,
   allowOutsideClick: false,
   customClass: {
    popup: 'rounded-2xl',
    title: 'text-base font-semibold',
   }
  }).then(function () {
   goSafe();
  });
 });
</script>
@endpush
@endsection
