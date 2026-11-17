<?php

namespace App\Support;

/**
 * Data statis untuk simulasi antarmuka V1.3.
 * Data ini bukan data transaksi atau kampanye operasional ResFund.
 */
class PrototypeCatalog
{
    public static function projects(array $additionalCollected = []): array
    {
        $projects = [
            [
                'slug' => 'sintesis-baterai-limbah-tebu',
                'title' => 'Sintesis Baterai Ramah Lingkungan dari Limbah Tebu',
                'researcher' => 'Dr. Andi Prasetyo',
                'institution' => 'Universitas Lampung',
                'category' => 'Energi',
                'category_label' => 'Energi Terbarukan',
                'status' => 'ACTIVE',
                'status_label' => 'Sedang Menggalang Dana',
                'target' => 50000000,
                'collected' => 32500000,
                'deadline' => '15 Desember 2026',
                'days_left' => 28,
                'image' => 'assets/images/project-battery-photo.jpg',
                'short' => 'Mengembangkan material penyimpanan energi dengan memanfaatkan limbah pertanian yang mudah ditemukan di Indonesia.',
                'description' => 'Penelitian ini mengeksplorasi pemanfaatan limbah tebu sebagai bahan alternatif untuk pengembangan material baterai. Hasil penelitian diharapkan menjadi salah satu langkah awal menuju teknologi penyimpanan energi yang lebih berkelanjutan.',
                'objectives' => [
                    'Mengolah limbah tebu menjadi material penelitian yang bernilai tambah.',
                    'Menguji karakteristik material untuk aplikasi penyimpanan energi.',
                    'Menyusun laporan hasil penelitian dan rekomendasi pengembangan.',
                ],
                'milestones' => [
                    ['name' => 'Persiapan dan karakterisasi bahan', 'description' => 'Pengumpulan bahan, preparasi sampel, dan pengujian awal.', 'allocation' => 25, 'status' => 'Belum dimulai'],
                    ['name' => 'Sintesis dan pengujian material', 'description' => 'Proses sintesis serta pengujian karakteristik material.', 'allocation' => 45, 'status' => 'Belum dimulai'],
                    ['name' => 'Analisis dan publikasi hasil', 'description' => 'Analisis hasil, penyusunan laporan, dan persiapan publikasi.', 'allocation' => 30, 'status' => 'Belum dimulai'],
                ],
            ],
            [
                'slug' => 'deteksi-kualitas-air-iot',
                'title' => 'Sistem Pemantauan Kualitas Air Berbasis Sensor IoT',
                'researcher' => 'Rina Kurniawati, S.T., M.T.',
                'institution' => 'Universitas Lampung',
                'category' => 'Lingkungan',
                'category_label' => 'Lingkungan',
                'status' => 'ACTIVE',
                'status_label' => 'Sedang Menggalang Dana',
                'target' => 35000000,
                'collected' => 22750000,
                'deadline' => '20 Desember 2026',
                'days_left' => 33,
                'image' => 'assets/images/project-water-photo.jpg',
                'short' => 'Membangun prototipe pemantauan kualitas air secara berkala menggunakan sensor dan konektivitas Internet of Things.',
                'description' => 'Proyek ini mengembangkan perangkat pemantauan kualitas air yang membaca beberapa parameter lingkungan dan menyajikan hasilnya melalui antarmuka digital. Penelitian berfokus pada prototipe, pengujian sensor, dan evaluasi hasil pengukuran.',
                'objectives' => [
                    'Merancang rangkaian sensor untuk pemantauan kualitas air.',
                    'Mengintegrasikan pembacaan sensor dengan platform IoT.',
                    'Melakukan pengujian dan analisis hasil pengukuran.',
                ],
                'milestones' => [
                    ['name' => 'Perancangan perangkat', 'description' => 'Pemilihan sensor, komponen, dan rancangan perangkat.', 'allocation' => 30, 'status' => 'Belum dimulai'],
                    ['name' => 'Pembuatan prototipe', 'description' => 'Integrasi perangkat, konektivitas, dan dashboard monitoring.', 'allocation' => 45, 'status' => 'Belum dimulai'],
                    ['name' => 'Pengujian dan evaluasi', 'description' => 'Pengujian lapangan dan penyusunan laporan.', 'allocation' => 25, 'status' => 'Belum dimulai'],
                ],
            ],
            [
                'slug' => 'serat-pisang-lahan-basah',
                'title' => 'Pemanfaatan Serat Pisang untuk Produk Ramah Lingkungan',
                'researcher' => 'Rina Kurniawati, S.T., M.T.',
                'institution' => 'Universitas Lampung',
                'category' => 'Lingkungan',
                'category_label' => 'Lingkungan',
                'status' => 'FAILED',
                'status_label' => 'Target pendanaan tidak tercapai',
                'target' => 30000000,
                'collected' => 21000000,
                'deadline' => '31 Oktober 2026',
                'days_left' => 0,
                'image' => 'https://images.unsplash.com/photo-1631556764629-b51c3ae40d18?auto=format&fit=crop&w=1200&q=85',
                'short' => 'Penelitian pemanfaatan serat pisang sebagai bahan alternatif untuk produk ramah lingkungan.',
                'description' => 'Proyek contoh ini berakhir sebelum target pendanaan tercapai. Sesuai mekanisme All-or-Nothing ResFund, donasi yang telah terverifikasi dikembalikan ke metode pembayaran asal.',
                'objectives' => [
                    'Mengidentifikasi karakteristik serat pisang.',
                    'Menguji potensi pemanfaatan serat sebagai bahan alternatif.',
                    'Menyusun laporan hasil penelitian.',
                ],
                'milestones' => [
                    ['name' => 'Karakterisasi bahan', 'description' => 'Pengumpulan dan pengujian awal bahan.', 'allocation' => 30, 'status' => 'Tidak dilanjutkan'],
                    ['name' => 'Pembuatan sampel', 'description' => 'Pengembangan sampel awal.', 'allocation' => 40, 'status' => 'Tidak dilanjutkan'],
                    ['name' => 'Evaluasi dan pelaporan', 'description' => 'Evaluasi dan penyusunan laporan.', 'allocation' => 30, 'status' => 'Tidak dilanjutkan'],
                ],
            ],
            [
                'slug' => 'bioplastik-pati-singkong',
                'title' => 'Pengembangan Bioplastik Ramah Lingkungan dari Pati Singkong',
                'researcher' => 'Nadia Prameswari, S.Si., M.Sc.',
                'institution' => 'Universitas Lampung',
                'category' => 'Lingkungan',
                'category_label' => 'Lingkungan',
                'status' => 'FUNDED',
                'status_label' => 'Target Pendanaan Tercapai',
                'target' => 50000000,
                'collected' => 50000000,
                'deadline' => '1 November 2026',
                'days_left' => 0,
                'image' => 'assets/images/project-bioplastic-photo.jpg',
                'short' => 'Mengembangkan alternatif bahan kemasan berbasis pati singkong untuk mengurangi ketergantungan pada plastik konvensional.',
                'description' => 'Penelitian ini mengembangkan material bioplastik berbasis pati singkong dan menguji karakteristik awalnya. Kampanye pada contoh ini telah mencapai target pendanaan. Ringkasan penggunaan dana dapat dilihat secara publik.',
                'objectives' => [
                    'Mempersiapkan dan mengolah bahan baku pati singkong.',
                    'Membuat serta menguji sampel bioplastik.',
                    'Menganalisis hasil dan menyusun laporan penelitian.',
                ],
                'milestones' => [
                    ['name' => 'Persiapan bahan', 'description' => 'Penyiapan bahan dan karakterisasi awal.', 'allocation' => 25, 'status' => 'Selesai'],
                    ['name' => 'Pembuatan dan pengujian sampel', 'description' => 'Pembuatan sampel dan pengujian karakteristik.', 'allocation' => 45, 'status' => 'Sedang berjalan'],
                    ['name' => 'Analisis dan pelaporan', 'description' => 'Analisis hasil dan penyusunan laporan akhir.', 'allocation' => 30, 'status' => 'Belum dimulai'],
                ],
            ],
        ];

        foreach ($projects as &$project) {
            $extra = max(0, (int) ($additionalCollected[$project['slug']] ?? 0));
            if ($extra > 0) {
                $project['collected'] = min($project['target'], $project['collected'] + $extra);
                if ($project['status'] === 'ACTIVE' && $project['collected'] >= $project['target']) {
                    $project['status'] = 'FUNDED';
                    $project['status_label'] = 'Target Pendanaan Tercapai';
                    $project['days_left'] = 0;
                }
            }
            $project['progress'] = $project['target'] > 0
                ? min(100, (int) round(($project['collected'] / $project['target']) * 100))
                : 0;
        }
        unset($project);

        return $projects;
    }

