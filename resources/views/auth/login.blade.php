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
.netivo-login{--ink:#172033;--muted:#697386;--line:#e5e8ee;--accent:#ee8a3a;background:#f3f5f8;color:var(--ink);overflow:hidden}.netivo-login__grid{grid-template-columns:minmax(420px,.92fr) minmax(520px,1.08fr)}.netivo-login__intro{position:relative;overflow:hidden;padding:clamp(32px,5vw,72px);background:#172033}.netivo-login__intro:before,.netivo-login__intro:after{position:absolute;border:1px solid rgba(255,255,255,.09);border-radius:50%;content:'';pointer-events:none}.netivo-login__intro:before{width:520px;height:520px;right:-230px;top:-150px}.netivo-login__intro:after{width:390px;height:390px;right:-110px;top:-85px}.netivo-login__brand,.netivo-login__copy,.netivo-login__footer{position:relative;z-index:1}.netivo-login__brand{gap:12px;font-size:19px}.netivo-login__brand img{border-radius:12px}.netivo-login__copy{max-width:505px;padding:68px 0}.netivo-login__copy small{color:#ffb46c;letter-spacing:.14em}.netivo-login__copy h1{max-width:470px;margin-top:19px;font-size:clamp(34px,4.4vw,58px);letter-spacing:-.045em;line-height:1.06}.netivo-login__copy p{max-width:420px;margin-top:24px;color:#aeb8ca;font-size:15px;line-height:1.75}.netivo-login__footer{color:#8793a8}.netivo-login__panel{padding:clamp(28px,6vw,86px);background:#f3f5f8}.netivo-login__card{max-width:438px;padding:clamp(28px,4vw,46px);border:1px solid rgba(23,32,51,.08);border-radius:18px;background:#fff;box-shadow:0 22px 60px rgba(23,32,51,.09)}.netivo-login__card h2{color:var(--ink);font-size:30px;letter-spacing:-.035em}.netivo-login__subtitle{margin:9px 0 30px;color:var(--muted);line-height:1.6}.netivo-login label{margin-bottom:8px}.netivo-login .form-control{height:48px;border-color:var(--line);border-radius:9px;color:var(--ink);transition:border-color .2s ease,box-shadow .2s ease}.netivo-login .form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(238,138,58,.16)}.netivo-login__remember{margin:-2px 0 25px}.netivo-login__remember input{accent-color:var(--accent)}.netivo-login__submit{min-height:49px;border-color:var(--accent);border-radius:9px;background:var(--accent);transition:background .2s ease,transform .2s ease,box-shadow .2s ease}.netivo-login__submit:hover,.netivo-login__submit:focus{border-color:#d87528;background:#d87528;box-shadow:0 8px 18px rgba(238,138,58,.22);transform:translateY(-1px)}@media(max-width:767.98px){.netivo-login__grid{display:block}.netivo-login__intro{min-height:190px;padding:24px 22px}.netivo-login__copy{padding:32px 0 4px}.netivo-login__copy h1{max-width:380px;margin-top:12px;font-size:30px}.netivo-login__copy p,.netivo-login__footer{display:none}.netivo-login__panel{min-height:calc(100vh - 190px);padding:24px 18px 34px}.netivo-login__card{padding:28px 22px;border-radius:14px;box-shadow:0 12px 34px rgba(23,32,51,.08)}}
</style>
<style>
    .netivo-login__brand img { display: block; width: 42px; height: 42px; padding: 6px; border-radius: 12px; object-fit: contain; background: #fff; }
    .netivo-login__remember { display: flex; align-items: center; gap: 9px; min-height: 24px; }
    .netivo-login__remember input { flex: 0 0 17px; width: 17px; height: 17px; margin: 0; vertical-align: middle; }
    .netivo-login__remember label { display: inline-flex; align-items: center; min-height: 24px; margin: 0; line-height: 1.3; }
    .netivo-login__password-field { position: relative; }
    .netivo-login__password-field .form-control { padding-right: 48px; }
    .netivo-login__password-toggle { position: absolute; top: 50%; right: 8px; display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; margin-top: -17px; padding: 0; border: 0; border-radius: 7px; background: transparent; color: #8b95a7; cursor: pointer; }
    .netivo-login__password-toggle:hover, .netivo-login__password-toggle:focus { background: #f3f5f8; color: #172033; outline: none; }
    .netivo-login__password-toggle:focus-visible { box-shadow: 0 0 0 3px rgba(238,138,58,.2); }
    .netivo-login__password-toggle svg { width: 17px; height: 17px; }
</style>
@endpush

@section('content')
<div class="netivo-login">
    <div class="netivo-login__grid">
        <section class="netivo-login__intro" aria-label="Tentang Netivo">
            <a class="netivo-login__brand" href="{{ url('/') }}"><img src="{{ url('assets/logo/netivo.png') }}" alt="Logo Netivo" width="42" height="42"><span>Netivo</span></a>
            <div class="netivo-login__copy"><small>Internet operations</small><h1>Satu ruang untuk layanan yang terus terhubung.</h1><p>Kelola customer, tagihan, pembayaran, dan laporan dengan alur kerja yang lebih teratur.</p></div>
            <span class="netivo-login__footer">&copy; {{ date('Y') }} Netivo</span>
        </section>
        <main class="netivo-login__panel">
            <div class="netivo-login__card">
                <h2>Selamat datang kembali</h2><p class="netivo-login__subtitle">Masuk untuk melanjutkan ke ruang kerja Anda.</p>
                @include('components.flash-message')
                <form method="POST" action="{{ route('login.store') }}" novalidate>
                    @csrf
                    <div class="form-group"><label for="email">Email</label><input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" aria-describedby="email-error" autofocus required>@error('email')<div id="email-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="password">Kata sandi</label><div class="netivo-login__password-field"><input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password" aria-describedby="password-error" required><button type="button" class="netivo-login__password-toggle" id="password-toggle" aria-label="Tampilkan kata sandi" aria-pressed="false"><i data-feather="eye" aria-hidden="true"></i></button></div>@error('password')<div id="password-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    <div class="netivo-login__remember"><input type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))><label for="remember">Ingat saya</label></div>
                    <button type="submit" class="netivo-login__submit">Masuk</button>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var password = document.getElementById('password');
        var toggle = document.getElementById('password-toggle');

        if (!password || !toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            var isVisible = password.type === 'text';
            password.type = isVisible ? 'password' : 'text';
            toggle.setAttribute('aria-pressed', String(!isVisible));
            toggle.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
            toggle.innerHTML = '<i data-feather="' + (isVisible ? 'eye' : 'eye-off') + '" aria-hidden="true"></i>';

            if (window.feather) {
                window.feather.replace();
            }
        });
    }());
</script>
@endpush
