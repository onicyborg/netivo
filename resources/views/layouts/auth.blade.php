<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') - {{ config('app.name', 'Netivo') }}</title>
    <link rel="shortcut icon" href="/assets/logo/netivo.png">
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @include('components.otika-font-fallback')
    @stack('styles')
</head>
<body>
    <div class="loader"></div>
    <div id="app">@yield('content')</div>
    <script src="{{ asset('js/app.min.js') }}"></script>
    @stack('plugin-scripts')
    @stack('config-scripts')
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @include('components.sweetalert')
    @stack('scripts')
</body>
</html>
