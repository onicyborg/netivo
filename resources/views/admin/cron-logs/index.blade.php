@extends('layouts.app')

@section('title', 'Log Cron')
@section('page-title', 'Log Cron')
@section('breadcrumb', 'Log Cron')

@section('content')
<div class="card"><div class="card-header"><h4>Riwayat Eksekusi Cron</h4></div><div class="card-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Job</th><th>Status</th><th>Ringkasan</th><th>Mulai</th><th>Selesai</th></tr></thead><tbody>@forelse($cronLogs as $log)<tr><td>{{ $log->job }}</td><td><span class="badge badge-{{ $log->status === 'success' ? 'success' : ($log->status === 'partial' ? 'warning' : 'danger') }}">{{ ucfirst($log->status) }}</span></td><td>{{ json_encode($log->summary, JSON_UNESCAPED_UNICODE) }}</td><td>{{ format_tanggal_id($log->started_at) }}</td><td>{{ $log->finished_at ? format_tanggal_id($log->finished_at) : '-' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">Belum ada log cron.</td></tr>@endforelse</tbody></table></div>{{ $cronLogs->links() }}</div></div>
@endsection
