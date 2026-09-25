@extends('layouts.app')

@section('title', 'Detail Perubahan Layanan')
@section('page-title', 'Detail Perubahan Layanan')
@section('breadcrumb', 'Detail Perubahan Layanan')

@section('content')
@error('upgrade')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
@error('rejection_reason')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<div class="row">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><h4>Data Customer</h4></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Nama</dt><dd class="col-7">{{ $upgrade->customer->user->name }}</dd>
                    <dt class="col-5">Nomor</dt><dd class="col-7">{{ $upgrade->customer->customer_number }}</dd>
                    <dt class="col-5">Layanan saat ini</dt><dd class="col-7">{{ $upgrade->fromService->name }}</dd>
                    <dt class="col-5">Layanan tujuan</dt><dd class="col-7">{{ $upgrade->toService->name }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7">{{ ucfirst($upgrade->status->value) }}</dd>
                    <dt class="col-5">Catatan</dt><dd class="col-7">{{ $upgrade->note ?: '-' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><h4>Keputusan</h4></div>
            <div class="card-body">
                @if($upgrade->effective_period)
                    <p>Periode efektif: <strong>{{ $upgrade->effective_period }}</strong></p>
                @endif

                @if($upgrade->reviewer)
                    <p>Diproses oleh {{ $upgrade->reviewer->name }} pada {{ format_tanggal_id($upgrade->reviewed_at) }}.</p>
                @endif

                @if($upgrade->status->value === 'pending')
                    <form id="approve-upgrade-form" method="POST" action="{{ route('admin.upgrades.approve', $upgrade) }}" class="mb-2">
                        @csrf
                        <button type="button" id="approve-upgrade-button" class="btn btn-success btn-block">
                            <i class="fas fa-check"></i> Setujui Perubahan
                        </button>
                    </form>
                    <button type="button" id="reject-upgrade-button" class="btn btn-danger btn-block">
                        <i class="fas fa-times"></i> Tolak
                    </button>
                @else
                    <p class="text-muted">Pengajuan ini sudah diproses.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<form id="reject-upgrade-form" method="POST" action="{{ route('admin.upgrades.reject', $upgrade) }}" class="d-none">
    @csrf
    <input type="hidden" id="rejection-reason-value" name="rejection_reason" value="{{ old('rejection_reason') }}">
</form>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#approve-upgrade-button').on('click', function () {
            Swal.fire({
                icon: 'question',
                title: 'Setujui perubahan layanan?',
                text: 'Perubahan layanan ini akan diproses untuk customer.',
                showCancelButton: true,
                confirmButtonText: 'Ya, setujui',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    $('#approve-upgrade-form').trigger('submit');
                }
            });
        });

        $('#reject-upgrade-button').on('click', function () {
            Swal.fire({
                icon: 'warning',
                title: 'Tolak perubahan layanan?',
                input: 'textarea',
                inputLabel: 'Alasan penolakan',
                inputPlaceholder: 'Tuliskan alasan penolakan...',
                inputAttributes: {
                    'aria-label': 'Alasan penolakan'
                },
                inputValidator: function (value) {
                    return !value || !value.trim() ? 'Alasan penolakan wajib diisi.' : undefined;
                },
                showCancelButton: true,
                confirmButtonText: 'Ya, tolak perubahan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    $('#rejection-reason-value').val(result.value.trim());
                    $('#reject-upgrade-form').trigger('submit');
                }
            });
        });
    });
</script>
@endpush
