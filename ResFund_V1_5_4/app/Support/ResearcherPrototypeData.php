<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Dataset session-only untuk simulasi dashboard Peneliti V1.3.
 * Seluruh angka, status, dan dokumen contoh bukan data operasional.
 */
class ResearcherPrototypeData
{
    public const DEMO_EMAIL = 'peneliti@resfund.test';

    private static function key(string $type, array $user): string
    {
        return 'prototype_researcher_'.$type.'_'.sha1(strtolower((string) ($user['email'] ?? 'guest')));
    }

    public static function projects(Request $request, array $user): array
    {
        $key = self::key('projects', $user);
        if (! $request->session()->has($key)) {
            $request->session()->put($key, self::isDemo($user) ? self::demoProjects() : []);
        }

        return $request->session()->get($key, []);
    }

    public static function saveProjects(Request $request, array $user, array $projects): void
    {
        $request->session()->put(self::key('projects', $user), array_values($projects));
    }

    public static function expenses(Request $request, array $user): array
    {
        $key = self::key('expenses', $user);
        if (! $request->session()->has($key)) {
            $request->session()->put($key, self::isDemo($user) ? self::demoExpenses() : []);
        }

        return $request->session()->get($key, []);
    }

    public static function saveExpenses(Request $request, array $user, array $expenses): void
    {
        $request->session()->put(self::key('expenses', $user), array_values($expenses));
    }

    public static function isDemo(array $user): bool
    {
        return strtolower((string) ($user['email'] ?? '')) === self::DEMO_EMAIL;
    }

