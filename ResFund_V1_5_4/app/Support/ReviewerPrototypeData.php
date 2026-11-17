<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Data simulasi untuk penugasan dan penilaian Reviewer.
 * Dua reviewer menilai pengajuan yang sama secara independen.
 */
class ReviewerPrototypeData
{
    public const SESSION_KEY = 'prototype_reviewer_assignments';

    public static function reviewers(): array
    {
        return [
            'reviewer_a' => [
                'id' => 'reviewer_a',
                'name' => 'Dr. Rina Kurniawati',
                'email' => 'reviewer@resfund.test',
                'expertise' => 'Instrumentasi lingkungan dan sensor',
                'institution' => 'Universitas Sriwijaya',
            ],
            'reviewer_b' => [
                'id' => 'reviewer_b',
                'name' => 'Prof. Budi Santoso',
                'email' => 'reviewer2@resfund.test',
                'expertise' => 'Rekayasa sistem dan metodologi penelitian',
                'institution' => 'Institut Teknologi Sumatera',
            ],
        ];
    }

    public static function reviewerCatalog(Request $request): array
    {
        $catalog = self::reviewers();
        foreach ($request->session()->get('prototype_admin_added_reviewers', []) as $id => $reviewer) {
            $catalog[$id] = $reviewer;
        }
        $statuses = $request->session()->get('prototype_admin_reviewer_status', []);
        foreach ($catalog as $id => &$reviewer) {
            $reviewer['status'] = $statuses[$id] ?? ($reviewer['status'] ?? 'active');
        }
        unset($reviewer);
        return $catalog;
    }

    public static function reviewerForEmail(string $email, ?Request $request = null): ?array
    {
        $catalog = $request ? self::reviewerCatalog($request) : self::reviewers();
        foreach ($catalog as $reviewer) {
            if (strtolower($reviewer['email']) === strtolower($email)) {
                return $reviewer;
            }
        }
        return null;
    }

    public static function assignments(Request $request): array
    {
        if (! $request->session()->has(self::SESSION_KEY)) {
            $request->session()->put(self::SESSION_KEY, self::seedAssignments($request));
        }

        $assignments = array_values($request->session()->get(self::SESSION_KEY, []));
        $changed = false;
        $reviewerCatalog = self::reviewerCatalog($request);
        foreach ($assignments as $index => $assignment) {
            // Recompute the aggregate on every read so legacy or malformed session data
            // cannot leave a stale payout-ready flag behind. A panel with inactive members
            // must be reassigned before it can accept another decision.
            $rawReviewerIds = array_values(array_map('strval', $assignment['assigned_reviewer_ids'] ?? []));
            $reviewerIds = array_values(array_unique($rawReviewerIds));
            $panelActive = count($rawReviewerIds) === 2 && count($reviewerIds) === 2;
            foreach ($reviewerIds as $reviewerId) {
                if (! isset($reviewerCatalog[$reviewerId]) || ($reviewerCatalog[$reviewerId]['status'] ?? 'active') !== 'active') {
                    $panelActive = false;
                    break;
                }
            }
            // A finalized panel remains an immutable record even if a reviewer is
            // deactivated later. Current activity status only invalidates unfinished panels.
            $isFinalized = ! empty($assignment['aggregate']['completed']);
            $aggregate = ($panelActive || $isFinalized)
                ? self::aggregate($assignment)
                : ['key' => 'unassigned', 'label' => 'Menunggu penugasan dua reviewer aktif', 'payout_ready' => false, 'completed' => false];
            if (($assignment['aggregate'] ?? []) !== $aggregate) {
                $assignments[$index]['aggregate'] = $aggregate;
                $changed = true;
                self::syncResearcherMilestone($request, $assignments[$index]);
            }
        }
        if ($changed) {
            $request->session()->put(self::SESSION_KEY, $assignments);
        }

        return $assignments;
    }

    public static function assertReviewerPanelAvailable(Request $request, string $researcherInstitution): void
    {
        $panel = self::initialReviewerPanel($request, $researcherInstitution);
        abort_unless(count($panel) === 2, 422, 'Belum tersedia dua reviewer aktif dari institusi berbeda yang memenuhi syarat. Pengajuan belum dapat diteruskan.');
    }

    public static function assignmentsFor(Request $request, string $reviewerId): array
    {
        return array_values(array_filter(
            self::assignments($request),
            fn (array $assignment): bool => in_array($reviewerId, $assignment['assigned_reviewer_ids'] ?? [], true)
        ));
    }

