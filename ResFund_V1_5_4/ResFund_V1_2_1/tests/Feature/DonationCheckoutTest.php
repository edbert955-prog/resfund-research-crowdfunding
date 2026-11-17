<?php

namespace Tests\Feature;

use Tests\TestCase;

class DonationCheckoutTest extends TestCase
{
    private const PROJECT_SLUG = 'sintesis-baterai-limbah-tebu';

    private function donorSession(): array
    {
        return [
            'prototype_user' => [
                'name' => 'Nadia Putri',
                'email' => 'donatur@resfund.test',
                'role' => 'donatur',
                'role_label' => 'Donatur',
                'verification_status' => 'terverifikasi',
            ],
        ];
    }

    public function test_logged_in_donor_is_sent_directly_to_checkout(): void
    {
        $this->withSession($this->donorSession())
            ->get(route('donation.entry', self::PROJECT_SLUG))
            ->assertRedirect(route('donation.checkout', self::PROJECT_SLUG));
    }

    public function test_donation_checkout_requires_a_donor_session(): void
    {
        $this->get(route('donation.checkout', self::PROJECT_SLUG))
            ->assertRedirect(route('login', ['next' => 'donatur']));
    }

    public function test_checkout_payment_and_confirmation_flow_updates_donor_history(): void
    {
        $response = $this->withSession($this->donorSession())->post(route('donation.create-payment', self::PROJECT_SLUG), [
            'amount' => 250000,
            'payment_method' => 'qris',
            'email_notification' => '1',
            'terms' => '1',
        ]);

        $response->assertRedirect();
        $paymentUrl = $response->headers->get('Location');
        $this->assertNotEmpty($paymentUrl);
        $reference = basename($paymentUrl);

        $this->get($paymentUrl)->assertOk()->assertSee('Menunggu pembayaran')->assertSee('Simulasikan pembayaran berhasil');

        $this->post(route('donation.simulate-paid', $reference))
            ->assertRedirect(route('donation.confirmation', $reference))
            ->assertSessionHas('prototype_user_donations', function (array $rows): bool {
                return ($rows['donatur@resfund.test'][0]['payment_status'] ?? null) === 'SETTLED';
            });

        $this->get(route('donor.donations.index'))
            ->assertOk()
            ->assertSee('Sintesis Baterai Ramah Lingkungan dari Limbah Tebu')
            ->assertSee('Rp250.000');

        $this->get(route('donation.confirmation', $reference))
            ->assertOk()
            ->assertSee('Donasi berhasil dicatat')
            ->assertSee('SETTLED');
    }

    public function test_checkout_rejects_donation_above_remaining_campaign_target(): void
    {
        $this->withSession($this->donorSession())
            ->from(route('donation.checkout', self::PROJECT_SLUG))
            ->post(route('donation.create-payment', self::PROJECT_SLUG), [
                'amount' => 18000000,
                'payment_method' => 'qris',
                'terms' => '1',
            ])
            ->assertRedirect(route('donation.checkout', self::PROJECT_SLUG))
            ->assertSessionHasErrors('amount');
    }

    public function test_public_finance_overview_lists_multiple_campaigns(): void
    {
        $this->get(route('finance.public'))
            ->assertOk()
            ->assertSee('Ringkasan keuangan publik')
            ->assertSee('Sintesis Baterai Ramah Lingkungan dari Limbah Tebu')
            ->assertSee('Pengembangan Bioplastik Ramah Lingkungan dari Pati Singkong')
            ->assertSee('Pemanfaatan Serat Pisang untuk Produk Ramah Lingkungan')
            ->assertSee('Sistem Pemantauan Kualitas Air Berbasis Sensor IoT');
    }

    public function test_active_campaign_finance_detail_does_not_invent_expenses(): void
    {
        $this->get(route('finance.public', self::PROJECT_SLUG))
            ->assertOk()
            ->assertSee('Dana masih berada di escrow')
            ->assertDontSee('Lihat kuitansi');
    }
}
