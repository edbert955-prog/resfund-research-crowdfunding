@extends('layouts.administrator')
@section('title', 'Penugasan Reviewer')
@section('page-heading', 'Penugasan Reviewer')
@section('admin-content')
<div class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">PANEL PENILAIAN INDEPENDEN</span><h1>Penugasan Reviewer</h1><p>Pastikan setiap pengajuan milestone dinilai oleh minimal dua reviewer independen tanpa konflik kepentingan.</p></div></div>
    <section class="admin-panel">
        <form method="GET" class="admin-filter-bar" action="{{ route('administrator.assignments.index') }}"><label class="admin-search"><span class="sr-only">Cari pengajuan</span><input type="search" name="q" value="{{ $query }}" placeholder="Cari proyek, peneliti, atau milestone..."></label><label><span class="sr-only">Status panel</span><select name="status"><option value="">Semua status panel</option>@foreach(['unassigned'=>'Belum memiliki dua reviewer','pending'=>'Menunggu penilaian','waiting_second'=>'Menunggu reviewer kedua','approved'=>'Disetujui semua reviewer','revision'=>'Perlu revisi','rejected'=>'Ditolak','conflict'=>'Konflik kepentingan'] as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }}</option>@endforeach</select></label><button class="button button-primary" type="submit">Cari</button><a class="button button-secondary" href="{{ route('administrator.assignments.index') }}">Reset</a></form>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Pengajuan</th><th>Peneliti</th><th>Panel reviewer</th><th>Status panel</th><th>Tenggat</th><th>Aksi</th></tr></thead><tbody>
        @forelse($assignments as $assignment)
            <tr><td><strong>{{ $assignment['project_title'] }}</strong><small>Milestone {{ $assignment['milestone_number'] }} · {{ $assignment['milestone_name'] }}</small></td><td>{{ $assignment['researcher_name'] }}<small>{{ $assignment['institution'] }}</small></td><td>{{ count($assignment['assigned_reviewer_ids'] ?? []) }} reviewer<small>{{ collect($assignment['assigned_reviewers'] ?? [])->pluck('name')->implode(', ') }}</small></td><td><span class="admin-status status-{{ ($assignment['aggregate']['key'] ?? '')==='approved'?'active':((($assignment['aggregate']['key'] ?? '')==='conflict'||($assignment['aggregate']['key'] ?? '')==='rejected')?'suspended':'pending') }}">{{ $assignment['aggregate']['label'] ?? 'Menunggu penilaian' }}</span></td><td>{{ $assignment['due_at'] ?? '—' }}</td><td><a class="admin-table-action" href="{{ route('administrator.assignments.show',['assignmentId'=>$assignment['id']]) }}">Kelola panel →</a></td></tr>
        @empty<tr><td colspan="6"><div class="admin-empty"><h3>Tidak ada pengajuan pada filter ini</h3><p>Pengajuan milestone yang diteruskan untuk penilaian akan muncul di sini.</p></div></td></tr>@endforelse
        </tbody></table></div>
        <div class="admin-table-foot">Keputusan reviewer tetap bersifat independen. Administrator hanya mengatur komposisi panel dan tidak mengubah isi evaluasi.</div>
    </section>
</div>
@endsection
