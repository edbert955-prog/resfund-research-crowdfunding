@extends('layouts.donor')
@section('title', 'Monitoring Proyek')
@section('page-heading', 'Monitoring Proyek')
@section('donor-content')
@include('donor.partials.page-intro', ['pageHeading' => 'Monitoring Proyek', 'breadcrumb' => 'Monitoring Proyek', 'eyebrow' => 'S09 · PERKEMBANGAN RISET', 'pageDescription' => 'Pantau target kampanye, tahapan penelitian, dan status milestone dari proyek yang pernah Anda dukung.'])
@if(count($projects))
<div class="donor-monitoring-grid">
@foreach($projects as $project)
    <article class="donor-panel monitoring-card">
        <div class="monitoring-card-top"><span class="category-pill category-pill-static">{{ $project['category'] }}</span><span class="status-pill campaign-{{ strtolower($project['campaign_status']) }}">{{ $project['campaign_status_label'] }}</span></div>
        <h2>{{ $project['project_title'] }}</h2><p class="muted-text">{{ $project['researcher'] }}</p>
        <div class="monitoring-progress-copy"><strong>{{ $project['campaign_progress'] }}%</strong><span>progres pendanaan</span></div>
        <div class="progress-track progress-track-large" role="progressbar" aria-label="Progres pendanaan {{ $project['project_title'] }}" aria-valuenow="{{ $project['campaign_progress'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $project['campaign_progress'] }}%"></span></div>
        <div class="monitoring-meta"><span>Donasi Anda<strong>Rp{{ number_format($project['amount'], 0, ',', '.') }}</strong></span><span>Batas kampanye<strong>{{ $project['deadline'] }}</strong></span></div>
        @if($project['payment_status'] === 'REFUNDED')<div class="monitoring-refund-note">Donasi telah dikembalikan. Proyek tidak melanjutkan milestone melalui pendanaan kampanye ini.</div>@endif
        <a class="button button-primary button-full" href="{{ route('donor.monitoring.show', ['slug' => $project['project_slug']]) }}">Lihat perkembangan proyek</a>
    </article>
@endforeach
</div>
@else
<div class="donor-panel donor-empty-state"><span class="empty-symbol" aria-hidden="true">◷</span><h3>Belum ada proyek untuk dipantau</h3><p>Proyek akan muncul setelah Anda memiliki donasi yang berhasil dikonfirmasi.</p><a href="{{ route('projects.index') }}" class="button button-primary">Jelajahi proyek</a></div>
@endif
<div class="donor-info-callout"><strong>Perlu dibedakan:</strong> status kampanye (ACTIVE/FUNDED/FAILED), status milestone (PENDING/SUBMITTED/UNDER_REVIEW/APPROVED/DISBURSED), dan status pembayaran donasi merupakan informasi yang berbeda.</div>
@endsection
