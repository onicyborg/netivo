@extends('layouts.app')

@section('title', 'Upgrade / Downgrade Layanan')
@section('page-title', 'Upgrade / Downgrade Layanan')
@section('breadcrumb', 'Upgrade / Downgrade Layanan')

@section('content')
<div class="card upgrade-list-card">
    <div class="card-header"><h4>Pengajuan Upgrade / Downgrade</h4></div>
    <div class="card-body">
        <form method="GET" class="upgrade-filter-bar" aria-label="Filter pengajuan perubahan layanan">
            <div class="upgrade-filter-bar__field">
                <label for="status" class="mb-1">Status pengajuan</label>
                <select id="status" name="status" class="form-control" aria-describedby="status-help" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="applied" @selected(request('status') === 'applied')>Diterapkan</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                </select>
            </div>
            <div class="upgrade-filter-bar__field">
                <label for="from_service_id" class="mb-1">Dari package</label>
                <select id="from_service_id" name="from_service_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua package asal</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(request('from_service_id') === $service->id)>{{ $service->name }} ({{ $service->speed_mbps }} Mbps)</option>
                    @endforeach
                </select>
            </div>
            <div class="upgrade-filter-bar__field">
                <label for="to_service_id" class="mb-1">Ke package</label>
                <select id="to_service_id" name="to_service_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua package tujuan</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(request('to_service_id') === $service->id)>{{ $service->name }} ({{ $service->speed_mbps }} Mbps)</option>
                    @endforeach
                </select>
            </div>
            <small id="status-help" class="upgrade-filter-bar__help">Daftar diperbarui otomatis saat filter diubah.</small>
        </form>

        <div class="table-responsive">
            <table id="upgrades-table" class="table table-striped">
                <thead><tr><th>Customer</th><th>Dari</th><th>Ke</th><th>Status</th><th>Efektif</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($upgrades as $upgrade)
                    <tr><td>{{ $upgrade->customer->user->name }}<br><small>{{ $upgrade->customer->customer_number }}</small></td><td>{{ $upgrade->fromService->name }}</td><td>{{ $upgrade->toService->name }}</td><td>{{ ucfirst($upgrade->status->value) }}</td><td>{{ $upgrade->effective_period ?: '-' }}</td><td><a href="{{ route('admin.upgrades.show', $upgrade) }}" class="btn btn-sm btn-primary">Detail</a></td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada pengajuan perubahan layanan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('bundles/datatables/datatables.min.css') }}">
<style>
    .upgrade-list-card .card-header { min-height: 70px; display: flex; align-items: center; }
    .upgrade-list-card .card-header h4 { margin: 0; }
    .upgrade-filter-bar { display: flex; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid #e8edf5; border-radius: 8px; background: #f8f9fc; }
    .upgrade-filter-bar__field { flex: 1 1 220px; min-width: 190px; }
    .upgrade-filter-bar label { display: block; color: #5f6b7a; font-size: 12px; font-weight: 600; }
    .upgrade-filter-bar .form-control { height: 40px; background: #fff; }
    .upgrade-filter-bar__help { flex: 1 0 100%; margin-top: -.35rem; color: #98a2b3; font-size: 11px; }
    @media (max-width: 575.98px) { .upgrade-filter-bar { gap: .75rem; } .upgrade-filter-bar__field { flex-basis: 100%; } }
</style>
@endpush
@push('plugin-scripts')<script src="{{ asset('bundles/datatables/datatables.min.js') }}"></script>@endpush
@push('scripts')
<script>
    $(function () {
        $('#upgrades-table').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                emptyTable: 'Belum ada pengajuan perubahan layanan.',
                paginate: { next: 'Berikutnya', previous: 'Sebelumnya' }
            }
        });
    });
</script>
@endpush
