@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="card card-primary">
    <div class="card-header"><h4>Masuk ke Netivo</h4></div>
    <div class="card-body">
        <p class="text-muted">Gunakan email dan kata sandi akun Anda.</p>
        <form method="POST" action="{{ route('login.store') }}" novalidate>
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" tabindex="1" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" tabindex="2" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="remember" class="custom-control-input" id="remember">
                    <label class="custom-control-label" for="remember">Ingat saya</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="3">Masuk</button>
        </form>
    </div>
</div>
@endsection
