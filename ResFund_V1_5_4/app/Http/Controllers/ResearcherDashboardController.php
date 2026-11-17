<?php

namespace App\Http\Controllers;

use App\Support\ResearcherPrototypeData;
use App\Support\ReviewerPrototypeData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResearcherDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $this->user($request);
        $projects = ResearcherPrototypeData::projects($request, $user);
        $expenses = ResearcherPrototypeData::expenses($request, $user);
        $activeProjects = array_filter($projects, fn (array $project) => in_array($project['campaign_status'], ['ACTIVE', 'FUNDED'], true));
        $drafts = array_filter($projects, fn (array $project) => in_array($project['proposal_status'], ['DRAFT', 'SUBMITTED'], true));
        $totalCollected = array_sum(array_column($projects, 'collected'));
        $finalExpenses = array_filter($expenses, fn (array $row) => $row['status'] === 'FINAL_LEDGER');

        return view('researcher.dashboard', [
            'user' => $user,
            'projects' => $projects,
            'activeProjects' => array_values($activeProjects),
            'draftCount' => count($drafts),
            'totalCollected' => $totalCollected,
            'finalExpenseTotal' => array_sum(array_column($finalExpenses, 'amount')),
            'pendingMilestones' => $this->pendingMilestoneCount($projects),
            'verificationStatus' => $user['verification_status'] ?? 'menunggu_verifikasi',
            'activeMenu' => 'dashboard',
        ]);
    }

    public function profile(Request $request)
    {
        return view('researcher.profile', [
            'user' => $this->user($request),
            'activeMenu' => 'profile',
        ]);
    }

    public function proposals(Request $request)
    {
        $user = $this->user($request);
        $projects = ResearcherPrototypeData::projects($request, $user);
        $status = (string) $request->query('status', '');
        $query = trim((string) $request->query('q', ''));

        if ($status !== '') {
            $projects = array_values(array_filter($projects, fn (array $project) => $project['proposal_status'] === $status || $project['campaign_status'] === $status));
        }
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $projects = array_values(array_filter($projects, fn (array $project) => str_contains(mb_strtolower($project['title'].' '.$project['abstract']), $needle)));
        }

        return view('researcher.proposals.index', compact('user', 'projects', 'status', 'query'))->with('activeMenu', 'proposals');
    }

    public function proposalCreate(Request $request)
    {
        return view('researcher.proposals.create', [
            'user' => $this->user($request),
            'activeMenu' => 'proposals',
        ]);
    }

    public function proposalStore(Request $request)
    {
        $user = $this->user($request);
        $this->ensureVerified($user);

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:12', 'max:180'],
            'category' => ['required', 'in:Energi Terbarukan,Lingkungan,Kesehatan,Teknologi Pertanian,Pendidikan,Lainnya'],
            'abstract' => ['required', 'string', 'min:20', 'max:3000'],
            'target' => ['required', 'integer', 'min:1'],
            'campaign_duration' => ['required', 'integer', 'min:15', 'max:90'],
            'milestones' => ['required', 'array', 'min:2', 'max:5'],
            'milestones.*.name' => ['required', 'string', 'min:5', 'max:120'],
            'milestones.*.description' => ['required', 'string', 'min:10', 'max:500'],
            'milestones.*.allocation' => ['required', 'numeric', 'gt:0', 'lte:100'],
        ], [
            'title.required' => 'Judul proposal wajib diisi.',
            'title.min' => 'Judul proposal minimal 12 karakter.',
            'abstract.required' => 'Abstrak atau ringkasan penelitian wajib diisi.',
            'abstract.min' => 'Ringkasan penelitian minimal 20 karakter.',
            'target.min' => 'Target pendanaan harus lebih besar dari nol.',
            'campaign_duration.min' => 'Durasi kampanye minimal 15 hari.',
            'campaign_duration.max' => 'Durasi kampanye maksimal 90 hari.',
            'milestones.min' => 'Proposal harus memiliki minimal 2 milestone.',
            'milestones.max' => 'Proposal maksimal memiliki 5 milestone.',
            'milestones.*.allocation.required' => 'Persentase alokasi setiap milestone wajib diisi.',
        ]);

        $allocationTotal = array_sum(array_map(fn ($milestone) => (float) $milestone['allocation'], $validated['milestones']));
        if (abs($allocationTotal - 100.0) > 0.001) {
            throw ValidationException::withMessages([
                'milestones' => 'Total alokasi seluruh milestone harus tepat 100%. Total yang Anda masukkan saat ini '.rtrim(rtrim(number_format($allocationTotal, 2, ',', '.'), '0'), ',').'%.',
            ]);
        }

        $projects = ResearcherPrototypeData::projects($request, $user);
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 2;
        while (collect($projects)->contains('slug', $slug)) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $milestones = [];
        $allocatedAmount = 0;
        $milestoneRows = array_values($validated['milestones']);
        foreach ($milestoneRows as $index => $milestone) {
            $allocation = (float) $milestone['allocation'];
            // Selisih pembulatan rupiah ditempatkan pada milestone terakhir agar
            // total nominal seluruh milestone selalu sama persis dengan target.
            $amount = $index === count($milestoneRows) - 1
                ? ((int) $validated['target'] - $allocatedAmount)
                : (int) round(((int) $validated['target']) * $allocation / 100);
            if ($amount < 1000) {
                throw ValidationException::withMessages([
                    'milestones' => 'Setiap milestone harus memiliki alokasi nominal minimal Rp1.000. Sesuaikan target atau persentase pembagian dana.',
                ]);
            }
            $allocatedAmount += $amount;
            $milestones[] = [
                'number' => $index + 1,
                'name' => trim($milestone['name']),
                'description' => trim($milestone['description']),
                'allocation' => $allocation,
                'amount' => $amount,
                'status' => 'LOCKED',
                'status_label' => 'Menunggu kampanye disetujui',
                'review_status' => 'NOT_SUBMITTED',
                'reviewers' => [],
                'sptjm_signed' => false,
                'financial_accountability_submitted' => false,
                'payout_reference' => null,
                'submitted_at' => null,
            ];
        }

        $projects[] = [
            'slug' => $slug,
            'title' => trim($validated['title']),
            'abstract' => trim($validated['abstract']),
            'category' => $validated['category'],
            'target' => (int) $validated['target'],
            'collected' => 0,
            'campaign_duration' => (int) $validated['campaign_duration'],
            'campaign_status' => 'DRAFT',
            'campaign_status_label' => 'Belum dipublikasikan',
            'proposal_status' => 'DRAFT',
            'proposal_status_label' => 'Draft',
            'deadline' => null,
            'created_at' => now()->translatedFormat('d F Y'),
            'sptjm_signed' => false,
            'milestones' => $milestones,
        ];
        ResearcherPrototypeData::saveProjects($request, $user, $projects);

        return redirect()->route('researcher.proposals.show', ['slug' => $slug])
            ->with('success', 'Draft proposal berhasil disimpan pada sesi prototype. Periksa kembali informasi sebelum diajukan untuk validasi Administrator.');
    }

    public function proposalShow(Request $request, string $slug)
    {
        $user = $this->user($request);
        $project = $this->findProject($request, $user, $slug);

        return view('researcher.proposals.show', compact('user', 'project'))->with('activeMenu', 'proposals');
    }

    public function proposalSubmit(Request $request, string $slug)
    {
        $user = $this->user($request);
        $this->ensureVerified($user);
        $projects = ResearcherPrototypeData::projects($request, $user);
        $index = $this->projectIndex($projects, $slug);
        abort_unless($index !== null, 404);

        if (($projects[$index]['proposal_status'] ?? '') !== 'DRAFT') {
            return back()->with('info', 'Proposal ini tidak lagi berstatus Draft sehingga tidak dapat diajukan ulang dari halaman ini.');
        }

        $projects[$index]['proposal_status'] = 'SUBMITTED';
        $projects[$index]['proposal_status_label'] = 'Menunggu validasi Administrator';
        $projects[$index]['submitted_for_validation_at'] = now()->translatedFormat('d F Y H:i');
        ResearcherPrototypeData::saveProjects($request, $user, $projects);

        return redirect()->route('researcher.proposals.show', ['slug' => $slug])
            ->with('success', 'Proposal dikirim untuk validasi Administrator. Kampanye belum tampil di area publik sampai status persetujuan diterima.');
    }

    public function milestones(Request $request)
    {
        $user = $this->user($request);
        $projects = ResearcherPrototypeData::projects($request, $user);

        return view('researcher.milestones.index', compact('user', 'projects'))->with('activeMenu', 'milestones');
    }

    public function milestoneSubmit(Request $request, string $slug, int $number)
    {
        $user = $this->user($request);
        $projects = ResearcherPrototypeData::projects($request, $user);
        $projectIndex = $this->projectIndex($projects, $slug);
        abort_unless($projectIndex !== null, 404);
        $project = $projects[$projectIndex];
        abort_unless(($project['campaign_status'] ?? '') === 'FUNDED', 403, 'Pengajuan pencairan hanya tersedia setelah kampanye mencapai target pendanaan 100%.');

        $milestoneIndex = $this->milestoneIndex($project['milestones'] ?? [], $number);
        abort_unless($milestoneIndex !== null, 404);
        $milestone = $project['milestones'][$milestoneIndex];
        if (in_array($milestone['status'], ['SUBMITTED', 'UNDER_REVIEW', 'APPROVED', 'DISBURSED'], true)) {
            return back()->with('info', 'Milestone ini sudah diajukan atau telah diproses. Pengajuan ganda tidak diperbolehkan.');
        }

        // Check reviewer capacity before accepting/uploading submission files.
        ReviewerPrototypeData::assertReviewerPanelAvailable($request, (string) ($user['institution'] ?? ''));

        if ($number === 1) {
            $validated = $request->validate([
                'sptjm_signed' => ['accepted'],
                'requested_amount' => ['required', 'integer', 'min:1000'],
            ], [
                'sptjm_signed.accepted' => 'SPTJM digital wajib ditandatangani sebelum pencairan awal.',
                'requested_amount.required' => 'Nominal pengajuan pencairan awal wajib diisi.',
            ]);
            $maximumInitialPayout = (int) floor(((int) $project['collected']) * 0.30);
            if ((int) $validated['requested_amount'] > $maximumInitialPayout || (int) $validated['requested_amount'] > (int) $milestone['amount']) {
                throw ValidationException::withMessages([
                    'requested_amount' => 'Pencairan awal tidak boleh melebihi 30% dana terkumpul atau alokasi milestone ini. Batas maksimum saat ini Rp'.number_format(min($maximumInitialPayout, (int) $milestone['amount']), 0, ',', '.').'.',
                ]);
            }
            $projects[$projectIndex]['sptjm_signed'] = true;
            $projects[$projectIndex]['milestones'][$milestoneIndex]['sptjm_signed'] = true;
            $projects[$projectIndex]['milestones'][$milestoneIndex]['requested_amount'] = (int) $validated['requested_amount'];
        } else {
            $previous = $project['milestones'][$milestoneIndex - 1] ?? null;
            if (! $previous || ($previous['status'] ?? '') !== 'DISBURSED') {
                throw ValidationException::withMessages(['deliverable' => 'Milestone sebelumnya harus selesai dan pencairannya telah tercatat sebelum termin berikutnya diajukan.']);
            }
            $validated = $request->validate([
                'deliverable' => ['required', 'file', 'mimes:pdf', 'max:10240'],
                'financial_accountability' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            ], [
                'deliverable.required' => 'Laporan luaran teknis PDF wajib dilampirkan.',
                'deliverable.mimes' => 'Laporan luaran harus berupa file PDF.',
                'financial_accountability.required' => 'Bukti pertanggungjawaban keuangan milestone sebelumnya wajib dilampirkan.',
                'financial_accountability.mimes' => 'Bukti pertanggungjawaban harus berupa file PDF.',
            ]);

            $deliverablePath = $validated['deliverable']->store('prototype/researcher-deliverables', 'local');
            $accountabilityPath = $validated['financial_accountability']->store('prototype/researcher-accountability', 'local');
            $projects[$projectIndex]['milestones'][$milestoneIndex]['deliverable_path'] = $deliverablePath;
            $projects[$projectIndex]['milestones'][$milestoneIndex]['deliverable_name'] = $validated['deliverable']->getClientOriginalName();
            $projects[$projectIndex]['milestones'][$milestoneIndex]['accountability_path'] = $accountabilityPath;
            $projects[$projectIndex]['milestones'][$milestoneIndex]['accountability_name'] = $validated['financial_accountability']->getClientOriginalName();
            $projects[$projectIndex]['milestones'][$milestoneIndex - 1]['financial_accountability_submitted'] = true;
        }

        $projects[$projectIndex]['milestones'][$milestoneIndex]['status'] = 'SUBMITTED';
        $projects[$projectIndex]['milestones'][$milestoneIndex]['status_label'] = 'Menunggu evaluasi reviewer';
        $projects[$projectIndex]['milestones'][$milestoneIndex]['review_status'] = 'UNDER_REVIEW';
        $projects[$projectIndex]['milestones'][$milestoneIndex]['submitted_at'] = now()->translatedFormat('d F Y H:i');
        $assignment = ReviewerPrototypeData::registerSubmission(
            $request,
            $projects[$projectIndex],
            $projects[$projectIndex]['milestones'][$milestoneIndex],
            $user
        );
        $projects[$projectIndex]['milestones'][$milestoneIndex]['reviewers'] = array_map(
            fn (array $reviewer) => [
                'id' => $reviewer['id'],
                'label' => $reviewer['name'],
                'status' => 'PENDING',
                'status_label' => 'Menunggu penilaian',
            ],
            $assignment['assigned_reviewers'] ?? []
        );
        $projects[$projectIndex]['milestones'][$milestoneIndex]['review_summary'] = $assignment['aggregate'] ?? [
            'key' => 'unassigned', 'label' => 'Menunggu penugasan dua reviewer independen', 'payout_ready' => false,
        ];
        ResearcherPrototypeData::saveProjects($request, $user, $projects);

        return redirect()->route('researcher.milestones')
            ->with('success', 'Pengajuan milestone tercatat. Pencairan hanya diproses setelah seluruh reviewer independen memberikan keputusan Disetujui.');
    }

    public function expenses(Request $request)
    {
        $user = $this->user($request);
        $projects = ResearcherPrototypeData::projects($request, $user);
        $expenses = ResearcherPrototypeData::expenses($request, $user);
        $projectSlug = (string) $request->query('proyek', '');
        $category = (string) $request->query('kategori', '');

        if ($projectSlug !== '') {
            $expenses = array_values(array_filter($expenses, fn (array $row) => $row['project_slug'] === $projectSlug));
        }
        if ($category !== '') {
            $expenses = array_values(array_filter($expenses, fn (array $row) => $row['category'] === $category));
        }
        $totalFinal = array_sum(array_column(array_filter($expenses, fn (array $row) => $row['status'] === 'FINAL_LEDGER'), 'amount'));
        $eligibleProjects = array_values(array_filter($projects, fn (array $project) => $project['campaign_status'] === 'FUNDED'));

        return view('researcher.expenses.index', compact('user', 'projects', 'eligibleProjects', 'expenses', 'projectSlug', 'category', 'totalFinal'))->with('activeMenu', 'expenses');
    }

    public function expenseStore(Request $request)
    {
        $user = $this->user($request);
        $projects = ResearcherPrototypeData::projects($request, $user);
        $validated = $request->validate([
            'project_slug' => ['required', 'string', 'max:120'],
            'milestone_number' => ['required', 'integer', 'min:1', 'max:5'],
            'date' => ['required', 'date'],
            'recipient' => ['required', 'string', 'min:3', 'max:150'],
            'category' => ['required', 'in:ALAT,BAHAN_HABIS_PAKAI,OPERASIONAL_LAPANGAN,PUBLIKASI'],
            'description' => ['required', 'string', 'min:10', 'max:500'],
            'amount' => ['required', 'integer', 'min:1000'],
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ], [
            'project_slug.required' => 'Pilih proyek yang akan dicatat pengeluarannya.',
            'date.required' => 'Tanggal pengeluaran wajib diisi.',
            'recipient.required' => 'Nama penerima atau toko wajib diisi.',
            'category.required' => 'Pilih pos kategori belanja.',
            'description.min' => 'Deskripsi pengeluaran minimal 10 karakter.',
            'amount.min' => 'Nominal pengeluaran minimal Rp1.000.',
            'receipt.required' => 'Kuitansi atau faktur wajib dilampirkan.',
            'receipt.mimes' => 'Bukti transaksi harus berupa PDF, JPG, JPEG, atau PNG.',
            'receipt.max' => 'Ukuran kuitansi maksimal 8 MB.',
        ]);

        $projectIndex = $this->projectIndex($projects, $validated['project_slug']);
        abort_unless($projectIndex !== null, 404);
        $project = $projects[$projectIndex];
        abort_unless($project['campaign_status'] === 'FUNDED', 403, 'Pencatatan pengeluaran hanya tersedia untuk proyek yang telah mencapai target pendanaan.');
        $milestoneIndex = $this->milestoneIndex($project['milestones'] ?? [], (int) $validated['milestone_number']);
        abort_unless($milestoneIndex !== null, 404);
        $milestone = $project['milestones'][$milestoneIndex];
        abort_unless(($milestone['status'] ?? '') === 'DISBURSED', 403, 'Pengeluaran hanya dicatat terhadap milestone yang dananya telah dicairkan.');

        $expenses = ResearcherPrototypeData::expenses($request, $user);
        $alreadyRecorded = array_sum(array_map(
            fn (array $row) => ($row['project_slug'] === $project['slug'] && (int) $row['milestone_number'] === (int) $validated['milestone_number']) ? (int) $row['amount'] : 0,
            $expenses
        ));
        $availableAmount = (int) ($milestone['disbursed_amount'] ?? $milestone['requested_amount'] ?? $milestone['amount']);
        $remainingAmount = max(0, $availableAmount - $alreadyRecorded);
        if ((int) $validated['amount'] > $remainingAmount) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pengeluaran melebihi sisa dana milestone. Sisa yang dapat dicatat Rp'.number_format($remainingAmount, 0, ',', '.').'.',
            ]);
        }

        $receipt = $validated['receipt'];
        $path = $receipt->store('prototype/researcher-receipts', 'local');
        $categoryLabels = [
            'ALAT' => 'Alat penelitian',
            'BAHAN_HABIS_PAKAI' => 'Bahan habis pakai',
            'OPERASIONAL_LAPANGAN' => 'Operasional lapangan',
            'PUBLIKASI' => 'Publikasi',
        ];
        $id = 'EXP-'.now()->format('YmdHis').'-'.random_int(10, 99);
        $expenses[] = [
            'id' => $id,
            'project_slug' => $project['slug'],
            'project_title' => $project['title'],
            'milestone_number' => (int) $validated['milestone_number'],
            'date' => $validated['date'],
            'recipient' => trim($validated['recipient']),
            'category' => $validated['category'],
            'category_label' => $categoryLabels[$validated['category']],
            'description' => trim($validated['description']),
            'amount' => (int) $validated['amount'],
            'receipt_number' => 'KWT-'.now()->format('Ymd').'-'.substr(sha1($id), 0, 5),
            'receipt_name' => $receipt->getClientOriginalName(),
            'receipt_path' => $path,
            'hash' => hash_file('sha256', $receipt->getRealPath()),
            'status' => 'DRAFT',
            'status_label' => 'Draft · Belum dipublikasikan',
            'created_at' => now()->translatedFormat('d F Y H:i'),
        ];
        ResearcherPrototypeData::saveExpenses($request, $user, $expenses);

        return redirect()->route('researcher.expenses', ['proyek' => $project['slug']])
            ->with('success', 'Bukti pengeluaran tersimpan sebagai draft. Publikasikan ke Buku Besar Riset setelah memastikan datanya benar.');
    }

    public function expensePublish(Request $request, string $id)
    {
        $user = $this->user($request);
        $expenses = ResearcherPrototypeData::expenses($request, $user);
        $found = false;
        foreach ($expenses as &$expense) {
            if ($expense['id'] === $id) {
                abort_unless($expense['status'] === 'DRAFT', 403, 'Entri Final Ledger bersifat read-only dan tidak dapat diubah atau dihapus sepihak.');
                $expense['status'] = 'FINAL_LEDGER';
                $expense['status_label'] = 'Final Ledger · Terkunci';
                $expense['published_at'] = now()->utc()->format('Y-m-d H:i:s').' UTC';
                $found = true;
                break;
            }
        }
        unset($expense);
        abort_unless($found, 404);
        ResearcherPrototypeData::saveExpenses($request, $user, $expenses);

        return redirect()->route('researcher.expenses')->with('success', 'Entri dipublikasikan ke Buku Besar Riset dan sekarang bersifat read-only (append-only).');
    }

    public function expenseReceipt(Request $request, string $id): StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = $this->user($request);
        $expense = collect(ResearcherPrototypeData::expenses($request, $user))->firstWhere('id', $id);
        abort_unless($expense, 404);
        abort_unless(! empty($expense['receipt_path']) && Storage::disk('local')->exists($expense['receipt_path']), 404, 'Bukti ini merupakan dokumen ilustratif dan tidak memiliki berkas asli.');

        return Storage::disk('local')->download($expense['receipt_path'], $expense['receipt_name']);
    }

    private function user(Request $request): array
    {
        return $request->session()->get('prototype_user', []);
    }

    private function ensureVerified(array $user): void
    {
        if (($user['verification_status'] ?? '') !== 'terverifikasi') {
            throw ValidationException::withMessages([
                'verification' => 'Profil akademik Anda masih menunggu validasi Administrator/SSO. Pengajuan proposal belum dapat dilakukan sebelum identitas dinyatakan terverifikasi.',
            ]);
        }
    }

    private function projectIndex(array $projects, string $slug): ?int
    {
        foreach ($projects as $index => $project) {
            if (($project['slug'] ?? null) === $slug) {
                return $index;
            }
        }

        return null;
    }

    private function findProject(Request $request, array $user, string $slug): array
    {
        $projects = ResearcherPrototypeData::projects($request, $user);
        $index = $this->projectIndex($projects, $slug);
        abort_unless($index !== null, 404);

        return $projects[$index];
    }

    private function milestoneIndex(array $milestones, int $number): ?int
    {
        foreach ($milestones as $index => $milestone) {
            if ((int) ($milestone['number'] ?? 0) === $number) {
                return $index;
            }
        }

        return null;
    }

    private function pendingMilestoneCount(array $projects): int
    {
        $count = 0;
        foreach ($projects as $project) {
            foreach ($project['milestones'] ?? [] as $milestone) {
                if (in_array($milestone['status'] ?? '', ['ACTIVE', 'SUBMITTED', 'UNDER_REVIEW'], true)) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
