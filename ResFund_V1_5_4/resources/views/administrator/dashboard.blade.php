@extends('layouts.administrator')
@section('title', 'Dashboard Admin')
@section('page-heading', 'Dashboard Admin')
@section('admin-content')
<div class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">PUSAT KENDALI RESFUND</span><h1>Selamat datang, {{ $user['name'] }}.</h1><p>Pantau akun, validasi akademik, penugasan reviewer, dan aktivitas sistem dari satu tempat.</p></div><a class="button button-primary" href="{{ route('administrator.monitoring.index') }}">Lihat audit log <x-icon name="arrow-right" size="16" /></a></div>
    <section class="admin-kpi-grid" aria-label="Ringkasan administrasi">
        <article class="admin-kpi-card"><span class="admin-kpi-icon icon-blue" ><x-icon name="users" size="21" /></span><div><span>Total pengguna</span><strong>{{ $userCount }}</strong><small>{{ $activeUserCount }} akun aktif</small></div></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon icon-amber" ><x-icon name="clock" size="21" /></span><div><span>Validasi peneliti</span><strong>{{ $pendingResearcherCount }}</strong><small>Menunggu pemeriksaan identitas</small></div></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon icon-green" ><x-icon name="user" size="21" /></span><div><span>Reviewer aktif</span><strong>{{ $reviewerCount }}</strong><small>Siap menerima penugasan</small></div></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon icon-purple" ><x-icon name="arrow-left-right" size="21" /></span><div><span>Penugasan berjalan</span><strong>{{ $pendingAssignmentCount }}</strong><small>Menunggu atau perlu ditindaklanjuti</small></div></article>
    </section>
    <div class="admin-dashboard-grid">
        <section class="admin-panel">
            <div class="admin-panel-heading"><div><span class="admin-eyebrow">TINDAKAN UTAMA</span><h2>Administrasi sistem</h2><p>Pilih pekerjaan yang ingin dikelola.</p></div></div>
            <div class="admin-quick-grid">
                <a href="{{ route('administrator.users.index') }}" class="admin-quick-card"><span class="admin-quick-icon icon-blue"><x-icon name="users" size="22" /></span><strong>Manajemen Pengguna</strong><small>Lihat akun dan status akses.</small><span class="admin-card-arrow">Kelola pengguna <x-icon name="arrow-right" size="15" /></span></a>
                <a href="{{ route('administrator.researchers.index') }}" class="admin-quick-card"><span class="admin-quick-icon icon-amber"><x-icon name="check-circle" size="22" /></span><strong>Validasi Peneliti</strong><small>Periksa data akademik dan keputusan validasi.</small><span class="admin-card-arrow">Periksa peneliti <x-icon name="arrow-right" size="15" /></span></a>
                <a href="{{ route('administrator.reviewers.index') }}" class="admin-quick-card"><span class="admin-quick-icon icon-green"><x-icon name="user" size="22" /></span><strong>Manajemen Reviewer</strong><small>Kelola keahlian dan status reviewer.</small><span class="admin-card-arrow">Kelola reviewer <x-icon name="arrow-right" size="15" /></span></a>
                <a href="{{ route('administrator.assignments.index') }}" class="admin-quick-card"><span class="admin-quick-icon icon-purple"><x-icon name="arrow-left-right" size="22" /></span><strong>Penugasan Reviewer</strong><small>Pastikan dua reviewer independen ditugaskan.</small><span class="admin-card-arrow">Atur penugasan <x-icon name="arrow-right" size="15" /></span></a>
            </div>
        </section>
        <section class="admin-panel">
            <div class="admin-panel-heading"><div><span class="admin-eyebrow">JEJAK AKTIVITAS</span><h2>Monitoring &amp; Audit Log</h2><p>Aktivitas sistem terbaru yang tercatat.</p></div><a href="{{ route('administrator.monitoring.index') }}" class="admin-text-link">Lihat semua <x-icon name="arrow-right" size="15" /></a></div>
            <div class="admin-log-list">
                @forelse($recentLogs as $log)
                    <div class="admin-log-item"><span class="admin-log-dot severity-{{ $log['severity'] ?? 'info' }}"></span><div><strong>{{ $log['action'] }}</strong><p>{{ $log['detail'] }}</p><small>{{ $log['actor'] }} · {{ $log['timestamp'] }}</small></div></div>
                @empty
                    <div class="admin-empty"><h3>Belum ada aktivitas</h3><p>Aktivitas administrasi akan tercatat di sini.</p></div>
                @endforelse
            </div>
        </section>
    </div>
    <div class="admin-note"><strong>Prinsip pengelolaan:</strong> Administrator mengelola akses dan penugasan, tetapi tidak memberikan keputusan evaluasi milestone dan tidak menjalankan pencairan dana secara manual.</div>
</div>
@endsection
