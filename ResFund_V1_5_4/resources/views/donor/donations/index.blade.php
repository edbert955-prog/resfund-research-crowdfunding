@extends('layouts.donor')
@section('title', 'Donasi Saya / Riwayat')
@section('page-heading', 'Donasi Saya / Riwayat')
@section('donor-content')
@include('donor.partials.page-intro', ['pageHeading' => 'Donasi Saya', 'breadcrumb' => 'Donasi Saya / Riwayat', 'eyebrow' => 'RIWAYAT TRANSAKSI', 'pageDescription' => 'Lihat seluruh transaksi, referensi pembayaran, dan status pengembalian dana.'])
<div class="donor-panel">
    <form method="GET" action="{{ route('donor.donations.index') }}" class="donor-filter-bar">
        <div class="donor-search-field"><label for="donation-search">Cari transaksi</label><input id="donation-search" name="q" type="search" value="{{ $query }}" placeholder="Nama proyek atau ID transaksi"></div>
        <div><label for="donation-status">Status pembayaran</label><select id="donation-status" name="status"><option value="">Semua status</option><option value="SETTLED" @selected($status === 'SETTLED')>Berhasil</option><option value="WAITING_PAYMENT" @selected($status === 'WAITING_PAYMENT')>Menunggu pembayaran</option><option value="EXPIRED" @selected($status === 'EXPIRED')>Kedaluwarsa</option><option value="REFUNDED" @selected($status === 'REFUNDED')>Dikembalikan</option></select></div>
        <div class="donor-filter-actions"><button class="button button-primary" type="submit">Terapkan</button><a class="button button-outline" href="{{ route('donor.donations.index') }}">Reset</a></div>
    </form>
    @if(count($donations))
        <div class="donor-table-wrap"><table class="donor-table donor-table-wide"><thead><tr><th scope="col">ID donasi</th><th scope="col">Proyek riset</th><th scope="col">Tanggal</th><th scope="col">Nominal</th><th scope="col">Status pembayaran</th><th scope="col">Status kampanye</th><th scope="col">Aksi</th></tr></thead><tbody>@foreach($donations as $donation)<tr><td><strong>{{ $donation['id'] }}</strong><small>{{ $donation['transaction_ref'] }}</small></td><td><strong>{{ $donation['project_title'] }}</strong><small>{{ $donation['researcher'] }}</small></td><td>{{ $donation['date'] }}</td><td class="amount-cell">Rp{{ number_format($donation['amount'], 0, ',', '.') }}</td><td><span class="status-pill {{ strtolower($donation['payment_status']) }}"><i aria-hidden="true"></i>{{ $donation['payment_status_label'] }}</span></td><td><span class="status-pill campaign-{{ strtolower($donation['campaign_status']) }}">{{ $donation['campaign_status_label'] }}</span></td><td><a class="table-action" href="{{ route('donor.donations.show', ['donationId' => $donation['id']]) }}">Detail</a></td></tr>@endforeach</tbody></table></div>
        <div class="donor-table-foot"><span>Menampilkan {{ count($donations) }} transaksi pada data prototype</span><span>Status donasi berbeda dari status kampanye.</span></div>
    @else
        <div class="donor-empty-state"><span class="empty-symbol"><x-icon name="file-text" size="26" /></span><h3>Tidak ada transaksi ditemukan</h3><p>Ubah filter atau jelajahi proyek riset untuk melihat informasi kampanye.</p><a href="{{ route('projects.index') }}" class="button button-primary">Jelajahi proyek</a></div>
    @endif
</div>
<div class="donor-info-callout"><strong>Memahami status:</strong> SETTLED berarti pembayaran berhasil; REFUNDED berarti dana telah dikembalikan. Status kampanye ACTIVE, FUNDED, atau FAILED menunjukkan kondisi penggalangan dana, bukan status transaksi pembayaran.</div>
@endsection
