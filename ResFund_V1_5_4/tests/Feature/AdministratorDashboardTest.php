<?php

namespace Tests\Feature;

use App\Support\AdminPrototypeData;
use App\Support\ReviewerPrototypeData;
use Tests\TestCase;

class AdministratorDashboardTest extends TestCase
{
    private function adminSession(): array
    {
        return ['prototype_user' => [
            'name' => 'Administrator ResFund',
            'email' => 'admin@resfund.test',
            'role' => 'administrator',
            'role_label' => 'Administrator',
            'admin_id' => 'admin_01',
        ]];
    }

    private function assignmentId(): string
    {
        return 'ASG-'.strtoupper(substr(sha1('panel-surya-pesisir-lampung-2-1'), 0, 8));
    }

    public function test_admin_demo_login_opens_admin_dashboard(): void
    {
        $this->post(route('login.submit'), [
            'email' => 'admin@resfund.test',
            'password' => 'ResFund123!',
        ])->assertRedirect(route('administrator.dashboard'))
          ->assertSessionHas('prototype_user.role', 'administrator');

        $this->get(route('administrator.dashboard'))->assertOk()->assertSee('Pusat Kendali ResFund');
    }

    public function test_admin_routes_are_protected_from_other_roles(): void
    {
        $this->withSession(['prototype_user' => ['name' => 'Nadia', 'role' => 'donatur']])
            ->get(route('administrator.dashboard'))->assertRedirect(route('home'));

        $this->withSession(['prototype_user' => ['name' => 'Andi', 'role' => 'peneliti']])
            ->get(route('administrator.users.index'))->assertRedirect(route('home'));

        $this->withSession(['prototype_user' => ['name' => 'Rina', 'role' => 'reviewer']])
            ->get(route('administrator.monitoring.index'))->assertRedirect(route('home'));
    }

    public function test_admin_dashboard_and_all_management_pages_render(): void
    {
        $this->withSession($this->adminSession())->get(route('administrator.dashboard'))
            ->assertOk()->assertSee('Manajemen Pengguna')->assertSee('Monitoring &amp; Audit Log');
        $this->withSession($this->adminSession())->get(route('administrator.users.index'))
            ->assertOk()->assertSee('Nadia Putri')->assertSee('Dr. Andi Prasetyo');
        $this->withSession($this->adminSession())->get(route('administrator.researchers.index'))
            ->assertOk()->assertSee('Rina Amelia');
        $this->withSession($this->adminSession())->get(route('administrator.reviewers.index'))
            ->assertOk()->assertSee('Dr. Rina Kurniawati')->assertSee('Prof. Budi Santoso');
        $this->withSession($this->adminSession())->get(route('administrator.assignments.index'))
            ->assertOk()->assertSee('Penugasan Reviewer');
        $this->withSession($this->adminSession())->get(route('administrator.monitoring.index'))
            ->assertOk()->assertSee('Hanya baca');
    }

    public function test_admin_can_approve_researcher_validation_and_log_action(): void
    {
        $id = 'USR-RES-002';
        $this->withSession($this->adminSession())
            ->post(route('administrator.researchers.decide', ['userId' => $id]), [
                'decision' => 'approve',
                'notes' => 'NIM dan institusi telah diperiksa dan sesuai dengan data pengajuan.',
            ])->assertRedirect(route('administrator.researchers.show', ['userId' => $id]))
              ->assertSessionHas('success');

        $this->withSession($this->adminSession())->get(route('administrator.researchers.show', ['userId' => $id]))
            ->assertOk()->assertSee('terverifikasi');
    }

    public function test_researcher_cannot_be_activated_from_user_management_before_validation(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('administrator.users.status', ['userId' => 'USR-RES-002']), ['status' => 'active'])
            ->assertSessionHasErrors();
    }

    public function test_admin_can_create_reviewer_and_new_account_can_login(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('administrator.reviewers.store'), [
                'name' => 'Dr. Maya Lestari',
                'email' => 'maya.reviewer@ipb.test',
                'phone' => '081234567899',
                'institution' => 'Institut Pertanian Bogor',
                'expertise' => 'Bioteknologi dan material ramah lingkungan',
                'password' => 'ReviewerPass123!',
            ])->assertRedirect(route('administrator.reviewers.index'));

        $this->withSession($this->adminSession())->get(route('administrator.reviewers.index'))
            ->assertOk()->assertSee('Dr. Maya Lestari');

        $this->post(route('logout'));
        $this->post(route('login.submit'), [
            'email' => 'maya.reviewer@ipb.test',
            'password' => 'ReviewerPass123!',
        ])->assertRedirect(route('reviewer.dashboard'))->assertSessionHas('prototype_user.reviewer_id');
        $this->get(route('reviewer.dashboard'))->assertOk()->assertSee('Dr. Maya Lestari');
    }

    public function test_admin_can_reassign_two_independent_reviewers(): void
    {
        $id = $this->assignmentId();
        $this->withSession($this->adminSession())
            ->post(route('administrator.assignments.assign', ['assignmentId' => $id]), [
                'reviewer_ids' => ['reviewer_a', 'reviewer_b'],
                'reason' => 'Penetapan panel untuk menjaga penilaian independen dan sesuai bidang kepakaran.',
            ])->assertRedirect(route('administrator.assignments.show', ['assignmentId' => $id]));
    }

    public function test_admin_assignment_rejects_duplicate_or_invalid_panel(): void
    {
        $id = $this->assignmentId();
        $this->withSession($this->adminSession())
            ->post(route('administrator.assignments.assign', ['assignmentId' => $id]), [
                'reviewer_ids' => ['reviewer_a', 'reviewer_a'],
                'reason' => 'Panel reviewer perlu ditata ulang dengan alasan yang jelas.',
            ])->assertSessionHasErrors('reviewer_ids.1');
    }

    public function test_audit_log_is_read_only_and_contains_admin_actions(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('administrator.researchers.decide', ['userId' => 'USR-RES-002']), [
                'decision' => 'reject',
                'notes' => 'Dokumen identitas akademik belum sesuai dan perlu diperbaiki.',
            ])->assertRedirect();

        $this->withSession($this->adminSession())
            ->get(route('administrator.monitoring.index'))
            ->assertOk()->assertSee('Hanya baca')->assertSee('Validasi peneliti');
    }


    public function test_suspended_user_cannot_login_after_admin_changes_access_status(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('administrator.users.status', ['userId' => 'USR-DON-001']), ['status' => 'suspended'])
            ->assertRedirect();

        $this->post(route('logout'));
        $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'donatur@resfund.test',
            'password' => 'ResFund123!',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');
    }

    public function test_admin_dashboard_does_not_allow_other_roles_to_be_assigned_through_public_registration(): void
    {
        $this->from(route('register'))->post(route('register.submit'), [
            'role' => 'administrator',
            'name' => 'Admin Baru',
            'email' => 'admin-baru@example.test',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('register'))->assertSessionHasErrors('role');
    }

    public function test_admin_interface_does_not_show_conceptual_screen_codes(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('administrator.dashboard'))
            ->assertOk()->assertDontSee('S19')->assertDontSee('S20');
    }
    public function test_monitoring_money_kpi_uses_overflow_safe_markup(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('administrator.monitoring.index'))
            ->assertOk()
            ->assertSee('admin-kpi-card--money', false)
            ->assertSee('admin-kpi-currency', false)
            ->assertSee('Dana terhimpun bersih');
    }

}
