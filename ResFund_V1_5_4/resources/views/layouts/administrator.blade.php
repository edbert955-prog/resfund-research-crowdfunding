<!DOCTYPE html>
@php($layoutUser = $user ?? session('prototype_user', []))
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173B73">
    <meta name="description" content="Area Administrator ResFund untuk mengelola pengguna, validasi peneliti, reviewer, penugasan, dan audit log.">
    <title>@yield('title', 'Area Administrator') | ResFund</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/motion.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/donor-dashboard.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}?v=1.5.4">
    <script src="{{ asset('assets/js/app.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/motion.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/donor-dashboard.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/admin-dashboard.js') }}?v=1.5.4" defer></script>
    @stack('head')
</head>
<body class="donor-app-body admin-app-body">
<a class="skip-link" href="#admin-main">Lewati ke konten utama</a>
<div class="donor-app-shell admin-app-shell">
    <aside class="donor-sidebar admin-sidebar" id="donor-sidebar" aria-label="Navigasi Area Administrator">
        <div class="donor-sidebar-brand">
            <a class="brand" href="{{ route('home') }}" aria-label="ResFund, kembali ke area publik"><img class="sidebar-brand-logo" src="{{ asset('assets/images/resfund-logo-white.png') }}" alt="ResFund — Research for Impact" width="220" height="74"></a>
            <span class="donor-role-chip">Area Administrator</span>
        </div>
        <div class="donor-sidebar-label">PENGELOLAAN SISTEM</div>
        <nav class="donor-nav admin-nav" aria-label="Menu utama Administrator">
            <a href="{{ route('administrator.dashboard') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'dashboard' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'dashboard') aria-current="page" @endif><x-icon name="dashboard" class="nav-icon" size="18" /><span>Dashboard Admin</span></a>
            <a href="{{ route('administrator.users.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'users' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'users') aria-current="page" @endif><x-icon name="file-text" class="nav-icon" size="18" /><span>Manajemen Pengguna</span></a>
            <a href="{{ route('administrator.researchers.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'researchers' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'researchers') aria-current="page" @endif><x-icon name="check" class="nav-icon" size="18" /><span>Validasi Peneliti</span></a>
            <a href="{{ route('administrator.reviewers.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'reviewers' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'reviewers') aria-current="page" @endif><x-icon name="user" class="nav-icon" size="18" /><span>Manajemen Reviewer</span></a>
            <a href="{{ route('administrator.assignments.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'assignments' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'assignments') aria-current="page" @endif><x-icon name="arrow-left-right" class="nav-icon" size="18" /><span>Penugasan Reviewer</span></a>
            <a href="{{ route('administrator.monitoring.index') }}" class="donor-nav-link {{ ($activeMenu ?? '') === 'monitoring' ? 'is-active' : '' }}" @if(($activeMenu ?? '') === 'monitoring') aria-current="page" @endif><x-icon name="receipt" class="nav-icon" size="18" /><span>Monitoring &amp; Audit Log</span></a>
        </nav>
        <div class="donor-sidebar-label donor-sidebar-label-bottom">AKUN</div>
        <nav class="donor-nav" aria-label="Navigasi akun Administrator">
            <a href="{{ route('home') }}" class="donor-nav-link"><x-icon name="home" class="nav-icon" size="18" /><span>Kembali ke Website</span></a>
        </nav>
        <div class="donor-sidebar-note"><strong>Administrasi yang akuntabel.</strong><p>Kelola akses, validasi identitas, penugasan independen, dan pantau jejak aktivitas sistem.</p></div>
        <form method="POST" action="{{ route('logout') }}" class="donor-logout-form">@csrf<button type="submit" class="donor-logout"><x-icon name="log-out" size="17" /> Keluar dari akun</button></form>
    </aside>
    <div class="donor-main-column">
        <header class="donor-topbar admin-topbar">
            <button class="donor-mobile-toggle" type="button" aria-label="Buka menu Administrator" aria-expanded="false" aria-controls="donor-sidebar" data-donor-menu-toggle><x-icon name="menu" size="21" /></button>
            <div class="donor-topbar-context"><span class="donor-topbar-kicker">RESFUND / AREA ADMINISTRATOR</span><strong>@yield('page-heading', 'Dashboard Admin')</strong></div>
            <div class="donor-topbar-actions"><div class="donor-user-chip admin-user-chip"><span class="donor-avatar" aria-hidden="true">A</span><span><strong>{{ $layoutUser['name'] ?? 'Administrator' }}</strong><small>Administrator</small></span></div></div>
        </header>
        @if(session('success') || session('info'))
            <div class="donor-flash-wrap" role="status" aria-live="polite"><div class="flash-message {{ session('info') ? 'flash-info' : 'flash-success' }}"><span class="flash-icon" aria-hidden="true">@if(session('info'))<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 10.8v5.1M12 7.8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>@else<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12.5 4.2 4.1L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>@endif</span><p>{{ session('success') ?? session('info') }}</p><button type="button" class="flash-close" aria-label="Tutup pemberitahuan" data-dismiss-alert><x-icon name="x" size="17" /></button></div></div>
        @endif
        @if($errors->any())
            <div class="donor-flash-wrap" role="alert" aria-live="assertive"><div class="form-alert form-alert-error"><strong>Periksa kembali data yang dimasukkan:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
        @endif
        <main id="admin-main" class="donor-main-content admin-main-content">@yield('admin-content')</main>
        <footer class="donor-footer"><span>ResFund · Research Crowdfunding &amp; Innovation System</span><span>Area Administrator · Akses terbatas</span></footer>
    </div>
</div>
<div class="donor-mobile-backdrop" data-donor-backdrop hidden></div>
</body>
</html>
