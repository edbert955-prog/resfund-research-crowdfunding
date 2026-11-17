<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173B73">
    <meta name="description" content="ResFund - platform crowdfunding proyek riset akademik dan inovasi.">
    <title>@yield('title', 'ResFund') | Research for Impact</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <script src="{{ asset('assets/js/app.js') }}" defer></script>
    @stack('head')
</head>
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>
    <x-public-header />
    @if(session('success') || session('info'))
        <div class="container flash-container" role="status" aria-live="polite">
            <div class="flash-message {{ session('info') ? 'flash-info' : 'flash-success' }}">
                <span class="flash-icon" aria-hidden="true">{{ session('info') ? 'i' : '✓' }}</span>
                <p>{{ session('success') ?? session('info') }}</p>
                <button type="button" class="flash-close" aria-label="Tutup pemberitahuan" data-dismiss-alert>×</button>
            </div>
        </div>
    @endif
    <main id="main-content">
        @yield('content')
    </main>
    <x-site-footer />
</body>
</html>
