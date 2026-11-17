@extends('layouts.administrator')
@section('title', 'Validasi Peneliti')
@section('page-heading', 'Validasi Peneliti')
@section('admin-content')
<div class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">VALIDASI IDENTITAS AKADEMIK</span><h1>Validasi Peneliti</h1><p>Periksa kesesuaian NIDN/NIM, status akademik, dan institusi sebelum mengaktifkan hak pengajuan proposal.</p></div></div>
    <section class="admin-panel">
        <form method="GET" class="admin-filter-bar" action="{{ route('administrator.researchers.index') }}"><label class="admin-search"><span class="sr-only">Cari peneliti</span><input type="search" name="q" value="{{ $query }}" placeholder="Cari nama, NIDN/NIM, atau institusi..."></label><label><span class="sr-only">Status validasi</span><select name="status"><option value="">Semua keputusan</option><option value="menunggu_verifikasi" @selected($status==='menunggu_verifikasi')>Menunggu verifikasi</option><option value="terverifikasi" @selected($status==='terverifikasi')>Terverifikasi</option><option value="ditolak" @selected($status==='ditolak')>Ditolak</option></select></label><button class="button button-primary" type="submit">Cari</button><a class="button button-secondary" href="{{ route('administrator.researchers.index') }}">Reset</a></form>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Peneliti</th><th>Status</th><th>NIDN / NIM</th><th>Institusi</th><th>Jenis</th><th>Aksi</th></tr></thead><tbody>
        @forelse($researchers as $researcher)
            <tr><td><strong>{{ $researcher['name'] }}</strong><small>{{ $researcher['email'] }}</small></td><td><span class="admin-status status-{{ ($researcher['verification_status'] ?? '')==='terverifikasi'?'active':(($researcher['verification_status'] ?? '')==='ditolak'?'suspended':'pending') }}">{{ str_replace('_',' ',ucfirst($researcher['verification_status'] ?? '')) }}</span></td><td>{{ $researcher['academic_id'] ?: '—' }}</td><td>{{ $researcher['institution'] ?: '—' }}</td><td>{{ ucfirst($researcher['researcher_type'] ?? 'Peneliti') }}</td><td><a class="admin-table-action" href="{{ route('administrator.researchers.show',['userId'=>$researcher['id']]) }}">Periksa →</a></td></tr>
        @empty<tr><td colspan="6"><div class="admin-empty"><h3>Tidak ada peneliti pada filter ini</h3><p>Daftar akan bertambah ketika calon peneliti melakukan pendaftaran.</p></div></td></tr>@endforelse
        </tbody></table></div>
    </section>
    <div class="admin-note"><strong>Catatan validasi:</strong> persetujuan pada prototipe ini merupakan simulasi pemeriksaan format dan berkas. Integrasi langsung ke SSO/PDDikti dan approval operasional belum tersedia.</div>
</div>
@endsection
