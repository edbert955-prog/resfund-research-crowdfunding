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
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/motion.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/donor-dashboard.css') }}?v=1.5.4">
    <script src="{{ asset('assets/js/app.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/motion.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/donor-dashboard.js') }}?v=1.5.4" defer></script>
    @stack('head')
</head>
<body class="donor-app-body">
    <a class="skip-link" href="#donor-main">Lewati ke konten utama</a>
    <div class="donor-app-shell">
        <aside class="donor-sidebar" id="donor-sidebar" aria-label="Navigasi dashboard Donatur">
            <div class="donor-sidebar-brand">
                <a class="brand" href="{{ route('home') }}" aria-label="ResFund, kembali ke area publik"><img class="sidebar-brand-logo" src="{{ asset('assets/images/resfund-logo-white.png') }}" alt="ResFund — Research for Impact" width="220" height="74"></a>
                <span class="donor-role-chip">Area Donatur</span>
            </div>
            <div class="donor-sidebar-label">MENU UTAMA</div>
            <nav class="donor-nav" aria-label="Menu utama Donatur">
                <a href="{{ route('donor.dashboard') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'dashboard' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'dashboard') aria-current="page" @endif>
                    <x-icon name="dashboard" class="nav-icon" size="18" /><span>Dashboard Donatur</span>
                </a>
                <a href="{{ route('donor.donations.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'donations' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'donations') aria-current="page" @endif>
                    <x-icon name="file-text" class="nav-icon" size="18" /><span>Donasi Saya / Riwayat</span>
                </a>
                <a href="{{ route('donor.monitoring.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'monitoring' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'monitoring') aria-current="page" @endif>
                    <x-icon name="clock" class="nav-icon" size="18" /><span>Monitoring Proyek</span>
                </a>
                <a href="{{ route('donor.finance') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'finance' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'finance') aria-current="page" @endif>
                    <x-icon name="chart" class="nav-icon" size="18" /><span>Laporan Keuangan</span><span class="nav-external" aria-label="halaman publik"><x-icon name="arrow-up-right" size="14" /></span>
                </a>
                <a href="{{ route('donor.ledger.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'ledger' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'ledger') aria-current="page" @endif>
                    <x-icon name="receipt" class="nav-icon" size="18" /><span>Ledger &amp; Preview Kuitansi</span>
                </a>
                <a href="{{ route('donor.audit.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'audit' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'audit') aria-current="page" @endif>
                    <x-icon name="shield-check" class="nav-icon" size="18" /><span>Klarifikasi Audit</span>
                </a>
            </nav>
            <div class="donor-sidebar-label donor-sidebar-label-bottom">AKUN</div>
            <nav class="donor-nav" aria-label="Menu akun Donatur">
                <a href="{{ route('donor.profile') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'profile' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'profile') aria-current="page" @endif>
                    <x-icon name="user" class="nav-icon" size="18" /><span>Profil &amp; Notifikasi</span>
                    @if($layoutUnreadCount > 0)<span class="nav-count">{{ $layoutUnreadCount }}</span>@endif
                </a>
                <a href="{{ route('home') }}" class="donor-nav-link"><x-icon name="home" class="nav-icon" size="18" /><span>Kembali ke Website</span></a>
            </nav>
            <div class="donor-sidebar-note"><strong>Transparansi dimulai dari informasi.</strong><p>Periksa status donasi, milestone, dan laporan penggunaan dana dari satu tempat.</p></div>
            <form method="POST" action="{{ route('logout') }}" class="donor-logout-form">@csrf<button type="submit" class="donor-logout"><x-icon name="log-out" size="17" /> Keluar dari akun</button></form>
        </aside>

        <div class="donor-main-column">
            <header class="donor-topbar">
                <button class="donor-mobile-toggle" type="button" aria-label="Buka menu dashboard" aria-expanded="false" aria-controls="donor-sidebar" data-donor-menu-toggle><x-icon name="menu" size="21" /></button>
                <div class="donor-topbar-context"><span class="donor-topbar-kicker">RESFUND / AREA DONATUR</span><strong>@yield('page-heading', 'Dashboard Donatur')</strong></div>
                <div class="donor-topbar-actions">
                    <a href="{{ route('donor.profile') }}" class="donor-user-chip"><span class="donor-avatar" aria-hidden="true">{{ strtoupper(substr($user['name'] ?? 'D', 0, 1)) }}</span><span><strong>{{ $user['name'] ?? 'Donatur' }}</strong><small>Donatur</small></span></a>
                </div>
            </header>

            @if(session('success') || session('info'))
                <div class="donor-flash-wrap" role="status" aria-live="polite">
                    <div class="flash-message {{ session('info') ? 'flash-info' : 'flash-success' }}"><span class="flash-icon" aria-hidden="true">@if(session('info'))<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5M12 7.5h.01"></path></svg>@else<x-icon name="check" size="15" />@endif</span><p>{{ session('success') ?? session('info') }}</p><button type="button" class="flash-close" aria-label="Tutup pemberitahuan" data-dismiss-alert><x-icon name="x" size="17" /></button></div>
                </div>
            @endif
            @if($errors->any())
                <div class="donor-flash-wrap" role="alert" aria-live="assertive"><div class="form-alert form-alert-error"><strong>Periksa kembali data yang dimasukkan:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
            @endif

            <main id="donor-main" class="donor-main-content">
                @yield('donor-content')
            </main>
            <footer class="donor-footer"><span>ResFund · Research Crowdfunding &amp; Innovation System</span><span>Area Donatur · Research for Impact</span></footer>
        </div>
    </div>
    <div class="donor-mobile-backdrop" data-donor-backdrop hidden></div>
</body>
</html>
