@extends('layouts.app')

@section('title', 'Upgrade Layanan')
@section('page-title', 'Upgrade Layanan')
@section('breadcrumb', 'Upgrade Layanan')

@section('content')
<div class="card"><div class="card-header"><h4>Pengajuan Upgrade</h4></div><div class="card-body"><form method="GET" class="form-inline mb-3"><label for="status" class="mr-2">Status</label><select id="status" name="status" class="form-control mr-2"><option value="">Semua</option><option value="pending" @selected(request('status') === 'pending')>Pending</option><option value="approved" @selected(request('status') === 'approved')>Disetujui</option><option value="applied" @selected(request('status') === 'applied')>Diterapkan</option><option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option></select><button class="btn btn-light">Filter</button></form><div class="table-responsive"><table id="upgrades-table" class="table table-striped"><thead><tr><th>Customer</th><th>Dari</th><th>Ke</th><th>Status</th><th>Efektif</th><th>Aksi</th></tr></thead><tbody>@forelse($upgrades as $upgrade)<tr><td>{{ $upgrade->customer->user->name }}<br><small>{{ $upgrade->customer->customer_number }}</small></td><td>{{ $upgrade->fromService->name }}</td><td>{{ $upgrade->toService->name }}</td><td>{{ ucfirst($upgrade->status->value) }}</td><td>{{ $upgrade->effective_period ?: '-' }}</td><td><a href="{{ route('admin.upgrades.show', $upgrade) }}" class="btn btn-sm btn-primary">Detail</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted">Belum ada pengajuan upgrade.</td></tr>@endforelse</tbody></table></div>{{ $upgrades->links() }}</div></div>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('bundles/datatables/datatables.min.css') }}">@endpush
@push('plugin-scripts')<script src="{{ asset('bundles/datatables/datatables.min.js') }}"></script>@endpush
@push('scripts')<script>$(function(){ $('#upgrades-table').DataTable({pageLength:10,ordering:true,responsive:true,language:{search:'Cari:',emptyTable:'Belum ada pengajuan.',paginate:{next:'Berikutnya',previous:'Sebelumnya'}}}); });</script>@endpush
