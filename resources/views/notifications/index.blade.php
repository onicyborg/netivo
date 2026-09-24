@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('breadcrumb', 'Notifikasi')

@section('content')
<div class="card">
    <div class="card-header"><h4>Notifikasi Saya</h4><div class="card-header-action"><form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button type="submit" class="btn btn-sm btn-light">Tandai semua dibaca</button></form></div></div>
    <div class="card-body p-0">
        @forelse($notifications as $notification)
            <a href="{{ route('notifications.read', $notification) }}" class="d-block p-3 border-bottom {{ $notification->read_at ? '' : 'bg-light' }}"><div class="d-flex justify-content-between"><strong>{{ $notification->title }}</strong><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small></div><div class="text-dark">{{ $notification->message }}</div>@if(!$notification->read_at)<span class="badge badge-primary mt-2">Belum dibaca</span>@endif</a>
        @empty
            <div class="empty-state"><div class="empty-state-icon"><i class="fas fa-bell"></i></div><h2>Belum ada notifikasi</h2><p class="lead">Notifikasi aktivitas sistem akan tampil di sini.</p></div>
        @endforelse
    </div>
    <div class="card-footer">{{ $notifications->links() }}</div>
</div>
@endsection
