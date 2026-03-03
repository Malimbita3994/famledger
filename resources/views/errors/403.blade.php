@extends('layouts.metronic')

@section('title', 'Access denied')
@section('page_title', '')

@section('content')
<div class="kt-container-fixed px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center text-muted-foreground text-sm">
        You do not have permission to view this page.
    </div>
</div>

@push('scripts')
<script>
 document.addEventListener('DOMContentLoaded', function () {
  if (!window.Swal) return;

  Swal.fire({
   icon: 'error',
   title: 'Access denied',
   text: 'You do not have permission to view this page.',
   confirmButtonText: 'Back to safety',
   confirmButtonColor: '#2563eb',
   width: 520,
   padding: '2.5rem 2.75rem',
   backdrop: true,
   customClass: {
    popup: 'rounded-2xl',
    title: 'text-lg font-semibold',
   }
  }).then(function () {
   // Prefer going back if there is history, otherwise send to a safe page
   if (window.history.length > 1) {
    window.history.back();
   } else {
    window.location.href = "{{ auth()->check() ? (auth()->user()->can('access_admin_panel') ? route('admin.dashboard') : route('dashboard')) : url('/') }}";
   }
  });
 });
</script>
@endpush
@endsection

