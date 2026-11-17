<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173B73">
    <meta name="description" content="ResFund - platform crowdfunding proyek riset akademik dan inovasi.">
    <title>@yield('title', 'ResFund') | Research for Impact</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v=1.5.4">
    <link rel="stylesheet" href="{{ asset('assets/css/motion.css') }}?v=1.5.4">
    <script src="{{ asset('assets/js/app.js') }}?v=1.5.4" defer></script>
    <script src="{{ asset('assets/js/motion.js') }}?v=1.5.4" defer></script>
    @stack('head')
</head>
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>
    <x-public-header />
    @if(session('success') || session('info'))
        <div class="container flash-container" role="status" aria-live="polite">
            <div class="flash-message {{ session('info') ? 'flash-info' : 'flash-success' }}">
                <span class="flash-icon" aria-hidden="true">@if(session('info'))<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5M12 7.5h.01"></path></svg>@else<x-icon name="check" size="15" />@endif</span>
                <p>{{ session('success') ?? session('info') }}</p>
                <button type="button" class="flash-close" aria-label="Tutup pemberitahuan" data-dismiss-alert><x-icon name="x" size="17" /></button>
            </div>
        </div>
    @endif
    <main id="main-content">
        @yield('content')
    </main>
    <x-site-footer />
</body>
</html>
