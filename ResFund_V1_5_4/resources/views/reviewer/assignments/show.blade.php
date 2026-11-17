@extends('layouts.reviewer')
@section('title', 'Detail Penilaian')
@section('page-heading', 'Detail Bukti dan Evaluasi')
@section('reviewer-content')
<div class="reviewer-page reviewer-detail-page">
    <nav class="reviewer-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('reviewer.dashboard') }}">Dashboard</a><span aria-hidden="true">/</span><a href="{{ route('reviewer.assignments.index') }}">Penugasan Saya</a><span aria-hidden="true">/</span><span aria-current="page">Detail Penilaian</span></nav>

    <div class="reviewer-detail-heading">
        <div>
            <a href="{{ route('reviewer.assignments.index') }}" class="reviewer-back-link"><x-icon name="arrow-left" size="16" /> Kembali ke penugasan</a>
            <span class="reviewer-eyebrow">DETAIL BUKTI DAN EVALUASI</span>
            <h1>{{ $assignment['project_title'] }}</h1>
            <p>Milestone {{ $assignment['milestone_number'] }} · {{ $assignment['milestone_name'] }}</p>
        </div>
        <div class="reviewer-deadline-box @if($daysLeft < 0) is-overdue @elseif($daysLeft <= 2) is-urgent @endif">
            <span>Batas penilaian</span>
            <strong>{{ \Carbon\Carbon::parse($assignment['due_at'])->translatedFormat('d F Y') }}</strong>
            @if($daysLeft < 0)<small>Tenggat telah lewat</small>@elseif($daysLeft === 0)<small>Hari terakhir</small>@else<small>{{ $daysLeft }} hari kalender tersisa</small>@endif
        </div>
    </div>

    @if(($assignment['aggregate']['key'] ?? '') === 'approved')
        <div class="reviewer-callout callout-success"><strong>Seluruh reviewer menyetujui pengajuan.</strong><p>Status siap diteruskan ke proses pencairan oleh sistem. Halaman Reviewer tidak melakukan transfer dana.</p></div>
    @elseif(($assignment['aggregate']['key'] ?? '') === 'revision')
        <div class="reviewer-callout callout-warning"><strong>Pengajuan memerlukan revisi.</strong><p>Pencairan belum dapat diproses. Catatan penilaian panel menjadi dasar perbaikan Peneliti.</p></div>
    @elseif(($assignment['aggregate']['key'] ?? '') === 'rejected')
        <div class="reviewer-callout callout-danger"><strong>Pengajuan ditolak oleh panel reviewer.</strong><p>Pencairan tidak dapat dilanjutkan berdasarkan keputusan penilaian ini.</p></div>
    @elseif(($assignment['aggregate']['key'] ?? '') === 'conflict')
        <div class="reviewer-callout callout-warning"><strong>Penugasan perlu diganti.</strong><p>Konflik kepentingan dilaporkan. Administrator perlu menetapkan reviewer pengganti sebelum proses evaluasi dapat diselesaikan.</p></div>
    @else
        <div class="reviewer-callout callout-info"><strong>Penilaian independen oleh dua reviewer.</strong><p>Keputusan Anda disimpan secara terpisah. Pencairan hanya dapat diteruskan jika kedua reviewer memilih Disetujui.</p></div>
    @endif

    <div class="reviewer-detail-grid">
        <div class="reviewer-detail-main">
            <section class="reviewer-panel">
                <div class="reviewer-panel-heading detail-panel-heading"><div><span class="reviewer-eyebrow">RINGKASAN PENGAJUAN</span><h2>Informasi proyek dan milestone</h2></div><span class="reviewer-category">{{ $assignment['category'] }}</span></div>
                <div class="reviewer-summary-grid">
                    <div><small>Nama peneliti</small><strong>{{ $assignment['researcher_name'] }}</strong></div>
                    <div><small>Institusi</small><strong>{{ $assignment['institution'] }}</strong></div>
                    <div><small>Nominal pencairan yang diminta</small><strong>Rp{{ number_format($assignment['requested_amount'], 0, ',', '.') }}</strong></div>
                    <div><small>Alokasi milestone</small><strong>{{ rtrim(rtrim(number_format($assignment['allocation'], 2, ',', '.'), '0'), ',') }}%</strong></div>
                    <div><small>Dokumen diajukan</small><strong>{{ count($assignment['documents']) }} berkas</strong></div>
                    <div><small>Putaran pengajuan</small><strong>{{ $assignment['revision_round'] ?? 1 }}</strong></div>
                </div>
                <div class="reviewer-milestone-description"><strong>Deskripsi dan target luaran</strong><p>{{ $assignment['target_output'] }}</p></div>
            </section>

            <section class="reviewer-panel">
                <div class="reviewer-panel-heading"><div><span class="reviewer-eyebrow">DOKUMEN PENDUKUNG</span><h2>Bukti yang harus ditelaah</h2><p>Baca ringkasan setiap berkas sebelum mengisi rubrik evaluasi.</p></div></div>
                <div class="reviewer-document-list">
                    @foreach($assignment['documents'] as $document)
                        <article class="reviewer-document-card">
                            <span class="reviewer-document-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M6 3.75h8l4 4v12.5H6z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 3.75v4h4M8.5 12h7M8.5 15.5h7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                            <div class="reviewer-document-copy"><strong>{{ $document['title'] }}</strong><span>{{ $document['filename'] }}</span><small>{{ $document['kind'] }} · Dikirim {{ $document['uploaded_at'] }}</small></div>
                            <button type="button" class="button button-outline reviewer-document-button" data-document-preview data-preview-title="{{ $document['title'] }}" data-preview-file="{{ $document['filename'] }}" data-preview-summary="{{ $document['summary'] }}">Lihat pratinjau</button>
                        </article>
                    @endforeach
                </div>
                <p class="reviewer-document-note">Pratinjau dokumen pada prototype ini menggunakan ringkasan ilustratif. Integrasi penyimpanan dokumen dan PDF asli belum diaktifkan.</p>
            </section>

            <section class="reviewer-panel reviewer-panel-reviewers">
                <div class="reviewer-panel-heading"><div><span class="reviewer-eyebrow">PENUGASAN PANEL</span><h2>Status reviewer independen</h2></div></div>
                <div class="reviewer-reviewer-status-list">
                    @foreach($otherReviews as $reviewer)
                        @php($statusClass = $reviewer['status'] === 'Disetujui' ? 'status-success' : (in_array($reviewer['status'], ['Revisi Minor', 'Ditolak', 'Konflik dilaporkan'], true) ? 'status-warning' : 'status-pending'))
                        <div class="reviewer-reviewer-status-row"><span class="reviewer-reviewer-avatar" aria-hidden="true">{{ strtoupper(substr($reviewer['name'], 0, 1)) }}</span><div><strong>{{ $reviewer['name'] }} @if($reviewer['is_self']) <em>(Anda)</em> @endif</strong><small>{{ $reviewer['institution'] ?? 'Institusi reviewer' }}</small></div><span class="reviewer-status {{ $statusClass }}">{{ $reviewer['status'] }}</span></div>
                    @endforeach
                </div>
            </section>

            @if($myReview && empty($myReview['conflict_reported']))
                <section class="reviewer-panel reviewer-locked-review">
                    <div class="reviewer-panel-heading"><div><span class="reviewer-eyebrow">PENILAIAN ANDA</span><h2>Keputusan telah dikirim</h2><p>Dikirim {{ $myReview['submitted_at'] }}</p></div><span class="reviewer-status status-success">Terkunci</span></div>
                    <div class="reviewer-decision-summary"><strong>{{ $myReview['decision_label'] }}</strong><p>{{ $myReview['general_comment'] }}</p>@if(!empty($myReview['decision_notes']))<p><strong>Catatan keputusan:</strong> {{ $myReview['decision_notes'] }}</p>@endif</div>
                    <div class="reviewer-score-summary">
                        @foreach(['relevance' => 'Relevansi', 'methodology' => 'Metodologi', 'evidence' => 'Kelengkapan bukti', 'budget' => 'Kesesuaian anggaran'] as $key => $label)
                            <div><span>{{ $label }}</span><strong>{{ $myReview[$key] ?? '—' }}/5</strong></div>
                        @endforeach
                    </div>
                </section>
            @elseif(!empty($myReview['conflict_reported']))
                <section class="reviewer-panel"><div class="reviewer-callout callout-warning"><strong>Konflik kepentingan telah dilaporkan.</strong><p>{{ $myReview['conflict_reason'] }}</p></div></section>
            @elseif($canEvaluate)
                <section class="reviewer-panel reviewer-evaluation-panel" id="form-penilaian">
                    <div class="reviewer-panel-heading"><div><span class="reviewer-eyebrow">FORM EVALUASI TERSTRUKTUR</span><h2>Berikan penilaian Anda</h2><p>Semua kriteria wajib diberi skor 1–5 dan disertai catatan yang jelas.</p></div><span class="reviewer-form-required">* Wajib diisi</span></div>
                    <form method="POST" action="{{ route('reviewer.assignments.evaluate', ['assignmentId' => $assignment['id']]) }}" class="reviewer-evaluation-form" data-evaluation-form>
                        @csrf
                        <div class="reviewer-declaration"><label><input type="checkbox" name="no_conflict" value="1" required @checked(old('no_conflict'))><span><strong>Saya menyatakan tidak memiliki konflik kepentingan.</strong><small>Saya tidak memiliki hubungan pribadi, finansial, atau institusional yang dapat memengaruhi objektivitas penilaian.</small></span></label></div>

                        @php($rubrics = [
                            'relevance' => ['Relevansi terhadap tujuan riset', 'Apakah kegiatan dan luaran milestone sesuai dengan tujuan proposal serta target yang dijanjikan?'],
                            'methodology' => ['Metodologi dan kelayakan teknis', 'Apakah metode, tahapan kerja, dan pendekatan teknis dapat dipertanggungjawabkan?'],
                            'evidence' => ['Kelengkapan dan validitas bukti', 'Apakah dokumen pendukung cukup jelas, relevan, dan menunjukkan capaian yang dapat diperiksa?'],
                            'budget' => ['Kesesuaian anggaran dan laporan', 'Apakah nominal yang diminta dan pertanggungjawaban dana sesuai alokasi serta dokumen yang tersedia?'],
                        ])
                        @foreach($rubrics as $key => $rubric)
                            <fieldset class="reviewer-rubric-fieldset">
                                <legend><strong>{{ $loop->iteration }}. {{ $rubric[0] }}</strong><small>{{ $rubric[1] }}</small></legend>
                                <div class="reviewer-rating-options" role="radiogroup" aria-label="Skor {{ $rubric[0] }}">
                                    @for($score = 1; $score <= 5; $score++)
                                        <label class="reviewer-rating-choice"><input type="radio" name="{{ $key }}" value="{{ $score }}" required @checked((string) old($key) === (string) $score)><span><strong>{{ $score }}</strong><small>{{ [1 => 'Sangat kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat baik'][$score] }}</small></span></label>
                                    @endfor
                                </div>
                                @error($key)<small class="field-error">{{ $message }}</small>@enderror
                            </fieldset>
                        @endforeach

                        <div class="form-group reviewer-form-group"><label for="general-comment">Catatan umum penilaian <span aria-hidden="true">*</span></label><textarea id="general-comment" name="general_comment" rows="4" minlength="20" maxlength="2000" required placeholder="Tuliskan temuan utama, kekuatan bukti, dan aspek yang perlu diperhatikan...">{{ old('general_comment') }}</textarea><small>Minimal 20 karakter. Catatan ini akan diteruskan kepada peneliti sesuai proses sistem.</small>@error('general_comment')<small class="field-error">{{ $message }}</small>@enderror</div>

                        <fieldset class="reviewer-decision-fieldset">
                            <legend><strong>Keputusan akhir <span aria-hidden="true">*</span></strong><small>Pilih keputusan berdasarkan rubrik dan bukti yang sudah ditinjau.</small></legend>
                            <div class="reviewer-decision-options">
                                <label class="reviewer-decision-option decision-approve"><input type="radio" name="decision" value="approved" required @checked(old('decision') === 'approved')><span><strong>Disetujui</strong><small>Bukti dan capaian memenuhi kriteria.</small></span></label>
                                <label class="reviewer-decision-option decision-revision"><input type="radio" name="decision" value="revision_minor" required @checked(old('decision') === 'revision_minor')><span><strong>Revisi Minor</strong><small>Perlu perbaikan atau bukti tambahan.</small></span></label>
                                <label class="reviewer-decision-option decision-reject"><input type="radio" name="decision" value="rejected" required @checked(old('decision') === 'rejected')><span><strong>Ditolak</strong><small>Bukti tidak memenuhi persyaratan.</small></span></label>
                            </div>
                        </fieldset>

                        <div class="form-group reviewer-form-group" data-decision-notes hidden><label for="decision-notes">Alasan revisi / penolakan <span aria-hidden="true">*</span></label><textarea id="decision-notes" name="decision_notes" rows="3" minlength="15" maxlength="1500" placeholder="Jelaskan bagian yang harus diperbaiki atau alasan penolakan...">{{ old('decision_notes') }}</textarea><small>Minimal 15 karakter dan harus spesifik agar dapat ditindaklanjuti.</small>@error('decision_notes')<small class="field-error">{{ $message }}</small>@enderror</div>

                        <div class="reviewer-submit-row"><p>Setelah dikirim, keputusan Anda terkunci. Pencairan hanya siap diproses apabila kedua reviewer menyetujui.</p><button type="submit" class="button button-primary button-large">Kirim hasil evaluasi <x-icon name="arrow-right" size="16" /></button></div>
                    </form>
                </section>

                @if($canReportConflict)
                    <section class="reviewer-panel reviewer-conflict-panel">
                        <details><summary>Laporkan konflik kepentingan</summary><p>Gunakan pilihan ini bila terdapat hubungan atau kondisi yang dapat memengaruhi independensi penilaian. Setelah laporan dikirim, penugasan akan dihentikan dan menunggu penggantian reviewer oleh Administrator.</p>
                            <form method="POST" action="{{ route('reviewer.assignments.conflict', ['assignmentId' => $assignment['id']]) }}" class="reviewer-conflict-form">@csrf<label for="conflict-reason">Jelaskan potensi konflik <span aria-hidden="true">*</span></label><textarea id="conflict-reason" name="conflict_reason" rows="3" minlength="20" maxlength="1000" required placeholder="Jelaskan hubungan atau potensi konflik yang perlu diketahui Administrator..."></textarea><button type="submit" class="button button-outline-danger">Kirim laporan konflik</button></form>
                        </details>
                    </section>
                @elseif(! $myReview && ($assignment['aggregate']['key'] ?? '') === 'conflict')
                    <section class="reviewer-panel reviewer-locked-review"><h2>Penilaian dihentikan sementara</h2><p>Salah satu reviewer melaporkan konflik kepentingan. Pengajuan menunggu penugasan ulang oleh Administrator; keputusan baru belum dapat dikirim.</p></section>
                @endif
            @else
                <section class="reviewer-panel reviewer-locked-review"><h2>Form evaluasi tidak tersedia</h2><p>@if($deadlinePassed)Batas waktu penilaian telah berakhir. Hubungi Administrator untuk penjadwalan ulang.@elsePenilaian belum dapat dilakukan karena penugasan ini sedang menunggu penanganan Administrator.@endif</p></section>
            @endif
        </div>

        <aside class="reviewer-detail-sidebar">
            <section class="reviewer-panel reviewer-sidebar-card"><span class="reviewer-eyebrow">RINGKASAN PANEL</span><h2>Status keputusan</h2><p class="reviewer-aggregate-label">{{ $assignment['aggregate']['label'] }}</p><div class="reviewer-progress-track"><span style="width: {{ count(array_filter($assignment['reviews'] ?? [], fn($r) => !empty($r['decision']) || !empty($r['conflict_reported']))) / max(1, count($assignment['assigned_reviewer_ids'])) * 100 }}%"></span></div><small>{{ count(array_filter($assignment['reviews'] ?? [], fn($r) => !empty($r['decision']) || !empty($r['conflict_reported']))) }} dari {{ count($assignment['assigned_reviewer_ids']) }} reviewer telah mengirim status</small>
                <div class="reviewer-side-separator"></div><div class="reviewer-amount-row"><span>Alokasi milestone</span><strong>{{ rtrim(rtrim(number_format($assignment['allocation'], 2, ',', '.'), '0'), ',') }}%</strong></div><div class="reviewer-amount-row"><span>Pengajuan pencairan</span><strong>Rp{{ number_format($assignment['requested_amount'], 0, ',', '.') }}</strong></div>
                <div class="reviewer-no-payout"><strong>Catatan kewenangan</strong><p>Reviewer hanya memberi keputusan evaluasi. Perintah pencairan dijalankan oleh proses sistem setelah seluruh reviewer menyetujui.</p></div>
            </section>
            <section class="reviewer-panel reviewer-sidebar-card"><span class="reviewer-eyebrow">KETENTUAN</span><h2>Standar evaluasi</h2><ul class="reviewer-standards"><li>Minimal dua reviewer independen.</li><li>Rubrik diisi lengkap sebelum keputusan dikirim.</li><li>Revisi atau penolakan wajib disertai alasan.</li><li>Dokumen hanya digunakan untuk penilaian penugasan.</li></ul></section>
        </aside>
    </div>
</div>

<div class="reviewer-modal" data-evidence-modal hidden role="dialog" aria-modal="true" aria-labelledby="evidence-modal-title">
    <div class="reviewer-modal-backdrop" data-modal-close></div>
    <div class="reviewer-modal-dialog" role="document"><div class="reviewer-modal-header"><div><span class="reviewer-eyebrow">PRATINJAU DOKUMEN</span><h2 id="evidence-modal-title" data-modal-title>Dokumen pendukung</h2><small data-modal-filename></small></div><button type="button" class="reviewer-modal-close" aria-label="Tutup pratinjau" data-modal-close><x-icon name="x" size="17" /></button></div><div class="reviewer-document-preview"><div class="reviewer-pdf-sheet"><div class="reviewer-pdf-mark">PDF</div><strong data-modal-preview-title>Ringkasan dokumen</strong><p data-modal-summary></p><div class="reviewer-preview-lines"><span></span><span></span><span></span><span></span></div><small>Pratinjau ringkasan ilustratif untuk prototype ResFund.</small></div></div><div class="reviewer-modal-footer"><span>Dokumen contoh · Tidak mewakili berkas asli penelitian</span><button type="button" class="button button-primary" data-modal-close>Tutup pratinjau</button></div></div>
</div>
@endsection
