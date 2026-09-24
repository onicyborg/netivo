@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page-title', 'Pengaturan Sistem')
@section('breadcrumb', 'Pengaturan Sistem')

@section('content')
<div class="row"><div class="col-12 col-lg-8"><div class="card"><div class="card-header"><h4>Pengaturan Billing</h4></div><div class="card-body">
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')
        <div class="form-group"><label for="bill_due_day">Tanggal jatuh tempo</label><input id="bill_due_day" name="bill_due_day" type="number" min="1" max="28" class="form-control @error('bill_due_day') is-invalid @enderror" value="{{ old('bill_due_day', $settings['bill_due_day'] ?? 10) }}" required><small class="form-text text-muted">Gunakan tanggal 1 sampai 28.</small>@error('bill_due_day')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <hr><h6>Informasi Perusahaan</h6>
        <div class="form-group"><label for="company_name">Nama perusahaan</label><input id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>@error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label for="company_address">Alamat perusahaan</label><textarea id="company_address" name="company_address" rows="3" class="form-control @error('company_address') is-invalid @enderror" required>{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>@error('company_address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label for="company_phone">Nomor telepon perusahaan</label><input id="company_phone" name="company_phone" class="form-control @error('company_phone') is-invalid @enderror" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" required>@error('company_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengaturan</button>
    </form>
</div></div></div></div>
@endsection
