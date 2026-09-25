<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>@yield('title', config('app.name', 'Netivo'))</title>
    <link rel="shortcut icon" href="{{ asset('img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @include('components.otika-font-fallback')
    @include('components.netivo-design-system')
    @stack('styles')
</head>
@php($preferences = $userPreferences ?? ['theme' => 'light', 'sidebar' => 'expanded', 'navbar' => 'sticky', 'sidebar_color' => 'light', 'color_theme' => 'white'])
<body class="{{ $preferences['theme'] === 'dark' ? 'dark' : 'light' }} {{ $preferences['sidebar_color'] === 'dark' ? 'dark-sidebar' : 'light-sidebar' }} theme-{{ $preferences['color_theme'] }} {{ $preferences['sidebar'] === 'compact' ? 'sidebar-mini' : '' }} {{ $preferences['navbar'] === 'static' ? 'navbar-static' : '' }}">
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar {{ $preferences['navbar'] === 'sticky' ? 'sticky' : '' }}">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn" aria-label="Buka atau tutup menu"><i data-feather="align-justify"></i></a></li>
                        <li><a href="#" class="nav-link nav-link-lg fullscreen-btn" aria-label="Layar penuh"><i data-feather="maximize"></i></a></li>
                    </ul>
                </div>
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg" aria-label="Notifikasi"><i data-feather="bell"></i><span class="badge badge-primary badge-pill">{{ $unreadNotificationCount ?? 0 }}</span></a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right">
                            <div class="dropdown-header">Notifikasi <div class="float-right"><a href="{{ route('notifications.index') }}">Lihat semua</a></div></div>
                            <div class="dropdown-list-content dropdown-list-icons">
                                @forelse(($navbarNotifications ?? collect()) as $notification)
                                    <a href="{{ route('notifications.read', $notification) }}" class="dropdown-item {{ $notification->read_at ? '' : 'dropdown-item-unread' }}"><div class="dropdown-item-icon bg-{{ $notification->read_at ? 'light' : 'primary' }} text-{{ $notification->read_at ? 'muted' : 'white' }}"><i data-feather="bell"></i></div><div class="dropdown-item-desc"><strong>{{ $notification->title }}</strong><div>{{ $notification->message }}</div><small>{{ $notification->created_at->diffForHumans() }}</small></div></a>
                                @empty
                                    <div class="dropdown-item text-center text-muted">Belum ada notifikasi.</div>
                                @endforelse
                            </div>
                        </div>
                    </li>
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user antre-profile" aria-label="Buka menu pengguna">
                            <img alt="Foto profil default" src="{{ asset('img/user.png') }}" class="user-img-radious-style">
                            <span class="antre-profile__copy d-none d-sm-inline-flex"><span class="antre-profile__name">{{ auth()->user()->name }}</span><small>{{ ucfirst(auth()->user()->role->value) }}</small></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Masuk sebagai {{ auth()->user()->role->value }}</div>
                            <a href="{{ route('profile') }}" class="dropdown-item has-icon"><i data-feather="user"></i> Profil</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item has-icon text-danger"><i data-feather="log-out"></i> Keluar</button>
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    @php($roleValue = auth()->user()->role->value)
                    <div class="sidebar-brand">
                        <a href="{{ route('dashboard') }}"><img src="{{ asset('img/logo.png') }}" alt="Logo Netivo" class="header-logo"><span class="logo-name">Netivo</span></a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">MENU UTAMA</li>
                        <li class="{{ request()->routeIs('admin.dashboard', 'supervisor.dashboard', 'customer.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a>
                        </li>
                        @if ($roleValue === 'admin')
                            <li class="menu-header">ADMINISTRASI</li>
                            <li class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}"><a href="{{ route('admin.services.index') }}" class="nav-link"><i data-feather="package"></i><span>Layanan</span></a></li>
                            <li class="{{ request()->routeIs('admin.payment-methods.*') ? 'active' : '' }}"><a href="{{ route('admin.payment-methods.index') }}" class="nav-link"><i data-feather="credit-card"></i><span>Metode Pembayaran</span></a></li>
                            <li class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"><a href="{{ route('admin.customers.index') }}" class="nav-link"><i data-feather="users"></i><span>Customer</span></a></li>
                            <li class="{{ request()->routeIs('admin.bills.*') ? 'active' : '' }}"><a href="{{ route('admin.bills.index') }}" class="nav-link"><i data-feather="file-text"></i><span>Tagihan</span></a></li>
                            <li class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"><a href="{{ route('admin.payments.index') }}" class="nav-link"><i data-feather="check-circle"></i><span>Verifikasi Pembayaran</span></a></li>
                            <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><a href="{{ route('admin.settings.index') }}" class="nav-link"><i data-feather="settings"></i><span>Pengaturan</span></a></li>
                            <li class="{{ request()->routeIs('admin.cron-logs.*') ? 'active' : '' }}"><a href="{{ route('admin.cron-logs.index') }}" class="nav-link"><i data-feather="activity"></i><span>Log Cron</span></a></li>
                            <li class="{{ request()->routeIs('admin.system-logs.*') ? 'active' : '' }}"><a href="{{ route('admin.system-logs.index') }}" class="nav-link"><i data-feather="shield"></i><span>Audit Log</span></a></li>
                            <li class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><a href="{{ route('admin.reports.index') }}" class="nav-link"><i data-feather="bar-chart-2"></i><span>Laporan Harian</span></a></li>
                            <li class="{{ request()->routeIs('admin.upgrades.*') ? 'active' : '' }}"><a href="{{ route('admin.upgrades.index') }}" class="nav-link"><i data-feather="trending-up"></i><span>Upgrade Layanan</span></a></li>
                        @elseif ($roleValue === 'supervisor')
                            <li class="menu-header">PEMANTAUAN</li>
                            <li><a href="#" class="nav-link disabled" aria-disabled="true" tabindex="-1"><i data-feather="file-text"></i><span>Laporan Harian</span></a></li>
                            <li class="{{ request()->routeIs('supervisor.bills.*') ? 'active' : '' }}"><a href="{{ route('supervisor.bills.index') }}" class="nav-link"><i data-feather="credit-card"></i><span>Tagihan</span></a></li>
                            <li class="{{ request()->routeIs('supervisor.payments.*') ? 'active' : '' }}"><a href="{{ route('supervisor.payments.index') }}" class="nav-link"><i data-feather="check-circle"></i><span>Pembayaran</span></a></li>
                            <li class="{{ request()->routeIs('supervisor.reports.*') ? 'active' : '' }}"><a href="{{ route('supervisor.reports.index') }}" class="nav-link"><i data-feather="bar-chart-2"></i><span>Laporan Harian</span></a></li>
                            <li class="{{ request()->routeIs('supervisor.system-logs.*') ? 'active' : '' }}"><a href="{{ route('supervisor.system-logs.index') }}" class="nav-link"><i data-feather="shield"></i><span>Audit Log</span></a></li>
                        @else
                            <li class="menu-header">LAYANAN SAYA</li>
                            <li class="{{ request()->routeIs('customer.bills.*') ? 'active' : '' }}"><a href="{{ route('customer.bills.index') }}" class="nav-link"><i data-feather="file-text"></i><span>Tagihan Saya</span></a></li>
                            <li class="{{ request()->routeIs('customer.payments.*') ? 'active' : '' }}"><a href="{{ route('customer.payments.index') }}" class="nav-link"><i data-feather="credit-card"></i><span>Pembayaran</span></a></li>
                            <li class="{{ request()->routeIs('customer.services.*') ? 'active' : '' }}"><a href="{{ route('customer.services.index') }}" class="nav-link"><i data-feather="trending-up"></i><span>Layanan Saya</span></a></li>
                        @endif
                        <li class="menu-header">AKUN</li>
                        <li class="{{ request()->routeIs('profile') ? 'active' : '' }}">
                            <a href="{{ route('profile') }}" class="nav-link"><i data-feather="user"></i><span>Profil Saya</span></a>
                        </li>
                    </ul>
                </aside>
            </div>

            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h1>@yield('page-title', 'Dashboard')</h1>
                        <div class="section-header-breadcrumb">
                            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                            @hasSection('breadcrumb')<div class="breadcrumb-item active">@yield('breadcrumb')</div>@endif
                        </div>
                    </div>
                    <div class="section-body">
                        @include('components.flash-message')
                        @yield('content')
                    </div>
                </section>
            </div>

            <aside class="settingSidebar" aria-label="Panel pengaturan tampilan">
                <a href="javascript:void(0)" class="settingPanelToggle" aria-label="Buka atau tutup panel tampilan"><i class="fa fa-spin fa-cog" aria-hidden="true"></i></a>
                <div class="settingSidebar-body ps-container ps-theme-default">
                    <form method="POST" action="{{ route('preferences.update') }}" id="display-preferences-form">
                        @csrf
                        @method('PUT')
                        <div class="setting-panel-header">Panel Tampilan</div>
                        <div class="p-15 border-bottom">
                            <h6 class="font-medium m-b-10">Pilih Layout</h6>
                            <div class="selectgroup layout-color w-50">
                                <label class="selectgroup-item"><input type="radio" name="theme_choice" value="1" class="selectgroup-input-radio select-layout" @checked($preferences['theme'] === 'light')><span class="selectgroup-button">Terang</span></label>
                                <label class="selectgroup-item"><input type="radio" name="theme_choice" value="2" class="selectgroup-input-radio select-layout" @checked($preferences['theme'] === 'dark')><span class="selectgroup-button">Gelap</span></label>
                                <input type="hidden" name="theme" id="preference-theme" value="{{ $preferences['theme'] }}">
                            </div>
                        </div>
                        <div class="p-15 border-bottom">
                            <h6 class="font-medium m-b-10">Warna Sidebar</h6>
                            <div class="selectgroup selectgroup-pills sidebar-color">
                                <label class="selectgroup-item"><input type="radio" name="sidebar_color_choice" value="1" class="selectgroup-input select-sidebar" @checked($preferences['sidebar_color'] === 'light')><span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip" title="Sidebar terang"><i class="fas fa-sun" aria-hidden="true"></i><span class="sr-only">Sidebar terang</span></span></label>
                                <label class="selectgroup-item"><input type="radio" name="sidebar_color_choice" value="2" class="selectgroup-input select-sidebar" @checked($preferences['sidebar_color'] === 'dark')><span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip" title="Sidebar gelap"><i class="fas fa-moon" aria-hidden="true"></i><span class="sr-only">Sidebar gelap</span></span></label>
                                <input type="hidden" name="sidebar_color" id="preference-sidebar-color" value="{{ $preferences['sidebar_color'] }}">
                            </div>
                        </div>
                        <div class="p-15 border-bottom">
                            <h6 class="font-medium m-b-10">Warna Tema</h6>
                            <ul class="choose-theme list-unstyled mb-0" aria-label="Pilih warna tema">
                                @foreach(['white', 'cyan', 'black', 'purple', 'orange', 'green', 'red'] as $color)
                                    <li title="{{ $color }}" class="{{ $preferences['color_theme'] === $color ? 'active' : '' }}"><button type="button" class="theme-swatch {{ $color }}" data-theme-color="{{ $color }}" aria-label="Tema {{ ucfirst($color) }}"></button></li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="color_theme" id="preference-color-theme" value="{{ $preferences['color_theme'] }}">
                        </div>
                        <div class="p-15 border-bottom"><input type="hidden" name="sidebar" value="expanded"><label class="m-b-0 custom-switch"><input type="checkbox" name="sidebar" value="compact" class="custom-switch-input" id="mini_sidebar_setting" @checked($preferences['sidebar'] === 'compact')><span class="custom-switch-indicator"></span><span class="control-label p-l-10">Sidebar ringkas</span></label></div>
                        <div class="p-15 border-bottom"><input type="hidden" name="navbar" value="static"><label class="m-b-0 custom-switch"><input type="checkbox" name="navbar" value="sticky" class="custom-switch-input" id="sticky_header_setting" @checked($preferences['navbar'] === 'sticky')><span class="custom-switch-indicator"></span><span class="control-label p-l-10">Header mengikuti layar</span></label></div>
                        <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele"><button type="button" class="btn btn-light btn-restore-theme"><i class="fas fa-undo" aria-hidden="true"></i> Pulihkan default</button><button type="submit" class="btn btn-primary btn-block mt-3"><i class="fas fa-save" aria-hidden="true"></i> Simpan preferensi</button></div>
                    </form>
                </div>
            </aside>

            <footer class="main-footer">
                <div class="footer-left">Copyright &copy; {{ date('Y') }} Netivo</div>
                <div class="footer-right">Sistem Pembayaran Billing Internet</div>
            </footer>
        </div>
    </div>
    <script src="{{ asset('js/app.min.js') }}"></script>
    @stack('plugin-scripts')
    @stack('config-scripts')
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @include('components.modal-fix')
    @include('components.otika-preferences-script', ['preferences' => $preferences])
    @stack('scripts')
</body>
</html>