    public static function findForReviewer(Request $request, string $assignmentId, string $reviewerId): ?array
    {
        foreach (self::assignmentsFor($request, $reviewerId) as $assignment) {
            if (hash_equals((string) $assignment['id'], $assignmentId)) {
                return $assignment;
            }
        }

        return null;
    }

    public static function reviewerDecision(array $assignment, string $reviewerId): ?array
    {
        return $assignment['reviews'][$reviewerId] ?? null;
    }

    public static function saveReview(Request $request, string $assignmentId, string $reviewerId, array $review): array
    {
        $assignments = self::assignments($request);
        foreach ($assignments as $index => $assignment) {
            if (! hash_equals((string) $assignment['id'], $assignmentId)) {
                continue;
            }

            abort_unless(in_array($reviewerId, $assignment['assigned_reviewer_ids'] ?? [], true), 403);
            abort_if(count($assignment['assigned_reviewer_ids'] ?? []) !== 2 || ($assignment['aggregate']['key'] ?? '') === 'unassigned', 409, 'Penilaian dikunci sampai Administrator menetapkan tepat dua reviewer independen.');
            abort_if(($assignment['aggregate']['key'] ?? '') === 'conflict', 409, 'Penugasan sedang diblokir karena konflik kepentingan dan menunggu penugasan ulang Administrator.');
            abort_if(! empty($assignment['aggregate']['completed']), 409, 'Keputusan panel sudah final dan tidak dapat diubah.');
            abort_if(! empty($assignment['reviews'][$reviewerId]['decision']), 409, 'Penilaian Anda sudah dikirim dan dikunci.');
            abort_if(! empty($assignment['reviews'][$reviewerId]['conflict_reported']), 409, 'Penugasan ini sudah Anda tandai memiliki konflik kepentingan.');

            $review['reviewer_id'] = $reviewerId;
            $review['reviewer_name'] = self::reviewerCatalog($request)[$reviewerId]['name'] ?? 'Reviewer';
            $review['submitted_at'] = now()->format('d M Y, H:i');
            $review['decision_label'] = self::decisionLabel($review['decision']);
            $assignments[$index]['reviews'][$reviewerId] = $review;
            $assignments[$index]['aggregate'] = self::aggregate($assignments[$index]);
            $assignments[$index]['updated_at'] = now()->format('d M Y, H:i');
            $request->session()->put(self::SESSION_KEY, array_values($assignments));
            self::syncResearcherMilestone($request, $assignments[$index]);

            return $assignments[$index];
        }

        abort(404);
    }

    public static function reportConflict(Request $request, string $assignmentId, string $reviewerId, string $reason): array
    {
        $assignments = self::assignments($request);
        foreach ($assignments as $index => $assignment) {
            if (! hash_equals((string) $assignment['id'], $assignmentId)) {
                continue;
            }

            abort_unless(in_array($reviewerId, $assignment['assigned_reviewer_ids'] ?? [], true), 403);
            abort_if(($assignment['aggregate']['key'] ?? '') === 'conflict', 409, 'Panel sudah diblokir karena konflik kepentingan. Tunggu penugasan ulang Administrator.');
            abort_if(! empty($assignment['reviews'][$reviewerId]['conflict_reported']), 409, 'Konflik kepentingan Anda sudah tercatat.');
            abort_if(! empty($assignment['reviews'][$reviewerId]['decision']), 409, 'Penilaian sudah dikirim; konflik tidak dapat dilaporkan setelah keputusan final.');

            $assignments[$index]['reviews'][$reviewerId] = [
                'reviewer_id' => $reviewerId,
                'reviewer_name' => self::reviewerCatalog($request)[$reviewerId]['name'] ?? 'Reviewer',
                'conflict_reported' => true,
                'conflict_reason' => $reason,
                'submitted_at' => now()->format('d M Y, H:i'),
            ];
            $assignments[$index]['aggregate'] = self::aggregate($assignments[$index]);
            $request->session()->put(self::SESSION_KEY, array_values($assignments));
            self::syncResearcherMilestone($request, $assignments[$index]);

            return $assignments[$index];
        }

        abort(404);
    }

