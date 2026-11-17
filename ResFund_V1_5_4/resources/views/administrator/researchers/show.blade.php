@extends('layouts.administrator')
@section('title', 'Pemeriksaan Identitas Peneliti')
@section('page-heading', 'Pemeriksaan Identitas Peneliti')
@section('admin-content')
<div class="admin-page admin-narrow-page">
    <div class="admin-breadcrumb"><a href="{{ route('administrator.researchers.index') }}">Validasi Peneliti</a><span>/</span><span>Detail identitas</span></div>
    <div class="admin-page-heading"><div><span class="admin-eyebrow">PEMERIKSAAN AKADEMIK</span><h1>Identitas peneliti</h1><p>Bandingkan data pendaftaran dengan bukti identitas akademik yang diajukan.</p></div><a href="{{ route('administrator.researchers.index') }}" class="button button-secondary">← Kembali</a></div>
    <section class="admin-panel">
        <div class="admin-profile-summary"><span class="admin-large-avatar">{{ strtoupper(substr($researcher['name'],0,1)) }}</span><div><h2>{{ $researcher['name'] }}</h2><p>{{ $researcher['email'] }}</p><span class="admin-role role-peneliti">Peneliti</span></div></div>
        <dl class="admin-detail-grid"><div><dt>Status akademik</dt><dd>{{ ucfirst($researcher['researcher_type'] ?? 'Belum diisi') }}</dd></div><div><dt>NIDN / NIM</dt><dd>{{ $researcher['academic_id'] ?: 'Belum diisi' }}</dd></div><div><dt>Perguruan tinggi</dt><dd>{{ $researcher['institution'] ?: 'Belum diisi' }}</dd></div><div><dt>Email</dt><dd>{{ $researcher['email'] }}</dd></div><div><dt>Status validasi</dt><dd>{{ str_replace('_',' ',ucfirst($researcher['verification_status'] ?? '')) }}</dd></div><div><dt>Tanggal pendaftaran</dt><dd>{{ $researcher['created_at'] }}</dd></div></dl>
        @if(($researcher['verification_status'] ?? '') === 'menunggu_verifikasi')
        <form method="POST" action="{{ route('administrator.researchers.decide',['userId'=>$researcher['id']]) }}" class="admin-decision-form" data-admin-decision-form>@csrf
            <h3>Keputusan validasi</h3><p>Berikan catatan yang jelas agar keputusan dapat ditindaklanjuti oleh peneliti.</p>
            <label class="admin-field"><span>Catatan pemeriksaan <em>*</em></span><textarea name="notes" rows="4" minlength="10" required placeholder="Contoh: Format NIDN sesuai dan institusi cocok dengan dokumen yang diperiksa."></textarea></label>
            <div class="admin-form-actions"><button class="button button-primary" name="decision" value="approve" type="submit">Setujui identitas</button><button class="button button-danger" name="decision" value="reject" type="submit">Tolak / minta perbaikan</button></div>
        </form>
        @else
        <div class="admin-note"><strong>Keputusan tersimpan:</strong> {{ $researcher['validation_notes'] ?? 'Belum ada catatan validasi.' }} @if(!empty($researcher['validated_at']))<br>Diproses {{ $researcher['validated_at'] }}@endif</div>
        @endif
    </section>
</div>
@endsection
