<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

/**
 * Data administrasi untuk prototipe. Seluruh perubahan disimpan pada session browser,
 * bukan database operasional. Log bersifat append-only melalui antarmuka.
 */
class AdminPrototypeData
{
    public const USERS_KEY = 'prototype_admin_users';
    public const REVIEWERS_KEY = 'prototype_admin_added_reviewers';
    public const REVIEWER_STATUS_KEY = 'prototype_admin_reviewer_status';
    public const AUDIT_KEY = 'prototype_admin_audit_logs';

    public static function users(Request $request): array
    {
        if (! $request->session()->has(self::USERS_KEY)) {
            $seed = [
                [
                    'id' => 'USR-DON-001', 'name' => 'Nadia Putri', 'email' => 'donatur@resfund.test',
                    'phone' => '0812-0000-1234', 'role' => 'donatur', 'role_label' => 'Donatur',
                    'institution' => null, 'academic_id' => null, 'status' => 'active',
                    'verification_status' => 'not_required', 'created_at' => '10 Nov 2026 09:00',
                ],
                [
                    'id' => 'USR-RES-001', 'name' => 'Dr. Andi Prasetyo', 'email' => 'peneliti@resfund.test',
                    'phone' => '0812-1111-2233', 'role' => 'peneliti', 'role_label' => 'Peneliti',
                    'institution' => 'Universitas Lampung', 'academic_id' => '0012087803', 'status' => 'active',
                    'verification_status' => 'terverifikasi', 'created_at' => '11 Nov 2026 10:15',
                ],
                [
                    'id' => 'USR-RES-002', 'name' => 'Rina Amelia, S.T.', 'email' => 'rina.amelia@kampus.test',
                    'phone' => '0813-2222-3456', 'role' => 'peneliti', 'role_label' => 'Peneliti',
                    'institution' => 'Institut Teknologi Sumatera', 'academic_id' => 'NIM-2024-0187', 'researcher_type' => 'mahasiswa',
                    'status' => 'pending', 'verification_status' => 'menunggu_verifikasi', 'created_at' => '16 Nov 2026 14:20',
                ],
                [
                    'id' => 'USR-REV-001', 'name' => 'Dr. Rina Kurniawati', 'email' => 'reviewer@resfund.test',
                    'phone' => '0812-2222-3344', 'role' => 'reviewer', 'role_label' => 'Reviewer',
                    'institution' => 'Universitas Sriwijaya', 'academic_id' => 'NIDN-001', 'status' => 'active',
                    'verification_status' => 'terverifikasi', 'created_at' => '12 Nov 2026 08:30',
                ],
                [
                    'id' => 'USR-REV-002', 'name' => 'Prof. Budi Santoso', 'email' => 'reviewer2@resfund.test',
                    'phone' => '0812-2222-3345', 'role' => 'reviewer', 'role_label' => 'Reviewer',
                    'institution' => 'Institut Teknologi Sumatera', 'academic_id' => 'NIDN-002', 'status' => 'active',
                    'verification_status' => 'terverifikasi', 'created_at' => '12 Nov 2026 08:35',
                ],
                [
                    'id' => 'USR-ADM-001', 'name' => 'Administrator ResFund', 'email' => 'admin@resfund.test',
                    'phone' => '0800-RESFUND', 'role' => 'administrator', 'role_label' => 'Administrator',
                    'institution' => 'ResFund', 'academic_id' => null, 'status' => 'active',
                    'verification_status' => 'not_required', 'created_at' => '01 Nov 2026 08:00',
                ],
            ];
            $request->session()->put(self::USERS_KEY, $seed);
        }

        $users = array_values($request->session()->get(self::USERS_KEY, []));
        $registered = $request->session()->get('prototype_registered_account');
        if (is_array($registered) && ! collect($users)->contains(fn (array $u) => ($u['email'] ?? '') === ($registered['email'] ?? ''))) {
            $users[] = [
                'id' => 'USR-REG-'.strtoupper(substr(sha1($registered['email']), 0, 7)),
                'name' => $registered['name'] ?? 'Pengguna Terdaftar',
                'email' => $registered['email'] ?? '',
                'phone' => $registered['phone'] ?? '',
                'role' => $registered['role'] ?? 'donatur',
                'role_label' => $registered['role_label'] ?? 'Donatur',
                'institution' => $registered['institution'] ?? null,
                'academic_id' => $registered['academic_id'] ?? null,
                'status' => ($registered['verification_status'] ?? '') === 'menunggu_verifikasi' ? 'pending' : 'active',
                'verification_status' => $registered['verification_status'] ?? 'aktif',
                'created_at' => 'Sesi saat ini',
            ];
            $request->session()->put(self::USERS_KEY, $users);
        }

        $statuses = $request->session()->get('prototype_admin_user_statuses', []);
        foreach ($users as &$user) {
            if (isset($statuses[$user['id']])) {
                $user['status'] = $statuses[$user['id']];
            }
        }
        unset($user);

        return array_values($users);
    }

