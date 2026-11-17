@extends('layouts.administrator')
@section('title', 'Detail Pengguna')
@section('page-heading', 'Detail Pengguna')
@section('admin-content')
<div class="admin-page admin-narrow-page">
    <div class="admin-breadcrumb"><a href="{{ route('administrator.users.index') }}">Manajemen Pengguna</a><span>/</span><span>Detail akun</span></div>
    <div class="admin-page-heading"><div><span class="admin-eyebrow">INFORMASI AKUN</span><h1>Detail pengguna</h1><p>Periksa identitas dan kelola status akses akun.</p></div><a href="{{ route('administrator.users.index') }}" class="button button-secondary">← Kembali</a></div>
    <section class="admin-panel admin-detail-panel">
        <div class="admin-profile-summary"><span class="admin-large-avatar">{{ strtoupper(substr($managedUser['name'],0,1)) }}</span><div><h2>{{ $managedUser['name'] }}</h2><p>{{ $managedUser['email'] }}</p><span class="admin-role role-{{ $managedUser['role'] }}">{{ $managedUser['role_label'] }}</span></div></div>
        <dl class="admin-detail-grid"><div><dt>Nomor akun</dt><dd>{{ $managedUser['id'] }}</dd></div><div><dt>Nomor telepon</dt><dd>{{ $managedUser['phone'] ?: 'Belum tersedia' }}</dd></div><div><dt>Perguruan tinggi / institusi</dt><dd>{{ $managedUser['institution'] ?: 'Tidak berlaku' }}</dd></div><div><dt>NIDN / NIM</dt><dd>{{ $managedUser['academic_id'] ?: 'Tidak berlaku' }}</dd></div><div><dt>Status validasi</dt><dd>{{ str_replace('_',' ',ucfirst($managedUser['verification_status'] ?? 'tidak berlaku')) }}</dd></div><div><dt>Tanggal pendaftaran</dt><dd>{{ $managedUser['created_at'] }}</dd></div></dl>
        <div class="admin-action-box"><div><strong>Status akses</strong><p>{{ $managedUser['status']==='active' ? 'Akun dapat menggunakan fitur sesuai perannya.' : 'Akun tidak dapat mengakses area privat.' }}</p></div>
            @if($managedUser['role'] === 'administrator')
                <span class="admin-status status-active">Akun administrator dilindungi</span>
            @elseif($managedUser['role'] === 'peneliti' && ($managedUser['verification_status'] ?? '') === 'menunggu_verifikasi')
                <a class="button button-primary" href="{{ route('administrator.researchers.show',['userId'=>$managedUser['id']]) }}">Lanjut ke validasi peneliti</a>
            @else
                <form method="POST" action="{{ route('administrator.users.status',['userId'=>$managedUser['id']]) }}" data-confirm="Ubah status akses akun ini?">@csrf<input type="hidden" name="status" value="{{ $managedUser['status']==='active' ? 'suspended' : 'active' }}"><button class="button {{ $managedUser['status']==='active' ? 'button-danger' : 'button-primary' }}" type="submit">{{ $managedUser['status']==='active' ? 'Tangguhkan akun' : 'Aktifkan akun' }}</button></form>
            @endif
        </div>
        <p class="admin-help-text">Perubahan status pada versi ini hanya tersimpan di sesi prototype. Pengubahan peran dan penghapusan akun tidak disediakan untuk mencegah eskalasi hak akses yang tidak disengaja.</p>
    </section>
</div>
@endsection
