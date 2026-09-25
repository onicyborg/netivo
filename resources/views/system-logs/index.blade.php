@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('breadcrumb', 'Audit Log')

@section('content')
<div class="card">
    <div class="card-header"><h4>System Logs</h4></div>
    <div class="card-body">
        <form method="GET" class="form-inline mb-3">
            <label for="date_from" class="mr-2">Dari</label>
            <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="form-control mr-2">
            <label for="date_to" class="mr-2">Sampai</label>
            <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="form-control mr-2">
            <label for="user_id" class="mr-2">User</label>
            <select id="user_id" name="user_id" class="form-control mr-2">
                <option value="">Semua user</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') === $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <label for="action" class="mr-2 ml-2">Aksi</label>
            <input id="action" name="action" value="{{ request('action') }}" class="form-control mr-2" placeholder="contoh: updated">
            <button class="btn btn-primary">Filter</button>
        </form>

        <div class="table-responsive">
            <table id="system-logs-table" class="table table-striped">
                <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Tabel/Record</th><th>Method</th><th>URL</th><th>Snapshot</th></tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ format_tanggal_id($log->created_at) }}</td>
                        <td>{{ $log->user?->name ?: 'Sistem' }}</td>
                        <td><span class="badge badge-secondary">{{ $log->action }}</span></td>
                        <td>{{ $log->table_name }}<br><small>{{ $log->record_id ?: '-' }}</small></td>
                        <td>{{ $log->method }}</td>
                        <td class="text-break">{{ $log->url }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-light js-view-audit-snapshot" data-toggle="modal" data-target="#audit-snapshot-modal" data-log-id="{{ $log->id }}" data-old="{{ e(json_encode($log->old_values, JSON_UNESCAPED_UNICODE)) }}" data-new="{{ e(json_encode($log->new_values, JSON_UNESCAPED_UNICODE)) }}" title="Lihat snapshot">Lihat snapshot</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada audit log.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</div>

<div class="modal fade" id="audit-snapshot-modal" tabindex="-1" role="dialog" aria-labelledby="audit-snapshot-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="audit-snapshot-title" class="modal-title">Snapshot Audit Log</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Nilai Lama</h6>
                        <pre id="audit-snapshot-old" class="small bg-light p-3 rounded mb-0" style="max-height: 360px; overflow: auto; white-space: pre-wrap;"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6>Nilai Baru</h6>
                        <pre id="audit-snapshot-new" class="small bg-light p-3 rounded mb-0" style="max-height: 360px; overflow: auto; white-space: pre-wrap;"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button></div>
        </div>
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
<script>
    $(function () {
        $('#system-logs-table').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: true,
            language: { search: 'Cari:', emptyTable: 'Belum ada audit log.', paginate: { next: 'Berikutnya', previous: 'Sebelumnya' } }
        });

        $(document).on('click', '.js-view-audit-snapshot', function () {
            var button = $(this);
            var formatSnapshot = function (value) {
                try {
                    return JSON.stringify(JSON.parse(value || '{}'), null, 2);
                } catch (error) {
                    return value || '{}';
                }
            };

            $('#audit-snapshot-title').text('Snapshot Audit Log #' + button.data('log-id'));
            $('#audit-snapshot-old').text(formatSnapshot(button.attr('data-old')));
            $('#audit-snapshot-new').text(formatSnapshot(button.attr('data-new')));
        });
    });
</script>
@endpush
