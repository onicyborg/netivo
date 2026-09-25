@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')
@section('breadcrumb', 'Dashboard')

@push('styles')
    @include('dashboard.partials.styles')
@endpush

@section('content')
<div class="dashboard-content">
    <div class="dashboard-section"><div class="dashboard-period-summary"><i data-feather="calendar" aria-hidden="true"></i><span>Periode aktif</span><strong>{{ $period }}</strong></div></div>

    <div class="row dashboard-section">
        @include('components.card-statistic', ['label' => 'Total Tagihan', 'value' => $billTotal, 'icon' => 'file-text', 'variant' => 'neutral', 'description' => $billTotal === 0 ? 'Tidak ada tagihan periode ini' : 'Tagihan pada periode ini'])
        @include('components.card-statistic', ['label' => 'Lunas', 'value' => $billStatuses['lunas'], 'icon' => 'check-circle', 'variant' => 'positive', 'description' => $billStatuses['lunas'] === 0 ? 'Belum ada tagihan lunas' : 'Tagihan berhasil dilunasi'])
        @include('components.card-statistic', ['label' => 'Belum Bayar', 'value' => $billStatuses['belum_bayar'], 'icon' => 'clock', 'variant' => 'warning', 'description' => $billStatuses['belum_bayar'] === 0 ? 'Tidak ada tagihan belum bayar' : 'Perlu ditindaklanjuti'])
        @include('components.card-statistic', ['label' => 'Terlambat', 'value' => $billStatuses['terlambat'], 'icon' => 'alert-triangle', 'variant' => 'danger', 'description' => $billStatuses['terlambat'] === 0 ? 'Tidak ada tagihan terlambat' : 'Perlu segera ditindaklanjuti'])
        @include('components.card-statistic', ['label' => 'Menunggu Verifikasi', 'value' => $billStatuses['menunggu_verifikasi'], 'icon' => 'loader', 'variant' => 'warning', 'description' => $billStatuses['menunggu_verifikasi'] === 0 ? 'Tidak ada pembayaran menunggu' : 'Menunggu pemeriksaan admin'])
        @include('components.card-statistic', ['label' => 'Pendapatan Hari Ini', 'value' => format_rupiah($revenueToday), 'icon' => 'dollar-sign', 'variant' => 'positive', 'description' => (float) $revenueToday === 0.0 ? 'Tidak ada pembayaran hari ini' : 'Total pembayaran terkonfirmasi'])
        @include('components.card-statistic', ['label' => 'Pendapatan Bulan Ini', 'value' => format_rupiah($revenueMonth), 'icon' => 'trending-up', 'variant' => 'positive', 'description' => (float) $revenueMonth === 0.0 ? 'Tidak ada pendapatan bulan ini' : 'Akumulasi bulan berjalan'])
        @include('components.card-statistic', ['label' => 'Payment Pending', 'value' => $pendingPayments, 'icon' => 'inbox', 'variant' => 'warning', 'description' => $pendingPayments === 0 ? 'Tidak ada payment pending' : 'Perlu verifikasi'])
        @include('components.card-statistic', ['label' => 'Perubahan Layanan Pending', 'value' => $pendingUpgrades, 'icon' => 'arrow-up-circle', 'variant' => 'danger', 'description' => $pendingUpgrades === 0 ? 'Tidak ada perubahan pending' : 'Perlu keputusan admin'])
    </div>

    <div class="row dashboard-panel-row dashboard-section">
        <div class="col-12 col-lg-6"><div class="card h-100"><div class="card-header"><h4>Payment Pending</h4><div class="card-header-action"><a href="{{ route('admin.payments.index', ['status' => 'pending']) }}">Lihat semua</a></div></div><div class="card-body"><p>{{ $pendingPayments }} pembayaran menunggu verifikasi.</p>@if($pendingPayments === 0)<div class="dashboard-empty-state"><div class="dashboard-empty-state__icon"><i data-feather="inbox"></i></div><p class="lead">Tidak ada payment pending.</p></div>@else<a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="btn btn-sm btn-primary">Buka verifikasi</a>@endif</div></div></div>
        <div class="col-12 col-lg-6"><div class="card h-100"><div class="card-header"><h4>Perubahan Layanan Pending</h4><div class="card-header-action"><a href="{{ route('admin.upgrades.index', ['status' => 'pending']) }}">Lihat semua</a></div></div><div class="card-body"><p>{{ $pendingUpgrades }} pengajuan menunggu keputusan.</p>@if($pendingUpgrades === 0)<div class="dashboard-empty-state"><div class="dashboard-empty-state__icon"><i data-feather="arrow-up-circle"></i></div><p class="lead">Tidak ada perubahan layanan pending.</p></div>@else<a href="{{ route('admin.upgrades.index', ['status' => 'pending']) }}" class="btn btn-sm btn-primary">Buka pengajuan</a>@endif</div></div></div>
    </div>

    <div class="dashboard-section">@include('dashboard.partials.transactions', ['transactions' => $transactions])</div>
</div>
@endsection
