@extends('layouts.app')

@section('title', 'Detail Tagihan')
@section('page-title', 'Detail Tagihan')
@section('breadcrumb', 'Detail Tagihan')

@section('content')
<div class="row"><div class="col-12 col-lg-7"><div class="card"><div class="card-header"><h4>Informasi Tagihan</h4><div class="card-header-action"><span class="badge badge-{{ ['belum_bayar'=>'warning','menunggu_verifikasi'=>'info','lunas'=>'success','terlambat'=>'danger'][$bill->status->value] ?? 'secondary' }}">{{ ['belum_bayar'=>'Belum Bayar','menunggu_verifikasi'=>'Menunggu Verifikasi','lunas'=>'Lunas','terlambat'=>'Terlambat'][$bill->status->value] ?? $bill->status->value }}</span></div></div><div class="card-body"><dl class="row mb-0"><dt class="col-sm-5">Nomor Tagihan</dt><dd class="col-sm-7">{{ $bill->bill_number }}</dd><dt class="col-sm-5">Periode</dt><dd class="col-sm-7">{{ $bill->period }}</dd><dt class="col-sm-5">Layanan</dt><dd class="col-sm-7">{{ $bill->service->name }}</dd><dt class="col-sm-5">Jumlah</dt><dd class="col-sm-7 font-weight-bold">{{ format_rupiah($bill->amount) }}</dd><dt class="col-sm-5">Jatuh Tempo</dt><dd class="col-sm-7">{{ format_tanggal_id($bill->due_date) }}</dd></dl></div><div class="card-footer">@if(in_array($bill->status->value, ['belum_bayar','terlambat'], true))<a href="{{ route('customer.bills.pay', $bill) }}" class="btn btn-success"><i class="fas fa-credit-card"></i> Bayar Tagihan</a>@elseif($bill->status->value === 'menunggu_verifikasi')<span class="text-info"><i class="fas fa-clock"></i> Pembayaran sedang menunggu verifikasi admin.</span>@else<span class="text-success"><i class="fas fa-check-circle"></i> Tagihan ini sudah lunas.</span>@endif</div></div></div><div class="col-12 col-lg-5"><div class="card"><div class="card-header"><h4>Bantuan</h4></div><div class="card-body"><p class="mb-0">Informasi sudah sesuai? Silakan lanjutkan pembayaran. Bila informasi tidak sesuai, hubungi admin untuk mendapatkan bantuan.</p></div></div></div></div>
<div class="card customer-proof-card"><div class="card-header"><h4>Bukti Pembayaran</h4></div><div class="card-body">@forelse($bill->payments as $payment)<div class="customer-proof-item"><div class="customer-proof-item__meta"><div><strong>{{ format_tanggal_id($payment->paid_date) }}</strong><span class="d-block text-muted">{{ $payment->paymentMethod->name }} · {{ format_rupiah($payment->amount) }}</span></div><span class="badge badge-{{ ['pending'=>'warning','confirmed'=>'success','rejected'=>'danger'][$payment->status->value] ?? 'secondary' }}">{{ ucfirst($payment->status->value) }}</span></div>@if($payment->proof_path && in_array(strtolower(pathinfo($payment->proof_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']))<div class="customer-proof-item__preview"><img src="{{ route('customer.payments.proof', $payment) }}" alt="Bukti pembayaran {{ $bill->bill_number }} pada {{ format_tanggal_id($payment->paid_date) }}"></div>@elseif($payment->proof_path)<a href="{{ route('customer.payments.proof', $payment) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light"><i class="fas fa-file-alt mr-1"></i> Buka bukti pembayaran</a>@else<span class="text-muted small">Bukti pembayaran tidak tersedia.</span>@endif</div>@empty<div class="customer-proof-empty"><i class="fas fa-receipt" aria-hidden="true"></i><span>Belum ada bukti pembayaran untuk tagihan ini.</span></div>@endforelse</div></div>
<a href="{{ route('customer.bills.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali ke Tagihan</a>
@endsection

@push('styles')
<style>
    .customer-proof-card { margin-top: 1.5rem; }
    .customer-proof-item { padding: 1rem; border: 1px solid #e8edf5; border-radius: 8px; background: #fbfcfe; }
    .customer-proof-item + .customer-proof-item { margin-top: 1rem; }
    .customer-proof-item__meta { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: .9rem; color: #34395e; font-size: 13px; }
    .customer-proof-item__meta .text-muted { font-size: 12px; }
    .customer-proof-item__preview { display: flex; align-items: center; justify-content: center; min-height: 180px; padding: .75rem; border: 1px solid #e8edf5; border-radius: 6px; background: #fff; }
    .customer-proof-item__preview img { display: block; width: auto; max-width: 100%; max-height: 360px; border-radius: 4px; object-fit: contain; }
    .customer-proof-empty { display: flex; align-items: center; justify-content: center; gap: .6rem; min-height: 110px; color: #98a2b3; font-size: 13px; text-align: center; }
    .customer-proof-empty i { color: #7b8794; font-size: 18px; }
    @media (max-width: 575.98px) { .customer-proof-item__meta { flex-direction: column; } }
</style>
@endpush
