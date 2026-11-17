<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResearcherDashboardTest extends TestCase
{
    private function researcherSession(array $overrides = []): array
    {
        return [
            'prototype_user' => array_merge([
                'name' => 'Dr. Andi Prasetyo',
                'email' => 'peneliti@resfund.test',
                'phone' => '0812-1111-2233',
                'role' => 'peneliti',
                'role_label' => 'Peneliti',
                'verification_status' => 'terverifikasi',
                'institution' => 'Universitas Lampung',
                'academic_id' => '0012087803',
                'researcher_type' => 'dosen',
            ], $overrides),
        ];
    }

    public function test_guest_is_redirected_to_login_from_researcher_dashboard(): void
    {
        $this->get(route('researcher.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_donor_cannot_open_researcher_area(): void
    {
        $this->withSession(['prototype_user' => [
            'name' => 'Nadia Putri',
            'email' => 'donatur@resfund.test',
            'role' => 'donatur',
        ]])->get(route('researcher.dashboard'))
            ->assertRedirect(route('home'));
    }

    public function test_demo_researcher_dashboard_shows_summary_and_navigation(): void
    {
        $this->withSession($this->researcherSession())
            ->get(route('researcher.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Peneliti')
            ->assertSee('Proposal &amp; Kampanye')
            ->assertSee('Milestone &amp; Pencairan')
            ->assertSee('Pengeluaran &amp; Kuitansi')
            ->assertSee('Profil &amp; Validasi')
            ->assertSee('Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir');
    }

    public function test_researcher_can_open_profile_proposals_milestones_and_expenses(): void
    {
        $this->withSession($this->researcherSession())->get(route('researcher.profile'))->assertOk()->assertSee('Profil dan validasi peneliti');
        $this->withSession($this->researcherSession())->get(route('researcher.proposals.index'))->assertOk()->assertSee('Proposal dan kampanye riset');
        $this->withSession($this->researcherSession())->get(route('researcher.milestones'))->assertOk()->assertSee('Milestone dan pengajuan pencairan');
        $this->withSession($this->researcherSession())->get(route('researcher.expenses'))->assertOk()->assertSee('Pencatatan pengeluaran riset');
    }


    public function test_researcher_screens_do_not_show_internal_screen_codes_or_redundant_profile_button(): void
    {
        $session = $this->researcherSession();
        $urls = [
            route('researcher.dashboard'),
            route('researcher.profile'),
            route('researcher.proposals.index'),
            route('researcher.proposals.create'),
            route('researcher.proposals.show', ['slug' => 'panel-surya-pesisir-lampung']),
            route('researcher.milestones'),
            route('researcher.expenses'),
        ];

        foreach ($urls as $url) {
            $response = $this->withSession($session)->get($url)->assertOk();
            $response->assertDontSee('S11');
            $response->assertDontSee('S12');
            $response->assertDontSee('S13');
            $response->assertDontSee('S14');
            $response->assertDontSee('S15');
            $response->assertDontSee('S16');
            $response->assertDontSee('S19');
            $response->assertDontSee('S20');
            $response->assertDontSee('donor-notification-button', false);
            $response->assertDontSee('aria-hidden="true">i</span>', false);
        }
    }

    public function test_researcher_profile_keeps_success_indicator_text_and_accessible_status(): void
    {
        $this->withSession($this->researcherSession())
            ->get(route('researcher.profile'))
            ->assertOk()
            ->assertSee('Identitas telah diverifikasi')
            ->assertSee('Registrasi akun Peneliti')
            ->assertSee('Verifikasi NIDN/NIM')
            ->assertSee('Akses pengelolaan proposal')
            ->assertSee('Catatan:')
            ->assertDontSee('aria-hidden="true">i</span>', false);
    }

    public function test_proposal_form_explains_two_to_five_milestones_and_campaign_duration(): void
    {
        $this->withSession($this->researcherSession())
            ->get(route('researcher.proposals.create'))
            ->assertOk()
            ->assertSee('2 sampai 5 milestone')
            ->assertSee('15–90 hari kalender')
            ->assertSee('Total alokasi');
    }

    public function test_proposal_store_rejects_milestone_allocation_not_equal_to_one_hundred_percent(): void
    {
        $this->withSession($this->researcherSession())
            ->from(route('researcher.proposals.create'))
            ->post(route('researcher.proposals.store'), [
                'title' => 'Penelitian Material Komposit Lampung',
                'category' => 'Lingkungan',
                'abstract' => 'Penelitian ini menguji bahan komposit dari sumber daya alam lokal.',
                'target' => 30000000,
                'campaign_duration' => 45,
                'milestones' => [
                    ['name' => 'Persiapan bahan penelitian', 'description' => 'Menyiapkan bahan dan instrumen penelitian.', 'allocation' => 40],
                    ['name' => 'Pengujian dan analisis', 'description' => 'Melakukan pengujian dan analisis hasil penelitian.', 'allocation' => 50],
                ],
            ])
            ->assertRedirect(route('researcher.proposals.create'))
            ->assertSessionHasErrors('milestones');
    }

    public function test_proposal_store_creates_draft_with_two_to_five_milestones(): void
    {
        $response = $this->withSession($this->researcherSession())
            ->post(route('researcher.proposals.store'), [
                'title' => 'Penelitian Material Komposit Lampung',
                'category' => 'Lingkungan',
                'abstract' => 'Penelitian ini menguji bahan komposit dari sumber daya alam lokal.',
                'target' => 30000001,
                'campaign_duration' => 45,
                'milestones' => [
                    ['name' => 'Persiapan bahan penelitian', 'description' => 'Menyiapkan bahan dan instrumen penelitian.', 'allocation' => 40],
                    ['name' => 'Pengujian dan analisis', 'description' => 'Melakukan pengujian dan analisis hasil penelitian.', 'allocation' => 60],
                ],
            ]);

        $response->assertRedirect();
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Penelitian Material Komposit Lampung')
            ->assertSee('Draft');
    }

    public function test_unverified_researcher_cannot_submit_proposal_for_validation(): void
    {
        $session = $this->researcherSession(['verification_status' => 'menunggu_verifikasi']);
        $response = $this->withSession($session)
            ->post(route('researcher.proposals.submit', ['slug' => 'biokomposit-serat-alam-lampung']));

        $response->assertRedirect();
        $response->assertSessionHasErrors('verification');
    }

    public function test_initial_payout_requires_sptjm_and_cannot_exceed_thirty_percent(): void
    {
        $this->withSession($this->researcherSession())
            ->from(route('researcher.milestones'))
            ->post(route('researcher.milestones.submit', [
                'slug' => 'sistem-pemantauan-kualitas-air-pesisir',
                'number' => 1,
            ]), [
                'requested_amount' => 10501000,
                'sptjm_signed' => '1',
            ])
            ->assertRedirect(route('researcher.milestones'))
            ->assertSessionHasErrors('requested_amount');
    }

    public function test_valid_initial_payout_is_submitted_to_two_independent_reviewers(): void
    {
        $this->withSession($this->researcherSession())
            ->post(route('researcher.milestones.submit', [
                'slug' => 'sistem-pemantauan-kualitas-air-pesisir',
                'number' => 1,
            ]), [
                'requested_amount' => 10500000,
                'sptjm_signed' => '1',
            ])
            ->assertRedirect(route('researcher.milestones'));

        $this->get(route('researcher.milestones'))
            ->assertOk()
            ->assertSee('Menunggu evaluasi reviewer')
            ->assertSee('Reviewer independen 1')
            ->assertSee('Reviewer independen 2');
    }

    public function test_later_milestone_requires_both_technical_and_financial_documents(): void
    {
        $this->withSession($this->researcherSession())
            ->from(route('researcher.milestones'))
            ->post(route('researcher.milestones.submit', [
                'slug' => 'panel-surya-pesisir-lampung',
                'number' => 2,
            ]), [])
            ->assertRedirect(route('researcher.milestones'))
            ->assertSessionHasErrors(['deliverable', 'financial_accountability']);
    }

    public function test_expense_ledger_displays_final_records_and_append_only_rule(): void
    {
        $this->withSession($this->researcherSession())
            ->get(route('researcher.expenses'))
            ->assertOk()
            ->assertSee('EXP-DEMO-001')
            ->assertSee('Final Ledger · Terkunci')
            ->assertSee('Append-only');
    }

    public function test_final_ledger_entry_cannot_be_published_or_modified_again(): void
    {
        $this->withSession($this->researcherSession())
            ->post(route('researcher.expenses.publish', ['id' => 'EXP-DEMO-001']))
            ->assertForbidden();
    }

    public function test_expense_amount_cannot_exceed_remaining_disbursed_milestone_funds(): void
    {
        Storage::fake('local');
        $this->withSession($this->researcherSession())
            ->from(route('researcher.expenses'))
            ->post(route('researcher.expenses.store'), [
                'project_slug' => 'panel-surya-pesisir-lampung',
                'milestone_number' => 1,
                'date' => '2026-11-17',
                'recipient' => 'Toko Komponen Penelitian',
                'category' => 'ALAT',
                'description' => 'Pembelian komponen tambahan untuk pengujian panel surya.',
                'amount' => 11000000,
                'receipt' => UploadedFile::fake()->image('kuitansi.jpg'),
            ])
            ->assertRedirect(route('researcher.expenses'))
            ->assertSessionHasErrors('amount');
    }

    public function test_login_demo_researcher_redirects_to_researcher_dashboard(): void
    {
        $this->post(route('login.submit'), [
            'email' => 'peneliti@resfund.test',
            'password' => 'ResFund123!',
        ])
            ->assertRedirect(route('researcher.dashboard'))
            ->assertSessionHas('prototype_user.role', 'peneliti');
    }
    public function test_proposal_rejects_milestone_allocation_below_rupiah_minimum(): void
    {
        $this->withSession($this->researcherSession())
            ->from(route('researcher.proposals.create'))
            ->post(route('researcher.proposals.store'), [
                'title' => 'Penelitian Alokasi Dana Komposit',
                'category' => 'Lingkungan',
                'abstract' => 'Penelitian ini menguji alokasi anggaran riset secara rinci dan bertahap.',
                'target' => 10000,
                'campaign_duration' => 45,
                'milestones' => [
                    ['name' => 'Tahap persiapan bahan', 'description' => 'Menyiapkan bahan dan instrumen awal.', 'allocation' => 99.99],
                    ['name' => 'Tahap pengujian akhir', 'description' => 'Menguji bahan dan menganalisis hasil akhir.', 'allocation' => 0.01],
                ],
            ])
            ->assertRedirect(route('researcher.proposals.create'))
            ->assertSessionHasErrors('milestones');
    }

    public function test_milestone_submission_is_blocked_before_upload_when_two_reviewers_are_not_available(): void
    {
        $session = $this->researcherSession();
        $session['prototype_admin_reviewer_status'] = ['reviewer_a' => 'inactive', 'reviewer_b' => 'inactive'];

        $this->withSession($session)
            ->post(route('researcher.milestones.submit', [
                'slug' => 'sistem-pemantauan-kualitas-air-pesisir',
                'number' => 1,
            ]), [
                'requested_amount' => 10500000,
                'sptjm_signed' => '1',
            ])
            ->assertStatus(422);
    }

}
