@extends('layouts.auth')

@section('title', 'Masuk')

@push('styles')
<style>
    .netivo-login { min-height: 100vh; background: #f5f7ff; color: #26315c; }
    .netivo-login__grid { display: grid; min-height: 100vh; grid-template-columns: minmax(0, 1.08fr) minmax(380px, .92fr); }
    .netivo-login__intro { display: flex; flex-direction: column; justify-content: space-between; padding: clamp(32px, 6vw, 88px); background: #303fa9; color: #fff; }
    .netivo-login__brand { display: inline-flex; align-items: center; gap: 11px; width: fit-content; color: #fff; font-size: 18px; font-weight: 800; text-decoration: none; }
    .netivo-login__brand:hover { color: #fff; text-decoration: none; }
    .netivo-login__brand img { width: 42px; height: 42px; border-radius: 9px; object-fit: contain; background: #fff; }
    .netivo-login__copy { max-width: 540px; margin: auto 0; }
    .netivo-login__copy small { color: #ffd477; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .netivo-login__copy h1 { max-width: 520px; margin: 16px 0 0; color: #fff; font-size: clamp(32px, 4vw, 52px); font-weight: 800; line-height: 1.15; }
    .netivo-login__copy p { max-width: 455px; margin: 20px 0 0; color: #e1e7ff; font-size: 16px; line-height: 1.7; }
    .netivo-login__footer { color: #c7d0ff; font-size: 12px; }
    .netivo-login__panel { display: flex; align-items: center; justify-content: center; padding: 32px; background: #fff; }
    .netivo-login__card { width: 100%; max-width: 400px; }
    .netivo-login__card h2 { margin: 0; color: #26315c; font-size: 28px; font-weight: 800; }
    .netivo-login__subtitle { margin: 8px 0 30px; color: #667085; font-size: 14px; }
    .netivo-login label { display: block; margin-bottom: 7px; color: #344054; font-size: 13px; font-weight: 700; }
    .netivo-login .form-control { height: 46px; border: 1px solid #d9def2; border-radius: 7px; color: #26315c; font-size: 14px; }
    .netivo-login .form-control:focus { border-color: #4c5bd4; box-shadow: 0 0 0 3px rgba(76,91,212,.15); }
    .netivo-login__remember { display: flex; align-items: center; min-height: 24px; margin: 4px 0 24px; }
    .netivo-login__remember input { width: 17px; height: 17px; margin-right: 8px; accent-color: #4c5bd4; }
    .netivo-login__remember label { margin: 0; color: #667085; font-weight: 400; cursor: pointer; }
    .netivo-login__submit { width: 100%; min-height: 46px; border: 1px solid #4c5bd4; border-radius: 7px; background: #4c5bd4; color: #fff; font-weight: 800; }
    .netivo-login__submit:hover, .netivo-login__submit:focus { border-color: #303fa9; background: #303fa9; color: #fff; }
    @media (max-width: 767.98px) { .netivo-login__grid { grid-template-columns: 1fr; } .netivo-login__intro { display: none; } .netivo-login__panel { min-height: 100vh; padding: 28px 20px; } }
</style>
@endpush

@section('content')
<div class="netivo-login">
    <div class="netivo-login__grid">
        <section class="netivo-login__intro" aria-label="Tentang Netivo">
            <a class="netivo-login__brand" href="{{ url('/') }}"><img src="/assets/logo/netivo.png" alt="Logo Netivo" width="42" height="42"><span>Netivo</span></a>
            <div class="netivo-login__copy"><small>Sistem billing internet</small><h1>Kelola layanan tanpa alur yang rumit.</h1><p>Kelola customer, tagihan, pembayaran, dan laporan dari satu ruang kerja yang sederhana.</p></div>
            <span class="netivo-login__footer">&copy; {{ date('Y') }} Netivo</span>
        </section>
        <main class="netivo-login__panel">
            <div class="netivo-login__card">
                <h2>Selamat datang</h2><p class="netivo-login__subtitle">Masuk untuk melanjutkan ke ruang kerja Anda.</p>
                @include('components.flash-message')
                <form method="POST" action="{{ route('login.store') }}" novalidate>
                    @csrf
                    <div class="form-group"><label for="email">Email</label><input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" aria-describedby="email-error" autofocus required>@error('email')<div id="email-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="password">Kata sandi</label><input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password" aria-describedby="password-error" required>@error('password')<div id="password-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    <div class="netivo-login__remember"><input type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))><label for="remember">Ingat saya</label></div>
                    <button type="submit" class="netivo-login__submit">Masuk</button>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
