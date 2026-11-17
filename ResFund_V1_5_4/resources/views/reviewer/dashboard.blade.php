@extends('layouts.reviewer')
@section('title', 'Dashboard Reviewer')
@section('page-heading', 'Dashboard Reviewer')
@section('reviewer-content')
<div class="reviewer-page">
    <div class="reviewer-page-heading">
        <div>
            <span class="reviewer-eyebrow">RUANG PENILAIAN INDEPENDEN</span>
            <h1>Selamat datang, {{ $user['name'] }}.</h1>
            <p>Tinjau bukti capaian milestone dan berikan evaluasi secara objektif sesuai bidang keahlian Anda.</p>
        </div>
        <div class="reviewer-expertise-card">
            <span class="reviewer-expertise-icon"><x-icon name="flask" size="22" /></span>
            <span><small>Bidang keahlian</small><strong>{{ $user['expertise'] ?? 'Reviewer Independen' }}</strong><small>{{ $user['institution'] ?? 'Institusi Reviewer' }}</small></span>
        </div>
    </div>

    <section class="reviewer-kpi-grid" aria-label="Ringkasan penugasan">
        <article class="reviewer-kpi-card kpi-blue"><span class="reviewer-kpi-icon"><x-icon name="file-text" size="21" /></span><div><span>Perlu ditinjau</span><strong>{{ $pendingCount }}</strong><small>Penugasan menunggu keputusan Anda</small></div></article>
        <article class="reviewer-kpi-card kpi-green"><span class="reviewer-kpi-icon"><x-icon name="check-circle" size="21" /></span><div><span>Sudah dinilai</span><strong>{{ $completedCount }}</strong><small>Keputusan tersimpan dan terkunci</small></div></article>
        <article class="reviewer-kpi-card kpi-amber"><span class="reviewer-kpi-icon"><x-icon name="clock" size="21" /></span><div><span>Tenggat dekat</span><strong>{{ $dueSoonCount }}</strong><small>Sisa waktu paling banyak dua hari</small></div></article>
        <article class="reviewer-kpi-card kpi-purple"><span class="reviewer-kpi-icon"><x-icon name="shield" size="21" /></span><div><span>Konflik kepentingan</span><strong>{{ $conflictCount }}</strong><small>Menunggu penugasan ulang</small></div></article>
    </section>

    <div class="reviewer-content-grid">
        <section class="reviewer-panel reviewer-panel-main">
            <div class="reviewer-panel-heading">
                <div><span class="reviewer-eyebrow">ANTRIAN EVALUASI</span><h2>Penugasan yang perlu ditinjau</h2><p>Setiap pengajuan ditelaah secara independen oleh minimal dua reviewer.</p></div>
                <a href="{{ route('reviewer.assignments.index') }}" class="reviewer-text-link">Lihat semua penugasan <x-icon name="arrow-right" size="16" /></a>
            </div>

            @forelse($pendingAssignments as $assignment)
                @php($status = $myStatus($assignment))
                @php($left = $daysLeft($assignment['due_at'] ?? null))
                <article class="reviewer-assignment-card">
                    <div class="reviewer-assignment-topline">
                        <span class="reviewer-category">{{ $assignment['category'] }}</span>
                        @if($left < 0)<span class="reviewer-status status-danger">Terlambat</span>@elseif($left <= 2)<span class="reviewer-status status-warning">Tenggat dekat</span>@else<span class="reviewer-status status-info">Menunggu penilaian</span>@endif
                    </div>
                    <h3>{{ $assignment['project_title'] }}</h3>
                    <p class="reviewer-assignment-milestone">Milestone {{ $assignment['milestone_number'] }} · {{ $assignment['milestone_name'] }}</p>
                    <div class="reviewer-assignment-meta">
                        <span><small>Peneliti</small><strong>{{ $assignment['researcher_name'] }}</strong></span>
                        <span><small>Pengajuan pencairan</small><strong>Rp{{ number_format($assignment['requested_amount'], 0, ',', '.') }}</strong></span>
                        <span><small>Batas penilaian</small><strong>{{ \Carbon\Carbon::parse($assignment['due_at'])->translatedFormat('d F Y') }}</strong></span>
                    </div>
                    <div class="reviewer-assignment-footer">
                        <span class="reviewer-assigned-note">Dua reviewer ditugaskan · keputusan bersifat independen</span>
                        <a href="{{ route('reviewer.assignments.show', ['assignmentId' => $assignment['id']]) }}" class="button button-primary">Tinjau bukti <x-icon name="arrow-right" size="16" /></a>
                    </div>
                </article>
            @empty
                <div class="reviewer-empty-state"><span class="reviewer-empty-icon"><x-icon name="check-circle" size="24" /></span><h3>Tidak ada penugasan yang menunggu</h3><p>Semua penugasan yang tersedia sudah Anda nilai. Penugasan baru akan muncul setelah Administrator menugaskan Anda.</p></div>
            @endforelse
        </section>

        <aside class="reviewer-side-column">
            <section class="reviewer-panel reviewer-rule-panel">
                <span class="reviewer-eyebrow">PEDOMAN PENILAIAN</span>
                <h2>Penilaian yang akuntabel</h2>
                <ul class="reviewer-rule-list">
                    <li><span aria-hidden="true">01</span><p><strong>Periksa dokumen</strong><small>Baca bukti luaran teknis dan pertanggungjawaban keuangan.</small></p></li>
                    <li><span aria-hidden="true">02</span><p><strong>Isi rubrik terstruktur</strong><small>Beri skor pada relevansi, metodologi, bukti, dan anggaran.</small></p></li>
                    <li><span aria-hidden="true">03</span><p><strong>Berikan keputusan</strong><small>Disetujui, Revisi Minor, atau Ditolak beserta alasannya.</small></p></li>
                </ul>
                <div class="reviewer-rule-note"><strong>Dual approval</strong><p>Pencairan hanya dapat diteruskan jika seluruh reviewer yang ditugaskan memilih Disetujui. Reviewer tidak melakukan transfer dana.</p></div>
            </section>

            <section class="reviewer-panel reviewer-deadline-panel">
                <span class="reviewer-eyebrow">PENGINGAT</span>
                <h2>Batas waktu penilaian</h2>
                <p>Setiap reviewer diberi waktu maksimal tujuh hari kalender untuk membaca dan menilai dokumen penugasan.</p>
                <a href="{{ route('reviewer.assignments.index') }}" class="reviewer-secondary-link">Buka daftar penugasan <x-icon name="arrow-right" size="16" /></a>
            </section>
        </aside>
    </div>
</div>
@endsection
