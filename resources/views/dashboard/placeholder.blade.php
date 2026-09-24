@extends('layouts.app')

@section('title', 'Dashboard '.$roleLabel)
@section('page-title', 'Dashboard '.$roleLabel)
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12 col-md-8 col-lg-8">
        <div class="card">
            <div class="card-header"><h4>Selamat datang, {{ auth()->user()->name }}</h4></div>
            <div class="card-body">
                <p class="mb-0">Dashboard {{ strtolower($roleLabel) }} siap digunakan. Ringkasan fitur akan tersedia pada fase berikutnya.</p>
            </div>
        </div>
    </div>
</div>
@endsection
