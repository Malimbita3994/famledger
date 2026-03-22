@php
    $logoPng = public_path('images/logo.png');
    $usePng = is_file($logoPng);
    $iconHref = $usePng ? asset('images/logo.png') : asset('metronic/assets/media/app/logo-32.svg');
    $iconType = $usePng ? 'image/png' : 'image/svg+xml';
@endphp
<link rel="icon" href="{{ $iconHref }}" type="{{ $iconType }}" sizes="any">
<link rel="shortcut icon" href="{{ $iconHref }}" type="{{ $iconType }}">
<link rel="apple-touch-icon" href="{{ $iconHref }}">
