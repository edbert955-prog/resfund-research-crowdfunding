<?php

namespace Tests\Feature;

use Tests\TestCase;

class DonorDashboardTest extends TestCase
{
    private const BIOPLASTIC = 'bioplastik-pati-singkong';
    private const BATTERY = 'sintesis-baterai-limbah-tebu';

    private function demoDonorSession(): array
    {
        return [
            'prototype_user' => [
                'name' => 'Nadia Putri',
                'email' => 'donatur@resfund.test',
                'phone' => '0812-0000-1234',
                'role' => 'donatur',
                'role_label' => 'Donatur',
                'verification_status' => 'terverifikasi',
            ],
        ];
    }

    public function test_guest_cannot_open_donor_dashboard(): void
    {
        $this->get(route('donor.dashboard'))
            ->assertRedirect(route('login', ['next' => 'donatur']));
    }

    public function test_donor_dashboard_shows_donor_summary_and_navigation(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Donatur')
            ->assertSee('Donasi Saya / Riwayat')
            ->assertSee('Monitoring Proyek')
            ->assertSee('Ledger &amp; Preview Kuitansi')
            ->assertSee('Klarifikasi Audit')
            ->assertSee('Rp750.000');
    }

    public function test_researcher_cannot_open_donor_dashboard(): void
    {
        $this->withSession(['prototype_user' => ['name' => 'Andi', 'email' => 'andi@example.test', 'role' => 'peneliti']])
            ->get(route('donor.dashboard'))
            ->assertRedirect(route('home'));
    }

    public function test_donation_history_separates_payment_and_campaign_status(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.donations.index'))
            ->assertOk()
            ->assertSee('DN-2026-001')
            ->assertSee('SETTLED')
            ->assertSee('REFUNDED')
            ->assertSee('Target tidak tercapai');
    }

    public function test_donation_detail_shows_full_refund_reference(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.donations.show', ['donationId' => 'DN-2026-003']))
            ->assertOk()
            ->assertSee('Pengembalian dana selesai')
            ->assertSee('Rp150.000')
            ->assertSee('RF-RFD-20261101-003');
    }

    public function test_donor_can_monitor_project_and_milestone(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.monitoring.show', ['slug' => self::BIOPLASTIC]))
            ->assertOk()
            ->assertSee('Milestone proyek')
            ->assertSee('Disetujui oleh 2 reviewer')
            ->assertSee('FUNDED');
    }

    public function test_donor_can_open_verified_ledger_and_receipt_preview(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.ledger.show', ['slug' => self::BIOPLASTIC]))
            ->assertOk()
            ->assertSee('KWT-2026-001')
            ->assertSee('data-receipt-open', false)
            ->assertSee('PREVIEW DOKUMEN', false);
    }

    public function test_donor_without_settled_donation_cannot_open_private_ledger(): void
    {
        $this->withSession(['prototype_user' => ['name' => 'Pengguna Baru', 'email' => 'baru@example.test', 'role' => 'donatur']])
            ->get(route('donor.ledger.show', ['slug' => self::BIOPLASTIC]))
            ->assertForbidden();
    }

    public function test_public_user_can_only_access_public_financial_summary(): void
    {
        $this->get(route('finance.public'))
            ->assertOk()
            ->assertSee('Ringkasan publik')
            ->assertDontSee('KWT-2026-001')
            ->assertDontSee('Lihat kuitansi');
    }

    public function test_audit_clarification_form_is_available_for_donor_with_project_transaction(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.audit.create', ['slug' => self::BIOPLASTIC]))
            ->assertOk()
            ->assertSee('Ajukan Klarifikasi Audit')
            ->assertSee('KWT-2026-001');
    }

    public function test_audit_clarification_is_denied_when_project_has_no_published_ledger(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.audit.create', ['slug' => self::BATTERY]))
            ->assertForbidden();
    }

