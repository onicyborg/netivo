@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')
@section('page-title', 'Verifikasi Pembayaran')
@section('breadcrumb', 'Verifikasi Pembayaran')

@section('content')
<div class="card">
    <div class="card-header"><h4>Daftar Pembayaran</h4></div>
    <div class="card-body">
        <form method="GET" class="form-inline mb-3">
            <label for="status" class="mr-2">Status</label>
            <select id="status" name="status" class="form-control mr-2">
                <option value="pending" @selected($status === 'pending')>Menunggu verifikasi</option>
                <option value="confirmed" @selected($status === 'confirmed')>Dikonfirmasi</option>
                <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
                <option value="all" @selected($status === 'all')>Semua</option>
            </select>
            <button class="btn btn-primary" type="submit">Terapkan</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped" id="admin-payments-table">
                <thead><tr><th>Tanggal</th><th>Customer</th><th>Tagihan</th><th>Jumlah</th><th>Metode</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($payments as $payment)
                    @php($statusClass = ['pending' => 'warning', 'confirmed' => 'success', 'rejected' => 'danger'][$payment->status->value] ?? 'secondary')
                    <tr>
                        <td>{{ format_tanggal_id($payment->paid_date) }}</td>
                        <td>{{ $payment->bill->customer->user->name }}<br><small>{{ $payment->bill->customer->customer_number }}</small></td>
                        <td>{{ $payment->bill->bill_number }}</td>
                        <td>{{ format_rupiah($payment->amount) }}</td>
                        <td>{{ $payment->paymentMethod->name }}</td>
                        <td><span class="badge badge-{{ $statusClass }}">{{ ucfirst($payment->status->value) }}</span></td>
                        <td><a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada pembayaran pada filter ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $payments->links() }}
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('bundles/datatables/datatables.min.css') }}">
@endpush
@push('plugin-scripts')
<script src="{{ asset('bundles/datatables/datatables.min.js') }}"></script>
@endpush
@push('scripts')
<script>$(function(){ $('#admin-payments-table').DataTable({pageLength:10,ordering:true,responsive:true,searching:true,language:{emptyTable:'Belum ada pembayaran.',search:'Cari:',lengthMenu:'Tampilkan _MENU_',info:'Menampilkan _START_ sampai _END_ dari _TOTAL_ pembayaran',paginate:{next:'Berikutnya',previous:'Sebelumnya'}}}); });</script>
@endpush
