@extends('layouts.app')

@section('title', 'Dashboard Supervisor')
@section('page-title', 'Dashboard Supervisor')
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
        @include('components.card-statistic', ['label' => 'Menunggu Verifikasi', 'value' => $billStatuses['menunggu_verifikasi'], 'icon' => 'loader', 'variant' => 'warning', 'description' => $billStatuses['menunggu_verifikasi'] === 0 ? 'Tidak ada pembayaran menunggu' : 'Menunggu pemeriksaan'])
        @include('components.card-statistic', ['label' => 'Pendapatan Hari Ini', 'value' => format_rupiah($revenueToday), 'icon' => 'dollar-sign', 'variant' => 'positive', 'description' => (float) $revenueToday === 0.0 ? 'Tidak ada pembayaran hari ini' : 'Total pembayaran terkonfirmasi'])
        @include('components.card-statistic', ['label' => 'Pendapatan Bulan Ini', 'value' => format_rupiah($revenueMonth), 'icon' => 'trending-up', 'variant' => 'positive', 'description' => (float) $revenueMonth === 0.0 ? 'Tidak ada pendapatan bulan ini' : 'Akumulasi bulan berjalan'])
    </div>
    <div class="row dashboard-panel-row dashboard-section"><div class="col-12"><div class="card h-100"><div class="card-header"><h4>Laporan Menunggu Review</h4><div class="card-header-action"><a href="{{ route('supervisor.reports.index', ['status' => 'dikirim']) }}">Lihat semua</a></div></div><div class="card-body">@forelse($pendingReports as $report)<a class="d-block border-bottom py-2" href="{{ route('supervisor.reports.show', $report) }}">Laporan {{ format_tanggal_id($report->report_date) }} <span class="badge badge-warning float-right">Dikirim</span></a>@empty<div class="dashboard-empty-state"><div class="dashboard-empty-state__icon"><i data-feather="clipboard"></i></div><p class="lead">Tidak ada laporan menunggu review.</p></div>@endforelse</div></div></div></div>
    <div class="dashboard-section">@include('dashboard.partials.transactions', ['transactions' => $transactions])</div>
</div>
@endsection
