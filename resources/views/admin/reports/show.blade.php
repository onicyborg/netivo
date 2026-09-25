@extends('layouts.app')

@section('title', 'Detail Laporan Harian')
@section('page-title', 'Detail Laporan Harian')
@section('breadcrumb', 'Detail Laporan')

@section('content')
@php($statusClass = ['dikirim' => 'warning', 'revisi' => 'danger', 'diarsipkan' => 'success'][$report->status->value] ?? 'secondary')

<div class="report-detail-page">
    <section class="card report-summary-card" aria-labelledby="report-summary-title">
        <div class="card-header report-summary-card__header">
            <div>
                <span class="report-summary-card__eyebrow">Ringkasan laporan</span>
                <h4 id="report-summary-title">Laporan {{ format_tanggal_id($report->report_date) }}</h4>
            </div>
            <span class="badge badge-{{ $statusClass }} report-status">{{ ucfirst($report->status->value) }}</span>
        </div>
        <div class="card-body">
            <div class="report-summary-grid">
                <article class="report-metric report-metric--success">
                    <div class="report-metric__icon" aria-hidden="true"><i class="fas fa-check"></i></div>
                    <div class="report-metric__content"><span class="report-metric__label">Confirmed</span><strong class="report-metric__value">{{ $report->total_confirmed_count }}</strong><span class="report-metric__note">{{ format_rupiah($report->total_confirmed_amount) }}</span></div>
                </article>
                <article class="report-metric report-metric--danger">
                    <div class="report-metric__icon" aria-hidden="true"><i class="fas fa-times"></i></div>
                    <div class="report-metric__content"><span class="report-metric__label">Ditolak</span><strong class="report-metric__value">{{ $report->rejected_count }}</strong><span class="report-metric__note">Transaksi ditolak</span></div>
                </article>
                <article class="report-metric report-metric--warning">
                    <div class="report-metric__icon" aria-hidden="true"><i class="fas fa-clock"></i></div>
                    <div class="report-metric__content"><span class="report-metric__label">Pending</span><strong class="report-metric__value">{{ $report->pending_count }}</strong><span class="report-metric__note">Menunggu proses</span></div>
                </article>
                <dl class="report-meta mb-0">
                    <div class="report-meta__item"><dt>Sumber</dt><dd>{{ ucfirst($report->source) }}</dd></div>
                    <div class="report-meta__item"><dt>Dikirim</dt><dd>{{ format_tanggal_id($report->sent_at) }}</dd></div>
                </dl>
            </div>
            @if($report->revision_note)
                <div class="alert alert-warning report-revision-note mb-0"><i class="fas fa-info-circle mr-2" aria-hidden="true"></i><span><strong>Catatan revisi:</strong> {{ $report->revision_note }}@if($report->reviewer) <small class="d-block mt-1">Oleh {{ $report->reviewer->name }}</small>@endif</span></div>
            @endif
        </div>
        @if($report->status->value === 'revisi')
            <div class="card-footer report-summary-card__footer"><form method="POST" action="{{ route('admin.reports.resend', $report) }}" class="mb-0">@csrf<button class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Perbaiki &amp; Kirim Ulang</button></form></div>
        @endif
    </section>

    <section class="card report-transactions-card" aria-labelledby="report-transactions-title">
        <div class="card-header"><div><span class="report-summary-card__eyebrow">Aktivitas laporan</span><h4 id="report-transactions-title">Daftar Transaksi</h4></div><span class="report-count">{{ $payments->count() }} transaksi</span></div>
        <div class="card-body"><div class="table-responsive"><table class="table table-striped mb-0"><thead><tr><th>Tanggal</th><th>Customer</th><th>Tagihan</th><th>Jumlah</th><th>Status</th></tr></thead><tbody>
        @forelse($payments as $payment)
            <tr><td>{{ format_tanggal_id($payment->verified_at ?: $payment->paid_date) }}</td><td>{{ $payment->bill->customer->user->name }}</td><td>{{ $payment->bill->bill_number }}</td><td>{{ format_rupiah($payment->amount) }}</td><td><span class="badge badge-{{ ['pending' => 'warning', 'confirmed' => 'success', 'rejected' => 'danger'][$payment->status->value] ?? 'secondary' }}">{{ ucfirst($payment->status->value) }}</span></td></tr>
        @empty
            <tr><td colspan="5"><div class="report-empty-state"><span class="report-empty-state__icon" aria-hidden="true"><i class="fas fa-receipt"></i></span><strong>Tidak ada transaksi pada tanggal laporan.</strong><span>Transaksi yang terkait laporan ini akan tampil di sini.</span></div></td></tr>
        @endforelse
        </tbody></table></div></div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .report-detail-page .card { border: 1px solid #e8edf5; border-radius: 10px; box-shadow: 0 5px 18px rgba(52, 57, 94, .07); }
    .report-detail-page .report-summary-card, .report-detail-page .report-transactions-card { overflow: hidden; }
    .report-detail-page .card-header { min-height: 70px; padding: 1rem 1.35rem; }
    .report-detail-page .card-header h4 { margin: .15rem 0 0; color: #34395e; font-size: 16px; font-weight: 700; }
    .report-summary-card__header, .report-transactions-card .card-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    .report-summary-card__eyebrow { display: block; color: #98a2b3; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .report-status { padding: .45rem .75rem; border-radius: 999px; font-size: 11px; }
    .report-detail-page .card-body { padding: 1.25rem; }
    .report-summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)) minmax(170px, .72fr); gap: 1rem; align-items: stretch; }
    .report-metric { display: flex; align-items: center; min-width: 0; min-height: 100px; padding: 1rem; border: 1px solid #e8edf5; border-radius: 9px; background: #fff; }
    .report-metric__icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 48px; width: 48px; height: 48px; border-radius: 50%; font-size: 19px; }
    .report-metric__content { min-width: 0; margin-left: .85rem; }
    .report-metric__label { display: block; color: #7a869a; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
    .report-metric__value { display: block; margin-top: .2rem; color: #34395e; font-size: 24px; line-height: 1.15; }
    .report-metric__note { display: block; margin-top: .25rem; color: #98a2b3; font-size: 11px; line-height: 1.35; }
    .report-metric--success .report-metric__icon { background: #e7f7ed; color: #218653; }
    .report-metric--danger .report-metric__icon { background: #ffebeb; color: #c43f4d; }
    .report-metric--warning .report-metric__icon { background: #fff2dc; color: #a86300; }
    .report-meta { display: flex; flex-direction: column; justify-content: center; gap: .85rem; min-height: 100px; padding: 1rem 1.1rem; border: 1px solid #e8edf5; border-radius: 9px; background: #f8f9fc; }
    .report-meta__item dt { margin: 0 0 .15rem; color: #7a869a; font-size: 11px; font-weight: 600; }
    .report-meta__item dd { margin: 0; color: #34395e; font-size: 13px; font-weight: 700; }
    .report-revision-note { display: flex; align-items: flex-start; margin-top: 1rem; padding: .8rem 1rem; font-size: 13px; }
    .report-summary-card__footer { display: flex; justify-content: flex-end; padding: .9rem 1.25rem; background: #fbfcfe; }
    .report-transactions-card { margin-top: 1.5rem; }
    .report-count { color: #7a869a; font-size: 12px; }
    .report-transactions-card table th { color: #7a869a; font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
    .report-transactions-card table td { color: #4b5563; font-size: 13px; vertical-align: middle; }
    .report-empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 145px; padding: 1.25rem; color: #7a869a; text-align: center; }
    .report-empty-state__icon { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; margin-bottom: .65rem; border-radius: 50%; background: #f0f2f5; color: #7b8794; }
    .report-empty-state strong { color: #5f6b7a; font-size: 13px; font-weight: 600; }
    .report-empty-state > span:last-child { margin-top: .2rem; font-size: 11px; }
    @media (max-width: 991.98px) { .report-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .report-meta { grid-column: 1 / -1; flex-direction: row; gap: 3rem; min-height: auto; } }
    @media (max-width: 575.98px) { .report-detail-page .card-header, .report-detail-page .card-body { padding: 1rem; } .report-summary-card__header, .report-transactions-card .card-header { align-items: flex-start; flex-direction: column; } .report-summary-grid { grid-template-columns: 1fr; } .report-meta { grid-column: auto; flex-direction: column; gap: .75rem; } .report-summary-card__footer { justify-content: stretch; } .report-summary-card__footer .btn { width: 100%; } }
</style>
@endpush
