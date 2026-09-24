@extends('layouts.app')

@section('title', 'Detail Verifikasi Pembayaran')
@section('page-title', 'Detail Verifikasi Pembayaran')
@section('breadcrumb', 'Detail Pembayaran')

@section('content')
@php($statusClass = ['pending' => 'warning', 'confirmed' => 'success', 'rejected' => 'danger'][$payment->status->value] ?? 'secondary')
<div class="row">
    <div class="col-12 col-lg-6">
        <div class="card"><div class="card-header"><h4>Data Pembayaran</h4></div><div class="card-body">
            @error('payment')<div class="alert alert-danger">{{ $message }}</div>@enderror
            <dl class="row mb-0">
                <dt class="col-5">Status</dt><dd class="col-7"><span class="badge badge-{{ $statusClass }}">{{ ucfirst($payment->status->value) }}</span></dd>
                <dt class="col-5">Tanggal bayar</dt><dd class="col-7">{{ format_tanggal_id($payment->paid_date) }}</dd>
                <dt class="col-5">Metode</dt><dd class="col-7">{{ $payment->paymentMethod->name }}</dd>
                <dt class="col-5">Jumlah</dt><dd class="col-7 font-weight-bold">{{ format_rupiah($payment->amount) }}</dd>
                <dt class="col-5">Nama pengirim</dt><dd class="col-7">{{ $payment->sender_name ?: '-' }}</dd>
                <dt class="col-5">Catatan</dt><dd class="col-7">{{ $payment->note ?: '-' }}</dd>
                @if($payment->rejection_reason)<dt class="col-5">Alasan penolakan</dt><dd class="col-7">{{ $payment->rejection_reason }}</dd>@endif
            </dl>
        </div></div>
        <div class="card"><div class="card-header"><h4>Pratinjau Bukti</h4><div class="card-header-action"><a class="btn btn-sm btn-light" target="_blank" rel="noopener" href="{{ route('admin.payments.proof', $payment) }}">Buka bukti</a></div></div><div class="card-body text-center">
            @if($payment->proof_path && in_array(strtolower(pathinfo($payment->proof_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']))
                <img src="{{ route('admin.payments.proof', $payment) }}" alt="Bukti pembayaran" class="img-fluid rounded" style="max-height:420px">
            @else
                <div class="empty-state"><div class="empty-state-icon"><i class="fas fa-file-pdf"></i></div><h2>Dokumen bukti</h2><p class="lead">Bukti berupa PDF atau dokumen lain. Gunakan tombol Buka bukti.</p></div>
            @endif
        </div></div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card"><div class="card-header"><h4>Customer</h4></div><div class="card-body"><dl class="row mb-0"><dt class="col-5">Nama</dt><dd class="col-7">{{ $payment->bill->customer->user->name }}</dd><dt class="col-5">Nomor customer</dt><dd class="col-7">{{ $payment->bill->customer->customer_number }}</dd><dt class="col-5">Email</dt><dd class="col-7">{{ $payment->bill->customer->user->email }}</dd><dt class="col-5">HP</dt><dd class="col-7">{{ $payment->bill->customer->phone }}</dd><dt class="col-5">Alamat</dt><dd class="col-7">{{ $payment->bill->customer->address }}</dd></dl></div></div>
        <div class="card"><div class="card-header"><h4>Tagihan</h4></div><div class="card-body"><dl class="row mb-0"><dt class="col-5">Nomor</dt><dd class="col-7">{{ $payment->bill->bill_number }}</dd><dt class="col-5">Periode</dt><dd class="col-7">{{ $payment->bill->period }}</dd><dt class="col-5">Layanan</dt><dd class="col-7">{{ $payment->bill->service->name }}</dd><dt class="col-5">Jatuh tempo</dt><dd class="col-7">{{ format_tanggal_id($payment->bill->due_date) }}</dd><dt class="col-5">Jumlah</dt><dd class="col-7">{{ format_rupiah($payment->bill->amount) }}</dd></dl></div></div>
        @if($payment->status->value === 'pending')
            <div class="card"><div class="card-header"><h4>Keputusan Verifikasi</h4></div><div class="card-body"><form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" class="mb-2">@csrf<button type="submit" class="btn btn-success btn-block" onclick="return confirm('Konfirmasi pembayaran ini?')"><i class="fas fa-check"></i> Konfirmasi Pembayaran</button></form><button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#reject-payment-modal"><i class="fas fa-times"></i> Tolak Pembayaran</button></div></div>
        @elseif($payment->receipt)
            <div class="alert alert-success">Kuitansi: <a target="_blank" rel="noopener" href="{{ route('admin.receipts.show', $payment->receipt) }}">{{ $payment->receipt->receipt_number }}</a></div>
        @endif
    </div>
</div>
@if($payment->status->value === 'pending')
<div class="modal fade" id="reject-payment-modal" tabindex="-1" role="dialog" aria-labelledby="reject-payment-title" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><form method="POST" action="{{ route('admin.payments.reject', $payment) }}">@csrf<div class="modal-header"><h5 class="modal-title" id="reject-payment-title">Tolak Pembayaran</h5><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div class="form-group"><label for="rejection_reason">Alasan penolakan <span class="text-danger">*</span></label><textarea id="rejection_reason" name="rejection_reason" rows="4" required class="form-control @error('rejection_reason') is-invalid @enderror">{{ old('rejection_reason') }}</textarea>@error('rejection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Tolak Pembayaran</button></div></form></div></div></div>
@endif
@endsection
