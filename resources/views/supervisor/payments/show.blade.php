@extends('layouts.app')

@section('title', 'Detail Pembayaran')
@section('page-title', 'Detail Pembayaran')
@section('breadcrumb', 'Detail Pembayaran')

@section('content')
<div class="card"><div class="card-header"><h4>Detail Pembayaran (Read-only)</h4></div><div class="card-body"><dl class="row"><dt class="col-sm-3">Customer</dt><dd class="col-sm-9">{{ $payment->bill->customer->user->name }} ({{ $payment->bill->customer->customer_number }})</dd><dt class="col-sm-3">Tagihan</dt><dd class="col-sm-9">{{ $payment->bill->bill_number }} / {{ $payment->bill->period }}</dd><dt class="col-sm-3">Layanan</dt><dd class="col-sm-9">{{ $payment->bill->service->name }}</dd><dt class="col-sm-3">Jumlah</dt><dd class="col-sm-9">{{ format_rupiah($payment->amount) }}</dd><dt class="col-sm-3">Metode</dt><dd class="col-sm-9">{{ $payment->paymentMethod->name }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($payment->status->value) }}</dd><dt class="col-sm-3">Bukti</dt><dd class="col-sm-9"><a target="_blank" rel="noopener" href="{{ route('supervisor.payments.proof', $payment) }}">Buka bukti pembayaran</a></dd></dl>@if($payment->receipt)<a target="_blank" rel="noopener" href="{{ route('supervisor.receipts.show', $payment->receipt) }}" class="btn btn-success">Lihat e-receipt</a>@endif</div></div>
@endsection
