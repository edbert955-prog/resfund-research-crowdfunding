<!DOCTYPE html>
@php($layoutUser = $user ?? session('prototype_user', []))
@php($layoutNotifications = session('prototype_notifications', \App\Support\DonorPrototypeData::notificationsFor($layoutUser)))
@php($layoutUnreadCount = count(array_filter($layoutNotifications, fn ($item) => !($item['read'] ?? false))))
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173B73">
    <meta name="description" content="Dashboard Donatur ResFund untuk memantau donasi, proyek riset, dan transparansi penggunaan dana.">
    <title>@yield('title', 'Dashboard Donatur') | ResFund</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/donor-dashboard.css') }}">
    <script src="{{ asset('assets/js/app.js') }}" defer></script>
    <script src="{{ asset('assets/js/donor-dashboard.js') }}" defer></script>
    @stack('head')
</head>
<body class="donor-app-body">
    <a class="skip-link" href="#donor-main">Lewati ke konten utama</a>
    <div class="donor-app-shell">
        <aside class="donor-sidebar" id="donor-sidebar" aria-label="Navigasi dashboard Donatur">
            <div class="donor-sidebar-brand">
                <a class="brand" href="{{ route('home') }}" aria-label="ResFund, kembali ke area publik">
                    <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 40 40" fill="none"><path d="M20 4.5 34 10v9.4c0 8.4-5.8 13.1-14 16.1C11.8 32.5 6 27.8 6 19.4V10L20 4.5Z" stroke="currentColor" stroke-width="2.3"/><path d="M13.4 20.2h13.2M20 13.6v13.2M15.4 15.4l9.2 9.2M24.6 15.4l-9.2 9.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
                    <span class="brand-copy"><strong>ResFund</strong><small>Research for Impact</small></span>
                </a>
                <span class="donor-role-chip">Area Donatur</span>
            </div>
            <div class="donor-sidebar-label">MENU UTAMA</div>
            <nav class="donor-nav" aria-label="Menu utama Donatur">
                <a href="{{ route('donor.dashboard') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'dashboard' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'dashboard') aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">▦</span><span>Dashboard Donatur</span>
                </a>
                <a href="{{ route('donor.donations.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'donations' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'donations') aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">▤</span><span>Donasi Saya / Riwayat</span>
                </a>
                <a href="{{ route('donor.monitoring.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'monitoring' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'monitoring') aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">◷</span><span>Monitoring Proyek</span>
                </a>
                <a href="{{ route('donor.finance') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'finance' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'finance') aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">▥</span><span>Laporan Keuangan</span><span class="nav-external" aria-label="halaman publik">↗</span>
                </a>
                <a href="{{ route('donor.ledger.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'ledger' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'ledger') aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">▧</span><span>Ledger &amp; Preview Kuitansi</span>
                </a>
                <a href="{{ route('donor.audit.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'audit' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'audit') aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">◎</span><span>Klarifikasi Audit</span>
                </a>
            </nav>
            <div class="donor-sidebar-label donor-sidebar-label-bottom">AKUN</div>
            <nav class="donor-nav" aria-label="Menu akun Donatur">
                <a href="{{ route('donor.profile') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'profile' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'profile') aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">◉</span><span>Profil &amp; Notifikasi</span>
                    @if($layoutUnreadCount > 0)<span class="nav-count">{{ $layoutUnreadCount }}</span>@endif
                </a>
                <a href="{{ route('home') }}" class="donor-nav-link"><span class="nav-icon" aria-hidden="true">⌂</span><span>Kembali ke Website</span></a>
            </nav>
            <div class="donor-sidebar-note"><strong>Transparansi dimulai dari informasi.</strong><p>Periksa status donasi, milestone, dan laporan penggunaan dana dari satu tempat.</p></div>
            <form method="POST" action="{{ route('logout') }}" class="donor-logout-form">@csrf<button type="submit" class="donor-logout"><span aria-hidden="true">↪</span> Keluar dari akun</button></form>
        </aside>

        <div class="donor-main-column">
            <header class="donor-topbar">
                <button class="donor-mobile-toggle" type="button" aria-label="Buka menu dashboard" aria-expanded="false" aria-controls="donor-sidebar" data-donor-menu-toggle><span aria-hidden="true">☰</span></button>
                <div class="donor-topbar-context"><span class="donor-topbar-kicker">RESFUND / AREA DONATUR</span><strong>@yield('page-heading', 'Dashboard Donatur')</strong></div>
                <div class="donor-topbar-actions">
                    <a href="{{ route('donor.profile') }}#notifications" class="donor-notification-button" aria-label="Notifikasi, {{ $layoutUnreadCount }} belum dibaca"><span aria-hidden="true">♧</span>@if($layoutUnreadCount > 0)<i>{{ $layoutUnreadCount }}</i>@endif</a>
                    <a href="{{ route('donor.profile') }}" class="donor-user-chip"><span class="donor-avatar" aria-hidden="true">{{ strtoupper(substr($user['name'] ?? 'D', 0, 1)) }}</span><span><strong>{{ $user['name'] ?? 'Donatur' }}</strong><small>Donatur</small></span></a>
                </div>
            </header>

            @if(session('success') || session('info'))
                <div class="donor-flash-wrap" role="status" aria-live="polite">
                    <div class="flash-message {{ session('info') ? 'flash-info' : 'flash-success' }}"><span class="flash-icon" aria-hidden="true">{{ session('info') ? 'i' : '✓' }}</span><p>{{ session('success') ?? session('info') }}</p><button type="button" class="flash-close" aria-label="Tutup pemberitahuan" data-dismiss-alert>×</button></div>
                </div>
            @endif
            @if($errors->any())
                <div class="donor-flash-wrap" role="alert" aria-live="assertive"><div class="form-alert form-alert-error"><strong>Periksa kembali data yang dimasukkan:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
            @endif

            <main id="donor-main" class="donor-main-content">
                @yield('donor-content')
            </main>
            <footer class="donor-footer"><span>ResFund · Research Crowdfunding &amp; Innovation System</span><span>Prototype V1.2.1 · Data demonstrasi, bukan transaksi operasional</span></footer>
        </div>
    </div>
    <div class="donor-mobile-backdrop" data-donor-backdrop hidden></div>
</body>
</html>
