@php($prototypeUser = session('prototype_user'))
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="ResFund, kembali ke beranda">
            <img class="brand-logo" src="{{ asset('assets/images/resfund-logo.png') }}" alt="ResFund — Research for Impact" width="220" height="74">
        </a>

        <button class="mobile-menu-button" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="public-navigation" data-mobile-toggle>
            <x-icon name="menu" size="22" class="menu-icon-svg" />
        </button>

        <nav class="public-nav" id="public-navigation" aria-label="Navigasi utama" data-mobile-menu>
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
            <a href="{{ route('projects.index') }}" @if(request()->routeIs('projects.*')) aria-current="page" @endif>Jelajahi Proyek</a>
            <a href="{{ route('how-it-works') }}" @if(request()->routeIs('how-it-works')) aria-current="page" @endif>Cara Kerja</a>
            <a href="{{ route('finance.public') }}" @if(request()->routeIs('finance.public')) aria-current="page" @endif>Transparansi</a>
            @if($prototypeUser)
                <span class="nav-divider" aria-hidden="true"></span>
                @switch($prototypeUser['role'] ?? '')
                    @case('donatur')
                        <a class="nav-role-dashboard" href="{{ route('donor.dashboard') }}">Dashboard Donatur</a>
                        @break
                    @case('peneliti')
                        <a class="nav-role-dashboard" href="{{ route('researcher.dashboard') }}">Dashboard Peneliti</a>
                        @break
                    @case('reviewer')
                        <a class="nav-role-dashboard" href="{{ route('reviewer.dashboard') }}">Ruang Reviewer</a>
                        @break
                    @case('administrator')
                        <a class="nav-role-dashboard" href="{{ route('administrator.dashboard') }}">Dashboard Admin</a>
                        @break
                @endswitch
                <span class="nav-user" title="{{ $prototypeUser['name'] }}">
                    <span class="avatar-small" aria-hidden="true">{{ substr($prototypeUser['name'], 0, 1) }}</span>
                    <span class="nav-user-copy">Halo, {{ $prototypeUser['name'] }}<small class="nav-role-label">{{ $prototypeUser['role_label'] ?? 'Pengguna' }}@if(($prototypeUser['verification_status'] ?? '') === 'menunggu_verifikasi') · menunggu verifikasi @endif</small></span>
                </span>
                <form action="{{ route('logout') }}" method="POST" class="nav-logout-form">
                    @csrf
                    <button class="button button-outline button-small" type="submit">Keluar</button>
                </form>
            @else
                <span class="nav-divider" aria-hidden="true"></span>
                <a class="nav-auth-button {{ request()->routeIs('login') ? 'is-active' : '' }}" href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>Masuk</a>
                <a class="nav-auth-button {{ request()->routeIs('register') ? 'is-active' : '' }}" href="{{ route('register') }}" @if(request()->routeIs('register')) aria-current="page" @endif>Daftar</a>
            @endif
        </nav>
    </div>
</header>
