<?php

namespace App\Http\Controllers;

use App\Support\ReviewerPrototypeData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewerDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $this->reviewer($request);
        $assignments = ReviewerPrototypeData::assignmentsFor($request, $user['reviewer_id']);
        $pending = array_values(array_filter($assignments, fn (array $a) => $this->myStatus($a, $user['reviewer_id']) === 'pending'));
        $completed = array_values(array_filter($assignments, fn (array $a) => $this->myStatus($a, $user['reviewer_id']) === 'completed'));
        $conflicts = array_values(array_filter($assignments, fn (array $a) => $this->myStatus($a, $user['reviewer_id']) === 'conflict'));
        $dueSoon = array_values(array_filter($pending, fn (array $a) => $this->daysLeft($a['due_at'] ?? null) >= 0 && $this->daysLeft($a['due_at'] ?? null) <= 2));

        usort($pending, fn (array $a, array $b) => strcmp($a['due_at'] ?? '9999-12-31', $b['due_at'] ?? '9999-12-31'));

        return view('reviewer.dashboard', [
            'user' => $user,
            'assignments' => $assignments,
            'pendingAssignments' => array_slice($pending, 0, 4),
            'pendingCount' => count($pending),
            'completedCount' => count($completed),
            'conflictCount' => count($conflicts),
            'dueSoonCount' => count($dueSoon),
            'activeMenu' => 'dashboard',
            'daysLeft' => fn ($date) => $this->daysLeft($date),
            'myStatus' => fn ($assignment) => $this->myStatus($assignment, $user['reviewer_id']),
        ]);
    }

    public function assignments(Request $request)
    {
        $user = $this->reviewer($request);
        $assignments = ReviewerPrototypeData::assignmentsFor($request, $user['reviewer_id']);
        $status = (string) $request->query('status', '');
        $query = trim((string) $request->query('q', ''));

        if ($status !== '') {
            $assignments = array_values(array_filter($assignments, fn (array $a) => $this->myStatus($a, $user['reviewer_id']) === $status));
        }
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $assignments = array_values(array_filter($assignments, function (array $a) use ($needle): bool {
                $haystack = mb_strtolower(($a['project_title'] ?? '').' '.($a['researcher_name'] ?? '').' '.($a['milestone_name'] ?? '').' '.($a['category'] ?? ''));
                return str_contains($haystack, $needle);
            }));
        }

        usort($assignments, fn (array $a, array $b) => strcmp($a['due_at'] ?? '9999-12-31', $b['due_at'] ?? '9999-12-31'));

        return view('reviewer.assignments.index', [
            'user' => $user,
            'assignments' => $assignments,
            'status' => $status,
            'query' => $query,
            'activeMenu' => 'assignments',
            'myStatus' => fn ($assignment) => $this->myStatus($assignment, $user['reviewer_id']),
            'daysLeft' => fn ($date) => $this->daysLeft($date),
        ]);
    }

    public function decisionHistory(Request $request)
    {
        $user = $this->reviewer($request);
        $reviewerId = $user['reviewer_id'];
        // Include current assignments and archived panels. A reviewer's past decision
        // must remain visible even after an Administrator replaces the panel.
        $assignments = array_values(array_filter(
            ReviewerPrototypeData::assignments($request),
            function (array $assignment) use ($reviewerId): bool {
                if (in_array($reviewerId, $assignment['assigned_reviewer_ids'] ?? [], true)) {
                    return true;
                }
                foreach (($assignment['panel_history'] ?? []) as $panel) {
                    if (in_array($reviewerId, $panel['reviewer_ids'] ?? [], true)) {
                        return true;
                    }
                }
                return false;
            }
        ));
        $status = (string) $request->query('status', '');
        $query = trim((string) $request->query('q', ''));
        $allowedStatuses = ['approved', 'revision_minor', 'rejected', 'conflict'];
        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $history = [];
        foreach ($assignments as $assignment) {
            $currentReview = ReviewerPrototypeData::reviewerDecision($assignment, $reviewerId);
            if (is_array($currentReview) && $currentReview !== []) {
                $decisionKey = ! empty($currentReview['conflict_reported']) ? 'conflict' : ($currentReview['decision'] ?? '');
                if ($decisionKey !== '') {
                    $history[] = $this->buildDecisionHistoryItem($assignment, $currentReview, $decisionKey, $assignment['aggregate']['label'] ?? 'Menunggu keputusan panel', $assignment['aggregate']['key'] ?? 'pending');
                }
            }

            foreach (($assignment['panel_history'] ?? []) as $panel) {
                $archivedReview = $panel['reviews'][$reviewerId] ?? null;
                if (! is_array($archivedReview) || $archivedReview === []) {
                    continue;
                }
                $archivedDecision = ! empty($archivedReview['conflict_reported']) ? 'conflict' : ($archivedReview['decision'] ?? '');
                if ($archivedDecision === '') {
                    continue;
                }
                $history[] = $this->buildDecisionHistoryItem(
                    $assignment,
                    $archivedReview,
                    $archivedDecision,
                    'Panel sebelumnya · ditugaskan ulang',
                    'archived'
                );
            }
        }

        if ($status !== '') {
            $history = array_values(array_filter($history, fn (array $item) => $item['decision_key'] === $status));
        }
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $history = array_values(array_filter($history, function (array $item) use ($needle): bool {
                $haystack = mb_strtolower(
                    $item['project_title'].' '.$item['researcher_name'].' '.$item['milestone_name'].' '.$item['decision_label']
                );
                return str_contains($haystack, $needle);
            }));
        }

        usort($history, fn (array $a, array $b) => $b['sort_at'] <=> $a['sort_at']);

        return view('reviewer.decisions.index', [
            'user' => $user,
            'history' => $history,
            'status' => $status,
            'query' => $query,
            'activeMenu' => 'decisions',
        ]);
    }

    private function buildDecisionHistoryItem(array $assignment, array $review, string $decisionKey, string $panelLabel, string $panelKey): array
    {
        $scores = array_values(array_filter(array_map(
            fn (string $key) => isset($review[$key]) ? (int) $review[$key] : null,
            ['relevance', 'methodology', 'evidence', 'budget']
        ), fn ($score) => $score !== null));
        $submittedAt = $review['submitted_at'] ?? ($assignment['submitted_at'] ?? '');
        $isConflict = ! empty($review['conflict_reported']);

        return [
            'assignment_id' => $assignment['id'],
            'project_slug' => $assignment['project_slug'],
            'project_title' => $assignment['project_title'],
            'researcher_name' => $assignment['researcher_name'],
            'milestone_number' => $assignment['milestone_number'],
            'milestone_name' => $assignment['milestone_name'],
            'decision_key' => $decisionKey,
            'decision_label' => $isConflict ? 'Konflik kepentingan dilaporkan' : ReviewerPrototypeData::decisionLabel($decisionKey),
            'panel_label' => $panelLabel,
            'panel_key' => $panelKey,
            'average_score' => $scores !== [] ? round(array_sum($scores) / count($scores), 1) : null,
            'submitted_at' => $submittedAt,
            'general_comment' => $review['general_comment'] ?? null,
            'decision_notes' => $review['decision_notes'] ?? null,
            'conflict_reason' => $review['conflict_reason'] ?? null,
            'sort_at' => $this->reviewTimestamp($submittedAt),
        ];
    }

    public function profile(Request $request)
    {
        $user = $this->reviewer($request);
        $reviewerId = $user['reviewer_id'];
        $preferences = $request->session()->get('prototype_reviewer_preferences', []);
        $preferences = $preferences[$reviewerId] ?? [
            'assignment_updates' => true,
            'deadline_reminders' => true,
            'panel_updates' => true,
        ];
        $notifications = $this->reviewerNotifications($request, $reviewerId);

        return view('reviewer.profile.index', [
            'user' => $user,
            'preferences' => $preferences,
            'notifications' => $notifications,
            'unreadCount' => count(array_filter($notifications, fn (array $item) => ! $item['read'])),
            'activeMenu' => 'profile',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $this->reviewer($request);
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+().\-\s]+$/'],
            'assignment_updates' => ['nullable', 'in:1'],
            'deadline_reminders' => ['nullable', 'in:1'],
            'panel_updates' => ['nullable', 'in:1'],
        ], [
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
        ]);

        $user['phone'] = trim($validated['phone']);
        $request->session()->put('prototype_user', $user);

        $preferences = $request->session()->get('prototype_reviewer_preferences', []);
        $preferences[$user['reviewer_id']] = [
            'assignment_updates' => ($validated['assignment_updates'] ?? null) === '1',
            'deadline_reminders' => ($validated['deadline_reminders'] ?? null) === '1',
            'panel_updates' => ($validated['panel_updates'] ?? null) === '1',
        ];
        $request->session()->put('prototype_reviewer_preferences', $preferences);

        return redirect()->route('reviewer.profile')->with('success', 'Profil dan preferensi notifikasi berhasil diperbarui pada sesi prototype.');
    }

    public function markNotificationRead(Request $request, string $notificationId)
    {
        $user = $this->reviewer($request);
        $notifications = $this->reviewerNotifications($request, $user['reviewer_id']);
        abort_unless(collect($notifications)->contains(fn (array $item) => hash_equals($item['id'], $notificationId)), 404);

        $reads = $request->session()->get('prototype_reviewer_notification_reads', []);
        $reads[$user['reviewer_id']][$notificationId] = true;
        $request->session()->put('prototype_reviewer_notification_reads', $reads);

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    public function markAllNotificationsRead(Request $request)
    {
        $user = $this->reviewer($request);
        $reads = $request->session()->get('prototype_reviewer_notification_reads', []);
        foreach ($this->reviewerNotifications($request, $user['reviewer_id']) as $notification) {
            $reads[$user['reviewer_id']][$notification['id']] = true;
        }
        $request->session()->put('prototype_reviewer_notification_reads', $reads);

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    private function reviewerNotifications(Request $request, string $reviewerId): array
    {
        $assignments = ReviewerPrototypeData::assignmentsFor($request, $reviewerId);
        $reads = $request->session()->get('prototype_reviewer_notification_reads', []);
        $reviewerReads = $reads[$reviewerId] ?? [];
        $allPreferences = $request->session()->get('prototype_reviewer_preferences', []);
        $preferences = $allPreferences[$reviewerId] ?? [
            'assignment_updates' => true,
            'deadline_reminders' => true,
            'panel_updates' => true,
        ];
        $items = [];

        foreach ($assignments as $assignment) {
            $ownReview = ReviewerPrototypeData::reviewerDecision($assignment, $reviewerId);
            $otherHasDecision = false;
            foreach (($assignment['assigned_reviewer_ids'] ?? []) as $otherId) {
                if ($otherId !== $reviewerId && ! empty(ReviewerPrototypeData::reviewerDecision($assignment, $otherId)['decision'])) {
                    $otherHasDecision = true;
                    break;
                }
            }

            $base = [
                'assignment_id' => $assignment['id'],
                'url' => route('reviewer.assignments.show', ['assignmentId' => $assignment['id']]),
                'date' => $assignment['submitted_at'] ?? '17 November 2026',
            ];

            if (! empty($ownReview['conflict_reported']) && ($preferences['panel_updates'] ?? true)) {
                $items[] = $base + [
                    'id' => 'own-conflict-'.$assignment['id'].'-'.$reviewerId,
                    'type' => 'conflict',
                    'title' => 'Laporan konflik tercatat',
                    'message' => 'Laporan konflik kepentingan Anda sudah dicatat dan menunggu tindak lanjut Administrator.',
                ];
            } elseif (($assignment['aggregate']['key'] ?? '') === 'conflict' && ($preferences['panel_updates'] ?? true)) {
                $items[] = $base + [
                    'id' => 'panel-conflict-'.$assignment['id'].'-'.$reviewerId,
                    'type' => 'conflict',
                    'title' => 'Penugasan ditangguhkan',
                    'message' => 'Panel penilaian untuk '.$assignment['project_title'].' dihentikan sementara karena laporan konflik kepentingan.',
                ];
            } elseif (! empty($ownReview['decision']) && ($preferences['panel_updates'] ?? true)) {
                $items[] = $base + [
                    'id' => 'own-decision-'.$assignment['id'].'-'.$reviewerId,
                    'type' => 'decision',
                    'title' => 'Keputusan evaluasi tersimpan',
                    'message' => 'Keputusan Anda: '.ReviewerPrototypeData::decisionLabel($ownReview['decision']).'. '.$assignment['aggregate']['label'].'.',
                ];
            } elseif ($otherHasDecision && ($preferences['panel_updates'] ?? true)) {
                $items[] = $base + [
                    'id' => 'panel-update-'.$assignment['id'].'-'.$reviewerId,
                    'type' => 'panel',
                    'title' => 'Keputusan reviewer panel diperbarui',
                    'message' => 'Reviewer lain telah mengirim keputusan untuk '.$assignment['project_title'].'. Penilaian Anda tetap independen.',
                ];
            } elseif ($this->daysLeft($assignment['due_at'] ?? null) >= 0 && $this->daysLeft($assignment['due_at'] ?? null) <= 2 && ($preferences['deadline_reminders'] ?? true)) {
                $items[] = $base + [
                    'id' => 'deadline-'.$assignment['id'].'-'.$reviewerId,
                    'type' => 'deadline',
                    'title' => 'Tenggat penilaian mendekat',
                    'message' => 'Batas penilaian '.$assignment['project_title'].' tinggal '.$this->daysLeft($assignment['due_at']).' hari.',
                ];
            } elseif (empty($ownReview) && ($assignment['aggregate']['key'] ?? '') !== 'conflict' && ($preferences['assignment_updates'] ?? true)) {
                $items[] = $base + [
                    'id' => 'assignment-'.$assignment['id'].'-'.$reviewerId,
                    'type' => 'info',
                    'title' => 'Penugasan tersedia untuk ditinjau',
                    'message' => 'Anda tercatat sebagai reviewer untuk '.$assignment['project_title'].' · '.$assignment['milestone_name'].'.',
                ];
            }
        }

        if ($items === []) {
            $items[] = [
                'id' => 'welcome-'.$reviewerId,
                'type' => 'info',
                'title' => 'Selamat datang di Ruang Reviewer',
                'message' => 'Penugasan baru, pembaruan panel, dan pengingat tenggat akan ditampilkan di halaman ini.',
                'date' => now()->translatedFormat('d F Y'),
                'url' => route('reviewer.assignments.index'),
                'assignment_id' => null,
            ];
        }

        usort($items, fn (array $a, array $b) => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));
        foreach ($items as &$item) {
            $item['read'] = (bool) ($reviewerReads[$item['id']] ?? false);
        }
        unset($item);

        return $items;
    }

    private function reviewTimestamp(string $date): int
    {
        try {
            return Carbon::parse($date)->timestamp;
        } catch (\Throwable) {
            return 0;
        }
    }

    public function showAssignment(Request $request, string $assignmentId)
    {
        $user = $this->reviewer($request);
        $assignment = ReviewerPrototypeData::findForReviewer($request, $assignmentId, $user['reviewer_id']);
        abort_unless($assignment, 404);

        $myReview = ReviewerPrototypeData::reviewerDecision($assignment, $user['reviewer_id']);
        $deadlineDays = $this->daysLeft($assignment['due_at'] ?? null);
        $deadlinePassed = $deadlineDays < 0;
        $otherReviews = [];
        foreach (($assignment['assigned_reviewers'] ?? []) as $reviewer) {
            $decision = ReviewerPrototypeData::reviewerDecision($assignment, $reviewer['id']);
            $otherReviews[] = [
                'id' => $reviewer['id'],
                'name' => $reviewer['name'],
                'status' => ! empty($decision['conflict_reported']) ? 'Konflik dilaporkan' : ($decision['decision_label'] ?? 'Menunggu penilaian'),
                'is_self' => $reviewer['id'] === $user['reviewer_id'],
            ];
        }

        return view('reviewer.assignments.show', [
            'user' => $user,
            'assignment' => $assignment,
            'myReview' => $myReview,
            'otherReviews' => $otherReviews,
            'daysLeft' => $deadlineDays,
            'deadlinePassed' => $deadlinePassed,
            'canEvaluate' => ! $myReview && ! $deadlinePassed && ($assignment['aggregate']['key'] ?? '') !== 'conflict',
            'canReportConflict' => ! $myReview && ($assignment['aggregate']['key'] ?? '') !== 'conflict',
            'activeMenu' => 'assignments',
        ]);
    }

    public function submitEvaluation(Request $request, string $assignmentId)
    {
        $user = $this->reviewer($request);
        $assignment = ReviewerPrototypeData::findForReviewer($request, $assignmentId, $user['reviewer_id']);
        abort_unless($assignment, 404);

        $existing = ReviewerPrototypeData::reviewerDecision($assignment, $user['reviewer_id']);
        if ($existing) {
            return back()->with('info', 'Penilaian Anda telah dikirim dan dikunci. Keputusan tidak dapat diubah secara sepihak.');
        }
        if (($assignment['aggregate']['key'] ?? '') === 'conflict') {
            return back()->with('info', 'Penugasan sedang diblokir karena konflik kepentingan. Tunggu penugasan ulang dari Administrator.');
        }
        if ($this->daysLeft($assignment['due_at'] ?? null) < 0) {
            return back()->with('info', 'Batas waktu penilaian telah berakhir. Hubungi Administrator untuk penjadwalan ulang penugasan.');
        }

        $validated = $request->validate([
            'no_conflict' => ['accepted'],
            'relevance' => ['required', 'integer', 'between:1,5'],
            'methodology' => ['required', 'integer', 'between:1,5'],
            'evidence' => ['required', 'integer', 'between:1,5'],
            'budget' => ['required', 'integer', 'between:1,5'],
            'decision' => ['required', 'in:approved,revision_minor,rejected'],
            'general_comment' => ['required', 'string', 'min:20', 'max:2000'],
            'decision_notes' => ['nullable', 'string', 'max:1500'],
        ], [
            'no_conflict.accepted' => 'Pernyataan bebas konflik kepentingan wajib dikonfirmasi sebelum menilai.',
            'relevance.required' => 'Berikan skor relevansi terhadap tujuan dan luaran.',
            'methodology.required' => 'Berikan skor metodologi dan kelayakan teknis.',
            'evidence.required' => 'Berikan skor kelengkapan bukti capaian.',
            'budget.required' => 'Berikan skor kesesuaian anggaran dan pertanggungjawaban.',
            'decision.required' => 'Pilih keputusan akhir penilaian.',
            'general_comment.required' => 'Catatan umum penilaian wajib diisi.',
            'general_comment.min' => 'Catatan umum minimal 20 karakter agar masukan dapat ditindaklanjuti.',
        ]);

        if (in_array($validated['decision'], ['revision_minor', 'rejected'], true) && mb_strlen(trim((string) ($validated['decision_notes'] ?? ''))) < 15) {
            throw ValidationException::withMessages([
                'decision_notes' => 'Jelaskan alasan revisi atau penolakan secara spesifik (minimal 15 karakter).',
            ]);
        }

        ReviewerPrototypeData::saveReview($request, $assignmentId, $user['reviewer_id'], $validated);
        $fresh = ReviewerPrototypeData::findForReviewer($request, $assignmentId, $user['reviewer_id']);
        $aggregate = $fresh['aggregate'];

        $message = match ($aggregate['key']) {
            'approved' => 'Kedua reviewer telah menyetujui pengajuan. Status siap diteruskan ke proses pencairan oleh sistem; Reviewer tidak mengeksekusi transfer dana.',
            'revision' => 'Penilaian panel selesai. Pengajuan memerlukan revisi sebelum proses pencairan dapat dilanjutkan.',
            'rejected' => 'Penilaian panel selesai dengan keputusan ditolak. Pencairan tidak dapat diproses.',
            default => 'Penilaian Anda tersimpan. Pengajuan tetap menunggu keputusan reviewer independen lainnya.',
        };

        return redirect()->route('reviewer.assignments.show', ['assignmentId' => $assignmentId])->with('success', $message);
    }

    public function reportConflict(Request $request, string $assignmentId)
    {
        $user = $this->reviewer($request);
        $assignment = ReviewerPrototypeData::findForReviewer($request, $assignmentId, $user['reviewer_id']);
        abort_unless($assignment, 404);

        $validated = $request->validate([
            'conflict_reason' => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'conflict_reason.required' => 'Jelaskan potensi konflik kepentingan.',
            'conflict_reason.min' => 'Keterangan konflik minimal 20 karakter.',
        ]);

        ReviewerPrototypeData::reportConflict($request, $assignmentId, $user['reviewer_id'], trim($validated['conflict_reason']));

        return redirect()->route('reviewer.assignments.index')->with('success', 'Laporan konflik kepentingan dikirim. Penilaian Anda dihentikan dan penugasan perlu diganti oleh Administrator.');
    }

    private function reviewer(Request $request): array
    {
        $user = $request->session()->get('prototype_user');
        abort_unless(is_array($user) && ($user['role'] ?? null) === 'reviewer', 403);

        $reviewerId = (string) ($user['reviewer_id'] ?? '');
        $catalog = ReviewerPrototypeData::reviewerCatalog($request);
        abort_unless($reviewerId !== '' && isset($catalog[$reviewerId]), 403);
        abort_if(($catalog[$reviewerId]['status'] ?? 'active') !== 'active', 403, 'Akses Reviewer telah dinonaktifkan oleh Administrator.');

        return $user;
    }

    private function myStatus(array $assignment, string $reviewerId): string
    {
        // Konflik dari salah satu anggota panel memblokir seluruh assignment, termasuk reviewer lain.
        if (($assignment['aggregate']['key'] ?? '') === 'conflict') {
            return 'conflict';
        }

        $review = ReviewerPrototypeData::reviewerDecision($assignment, $reviewerId);
        if (! $review) {
            return 'pending';
        }
        if (! empty($review['conflict_reported'])) {
            return 'conflict';
        }

        return ! empty($review['decision']) ? 'completed' : 'pending';
    }

    private function daysLeft(?string $date): int
    {
        if (! $date) {
            return 0;
        }

        return (int) Carbon::today()->diffInDays(Carbon::parse($date)->startOfDay(), false);
    }
}
