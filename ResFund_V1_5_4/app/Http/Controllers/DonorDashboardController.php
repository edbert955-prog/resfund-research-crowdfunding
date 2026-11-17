<?php

namespace App\Http\Controllers;

use App\Support\DonorPrototypeData;
use App\Support\PrototypeCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DonorDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $this->user($request);
        $donations = DonorPrototypeData::donationsFor($user, $request);
        $notifications = $this->notifications($request, $user);
        $settled = array_values(array_filter($donations, fn ($row) => $row['payment_status'] === 'SETTLED'));
        $refunded = array_values(array_filter($donations, fn ($row) => $row['payment_status'] === 'REFUNDED'));
        $activeProjects = array_values(array_filter($donations, fn ($row) => $row['campaign_status'] === 'ACTIVE'));
        $recentDonations = array_slice($donations, 0, 4);

        return view('donor.dashboard', [
            'user' => $user,
            'donations' => $donations,
            'recentDonations' => $recentDonations,
            'settledTotal' => array_sum(array_column($settled, 'amount')),
            'projectCount' => count(array_unique(array_column($donations, 'project_slug'))),
            'activeCount' => count($activeProjects),
            'refundedTotal' => array_sum(array_column($refunded, 'refund_amount')),
            'notifications' => $notifications,
            'unreadCount' => count(array_filter($notifications, fn ($item) => ! $item['read'])),
            'isDemo' => strtolower((string) ($user['email'] ?? '')) === DonorPrototypeData::DEMO_EMAIL,
            'activeMenu' => 'dashboard',
        ]);
    }

    public function donations(Request $request)
    {
        $user = $this->user($request);
        $donations = DonorPrototypeData::donationsFor($user, $request);
        $status = (string) $request->query('status', '');
        $query = trim((string) $request->query('q', ''));

        if ($status !== '') {
            $donations = array_values(array_filter($donations, fn ($row) => $row['payment_status'] === $status));
        }
        if ($query !== '') {
            $needle = strtolower($query);
            $donations = array_values(array_filter($donations, fn ($row) => str_contains(strtolower($row['project_title'].' '.$row['id'].' '.$row['transaction_ref']), $needle)));
        }

        return view('donor.donations.index', compact('user', 'donations', 'status', 'query'))->with('activeMenu', 'donations');
    }

    public function donationDetail(Request $request, string $donationId)
    {
        $user = $this->user($request);
        $donation = collect(DonorPrototypeData::donationsFor($user, $request))->firstWhere('id', $donationId);
        abort_unless($donation, 404);

        return view('donor.donations.show', compact('user', 'donation'))->with('activeMenu', 'donations');
    }

    public function monitoring(Request $request)
    {
        $user = $this->user($request);
        $donations = DonorPrototypeData::donationsFor($user, $request);
        $projects = $this->uniqueProjectsFromDonations($donations);

        return view('donor.monitoring.index', compact('user', 'projects'))->with('activeMenu', 'monitoring');
    }

    public function monitoringDetail(Request $request, string $slug)
    {
        $user = $this->user($request);
        $donation = $this->findProjectDonation($request, $user, $slug);
        abort_unless($donation, 403, 'Anda belum memiliki riwayat donasi pada proyek ini.');
        $project = collect(PrototypeCatalog::projects($request->session()->get('prototype_project_collected', [])))->firstWhere('slug', $slug);
        abort_unless($project, 404);
        $milestones = $donation['milestones'] ?: $project['milestones'];

        return view('donor.monitoring.show', compact('user', 'donation', 'project', 'milestones'))->with('activeMenu', 'monitoring');
    }

    /**
     * S10 tetap merupakan ringkasan yang bersifat publik. Route ini hanya
     * menyediakan entry point dengan sidebar Donatur agar sesuai sitemap.
     * Konten yang ditampilkan tetap agregat dan tidak membuka bukti sensitif.
     */
    public function financeSummary(Request $request, ?string $slug = null)
    {
        $user = $this->user($request);
        $dataset = PrototypeCatalog::publicReports($request->session()->get('prototype_project_collected', []));
        $reports = $dataset['reports'];
        $summary = $dataset['summary'];
        $report = $slug !== null ? PrototypeCatalog::publicReport($slug, $request->session()->get('prototype_project_collected', [])) : null;
        abort_if($slug !== null && $report === null, 404);

        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        if ($report === null && $query !== '') {
            $reports = array_values(array_filter($reports, fn (array $item) => str_contains(mb_strtolower($item['project_title'].' '.$item['researcher']), mb_strtolower($query))));
        }
        if ($report === null && $status !== '') {
            $reports = array_values(array_filter($reports, fn (array $item) => $item['status'] === $status));
        }

        return view('donor.finance', compact('user', 'reports', 'summary', 'report', 'query', 'status'))->with('activeMenu', 'finance');
    }

    public function ledgerIndex(Request $request)
    {
        $user = $this->user($request);
        $donations = DonorPrototypeData::donationsFor($user, $request);
        $projects = array_values(array_filter($this->uniqueProjectsFromDonations($donations), fn ($row) => $row['payment_status'] === 'SETTLED' && $row['verified']));

        return view('donor.ledger.index', compact('user', 'projects'))->with('activeMenu', 'ledger');
    }

    public function ledger(Request $request, string $slug)
    {
        $user = $this->user($request);
        $donation = $this->findProjectDonation($request, $user, $slug);
        abort_unless($donation && $donation['payment_status'] === 'SETTLED' && $donation['verified'], 403, 'Rincian ledger hanya tersedia bagi Donatur dengan donasi terverifikasi pada proyek terkait.');

        $project = collect(PrototypeCatalog::projects($request->session()->get('prototype_project_collected', [])))->firstWhere('slug', $slug);
        abort_unless($project, 404);
        $entries = DonorPrototypeData::ledgerFor($slug);
        $category = (string) $request->query('kategori', '');
        $query = trim((string) $request->query('q', ''));
        if ($category !== '') {
            $entries = array_values(array_filter($entries, fn ($entry) => $entry['category'] === $category));
        }
        if ($query !== '') {
            $needle = strtolower($query);
            $entries = array_values(array_filter($entries, fn ($entry) => str_contains(strtolower($entry['recipient'].' '.$entry['description'].' '.$entry['receipt']), $needle)));
        }
        $totalEntries = count($entries);
        $perPage = 5;
        $lastPage = max(1, (int) ceil($totalEntries / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $entries = array_slice($entries, ($page - 1) * $perPage, $perPage);
        $total = array_sum(array_column(DonorPrototypeData::ledgerFor($slug), 'amount'));
        $categories = array_values(array_unique(array_column(DonorPrototypeData::ledgerFor($slug), 'category')));

        return view('donor.ledger.show', compact('user', 'donation', 'project', 'entries', 'category', 'query', 'total', 'categories', 'page', 'lastPage', 'totalEntries'))->with('activeMenu', 'ledger');
    }

    public function auditIndex(Request $request)
    {
        $user = $this->user($request);
        $donations = DonorPrototypeData::donationsFor($user, $request);
        $eligibleProjects = array_values(array_filter($this->uniqueProjectsFromDonations($donations), fn ($row) => $row['payment_status'] === 'SETTLED' && $row['has_ledger']));
        $tickets = $this->clarifications($request, $user);

        return view('donor.audit.index', compact('user', 'eligibleProjects', 'tickets'))->with('activeMenu', 'audit');
    }

    public function auditCreate(Request $request, string $slug)
    {
        $user = $this->user($request);
        $donation = $this->findProjectDonation($request, $user, $slug);
        abort_unless($donation && $donation['payment_status'] === 'SETTLED', 403, 'Klarifikasi hanya tersedia bagi Donatur yang memiliki donasi sah pada proyek terkait.');
        $project = collect(PrototypeCatalog::projects($request->session()->get('prototype_project_collected', [])))->firstWhere('slug', $slug);
        abort_unless($project, 404);
        $entries = DonorPrototypeData::ledgerFor($slug);
        abort_unless(count($entries) > 0, 403, 'Belum ada pengeluaran yang dipublikasikan untuk proyek ini.');
        $tickets = array_values(array_filter($this->clarifications($request, $user), fn ($ticket) => $ticket['project_slug'] === $slug));

        return view('donor.audit.create', compact('user', 'donation', 'project', 'entries', 'tickets'))->with('activeMenu', 'audit');
    }

    public function auditStore(Request $request, string $slug)
    {
        $user = $this->user($request);
        $donation = $this->findProjectDonation($request, $user, $slug);
        abort_unless($donation && $donation['payment_status'] === 'SETTLED', 403);

        $validated = $request->validate([
            'ledger_id' => ['required', 'string', 'max:30'],
            'subject' => ['required', 'string', 'min:5', 'max:120'],
            'message' => ['required', 'string', 'min:15', 'max:1500'],
        ], [
            'ledger_id.required' => 'Pilih transaksi yang ingin diklarifikasi.',
            'subject.required' => 'Judul pertanyaan wajib diisi.',
            'subject.min' => 'Judul pertanyaan terlalu singkat.',
            'message.required' => 'Uraian klarifikasi wajib diisi.',
            'message.min' => 'Uraian klarifikasi minimal 15 karakter agar mudah dipahami peneliti.',
        ]);

        $entry = collect(DonorPrototypeData::ledgerFor($slug))->firstWhere('id', $validated['ledger_id']);
        if (! $entry) {
            throw ValidationException::withMessages(['ledger_id' => 'Transaksi yang dipilih tidak ditemukan pada ledger proyek.']);
        }

        $tickets = $this->clarifications($request, $user);
        $tickets[] = [
            'id' => 'AUD-'.now()->format('YmdHis'),
            'project_slug' => $slug,
            'project_title' => $donation['project_title'],
            'ledger_id' => $entry['id'],
            'receipt' => $entry['receipt'],
            'subject' => trim($validated['subject']),
            'message' => trim($validated['message']),
            'date' => now()->translatedFormat('d F Y'),
            'status' => 'MENUNGGU_RESPONS',
            'status_label' => 'Menunggu respons peneliti',
            'response_deadline' => now()->addHours(72)->translatedFormat('d F Y H:i'),
            'researcher_response' => null,
        ];
        $request->session()->put('prototype_audit_tickets', $tickets);

        return redirect()->route('donor.audit.create', ['slug' => $slug])
            ->with('success', 'Klarifikasi audit berhasil dicatat dalam simulasi. Target respons peneliti adalah 3 × 24 jam sesuai SKPL.');
    }

    public function profile(Request $request)
    {
        $user = $this->user($request);
        $notifications = $this->notifications($request, $user);

        return view('donor.profile.index', [
            'user' => $user,
            'notifications' => $notifications,
            'unreadCount' => count(array_filter($notifications, fn ($item) => ! $item['read'])),
            'activeMenu' => 'profile',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+().\-\s]+$/'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
        ]);

        $user = $this->user($request);
        $user['name'] = trim($validated['name']);
        $user['phone'] = trim($validated['phone']);
        $request->session()->put('prototype_user', $user);

        $registered = $request->session()->get('prototype_registered_account');
        if (is_array($registered) && ($registered['email'] ?? null) === ($user['email'] ?? null)) {
            $registered['name'] = $user['name'];
            $registered['phone'] = $user['phone'];
            $request->session()->put('prototype_registered_account', $registered);
        }

        return redirect()->route('donor.profile')->with('success', 'Profil berhasil diperbarui pada sesi prototype ini.');
    }

    public function markNotificationRead(Request $request, string $id)
    {
        $user = $this->user($request);
        $notifications = $this->notifications($request, $user);
        foreach ($notifications as &$notification) {
            if ($notification['id'] === $id) {
                $notification['read'] = true;
            }
        }
        unset($notification);
        $request->session()->put('prototype_notifications', $notifications);

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    public function markAllNotificationsRead(Request $request)
    {
        $user = $this->user($request);
        $notifications = $this->notifications($request, $user);
        foreach ($notifications as &$notification) {
            $notification['read'] = true;
        }
        unset($notification);
        $request->session()->put('prototype_notifications', $notifications);

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }

    private function user(Request $request): array
    {
        return $request->session()->get('prototype_user', []);
    }

    private function notifications(Request $request, array $user): array
    {
        return $request->session()->get('prototype_notifications', DonorPrototypeData::notificationsFor($user));
    }

    private function clarifications(Request $request, array $user): array
    {
        return $request->session()->get('prototype_audit_tickets', DonorPrototypeData::clarificationsFor($user));
    }

    private function findProjectDonation(Request $request, array $user, string $slug): ?array
    {
        return collect(DonorPrototypeData::donationsFor($user, $request))->firstWhere('project_slug', $slug);
    }

    private function uniqueProjectsFromDonations(array $donations): array
    {
        $seen = [];
        $projects = [];
        foreach ($donations as $donation) {
            if (isset($seen[$donation['project_slug']])) {
                continue;
            }
            $seen[$donation['project_slug']] = true;
            $projects[] = $donation;
        }

        return $projects;
    }
}
