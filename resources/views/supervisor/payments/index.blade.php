@extends('layouts.app')

@section('title', 'Pembayaran')
@section('page-title', 'Pembayaran')
@section('breadcrumb', 'Pembayaran')

@section('content')
<div class="card"><div class="card-header"><h4>Daftar Pembayaran (Read-only)</h4></div><div class="card-body"><form method="GET" class="form-inline mb-3"><label for="status" class="mr-2">Status</label><select id="status" name="status" class="form-control mr-2"><option value="all" @selected($status === 'all')>Semua</option><option value="pending" @selected($status === 'pending')>Menunggu verifikasi</option><option value="confirmed" @selected($status === 'confirmed')>Dikonfirmasi</option><option value="rejected" @selected($status === 'rejected')>Ditolak</option></select><button class="btn btn-primary">Terapkan</button></form><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Tanggal</th><th>Customer</th><th>Tagihan</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($payments as $payment)<tr><td>{{ format_tanggal_id($payment->paid_date) }}</td><td>{{ $payment->bill->customer->user->name }}</td><td>{{ $payment->bill->bill_number }}</td><td>{{ format_rupiah($payment->amount) }}</td><td>{{ ucfirst($payment->status->value) }}</td><td><a href="{{ route('supervisor.payments.show', $payment) }}" class="btn btn-sm btn-primary">Detail</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted">Belum ada pembayaran.</td></tr>@endforelse</tbody></table></div>{{ $payments->links() }}</div></div>
@endsection