    public function test_donor_can_submit_audit_clarification_and_session_records_ticket(): void
    {
        $this->withSession($this->demoDonorSession())
            ->post(route('donor.audit.store', ['slug' => self::BIOPLASTIC]), [
                'ledger_id' => 'LED-001',
                'subject' => 'Permintaan rincian bahan',
                'message' => 'Mohon jelaskan rincian bahan pada transaksi ini secara lebih lengkap.',
            ])
            ->assertRedirect(route('donor.audit.create', ['slug' => self::BIOPLASTIC]))
            ->assertSessionHas('prototype_audit_tickets');
    }

    public function test_profile_can_be_updated_in_prototype_session(): void
    {
        $this->withSession($this->demoDonorSession())
            ->post(route('donor.profile.update'), [
                'name' => 'Nadia Putri Updated',
                'phone' => '081234567891',
            ])
            ->assertRedirect(route('donor.profile'))
            ->assertSessionHas('prototype_user.name', 'Nadia Putri Updated')
            ->assertSessionHas('prototype_user.phone', '081234567891');
    }


    public function test_donor_can_open_financial_summary_inside_donor_navigation(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.finance'))
            ->assertOk()
            ->assertSee('Ringkasan keuangan publik')
            ->assertSee('Laporan Keuangan')
            ->assertSee('Jelajahi transparansi pendanaan')
            ->assertSee('Sintesis Baterai Ramah Lingkungan dari Limbah Tebu')
            ->assertDontSee('KWT-2026-001');
    }

    public function test_dashboard_shows_newest_donation_first(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['DN-2026-002', 'DN-2026-001', 'DN-2026-003']);
    }

    public function test_ledger_has_pagination_and_category_filter(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.ledger.show', ['slug' => self::BIOPLASTIC]))
            ->assertOk()
            ->assertSee('Menampilkan 5 dari 8 entri sesuai filter')
            ->assertSee('Halaman 1 dari 2')
            ->assertSee('Kategori belanja');
    }

    public function test_refunded_donation_does_not_grant_private_ledger_access(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.ledger.show', ['slug' => 'serat-pisang-lahan-basah']))
            ->assertForbidden();
    }

    public function test_audit_form_rejects_ledger_entry_from_another_project(): void
    {
        $this->withSession($this->demoDonorSession())
            ->post(route('donor.audit.store', ['slug' => self::BIOPLASTIC]), [
                'ledger_id' => 'LED-999',
                'subject' => 'Pertanyaan atas transaksi',
                'message' => 'Mohon penjelasan atas transaksi yang dicatat pada halaman ini.',
            ])
            ->assertSessionHasErrors('ledger_id');
    }

    public function test_donor_navigation_contains_all_information_architecture_items(): void
    {
        $this->withSession($this->demoDonorSession())
            ->get(route('donor.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Donatur')
            ->assertSee('Donasi Saya / Riwayat')
            ->assertSee('Monitoring Proyek')
            ->assertSee('Laporan Keuangan')
            ->assertSee('Ledger &amp; Preview Kuitansi')
            ->assertSee('Klarifikasi Audit')
            ->assertSee('Profil &amp; Notifikasi');
    }

    public function test_donor_screens_do_not_show_internal_screen_codes_or_plain_info_glyphs(): void
    {
        $urls = [
            route('donor.dashboard'),
            route('donor.donations.index'),
            route('donor.monitoring.index'),
            route('donor.finance'),
            route('donor.ledger.index'),
            route('donor.audit.index'),
            route('donor.profile'),
        ];

        foreach ($urls as $url) {
            $response = $this->withSession($this->demoDonorSession())->get($url)->assertOk();
            foreach (['S07', 'S08', 'S09', 'S10', 'S11', 'S12', 'S19', 'S20'] as $screenCode) {
                $response->assertDontSee($screenCode);
            }
            $response->assertDontSee('aria-hidden="true">i</span>', false);
        }
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $this->withSession($this->demoDonorSession())
            ->post(route('donor.notifications.read', ['id' => 'NTF-001']))
            ->assertRedirect();
    }
}