    public static function findUser(Request $request, string $id): ?array
    {
        foreach (self::users($request) as $user) {
            if (hash_equals((string) $user['id'], $id)) {
                return $user;
            }
        }
        return null;
    }

    public static function updateUserStatus(Request $request, string $id, string $status): array
    {
        abort_unless(in_array($status, ['active', 'suspended'], true), 422);
        $user = self::findUser($request, $id);
        abort_unless($user, 404);
        if (($user['role'] ?? '') === 'peneliti' && ($user['verification_status'] ?? '') === 'menunggu_verifikasi' && $status === 'active') {
            throw ValidationException::withMessages(['status' => 'Peneliti yang belum divalidasi hanya dapat diaktifkan melalui menu Validasi Peneliti.']);
        }
        abort_if(($user['role'] ?? '') === 'administrator' && $status === 'suspended', 422, 'Akun Administrator yang sedang digunakan tidak dapat dinonaktifkan dari sesi ini.');

        $statuses = $request->session()->get('prototype_admin_user_statuses', []);
        $statuses[$id] = $status;
        $request->session()->put('prototype_admin_user_statuses', $statuses);

        if (($user['role'] ?? '') === 'reviewer') {
            self::setReviewerStatus($request, $user['email'], $status === 'active' ? 'active' : 'inactive');
        }

        return array_merge($user, ['status' => $status]);
    }

    public static function researcherApplicants(Request $request): array
    {
        return array_values(array_filter(self::users($request), fn (array $u) => ($u['role'] ?? '') === 'peneliti'));
    }

    public static function decideResearcher(Request $request, string $id, string $decision, string $notes): array
    {
        abort_unless(in_array($decision, ['approve', 'reject'], true), 422);
        $users = self::users($request);
        $found = false;
        foreach ($users as &$user) {
            if (hash_equals((string) $user['id'], $id) && ($user['role'] ?? '') === 'peneliti') {
                $user['verification_status'] = $decision === 'approve' ? 'terverifikasi' : 'ditolak';
                $user['status'] = $decision === 'approve' ? 'active' : 'suspended';
                $user['validation_notes'] = $notes;
                $user['validated_at'] = now()->format('d M Y, H:i');
                $found = true;
                break;
            }
        }
        unset($user);
        abort_unless($found, 404);
        $request->session()->put(self::USERS_KEY, $users);
        $statuses = $request->session()->get('prototype_admin_user_statuses', []);
        $statuses[$id] = $decision === 'approve' ? 'active' : 'suspended';
        $request->session()->put('prototype_admin_user_statuses', $statuses);

        return self::findUser($request, $id) ?? [];
    }

    public static function reviewers(Request $request): array
    {
        $catalog = ReviewerPrototypeData::reviewerCatalog($request);
        $statuses = $request->session()->get(self::REVIEWER_STATUS_KEY, []);
        foreach ($catalog as $id => &$reviewer) {
            $reviewer['status'] = $statuses[$id] ?? ($reviewer['status'] ?? 'active');
            $reviewer['status_label'] = $reviewer['status'] === 'active' ? 'Aktif' : 'Nonaktif';
        }
        unset($reviewer);
        return array_values($catalog);
    }

    public static function addReviewer(Request $request, array $data): array
    {
        $reviewers = $request->session()->get(self::REVIEWERS_KEY, []);
        $id = 'reviewer_'.substr(sha1(mb_strtolower($data['email']).microtime(true)), 0, 10);
        $reviewer = [
            'id' => $id,
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'phone' => trim($data['phone']),
            'expertise' => trim($data['expertise']),
            'institution' => trim($data['institution']),
            'status' => 'active',
            'password_hash' => Hash::make($data['password']),
            'created_at' => now()->format('d M Y, H:i'),
        ];
        $reviewers[$id] = $reviewer;
        $request->session()->put(self::REVIEWERS_KEY, $reviewers);
        $users = self::users($request);
        $users[] = [
            'id' => 'USR-'.strtoupper(substr(sha1($reviewer['email']), 0, 8)),
            'name' => $reviewer['name'], 'email' => $reviewer['email'], 'phone' => $reviewer['phone'],
            'role' => 'reviewer', 'role_label' => 'Reviewer', 'institution' => $reviewer['institution'],
            'academic_id' => null, 'status' => 'active', 'verification_status' => 'terverifikasi',
            'created_at' => $reviewer['created_at'],
        ];
        $request->session()->put(self::USERS_KEY, $users);
        return $reviewer;
    }