    public static function reassignPanel(Request $request, string $assignmentId, array $reviewerIds): array
    {
        $reviewerIds = array_values(array_map('strval', $reviewerIds));
        abort_unless(count($reviewerIds) === 2 && count(array_unique($reviewerIds)) === 2, 422, 'Penugasan harus berisi tepat dua reviewer independen yang berbeda.');
        $catalog = self::reviewerCatalog($request);
        $selected = [];
        foreach ($reviewerIds as $reviewerId) {
            abort_unless(isset($catalog[$reviewerId]), 422, 'Reviewer yang dipilih tidak tersedia.');
            $reviewer = $catalog[$reviewerId];
            abort_unless(($reviewer['status'] ?? 'active') === 'active', 422, 'Reviewer nonaktif tidak dapat ditugaskan.');
            $selected[] = $reviewer;
        }
        $assignmentRows = self::assignments($request);
        foreach ($assignmentRows as $index => $assignment) {
            if (! hash_equals((string) $assignment['id'], $assignmentId)) {
                continue;
            }
            abort_if(! empty($assignment['aggregate']['completed']), 409, 'Keputusan panel sudah final. Penugasan ulang tidak boleh menghapus atau mengganti keputusan yang telah dikirim. Jika ada keberatan, buat proses pengajuan revisi baru sesuai alur sistem.');
            $researcherInstitution = mb_strtolower(trim((string) ($assignment['institution'] ?? '')));
            $conflictedReviewerIds = array_keys(array_filter($assignment['reviews'] ?? [], fn (array $review) => ! empty($review['conflict_reported'])));
            abort_if(array_intersect($reviewerIds, $conflictedReviewerIds) !== [], 422, 'Reviewer yang melaporkan konflik kepentingan tidak dapat ditugaskan kembali ke pengajuan yang sama.');
            $institutions = array_map(fn (array $r) => mb_strtolower(trim((string) ($r['institution'] ?? ''))), $selected);
            abort_if(in_array($researcherInstitution, $institutions, true), 422, 'Reviewer tidak boleh berasal dari institusi peneliti.');
            abort_if(count(array_unique($institutions)) !== 2, 422, 'Dua reviewer harus berasal dari institusi yang berbeda.');

            $history = $assignment['panel_history'] ?? [];
            $history[] = [
                'reviewer_ids' => $assignment['assigned_reviewer_ids'] ?? [],
                'reviews' => $assignment['reviews'] ?? [],
                'changed_at' => now()->format('d M Y, H:i'),
            ];
            $assignmentRows[$index]['panel_history'] = $history;
            $assignmentRows[$index]['assigned_reviewer_ids'] = $reviewerIds;
            $assignmentRows[$index]['assigned_reviewers'] = array_map(fn (array $r) => [
                'id' => $r['id'], 'name' => $r['name'], 'institution' => $r['institution'], 'expertise' => $r['expertise'] ?? '',
            ], $selected);
            $assignmentRows[$index]['reviews'] = [];
            $assignmentRows[$index]['aggregate'] = [
                'key' => 'pending', 'label' => 'Menunggu penilaian dua reviewer', 'payout_ready' => false, 'completed' => false,
            ];
            $assignmentRows[$index]['revision_round'] = ((int) ($assignment['revision_round'] ?? 1)) + 1;
            $assignmentRows[$index]['updated_at'] = now()->format('d M Y, H:i');
            $request->session()->put(self::SESSION_KEY, array_values($assignmentRows));
            self::syncResearcherMilestone($request, $assignmentRows[$index]);
            return $assignmentRows[$index];
        }
        abort(404);
    }

