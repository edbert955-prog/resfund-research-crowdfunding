<?php

namespace Tests\Feature;

use App\Support\ReviewerPrototypeData;
use Tests\TestCase;

class ReviewerDashboardTest extends TestCase
{
    private function reviewerSession(string $reviewerId = 'reviewer_a', string $email = 'reviewer@resfund.test'): array
    {
        return [
            'prototype_user' => [
                'name' => $reviewerId === 'reviewer_a' ? 'Dr. Rina Kurniawati' : 'Prof. Budi Santoso',
                'email' => $email,
                'role' => 'reviewer',
                'role_label' => 'Reviewer',
                'reviewer_id' => $reviewerId,
                'expertise' => 'Instrumentasi lingkungan dan sensor',
                'institution' => 'Universitas Lampung',
                'verification_status' => 'terverifikasi',
            ],
        ];
    }

    private function assignmentId(string $slug, int $number): string
    {
        return 'ASG-'.strtoupper(substr(sha1($slug.'-'.$number.'-1'), 0, 8));
    }

    private function approvedPayload(): array
    {
        return [
            'no_conflict' => '1',
            'relevance' => '5',
            'methodology' => '4',
            'evidence' => '4',
            'budget' => '5',
            'decision' => 'approved',
            'general_comment' => 'Bukti dan rencana luaran sesuai dengan target milestone yang diajukan.',
            'decision_notes' => '',
        ];
    }

