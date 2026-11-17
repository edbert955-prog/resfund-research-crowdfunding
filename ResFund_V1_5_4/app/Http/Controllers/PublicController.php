<?php

namespace App\Http\Controllers;

use App\Support\PrototypeCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    public function home(Request $request)
    {
        $projects = PrototypeCatalog::projects($request->session()->get('prototype_project_collected', []));

        return view('public.home', [
            'featuredProjects' => array_slice(array_values(array_filter($projects, fn (array $item) => $item['status'] !== 'FAILED')), 0, 3),
            'fundedProject' => collect($projects)->firstWhere('status', 'FUNDED'),
        ]);
    }

    public function projects(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $category = (string) $request->query('kategori', '');
        $status = (string) $request->query('status', '');

        $filtered = array_values(array_filter(PrototypeCatalog::projects($request->session()->get('prototype_project_collected', [])), function (array $project) use ($query, $category, $status): bool {
            $matchesQuery = $query === ''
                || Str::contains(Str::lower($project['title'].' '.$project['researcher'].' '.$project['short']), Str::lower($query));
            $matchesCategory = $category === '' || $project['category'] === $category;
            $matchesStatus = $status === '' || $project['status'] === $status;

            return $matchesQuery && $matchesCategory && $matchesStatus;
        }));

        $perPage = 6;
        $total = count($filtered);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $requestedPage = max(1, (int) $request->query('page', 1));
        $page = min($requestedPage, $lastPage);
        $projects = array_slice($filtered, ($page - 1) * $perPage, $perPage);

        return view('public.projects', [
            'projects' => $projects,
            'query' => $query,
            'category' => $category,
            'status' => $status,
            'categories' => PrototypeCatalog::categories(),
            'total' => $total,
            'page' => min($page, $lastPage),
            'lastPage' => $lastPage,
        ]);
    }

    public function project(string $slug)
    {
        $project = collect(PrototypeCatalog::projects(request()->session()->get('prototype_project_collected', [])))->firstWhere('slug', $slug);
        abort_unless($project, 404);

        return view('public.project-detail', compact('project'));
    }

    /**
     * Guest diarahkan ke autentikasi. Donatur yang sudah login langsung masuk checkout.
     */
    public function donationEntry(Request $request, string $slug)
    {
        $project = collect(PrototypeCatalog::projects($request->session()->get('prototype_project_collected', [])))->firstWhere('slug', $slug);
        abort_unless($project, 404);

        if ($project['status'] !== 'ACTIVE') {
            return redirect()->route('projects.show', ['slug' => $slug])
                ->with('info', 'Kampanye ini tidak sedang menerima donasi baru.');
        }

        $user = $request->session()->get('prototype_user');
        if (! is_array($user)) {
            return redirect()->route('login', [
                'intent' => 'donasi',
                'project' => $slug,
            ])->with('info', 'Masuk atau buat akun Donatur untuk melanjutkan ke checkout.');
        }

        if (($user['role'] ?? null) !== 'donatur') {
            return redirect()->route('projects.show', ['slug' => $slug])
                ->with('info', 'Hanya akun Donatur yang dapat melanjutkan proses donasi.');
        }

        return redirect()->route('donation.checkout', ['slug' => $slug]);
    }

    public function finance(Request $request, ?string $slug = null)
    {
        $dataset = PrototypeCatalog::publicReports($request->session()->get('prototype_project_collected', []));
        $reports = $dataset['reports'];
        $summary = $dataset['summary'];
        $report = $slug !== null ? PrototypeCatalog::publicReport($slug, $request->session()->get('prototype_project_collected', [])) : null;
        if ($slug !== null && $report === null) {
            abort(404);
        }

        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        if ($report === null) {
            if ($query !== '') {
                $reports = array_values(array_filter($reports, fn (array $item) => str_contains(mb_strtolower($item['project_title'].' '.$item['researcher']), mb_strtolower($query))));
            }
            if ($status !== '') {
                $reports = array_values(array_filter($reports, fn (array $item) => $item['status'] === $status));
            }
        }

        return view('public.finance', compact('reports', 'summary', 'report', 'query', 'status'));
    }

    public function howItWorks()
    {
        return view('public.how-it-works');
    }
}
