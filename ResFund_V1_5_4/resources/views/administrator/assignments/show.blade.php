@extends('layouts.administrator')
@section('title', 'Detail Penugasan Reviewer')
@section('page-heading', 'Detail Penugasan Reviewer')
@section('admin-content')
<div class="admin-page admin-narrow-page">
    <div class="admin-breadcrumb"><a href="{{ route('administrator.assignments.index') }}">Penugasan Reviewer</a><span>/</span><span>Kelola panel</span></div>
    <div class="admin-page-heading"><div><span class="admin-eyebrow">PENGATURAN PANEL</span><h1>Kelola penugasan reviewer</h1><p>Tinjau pengajuan dan tetapkan dua reviewer dari institusi berbeda.</p></div><a href="{{ route('administrator.assignments.index') }}" class="button button-secondary">← Kembali</a></div>
    <section class="admin-panel">
        <div class="admin-assignment-heading"><div><span class="admin-role role-peneliti">{{ $assignment['category'] }}</span><h2>{{ $assignment['project_title'] }}</h2><p>{{ $assignment['researcher_name'] }} · {{ $assignment['institution'] }}</p></div><span class="admin-status status-pending">{{ $assignment['aggregate']['label'] ?? 'Menunggu penilaian' }}</span></div>
        <dl class="admin-detail-grid"><div><dt>Milestone</dt><dd>{{ $assignment['milestone_number'] }} · {{ $assignment['milestone_name'] }}</dd></div><div><dt>Pengajuan pencairan</dt><dd>Rp{{ number_format($assignment['requested_amount'],0,',','.') }}</dd></div><div><dt>Alokasi milestone</dt><dd>{{ number_format($assignment['allocation'],1,',','.') }}%</dd></div><div><dt>Tanggal pengajuan</dt><dd>{{ $assignment['submitted_at'] }}</dd></div><div><dt>Batas evaluasi</dt><dd>{{ $assignment['due_at'] }}</dd></div><div><dt>ID pengajuan</dt><dd>{{ $assignment['id'] }}</dd></div></dl>
        <h3 class="admin-section-title">Panel saat ini</h3>
        <div class="admin-panel-members">
            @foreach($assignment['assigned_reviewers'] as $reviewer)
                @php($decision = $assignment['reviews'][$reviewer['id']] ?? [])
                <article class="admin-panel-member"><span class="admin-large-avatar">{{ strtoupper(substr($reviewer['name'],0,1)) }}</span><div><strong>{{ $reviewer['name'] }}</strong><small>{{ $reviewer['institution'] }} · {{ $reviewer['expertise'] }}</small><span class="admin-status {{ !empty($decision['conflict_reported'])?'status-suspended':(!empty($decision['decision'])?'status-active':'status-pending') }}">{{ !empty($decision['conflict_reported'])?'Konflik dilaporkan':($decision['decision_label'] ?? 'Belum dinilai') }}</span></div></article>
            @endforeach
        </div>
        @if(($assignment['aggregate']['key'] ?? '') === 'approved')
            <div class="admin-note admin-note-success"><strong>Panel telah menyetujui pengajuan.</strong> Sistem dapat meneruskan proses sesuai alur payout. Administrator tidak mengeksekusi transfer secara manual.</div>
        @endif
    </section>
    <section class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">PENUGASAN / PENUGASAN ULANG</span><h2>Tetapkan dua reviewer</h2><p>Pilih tepat dua reviewer aktif. Keduanya harus berasal dari institusi yang berbeda dan bukan institusi peneliti.</p></div></div>
        @if(count($reviewers) < 2)
            <div class="admin-note admin-note-warning"><strong>Reviewer aktif belum mencukupi.</strong> Aktifkan atau tambahkan reviewer dari institusi yang memenuhi syarat sebelum melanjutkan.</div>
        @else
        <form method="POST" action="{{ route('administrator.assignments.assign',['assignmentId'=>$assignment['id']]) }}" class="admin-assignment-form" data-admin-assignment-form>@csrf
            <div class="admin-reviewer-choice-grid">
                @foreach($reviewers as $reviewer)
                    <label class="admin-reviewer-choice"><input type="checkbox" name="reviewer_ids[]" value="{{ $reviewer['id'] }}" @checked(in_array($reviewer['id'],$assignment['assigned_reviewer_ids'] ?? [],true))><span class="admin-choice-content"><strong>{{ $reviewer['name'] }}</strong><small>{{ $reviewer['institution'] }}</small><small>{{ $reviewer['expertise'] }}</small></span></label>
                @endforeach
            </div>
            <label class="admin-field"><span>Alasan penugasan / penugasan ulang <em>*</em></span><textarea name="reason" rows="3" required minlength="10" placeholder="Contoh: Mengganti reviewer yang melaporkan konflik kepentingan dan menjaga independensi panel."></textarea></label>
            <div class="admin-note"><strong>Catatan:</strong> Jika panel diganti, penilaian panel sebelumnya disimpan dalam riwayat penugasan dan tidak dihitung untuk panel baru. Kedua reviewer baru harus memberikan keputusan independen.</div>
            <button class="button button-primary" type="submit">Simpan panel reviewer</button>
        </form>
        @endif
    </section>
    @if($panelHistory !== [])
    <section class="admin-panel"><h2>Riwayat perubahan panel</h2><div class="admin-log-list">@foreach($panelHistory as $entry)<div class="admin-log-item"><span class="admin-log-dot severity-warning"></span><div><strong>Panel sebelumnya</strong><p>{{ implode(', ', $entry['reviewer_ids'] ?? []) }} · {{ count($entry['reviews'] ?? []) }} respons tercatat</p><small>{{ $entry['changed_at'] }}</small></div></div>@endforeach</div></section>
    @endif
</div>
@endsection