    public static function registerSubmission(Request $request, array $project, array $milestone, array $researcher): array
    {
        $assignments = self::assignments($request);
        $number = (int) ($milestone['number'] ?? 0);
        $slug = (string) ($project['slug'] ?? '');
        foreach ($assignments as $existingIndex => $existing) {
            if (($existing['project_slug'] ?? '') === $slug
                && (int) ($existing['milestone_number'] ?? 0) === $number
                && empty($existing['reviews'])) {
                $assignments[$existingIndex]['project_title'] = $project['title'] ?? $existing['project_title'];
                $assignments[$existingIndex]['researcher_name'] = $researcher['name'] ?? $existing['researcher_name'];
                $assignments[$existingIndex]['researcher_email'] = $researcher['email'] ?? $existing['researcher_email'];
                $assignments[$existingIndex]['institution'] = $researcher['institution'] ?? $existing['institution'];
                $assignments[$existingIndex]['milestone_name'] = $milestone['name'] ?? $existing['milestone_name'];
                $assignments[$existingIndex]['milestone_description'] = $milestone['description'] ?? $existing['milestone_description'];
                $assignments[$existingIndex]['allocation'] = (float) ($milestone['allocation'] ?? $existing['allocation']);
                $assignments[$existingIndex]['requested_amount'] = (int) ($milestone['requested_amount'] ?? $milestone['amount'] ?? $existing['requested_amount']);
                $assignments[$existingIndex]['target_output'] = $milestone['description'] ?? $existing['target_output'];
                $assignments[$existingIndex]['submitted_at'] = now()->format('d M Y, H:i');
                $assignments[$existingIndex]['submitted_iso'] = now()->toDateString();
                $assignments[$existingIndex]['due_at'] = now()->addDays(7)->toDateString();
                $assignments[$existingIndex]['documents'] = self::documentsFromMilestone($milestone);
                $existingPanelIds = array_values(array_unique(array_map('strval', $assignments[$existingIndex]['assigned_reviewer_ids'] ?? [])));
                $catalog = self::reviewerCatalog($request);
                $existingPanelActive = count($existingPanelIds) === 2;
                foreach ($existingPanelIds as $existingReviewerId) {
                    if (! isset($catalog[$existingReviewerId]) || ($catalog[$existingReviewerId]['status'] ?? 'active') !== 'active') {
                        $existingPanelActive = false;
                        break;
                    }
                }
                if (! $existingPanelActive
                    || ($assignments[$existingIndex]['aggregate']['key'] ?? '') === 'unassigned') {
                    $panel = self::initialReviewerPanel($request, (string) ($researcher['institution'] ?? ''));
                    abort_unless(count($panel) === 2, 422, 'Pengajuan belum dapat diteruskan karena belum tersedia dua reviewer aktif dari institusi berbeda.');
                    $assignments[$existingIndex]['assigned_reviewer_ids'] = array_column($panel, 'id');
                    $assignments[$existingIndex]['assigned_reviewers'] = array_map(fn (array $r) => [
                        'id' => $r['id'], 'name' => $r['name'], 'institution' => $r['institution'], 'expertise' => $r['expertise'] ?? '',
                    ], $panel);
                    $assignments[$existingIndex]['aggregate'] = ['key' => 'pending', 'label' => 'Menunggu penilaian dua reviewer', 'payout_ready' => false, 'completed' => false];
                }
                $request->session()->put(self::SESSION_KEY, array_values($assignments));
                return $assignments[$existingIndex];
            }
        }
        $round = 1;
        foreach ($assignments as $existing) {
            if (($existing['project_slug'] ?? '') === $slug && (int) ($existing['milestone_number'] ?? 0) === $number) {
                $round = max($round, ((int) ($existing['revision_round'] ?? 1)) + 1);
            }
        }

        $id = 'ASG-'.strtoupper(substr(sha1($slug.'-'.$number.'-'.$round), 0, 8));
        $assigned = self::initialReviewerPanel($request, (string) ($researcher['institution'] ?? ''));
        abort_unless(count($assigned) === 2, 422, 'Pengajuan belum dapat diteruskan karena belum tersedia dua reviewer aktif dari institusi berbeda.');
        $assignment = [
            'id' => $id,
            'project_slug' => $slug,
            'project_title' => $project['title'] ?? 'Proyek penelitian',
            'researcher_name' => $researcher['name'] ?? 'Peneliti',
            'researcher_email' => $researcher['email'] ?? null,
            'institution' => $researcher['institution'] ?? 'Perguruan tinggi',
            'category' => $project['category'] ?? 'Riset',
            'milestone_number' => $number,
            'milestone_name' => $milestone['name'] ?? 'Milestone',
            'milestone_description' => $milestone['description'] ?? '',
            'allocation' => (float) ($milestone['allocation'] ?? 0),
            'requested_amount' => (int) ($milestone['requested_amount'] ?? $milestone['amount'] ?? 0),
            'target_output' => $milestone['description'] ?? 'Luaran teknis sesuai proposal.',
            'submitted_at' => now()->format('d M Y, H:i'),
            'submitted_iso' => now()->toDateString(),
            'due_at' => now()->addDays(7)->toDateString(),
            'revision_round' => $round,
            'assigned_reviewer_ids' => array_column($assigned, 'id'),
            'assigned_reviewers' => array_map(fn (array $r): array => ['id' => $r['id'], 'name' => $r['name'], 'institution' => $r['institution'], 'expertise' => $r['expertise']], $assigned),
            'documents' => self::documentsFromMilestone($milestone),
            'reviews' => [],
            'aggregate' => ['key' => 'pending', 'label' => 'Menunggu penilaian dua reviewer', 'payout_ready' => false],
            'source' => 'researcher_submission',
        ];

        $assignments[] = $assignment;
        $request->session()->put(self::SESSION_KEY, array_values($assignments));

        return $assignment;
    }

