@extends('layouts.app')
@section('title', 'Jelajahi Proyek')
@section('content')
<section class="page-hero page-hero-compact"><div class="container"><div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><span aria-current="page">Jelajahi Proyek</span></div><span class="eyebrow">Temukan riset yang ingin kamu dukung</span><h1>Jelajahi proyek riset</h1><p>Bandingkan tujuan penelitian, target pendanaan, dan tahapan proyek sebelum mengambil keputusan.</p></div></section>
<section class="section projects-list-section"><div class="container">
    <form action="{{ route('projects.index') }}" method="GET" class="filter-panel">
        <div class="filter-search"><label for="project-query">Cari proyek</label><div class="search-input-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg><input id="project-query" name="q" type="search" value="{{ $query }}" placeholder="Judul, topik, atau nama peneliti"></div></div>
        <div><label for="project-category">Kategori</label><select id="project-category" name="kategori"><option value="">Semua kategori</option>@foreach($categories as $item)<option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>@endforeach</select></div>
        <div><label for="project-status">Status kampanye</label><select id="project-status" name="status"><option value="">Semua status</option><option value="ACTIVE" @selected($status === 'ACTIVE')>Sedang menggalang dana</option><option value="FUNDED" @selected($status === 'FUNDED')>Target tercapai</option><option value="FAILED" @selected($status === 'FAILED')>Target tidak tercapai</option></select></div>
        <div class="filter-actions"><button class="button button-primary" type="submit">Terapkan</button><a href="{{ route('projects.index') }}" class="button button-outline">Reset</a></div>
    </form>
    <div class="results-heading"><div><h2>Daftar proyek</h2><p>{{ $total }} proyek ditemukan</p></div></div>
    @if(count($projects))
        <div class="project-grid">
            @foreach($projects as $project)<x-project-card :project="$project" />@endforeach
        </div>
        @if($lastPage > 1)
            <nav class="pagination" aria-label="Navigasi halaman proyek">
                @if($page > 1)<a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" aria-label="Halaman sebelumnya"><x-icon name="arrow-left" size="14" /> Sebelumnya</a>@else<span class="pagination-disabled"><x-icon name="arrow-left" size="14" /> Sebelumnya</span>@endif
                <span>Halaman {{ $page }} dari {{ $lastPage }}</span>
                @if($page < $lastPage)<a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" aria-label="Halaman berikutnya">Berikutnya <x-icon name="arrow-right" size="14" /></a>@else<span class="pagination-disabled">Berikutnya <x-icon name="arrow-right" size="14" /></span>@endif
            </nav>
        @endif
    @else
        <div class="empty-state"><span class="empty-icon"><x-icon name="search" size="26" /></span><h3>Proyek tidak ditemukan</h3><p>Coba ubah kata pencarian atau filter kategori yang dipilih.</p><a href="{{ route('projects.index') }}" class="button button-outline">Tampilkan semua proyek</a></div>
    @endif
</div></section>
@endsection
