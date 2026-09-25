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
            <div class="card"><div class="card-header"><h4>Keputusan Verifikasi</h4></div><div class="card-body">
                <form id="confirm-payment-form" method="POST" action="{{ route('admin.payments.confirm', $payment) }}" class="mb-2">@csrf<button id="confirm-payment-button" type="button" class="btn btn-success btn-block"><i class="fas fa-check"></i> Konfirmasi Pembayaran</button></form>
                <button id="reject-payment-button" type="button" class="btn btn-danger btn-block"><i class="fas fa-times"></i> Tolak Pembayaran</button>
                <form id="reject-payment-form" method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="d-none">@csrf<input id="rejection-reason-value" type="hidden" name="rejection_reason"></form>
            </div></div>
        @elseif($payment->receipt)
            <div class="alert alert-success">Kuitansi: <a target="_blank" rel="noopener" href="{{ route('admin.receipts.show', $payment->receipt) }}">{{ $payment->receipt->receipt_number }}</a></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#confirm-payment-button').on('click', function () {
            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi pembayaran?',
                text: 'Pembayaran ini akan ditandai sebagai dikonfirmasi dan kuitansi akan dibuat.',
                showCancelButton: true,
                confirmButtonText: 'Ya, konfirmasi',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    $('#confirm-payment-form').trigger('submit');
                }
            });
        });

        $('#reject-payment-button').on('click', function () {
            Swal.fire({
                icon: 'warning',
                title: 'Tolak pembayaran?',
                input: 'textarea',
                inputLabel: 'Alasan penolakan',
                inputPlaceholder: 'Tuliskan alasan penolakan...',
                inputAttributes: { 'aria-label': 'Alasan penolakan' },
                inputValidator: function (value) {
                    return !value || !value.trim() ? 'Alasan penolakan wajib diisi.' : undefined;
                },
                showCancelButton: true,
                confirmButtonText: 'Ya, tolak pembayaran',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    $('#rejection-reason-value').val(result.value.trim());
                    $('#reject-payment-form').trigger('submit');
                }
            });
        });
    });
</script>
@endpush
