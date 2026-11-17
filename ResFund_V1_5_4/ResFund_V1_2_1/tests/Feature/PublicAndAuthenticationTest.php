<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicAndAuthenticationTest extends TestCase
{
    private const PROJECT_SLUG = 'sintesis-baterai-limbah-tebu';

    public function test_public_pages_are_accessible_without_authentication(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Dukung penelitian');
        $this->get(route('projects.index'))->assertOk()->assertSee('Jelajahi proyek riset');
        $this->get(route('projects.show', self::PROJECT_SLUG))->assertOk()->assertSee('Detail proyek riset');
        $this->get(route('finance.public'))->assertOk()->assertSee('Ringkasan keuangan publik')->assertSee('Jelajahi transparansi pendanaan');
        $this->get(route('how-it-works'))->assertOk()->assertSee('Bagaimana ResFund bekerja?');
    }

    public function test_project_search_and_filters_can_be_combined(): void
    {
        $this->get(route('projects.index', ['q' => 'baterai', 'kategori' => 'Energi', 'status' => 'ACTIVE']))
            ->assertOk()
            ->assertSee('Sintesis Baterai Ramah Lingkungan dari Limbah Tebu')
            ->assertDontSee('Pengembangan Bioplastik Ramah Lingkungan dari Pati Singkong');
    }

    public function test_unknown_project_returns_404(): void
    {
        $this->get(route('projects.show', 'proyek-tidak-ada'))->assertNotFound();
    }

    public function test_guest_is_sent_to_login_before_donation_entry(): void
    {
        $this->get(route('donation.entry', self::PROJECT_SLUG))
            ->assertRedirect(route('login', ['intent' => 'donasi', 'project' => self::PROJECT_SLUG]));
    }

    public function test_donor_registration_assigns_donor_role_and_logs_user_in(): void
    {
        $this->post(route('register.submit'), [
            'role' => 'donatur',
            'name' => 'Dinda Putri',
            'email' => 'dinda@example.test',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('donor.dashboard'))
            ->assertSessionHas('prototype_user.role', 'donatur')
            ->assertSessionHas('prototype_user.role_label', 'Donatur');
    }

    public function test_invalid_login_is_rejected_with_field_error(): void
    {
        $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'not-an-email',
            'password' => 'short',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_registration_requires_an_explicit_role_choice(): void
    {
        $this->from(route('register'))->post(route('register.submit'), [
            'name' => 'Dinda Putri',
            'email' => 'dinda@example.test',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('register'))
            ->assertSessionHasErrors('role');
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $this->from(route('register'))->post(route('register.submit'), [
            'role' => 'donatur',
            'name' => 'Dinda Putri',
            'email' => 'dinda@example.test',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Different123!',
            'terms' => '1',
        ])->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');
    }

    public function test_researcher_registration_requires_academic_identity(): void
    {
        $this->from(route('register'))->post(route('register.submit'), [
            'role' => 'peneliti',
            'name' => 'Andi Prasetyo',
            'email' => 'andi@example.test',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('register'))
            ->assertSessionHasErrors(['researcher_type', 'academic_id', 'institution']);
    }

    public function test_researcher_registration_is_pending_academic_verification(): void
    {
        $this->post(route('register.submit'), [
            'role' => 'peneliti',
            'name' => 'Andi Prasetyo',
            'email' => 'andi@example.test',
            'phone' => '081234567890',
            'researcher_type' => 'dosen',
            'academic_id' => '0123456789',
            'institution' => 'Universitas Lampung',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('home'))
            ->assertSessionHas('prototype_user.role', 'peneliti')
            ->assertSessionHas('prototype_user.verification_status', 'menunggu_verifikasi');
    }

    public function test_researcher_cannot_register_from_donation_intent(): void
    {
        $this->from(route('register', ['intent' => 'donasi', 'project' => self::PROJECT_SLUG]))
            ->post(route('register.submit'), [
                'role' => 'peneliti',
                'name' => 'Andi Prasetyo',
                'email' => 'andi@example.test',
                'phone' => '081234567890',
                'researcher_type' => 'dosen',
                'academic_id' => '0123456789',
                'institution' => 'Universitas Lampung',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'terms' => '1',
                'intent' => 'donasi',
                'project_slug' => self::PROJECT_SLUG,
            ])->assertRedirect(route('register', ['intent' => 'donasi', 'project' => self::PROJECT_SLUG]))
            ->assertSessionHasErrors('role');
    }

    public function test_reviewer_and_admin_cannot_self_register(): void
    {
        foreach (['reviewer', 'admin'] as $role) {
            $this->from(route('register'))->post(route('register.submit'), [
                'role' => $role,
                'name' => 'Pengguna Uji',
                'email' => $role.'@example.test',
                'phone' => '081234567890',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'terms' => '1',
            ])->assertRedirect(route('register'))->assertSessionHasErrors('role');
        }
    }

    public function test_duplicate_registered_email_is_rejected_in_same_prototype_session(): void
    {
        $payload = [
            'role' => 'donatur',
            'name' => 'Dinda Putri',
            'email' => 'dinda@example.test',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ];

        $this->post(route('register.submit'), $payload)->assertRedirect(route('donor.dashboard'));
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->from(route('register'))->post(route('register.submit'), $payload)
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
    }

    public function test_registered_donor_can_log_in_after_logging_out(): void
    {
        $this->post(route('register.submit'), [
            'role' => 'donatur',
            'name' => 'Dinda Putri',
            'email' => 'dinda@example.test',
            'phone' => '081234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('donor.dashboard'));

        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->post(route('login.submit'), [
            'email' => 'dinda@example.test',
            'password' => 'Password123!',
        ])->assertRedirect(route('donor.dashboard'))
            ->assertSessionHas('prototype_user.role', 'donatur');
    }

    public function test_donor_demo_login_continues_to_donation_access_gate(): void
    {
        $this->post(route('login.submit'), [
            'email' => 'donatur@resfund.test',
            'password' => 'ResFund123!',
            'intent' => 'donasi',
            'project_slug' => self::PROJECT_SLUG,
        ])->assertRedirect(route('donation.entry', self::PROJECT_SLUG))
            ->assertSessionHas('prototype_user.role', 'donatur');
    }

    public function test_logged_in_researcher_cannot_use_donor_donation_flow(): void
    {
        $this->withSession([
            'prototype_user' => [
                'name' => 'Andi Prasetyo',
                'email' => 'andi@example.test',
                'role' => 'peneliti',
                'role_label' => 'Peneliti',
            ],
        ])->get(route('donation.entry', self::PROJECT_SLUG))
            ->assertRedirect(route('projects.show', self::PROJECT_SLUG))
            ->assertSessionHas('info');
    }

    public function test_authenticated_session_cannot_switch_account_via_public_post(): void
    {
        $this->withSession([
            'prototype_user' => [
                'name' => 'Nadia Putri',
                'email' => 'donatur@resfund.test',
                'role' => 'donatur',
                'role_label' => 'Donatur',
            ],
        ])->post(route('register.submit'), [
            'role' => 'peneliti',
            'name' => 'Andi Prasetyo',
            'email' => 'andi@example.test',
            'phone' => '081234567890',
            'researcher_type' => 'dosen',
            'academic_id' => '0123456789',
            'institution' => 'Universitas Lampung',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('home'))
            ->assertSessionHas('prototype_user.role', 'donatur');
    }

    public function test_demo_login_and_logout_work(): void
    {
        $this->post(route('login.submit'), [
            'email' => 'donatur@resfund.test',
            'password' => 'ResFund123!',
        ])->assertRedirect(route('donor.dashboard'))
            ->assertSessionHas('prototype_user.role', 'donatur');

        $this->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionMissing('prototype_user');
    }

    public function test_public_financial_summary_does_not_show_private_receipt_details(): void
    {
        $this->get(route('finance.public'))
            ->assertOk()
            ->assertSee('Ringkasan publik')
            ->assertSee('Rincian ledger')
            ->assertDontSee('Lihat kuitansi');
    }
}
