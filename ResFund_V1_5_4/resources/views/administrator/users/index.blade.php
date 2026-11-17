@extends('layouts.administrator')
@section('title', 'Manajemen Pengguna')
@section('page-heading', 'Manajemen Pengguna')
@section('admin-content')
<div class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">PENGELOLAAN AKUN</span><h1>Manajemen Pengguna</h1><p>Lihat akun terdaftar, peran, status akses, dan informasi institusi pengguna.</p></div><div class="admin-heading-stat"><strong>{{ count($users) }}</strong><span>akun sesuai filter</span></div></div>
    <section class="admin-panel">
        <form method="GET" class="admin-filter-bar" action="{{ route('administrator.users.index') }}">
            <label class="admin-search"><span class="sr-only">Cari pengguna</span><input type="search" name="q" value="{{ $query }}" placeholder="Cari nama, email, atau institusi..."></label>
            <label><span class="sr-only">Filter peran</span><select name="role"><option value="">Semua peran</option>@foreach(['donatur'=>'Donatur','peneliti'=>'Peneliti','reviewer'=>'Reviewer','administrator'=>'Administrator'] as $key=>$label)<option value="{{ $key }}" @selected($role===$key)>{{ $label }}</option>@endforeach</select></label>
            <label><span class="sr-only">Filter status</span><select name="status"><option value="">Semua status</option><option value="active" @selected($status==='active')>Aktif</option><option value="pending" @selected($status==='pending')>Menunggu verifikasi</option><option value="suspended" @selected($status==='suspended')>Ditangguhkan</option></select></label>
            <button class="button button-primary" type="submit">Terapkan filter</button><a class="button button-secondary" href="{{ route('administrator.users.index') }}">Reset</a>
        </form>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Pengguna</th><th>Peran</th><th>Institusi</th><th>Status akun</th><th>Terdaftar</th><th>Aksi</th></tr></thead><tbody>
        @forelse($users as $item)
            <tr><td><strong>{{ $item['name'] }}</strong><small>{{ $item['email'] }}</small></td><td><span class="admin-role role-{{ $item['role'] }}">{{ $item['role_label'] }}</span></td><td>{{ $item['institution'] ?: '—' }}@if(!empty($item['academic_id']))<small>{{ $item['academic_id'] }}</small>@endif</td><td><span class="admin-status status-{{ $item['status'] }}">{{ ['active'=>'Aktif','pending'=>'Menunggu','suspended'=>'Ditangguhkan'][$item['status']] ?? $item['status'] }}</span></td><td>{{ $item['created_at'] }}</td><td><a class="admin-table-action" href="{{ route('administrator.users.show', ['userId'=>$item['id']]) }}">Lihat detail →</a></td></tr>
        @empty<tr><td colspan="6"><div class="admin-empty"><h3>Pengguna tidak ditemukan</h3><p>Ubah kata kunci atau filter pencarian.</p></div></td></tr>@endforelse
        </tbody></table></div>
        <div class="admin-table-foot">Menampilkan {{ count($users) }} akun pada sesi prototype ini. Data merupakan data demonstrasi dan akun yang dibuat dalam sesi browser.</div>
    </section>
</div>
@endsection