    /**
     * Ringkasan finansial publik untuk seluruh kampanye contoh.
     * Detail transaksi dan kuitansi tidak disertakan pada data publik.
     */
    public static function publicReports(array $additionalCollected = []): array
    {
        $projects = self::projects($additionalCollected);
        $ledger = [
            ['name' => 'Bahan habis pakai', 'planned' => 30000000, 'actual' => 12000000, 'color' => '#2F6FED'],
            ['name' => 'Alat penelitian', 'planned' => 12000000, 'actual' => 6000000, 'color' => '#0B786B'],
            ['name' => 'Operasional lapangan', 'planned' => 5000000, 'actual' => 3000000, 'color' => '#9C650B'],
            ['name' => 'Publikasi', 'planned' => 3000000, 'actual' => 1150000, 'color' => '#6651A9'],
        ];

        $reports = [];
        foreach ($projects as $project) {
            $actualSpent = $project['slug'] === 'bioplastik-pati-singkong' ? 22150000 : 0;
            $categories = $project['slug'] === 'bioplastik-pati-singkong' ? $ledger : [];
            $remainingBudget = max(0, $project['target'] - $actualSpent);
            $escrowBalance = match ($project['status']) {
                'ACTIVE' => $project['collected'],
                'FUNDED' => $remainingBudget,
                default => 0,
            };
            $refundAmount = $project['status'] === 'FAILED' ? $project['collected'] : 0;

            if ($project['slug'] === 'bioplastik-pati-singkong') {
                $escrowBalance = $remainingBudget;
            }

            $reports[] = [
                'project_slug' => $project['slug'],
                'project_title' => $project['title'],
                'researcher' => $project['researcher'],
                'institution' => $project['institution'],
                'category' => $project['category_label'],
                'status' => $project['status'],
                'status_label' => $project['status_label'],
                'image' => $project['image'],
                'target_budget' => $project['target'],
                'funded_amount' => $project['collected'],
                'progress' => $project['progress'],
                'actual_spent' => $actualSpent,
                'remaining_budget' => $remainingBudget,
                'escrow_balance' => $escrowBalance,
                'refund_amount' => $refundAmount,
                'categories' => $categories,
                'can_view_expenses' => $actualSpent > 0,
                'deadline' => $project['deadline'],
                'updated_at' => '16 November 2026',
            ];
        }

        $actualTotal = array_sum(array_column($reports, 'actual_spent'));
        $escrowTotal = array_sum(array_column($reports, 'escrow_balance'));
        $refundTotal = array_sum(array_column($reports, 'refund_amount'));

        return [
            'reports' => $reports,
            'summary' => [
                'project_count' => count($reports),
                'active_count' => count(array_filter($reports, fn (array $r) => $r['status'] === 'ACTIVE')),
                'funded_count' => count(array_filter($reports, fn (array $r) => $r['status'] === 'FUNDED')),
                'failed_count' => count(array_filter($reports, fn (array $r) => $r['status'] === 'FAILED')),
                'actual_total' => $actualTotal,
                'escrow_total' => $escrowTotal,
                'refund_total' => $refundTotal,
                'collected_total' => array_sum(array_column($reports, 'funded_amount')),
            ],
        ];
    }

    public static function publicReport(string $slug, array $additionalCollected = []): ?array
    {
        foreach (self::publicReports($additionalCollected)['reports'] as $report) {
            if ($report['project_slug'] === $slug) {
                return $report;
            }
        }

        return null;
    }

    public static function categories(): array
    {
        return ['Energi', 'Lingkungan'];
    }
}

/*
 * Legacy single-report schema is intentionally replaced by publicReports().
 * This closing marker keeps the catalog as a single source of demo data.
 */

