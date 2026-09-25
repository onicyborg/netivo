@extends('layouts.app')

@section('title', 'Dashboard Customer')
@section('page-title', 'Dashboard Customer')
@section('breadcrumb', 'Dashboard')

@push('styles')
    @include('dashboard.partials.styles')
    <style>
        .dashboard-content--customer .dashboard-panel-row { align-items: flex-start; }
        .dashboard-content--customer .dashboard-panel-row > [class*="col-"] { display: flex; align-items: flex-start; }
        .dashboard-content--customer .dashboard-customer-card { width: 100%; height: auto !important; }
        .dashboard-content--customer .dashboard-empty-state { padding: 1.25rem .75rem; }
        .dashboard-content--customer .dashboard-empty-state__icon { margin-bottom: .6rem; }
    </style>
@endpush

@section('content')
<div class="dashboard-content dashboard-content--customer">
    @if($overdueBills->isNotEmpty())
        <div class="dashboard-section">
            <div class="alert alert-danger"><i data-feather="alert-triangle" aria-hidden="true"></i> Anda memiliki {{ $overdueBills->count() }} tagihan terlambat. Silakan lakukan pembayaran atau hubungi admin.</div>
        </div>
    @endif

    <div class="row dashboard-section">
        @include('components.card-statistic', ['label' => 'Tagihan Berjalan', 'value' => $currentBill ? format_rupiah($currentBill->amount) : 0, 'icon' => 'file-text', 'variant' => 'neutral', 'description' => $currentBill ? 'Tagihan periode berjalan' : 'Tidak ada tagihan periode ini'])
        @include('components.card-statistic', ['label' => 'Tagihan Terlambat', 'value' => $overdueBills->count(), 'icon' => 'alert-triangle', 'variant' => 'danger', 'description' => $overdueBills->count() === 0 ? 'Tidak ada tagihan terlambat' : 'Perlu segera dibayar'])
        @include('components.card-statistic', ['label' => 'Riwayat Pembayaran', 'value' => $payments->count(), 'icon' => 'check-circle', 'variant' => 'positive', 'description' => $payments->count() === 0 ? 'Belum ada pembayaran tercatat' : 'Pembayaran terakhir'])
    </div>

    <div class="row dashboard-panel-row dashboard-section">
        <div class="col-12 col-lg-7">
            <div class="card dashboard-customer-card">
                <div class="card-header"><h4>Tagihan Periode Berjalan</h4><div class="card-header-action"><a href="{{ route('customer.bills.index') }}">Lihat semua</a></div></div>
                <div class="card-body">
                    @if($currentBill)
                        <dl class="row">
                            <dt class="col-5">Nomor</dt><dd class="col-7">{{ $currentBill->bill_number }}</dd>
                            <dt class="col-5">Layanan</dt><dd class="col-7">{{ $currentBill->service->name }}</dd>
                            <dt class="col-5">Jumlah</dt><dd class="col-7 font-weight-bold">{{ format_rupiah($currentBill->amount) }}</dd>
                            <dt class="col-5">Jatuh tempo</dt><dd class="col-7">{{ format_tanggal_id($currentBill->due_date) }}</dd>
                            <dt class="col-5">Status</dt><dd class="col-7">{{ ucfirst(str_replace('_', ' ', $currentBill->status->value)) }}</dd>
                        </dl>
                        <a href="{{ route('customer.bills.show', $currentBill) }}" class="btn btn-primary">Detail Tagihan</a>
                        @if(in_array($currentBill->status->value, ['belum_bayar', 'terlambat'], true))
                            <a href="{{ route('customer.bills.pay', $currentBill) }}" class="btn btn-success">Bayar Sekarang</a>
                        @endif
                    @else
                        <div class="dashboard-empty-state"><div class="dashboard-empty-state__icon"><i data-feather="file-text"></i></div><p class="lead">Belum ada tagihan periode berjalan.</p></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card dashboard-customer-card">
                <div class="card-header"><h4>Layanan Aktif</h4></div>
                <div class="card-body">
                    @if($activeService)
                        <h5>{{ $activeService->name }}</h5>
                        <p>{{ $activeService->speed_mbps }} Mbps · {{ format_rupiah($activeService->price) }} / bulan</p>
                        @if($upgrade && $upgrade->status->value === 'approved')
                            <div class="alert alert-info mb-0">Dijadwalkan ke <strong>{{ $upgrade->toService->name }}</strong> mulai {{ $upgrade->effective_period }}.</div>
                        @elseif($upgrade && $upgrade->status->value === 'pending')
                            <div class="alert alert-warning mb-0">Pengajuan upgrade sedang menunggu keputusan.</div>
                        @else
                            <a href="{{ route('customer.services.index') }}">Kelola layanan</a>
                        @endif
                    @else
                        <div class="dashboard-empty-state"><div class="dashboard-empty-state__icon"><i data-feather="wifi"></i></div><p class="lead">Data layanan belum tersedia.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row dashboard-panel-row dashboard-section">
        <div class="col-12 col-lg-6">
            <div class="card dashboard-customer-card">
                <div class="card-header"><h4>5 Pembayaran Terakhir</h4><div class="card-header-action"><a href="{{ route('customer.payments.index') }}">Lihat semua</a></div></div>
                <div class="card-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Tanggal</th><th>Tagihan</th><th>Jumlah</th><th>Status</th></tr></thead><tbody>
                    @forelse($payments as $payment)
                        <tr><td>{{ format_tanggal_id($payment->paid_date) }}</td><td>{{ $payment->bill->bill_number }}</td><td>{{ format_rupiah($payment->amount) }}</td><td>{{ ucfirst($payment->status->value) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada pembayaran.</td></tr>
                    @endforelse
                </tbody></table></div></div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card dashboard-customer-card">
                <div class="card-header"><h4>Notifikasi Terbaru</h4><div class="card-header-action"><a href="{{ route('notifications.index') }}">Lihat semua</a></div></div>
                <div class="card-body">
                    @forelse($notifications as $notification)
                        <a href="{{ route('notifications.read', $notification) }}" class="d-block border-bottom py-2"><strong>{{ $notification->title }}</strong><br><small>{{ $notification->message }}</small></a>
                    @empty
                        <div class="dashboard-empty-state"><div class="dashboard-empty-state__icon"><i data-feather="bell"></i></div><p class="lead">Belum ada notifikasi.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
