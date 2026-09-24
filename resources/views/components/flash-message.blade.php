@if (session('success'))
    <div class="alert alert-success alert-has-icon" role="alert"><div class="alert-icon"><i class="far fa-check-circle"></i></div><div class="alert-body">{{ session('success') }}</div></div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-has-icon" role="alert"><div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div><div class="alert-body">{{ session('error') }}</div></div>
@endif
@if ($errors->any())
    <div class="alert alert-danger alert-has-icon" role="alert"><div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div><div class="alert-body"><div class="font-weight-bold">Periksa kembali data Anda.</div><ul class="mb-0 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif
