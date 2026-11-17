@props(['project'])
@php($projectImage = str_starts_with($project['image'], 'http') ? $project['image'] : asset($project['image']))
@php($progress = $project['target'] > 0 ? min(100, (int) round(($project['collected'] / $project['target']) * 100)) : 0)
<article class="project-card">
    <a class="project-card-image" href="{{ route('projects.show', $project['slug']) }}" aria-label="Lihat detail {{ $project['title'] }}">
        <img src="{{ $projectImage }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/project-battery-photo.jpg') }}'" alt="Foto ilustratif {{ $project['title'] }}" loading="lazy">
        <span class="category-pill">{{ $project['category_label'] }}</span>
    </a>
    <div class="project-card-body">
        <div class="project-meta"><span class="status-dot {{ $project['status'] === 'FUNDED' ? 'status-dot-success' : '' }}"></span><span>{{ $project['status_label'] }}</span></div>
        <h3><a href="{{ route('projects.show', $project['slug']) }}">{{ $project['title'] }}</a></h3>
        <p class="project-researcher">{{ $project['researcher'] }} · {{ $project['institution'] }}</p>
        <p class="project-short">{{ $project['short'] }}</p>
        <div class="funding-line"><span>Terkumpul</span><strong>Rp{{ number_format($project['collected'], 0, ',', '.') }}</strong></div>
        <div class="progress-track" role="progressbar" aria-label="Progres pendanaan {{ $project['title'] }}" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $progress }}%"></span></div>
        <div class="funding-foot"><span>{{ $progress }}% dari target</span><span>Target Rp{{ number_format($project['target'], 0, ',', '.') }}</span></div>
        <div class="project-card-bottom"><span class="deadline-label">{{ $project['status'] === 'FUNDED' ? 'Target tercapai' : ($project['status'] === 'FAILED' ? 'Kampanye berakhir' : 'Batas kampanye: '.$project['deadline']) }}</span><a class="text-link" href="{{ route('projects.show', $project['slug']) }}">Lihat detail <span aria-hidden="true">→</span></a></div>
    </div>
</article>
