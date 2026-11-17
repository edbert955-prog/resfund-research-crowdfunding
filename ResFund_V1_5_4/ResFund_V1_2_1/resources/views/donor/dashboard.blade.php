@extends('layouts.donor')
@section('title', 'Dashboard Donatur')
@section('page-heading', 'Dashboard Donatur')
@section('donor-content')
@include('donor.partials.page-intro', ['pageHeading' => 'Dashboard Donatur', 'breadcrumb' => 'Ringkasan', 'eyebrow' => 'RINGKASAN AKUN', 'pageDescription' => 'Pantau kontribusi, status donasi, dan perkembangan proyek riset yang Anda dukung.'])
@if($isDemo)<div class="prototype-notice"><span aria-hidden="true">i</span><p><strong>Mode demonstrasi.</strong> Angka dan aktivitas di dashboard ini merupakan data contoh untuk menguji alur prototype. Data belum berasal dari transaksi operasional.</p></div>@endif
<div class="donor-stat-grid">
    <article class="donor-stat-card"><div class="donor-stat-top"><span>Total donasi berhasil</span><span class="stat-icon stat-icon-blue" aria-hidden="true">↗</span></div><strong>Rp{{ number_format($settledTotal, 0, ',', '.') }}</strong><small>Akumulasi transaksi berstatus SETTLED</small></article>
    <article class="donor-stat-card"><div class="donor-stat-top"><span>Proyek didukung</span><span class="stat-icon stat-icon-teal" aria-hidden="true">⌘</span></div><strong>{{ $projectCount }}</strong><small>Proyek yang memiliki riwayat donasi</small></article>
    <article class="donor-stat-card"><div class="donor-stat-top"><span>Proyek aktif</span><span class="stat-icon stat-icon-violet" aria-hidden="true">◷</span></div><strong>{{ $activeCount }}</strong><small>Kampanye yang masih berjalan</small></article>
    <article class="donor-stat-card"><div class="donor-stat-top"><span>Refund selesai</span><span class="stat-icon stat-icon-amber" aria-hidden="true">↩</span></div><strong>Rp{{ number_format($refundedTotal, 0, ',', '.') }}</strong><small>Nominal yang telah dikembalikan</small></article>
</div>
<div class="donor-content-grid donor-dashboard-grid">
    <section class="donor-panel donor-panel-main" aria-labelledby="recent-donations-heading">
        <div class="donor-panel-heading"><div><span class="donor-section-kicker">AKTIVITAS</span><h2 id="recent-donations-heading">Donasi terbaru</h2><p>Ringkasan transaksi yang tercatat pada akun Anda.</p></div><a class="donor-text-link" href="{{ route('donor.donations.index') }}">Lihat semua <span aria-hidden="true">→</span></a></div>
        @if(count($recentDonations))
            <div class="donor-table-wrap"><table class="donor-table"><thead><tr><th scope="col">Proyek</th><th scope="col">Tanggal</th><th scope="col">Nominal</th><th scope="col">Status donasi</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead><tbody>
                @foreach($recentDonations as $donation)<tr><td><strong>{{ $donation['project_title'] }}</strong><small>{{ $donation['researcher'] }}</small></td><td>{{ $donation['date'] }}</td><td class="amount-cell">Rp{{ number_format($donation['amount'], 0, ',', '.') }}</td><td><span class="status-pill {{ strtolower($donation['payment_status']) }}"><i aria-hidden="true"></i>{{ $donation['payment_status_label'] }}</span></td><td><a class="table-action" href="{{ route('donor.donations.show', ['donationId' => $donation['id']]) }}">Detail</a></td></tr>@endforeach
            </tbody></table></div>
        @else
            <div class="donor-empty-state"><span class="empty-symbol" aria-hidden="true">▤</span><h3>Belum ada donasi</h3><p>Riwayat donasi akan muncul setelah transaksi berhasil dikonfirmasi oleh Payment Gateway.</p><a href="{{ route('projects.index') }}" class="button button-primary">Jelajahi proyek</a></div>
        @endif
    </section>
    <aside class="donor-panel donor-quick-panel" aria-labelledby="quick-actions-heading"><div class="donor-panel-heading"><div><span class="donor-section-kicker">AKSES CEPAT</span><h2 id="quick-actions-heading">Menu pilihan</h2></div></div>
        <a class="quick-link" href="{{ route('donor.monitoring.index') }}"><span class="quick-icon">◷</span><span><strong>Monitoring proyek</strong><small>Lihat progres milestone</small></span><b aria-hidden="true">→</b></a>
        <a class="quick-link" href="{{ route('donor.finance') }}"><span class="quick-icon">▥</span><span><strong>Laporan keuangan</strong><small>Lihat ringkasan publik</small></span><b aria-hidden="true">→</b></a>
        <a class="quick-link" href="{{ route('donor.ledger.index') }}"><span class="quick-icon">▧</span><span><strong>Ledger dan kuitansi</strong><small>Audit transaksi proyek</small></span><b aria-hidden="true">→</b></a>
        <a class="quick-link" href="{{ route('donor.audit.index') }}"><span class="quick-icon">◎</span><span><strong>Klarifikasi audit</strong><small>Lihat pertanyaan dan respons</small></span><b aria-hidden="true">→</b></a>
    </aside>
