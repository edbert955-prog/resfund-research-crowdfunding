@extends('layouts.administrator')
@section('title', 'Manajemen Reviewer')
@section('page-heading', 'Manajemen Reviewer')
@section('admin-content')
<div class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">PENGELOLAAN PENILAI INDEPENDEN</span><h1>Manajemen Reviewer</h1><p>Kelola keahlian, institusi, akun akses, dan ketersediaan reviewer untuk penilaian milestone.</p></div><a href="#form-reviewer" class="button button-primary">+ Tambah Reviewer</a></div>
    <section class="admin-panel">
        <form method="GET" class="admin-filter-bar" action="{{ route('administrator.reviewers.index') }}"><label class="admin-search"><span class="sr-only">Cari reviewer</span><input type="search" name="q" value="{{ $query }}" placeholder="Cari nama, institusi, atau keahlian..."></label><label><span class="sr-only">Status reviewer</span><select name="status"><option value="">Semua status</option><option value="active" @selected($status==='active')>Aktif</option><option value="inactive" @selected($status==='inactive')>Nonaktif</option></select></label><button class="button button-primary" type="submit">Terapkan</button><a class="button button-secondary" href="{{ route('administrator.reviewers.index') }}">Reset</a></form>
        <div class="admin-reviewer-grid">
            @forelse($reviewers as $reviewer)
                <article class="admin-reviewer-card"><div class="admin-reviewer-card-top"><span class="admin-large-avatar">{{ strtoupper(substr($reviewer['name'],0,1)) }}</span><span class="admin-status status-{{ $reviewer['status']==='active'?'active':'suspended' }}">{{ $reviewer['status_label'] }}</span></div><h2>{{ $reviewer['name'] }}</h2><p>{{ $reviewer['email'] }}</p><dl><div><dt>Institusi</dt><dd>{{ $reviewer['institution'] }}</dd></div><div><dt>Bidang keahlian</dt><dd>{{ $reviewer['expertise'] }}</dd></div></dl><form method="POST" action="{{ route('administrator.reviewers.status',['reviewerId'=>$reviewer['id']]) }}" data-confirm="{{ $reviewer['status']==='active' ? 'Nonaktifkan reviewer ini? Reviewer tidak dapat masuk atau menerima penugasan baru.' : 'Aktifkan kembali reviewer ini?' }}">@csrf<input type="hidden" name="status" value="{{ $reviewer['status']==='active'?'inactive':'active' }}"><button type="submit" class="button {{ $reviewer['status']==='active'?'button-secondary':'button-primary' }} button-block">{{ $reviewer['status']==='active'?'Nonaktifkan reviewer':'Aktifkan reviewer' }}</button></form></article>
            @empty<div class="admin-empty"><h3>Reviewer tidak ditemukan</h3><p>Ubah kata kunci atau filter.</p></div>@endforelse
        </div>
    </section>
    <section class="admin-panel" id="form-reviewer">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">PENAMBAHAN AKUN</span><h2>Tambah reviewer baru</h2><p>Gunakan data reviewer yang telah ditetapkan secara institusional. Reviewer baru akan aktif setelah akun dibuat.</p></div></div>
        <form method="POST" action="{{ route('administrator.reviewers.store') }}" class="admin-form-grid">@csrf
            <label class="admin-field"><span>Nama lengkap <em>*</em></span><input name="name" value="{{ old('name') }}" required minlength="3" maxlength="120" placeholder="Nama dan gelar akademik"></label>
            <label class="admin-field"><span>Email akun <em>*</em></span><input type="email" name="email" value="{{ old('email') }}" required placeholder="reviewer@institusi.ac.id"></label>
            <label class="admin-field"><span>Nomor telepon <em>*</em></span><input name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx"></label>
            <label class="admin-field"><span>Institusi <em>*</em></span><input name="institution" value="{{ old('institution') }}" required placeholder="Nama perguruan tinggi"></label>
            <label class="admin-field admin-field-span"><span>Bidang keahlian <em>*</em></span><input name="expertise" value="{{ old('expertise') }}" required minlength="5" placeholder="Contoh: instrumentasi lingkungan dan sensor"></label>
            <label class="admin-field"><span>Kata sandi awal <em>*</em></span><input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter"></label>
            <div class="admin-field admin-field-end"><button type="submit" class="button button-primary">Buat akun Reviewer</button></div>
        </form>
        <p class="admin-help-text">Akun dan kata sandi hanya disimpan di sesi prototype. Untuk sistem produksi, gunakan penyimpanan akun terenkripsi, proses undangan resmi, dan kebijakan reset kata sandi.</p>
    </section>
</div>
@endsection
