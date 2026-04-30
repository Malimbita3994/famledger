{{--
  Authenticated app: session flash Swal, swalToast / swalAlert / swalConfirm, js-confirm-delete, js-confirm-logout.
  Requires SweetAlert2 + canvas-confetti loaded before this (see layouts/metronic).
--}}
@php
    $sessionError = session('error');
    $skipValidationFlashForSwal = request()->routeIs('families.goals.create');
    $errorBag = view()->shared('errors');
    $hasValidationForSwal = $errorBag instanceof \Illuminate\Support\ViewErrorBag
        && $errorBag->any();
    $validationError = ! $sessionError && ! $skipValidationFlashForSwal && $hasValidationForSwal
        ? $errorBag->first()
        : null;
    $flashMessages = [
        'success' => session('success'),
        'error'   => $sessionError ?: $validationError,
        'warning' => session('warning'),
        'info'    => session('info'),
    ];
@endphp
<x-sweetalert.compact-styles />
<!-- Global SweetAlert2: flash messages and confirm-delete, styled to fit Metronic -->
<script>
 (function () {
 var Swal = window.Swal;
 if (!Swal) return;

 var flash = @json($flashMessages);

 function fireConfetti() {
  if (!window.confetti) return;
  try {
   var duration = 1200;
   var end = Date.now() + duration;
   (function frame() {
    window.confetti({
     particleCount: 40,
     spread: 70,
     origin: { y: 0.25, x: 0.5 }
    });
    if (Date.now() < end) {
     requestAnimationFrame(frame);
    }
   })();
  } catch (e) {}
 }

 function showFlash() {
  var type = flash.success ? 'success' : flash.error ? 'error' : flash.warning ? 'warning' : flash.info ? 'info' : null;
  var msg = flash.success || flash.error || flash.warning || flash.info;
  if (!type || !msg) return;

  var isSuccess = type === 'success';

  Swal.fire({
   icon: type,
   title: isSuccess ? msg : (type.charAt(0).toUpperCase() + type.slice(1)),
   text: isSuccess ? '' : msg,
   showConfirmButton: true,
   confirmButtonText: isSuccess ? 'Great, thanks' : 'OK',
   backdrop: true,
   customClass: {
    popup: 'rounded-2xl',
    title: 'text-base font-semibold',
   },
   didOpen: function () {
    if (isSuccess) {
     fireConfetti();
    }
   }
  });
 }

 if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', showFlash);
 } else {
  showFlash();
 }

 window.swalToast = function (msg, type) {
  type = type || 'success';
  Swal.fire({
   icon: type,
   text: msg,
   timer: 3000,
   timerProgressBar: true,
   showConfirmButton: false,
   toast: true,
   position: 'top-end',
   didOpen: function () {
    if (type === 'success') {
     fireConfetti();
    }
   }
  });
 };

 var swalModalBase = {
  customClass: { popup: 'rounded-2xl' },
 };

 window.swalAlert = function (opts) {
  return Swal.fire(Object.assign({}, swalModalBase, opts));
 };

 window.swalConfirm = function (opts) {
  return Swal.fire(Object.assign({}, swalModalBase, {
   showCancelButton: true,
   confirmButtonColor: '#dc2626',
   cancelButtonColor: '#6b7280',
  }, opts));
 };

 document.body.addEventListener('submit', function (e) {
  var form = e.target;
  if (!form || !form.classList.contains('js-confirm-delete')) return;
  e.preventDefault();
  var title = form.getAttribute('data-confirm-title') || 'Are you sure?';
  var text = form.getAttribute('data-confirm-message') || 'This action cannot be undone.';
  var isDanger = form.classList.contains('js-confirm-delete-danger');
  var yes = form.getAttribute('data-confirm-yes') || (isDanger ? 'Yes, delete permanently' : 'Yes, delete');
  var no = form.getAttribute('data-confirm-no') || 'Cancel';
  var dangerNote = form.getAttribute('data-confirm-danger-note') || 'This cannot be undone.';

  var baseOpts = {
   title: title,
   showCancelButton: true,
   cancelButtonText: no,
   confirmButtonText: yes,
   cancelButtonColor: '#64748b',
  };

  if (isDanger) {
   var esc = String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
   var escNote = String(dangerNote)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
   Swal.fire(Object.assign({}, swalModalBase, baseOpts, {
    html:
     '<p class="text-sm text-slate-600 dark:text-slate-300 text-left leading-relaxed m-0">' +
     esc +
     '</p>' +
     '<p class="text-xs font-semibold text-red-600 dark:text-red-400 mt-3 mb-0 text-center">' +
     '⚠ ' +
     escNote +
     '</p>',
    icon: 'warning',
    iconColor: '#dc2626',
    confirmButtonColor: '#991b1b',
    focusCancel: true,
    reverseButtons: true,
    customClass: {
     popup: 'rounded-2xl swal2-danger-confirm',
     title: 'text-base font-bold',
     htmlContainer: 'text-left',
    },
   })).then(function (r) {
    if (r.isConfirmed) form.submit();
   });
   return;
  }

  Swal.fire(Object.assign({}, swalModalBase, baseOpts, {
   text: text,
   icon: 'warning',
   confirmButtonColor: '#dc2626',
   confirmButtonText: yes,
  })).then(function (r) {
   if (r.isConfirmed) form.submit();
  });
 });

 document.body.addEventListener('submit', function (e) {
  var form = e.target;
  if (!form || !form.classList.contains('js-confirm-logout')) return;
  e.preventDefault();
  var title = form.getAttribute('data-confirm-title') || 'Log out?';
  var text = form.getAttribute('data-confirm-message') || '';
  var yes = form.getAttribute('data-confirm-yes') || 'Log out';
  var no = form.getAttribute('data-confirm-no') || 'Cancel';
  Swal.fire(Object.assign({}, swalModalBase, {
   title: title,
   text: text || undefined,
   icon: 'question',
   showCancelButton: true,
   confirmButtonColor: '#dc2626',
   cancelButtonColor: '#6b7280',
   confirmButtonText: yes,
   cancelButtonText: no,
   focusCancel: true,
  })).then(function (r) {
   if (r.isConfirmed) form.submit();
  });
 });
 })();
</script>
