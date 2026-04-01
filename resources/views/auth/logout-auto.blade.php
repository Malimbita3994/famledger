<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Signing out…') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; margin: 2rem; color: #334155;">
    <p>{{ __('Signing you out…') }}</p>
    <form id="logout-form" method="POST" action="{{ route('logout') }}">
        @csrf
    </form>
    <script>
        document.getElementById('logout-form').submit();
    </script>
    <noscript>
        <p><button type="submit" form="logout-form">{{ __('Continue sign out') }}</button></p>
    </noscript>
</body>
</html>
