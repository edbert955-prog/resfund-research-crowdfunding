@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Pendanaan riset yang lebih terbuka</span>
            <h1>Dukung penelitian.<br><span>Wujudkan inovasi.</span></h1>
            <p class="hero-lead">Temukan proyek riset akademik, dukung gagasan yang bermanfaat, dan ikuti perkembangan penggunaan dananya melalui satu platform.</p>
            <div class="hero-actions">
                <a href="{{ route('projects.index') }}" class="button button-primary button-large">Jelajahi proyek <x-icon name="arrow-right" size="16" /></a>
                <a href="{{ route('how-it-works') }}" class="button button-quiet button-large">Pelajari cara kerja</a>
            </div>
            <div class="hero-trust-row">
                <span class="trust-mark"><x-icon name="check-circle" size="18" /></span>
                <span>Proses pendanaan bertahap dan informasi keuangan yang dapat ditelusuri.</span>
            </div>
        </div>
        <div class="hero-visual hero-photo-visual" aria-label="Foto ilustratif peneliti sedang mengolah bahan biomassa di laboratorium" role="img">
            <img src="{{ asset('assets/images/project-battery-photo.jpg') }}" alt="Peneliti sedang memproses bahan biomassa dan menguji material di laboratorium" fetchpriority="high">
            <div class="hero-photo-shade"><span>RESEARCH FOR IMPACT</span><strong>Riset yang terbuka.<br>Inovasi yang berdampak.</strong></div>
            <div class="floating-note note-top"><span class="note-icon"><x-icon name="check-circle" size="18" /></span><span><strong>Transparansi</strong><small>Informasi dapat ditelusuri</small></span></div>
            <div class="floating-note note-bottom"><span class="note-icon note-icon-blue"><x-icon name="activity" size="18" /></span><span><strong>Milestone riset</strong><small>Progres bertahap</small></span></div>
        </div>
    </div>
</section>

<section class="quick-search-section">
    <div class="container">
        <form class="quick-search" action="{{ route('projects.index') }}" method="GET">
            <div class="search-field-wrap">
                <label for="home-search">Temukan proyek riset</label>
                <div class="search-input-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg><input id="home-search" type="search" name="q" placeholder="Cari topik atau judul penelitian..." autocomplete="off"></div>
            </div>
            <div class="search-category-wrap"><label for="home-category">Bidang penelitian</label><select id="home-category" name="kategori"><option value="">Semua bidang</option><option value="Energi">Energi</option><option value="Lingkungan">Lingkungan</option></select></div>
            <button class="button button-primary search-submit" type="submit">Cari proyek</button>
        </form>
    </div>
</section>

<section class="section section-projects" id="proyek-unggulan">
    <div class="container">
        <div class="section-heading-row">
            <div><span class="eyebrow">Pilih kontribusimu</span><h2>Proyek riset pilihan</h2><p>Kenali tujuan penelitian dan rencana pendanaannya sebelum memberikan dukungan.</p></div>
            <a class="text-link text-link-large" href="{{ route('projects.index') }}">Lihat semua proyek <x-icon name="arrow-right" size="16" /></a>
        </div>
        <div class="project-grid">
            @foreach($featuredProjects as $project)
                <x-project-card :project="$project" />
            @endforeach
        </div>
    </div>
</section>

<section class="how-section" id="cara-kerja">
    <div class="container">
        <div class="section-heading centered-heading"><span class="eyebrow">Sederhana dan terarah</span><h2>Bagaimana ResFund bekerja?</h2><p>Alur dukungan riset dirancang agar mudah dipahami sejak awal.</p></div>
        <div class="steps-grid">
            <article class="step-card"><span class="step-number">01</span><span class="step-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg></span><h3>Temukan proyek</h3><p>Jelajahi penelitian berdasarkan bidang dan tujuan yang menarik bagimu.</p></article>
            <article class="step-card"><span class="step-number">02</span><span class="step-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5h16v13H4zM8 9h8M8 12.5h5"/></svg></span><h3>Pahami rencana</h3><p>Baca profil peneliti, target pendanaan, dan tahapan milestone.</p></article>
            <article class="step-card"><span class="step-number">03</span><span class="step-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v18M17 7.5c0-1.7-2.2-3-5-3s-5 1.3-5 3 2.2 3 5 3 5 1.3 5 3-2.2 3-5 3-5-1.3-5-3"/></svg></span><h3>Berikan dukungan</h3><p>Pilih nominal dukungan dan selesaikan proses pembayaran yang tersedia.</p></article>
            <article class="step-card"><span class="step-number">04</span><span class="step-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16M7 15l4-4 3 2 5-6"/></svg></span><h3>Pantau perkembangan</h3><p>Ikuti informasi progres penelitian dan ringkasan penggunaan dana.</p></article>
        </div>
        <div class="steps-cta"><a href="{{ route('how-it-works') }}" class="button button-outline">Pelajari mekanisme ResFund</a></div>
    </div>
</section>

<section class="transparency-promo">
    <div class="container transparency-promo-inner">
        <div class="transparency-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M8 9h32v30H8zM15 17h18M15 24h18M15 31h10"/><path d="m31 31 3 3 6-7"/></svg></div>
        <div><span class="eyebrow">Terbuka dan dapat ditelusuri</span><h2>Kenali penggunaan dana penelitian.</h2><p>Lihat ringkasan anggaran dan realisasi pengeluaran dari proyek yang telah mencapai target pendanaan.</p></div>
        <a class="button button-light" href="{{ route('finance.public', ['slug' => $fundedProject['slug'] ?? 'bioplastik-pati-singkong']) }}">Lihat laporan publik <x-icon name="arrow-right" size="16" /></a>
    </div>
</section>

@endsection
