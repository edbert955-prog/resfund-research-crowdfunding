@extends('layouts.donor')
@section('title', 'Profil dan Notifikasi')
@section('page-heading', 'Profil & Notifikasi')
@section('donor-content')
@include('donor.partials.page-intro', ['pageHeading' => 'Profil & Notifikasi', 'breadcrumb' => 'Profil & Notifikasi', 'eyebrow' => 'PENGATURAN AKUN', 'pageDescription' => 'Perbarui informasi dasar akun dan kelola pemberitahuan aktivitas ResFund.'])
<div class="donor-content-grid profile-grid">
    <section class="donor-panel"><div class="donor-panel-heading"><div><span class="donor-section-kicker">INFORMASI PRIBADI</span><h2>Profil Donatur</h2><p>Informasi ini digunakan pada riwayat transaksi prototype.</p></div></div>
        <div class="profile-identity"><span class="profile-avatar-large" aria-hidden="true">{{ strtoupper(substr($user['name'] ?? 'D', 0, 1)) }}</span><div><strong>{{ $user['name'] }}</strong><small>{{ $user['email'] }}</small><span class="status-pill settled">Akun Donatur</span></div></div>
        <form method="POST" action="{{ route('donor.profile.update') }}" class="donor-form">@csrf
            <div class="donor-form-field"><label for="profile-name">Nama lengkap</label><input id="profile-name" name="name" value="{{ old('name', $user['name'] ?? '') }}" autocomplete="name" minlength="3" maxlength="100" required></div>
            <div class="donor-form-field"><label for="profile-email">Alamat email</label><input id="profile-email" type="email" value="{{ $user['email'] ?? '' }}" disabled aria-describedby="email-readonly-note"><small id="email-readonly-note">Email tidak dapat diubah pada prototype ini.</small></div>
            <div class="donor-form-field"><label for="profile-phone">Nomor telepon</label><input id="profile-phone" name="phone" value="{{ old('phone', $user['phone'] ?? '') }}" autocomplete="tel" required><small>Gunakan nomor yang aktif untuk kebutuhan notifikasi.</small></div>
            <button type="submit" class="button button-primary">Simpan perubahan</button>
        </form>
        <div class="profile-session-note"><strong>Penyimpanan prototype</strong><p>Perubahan disimpan sementara pada sesi browser. Profil belum tersimpan pada database permanen.</p></div>
    </section>
    <section class="donor-panel" id="notifications"><div class="donor-panel-heading"><div><span class="donor-section-kicker">PEMBERITAHUAN</span><h2>Notifikasi</h2><p>{{ $unreadCount }} notifikasi belum dibaca.</p></div>@if(count($notifications))<form method="POST" action="{{ route('donor.notifications.read-all') }}">@csrf<button class="text-button" type="submit">Tandai semua dibaca</button></form>@endif</div>
        @if(count($notifications))<div class="donor-notification-list">@foreach($notifications as $notification)<article class="donor-notification-item {{ $notification['read'] ? '' : 'is-unread' }}"><span class="notification-type-icon type-{{ $notification['type'] }}" aria-hidden="true">{{ $notification['type'] === 'refund' ? '↩' : ($notification['type'] === 'audit' ? '◎' : ($notification['type'] === 'ledger' ? '▧' : '◷')) }}</span><div class="notification-copy"><strong>{{ $notification['title'] }}</strong><p>{{ $notification['message'] }}</p><small>{{ $notification['date'] }}</small></div><div class="notification-actions"><a href="{{ url($notification['url']) }}" class="table-action">Buka</a>@if(!$notification['read'])<form method="POST" action="{{ route('donor.notifications.read', ['id' => $notification['id']]) }}">@csrf<button type="submit" class="text-button">Tandai dibaca</button></form>@else<span class="read-label">Sudah dibaca</span>@endif</div></article>@endforeach</div>@else<div class="donor-empty-state donor-empty-compact"><span class="empty-symbol" aria-hidden="true">♧</span><h3>Belum ada notifikasi</h3><p>Pemberitahuan transaksi dan perkembangan proyek akan muncul di sini.</p></div>@endif
    </section>
</div>
@endsection