    public static function aggregate(array $assignment): array
    {
        $rawReviewerIds = array_values(array_map('strval', $assignment['assigned_reviewer_ids'] ?? []));
        $reviewerIds = array_values(array_unique($rawReviewerIds));
        $reviewers = $assignment['assigned_reviewers'] ?? [];
        $reviewerMap = [];
        foreach ($reviewers as $reviewer) {
            if (! empty($reviewer['id'])) {
                $reviewerMap[(string) $reviewer['id']] = $reviewer;
            }
        }
        $researcherInstitution = mb_strtolower(trim((string) ($assignment['institution'] ?? '')));
        $reviewerInstitutions = array_map(
            fn (string $reviewerId) => mb_strtolower(trim((string) ($reviewerMap[$reviewerId]['institution'] ?? ''))),
            $reviewerIds
        );
        $validPanel = count($rawReviewerIds) === 2
            && count($reviewerIds) === 2
            && count($reviewerMap) === 2
            && count(array_diff($reviewerIds, array_keys($reviewerMap))) === 0
            && count(array_filter($reviewerInstitutions, fn ($institution) => $institution !== '')) === 2
            && count(array_unique($reviewerInstitutions)) === 2
            && ! in_array($researcherInstitution, $reviewerInstitutions, true);

        if (! $validPanel) {
            return ['key' => 'unassigned', 'label' => 'Menunggu penugasan dua reviewer independen', 'payout_ready' => false, 'completed' => false];
        }

        $reviews = $assignment['reviews'] ?? [];
        $decisions = [];
        $conflicts = [];
        foreach ($reviewerIds as $reviewerId) {
            $entry = $reviews[$reviewerId] ?? [];
            if (! empty($entry['conflict_reported'])) {
                $conflicts[] = $reviewerId;
            }
            if (! empty($entry['decision'])) {
                $decisions[] = $entry['decision'];
            }
        }

        if ($conflicts !== []) {
            return ['key' => 'conflict', 'label' => 'Penugasan perlu diganti oleh Administrator', 'payout_ready' => false, 'completed' => false];
        }

        if (count($decisions) < count($reviewerIds)) {
            $hasDecision = count($decisions) > 0;
            return [
                'key' => $hasDecision ? 'waiting_second' : 'pending',
                'label' => $hasDecision ? 'Menunggu keputusan reviewer lainnya' : 'Menunggu penilaian dua reviewer',
                'payout_ready' => false,
                'completed' => false,
            ];
        }

        if (count($decisions) === count($reviewerIds) && $decisions !== [] && count(array_filter($decisions, fn ($d) => $d === 'approved')) === count($reviewerIds)) {
            return ['key' => 'approved', 'label' => 'Disetujui oleh seluruh reviewer', 'payout_ready' => true, 'completed' => true];
        }
        if (in_array('rejected', $decisions, true)) {
            return ['key' => 'rejected', 'label' => 'Pengajuan ditolak oleh panel reviewer', 'payout_ready' => false, 'completed' => true];
        }
        if (in_array('revision_minor', $decisions, true)) {
            return ['key' => 'revision', 'label' => 'Perlu revisi sebelum pencairan', 'payout_ready' => false, 'completed' => true];
        }

        return ['key' => 'pending', 'label' => 'Menunggu penilaian', 'payout_ready' => false, 'completed' => false];
    }

    public static function decisionLabel(string $decision): string
    {
        return match ($decision) {
            'approved' => 'Disetujui',
            'revision_minor' => 'Revisi Minor',
            'rejected' => 'Ditolak',
            default => 'Belum dinilai',
        };
    }


