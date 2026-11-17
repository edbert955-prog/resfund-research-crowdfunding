@extends('layouts.donor')
@section('title', 'Detail Donasi')
@section('page-heading', 'Detail Donasi')
@section('donor-content')
@include('donor.partials.page-intro', ['pageHeading' => 'Detail Donasi', 'breadcrumb' => 'Donasi Saya / Detail', 'eyebrow' => 'RINCIAN TRANSAKSI', 'pageDescription' => 'Informasi pembayaran dan status kampanye untuk transaksi yang dipilih.'])
<div class="donor-detail-layout">
    <section class="donor-panel">
        <div class="donor-detail-status"><span class="status-pill {{ strtolower($donation['payment_status']) }}"><i aria-hidden="true"></i>{{ $donation['payment_status_label'] }}</span><span class="detail-id">{{ $donation['id'] }}</span></div>
        <h2 class="donor-detail-project-title">{{ $donation['project_title'] }}</h2><p class="muted-text">{{ $donation['researcher'] }} · {{ $donation['category'] }}</p>
        <div class="donor-amount-highlight"><span>Nominal donasi</span><strong>Rp{{ number_format($donation['amount'], 0, ',', '.') }}</strong></div>
        <dl class="donor-definition-list">
            <div><dt>Tanggal transaksi</dt><dd>{{ $donation['date'] }}</dd></div>
            <div><dt>Metode pembayaran</dt><dd>{{ $donation['payment_method'] }}</dd></div>
            <div><dt>Referensi Payment Gateway</dt><dd>{{ $donation['transaction_ref'] }}</dd></div>
            <div><dt>Status pembayaran</dt><dd>{{ $donation['payment_status'] }}</dd></div>
            <div><dt>Status kampanye</dt><dd>{{ $donation['campaign_status'] }} · {{ $donation['campaign_status_label'] }}</dd></div>
            <div><dt>Progres pendanaan</dt><dd>{{ $donation['campaign_progress'] }}%</dd></div>
        </dl>
        @if($donation['payment_status'] === 'REFUNDED')
            <div class="status-explanation status-explanation-success"><strong>Pengembalian dana selesai</strong><p>Nominal yang dikembalikan: <strong>Rp{{ number_format($donation['refund_amount'], 0, ',', '.') }}</strong>. Dana dikembalikan 100% melalui metode pembayaran asal sesuai mekanisme All-or-Nothing.</p><small>Referensi refund: {{ $donation['refund_reference'] }}</small></div>
        @elseif($donation['payment_status'] === 'SETTLED')
            <div class="status-explanation"><strong>Pembayaran telah dikonfirmasi</strong><p>Donasi tercatat pada rekening escrow proyek. Dana tidak langsung diserahkan seluruhnya kepada peneliti dan dikelola berdasarkan tahapan milestone.</p></div>
        @endif
    </section>
    <aside class="donor-panel donor-detail-side"><h2>Proyek terkait</h2><p>{{ $donation['project_title'] }}</p><span class="status-pill campaign-{{ strtolower($donation['campaign_status']) }}">{{ $donation['campaign_status_label'] }}</span><a class="button button-primary button-full" href="{{ route('donor.monitoring.show', ['slug' => $donation['project_slug']]) }}">Lihat monitoring proyek</a>@if($donation['payment_status'] === 'SETTLED' && $donation['verified'] && $donation['has_ledger'])<a class="button button-outline button-full" href="{{ route('donor.ledger.show', ['slug' => $donation['project_slug']]) }}">Lihat ledger &amp; kuitansi</a>@endif<a class="button button-outline button-full" href="{{ route('donor.donations.index') }}">Kembali ke riwayat</a></aside>
</div>
@endsection
