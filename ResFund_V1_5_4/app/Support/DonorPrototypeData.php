<?php

namespace App\Support;

/**
 * Data demonstrasi dashboard donatur.
 * Data ini hanya untuk prototype dan bukan transaksi operasional ResFund.
 */
class DonorPrototypeData
{
    public const DEMO_EMAIL = 'donatur@resfund.test';

    public static function donationsFor(array $user, ?\Illuminate\Http\Request $request = null): array
    {
        $isDemo = strtolower((string) ($user['email'] ?? '')) === self::DEMO_EMAIL;

        $donations = $isDemo ? [
            [
                'id' => 'DN-2026-001',
                'project_slug' => 'bioplastik-pati-singkong',
                'project_title' => 'Pengembangan Bioplastik Ramah Lingkungan dari Pati Singkong',
                'researcher' => 'Nadia Prameswari, S.Si., M.Sc.',
                'category' => 'Lingkungan',
                'amount' => 500000,
                'date' => '12 Oktober 2026',
                'date_iso' => '2026-10-12',
                'payment_method' => 'Virtual Account · Payment Gateway',
                'payment_status' => 'SETTLED',
                'payment_status_label' => 'Berhasil',
                'campaign_status' => 'FUNDED',
                'campaign_status_label' => 'Target tercapai',
                'campaign_progress' => 100,
                'deadline' => '1 November 2026',
                'transaction_ref' => 'RF-PG-20261012-001',
                'refund_amount' => 0,
                'refund_reference' => null,
                'verified' => true,
                'has_ledger' => true,
                'milestones' => [
                    ['name' => 'Persiapan bahan', 'description' => 'Penyiapan bahan baku pati singkong dan karakterisasi awal.', 'allocation' => 25, 'progress' => 'Selesai', 'payout' => 'Pencairan tahap berjalan sesuai persetujuan', 'review' => 'Disetujui oleh 2 reviewer'],
                    ['name' => 'Pembuatan dan pengujian sampel', 'description' => 'Pembuatan sampel bioplastik dan pengujian karakteristik material.', 'allocation' => 45, 'progress' => 'Sedang berjalan', 'payout' => 'Dalam pelaksanaan sesuai keputusan pencairan', 'review' => 'Disetujui oleh 2 reviewer'],
                    ['name' => 'Analisis dan pelaporan', 'description' => 'Analisis hasil pengujian dan penyusunan laporan akhir.', 'allocation' => 30, 'progress' => 'Belum dimulai', 'payout' => 'Menunggu pengajuan tahap sebelumnya', 'review' => 'Belum diajukan'],
                ],
            ],
            [
                'id' => 'DN-2026-002',
                'project_slug' => 'sintesis-baterai-limbah-tebu',
                'project_title' => 'Sintesis Baterai Ramah Lingkungan dari Limbah Tebu',
                'researcher' => 'Dr. Andi Prasetyo',
                'category' => 'Energi',
                'amount' => 250000,
                'date' => '4 November 2026',
                'date_iso' => '2026-11-04',
                'payment_method' => 'Virtual Account · Payment Gateway',
                'payment_status' => 'SETTLED',
                'payment_status_label' => 'Berhasil',
                'campaign_status' => 'ACTIVE',
                'campaign_status_label' => 'Sedang menggalang dana',
                'campaign_progress' => 65,
                'deadline' => '15 Desember 2026',
                'transaction_ref' => 'RF-PG-20261104-002',
                'refund_amount' => 0,
                'refund_reference' => null,
                'verified' => true,
                'has_ledger' => false,
                'milestones' => [
                    ['name' => 'Persiapan dan karakterisasi bahan', 'description' => 'Pengumpulan bahan, preparasi sampel, dan pengujian awal.', 'allocation' => 25, 'progress' => 'Belum dimulai', 'payout' => 'Dana masih berada di escrow', 'review' => 'Belum diajukan'],
                    ['name' => 'Sintesis dan pengujian material', 'description' => 'Proses sintesis serta pengujian karakteristik material.', 'allocation' => 45, 'progress' => 'Belum dimulai', 'payout' => 'Menunggu capaian milestone', 'review' => 'Belum dievaluasi'],
                    ['name' => 'Analisis dan publikasi hasil', 'description' => 'Analisis hasil, penyusunan laporan, dan persiapan publikasi.', 'allocation' => 30, 'progress' => 'Belum dimulai', 'payout' => 'Menunggu capaian milestone', 'review' => 'Belum dievaluasi'],
                ],
            ],
            [
                'id' => 'DN-2026-003',
                'project_slug' => 'serat-pisang-lahan-basah',
                'project_title' => 'Pemanfaatan Serat Pisang untuk Produk Ramah Lingkungan',
                'researcher' => 'Rina Kurniawati, S.T., M.T.',
                'category' => 'Lingkungan',
                'amount' => 150000,
                'date' => '1 Oktober 2026',
                'date_iso' => '2026-10-01',
                'payment_method' => 'Virtual Account · Payment Gateway',
                'payment_status' => 'REFUNDED',
                'payment_status_label' => 'Dikembalikan',
                'campaign_status' => 'FAILED',
                'campaign_status_label' => 'Target tidak tercapai',
                'campaign_progress' => 70,
                'deadline' => '31 Oktober 2026',
                'transaction_ref' => 'RF-PG-20261001-003',
                'refund_amount' => 150000,
                'refund_reference' => 'RF-RFD-20261101-003',
                'verified' => false,
                'has_ledger' => false,
                'milestones' => [],
            ],
        ] : [];

        if ($request !== null) {
            $sessionDonations = $request->session()->get('prototype_user_donations', []);
            $email = strtolower((string) ($user['email'] ?? ''));
            $donations = array_merge($donations, $sessionDonations[$email] ?? []);
        }

        usort($donations, static fn (array $a, array $b) => strcmp($b['date_iso'] ?? '', $a['date_iso'] ?? ''));

        return $donations;
    }