    public static function demoProjects(): array
    {
        return [
            [
                'slug' => 'panel-surya-pesisir-lampung',
                'title' => 'Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir',
                'abstract' => 'Penelitian ini mengembangkan rancangan pembangkit surya skala mikro untuk mendukung kebutuhan energi di wilayah pesisir. Kegiatan meliputi survei lokasi, perancangan prototipe, pengujian, dan evaluasi kinerja sistem.',
                'category' => 'Energi Terbarukan',
                'target' => 80000000,
                'collected' => 80000000,
                'campaign_duration' => 60,
                'campaign_status' => 'FUNDED',
                'campaign_status_label' => 'Target pendanaan tercapai',
                'proposal_status' => 'APPROVED',
                'proposal_status_label' => 'Disetujui untuk kampanye',
                'deadline' => '30 Oktober 2026',
                'created_at' => '12 Agustus 2026',
                'sptjm_signed' => true,
                'milestones' => [
                    [
                        'number' => 1, 'name' => 'Survei dan pengumpulan data',
                        'description' => 'Survei lokasi, pengumpulan kebutuhan energi, dan pengukuran awal.',
                        'allocation' => 25, 'amount' => 20000000, 'status' => 'DISBURSED',
                        'status_label' => 'Selesai · Dana telah dicairkan', 'review_status' => 'APPROVED',
                        'reviewers' => [
                            ['label' => 'Reviewer independen 1', 'status' => 'APPROVED', 'status_label' => 'Disetujui'],
                            ['label' => 'Reviewer independen 2', 'status' => 'APPROVED', 'status_label' => 'Disetujui'],
                        ],
                        'sptjm_signed' => true, 'financial_accountability_submitted' => true, 'disbursed_amount' => 20000000,
                        'payout_reference' => 'PO-DEMO-2026-001', 'submitted_at' => '5 September 2026',
                    ],
                    [
                        'number' => 2, 'name' => 'Pembuatan dan integrasi prototipe',
                        'description' => 'Perakitan panel surya, pengendali, penyimpanan energi, dan integrasi perangkat.',
                        'allocation' => 45, 'amount' => 36000000, 'status' => 'SUBMITTED',
                        'status_label' => 'Menunggu evaluasi dua reviewer', 'review_status' => 'UNDER_REVIEW',
                        'reviewers' => [
                            ['id' => 'reviewer_a', 'label' => 'Dr. Rina Kurniawati', 'status' => 'PENDING', 'status_label' => 'Menunggu penilaian'],
                            ['id' => 'reviewer_b', 'label' => 'Prof. Budi Santoso', 'status' => 'PENDING', 'status_label' => 'Menunggu penilaian'],
                        ],
                        'sptjm_signed' => true, 'financial_accountability_submitted' => true,
                        'requested_amount' => 36000000,
                        'deliverable_name' => 'Laporan luaran teknis milestone 1.pdf',
                        'accountability_name' => 'Rekonsiliasi keuangan milestone 1.pdf',
                        'payout_reference' => null, 'submitted_at' => '17 November 2026 09:30',
                        'review_due_at' => '2026-11-24',
                    ],
                    [
                        'number' => 3, 'name' => 'Pengujian dan evaluasi kinerja',
                        'description' => 'Pengujian sistem pada kondisi lapangan dan penyusunan laporan akhir.',
                        'allocation' => 30, 'amount' => 24000000, 'status' => 'LOCKED',
                        'status_label' => 'Terkunci · Menunggu milestone sebelumnya', 'review_status' => 'NOT_SUBMITTED',
                        'reviewers' => [
                            ['label' => 'Reviewer independen 1', 'status' => 'NOT_ASSIGNED', 'status_label' => 'Belum ditugaskan'],
                            ['label' => 'Reviewer independen 2', 'status' => 'NOT_ASSIGNED', 'status_label' => 'Belum ditugaskan'],
                        ],
                        'sptjm_signed' => true, 'financial_accountability_submitted' => false,
                        'payout_reference' => null, 'submitted_at' => null,
                    ],
                ],
            ],
            [
                'slug' => 'sistem-pemantauan-kualitas-air-pesisir',
                'title' => 'Sistem Pemantauan Kualitas Air Pesisir Berbasis Sensor',
                'abstract' => 'Proyek ini merancang perangkat pemantauan kualitas air untuk membantu pengumpulan data lingkungan secara berkala di wilayah pesisir.',
                'category' => 'Lingkungan',
                'target' => 35000000,
                'collected' => 35000000,
                'campaign_duration' => 45,
                'campaign_status' => 'FUNDED',
                'campaign_status_label' => 'Target pendanaan tercapai',
                'proposal_status' => 'APPROVED',
                'proposal_status_label' => 'Disetujui untuk kampanye',
                'deadline' => '10 November 2026',
                'created_at' => '1 September 2026',
                'sptjm_signed' => true,
                'milestones' => [
                    ['number' => 1, 'name' => 'Perancangan perangkat dan pengadaan sensor', 'description' => 'Perancangan rangkaian, pemilihan sensor, serta pengadaan komponen awal.', 'allocation' => 30, 'amount' => 10500000, 'status' => 'SUBMITTED', 'status_label' => 'Menunggu evaluasi dua reviewer', 'review_status' => 'UNDER_REVIEW', 'reviewers' => [['id' => 'reviewer_a', 'label' => 'Dr. Rina Kurniawati', 'status' => 'PENDING', 'status_label' => 'Menunggu penilaian'], ['id' => 'reviewer_b', 'label' => 'Prof. Budi Santoso', 'status' => 'PENDING', 'status_label' => 'Menunggu penilaian']], 'sptjm_signed' => true, 'requested_amount' => 10500000, 'sptjm_name' => 'SPTJM digital bertanda tangan.pdf', 'workplan_name' => 'Rencana kerja milestone pertama.pdf', 'financial_accountability_submitted' => false, 'payout_reference' => null, 'submitted_at' => '17 November 2026 10:15', 'review_due_at' => '2026-11-24'],
                    ['number' => 2, 'name' => 'Integrasi prototipe dan pengujian', 'description' => 'Integrasi sensor, uji lapangan, dan evaluasi data.', 'allocation' => 45, 'amount' => 15750000, 'status' => 'LOCKED', 'status_label' => 'Menunggu pencairan milestone pertama', 'review_status' => 'NOT_SUBMITTED', 'reviewers' => [], 'sptjm_signed' => false, 'financial_accountability_submitted' => false, 'payout_reference' => null, 'submitted_at' => null],
                    ['number' => 3, 'name' => 'Analisis dan pelaporan', 'description' => 'Analisis data pengujian dan penyusunan laporan akhir.', 'allocation' => 25, 'amount' => 8750000, 'status' => 'LOCKED', 'status_label' => 'Menunggu milestone sebelumnya', 'review_status' => 'NOT_SUBMITTED', 'reviewers' => [], 'sptjm_signed' => false, 'financial_accountability_submitted' => false, 'payout_reference' => null, 'submitted_at' => null],
                ],
            ],
            [
                'slug' => 'biokomposit-serat-alam-lampung',
                'title' => 'Pengembangan Biokomposit Berbasis Serat Alam Lampung',
                'abstract' => 'Proposal ini mengkaji pemanfaatan serat alam lokal sebagai bahan biokomposit untuk aplikasi produk ramah lingkungan.',
                'category' => 'Lingkungan',
                'target' => 45000000,
                'collected' => 0,
                'campaign_duration' => 45,
                'campaign_status' => 'DRAFT',
                'campaign_status_label' => 'Belum dipublikasikan',
                'proposal_status' => 'DRAFT',
                'proposal_status_label' => 'Draft',
                'deadline' => null,
                'created_at' => '15 November 2026',
                'sptjm_signed' => false,
                'milestones' => [
                    ['number' => 1, 'name' => 'Karakterisasi serat alam', 'description' => 'Persiapan sampel dan uji karakteristik awal.', 'allocation' => 40, 'amount' => 18000000, 'status' => 'LOCKED', 'status_label' => 'Menunggu kampanye', 'review_status' => 'NOT_SUBMITTED', 'reviewers' => [], 'sptjm_signed' => false, 'financial_accountability_submitted' => false, 'payout_reference' => null, 'submitted_at' => null],
                    ['number' => 2, 'name' => 'Formulasi dan pengujian biokomposit', 'description' => 'Formulasi bahan dan pengujian sifat mekanik.', 'allocation' => 60, 'amount' => 27000000, 'status' => 'LOCKED', 'status_label' => 'Menunggu kampanye', 'review_status' => 'NOT_SUBMITTED', 'reviewers' => [], 'sptjm_signed' => false, 'financial_accountability_submitted' => false, 'payout_reference' => null, 'submitted_at' => null],
                ],
            ],
        ];
    }