    private static function initialReviewerPanel(Request $request, string $researcherInstitution): array
    {
        $eligible = array_values(array_filter(self::reviewerCatalog($request), function (array $reviewer) use ($researcherInstitution): bool {
            return ($reviewer['status'] ?? 'active') === 'active'
                && mb_strtolower(trim((string) ($reviewer['institution'] ?? ''))) !== mb_strtolower(trim($researcherInstitution));
        }));
        $selected = [];
        $institutions = [];
        foreach ($eligible as $reviewer) {
            $institution = mb_strtolower(trim((string) ($reviewer['institution'] ?? '')));
            if ($institution === '' || in_array($institution, $institutions, true)) {
                continue;
            }
            $selected[] = $reviewer;
            $institutions[] = $institution;
            if (count($selected) === 2) {
                break;
            }
        }
        return count($selected) === 2 ? $selected : [];
    }

    private static function seedAssignments(Request $request): array
    {
        $researcher = ['email' => ResearcherPrototypeData::DEMO_EMAIL];
        $projects = ResearcherPrototypeData::projects($request, $researcher);
        $assignments = [];

        foreach ($projects as $project) {
            foreach (($project['milestones'] ?? []) as $milestone) {
                if (! in_array($milestone['status'] ?? '', ['SUBMITTED', 'UNDER_REVIEW'], true)) {
                    continue;
                }
                $number = (int) $milestone['number'];
                $id = 'ASG-'.strtoupper(substr(sha1($project['slug'].'-'.$number.'-1'), 0, 8));
                $reviewers = self::initialReviewerPanel($request, (string) ($project['institution'] ?? 'Universitas Lampung'));
                $assignments[] = [
                    'id' => $id,
                    'project_slug' => $project['slug'],
                    'project_title' => $project['title'],
                    'researcher_name' => 'Dr. Andi Prasetyo',
                    'researcher_email' => ResearcherPrototypeData::DEMO_EMAIL,
                    'institution' => 'Universitas Lampung',
                    'category' => $project['category'],
                    'milestone_number' => $number,
                    'milestone_name' => $milestone['name'],
                    'milestone_description' => $milestone['description'],
                    'allocation' => (float) $milestone['allocation'],
                    'requested_amount' => (int) ($milestone['requested_amount'] ?? $milestone['amount']),
                    'target_output' => $milestone['description'],
                    'submitted_at' => $milestone['submitted_at'] ?? '17 Nov 2026, 09:30',
                    'submitted_iso' => '2026-11-17',
                    'due_at' => $milestone['review_due_at'] ?? '2026-11-24',
                    'revision_round' => 1,
                    'assigned_reviewer_ids' => array_column($reviewers, 'id'),
                    'assigned_reviewers' => array_map(fn (array $r): array => ['id' => $r['id'], 'name' => $r['name'], 'institution' => $r['institution'], 'expertise' => $r['expertise']], $reviewers),
                    'documents' => self::documentsFromMilestone($milestone),
                    'reviews' => [],
                    'aggregate' => count($reviewers) === 2 ? ['key' => 'pending', 'label' => 'Menunggu penilaian dua reviewer', 'payout_ready' => false, 'completed' => false] : ['key' => 'unassigned', 'label' => 'Menunggu penugasan dua reviewer independen', 'payout_ready' => false, 'completed' => false],
                    'source' => 'demo_assignment',
                ];
            }
        }

        if ($assignments === []) {
            $assignments = self::fallbackAssignments($request);
        }
        foreach ($assignments as $assignment) {
            if (($assignment['aggregate']['key'] ?? '') === 'unassigned') {
                self::syncResearcherMilestone($request, $assignment);
            }
        }

        return $assignments;
    }

