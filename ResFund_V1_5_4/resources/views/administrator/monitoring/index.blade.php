@extends('layouts.administrator')
@section('title', 'Monitoring & Audit Log')
@section('page-heading', 'Monitoring & Audit Log')
@section('admin-content')
<div class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">PEMANTAUAN AKTIVITAS SISTEM</span><h1>Monitoring &amp; Audit Log</h1><p>Telusuri aktivitas administratif dan peristiwa sistem. Catatan audit bersifat hanya-baca dan tidak dapat dihapus melalui antarmuka.</p></div><span class="admin-readonly-badge">Hanya baca</span></div>
    <section class="admin-kpi-grid admin-monitoring-kpis" aria-label="Ringkasan kondisi platform">
        <article class="admin-kpi-card"><span class="admin-kpi-icon icon-blue"><x-icon name="layers" size="21" /></span><div><span>Proyek terdaftar</span><strong>{{ $monitoring['projects'] }}</strong><small>Data proyek demonstrasi</small></div></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon icon-amber"><x-icon name="clock" size="21" /></span><div><span>Kampanye aktif</span><strong>{{ $monitoring['active'] }}</strong><small>Sedang menggalang dana</small></div></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon icon-green"><x-icon name="check-circle" size="21" /></span><div><span>Target tercapai</span><strong>{{ $monitoring['funded'] }}</strong><small>Menunggu proses milestone</small></div></article>
        <article class="admin-kpi-card admin-kpi-card--money">
            <span class="admin-kpi-icon icon-purple"><x-icon name="wallet" size="21" /></span>
            <div class="admin-kpi-content">
                <span>Dana terhimpun bersih</span>
                <strong class="admin-kpi-currency">Rp{{ number_format($monitoring['collected'],0,',','.') }}</strong>
                <small>Refund kampanye gagal dikecualikan: Rp{{ number_format($monitoring['excluded_failed_amount'],0,',','.') }}</small>
            </div>
        </article>
    </section>
    <section class="admin-panel">
        <form method="GET" class="admin-filter-bar" action="{{ route('administrator.monitoring.index') }}"><label class="admin-search"><span class="sr-only">Cari log audit</span><input type="search" name="q" value="{{ $query }}" placeholder="Cari aktor, tindakan, entitas, atau detail..."></label><label><span class="sr-only">Tingkat log</span><select name="severity"><option value="">Semua tingkat</option><option value="info" @selected($severity==='info')>Informasi</option><option value="warning" @selected($severity==='warning')>Peringatan</option><option value="critical" @selected($severity==='critical')>Kritis</option></select></label><label><span class="sr-only">Jenis tindakan</span><select name="action"><option value="">Semua tindakan</option>@foreach(collect($logs)->pluck('action')->unique()->sort() as $actionOption)<option value="{{ $actionOption }}" @selected($action===$actionOption)>{{ $actionOption }}</option>@endforeach</select></label><button class="button button-primary" type="submit">Filter</button><a class="button button-secondary" href="{{ route('administrator.monitoring.index') }}">Reset</a></form>
        <div class="admin-table-wrap"><table class="admin-table admin-audit-table"><thead><tr><th>Waktu</th><th>Aktor</th><th>Tindakan</th><th>Entitas</th><th>Detail aktivitas</th><th>Tingkat</th><th>IP</th></tr></thead><tbody>
        @forelse($logs as $log)
            <tr><td><strong>{{ $log['timestamp'] }}</strong></td><td>{{ $log['actor'] }}<small>{{ $log['actor_email'] }}</small></td><td><code>{{ $log['action'] }}</code></td><td>{{ $log['entity'] }}</td><td>{{ $log['detail'] }}</td><td><span class="admin-status status-{{ ($log['severity'] ?? '')==='warning'?'pending':(($log['severity'] ?? '')==='critical'?'suspended':'active') }}">{{ strtoupper($log['severity'] ?? 'info') }}</span></td><td>{{ $log['ip'] ?? '—' }}</td></tr>
        @empty<tr><td colspan="7"><div class="admin-empty"><h3>Tidak ada catatan sesuai filter</h3><p>Ubah filter pencarian untuk melihat aktivitas lain.</p></div></td></tr>@endforelse
        </tbody></table></div>
        <div class="admin-table-foot">{{ count($logs) }} catatan ditampilkan · log terbaru berada di urutan teratas · data demo disimpan pada sesi prototype.</div>
    </section>
    <div class="admin-note"><strong>Catatan integritas:</strong> log pada prototipe ini tidak menyediakan tombol edit atau hapus. Pada implementasi produksi, audit log perlu disimpan di penyimpanan terpusat dengan kontrol akses, retensi, sinkronisasi waktu, dan proteksi perubahan.</div>
</div>
@endsection
