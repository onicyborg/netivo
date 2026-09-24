@extends('layouts.app')

@section('title', 'Tagihan Saya')
@section('page-title', 'Tagihan Saya')
@section('breadcrumb', 'Tagihan Saya')

@section('content')
<div class="card"><div class="card-header"><h4>Daftar Tagihan</h4></div><div class="card-body"><div class="alert alert-info"><i class="fas fa-info-circle"></i> Periksa informasi tagihan sebelum membayar. Hubungi admin bila informasi tidak sesuai.</div><div class="table-responsive"><table id="customer-bills-table" class="table table-striped"><thead><tr><th>Nomor</th><th>Periode</th><th>Layanan</th><th>Jumlah</th><th>Jatuh Tempo</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($bills as $bill)<tr><td>{{ $bill->bill_number }}</td><td>{{ $bill->period }}</td><td>{{ $bill->service->name }}</td><td>{{ format_rupiah($bill->amount) }}</td><td>{{ format_tanggal_id($bill->due_date) }}</td><td><span class="badge badge-{{ ['belum_bayar'=>'warning','menunggu_verifikasi'=>'info','lunas'=>'success','terlambat'=>'danger'][$bill->status->value] ?? 'secondary' }}">{{ ['belum_bayar'=>'Belum Bayar','menunggu_verifikasi'=>'Menunggu Verifikasi','lunas'=>'Lunas','terlambat'=>'Terlambat'][$bill->status->value] ?? $bill->status->value }}</span></td><td class="text-nowrap"><a class="btn btn-sm btn-primary" href="{{ route('customer.bills.show', $bill) }}"><i class="fas fa-eye"></i> Detail</a>@if(in_array($bill->status->value, ['belum_bayar','terlambat'], true)) <a class="btn btn-sm btn-success" href="{{ route('customer.bills.pay', $bill) }}"><i class="fas fa-credit-card"></i> Bayar</a>@endif</td></tr>@empty<tr><td colspan="7" class="text-center text-muted"><i class="fas fa-file-invoice fa-2x d-block mb-2"></i>Belum ada tagihan.</td></tr>@endforelse</tbody></table></div></div></div>
@push('styles')<link rel="stylesheet" href="{{ asset('bundles/datatables/datatables.min.css') }}">@endpush
@push('plugin-scripts')<script src="{{ asset('bundles/datatables/datatables.min.js') }}"></script>@endpush
@push('scripts')<script>$(function(){ $('#customer-bills-table').DataTable({pageLength:10,ordering:true,responsive:true,language:{emptyTable:'Belum ada tagihan.',search:'Cari:',lengthMenu:'Tampilkan _MENU_',info:'Menampilkan _START_ sampai _END_ dari _TOTAL_ tagihan',paginate:{next:'Berikutnya',previous:'Sebelumnya'}}}); });</script>@endpush
@endsection
