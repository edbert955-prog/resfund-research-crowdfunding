@extends('layouts.reviewer')
@section('title', 'Riwayat Keputusan')
@section('page-heading', 'Riwayat Keputusan')
@section('reviewer-content')
<div class="reviewer-page">
    <div class="reviewer-page-heading compact-heading">
        <div>
            <span class="reviewer-eyebrow">ARSIP EVALUASI PRIBADI</span>
            <h1>Riwayat Keputusan</h1>
            <p>Daftar keputusan dan deklarasi konflik yang pernah Anda kirim. Riwayat ini hanya menampilkan aktivitas akun Reviewer yang sedang masuk.</p>
        </div>
        <div class="reviewer-count-chip"><strong>{{ count($history) }}</strong><span>catatan ditampilkan</span></div>
    </div>

    <section class="reviewer-panel reviewer-filter-panel">
        <form method="GET" action="{{ route('reviewer.decisions.index') }}" class="reviewer-filter-form reviewer-history-filter">
            <div class="reviewer-search-field">
                <label class="sr-only" for="decision-search">Cari riwayat keputusan</label>
                <x-icon name="search" size="18" />
                <input id="decision-search" type="search" name="q" value="{{ $query }}" placeholder="Cari proyek, peneliti, atau milestone...">
            </div>
            <div>
                <label class="sr-only" for="decision-status">Filter keputusan</label>
                <select id="decision-status" name="status">
                    <option value="">Semua keputusan</option>
                    <option value="approved" @selected($status === 'approved')>Disetujui</option>
                    <option value="revision_minor" @selected($status === 'revision_minor')>Revisi Minor</option>
                    <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
                    <option value="conflict" @selected($status === 'conflict')>Konflik kepentingan</option>
                </select>
            </div>
            <button class="button button-primary" type="submit">Terapkan</button>
            @if($query !== '' || $status !== '')
                <a href="{{ route('reviewer.decisions.index') }}" class="button button-quiet">Reset</a>
            @endif
        </form>
    </section>

    <section class="reviewer-panel reviewer-history-panel">
        <div class="reviewer-panel-heading">
            <div><span class="reviewer-eyebrow">CATATAN TERSIMPAN</span><h2>Keputusan yang pernah dikirim</h2><p>Keputusan yang sudah dikirim bersifat terkunci dan tidak dapat diubah secara sepihak.</p></div>
        </div>

        @forelse($history as $item)
            @php
                $decisionClass = match($item['decision_key']) {
                    'approved' => 'status-success',
                    'revision_minor' => 'status-warning',
                    'rejected', 'conflict' => 'status-danger',
                    default => 'status-pending',
                };
            @endphp
            <article class="reviewer-history-card">
                <div class="reviewer-history-main">
                    <div class="reviewer-assignment-topline">
                        <span class="reviewer-category">{{ $item['milestone_name'] }}</span>
                        <span class="reviewer-status {{ $decisionClass }}">{{ $item['decision_label'] }}</span>
                    </div>
                    <h3>{{ $item['project_title'] }}</h3>
                    <p class="reviewer-assignment-milestone">Peneliti: {{ $item['researcher_name'] }} · Milestone {{ $item['milestone_number'] }}</p>
                    <div class="reviewer-history-meta">
                        <span><small>Tanggal dikirim</small><strong>{{ $item['submitted_at'] }}</strong></span>
                        <span><small>Rata-rata skor rubrik</small><strong>{{ $item['average_score'] !== null ? number_format($item['average_score'], 1, ',', '.') . ' / 5' : 'Tidak berlaku' }}</strong></span>
                        <span><small>Status panel</small><strong>{{ $item['panel_label'] }}</strong></span>
                    </div>
                    @if($item['general_comment'])
                        <div class="reviewer-history-comment"><small>Catatan umum Anda</small><p>{{ $item['general_comment'] }}</p></div>
                    @elseif($item['conflict_reason'])
                        <div class="reviewer-history-comment"><small>Alasan konflik yang Anda laporkan</small><p>{{ $item['conflict_reason'] }}</p></div>
                    @endif
                    @if($item['decision_notes'])
                        <div class="reviewer-history-comment reviewer-history-comment-note"><small>Alasan revisi / penolakan</small><p>{{ $item['decision_notes'] }}</p></div>
                    @endif
                </div>
                <div class="reviewer-history-action">
                    <a href="{{ route('reviewer.assignments.show', ['assignmentId' => $item['assignment_id']]) }}" class="button button-outline">Lihat penugasan <x-icon name="arrow-right" size="16" /></a>
                    <span class="reviewer-locked-label"><x-icon name="check-circle" size="18" /> Tersimpan dan terkunci</span>
                </div>
            </article>
        @empty
            <div class="reviewer-empty-state reviewer-history-empty">
                <span class="reviewer-empty-icon"><x-icon name="check-circle" size="24" /></span>
                <h3>{{ $query !== '' || $status !== '' ? 'Riwayat tidak ditemukan' : 'Belum ada keputusan' }}</h3>
                <p>{{ $query !== '' || $status !== '' ? 'Coba ubah kata kunci atau filter. Riwayat keputusan yang sesuai akan muncul di sini.' : 'Keputusan yang Anda kirim setelah meninjau bukti akan tercatat otomatis di halaman ini.' }}</p>
                @if($query !== '' || $status !== '')
                    <a href="{{ route('reviewer.decisions.index') }}" class="button button-outline">Tampilkan semua riwayat</a>
                @else
                    <a href="{{ route('reviewer.assignments.index') }}" class="button button-primary">Buka penugasan saya</a>
                @endif
            </div>
        @endforelse
    </section>
</div>
@endsection