    public static function ledgerFor(string $slug): array
    {
        if ($slug !== 'bioplastik-pati-singkong') {
            return [];
        }

        return [
            ['id' => 'LED-001', 'date' => '8 Oktober 2026', 'date_iso' => '2026-10-08', 'recipient' => 'Laboratorium Kimia Material', 'category' => 'Bahan habis pakai', 'description' => 'Pembelian bahan kimia untuk preparasi pati singkong', 'amount' => 4500000, 'receipt' => 'KWT-2026-001', 'milestone' => 'Persiapan bahan', 'hash' => 'a4b7d2e91f0c6a55...'],
            ['id' => 'LED-002', 'date' => '10 Oktober 2026', 'date_iso' => '2026-10-10', 'recipient' => 'CV Polimer Nusantara', 'category' => 'Bahan habis pakai', 'description' => 'Pembelian bahan pendukung formulasi bioplastik', 'amount' => 4000000, 'receipt' => 'KWT-2026-002', 'milestone' => 'Persiapan bahan', 'hash' => 'b5c8e3fa201d7b66...'],
            ['id' => 'LED-008', 'date' => '12 Oktober 2026', 'date_iso' => '2026-10-12', 'recipient' => 'Pusat Bahan Polimer', 'category' => 'Bahan habis pakai', 'description' => 'Pembelian bahan tambahan dan bahan uji formulasi', 'amount' => 3500000, 'receipt' => 'KWT-2026-008', 'milestone' => 'Persiapan bahan', 'hash' => '152e49507a63d2cc...'],
            ['id' => 'LED-003', 'date' => '14 Oktober 2026', 'date_iso' => '2026-10-14', 'recipient' => 'Toko Alat Laboratorium', 'category' => 'Alat penelitian', 'description' => 'Pembelian alat bantu pencetakan sampel', 'amount' => 3200000, 'receipt' => 'KWT-2026-003', 'milestone' => 'Pembuatan dan pengujian sampel', 'hash' => 'c6d9f40b312e8c77...'],
            ['id' => 'LED-004', 'date' => '18 Oktober 2026', 'date_iso' => '2026-10-18', 'recipient' => 'Penyedia Peralatan Uji', 'category' => 'Alat penelitian', 'description' => 'Pengadaan komponen pengujian karakteristik sampel', 'amount' => 2800000, 'receipt' => 'KWT-2026-004', 'milestone' => 'Pembuatan dan pengujian sampel', 'hash' => 'd7ea051c423f9d88...'],
            ['id' => 'LED-005', 'date' => '20 Oktober 2026', 'date_iso' => '2026-10-20', 'recipient' => 'Jasa Transportasi Sampel', 'category' => 'Operasional lapangan', 'description' => 'Pengiriman sampel untuk pengujian', 'amount' => 1800000, 'receipt' => 'KWT-2026-005', 'milestone' => 'Pembuatan dan pengujian sampel', 'hash' => 'e8fb162d5340ae99...'],
            ['id' => 'LED-006', 'date' => '22 Oktober 2026', 'date_iso' => '2026-10-22', 'recipient' => 'Unit Pengujian Material', 'category' => 'Operasional lapangan', 'description' => 'Biaya operasional pengujian sampel', 'amount' => 1200000, 'receipt' => 'KWT-2026-006', 'milestone' => 'Pembuatan dan pengujian sampel', 'hash' => 'f90c273e6451b0aa...'],
            ['id' => 'LED-007', 'date' => '27 Oktober 2026', 'date_iso' => '2026-10-27', 'recipient' => 'Penerbit Jurnal Ilmiah', 'category' => 'Publikasi', 'description' => 'Biaya persiapan dan pengelolaan naskah publikasi', 'amount' => 1150000, 'receipt' => 'KWT-2026-007', 'milestone' => 'Analisis dan pelaporan', 'hash' => '0a1d384f7562c1bb...'],
        ];
    }

