@extends('layouts.app')
@section('title', 'Detail Proyek')
@php($projectImage = str_starts_with($project['image'], 'http') ? $project['image'] : asset($project['image']))
@php($progress = $project['target'] > 0 ? min(100, (int) round(($project['collected'] / $project['target']) * 100)) : 0)
@section('content')
<section class="page-hero page-hero-compact"><div class="container"><div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><a href="{{ route('projects.index') }}">Jelajahi Proyek</a><span>/</span><span aria-current="page">Detail proyek</span></div><span class="eyebrow">Informasi penelitian</span><h1>Detail proyek riset</h1></div></section>
<section class="section detail-section"><div class="container">
    <div class="detail-layout">
        <div class="detail-main">
            <div class="detail-cover"><img src="{{ $projectImage }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/project-battery-photo.jpg') }}'" alt="Foto ilustratif penelitian {{ $project['title'] }}"></div>
            <div class="detail-title-row"><span class="category-pill category-pill-static">{{ $project['category_label'] }}</span><span class="status-label {{ $project['status'] === 'FUNDED' ? 'status-funded' : ($project['status'] === 'FAILED' ? 'status-failed' : '') }}"><i></i>{{ $project['status_label'] }}</span></div>
            <h2 class="detail-title">{{ $project['title'] }}</h2>
            <div class="researcher-line"><span class="researcher-avatar" aria-hidden="true">{{ substr($project['researcher'], 0, 1) }}</span><span><strong>{{ $project['researcher'] }}</strong><small>{{ $project['institution'] }} · Profil peneliti pada data simulasi</small></span></div>
            <section class="detail-content-block"><h3>Tentang penelitian</h3><p>{{ $project['description'] }}</p></section>
            <section class="detail-content-block"><h3>Tujuan penelitian</h3><ul class="check-list">@foreach($project['objectives'] as $objective)<li><span aria-hidden="true">✓</span>{{ $objective }}</li>@endforeach</ul></section>
            <section class="detail-content-block milestone-block"><div class="section-title-inline"><div><h3>Tahapan penelitian</h3><p>Rencana pelaksanaan dibagi menjadi beberapa milestone.</p></div><span class="mini-tag">Total alokasi 100%</span></div>
                <div class="milestone-timeline">@foreach($project['milestones'] as $index => $milestone)<article class="milestone-item"><span class="milestone-marker" aria-hidden="true">{{ $index + 1 }}</span><div class="milestone-content"><div class="milestone-heading"><h4>{{ $milestone['name'] }}</h4><span class="allocation-pill">{{ $milestone['allocation'] }}%</span></div><p>{{ $milestone['description'] }}</p><span class="milestone-status">{{ $milestone['status'] }}</span></div></article>@endforeach</div>
            </section>
        </div>
        <aside class="detail-sidebar" aria-label="Ringkasan pendanaan proyek">
            <div class="funding-panel">
                <span class="panel-eyebrow">Ringkasan pendanaan</span>
                <div class="funding-total">Rp{{ number_format($project['collected'], 0, ',', '.') }}</div><div class="funding-target">dari target Rp{{ number_format($project['target'], 0, ',', '.') }}</div>
                <div class="progress-track progress-track-large" role="progressbar" aria-label="Progres pendanaan" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $progress }}%"></span></div>
                <div class="funding-foot"><strong>{{ $progress }}% tercapai</strong><span>{{ $project['status'] === 'FUNDED' ? 'Target terpenuhi' : ($project['status'] === 'FAILED' ? 'Kampanye berakhir' : $project['days_left'].' hari tersisa') }}</span></div>
                <div class="panel-divider"></div>
                <div class="funding-detail-row"><span>Status kampanye</span><strong>{{ $project['status'] }}</strong></div>
                <div class="funding-detail-row"><span>Batas kampanye</span><strong>{{ $project['deadline'] }}</strong></div>
                @if($project['status'] === 'ACTIVE')
                    <a href="{{ route('donation.entry', ['slug' => $project['slug']]) }}" class="button button-primary button-full button-large">Donasi sekarang <span aria-hidden="true">→</span></a>
                    <p class="panel-helper">Masuk atau daftar sebagai Donatur terlebih dahulu. Pembayaran dilakukan pada tahap checkout melalui Payment Gateway.</p>
                @elseif($project['status'] === 'FUNDED')
                    <a href="{{ route('finance.public', ['slug' => $project['slug']]) }}" class="button button-primary button-full">Lihat ringkasan keuangan</a>
                @else
                    <div class="form-alert form-alert-info" role="status">Kampanye telah berakhir dan target pendanaan tidak tercapai. Donasi yang berhasil akan dikembalikan sesuai mekanisme ResFund.</div>
                @endif
                <div class="escrow-note"><span aria-hidden="true">↔</span><p>Dana donasi yang berhasil dibayarkan dicatat pada rekening escrow dan dikelola sesuai mekanisme ResFund.</p></div>
            </div>
            <div class="detail-side-note"><strong>Informasi transparansi</strong><p>Setelah proyek memperoleh pendanaan, perkembangan dan ringkasan penggunaan dana dapat dipantau melalui informasi yang dipublikasikan.</p><a href="{{ route('finance.public') }}" class="text-link">Lihat contoh laporan publik →</a></div>
        </aside>
    </div>
    <div class="prototype-disclaimer"><strong>Catatan prototype:</strong> checkout dan halaman Payment Gateway tersedia untuk simulasi alur donasi. Tidak ada transaksi uang sungguhan yang diproses.</div>
</div></section>
@endsection