    private static function fallbackAssignments(Request $request): array
    {
        $reviewers = self::initialReviewerPanel($request, 'Universitas Lampung');
        $panel = array_map(fn (array $r): array => ['id' => $r['id'], 'name' => $r['name'], 'institution' => $r['institution'], 'expertise' => $r['expertise']], $reviewers);
        $aggregate = count($panel) === 2
            ? ['key' => 'pending', 'label' => 'Menunggu penilaian dua reviewer', 'payout_ready' => false, 'completed' => false]
            : ['key' => 'unassigned', 'label' => 'Menunggu penugasan dua reviewer independen', 'payout_ready' => false, 'completed' => false];
        return [
            [
                'id' => 'ASG-'.strtoupper(substr(sha1('panel-surya-pesisir-lampung-2-1'), 0, 8)),
                'project_slug' => 'panel-surya-pesisir-lampung',
                'project_title' => 'Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir',
                'researcher_name' => 'Dr. Andi Prasetyo', 'researcher_email' => ResearcherPrototypeData::DEMO_EMAIL,
                'institution' => 'Universitas Lampung', 'category' => 'Energi Terbarukan',
                'milestone_number' => 2, 'milestone_name' => 'Pembuatan dan integrasi prototipe',
                'milestone_description' => 'Perakitan panel surya, pengendali, penyimpanan energi, dan integrasi perangkat.',
                'allocation' => 45.0, 'requested_amount' => 36000000,
                'target_output' => 'Prototipe panel surya skala mikro terintegrasi dan hasil uji awal sistem.',
                'submitted_at' => '17 Nov 2026, 09:30', 'submitted_iso' => '2026-11-17', 'due_at' => '2026-11-24',
                'revision_round' => 1, 'assigned_reviewer_ids' => array_column($panel, 'id'), 'assigned_reviewers' => $panel,
                'documents' => self::documentsFromMilestone(['number' => 2, 'submitted_at' => '17 Nov 2026']),
                'reviews' => [], 'aggregate' => $aggregate, 'source' => 'demo_assignment',
            ],
            [
                'id' => 'ASG-'.strtoupper(substr(sha1('sistem-pemantauan-kualitas-air-pesisir-1-1'), 0, 8)),
                'project_slug' => 'sistem-pemantauan-kualitas-air-pesisir',
                'project_title' => 'Sistem Pemantauan Kualitas Air Pesisir Berbasis Sensor',
                'researcher_name' => 'Dr. Andi Prasetyo', 'researcher_email' => ResearcherPrototypeData::DEMO_EMAIL,
                'institution' => 'Universitas Lampung', 'category' => 'Lingkungan',
                'milestone_number' => 1, 'milestone_name' => 'Perancangan perangkat dan pengadaan sensor',
                'milestone_description' => 'Perancangan rangkaian, pemilihan sensor, serta pengadaan komponen awal.',
                'allocation' => 30.0, 'requested_amount' => 10500000,
                'target_output' => 'Rencana kerja tahap pertama dan dokumen komitmen pencairan awal.',
                'submitted_at' => '17 Nov 2026, 10:15', 'submitted_iso' => '2026-11-17', 'due_at' => '2026-11-24',
                'revision_round' => 1, 'assigned_reviewer_ids' => array_column($panel, 'id'), 'assigned_reviewers' => $panel,
                'documents' => self::documentsFromMilestone(['number' => 1, 'submitted_at' => '17 Nov 2026']),
                'reviews' => [], 'aggregate' => $aggregate, 'source' => 'demo_assignment',
            ],
        ];
    }

    private static function documentsFromMilestone(array $milestone): array
    {
        if ((int) ($milestone['number'] ?? 0) === 1) {
            return [
                [
                    'id' => 'sptjm', 'title' => 'SPTJM digital',
                    'filename' => $milestone['sptjm_name'] ?? 'SPTJM digital bertanda tangan.pdf',
                    'kind' => 'Dokumen komitmen peneliti',
                    'uploaded_at' => $milestone['submitted_at'] ?? '17 November 2026',
                    'summary' => 'Pernyataan tanggung jawab peneliti sebelum pencairan awal. Dokumen ini digunakan untuk memeriksa kelengkapan komitmen dan identitas peneliti.',
                ],
                [
                    'id' => 'workplan', 'title' => 'Rencana kerja milestone',
                    'filename' => $milestone['workplan_name'] ?? 'Rencana kerja milestone pertama.pdf',
                    'kind' => 'Rencana luaran teknis',
                    'uploaded_at' => $milestone['submitted_at'] ?? '17 November 2026',
                    'summary' => 'Ringkasan target luaran, tahapan kerja, jadwal, serta indikator keberhasilan pada milestone pertama.',
                ],
            ];
        }

        return [
            [
                'id' => 'deliverable', 'title' => 'Laporan luaran teknis',
                'filename' => $milestone['deliverable_name'] ?? 'Laporan luaran teknis milestone sebelumnya.pdf',
                'kind' => 'Bukti capaian penelitian',
                'uploaded_at' => $milestone['submitted_at'] ?? '17 November 2026',
                'summary' => 'Dokumen berisi hasil pekerjaan, metode, data pengujian, dokumentasi prototipe, serta ketercapaian luaran teknis yang dijanjikan.',
            ],
            [
                'id' => 'accountability', 'title' => 'Pertanggungjawaban keuangan',
                'filename' => $milestone['accountability_name'] ?? 'Rekonsiliasi keuangan milestone sebelumnya.pdf',
                'kind' => 'Rekonsiliasi penggunaan dana',
                'uploaded_at' => $milestone['submitted_at'] ?? '17 November 2026',
                'summary' => 'Ringkasan penggunaan dana milestone sebelumnya dan keterhubungannya dengan pos anggaran serta bukti pengeluaran.',
            ],
        ];
    }

