<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Terjadi Kesalahan') - {{ config('app.name', 'Netivo') }}</title>
    <link rel="shortcut icon" href="{{ asset('img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <div id="app"><section class="section"><div class="container"><div class="row"><div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-6 offset-lg-3"><div class="card card-primary"><div class="card-body text-center"><div class="empty-state"><div class="empty-state-icon bg-danger"><i data-feather="alert-triangle"></i></div><h2>@yield('code')</h2><p class="lead">@yield('message')</p><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary mt-4">Kembali</a></div></div></div></div></div></div></section></div>
    <script src="{{ asset('js/app.min.js') }}"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
</body>
</html>