    public function test_reviewer_demo_login_rejects_an_incorrect_password(): void
    {
        $this->from(route('login'))
            ->post(route('login.submit'), [
                'email' => 'reviewer@resfund.test',
                'password' => 'WrongPassword123!',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_reviewer_interface_uses_affiliations_separate_from_demo_researcher_institution(): void
    {
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.assignments.show', ['assignmentId' => $this->assignmentId('panel-surya-pesisir-lampung', 2)]))
            ->assertOk()
            ->assertSee('Universitas Sriwijaya')
            ->assertSee('Institut Teknologi Sumatera')
            ->assertSee('Universitas Lampung');
    }

    public function test_reviewer_dashboard_does_not_expose_internal_screen_codes_or_notification_shortcut(): void
    {
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.dashboard'))
            ->assertOk()
            ->assertDontSee('S17')
            ->assertDontSee('S18')
            ->assertDontSee('S19')
            ->assertDontSee('S20')
            ->assertDontSee('donor-notification-button', false);
    }

    public function test_donor_topbar_notification_shortcut_has_been_removed_but_profile_menu_remains(): void
    {
        $this->withSession(['prototype_user' => ['name' => 'Nadia Putri', 'email' => 'donatur@resfund.test', 'role' => 'donatur']])
            ->get(route('donor.profile'))
            ->assertOk()
            ->assertDontSee('donor-notification-button', false)
            ->assertSee('Profil & Notifikasi');
    }

    public function test_info_flash_uses_an_icon_not_a_loose_letter_i(): void
    {
        $this->withSession([
            'prototype_user' => ['name' => 'Nadia Putri', 'email' => 'donatur@resfund.test', 'role' => 'donatur'],
            'info' => 'Contoh informasi akun.',
        ])->get(route('donor.profile'))
            ->assertOk()
            ->assertSee('Contoh informasi akun.')
            ->assertDontSee('class="flash-icon" aria-hidden="true">i</span>', false);
    }

    public function test_reviewer_demo_login_opens_reviewer_dashboard(): void
    {
        $this->post(route('login.submit'), [
            'email' => 'reviewer@resfund.test',
            'password' => 'ResFund123!',
        ])
            ->assertRedirect(route('reviewer.dashboard'))
            ->assertSessionHas('prototype_user.role', 'reviewer')
            ->assertSessionHas('prototype_user.reviewer_id', 'reviewer_a');

        $this->get(route('reviewer.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Reviewer')
            ->assertSee('Penugasan yang perlu ditinjau')
            ->assertSee('Dr. Rina Kurniawati');
    }

    public function test_second_reviewer_demo_account_is_available_for_independent_review(): void
    {
        $this->post(route('login.submit'), [
            'email' => 'reviewer2@resfund.test',
            'password' => 'ResFund123!',
        ])->assertRedirect(route('reviewer.dashboard'))
          ->assertSessionHas('prototype_user.reviewer_id', 'reviewer_b');
    }

    public function test_reviewer_is_not_available_in_public_self_registration(): void
    {
        $this->get(route('register'))->assertOk()->assertDontSee('Reviewer', false);
        $this->post(route('register'), [
            'role' => 'reviewer',
            'name' => 'Reviewer Baru',
            'email' => 'reviewer-baru@example.test',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => '1',
        ])->assertSessionHasErrors('role');
    }

    public function test_non_reviewer_roles_cannot_open_reviewer_area(): void
    {
        $this->withSession(['prototype_user' => ['name' => 'Nadia', 'role' => 'donatur']])
            ->get(route('reviewer.dashboard'))->assertRedirect(route('home'));
        $this->withSession(['prototype_user' => ['name' => 'Andi', 'role' => 'peneliti']])
            ->get(route('reviewer.assignments.index'))->assertRedirect(route('home'));
    }

    public function test_reviewer_only_sees_assignments_explicitly_assigned_to_reviewer_id(): void
    {
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->get(route('reviewer.assignments.index'))
            ->assertOk()
            ->assertSee('Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir')
            ->assertSee('Sistem Pemantauan Kualitas Air Pesisir Berbasis Sensor');

        $this->withSession($this->reviewerSession('unknown', 'other@resfund.test'))
            ->get(route('reviewer.dashboard'))->assertForbidden();
    }

    public function test_assigned_reviewer_can_open_evidence_and_evaluation_detail(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()
            ->assertSee('Bukti yang harus ditelaah')
            ->assertSee('Laporan luaran teknis milestone 1.pdf')
            ->assertSee('Rekonsiliasi keuangan milestone 1.pdf')
            ->assertSee('Berikan penilaian Anda')
            ->assertSee('Revisi Minor');
    }

    public function test_non_assigned_reviewer_cannot_open_unknown_assignment(): void
    {
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.assignments.show', ['assignmentId' => 'ASG-NOT-ASSIGNED']))
            ->assertNotFound();
    }

    public function test_evaluation_requires_conflict_declaration_rubrics_and_decision(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession())
            ->from(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), [])
            ->assertRedirect(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertSessionHasErrors(['no_conflict', 'relevance', 'methodology', 'evidence', 'budget', 'decision', 'general_comment']);
    }

    public function test_approval_by_first_reviewer_does_not_make_payout_ready(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertSessionHas('success');

        $this->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()
            ->assertSee('Menunggu keputusan reviewer lainnya')
            ->assertSee('Penilaian Anda telah dikirim dan dikunci');
    }

    public function test_payout_is_ready_only_after_both_independent_reviewers_approve(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()
            ->assertSee('Seluruh reviewer menyetujui pengajuan')
            ->assertSee('siap diteruskan ke proses pencairan oleh sistem');

        $researcher = ['prototype_user' => ['name' => 'Dr. Andi Prasetyo', 'email' => 'peneliti@resfund.test', 'role' => 'peneliti']];
        $this->withSession($researcher)->get(route('researcher.milestones'))
            ->assertOk()
            ->assertSee('Disetujui dua reviewer');
    }

    public function test_revision_minor_blocks_payout_until_researcher_corrects_evidence(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $payload = $this->approvedPayload();
        $payload['decision'] = 'revision_minor';
        $payload['decision_notes'] = 'Lampirkan hasil pengujian tambahan dan jelaskan metode kalibrasi sensor.';

        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $payload)
            ->assertRedirect();
        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();
        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()
            ->assertSee('Pengajuan memerlukan revisi')
            ->assertDontSee('siap diteruskan ke proses pencairan oleh sistem');
    }

    public function test_rejection_blocks_payout_after_both_reviewers_submit(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $payload = $this->approvedPayload();
        $payload['decision'] = 'rejected';
        $payload['decision_notes'] = 'Bukti capaian tidak menunjukkan hasil teknis yang diminta proposal.';
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $payload)->assertRedirect();
        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())->assertRedirect();
        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()->assertSee('Pengajuan ditolak oleh panel reviewer');
    }

    public function test_revision_or_rejection_requires_specific_decision_notes(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $payload = $this->approvedPayload();
        $payload['decision'] = 'revision_minor';
        $payload['decision_notes'] = 'singkat';
        $this->withSession($this->reviewerSession())
            ->from(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $payload)
            ->assertSessionHasErrors('decision_notes');
    }

