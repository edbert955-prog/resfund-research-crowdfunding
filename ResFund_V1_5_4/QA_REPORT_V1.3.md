# QA Report — ResFund Hi-Fi Interactive Prototype V1.3

## Ruang lingkup pemeriksaan

V1.3 melanjutkan V1.2.1. Perubahan yang diperiksa meliputi penghapusan strip catatan prototype di area publik, perbaikan alignment tombol **Lihat detail** pada kartu proyek, penambahan halaman Peneliti S13–S16, dan penambahan foto lokal proyek bioplastik.

## Hasil pemeriksaan statis

| Area | Pemeriksaan | Hasil |
|---|---|---|
| PHP | `php -l` pada semua file PHP aplikasi, Blade, route, config, middleware, dan test | PASS — 69 file |
| JavaScript | `node --check` pada seluruh file JavaScript lokal | PASS — 4 file |
| CSS | Parse `app.css`, `donor-dashboard.css`, `researcher-dashboard.css` dengan tinycss2 | PASS — 0 parser/declaration error |
| Composer | Parse `composer.json` | PASS — JSON valid |
| Dependensi Composer | Bandingkan `require` dan `require-dev` dengan V1.2.1 | IDENTIK — tidak perlu install ulang saat upgrade |
| Route references | Pencocokan named route yang dipanggil Blade/controller dengan definisi route | PASS — tidak ada route yang tidak dikenal |
| Aset lokal | Periksa seluruh `asset('assets/...')` dan gambar lokal yang dirujuk katalog | PASS — tidak ada aset lokal hilang |
| CSRF | Periksa form POST pada Blade | PASS — tidak ditemukan form POST tanpa `@csrf` |
| Strip catatan publik | Cari markup `scope-note-section`/`Catatan prototype` di semua Blade | PASS — tidak ditemukan |
| Link kartu proyek | CSS khusus `project-card-detail-link` menetapkan `inline-flex`, `max-content`, `white-space: nowrap`, dan kolom aksi terpisah | PASS — aturan tidak membungkus teks link |
| JavaScript ganda | `researcher-dashboard.js` hanya dimuat dari layout Peneliti | PASS |

## Pemeriksaan business flow dan validasi

- Guest yang membuka Dashboard Peneliti diarahkan ke Login.
- Sesi role Donatur tidak dapat membuka area Peneliti.
- Login akun demo Peneliti diarahkan ke Dashboard Peneliti.
- Proposal membutuhkan 2–5 milestone dan total persentase alokasi tepat 100%.
- Durasi kampanye divalidasi pada rentang 15–90 hari.
- Alokasi nominal Rupiah dikoreksi terhadap pembulatan agar jumlahnya sama dengan target.
- Pengajuan proposal memerlukan status identitas akademik terverifikasi.
- Pencairan awal mensyaratkan SPTJM dan dibatasi maksimal 30% dana terkumpul serta alokasi milestone pertama.
- Pengajuan termin selanjutnya mensyaratkan milestone sebelumnya telah dicairkan dan dua dokumen PDF (luaran teknis serta pertanggungjawaban keuangan).
- Status pengajuan pencairan menunggu minimal dua reviewer independen; prototype tidak memproses payout sungguhan.
- Pengeluaran hanya dapat dicatat dari milestone yang sudah berstatus `DISBURSED` dan nominalnya tidak boleh melampaui sisa dana milestone.
- Final Ledger tidak dapat dipublikasikan ulang dan antarmuka tidak menyediakan edit/hapus entri final.
- Upload bukti mengizinkan PDF/JPG/JPEG/PNG sampai 8 MB; hash SHA-256 dihitung pada file asli yang diunggah.

## Test suite yang disertakan

Terdapat 60 test method yang tersebar sebagai berikut:

| File | Jumlah test |
|---|---:|
| `PublicAndAuthenticationTest.php` | 19 |
| `DonationCheckoutTest.php` | 6 |
| `DonorDashboardTest.php` | 20 |
| `ResearcherDashboardTest.php` | 15 |
| **Total** | **60** |

**Batas eksekusi:** PHPUnit belum dijalankan di lingkungan pembuatan ZIP karena Composer dan folder `vendor` tidak tersedia. `php artisan test` harus dijalankan di Laragon/komputer pengguna setelah dependensi Laravel terpasang.

## Pemeriksaan visual dan accessibility

- Kartu proyek ditata dengan grid `minmax(0, 1fr) max-content`; deadline boleh membungkus, sedangkan tombol detail dipertahankan satu baris.
- Area Peneliti memakai ukuran isi dan label yang lebih besar (umumnya 14–16 px; body 16–17 px; judul halaman 27–34 px).
- CSS menyediakan focus indicator `:focus-visible`, skip link, dukungan `prefers-reduced-motion`, label form, pesan validasi, dan layout responsif.
- Status reviewer, milestone, kampanye, dan proposal disajikan dengan teks serta indikator visual, bukan warna saja.
- Aset foto baterai, kualitas air, dan bioplastik tersedia lokal. Foto proyek lain yang memakai URL eksternal memiliki fallback gambar lokal.

**Batas visual testing:** pemeriksaan browser end-to-end pada aplikasi Laravel tidak dapat diselesaikan di lingkungan pembuatan karena folder `vendor`/server Laravel tidak tersedia dan Chromium tidak dapat mengakses localhost/file dalam sandbox (`ERR_BLOCKED_BY_ADMINISTRATOR`). Karena itu, laporan ini tidak menyatakan browser E2E atau full visual regression telah lulus. Gunakan checklist manual pada README untuk uji desktop 1440 px, tablet 768 px, dan mobile 390 px setelah dijalankan di Laragon.

## Kesimpulan

Source V1.3 lolos pemeriksaan sintaks PHP/JavaScript, parsing CSS, validitas konfigurasi Composer, referensi route, aset, CSRF, serta pemeriksaan statis business rule dan IA. Test suite 60 kasus telah disertakan, tetapi harus dieksekusi di lingkungan Laravel lokal. Integrasi SSO/PDDikti, Administrator/Reviewer, database operasional, Payment Gateway, escrow dan payout sungguhan tetap di luar cakupan prototype ini.
