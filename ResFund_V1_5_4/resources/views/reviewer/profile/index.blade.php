@extends('layouts.reviewer')
@section('title', 'Profil dan Notifikasi')
@section('page-heading', 'Profil & Notifikasi')
@section('reviewer-content')
<div class="reviewer-page">
    <div class="reviewer-page-heading compact-heading">
        <div>
            <span class="reviewer-eyebrow">PENGATURAN AKUN REVIEWER</span>
            <h1>Profil &amp; Notifikasi</h1>
            <p>Periksa informasi akun dan atur pemberitahuan terkait penugasan serta evaluasi.</p>
        </div>
    </div>

    <div class="reviewer-profile-grid">
        <section class="reviewer-panel reviewer-profile-panel">
            <div class="reviewer-panel-heading"><div><span class="reviewer-eyebrow">IDENTITAS REVIEWER</span><h2>Profil Reviewer</h2><p>Identitas akademik terverifikasi ditampilkan sebagai informasi baca-saja.</p></div></div>
            <div class="reviewer-profile-identity">
                <span class="reviewer-profile-avatar" aria-hidden="true">{{ strtoupper(substr($user['name'] ?? 'R', 0, 1)) }}</span>
                <div><strong>{{ $user['name'] }}</strong><small>{{ $user['email'] }}</small><span class="reviewer-status status-success">Reviewer Independen</span></div>
            </div>
            <dl class="reviewer-profile-details">
                <div><dt>Nama lengkap</dt><dd>{{ $user['name'] }}</dd></div>
                <div><dt>Alamat email</dt><dd>{{ $user['email'] }}</dd></div>
                <div><dt>Institusi</dt><dd>{{ $user['institution'] ?? 'Belum dicantumkan' }}</dd></div>
                <div><dt>Bidang keahlian</dt><dd>{{ $user['expertise'] ?? 'Belum dicantumkan' }}</dd></div>
                <div><dt>Status akun</dt><dd><span class="reviewer-status status-success">Terverifikasi</span></dd></div>
                <div><dt>Peran</dt><dd>Reviewer Independen</dd></div>
            </dl>
            <form method="POST" action="{{ route('reviewer.profile.update') }}" class="reviewer-profile-form">
                @csrf
                <div class="reviewer-form-group">
                    <label for="reviewer-phone">Nomor telepon</label>
                    <input id="reviewer-phone" name="phone" type="tel" value="{{ old('phone', $user['phone'] ?? '') }}" autocomplete="tel" minlength="9" maxlength="20" required>
                    <small>Gunakan nomor aktif untuk kebutuhan komunikasi penugasan. Email dan identitas akademik tidak dapat diubah dari halaman ini.</small>
                    @error('phone')<small class="field-error">{{ $message }}</small>@enderror
                </div>
                <div class="reviewer-preference-box">
                    <strong>Preferensi pemberitahuan</strong>
                    <label><input type="checkbox" name="assignment_updates" value="1" @checked(old('assignment_updates', $preferences['assignment_updates'] ?? true))> Pemberitahuan penugasan baru</label>
                    <label><input type="checkbox" name="deadline_reminders" value="1" @checked(old('deadline_reminders', $preferences['deadline_reminders'] ?? true))> Pengingat tenggat penilaian</label>
                    <label><input type="checkbox" name="panel_updates" value="1" @checked(old('panel_updates', $preferences['panel_updates'] ?? true))> Pembaruan status panel reviewer</label>
                    <small>Preferensi disimpan pada sesi prototype. Pengiriman email/WhatsApp belum terhubung ke layanan notifikasi eksternal.</small>
                </div>
                <button type="submit" class="button button-primary">Simpan perubahan</button>
            </form>
            <div class="reviewer-profile-note"><strong>Penyimpanan prototype</strong><p>Nomor telepon dan preferensi hanya disimpan sementara pada sesi browser, bukan database permanen.</p></div>
        </section>

        <section class="reviewer-panel reviewer-notifications-panel" id="notifications">
            <div class="reviewer-panel-heading">
                <div><span class="reviewer-eyebrow">PEMBERITAHUAN AKTIVITAS</span><h2>Notifikasi</h2><p>{{ $unreadCount }} notifikasi belum dibaca.</p></div>
                @if(count($notifications) > 0)
                    <form method="POST" action="{{ route('reviewer.notifications.read-all') }}">@csrf<button type="submit" class="reviewer-text-link reviewer-text-button">Tandai semua dibaca</button></form>
                @endif
            </div>
            @if(count($notifications))
                <div class="reviewer-notification-list">
                    @foreach($notifications as $notification)
                        @php($notificationIcon = match($notification['type']) { 'conflict' => 'alert-circle', 'decision' => 'check-circle', 'panel' => 'users', 'deadline' => 'clock', default => 'bell' })
                        <article class="reviewer-notification-item {{ $notification['read'] ? '' : 'is-unread' }}">
                            <span class="reviewer-notification-icon notification-{{ $notification['type'] }}"><x-icon :name="$notificationIcon" size="20" /></span>
                            <div class="reviewer-notification-copy"><strong>{{ $notification['title'] }}</strong><p>{{ $notification['message'] }}</p><small>{{ $notification['date'] }}</small></div>
                            <div class="reviewer-notification-actions">
                                <a href="{{ $notification['url'] }}" class="button button-outline button-small">Buka</a>
                                @if(!$notification['read'])
                                    <form method="POST" action="{{ route('reviewer.notifications.read', ['notificationId' => $notification['id']]) }}">@csrf<button type="submit" class="reviewer-text-button">Tandai dibaca</button></form>
                                @else
                                    <span class="reviewer-read-label">Sudah dibaca</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="reviewer-empty-state"><span class="reviewer-empty-icon"><x-icon name="bell" size="24" /></span><h3>Belum ada notifikasi</h3><p>Pemberitahuan penugasan, keputusan, dan tenggat akan ditampilkan di sini.</p></div>
            @endif
        </section>
    </div>
</div>
@endsection
