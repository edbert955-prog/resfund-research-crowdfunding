<!DOCTYPE html>
@php($layoutUser = $user ?? session('prototype_user', []))
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173B73">
    <meta name="description" content="Ruang Reviewer ResFund untuk meninjau bukti milestone dan memberikan evaluasi independen.">
    <title>@yield('title', 'Ruang Reviewer') | ResFund</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/motion.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/donor-dashboard.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/reviewer-dashboard.css') }}?v=1.5.4">
    <script src="{{ asset('assets/js/app.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/motion.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/donor-dashboard.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/reviewer-dashboard.js') }}?v=1.5.4" defer></script>
    @stack('head')
</head>
<body class="donor-app-body reviewer-app-body">
    <a class="skip-link" href="#reviewer-main">Lewati ke konten utama</a>
    <div class="donor-app-shell reviewer-app-shell">
        <aside class="donor-sidebar reviewer-sidebar" id="donor-sidebar" aria-label="Navigasi Ruang Reviewer">
            <div class="donor-sidebar-brand">
                <a class="brand" href="{{ route('home') }}" aria-label="ResFund, kembali ke area publik"><img class="sidebar-brand-logo" src="{{ asset('assets/images/resfund-logo-white.png') }}" alt="ResFund — Research for Impact" width="220" height="74"></a>
                <span class="donor-role-chip">Area Reviewer</span>
            </div>
            <div class="donor-sidebar-label">PENILAIAN RISET</div>
            <nav class="donor-nav" aria-label="Menu utama Reviewer">
                <a href="{{ route('reviewer.dashboard') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'dashboard' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'dashboard') aria-current="page" @endif>
                    <x-icon name="dashboard" class="nav-icon" size="18" /><span>Dashboard Reviewer</span>
                </a>
                <a href="{{ route('reviewer.assignments.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'assignments' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'assignments') aria-current="page" @endif>
                    <x-icon name="file-text" class="nav-icon" size="18" /><span>Penugasan Saya</span>
                </a>
                <a href="{{ route('reviewer.decisions.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'decisions' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'decisions') aria-current="page" @endif>
                    <x-icon name="check" class="nav-icon" size="18" /><span>Riwayat Keputusan</span>
                </a>
            </nav>
            <div class="donor-sidebar-label donor-sidebar-label-bottom">AKUN</div>
            <nav class="donor-nav" aria-label="Menu akun Reviewer">
                <a href="{{ route('reviewer.profile') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'profile' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'profile') aria-current="page" @endif>
                    <x-icon name="user" class="nav-icon" size="18" /><span>Profil &amp; Notifikasi</span>
                </a>
                <a href="{{ route('home') }}" class="donor-nav-link"><x-icon name="home" class="nav-icon" size="18" /><span>Kembali ke Website</span></a>
            </nav>
            <div class="donor-sidebar-note"><strong>Penilaian yang independen.</strong><p>Tinjau bukti secara objektif, berikan catatan yang dapat ditindaklanjuti, dan jaga kerahasiaan dokumen riset.</p></div>
            <form method="POST" action="{{ route('logout') }}" class="donor-logout-form">@csrf<button type="submit" class="donor-logout"><x-icon name="log-out" size="17" /> Keluar dari akun</button></form>
        </aside>

        <div class="donor-main-column">
            <header class="donor-topbar reviewer-topbar">
                <button class="donor-mobile-toggle" type="button" aria-label="Buka menu Reviewer" aria-expanded="false" aria-controls="donor-sidebar" data-donor-menu-toggle><x-icon name="menu" size="21" /></button>
                <div class="donor-topbar-context"><span class="donor-topbar-kicker">RESFUND / RUANG REVIEWER</span><strong>@yield('page-heading', 'Dashboard Reviewer')</strong></div>
                <div class="donor-topbar-actions">
                    <div class="donor-user-chip reviewer-user-chip"><span class="donor-avatar" aria-hidden="true">{{ strtoupper(substr(preg_replace('/^(Dr\.|Prof\.|Ir\.)\s*/i', '', $layoutUser['name'] ?? 'R'), 0, 1)) }}</span><span><strong>{{ $layoutUser['name'] ?? 'Reviewer' }}</strong><small>Reviewer Independen</small></span></div>
                </div>
            </header>

            @if(session('success') || session('info'))
                <div class="donor-flash-wrap" role="status" aria-live="polite"><div class="flash-message {{ session('info') ? 'flash-info' : 'flash-success' }}"><span class="flash-icon" aria-hidden="true">@if(session('info'))<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5M12 7.5h.01"></path></svg>@else<x-icon name="check" size="15" />@endif</span><p>{{ session('success') ?? session('info') }}</p><button type="button" class="flash-close" aria-label="Tutup pemberitahuan" data-dismiss-alert><x-icon name="x" size="17" /></button></div></div>
            @endif
            @if($errors->any())
                <div class="donor-flash-wrap" role="alert" aria-live="assertive"><div class="form-alert form-alert-error"><strong>Periksa kembali data penilaian:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
            @endif

            <main id="reviewer-main" class="donor-main-content reviewer-main-content">@yield('reviewer-content')</main>
            <footer class="donor-footer"><span>ResFund · Research Crowdfunding &amp; Innovation System</span><span>Ruang Reviewer · Penilaian Independen</span></footer>
        </div>
    </div>
    <div class="donor-mobile-backdrop" data-donor-backdrop hidden></div>
</body>
</html>
