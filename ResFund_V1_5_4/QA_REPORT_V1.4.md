# QA Report — ResFund V1.4

## Ringkasan perubahan

- Menambahkan Area Reviewer sesuai Information Architecture BAB V: Dashboard/Daftar Penugasan serta Detail Bukti/Form Evaluasi.
- Menambahkan dua akun demo Reviewer yang berbeda untuk mensimulasikan dual approval.
- Membatasi akses berdasarkan role dan daftar assignment.
- Menambahkan rubric scoring 1–5, catatan umum, keputusan, syarat alasan revisi/penolakan, dan deklarasi bebas konflik.
- Menambahkan pelaporan konflik kepentingan dan status penugasan ulang yang membutuhkan Administrator.
- Keputusan Reviewer disinkronkan ke status milestone pada area Peneliti dalam sesi prototype yang sama.
- Menghapus shortcut ikon notifikasi di topbar Donatur; menu Profil & Notifikasi tetap tersedia di sidebar.
- Mengganti ikon info berbentuk karakter `i` lepas menjadi SVG pada flash message Donatur dan area publik.
- Menutup celah business logic: ketika satu Reviewer melaporkan konflik kepentingan, Reviewer lainnya tidak dapat mengirim keputusan lewat endpoint langsung; form evaluasi dan form konflik juga dikunci sampai penugasan ulang.
- Memperbesar beberapa label/keterangan sekunder dan menata ulang rubrik skor agar lebih mudah dibaca pada layar kecil.
- Menambahkan focus trap, Escape, dan pengembalian fokus pada modal pratinjau dokumen.
- Menyelaraskan status dashboard/list: jika salah satu reviewer melaporkan konflik, assignment tidak lagi dihitung sebagai tugas yang menunggu keputusan reviewer lainnya.

## Pemeriksaan statis yang benar-benar dijalankan

| Pemeriksaan | Hasil |
|---|---:|
| PHP syntax lint (`app`, `routes`, `tests`, `bootstrap`) | 25 file, lulus |
| JavaScript syntax (`app.js`, `donation-checkout.js`, `donor-dashboard.js`, `researcher-dashboard.js`, `reviewer-dashboard.js`) | 5 file, lulus |
| CSS parser (4 file CSS) | 1.618 top-level rules; 0 parse error |
| Composer manifest | JSON valid |
| Composer dependencies vs V1.3.1 | Identik; tidak ada perubahan `require`/`require-dev` |
| Route reference pada Blade Reviewer | Semua nama route Reviewer yang dirujuk ada pada deklarasi route |
| Kode layar internal pada UI Reviewer | Tidak ditemukan S17–S20 |
| Karakter `i` yang berdiri sendiri pada flash info | Tidak ditemukan pada layout Donatur/Peneliti/area publik |
| Shortcut ikon notifikasi pada topbar Donatur | Tidak ditemukan; menu Profil & Notifikasi tetap ada |
| Feature test disediakan | 87 metode test |

## Pemeriksaan logika agregasi Reviewer

Fungsi agregasi keputusan diuji secara terisolasi dengan PHP tanpa memanggil Laravel:

1. Belum ada keputusan → `pending`.
2. Satu persetujuan → `waiting_second`, belum siap payout.
3. Dua persetujuan → `approved`, `payout_ready = true`.
4. Revisi Minor → `revision`, payout ditahan.
5. Ditolak → `rejected`, payout ditahan.
6. Konflik kepentingan → `conflict`, payout ditahan.

Semua enam skenario logika agregasi tersebut lulus pada pemeriksaan terisolasi. Pemeriksaan source juga memastikan `saveReview` menolak penilaian baru saat aggregate sudah berada pada status konflik.

## Aturan bisnis yang diterapkan

- Reviewer hanya dapat membuka assignment yang ditugaskan kepadanya.
- Dua Reviewer mengirim keputusan secara terpisah.
- Satu keputusan Disetujui tidak cukup untuk meneruskan payout.
- Payout hanya berstatus siap diteruskan jika seluruh Reviewer yang ditugaskan memilih Disetujui.
- Revisi Minor atau Ditolak menahan payout.
- Keputusan yang telah dikirim dikunci dan tidak dapat dikirim ulang oleh Reviewer yang sama.
- Jika konflik kepentingan dilaporkan, panel diblokir dan menunggu penugasan ulang Administrator.
- Reviewer tidak memiliki menu atau endpoint untuk mentransfer dana atau mengeksekusi payout.
- Tenggat evaluasi tujuh hari kalender diperiksa sebelum keputusan dikirim.

## Test suite

Folder `tests/Feature` menyediakan 87 feature test, termasuk tes login dua akun Reviewer, role gate, daftar penugasan, detail bukti, validasi rubrik, dual approval, penahanan payout, konflik kepentingan, dan UI regression.

Jalankan pada komputer yang sudah memiliki dependensi Laravel:

```bash
php artisan test
```

## Batasan yang belum dapat diuji di lingkungan pembuatan ZIP

- Composer dan folder `vendor` tidak tersedia, sehingga PHPUnit/Laravel feature test tidak dapat dieksekusi di lingkungan ini.
- Percobaan browser Chromium headless untuk membuka fixture lokal berhenti karena runtime browser sandbox tidak merespons; screenshot visual/E2E tidak dapat diklaim lulus.
- Dokumen pada modal Reviewer merupakan ringkasan ilustratif, bukan berkas PDF asli dari cloud storage.
- Penugasan, keputusan, dan akun demo tersimpan di session browser. Dua akun Reviewer dicoba dengan logout/login secara bergantian pada browser/sesi yang sama; data keputusan belum tersimpan pada database bersama lintas perangkat.
- Dashboard Administrator untuk melakukan penugasan ulang setelah konflik belum termasuk versi ini.
- Payment Gateway/payout tidak menjalankan transaksi uang sungguhan.

## Upgrade

V1.4 menggunakan `composer.json` yang identik dengan V1.3.1. Jika `vendor` V1.3.1 sudah terpasang, cukup salin file V1.4 ke proyek yang sama, pertahankan `.env`, `vendor`, dan `storage`, lalu jalankan:

```bash
php artisan optimize:clear
php artisan serve
```
