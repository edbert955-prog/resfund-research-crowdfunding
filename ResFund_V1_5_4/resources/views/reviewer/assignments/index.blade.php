@extends('layouts.reviewer')
@section('title', 'Penugasan Saya')
@section('page-heading', 'Penugasan Saya')
@section('reviewer-content')
<div class="reviewer-page">
    <div class="reviewer-page-heading compact-heading">
        <div><span class="reviewer-eyebrow">DAFTAR PENUGASAN</span><h1>Penugasan Saya</h1><p>Semua proposal dan milestone yang ditugaskan kepada Anda untuk ditelaah.</p></div>
        <div class="reviewer-count-chip"><strong>{{ count($assignments) }}</strong><span>penugasan ditampilkan</span></div>
    </div>

    <section class="reviewer-panel reviewer-filter-panel">
        <form method="GET" action="{{ route('reviewer.assignments.index') }}" class="reviewer-filter-form">
            <div class="reviewer-search-field"><label class="sr-only" for="reviewer-search">Cari penugasan</label><x-icon name="search" size="18" /><input id="reviewer-search" type="search" name="q" value="{{ $query }}" placeholder="Cari proyek, peneliti, atau milestone..."></div>
            <div><label class="sr-only" for="reviewer-status">Filter status</label><select id="reviewer-status" name="status"><option value="">Semua status saya</option><option value="pending" @selected($status === 'pending')>Perlu ditinjau</option><option value="completed" @selected($status === 'completed')>Sudah dinilai</option><option value="conflict" @selected($status === 'conflict')>Konflik kepentingan</option></select></div>
            <button class="button button-primary" type="submit">Terapkan</button>
            @if($query !== '' || $status !== '')<a href="{{ route('reviewer.assignments.index') }}" class="button button-quiet">Reset</a>@endif
        </form>
    </section>

    <div class="reviewer-assignment-list">
        @forelse($assignments as $assignment)
            @php($myReview = \App\Support\ReviewerPrototypeData::reviewerDecision($assignment, $user['reviewer_id']))
            @php($myState = $myStatus($assignment))
            @php($left = $daysLeft($assignment['due_at'] ?? null))
            <article class="reviewer-list-card">
                <div class="reviewer-list-card-main">
                    <div class="reviewer-assignment-topline"><span class="reviewer-category">{{ $assignment['category'] }}</span>
                        @if($myState === 'completed')<span class="reviewer-status status-success">{{ $myReview['decision_label'] ?? 'Sudah dinilai' }}</span>@elseif($myState === 'conflict')<span class="reviewer-status status-danger">Konflik dilaporkan</span>@elseif($left < 0)<span class="reviewer-status status-danger">Terlambat</span>@elseif($left <= 2)<span class="reviewer-status status-warning">Tenggat dekat</span>@else<span class="reviewer-status status-info">Perlu ditinjau</span>@endif
                    </div>
                    <h2>{{ $assignment['project_title'] }}</h2>
                    <p class="reviewer-assignment-milestone">Milestone {{ $assignment['milestone_number'] }} · {{ $assignment['milestone_name'] }}</p>
                    <div class="reviewer-list-meta"><span><small>Peneliti</small><strong>{{ $assignment['researcher_name'] }}</strong></span><span><small>Nominal pengajuan</small><strong>Rp{{ number_format($assignment['requested_amount'], 0, ',', '.') }}</strong></span><span><small>Batas penilaian</small><strong>{{ \Carbon\Carbon::parse($assignment['due_at'])->translatedFormat('d F Y') }}</strong></span></div>
                    <div class="reviewer-reviewers-line"><strong>Panel reviewer:</strong>@foreach($assignment['assigned_reviewers'] as $reviewer)<span>{{ $reviewer['name'] }}@if($reviewer['id'] === $user['reviewer_id']) (Anda)@endif</span>@endforeach</div>
                </div>
                <div class="reviewer-list-action"><a href="{{ route('reviewer.assignments.show', ['assignmentId' => $assignment['id']]) }}" class="button {{ $myState === 'pending' ? 'button-primary' : 'button-outline' }}">{{ $myState === 'pending' ? 'Tinjau bukti' : 'Lihat penilaian' }} <x-icon name="arrow-right" size="16" /></a></div>
            </article>
        @empty
            <div class="reviewer-panel reviewer-empty-state"><span class="reviewer-empty-icon"><x-icon name="search" size="24" /></span><h3>Penugasan tidak ditemukan</h3><p>Coba ubah kata kunci atau filter status. Jika belum ada penugasan, Administrator akan menambahkannya sesuai bidang keahlian.</p><a href="{{ route('reviewer.assignments.index') }}" class="button button-outline">Hapus filter</a></div>
        @endforelse
    </div>
</div>
@endsection
