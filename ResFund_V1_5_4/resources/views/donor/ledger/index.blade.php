@extends('layouts.donor')
@section('title', 'Ledger dan Kuitansi')
@section('page-heading', 'Ledger & Preview Kuitansi')
@section('donor-content')
@include('donor.partials.page-intro', ['pageHeading' => 'Ledger & Preview Kuitansi', 'breadcrumb' => 'Ledger & Preview Kuitansi', 'eyebrow' => 'AUDIT KEUANGAN', 'pageDescription' => 'Pilih proyek yang memiliki donasi terverifikasi untuk membuka rincian pengeluaran dan bukti transaksi.'])
<div class="donor-info-callout"><strong>Pembatasan akses:</strong> rincian ledger dan pratinjau kuitansi hanya tersedia bagi Donatur dengan transaksi donasi terverifikasi pada proyek terkait. Pengunjung publik hanya melihat ringkasan keuangan.</div>
@if(count($projects))
<div class="donor-project-access-list">@foreach($projects as $project)<article class="donor-panel access-project-card"><div class="access-project-main"><span class="category-pill category-pill-static">{{ $project['category'] }}</span><h2>{{ $project['project_title'] }}</h2><p>{{ $project['researcher'] }}</p><small>Donasi terverifikasi: Rp{{ number_format($project['amount'], 0, ',', '.') }}</small></div><div class="access-project-action">@if($project['has_ledger'])<span class="status-pill settled"><i aria-hidden="true"></i>Ledger tersedia</span><a class="button button-primary" href="{{ route('donor.ledger.show', ['slug' => $project['project_slug']]) }}">Buka ledger</a>@else<span class="status-pill waiting_payment">Belum ada ledger</span><a class="button button-outline" href="{{ route('donor.ledger.show', ['slug' => $project['project_slug']]) }}">Lihat status</a>@endif</div></article>@endforeach</div>
@else
<div class="donor-panel donor-empty-state"><span class="empty-symbol"><x-icon name="receipt" size="26" /></span><h3>Akses ledger belum tersedia</h3><p>Anda belum memiliki transaksi donasi berstatus SETTLED yang terverifikasi. Setelah donasi berhasil dan tercatat pada proyek, akses audit terkait dapat ditampilkan.</p><a href="{{ route('projects.index') }}" class="button button-primary">Jelajahi proyek</a></div>
@endif
@endsection