    public static function demoExpenses(): array
    {
        return [
            ['id' => 'EXP-DEMO-001', 'project_slug' => 'panel-surya-pesisir-lampung', 'project_title' => 'Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir', 'milestone_number' => 1, 'date' => '2026-09-08', 'recipient' => 'Toko Komponen Energi Surya', 'category' => 'ALAT', 'category_label' => 'Alat penelitian', 'description' => 'Pembelian alat ukur dan komponen pengujian awal sistem panel surya.', 'amount' => 4500000, 'receipt_number' => 'KWT-DEMO-001', 'receipt_name' => 'kuitansi-komponen-surya-demo.jpg', 'receipt_path' => null, 'hash' => 'Ilustrasi · hash tidak dihitung untuk dokumen contoh', 'status' => 'FINAL_LEDGER', 'status_label' => 'Final Ledger · Terkunci', 'created_at' => '8 September 2026'],
            ['id' => 'EXP-DEMO-002', 'project_slug' => 'panel-surya-pesisir-lampung', 'project_title' => 'Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir', 'milestone_number' => 1, 'date' => '2026-09-12', 'recipient' => 'Penyedia Survei Wilayah Pesisir', 'category' => 'OPERASIONAL_LAPANGAN', 'category_label' => 'Operasional lapangan', 'description' => 'Biaya perjalanan dan pengukuran kebutuhan energi pada lokasi survei.', 'amount' => 3200000, 'receipt_number' => 'KWT-DEMO-002', 'receipt_name' => 'bukti-survei-demo.pdf', 'receipt_path' => null, 'hash' => 'Ilustrasi · hash tidak dihitung untuk dokumen contoh', 'status' => 'FINAL_LEDGER', 'status_label' => 'Final Ledger · Terkunci', 'created_at' => '12 September 2026'],
            ['id' => 'EXP-DEMO-003', 'project_slug' => 'panel-surya-pesisir-lampung', 'project_title' => 'Pengembangan Pembangkit Surya Skala Mikro di Daerah Pesisir', 'milestone_number' => 1, 'date' => '2026-09-18', 'recipient' => 'Laboratorium Energi Terbarukan', 'category' => 'BAHAN_HABIS_PAKAI', 'category_label' => 'Bahan habis pakai', 'description' => 'Pembelian kabel, konektor, dan bahan pendukung pengujian awal.', 'amount' => 1750000, 'receipt_number' => 'KWT-DEMO-003', 'receipt_name' => 'nota-bahan-pengujian-demo.png', 'receipt_path' => null, 'hash' => 'Ilustrasi · hash tidak dihitung untuk dokumen contoh', 'status' => 'FINAL_LEDGER', 'status_label' => 'Final Ledger · Terkunci', 'created_at' => '18 September 2026'],
        ];
    }
}
