@extends('layouts.app')

@section('title', 'Review Laporan Harian')
@section('page-title', 'Review Laporan Harian')
@section('breadcrumb', 'Review Laporan')

@section('content')
@php($statusClass = ['dikirim' => 'warning', 'revisi' => 'danger', 'diarsipkan' => 'success'][$report->status->value] ?? 'secondary')
<div class="card"><div class="card-header"><h4>Laporan {{ format_tanggal_id($report->report_date) }}</h4><div class="card-header-action"><span class="badge badge-{{ $statusClass }}">{{ ucfirst($report->status->value) }}</span></div></div><div class="card-body"><div class="row"><div class="col-md-4"><h6>Confirmed</h6><p>{{ $report->total_confirmed_count }} transaksi / {{ format_rupiah($report->total_confirmed_amount) }}</p></div><div class="col-md-4"><h6>Ditolak</h6><p>{{ $report->rejected_count }}</p></div><div class="col-md-4"><h6>Pending</h6><p>{{ $report->pending_count }}</p></div></div>@if($report->revision_note)<div class="alert alert-warning" data-sweetalert-ignore><strong>Catatan revisi:</strong> {{ $report->revision_note }}</div>@endif</div></div>
<div class="card"><div class="card-header"><h4>Daftar Transaksi</h4></div><div class="card-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Tanggal</th><th>Customer</th><th>Tagihan</th><th>Jumlah</th><th>Status</th></tr></thead><tbody>@forelse($payments as $payment)<tr><td>{{ format_tanggal_id($payment->verified_at ?: $payment->paid_date) }}</td><td>{{ $payment->bill->customer->user->name }}</td><td>{{ $payment->bill->bill_number }}</td><td>{{ format_rupiah($payment->amount) }}</td><td>{{ ucfirst($payment->status->value) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">Tidak ada transaksi.</td></tr>@endforelse</tbody></table></div></div></div>
@if($report->status->value === 'dikirim')<div class="card"><div class="card-header"><h4>Aksi Review</h4></div><div class="card-body"><form id="archive-report-form" method="POST" action="{{ route('supervisor.reports.archive', $report) }}" class="d-inline">@csrf<button id="archive-report-button" type="button" class="btn btn-success"><i class="fas fa-archive"></i> Setujui &amp; Arsipkan</button></form><button class="btn btn-warning ml-2" data-toggle="modal" data-target="#revision-modal"><i class="fas fa-edit"></i> Minta Revisi</button></div></div><div class="modal fade" id="revision-modal" tabindex="-1" role="dialog" aria-labelledby="revision-title" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><form method="POST" action="{{ route('supervisor.reports.revision', $report) }}">@csrf<div class="modal-header"><h5 id="revision-title" class="modal-title">Minta Revisi</h5><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><label for="revision_note">Catatan revisi <span class="text-danger">*</span></label><textarea id="revision_note" name="revision_note" class="form-control" rows="4" required></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-warning">Kirim Permintaan Revisi</button></div></form></div></div></div></div>@endif
@endsection

@push('scripts')
<script>
    $(function () {
        $('#archive-report-button').on('click', function () {
            Swal.fire({
                icon: 'question',
                title: 'Setujui dan arsipkan laporan?',
                text: 'Laporan ini akan ditandai sudah ditinjau dan diarsipkan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, arsipkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    $('#archive-report-form').trigger('submit');
                }
            });
        });
    });
</script>
@endpush