    public function test_reviewer_cannot_submit_a_second_decision(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession())
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())->assertRedirect();
        $this->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertSessionHas('info');
    }

    public function test_reviewer_can_report_conflict_of_interest_and_is_blocked_from_evaluation(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession())
            ->post(route('reviewer.assignments.conflict', ['assignmentId' => $id]), [
                'conflict_reason' => 'Saya memiliki kolaborasi riset aktif dengan ketua peneliti pada proyek ini.',
            ])
            ->assertRedirect(route('reviewer.assignments.index'));
        $this->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()->assertSee('Penugasan perlu diganti');
    }

    public function test_second_reviewer_cannot_submit_evaluation_after_first_reviewer_reports_conflict(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.conflict', ['assignmentId' => $id]), [
                'conflict_reason' => 'Saya memiliki kolaborasi riset aktif dengan ketua peneliti pada proyek ini.',
            ])->assertRedirect(route('reviewer.assignments.index'));

        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()
            ->assertSee('Penilaian dihentikan sementara')
            ->assertDontSee('Berikan penilaian Anda');

        $this->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertSessionHas('info');

        $this->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()
            ->assertSee('Penugasan perlu diganti')
            ->assertDontSee('Seluruh reviewer menyetujui pengajuan');
    }

    public function test_panel_conflict_is_not_counted_as_pending_for_the_other_reviewer(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.conflict', ['assignmentId' => $id]), [
                'conflict_reason' => 'Saya memiliki kolaborasi riset aktif dengan ketua peneliti pada proyek ini.',
            ])->assertRedirect();

        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->get(route('reviewer.dashboard'))
            ->assertOk()
            ->assertSee('Konflik kepentingan')
            ->assertDontSee('Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir');
    }

    public function test_reviewer_conflict_form_is_hidden_after_own_decision_is_locked(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $this->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()
            ->assertSee('Keputusan telah dikirim')
            ->assertDontSee('Laporkan konflik kepentingan');
    }

    public function test_reviewer_assignment_detail_contains_no_internal_screen_code_labels(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.assignments.show', ['assignmentId' => $id]))
            ->assertOk()
            ->assertDontSee('S17')
            ->assertDontSee('S18')
            ->assertDontSee('S19')
            ->assertDontSee('S20');
    }
    public function test_reviewer_sidebar_contains_decision_history_and_profile_notifications(): void
    {
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.dashboard'))
            ->assertOk()
            ->assertSee('Riwayat Keputusan')
            ->assertSee('Profil &amp; Notifikasi', false);
    }

    public function test_reviewer_can_open_decision_history_and_sees_empty_state_before_submitting(): void
    {
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.decisions.index'))
            ->assertOk()
            ->assertSee('Riwayat Keputusan')
            ->assertSee('Belum ada keputusan');
    }

    public function test_decision_history_contains_only_the_logged_in_reviewers_own_decision(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $this->get(route('reviewer.decisions.index'))
            ->assertOk()
            ->assertSee('Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir');

        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->get(route('reviewer.decisions.index'))
            ->assertOk()
            ->assertSee('Belum ada keputusan')
            ->assertDontSee('Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir');
    }

    public function test_reviewer_profile_shows_verified_identity_and_notification_preferences(): void
    {
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.profile'))
            ->assertOk()
            ->assertSee('Profil &amp; Notifikasi', false)
            ->assertSee('Dr. Rina Kurniawati')
            ->assertSee('Universitas Sriwijaya')
            ->assertSee('Pemberitahuan penugasan baru')
            ->assertSee('Notifikasi');
    }

    public function test_reviewer_can_update_phone_and_notification_preferences_without_changing_role(): void
    {
        $this->withSession($this->reviewerSession())
            ->post(route('reviewer.profile.update'), [
                'phone' => '081234567890',
                'assignment_updates' => '1',
                'panel_updates' => '1',
            ])
            ->assertRedirect(route('reviewer.profile'))
            ->assertSessionHas('prototype_user.phone', '081234567890')
            ->assertSessionHas('prototype_user.role', 'reviewer')
            ->assertSessionHas('prototype_reviewer_preferences.reviewer_a.assignment_updates', true)
            ->assertSessionHas('prototype_reviewer_preferences.reviewer_a.deadline_reminders', false)
            ->assertSessionHas('prototype_reviewer_preferences.reviewer_a.panel_updates', true);
    }

    public function test_reviewer_profile_rejects_invalid_phone_number(): void
    {
        $this->withSession($this->reviewerSession())
            ->from(route('reviewer.profile'))
            ->post(route('reviewer.profile.update'), ['phone' => 'abc'])
            ->assertRedirect(route('reviewer.profile'))
            ->assertSessionHasErrors('phone');
    }

    public function test_reviewer_receives_own_decision_notification_and_can_mark_it_read(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $notificationId = 'own-decision-'.$id.'-reviewer_a';
        $this->withSession($this->reviewerSession())
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $this->get(route('reviewer.profile'))
            ->assertOk()
            ->assertSee('Keputusan evaluasi tersimpan');

        $this->post(route('reviewer.notifications.read', ['notificationId' => $notificationId]))
            ->assertRedirect();
        $this->get(route('reviewer.profile'))
            ->assertOk()
            ->assertSee('Sudah dibaca');
    }

    public function test_reviewer_can_mark_all_notifications_as_read(): void
    {
        $this->withSession($this->reviewerSession())
            ->get(route('reviewer.profile'))->assertOk();
        $this->post(route('reviewer.notifications.read-all'))
            ->assertRedirect();
        $this->get(route('reviewer.profile'))
            ->assertOk()
            ->assertSee('0 notifikasi belum dibaca');
    }

    public function test_non_reviewer_cannot_access_decision_history_or_profile(): void
    {
        $session = ['prototype_user' => ['name' => 'Nadia', 'email' => 'donatur@resfund.test', 'role' => 'donatur']];
        $this->withSession($session)->get(route('reviewer.decisions.index'))->assertRedirect(route('home'));
        $this->withSession($session)->get(route('reviewer.profile'))->assertRedirect(route('home'));
    }


    public function test_suspended_reviewer_session_cannot_open_dashboard_or_submit_evaluation(): void
    {
        $session = $this->reviewerSession('reviewer_a') + [
            'prototype_admin_reviewer_status' => ['reviewer_a' => 'inactive'],
        ];
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);

        $this->withSession($session)
            ->get(route('reviewer.dashboard'))
            ->assertForbidden();

        $this->withSession($session)
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertForbidden();
    }

    public function test_deactivating_one_panel_member_invalidates_any_payout_ready_aggregate(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $session = $this->reviewerSession('reviewer_a') + [
            'prototype_admin_reviewer_status' => ['reviewer_b' => 'inactive'],
        ];
        $this->withSession($session)
            ->get(route('reviewer.assignments.index'))
            ->assertOk()
            ->assertSee('Menunggu penugasan dua reviewer aktif');
    }

    public function test_payout_cannot_be_ready_with_only_one_reviewer_even_if_that_reviewer_approves(): void
    {
        $aggregate = ReviewerPrototypeData::aggregate([
            'institution' => 'Universitas Lampung',
            'assigned_reviewer_ids' => ['reviewer_a'],
            'assigned_reviewers' => [
                ['id' => 'reviewer_a', 'institution' => 'Universitas Sriwijaya'],
            ],
            'reviews' => ['reviewer_a' => ['decision' => 'approved']],
        ]);

        $this->assertSame('unassigned', $aggregate['key']);
        $this->assertFalse($aggregate['payout_ready']);
    }

    public function test_duplicate_reviewer_ids_do_not_count_as_two_independent_reviewers(): void
    {
        $aggregate = ReviewerPrototypeData::aggregate([
            'institution' => 'Universitas Lampung',
            'assigned_reviewer_ids' => ['reviewer_a', 'reviewer_a'],
            'assigned_reviewers' => [
                ['id' => 'reviewer_a', 'institution' => 'Universitas Sriwijaya'],
                ['id' => 'reviewer_b', 'institution' => 'Institut Teknologi Sumatera'],
            ],
            'reviews' => ['reviewer_a' => ['decision' => 'approved']],
        ]);

        $this->assertSame('unassigned', $aggregate['key']);
        $this->assertFalse($aggregate['payout_ready']);
    }

    public function test_reviewer_endpoint_rejects_decision_when_panel_contains_only_one_reviewer(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $assignment = [
            'id' => $id,
            'project_slug' => 'panel-surya-pesisir-lampung',
            'project_title' => 'Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir',
            'researcher_name' => 'Dr. Andi Prasetyo',
            'researcher_email' => 'peneliti@resfund.test',
            'institution' => 'Universitas Lampung',
            'category' => 'Energi Terbarukan',
            'milestone_number' => 2,
            'milestone_name' => 'Pembuatan dan integrasi prototipe',
            'allocation' => 45,
            'requested_amount' => 36000000,
            'due_at' => '2026-11-24',
            'assigned_reviewer_ids' => ['reviewer_a'],
            'assigned_reviewers' => [['id' => 'reviewer_a', 'name' => 'Dr. Rina Kurniawati', 'institution' => 'Universitas Sriwijaya', 'expertise' => 'Instrumentasi']],
            'reviews' => [],
            'documents' => [],
            'aggregate' => ['key' => 'pending', 'label' => 'Menunggu penilaian', 'payout_ready' => false, 'completed' => false],
        ];

        $this->withSession($this->reviewerSession('reviewer_a') + [ReviewerPrototypeData::SESSION_KEY => [$assignment]])
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertStatus(409);
    }

    public function test_conflict_report_cannot_be_submitted_twice_or_after_panel_is_blocked(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $payload = ['conflict_reason' => 'Saya pernah menjadi kolaborator aktif dengan ketua peneliti pada proyek ini.'];

        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.conflict', ['assignmentId' => $id]), $payload)
            ->assertRedirect(route('reviewer.assignments.index'));

        $this->post(route('reviewer.assignments.conflict', ['assignmentId' => $id]), $payload)
            ->assertStatus(409);
    }

    public function test_administrator_cannot_replace_panel_after_final_decision(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();
        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $admin = ['prototype_user' => [
            'name' => 'Administrator ResFund', 'email' => 'admin@resfund.test',
            'role' => 'administrator', 'role_label' => 'Administrator', 'admin_id' => 'admin_01',
        ]];
        $this->withSession($admin)
            ->post(route('administrator.assignments.assign', ['assignmentId' => $id]), [
                'reviewer_ids' => ['reviewer_a', 'reviewer_b'],
                'reason' => 'Mencoba mengganti panel yang sudah memberikan keputusan akhir.',
            ])
            ->assertStatus(409);
    }

    public function test_final_panel_decision_remains_immutable_if_a_reviewer_is_deactivated_later(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();
        $this->withSession($this->reviewerSession('reviewer_b', 'reviewer2@resfund.test'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $admin = ['prototype_user' => [
            'name' => 'Administrator ResFund', 'email' => 'admin@resfund.test',
            'role' => 'administrator', 'role_label' => 'Administrator', 'admin_id' => 'admin_01',
        ], 'prototype_admin_reviewer_status' => ['reviewer_b' => 'inactive']];

        $this->withSession($admin)
            ->post(route('administrator.assignments.assign', ['assignmentId' => $id]), [
                'reviewer_ids' => ['reviewer_a', 'reviewer_b'],
                'reason' => 'Mencoba mengganti panel setelah keputusan akhir tersimpan.',
            ])
            ->assertStatus(409);
    }

    public function test_reviewer_decision_remains_in_history_after_administrator_reassigns_panel(): void
    {
        $id = $this->assignmentId('panel-surya-pesisir-lampung', 2);
        $this->withSession($this->reviewerSession('reviewer_a'))
            ->post(route('reviewer.assignments.evaluate', ['assignmentId' => $id]), $this->approvedPayload())
            ->assertRedirect();

        $admin = ['prototype_user' => [
            'name' => 'Administrator ResFund', 'email' => 'admin@resfund.test',
            'role' => 'administrator', 'role_label' => 'Administrator', 'admin_id' => 'admin_01',
        ], 'prototype_admin_added_reviewers' => [
            'reviewer_c' => [
                'id' => 'reviewer_c', 'name' => 'Dr. Maya Lestari',
                'email' => 'maya.reviewer@ipb.test', 'phone' => '081234567899',
                'institution' => 'Institut Pertanian Bogor',
                'expertise' => 'Bioteknologi dan material ramah lingkungan',
                'status' => 'active',
            ],
        ]];

        $this->withSession($admin)
            ->post(route('administrator.assignments.assign', ['assignmentId' => $id]), [
                'reviewer_ids' => ['reviewer_b', 'reviewer_c'],
                'reason' => 'Mengganti komposisi panel untuk melanjutkan penilaian secara independen.',
            ])->assertRedirect();

        $this->withSession($this->reviewerSession('reviewer_a'))
            ->get(route('reviewer.decisions.index'))
            ->assertOk()
            ->assertSee('Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir')
            ->assertSee('Panel sebelumnya');
    }

    public function test_admin_can_see_unassigned_submission_when_two_active_reviewers_are_unavailable(): void
    {
        $admin = [
            'prototype_user' => [
                'name' => 'Administrator ResFund', 'email' => 'admin@resfund.test',
                'role' => 'administrator', 'role_label' => 'Administrator', 'admin_id' => 'admin_01',
            ],
            'prototype_admin_reviewer_status' => ['reviewer_a' => 'inactive', 'reviewer_b' => 'inactive'],
        ];

        $this->withSession($admin)
            ->get(route('administrator.assignments.index'))
            ->assertOk()
            ->assertSee('Belum memiliki dua reviewer');
    }

}
