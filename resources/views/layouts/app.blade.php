<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Netivo'))</title>
    <link rel="shortcut icon" href="{{ asset('assets/logo/netivo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @include('components.otika-font-fallback')
    @include('components.otika-overrides')
    @stack('styles')
</head>
@php($preferences = $userPreferences ?? ['theme' => 'light', 'sidebar' => 'expanded', 'navbar' => 'sticky', 'sidebar_color' => 'light', 'color_theme' => 'white'])
<body class="{{ $preferences['theme'] === 'dark' ? 'dark' : 'light' }} {{ $preferences['sidebar_color'] === 'dark' ? 'dark-sidebar' : 'light-sidebar' }} theme-{{ $preferences['color_theme'] }} {{ $preferences['sidebar'] === 'compact' ? 'sidebar-mini' : '' }} {{ $preferences['navbar'] === 'static' ? 'navbar-static' : '' }}">
    <div class="loader"></div>
    <div id="app"><div class="main-wrapper main-wrapper-1">
        <div class="navbar-bg"></div>
        @include('partials.navbar')
        @include('partials.sidebar')
        <div class="main-content"><section class="section">
            <div class="section-header"><h1>@yield('page-title', 'Dashboard')</h1><div class="section-header-breadcrumb"><div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>@hasSection('breadcrumb')<div class="breadcrumb-item active">@yield('breadcrumb')</div>@endif</div></div>
            <div class="section-body">@include('components.flash-message')@yield('content')</div>
        </section></div>
        @include('partials.footer')
    </div></div>
    <script src="{{ asset('js/app.min.js') }}"></script>
    @stack('plugin-scripts')
    @stack('config-scripts')
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @include('components.sweetalert')
    @include('components.otika-table-tooltips')
    @include('components.modal-fix')
    @stack('scripts')
</body>
</html>