    public static function setReviewerStatus(Request $request, string $email, string $status): void
    {
        $catalog = ReviewerPrototypeData::reviewerCatalog($request);
        $statuses = $request->session()->get(self::REVIEWER_STATUS_KEY, []);
        foreach ($catalog as $id => $reviewer) {
            if (mb_strtolower($reviewer['email']) === mb_strtolower($email)) {
                $statuses[$id] = $status;
            }
        }
        $request->session()->put(self::REVIEWER_STATUS_KEY, $statuses);
    }

    public static function setReviewerStatusById(Request $request, string $id, string $status): array
    {
        abort_unless(in_array($status, ['active', 'inactive'], true), 422);
        $catalog = ReviewerPrototypeData::reviewerCatalog($request);
        abort_unless(isset($catalog[$id]), 404);
        $statuses = $request->session()->get(self::REVIEWER_STATUS_KEY, []);
        $statuses[$id] = $status;
        $request->session()->put(self::REVIEWER_STATUS_KEY, $statuses);
        return array_merge($catalog[$id], ['status' => $status]);
    }

    public static function eligibleReviewers(Request $request, string $researcherInstitution = ''): array
    {
        return array_values(array_filter(self::reviewers($request), function (array $reviewer) use ($researcherInstitution): bool {
            return ($reviewer['status'] ?? 'active') === 'active'
                && mb_strtolower(trim($reviewer['institution'] ?? '')) !== mb_strtolower(trim($researcherInstitution));
        }));
    }

    public static function log(Request $request, string $action, string $entity, string $detail, string $severity = 'info'): void
    {
        $logs = $request->session()->get(self::AUDIT_KEY, []);
        array_unshift($logs, [
            'id' => (string) Str::uuid(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'actor' => $request->session()->get('prototype_user.name', 'Administrator'),
            'actor_email' => $request->session()->get('prototype_user.email', 'admin@resfund.test'),
            'action' => $action,
            'entity' => $entity,
            'detail' => $detail,
            'severity' => $severity,
            'ip' => $request->ip() ?: '127.0.0.1',
        ]);
        $request->session()->put(self::AUDIT_KEY, array_slice($logs, 0, 250));
    }

    public static function auditLogs(Request $request): array
    {
        if (! $request->session()->has(self::AUDIT_KEY)) {
            $request->session()->put(self::AUDIT_KEY, [
                ['id' => 'SYS-001', 'timestamp' => now()->subHours(2)->format('Y-m-d H:i:s'), 'actor' => 'Sistem ResFund', 'actor_email' => 'system@resfund.test', 'action' => 'JOB_CHECK', 'entity' => 'Kampanye', 'detail' => 'Pemeriksaan tenggat kampanye dan status pendanaan otomatis.', 'severity' => 'info', 'ip' => '127.0.0.1'],
                ['id' => 'SYS-002', 'timestamp' => now()->subHours(5)->format('Y-m-d H:i:s'), 'actor' => 'Sistem ResFund', 'actor_email' => 'system@resfund.test', 'action' => 'REVIEW_SYNC', 'entity' => 'Penilaian milestone', 'detail' => 'Status keputusan dua reviewer disinkronkan ke ringkasan milestone.', 'severity' => 'info', 'ip' => '127.0.0.1'],
                ['id' => 'SYS-003', 'timestamp' => now()->subDay()->format('Y-m-d H:i:s'), 'actor' => 'Sistem ResFund', 'actor_email' => 'system@resfund.test', 'action' => 'REFUND_CHECK', 'entity' => 'Refund', 'detail' => 'Pemeriksaan status refund kampanye yang berakhir.', 'severity' => 'warning', 'ip' => '127.0.0.1'],
            ]);
        }
        return array_values($request->session()->get(self::AUDIT_KEY, []));
    }
}
