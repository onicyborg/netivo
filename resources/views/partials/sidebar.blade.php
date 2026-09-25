<div class="main-sidebar sidebar-style-2"><aside id="sidebar-wrapper">
    <div class="sidebar-brand"><a href="{{ route('dashboard') }}"><img src="/assets/logo/netivo.png" alt="Logo Netivo" class="header-logo"><span class="logo-name">Netivo</span></a></div>
    <div class="sidebar-brand sidebar-brand-sm"><a href="{{ route('dashboard') }}"><img src="/assets/logo/netivo.png" alt="Logo Netivo" class="header-logo"></a></div>
    @php($role = auth()->user()->role->value)
    <ul class="sidebar-menu">
        <li class="menu-header">MENU UTAMA</li><li class="{{ request()->routeIs('admin.dashboard','supervisor.dashboard','customer.dashboard') ? 'active' : '' }}"><a class="nav-link" href="{{ route('dashboard') }}"><i data-feather="monitor"></i><span>Dashboard</span></a></li>
        @if($role === 'admin')
            <li class="menu-header">ADMINISTRASI</li>
            @foreach([['admin.services.*','admin.services.index','package','Layanan'],['admin.payment-methods.*','admin.payment-methods.index','credit-card','Metode Pembayaran'],['admin.customers.*','admin.customers.index','users','Customer'],['admin.bills.*','admin.bills.index','file-text','Tagihan'],['admin.payments.*','admin.payments.index','check-circle','Verifikasi Pembayaran'],['admin.settings.*','admin.settings.index','settings','Pengaturan'],['admin.cron-logs.*','admin.cron-logs.index','activity','Log Cron'],['admin.system-logs.*','admin.system-logs.index','shield','Audit Log'],['admin.reports.*','admin.reports.index','bar-chart-2','Laporan Harian'],['admin.upgrades.*','admin.upgrades.index','trending-up','Upgrade Layanan']] as [$pattern,$route,$icon,$label])<li class="{{ request()->routeIs($pattern) ? 'active' : '' }}"><a class="nav-link" href="{{ route($route) }}"><i data-feather="{{ $icon }}"></i><span>{{ $label }}</span></a></li>@endforeach
        @elseif($role === 'supervisor')
            <li class="menu-header">PEMANTAUAN</li>
            @foreach([['supervisor.bills.*','supervisor.bills.index','file-text','Tagihan'],['supervisor.payments.*','supervisor.payments.index','check-circle','Pembayaran'],['supervisor.reports.*','supervisor.reports.index','bar-chart-2','Laporan Harian'],['supervisor.system-logs.*','supervisor.system-logs.index','shield','Audit Log']] as [$pattern,$route,$icon,$label])<li class="{{ request()->routeIs($pattern) ? 'active' : '' }}"><a class="nav-link" href="{{ route($route) }}"><i data-feather="{{ $icon }}"></i><span>{{ $label }}</span></a></li>@endforeach
        @else
            <li class="menu-header">LAYANAN SAYA</li>@foreach([['customer.bills.*','customer.bills.index','file-text','Tagihan Saya'],['customer.payments.*','customer.payments.index','credit-card','Pembayaran'],['customer.services.*','customer.services.index','trending-up','Layanan Saya']] as [$pattern,$route,$icon,$label])<li class="{{ request()->routeIs($pattern) ? 'active' : '' }}"><a class="nav-link" href="{{ route($route) }}"><i data-feather="{{ $icon }}"></i><span>{{ $label }}</span></a></li>@endforeach
        @endif
        <li class="menu-header">AKUN</li><li class="{{ request()->routeIs('profile') ? 'active' : '' }}"><a class="nav-link" href="{{ route('profile') }}"><i data-feather="user"></i><span>Profil Saya</span></a></li>
    </ul>
</aside></div>