    private static function syncResearcherMilestone(Request $request, array $assignment): void
    {
        $owner = ['email' => $assignment['researcher_email'] ?? ResearcherPrototypeData::DEMO_EMAIL];
        $projects = ResearcherPrototypeData::projects($request, $owner);
        foreach ($projects as $projectIndex => $project) {
            if (($project['slug'] ?? '') !== $assignment['project_slug']) {
                continue;
            }
            foreach (($project['milestones'] ?? []) as $milestoneIndex => $milestone) {
                if ((int) ($milestone['number'] ?? 0) !== (int) $assignment['milestone_number']) {
                    continue;
                }

                $reviewerRows = [];
                foreach (($assignment['assigned_reviewer_ids'] ?? []) as $reviewerId) {
                    $reviewer = self::reviewerCatalog($request)[$reviewerId] ?? ['name' => 'Reviewer'];
                    $review = $assignment['reviews'][$reviewerId] ?? [];
                    $status = ! empty($review['conflict_reported']) ? 'CONFLICT' : match ($review['decision'] ?? null) {
                        'approved' => 'APPROVED',
                        'revision_minor' => 'REVISION_MINOR',
                        'rejected' => 'REJECTED',
                        default => 'PENDING',
                    };
                    $reviewerRows[] = [
                        'id' => $reviewerId,
                        'label' => $reviewer['name'],
                        'status' => $status,
                        'status_label' => match ($status) {
                            'APPROVED' => 'Disetujui',
                            'REVISION_MINOR' => 'Revisi Minor',
                            'REJECTED' => 'Ditolak',
                            'CONFLICT' => 'Konflik dilaporkan',
                            default => 'Menunggu penilaian',
                        },
                        'notes' => $review['general_comment'] ?? $review['conflict_reason'] ?? null,
                    ];
                }

                $projects[$projectIndex]['milestones'][$milestoneIndex]['reviewers'] = $reviewerRows;
                $aggregate = self::aggregate($assignment);
                $projects[$projectIndex]['milestones'][$milestoneIndex]['review_status'] = match ($aggregate['key']) {
                    'approved' => 'APPROVED',
                    'revision' => 'REVISION_REQUIRED',
                    'rejected' => 'REJECTED',
                    'conflict', 'unassigned' => 'REASSIGNMENT_REQUIRED',
                    default => 'UNDER_REVIEW',
                };
                if ($aggregate['key'] === 'approved') {
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status'] = 'APPROVED';
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status_label'] = 'Disetujui dua reviewer · menunggu proses pencairan sistem';
                } elseif ($aggregate['key'] === 'revision') {
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status'] = 'REVISION_REQUIRED';
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status_label'] = 'Perlu perbaikan bukti sesuai catatan reviewer';
                } elseif ($aggregate['key'] === 'rejected') {
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status'] = 'REJECTED';
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status_label'] = 'Pengajuan ditolak panel reviewer';
                } elseif ($aggregate['key'] === 'conflict') {
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status_label'] = 'Penugasan ulang reviewer diperlukan karena konflik kepentingan';
                } elseif ($aggregate['key'] === 'unassigned') {
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status'] = 'SUBMITTED';
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status_label'] = 'Menunggu penugasan dua reviewer aktif dari institusi berbeda';
                } else {
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status'] = 'SUBMITTED';
                    $projects[$projectIndex]['milestones'][$milestoneIndex]['status_label'] = $aggregate['label'];
                }
                $projects[$projectIndex]['milestones'][$milestoneIndex]['review_summary'] = $aggregate;
            }
            break;
        }
        ResearcherPrototypeData::saveProjects($request, $owner, $projects);
    }
}
