@php($prototypeUser = session('prototype_user'))
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="ResFund, kembali ke beranda">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 40 40" role="img" aria-hidden="true"><path d="M20 4.5 34.5 12v16L20 35.5 5.5 28V12L20 4.5Z" fill="currentColor" opacity=".12"/><path d="M12 25.5c-2.2-1.3-3.1-4-2-6.3.8-1.6 2.3-2.4 4-2.4.2-3.2 2.7-5.4 5.7-5.4 3.1 0 5.5 2.4 5.5 5.4 2.6.1 4.8 2.2 4.8 4.9 0 2.2-1.4 4-3.4 4.7v3H13.2v-3.9Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M20 12v16m-5.2-9.4 5.2 3m5.2-3-5.2 3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M16 31h8m-6 3h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            <span class="brand-copy"><strong>ResFund</strong><small>Research for Impact</small></span>
        </a>

        <button class="mobile-menu-button" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="public-navigation" data-mobile-toggle>
            <span class="menu-icon" aria-hidden="true"><span></span><span></span><span></span></span>
        </button>

        <nav class="public-nav" id="public-navigation" aria-label="Navigasi utama" data-mobile-menu>
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
            <a href="{{ route('projects.index') }}" @if(request()->routeIs('projects.*')) aria-current="page" @endif>Jelajahi Proyek</a>
            <a href="{{ route('how-it-works') }}" @if(request()->routeIs('how-it-works')) aria-current="page" @endif>Cara Kerja</a>
            <a href="{{ route('finance.public') }}" @if(request()->routeIs('finance.public')) aria-current="page" @endif>Transparansi</a>
            <span class="nav-divider" aria-hidden="true"></span>
            @if($prototypeUser)
                @if(($prototypeUser['role'] ?? '') === 'donatur')<a class="nav-login" href="{{ route('donor.dashboard') }}">Dashboard Donatur</a>@endif
                <span class="nav-user"><span class="avatar-small" aria-hidden="true">{{ substr($prototypeUser['name'], 0, 1) }}</span><span>Halo, {{ $prototypeUser['name'] }}<small class="nav-role-label">{{ $prototypeUser['role_label'] ?? 'Donatur' }}@if(($prototypeUser['verification_status'] ?? '') === 'menunggu_verifikasi') · menunggu verifikasi @endif</small></span></span>
                <form action="{{ route('logout') }}" method="POST" class="nav-logout-form">
                    @csrf
                    <button class="button button-outline button-small" type="submit">Keluar</button>
                </form>
            @else
                <a class="nav-login" href="{{ route('login') }}">Masuk</a>
                <a class="button button-primary button-small" href="{{ route('register') }}">Daftar</a>
            @endif
        </nav>
    </div>
</header>
