@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('breadcrumb', 'Profil Saya')

@section('content')
<div class="row">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><h4>Informasi Akun</h4></div>
            <div class="card-body">
                <dl class="row mb-0"><dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ $user->name }}</dd><dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $user->email }}</dd><dt class="col-sm-4">Peran</dt><dd class="col-sm-8">{{ ucfirst($user->role->value) }}</dd></dl>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><h4>Ubah Kata Sandi</h4></div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group"><label for="current_password">Kata sandi saat ini</label><input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="password">Kata sandi baru</label><input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="password_confirmation">Konfirmasi kata sandi baru</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required></div>
                    <button type="submit" class="btn btn-primary">Simpan Kata Sandi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