</div>
<div class="donor-content-grid donor-secondary-grid">
    <section class="donor-panel" aria-labelledby="project-updates-heading"><div class="donor-panel-heading"><div><span class="donor-section-kicker">PERKEMBANGAN</span><h2 id="project-updates-heading">Proyek yang dipantau</h2><p>Proyek yang pernah Anda dukung.</p></div><a class="donor-text-link" href="{{ route('donor.monitoring.index') }}">Buka monitoring →</a></div>
        @if(count($donations))<div class="mini-project-list">@foreach(array_slice($donations, 0, 3) as $donation)<div class="mini-project-row"><span class="mini-project-mark" aria-hidden="true">{{ strtoupper(substr($donation['category'], 0, 1)) }}</span><div class="mini-project-copy"><strong>{{ $donation['project_title'] }}</strong><div class="mini-progress"><span style="width: {{ $donation['campaign_progress'] }}%"></span></div><small>{{ $donation['campaign_progress'] }}% pendanaan · {{ $donation['campaign_status_label'] }}</small></div><a href="{{ route('donor.monitoring.show', ['slug' => $donation['project_slug']]) }}" aria-label="Lihat monitoring {{ $donation['project_title'] }}">→</a></div>@endforeach</div>@else<div class="donor-inline-empty">Belum ada proyek untuk dipantau.</div>@endif
    </section>
    <section class="donor-panel" aria-labelledby="notifications-preview-heading"><div class="donor-panel-heading"><div><span class="donor-section-kicker">PEMBERITAHUAN</span><h2 id="notifications-preview-heading">Notifikasi terbaru</h2><p>Informasi transaksi dan perkembangan riset.</p></div><a class="donor-text-link" href="{{ route('donor.profile') }}#notifications">Lihat semua →</a></div>
        @if(count($notifications))<div class="notification-preview-list">@foreach(array_slice($notifications, 0, 3) as $notification)<a class="notification-preview {{ $notification['read'] ? '' : 'is-unread' }}" href="{{ url($notification['url']) }}"><span class="notification-type-icon type-{{ $notification['type'] }}" aria-hidden="true">{{ $notification['type'] === 'refund' ? '↩' : ($notification['type'] === 'audit' ? '◎' : ($notification['type'] === 'ledger' ? '▧' : '◷')) }}</span><span><strong>{{ $notification['title'] }}</strong><small>{{ $notification['message'] }}</small><em>{{ $notification['date'] }}</em></span></a>@endforeach</div>@else<div class="donor-inline-empty">Belum ada notifikasi.</div>@endif
    </section>
</div>
<div class="donor-prototype-footnote">Informasi status pembayaran mengikuti konfirmasi Payment Gateway. Dashboard ini menggunakan data sesi demonstrasi dan tidak mengeksekusi transaksi nyata.</div>
@endsection