    public static function notificationsFor(array $user): array
    {
        if (strtolower((string) ($user['email'] ?? '')) !== self::DEMO_EMAIL) {
            return [];
        }

        return [
            ['id' => 'NTF-001', 'type' => 'milestone', 'title' => 'Pembaruan milestone', 'message' => 'Proyek bioplastik telah memperbarui informasi pelaksanaan milestone kedua.', 'date' => '16 November 2026', 'read' => false, 'url' => '/donatur/monitoring/bioplastik-pati-singkong'],
            ['id' => 'NTF-002', 'type' => 'refund', 'title' => 'Pengembalian dana selesai', 'message' => 'Donasi Rp150.000 pada proyek serat pisang telah dikembalikan ke metode pembayaran asal.', 'date' => '1 November 2026', 'read' => false, 'url' => '/donatur/donasi/DN-2026-003'],
            ['id' => 'NTF-003', 'type' => 'ledger', 'title' => 'Laporan keuangan diperbarui', 'message' => 'Bukti pengeluaran baru telah dipublikasikan pada proyek bioplastik.', 'date' => '27 Oktober 2026', 'read' => true, 'url' => '/donatur/proyek/bioplastik-pati-singkong/ledger'],
            ['id' => 'NTF-004', 'type' => 'audit', 'title' => 'Klarifikasi audit tercatat', 'message' => 'Pertanyaan audit Anda telah diteruskan kepada peneliti terkait.', 'date' => '14 November 2026', 'read' => false, 'url' => '/donatur/klarifikasi-audit'],
        ];
    }

    public static function clarificationsFor(array $user): array
    {
        if (strtolower((string) ($user['email'] ?? '')) !== self::DEMO_EMAIL) {
            return [];
        }

        return [
            [
                'id' => 'AUD-2026-001',
                'project_slug' => 'bioplastik-pati-singkong',
                'project_title' => 'Pengembangan Bioplastik Ramah Lingkungan dari Pati Singkong',
                'ledger_id' => 'LED-001',
                'receipt' => 'KWT-2026-001',
                'subject' => 'Permintaan rincian pembelian bahan',
                'message' => 'Mohon penjelasan rincian bahan yang termasuk dalam transaksi ini.',
                'date' => '14 November 2026',
                'status' => 'MENUNGGU_RESPONS',
                'status_label' => 'Menunggu respons peneliti',
                'response_deadline' => '17 November 2026',
                'researcher_response' => null,
            ],
        ];
    }

    public static function projectTitle(string $slug): ?string
    {
        foreach (PrototypeCatalog::projects() as $project) {
            if ($project['slug'] === $slug) {
                return $project['title'];
            }
        }

        return null;
    }
}
