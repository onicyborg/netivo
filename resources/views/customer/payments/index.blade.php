@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')
@section('page-title', 'Riwayat Pembayaran')
@section('breadcrumb', 'Riwayat Pembayaran')

@section('content')
@php($imageExtensions = ['jpg', 'jpeg', 'png'])
<div class="card"><div class="card-header"><h4>Riwayat Tagihan</h4></div><div class="card-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Nomor</th><th>Periode</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($bills as $bill)<tr><td>{{ $bill->bill_number }}</td><td>{{ $bill->period }}</td><td>{{ format_rupiah($bill->amount) }}</td><td>{{ ucfirst(str_replace('_', ' ', $bill->status->value)) }}</td><td><a class="btn btn-sm btn-primary" href="{{ route('customer.bills.show', $bill) }}">Detail</a></td></tr>@empty<tr><td colspan="5" class="text-center text-muted">Belum ada riwayat tagihan.</td></tr>@endforelse</tbody></table></div></div></div>

<div class="card"><div class="card-header"><h4>Riwayat Pembayaran</h4></div><div class="card-body"><div class="table-responsive"><table id="customer-payments-table" class="table table-striped"><thead><tr><th>Tanggal</th><th>Tagihan</th><th>Metode</th><th>Jumlah</th><th>Status</th><th>Alasan penolakan</th><th>Bukti</th><th>Kuitansi</th></tr></thead><tbody>@forelse($payments as $payment)@php($proofExtension = strtolower(pathinfo((string) $payment->proof_path, PATHINFO_EXTENSION)))<tr><td>{{ format_tanggal_id($payment->paid_date) }}</td><td>{{ $payment->bill->bill_number }}</td><td>{{ $payment->paymentMethod->name }}</td><td>{{ format_rupiah($payment->amount) }}</td><td><span class="badge badge-{{ ['pending'=>'warning','confirmed'=>'success','rejected'=>'danger'][$payment->status->value] ?? 'secondary' }}">{{ ucfirst($payment->status->value) }}</span></td><td>{{ $payment->rejection_reason ?: '-' }}</td><td>@if($payment->proof_path)<button type="button" class="btn btn-sm btn-light js-view-payment-proof" data-toggle="modal" data-target="#payment-proof-modal" data-proof-url="{{ route('customer.payments.proof', $payment) }}" data-proof-kind="{{ in_array($proofExtension, $imageExtensions, true) ? 'image' : 'document' }}" data-proof-title="Bukti pembayaran {{ $payment->bill->bill_number }}"><i class="fas fa-image mr-1"></i> Lihat bukti</button>@else - @endif</td><td>@if($payment->receipt)<a href="{{ route('customer.receipts.show', $payment->receipt) }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">E-receipt</a>@else - @endif</td></tr>@empty<tr><td colspan="8" class="text-center text-muted"><i class="fas fa-history fa-2x d-block mb-2"></i>Belum ada pembayaran.</td></tr>@endforelse</tbody></table></div></div></div>

<div class="modal fade" id="payment-proof-modal" tabindex="-1" role="dialog" aria-labelledby="payment-proof-title" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><h5 id="payment-proof-title" class="modal-title">Bukti Pembayaran</h5><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div><div class="modal-body text-center"><img id="payment-proof-image" class="payment-proof-preview d-none" src="" alt=""><iframe id="payment-proof-document" class="payment-proof-document d-none" title="Pratinjau bukti pembayaran"></iframe></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button></div></div></div></div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('bundles/datatables/datatables.min.css') }}">
<style>
    .payment-proof-preview { display: block; width: auto; max-width: 100%; max-height: 70vh; margin: 0 auto; border-radius: 4px; }
    .payment-proof-document { width: 100%; height: 70vh; border: 0; }
    #payment-proof-modal .modal-body { background: #f8f9fc; }
</style>
@endpush
@push('plugin-scripts')<script src="{{ asset('bundles/datatables/datatables.min.js') }}"></script>@endpush
@push('scripts')
<script>
    $(function () {
        $('#customer-payments-table').DataTable({ pageLength: 10, ordering: true, responsive: true, language: { emptyTable: 'Belum ada pembayaran.', search: 'Cari:', lengthMenu: 'Tampilkan _MENU_', info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ pembayaran', paginate: { next: 'Berikutnya', previous: 'Sebelumnya' } } });

        $(document).on('click', '.js-view-payment-proof', function () {
            var button = $(this);
            var image = $('#payment-proof-image');
            var documentFrame = $('#payment-proof-document');
            var url = button.data('proof-url');
            var title = button.data('proof-title');

            $('#payment-proof-title').text(title);
            image.addClass('d-none').attr({ src: '', alt: '' });
            documentFrame.addClass('d-none').attr('src', '');

            if (button.data('proof-kind') === 'image') {
                image.attr({ src: url, alt: title }).removeClass('d-none');
            } else {
                documentFrame.attr('src', url).removeClass('d-none');
            }
        });

        $('#payment-proof-modal').on('hidden.bs.modal', function () {
            $('#payment-proof-image').attr('src', '').addClass('d-none');
            $('#payment-proof-document').attr('src', '').addClass('d-none');
        });
    });
</script>
@endpush
