@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('breadcrumb', 'Profil Saya')

@section('content')
<div class="profile-page">
    <div class="row">
        <div class="col-12 col-lg-4">
            <section class="card profile-identity-card">
                <div class="profile-identity-card__cover"></div>
                <div class="card-body text-center">
                    <div class="profile-avatar-wrap">
                        <img src="{{ $user->profilePhotoUrl() }}" alt="Foto profil {{ $user->name }}" class="profile-avatar">
                        <span class="profile-avatar-status" aria-hidden="true"><i class="fas fa-check"></i></span>
                    </div>
                    <h2 class="profile-name">{{ $user->name }}</h2>
                    <span class="badge badge-primary profile-role">{{ ucfirst($user->role->value) }}</span>
                    <p class="profile-email"><i class="far fa-envelope mr-1" aria-hidden="true"></i>{{ $user->email }}</p>
                    <div class="profile-identity-card__meta">
                        <span><i class="fas fa-shield-alt mr-1" aria-hidden="true"></i>Akun aktif</span>
                        <span><i class="fas fa-lock mr-1" aria-hidden="true"></i>Role dikelola admin</span>
                    </div>
                </div>
            </section>

            <div class="profile-tip">
                <i class="fas fa-lightbulb" aria-hidden="true"></i>
                <div><strong>Tips profil</strong><span>Gunakan foto yang jelas agar mudah dikenali pada navbar dan halaman aplikasi.</span></div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <section class="card profile-card">
                <div class="card-header profile-card__header">
                    <div><span class="profile-eyebrow">Pengaturan akun</span><h4>Informasi pribadi</h4></div>
                    <span class="profile-card__icon"><i class="fas fa-user-edit" aria-hidden="true"></i></span>
                </div>
                <div class="card-body">
                    <p class="profile-intro">Perbarui nama, email, dan foto profil Anda. Role akun tidak dapat diubah dari halaman ini.</p>
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="profile-upload-row">
                            <img id="profile-upload-preview" src="{{ $user->profilePhotoUrl() }}" alt="Pratinjau foto profil {{ $user->name }}" class="profile-upload-preview">
                            <div class="profile-upload-copy"><label for="profile_photo">Foto profil</label><span>JPG, JPEG, PNG, atau WEBP · maksimal 2 MB</span><input id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="form-control-file @error('profile_photo') is-invalid @enderror">@error('profile_photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6"><div class="form-group"><label for="name">Nama lengkap</label><input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" autocomplete="name" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                            <div class="col-12 col-md-6"><div class="form-group"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                        </div>
                        <div class="profile-readonly"><div><span class="profile-readonly__label">Role akun</span><strong>{{ ucfirst($user->role->value) }}</strong></div><i class="fas fa-lock" aria-hidden="true"></i></div>
                        <div class="profile-card__footer"><span class="text-muted small"><i class="fas fa-info-circle mr-1" aria-hidden="true"></i>Perubahan akan langsung diterapkan.</span><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan perubahan</button></div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <section class="card profile-card profile-password-card">
        <div class="card-header profile-card__header">
            <div><span class="profile-eyebrow">Keamanan</span><h4>Ubah kata sandi</h4></div>
            <span class="profile-card__icon profile-card__icon--muted"><i class="fas fa-key" aria-hidden="true"></i></span>
        </div>
        <div class="card-body">
            <p class="profile-intro">Gunakan kata sandi yang kuat dan jangan membagikannya kepada siapa pun.</p>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-12 col-md-4"><div class="form-group"><label for="current_password">Kata sandi saat ini</label><input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                    <div class="col-12 col-md-4"><div class="form-group"><label for="password">Kata sandi baru</label><input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                    <div class="col-12 col-md-4"><div class="form-group"><label for="password_confirmation">Konfirmasi kata sandi baru</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required></div></div>
                </div>
                <button type="submit" class="btn btn-light"><i class="fas fa-shield-alt mr-1"></i> Perbarui kata sandi</button>
            </form>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .profile-page .card { border: 1px solid #e8edf5; border-radius: 12px; box-shadow: 0 6px 20px rgba(52, 57, 94, .07); overflow: hidden; }
    .profile-identity-card { position: relative; }
    .profile-identity-card__cover { height: 92px; background: linear-gradient(135deg, #5064d8, #6c5ce7); }
    .profile-identity-card .card-body { padding: 0 1.5rem 1.5rem; }
    .profile-avatar-wrap { position: relative; width: 104px; height: 104px; margin: -52px auto 1rem; padding: 5px; border-radius: 50%; background: #fff; box-shadow: 0 5px 18px rgba(52, 57, 94, .18); }
    .profile-avatar { display: block; width: 94px; height: 94px; border-radius: 50%; object-fit: cover; }
    .profile-avatar-status { position: absolute; right: 1px; bottom: 6px; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border: 3px solid #fff; border-radius: 50%; background: #47bd75; color: #fff; font-size: 9px; }
    .profile-name { margin: 0; color: #34395e; font-size: 20px; font-weight: 700; }
    .profile-role { margin-top: .55rem; padding: .4rem .75rem; border-radius: 999px; font-size: 11px; text-transform: capitalize; }
    .profile-email { margin: .8rem 0 1.2rem; color: #7a869a; font-size: 13px; overflow-wrap: anywhere; }
    .profile-identity-card__meta { display: flex; justify-content: center; gap: 1rem; padding-top: 1rem; border-top: 1px solid #eef1f6; color: #98a2b3; font-size: 11px; }
    .profile-tip { display: flex; align-items: flex-start; gap: .75rem; margin-top: 1rem; padding: 1rem; border: 1px solid #e4e8ff; border-radius: 10px; background: #f5f7ff; color: #5064d8; }
    .profile-tip i { margin-top: 2px; }
    .profile-tip div { display: flex; flex-direction: column; gap: .2rem; }
    .profile-tip strong { font-size: 12px; }
    .profile-tip span { color: #7a869a; font-size: 11px; line-height: 1.5; }
    .profile-card { height: 100%; }
    .profile-card__header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 76px; padding: 1rem 1.35rem; }
    .profile-card__header h4 { margin: .15rem 0 0; color: #34395e; font-size: 16px; font-weight: 700; }
    .profile-eyebrow { display: block; color: #98a2b3; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .profile-card__icon { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; background: #edf0ff; color: #5064d8; }
    .profile-card__icon--muted { background: #f0f2f5; color: #7b8794; }
    .profile-card .card-body { padding: 1.35rem; }
    .profile-intro { margin: 0 0 1.25rem; color: #7a869a; font-size: 13px; line-height: 1.6; }
    .profile-upload-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.35rem; padding: 1rem; border: 1px dashed #dce2ef; border-radius: 9px; background: #fbfcfe; }
    .profile-upload-preview { flex: 0 0 64px; width: 64px; height: 64px; border-radius: 50%; object-fit: cover; }
    .profile-upload-copy { min-width: 0; }
    .profile-upload-copy label { display: block; margin-bottom: .25rem; color: #34395e; font-size: 13px; font-weight: 700; }
    .profile-upload-copy span { display: block; margin-bottom: .55rem; color: #98a2b3; font-size: 11px; }
    .profile-upload-copy .form-control-file { color: #7a869a; font-size: 12px; }
    .profile-card label { color: #5f6b7a; font-size: 12px; font-weight: 600; }
    .profile-card .form-control { min-height: 42px; border-color: #e0e5ef; border-radius: 7px; font-size: 13px; }
    .profile-readonly { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding: .85rem 1rem; border: 1px solid #e8edf5; border-radius: 7px; background: #f8f9fc; color: #98a2b3; }
    .profile-readonly div { display: flex; flex-direction: column; gap: .15rem; }
    .profile-readonly__label { font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .profile-readonly strong { color: #5f6b7a; font-size: 13px; text-transform: capitalize; }
    .profile-card__footer { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding-top: .35rem; }
    .profile-password-card { margin-top: 1.5rem; }
    @media (max-width: 575.98px) { .profile-identity-card__meta { align-items: center; flex-direction: column; gap: .45rem; } .profile-upload-row { align-items: flex-start; flex-direction: column; } .profile-card__footer { align-items: stretch; flex-direction: column; } .profile-card__footer .btn { width: 100%; } }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        $('#profile_photo').on('change', function (event) {
            var file = event.target.files && event.target.files[0];

            if (file && file.type.indexOf('image/') === 0) {
                $('#profile-upload-preview').attr('src', URL.createObjectURL(file));
            }
        });
    });
</script>
@endpush
