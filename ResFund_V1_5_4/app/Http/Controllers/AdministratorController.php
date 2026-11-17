<?php

namespace App\Http\Controllers;

use App\Support\AdminPrototypeData;
use App\Support\ReviewerPrototypeData;
use App\Support\PrototypeCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdministratorController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $this->administrator($request);
        $users = AdminPrototypeData::users($request);
        $reviewers = AdminPrototypeData::reviewers($request);
        $assignments = ReviewerPrototypeData::assignments($request);
        $logs = AdminPrototypeData::auditLogs($request);

        return view('administrator.dashboard', [
            'user' => $user,
            'activeMenu' => 'dashboard',
            'userCount' => count($users),
            'activeUserCount' => count(array_filter($users, fn (array $u) => ($u['status'] ?? '') === 'active')),
            'pendingResearcherCount' => count(array_filter($users, fn (array $u) => ($u['role'] ?? '') === 'peneliti' && ($u['verification_status'] ?? '') === 'menunggu_verifikasi')),
            'reviewerCount' => count(array_filter($reviewers, fn (array $r) => ($r['status'] ?? '') === 'active')),
            'pendingAssignmentCount' => count(array_filter($assignments, fn (array $a) => in_array($a['aggregate']['key'] ?? 'pending', ['pending', 'waiting_second', 'conflict', 'unassigned'], true))),
            'recentLogs' => array_slice($logs, 0, 6),
        ]);
    }

    public function users(Request $request)
    {
        $user = $this->administrator($request);
        $users = AdminPrototypeData::users($request);
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');
        $query = trim((string) $request->query('q', ''));

        if (in_array($role, ['donatur', 'peneliti', 'reviewer', 'administrator'], true)) {
            $users = array_values(array_filter($users, fn (array $u) => $u['role'] === $role));
        } else {
            $role = '';
        }
        if (in_array($status, ['active', 'pending', 'suspended'], true)) {
            $users = array_values(array_filter($users, fn (array $u) => ($u['status'] ?? '') === $status));
        } else {
            $status = '';
        }
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $users = array_values(array_filter($users, fn (array $u) => str_contains(mb_strtolower(($u['name'] ?? '').' '.($u['email'] ?? '').' '.($u['institution'] ?? '')), $needle)));
        }

        return view('administrator.users.index', compact('user', 'users', 'role', 'status', 'query') + ['activeMenu' => 'users']);
    }

    public function userDetail(Request $request, string $userId)
    {
        $user = $this->administrator($request);
        $managedUser = AdminPrototypeData::findUser($request, $userId);
        abort_unless($managedUser, 404);
        return view('administrator.users.show', compact('user', 'managedUser') + ['activeMenu' => 'users']);
    }

    public function updateUserStatus(Request $request, string $userId)
    {
        $this->administrator($request);
        $validated = $request->validate(['status' => ['required', 'in:active,suspended']]);
        $changed = AdminPrototypeData::updateUserStatus($request, $userId, $validated['status']);
        AdminPrototypeData::log($request, 'USER_STATUS_CHANGED', $changed['email'], 'Status akun diubah menjadi '.($validated['status'] === 'active' ? 'Aktif' : 'Ditangguhkan').'.', $validated['status'] === 'suspended' ? 'warning' : 'info');
        return redirect()->route('administrator.users.show', ['userId' => $userId])->with('success', 'Status akun berhasil diperbarui pada sesi prototype.');
    }

    public function researcherValidation(Request $request)
    {
        $user = $this->administrator($request);
        $researchers = AdminPrototypeData::researcherApplicants($request);
        $status = (string) $request->query('status', 'menunggu_verifikasi');
        $query = trim((string) $request->query('q', ''));
        if (in_array($status, ['menunggu_verifikasi', 'terverifikasi', 'ditolak'], true)) {
            $researchers = array_values(array_filter($researchers, fn (array $r) => ($r['verification_status'] ?? '') === $status));
        } else {
            $status = '';
        }
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $researchers = array_values(array_filter($researchers, fn (array $r) => str_contains(mb_strtolower(($r['name'] ?? '').' '.($r['email'] ?? '').' '.($r['academic_id'] ?? '').' '.($r['institution'] ?? '')), $needle)));
        }
        return view('administrator.researchers.index', compact('user', 'researchers', 'status', 'query') + ['activeMenu' => 'researchers']);
    }

    public function researcherDetail(Request $request, string $userId)
    {
        $user = $this->administrator($request);
        $researcher = AdminPrototypeData::findUser($request, $userId);
        abort_unless($researcher && ($researcher['role'] ?? '') === 'peneliti', 404);
        return view('administrator.researchers.show', compact('user', 'researcher') + ['activeMenu' => 'researchers']);
    }

    public function researcherDecision(Request $request, string $userId)
    {
        $this->administrator($request);
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'notes' => ['required', 'string', 'min:10', 'max:1200'],
        ], [
            'decision.required' => 'Pilih keputusan validasi.',
            'notes.required' => 'Catatan verifikasi wajib diisi.',
            'notes.min' => 'Catatan minimal 10 karakter agar dapat ditindaklanjuti.',
        ]);
        $researcher = AdminPrototypeData::decideResearcher($request, $userId, $validated['decision'], trim($validated['notes']));
        AdminPrototypeData::log($request, 'RESEARCHER_VALIDATION', $researcher['email'], 'Validasi peneliti: '.($validated['decision'] === 'approve' ? 'disetujui' : 'ditolak').'. Catatan: '.$validated['notes'], $validated['decision'] === 'approve' ? 'info' : 'warning');
        return redirect()->route('administrator.researchers.show', ['userId' => $userId])->with('success', 'Keputusan validasi berhasil disimpan pada sesi prototype.');
    }

    public function reviewers(Request $request)
    {
        $user = $this->administrator($request);
        $reviewers = AdminPrototypeData::reviewers($request);
        $query = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            $reviewers = array_values(array_filter($reviewers, fn (array $r) => ($r['status'] ?? 'active') === $status));
        } else {
            $status = '';
        }
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $reviewers = array_values(array_filter($reviewers, fn (array $r) => str_contains(mb_strtolower(($r['name'] ?? '').' '.($r['email'] ?? '').' '.($r['institution'] ?? '').' '.($r['expertise'] ?? '')), $needle)));
        }
        return view('administrator.reviewers.index', compact('user', 'reviewers', 'query', 'status') + ['activeMenu' => 'reviewers']);
    }

    public function createReviewer(Request $request)
    {
        $this->administrator($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+().\-\s]+$/'],
            'institution' => ['required', 'string', 'min:3', 'max:150'],
            'expertise' => ['required', 'string', 'min:5', 'max:180'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $email = mb_strtolower(trim($validated['email']));
        $exists = collect(AdminPrototypeData::users($request))->contains(fn (array $u) => mb_strtolower($u['email']) === $email);
        if ($exists) {
            throw ValidationException::withMessages(['email' => 'Email sudah terdaftar pada sistem prototype.']);
        }
        $reviewer = AdminPrototypeData::addReviewer($request, $validated);
        AdminPrototypeData::log($request, 'REVIEWER_CREATED', $reviewer['email'], 'Akun Reviewer baru dibuat untuk '.$reviewer['institution'].' dengan bidang '.$reviewer['expertise'].'.');
        return redirect()->route('administrator.reviewers.index')->with('success', 'Akun Reviewer berhasil dibuat. Reviewer dapat masuk menggunakan email dan kata sandi yang ditetapkan.');
    }

    public function updateReviewerStatus(Request $request, string $reviewerId)
    {
        $this->administrator($request);
        $validated = $request->validate(['status' => ['required', 'in:active,inactive']]);
        $reviewers = AdminPrototypeData::reviewers($request);
        $reviewer = collect($reviewers)->firstWhere('id', $reviewerId);
        abort_unless($reviewer, 404);
        AdminPrototypeData::setReviewerStatusById($request, $reviewerId, $validated['status']);
        $reviewerUser = collect(AdminPrototypeData::users($request))->firstWhere('email', $reviewer['email']);
        if ($reviewerUser) {
            AdminPrototypeData::updateUserStatus($request, $reviewerUser['id'], $validated['status'] === 'active' ? 'active' : 'suspended');
        }
        AdminPrototypeData::log($request, 'REVIEWER_STATUS_CHANGED', $reviewer['email'], 'Status Reviewer diubah menjadi '.($validated['status'] === 'active' ? 'Aktif' : 'Nonaktif').'.', $validated['status'] === 'inactive' ? 'warning' : 'info');
        return back()->with('success', 'Status Reviewer berhasil diperbarui.');
    }

    public function assignments(Request $request)
    {
        $user = $this->administrator($request);
        $assignments = ReviewerPrototypeData::assignments($request);
        $status = (string) $request->query('status', '');
        $query = trim((string) $request->query('q', ''));
        if (in_array($status, ['pending', 'waiting_second', 'approved', 'revision', 'rejected', 'conflict', 'unassigned'], true)) {
            $assignments = array_values(array_filter($assignments, fn (array $a) => ($a['aggregate']['key'] ?? 'pending') === $status));
        } else {
            $status = '';
        }
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $assignments = array_values(array_filter($assignments, fn (array $a) => str_contains(mb_strtolower(($a['project_title'] ?? '').' '.($a['researcher_name'] ?? '').' '.($a['milestone_name'] ?? '')), $needle)));
        }
        return view('administrator.assignments.index', compact('user', 'assignments', 'status', 'query') + ['activeMenu' => 'assignments']);
    }

    public function assignmentDetail(Request $request, string $assignmentId)
    {
        $user = $this->administrator($request);
        $assignment = collect(ReviewerPrototypeData::assignments($request))->first(fn (array $a) => hash_equals((string) $a['id'], $assignmentId));
        abort_unless($assignment, 404);
        $reviewers = AdminPrototypeData::eligibleReviewers($request, $assignment['institution'] ?? '');
        $conflictedIds = array_keys(array_filter($assignment['reviews'] ?? [], fn (array $review) => ! empty($review['conflict_reported'])));
        $reviewers = array_values(array_filter($reviewers, fn (array $reviewer) => ! in_array($reviewer['id'], $conflictedIds, true)));
        $panelHistory = $assignment['panel_history'] ?? [];
        return view('administrator.assignments.show', compact('user', 'assignment', 'reviewers', 'panelHistory') + ['activeMenu' => 'assignments']);
    }

    public function assignReviewers(Request $request, string $assignmentId)
    {
        $this->administrator($request);
        $validated = $request->validate([
            'reviewer_ids' => ['required', 'array', 'size:2'],
            'reviewer_ids.*' => ['required', 'string', 'distinct'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'reviewer_ids.size' => 'Pilih tepat dua reviewer independen.',
            'reason.min' => 'Alasan penugasan ulang minimal 10 karakter.',
        ]);
        $assignment = ReviewerPrototypeData::reassignPanel($request, $assignmentId, $validated['reviewer_ids']);
        AdminPrototypeData::log($request, 'REVIEWER_PANEL_ASSIGNED', $assignmentId, 'Panel reviewer ditetapkan ulang untuk '.$assignment['project_title'].' / milestone '.$assignment['milestone_number'].'. Alasan: '.$validated['reason'], 'warning');
        return redirect()->route('administrator.assignments.show', ['assignmentId' => $assignmentId])->with('success', 'Dua reviewer berhasil ditugaskan. Semua keputusan panel baru harus diberikan secara independen.');
    }

    public function auditLogs(Request $request)
    {
        $user = $this->administrator($request);
        $logs = AdminPrototypeData::auditLogs($request);
        $projects = PrototypeCatalog::projects();
        $query = trim((string) $request->query('q', ''));
        $severity = (string) $request->query('severity', '');
        $action = (string) $request->query('action', '');
        if (in_array($severity, ['info', 'warning', 'critical'], true)) {
            $logs = array_values(array_filter($logs, fn (array $log) => ($log['severity'] ?? '') === $severity));
        } else {
            $severity = '';
        }
        if ($action !== '') {
            $logs = array_values(array_filter($logs, fn (array $log) => ($log['action'] ?? '') === $action));
        }
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $logs = array_values(array_filter($logs, fn (array $log) => str_contains(mb_strtolower(($log['actor'] ?? '').' '.($log['actor_email'] ?? '').' '.($log['entity'] ?? '').' '.($log['detail'] ?? '')), $needle)));
        }
        $eligibleProjects = array_values(array_filter($projects, fn (array $p) => in_array(($p['status'] ?? ''), ['ACTIVE', 'FUNDED', 'COMPLETED'], true)));
        $failedProjects = array_values(array_filter($projects, fn (array $p) => ($p['status'] ?? '') === 'FAILED'));
        $monitoring = [
            'projects' => count($projects),
            'active' => count(array_filter($projects, fn (array $p) => ($p['status'] ?? '') === 'ACTIVE')),
            'funded' => count(array_filter($projects, fn (array $p) => ($p['status'] ?? '') === 'FUNDED')),
            'failed' => count($failedProjects),
            // Kampanye FAILED mengikuti All-or-Nothing refund, sehingga bukan dana yang masih terhimpun.
            'collected' => array_sum(array_column($eligibleProjects, 'collected')),
            'excluded_failed_amount' => array_sum(array_column($failedProjects, 'collected')),
        ];
        return view('administrator.monitoring.index', compact('user', 'logs', 'query', 'severity', 'action', 'monitoring') + ['activeMenu' => 'monitoring']);
    }

    private function administrator(Request $request): array
    {
        $user = $request->session()->get('prototype_user');
        abort_unless(is_array($user) && ($user['role'] ?? '') === 'administrator', 403);
        return $user;
    }
}
