# ResFund Hi-Fi Interactive Prototype — V1.5.4

Prototype front-end ResFund berbasis Laravel 13. V1.5.4 menambahkan sistem JavaScript motion dan micro-interaction lintas area tanpa mengubah business flow, konfigurasi dependensi, atau framework. Seluruh area Publik, Donatur, Peneliti, Reviewer, dan Administrator tetap dipertahankan.

> **Mode prototipe:** data akun, pengguna, validasi, daftar reviewer, penugasan, keputusan, serta audit log disimpan pada session browser. Belum menggunakan database operasional atau integrasi SSO/PDDikti dan Payment Gateway live.



## Pembaruan V1.5.4 — JavaScript Motion & Interaction

V1.5.4 menambahkan `public/assets/js/motion.js` dan `public/assets/css/motion.css`. Sistem motion menggunakan Vanilla JavaScript dan CSS lokal, tanpa CDN, framework animasi, atau dependensi Composer/NPM tambahan.

Efek yang diterapkan secara terukur:

- **Page entrance:** transisi masuk halaman berupa fade dan pergeseran vertikal yang sangat ringan. Kelas transform dibersihkan setelah animasi agar elemen `position: fixed` tetap berfungsi normal.
- **Scroll reveal:** hero, panel, kartu proyek, KPI, dan bagian penting muncul saat masuk viewport. Kartu berulang menggunakan stagger pendek. Kelas reveal dibersihkan setelah transisi agar tidak meninggalkan transform/containing block.
- **KPI count-up:** angka ringkasan Donatur, Peneliti, Reviewer, Administrator, dan laporan keuangan dihitung naik secara singkat lalu berhenti tepat pada nilai server-rendered.
- **Progress animation:** progress pendanaan dan realisasi bergerak dari 0 menuju persentase yang sudah dirender server. Tidak mengubah nilai atau status di sisi server.
- **Header depth:** bayangan header publik muncul secara halus setelah halaman digulir.
- **Button feedback:** tombol dan tautan aksi memiliki umpan balik tekan yang ringan, bukan ripple berlebihan.
- **Hero parallax:** visual hero publik mendapat tilt sangat kecil pada perangkat dengan pointer presisi; tidak aktif pada perangkat sentuh atau mode reduced motion.
- **Form focus cue:** label formulir mendapat penekanan warna ketika field aktif, tanpa mengubah validasi.
- **Flash/modal/details entrance:** pesan status, modal, dan detail disclosure memperoleh transisi masuk singkat.
- **Accessibility:** `prefers-reduced-motion: reduce` menonaktifkan gerakan; seluruh konten tetap terlihat dan fungsi kontrol tetap berjalan.

Sistem motion tidak mengubah rute, hak akses, status pembayaran, keputusan reviewer, nominal, validasi, maupun penyimpanan data. Skrip yang sudah ada (`app.js`, dashboard Donatur/Peneliti/Reviewer/Admin, dan checkout) tetap dipertahankan.

### Pengujian V1.5.4

- `node --check` untuk seluruh 7 file JavaScript: lulus.
- Parsing 6 file CSS menggunakan `tinycss2`: 0 parse error.
- `php -l` pada 93 file PHP/Blade: tidak ditemukan syntax error.
- `qa/run_motion_smoke.py` dengan Chromium: scroll reveal, count-up, progress animation, header depth, button feedback, mobile menu/Escape, password toggle, pemilihan role, reduced-motion, dan horizontal overflow: lulus; tidak ada console error.
- `qa/run_visual_smoke.py`: Beranda, Login, dan Daftar pada desktop 1600 px serta mobile 390 px; logo termuat, navbar tidak overflow, tombol autentikasi tetap 46 px, tombol aktif sesuai halaman, dan panel autentikasi tetap di tengah.
- `qa/business_logic_smoke.php`: 9/9 skenario agregasi reviewer lulus.
- `composer.json` identik dengan V1.5.3; tidak ada dependensi baru.

Lihat `QA_REPORT_V1.5.4.md` dan folder `qa/motion-preview/` untuk ringkasan hasil serta screenshot uji motion. PHPUnit penuh belum dijalankan di lingkungan penyusunan karena direktori `vendor` tidak tersedia; 121 Feature Test tetap disertakan dalam proyek.

### File motion

- `public/assets/js/motion.js` — inisialisasi reveal, counter, progress, header, pointer parallax, dan micro-interaction.
- `public/assets/css/motion.css` — timing, easing, hover/press, entrance, dan reduced-motion styles.
- `qa/run_motion_smoke.py` — browser smoke test untuk motion dan interaksi bersama.

## Pembaruan V1.5.2 — UI dan konsistensi proses bisnis

- Tombol Masuk/Daftar dikecualikan dari padding generik tautan navbar. Tinggi aktual kedua tombol menjadi 46 px pada desktop, dengan gaya biru hanya untuk halaman autentikasi yang sedang aktif.
- Panel Login dan Daftar dipusatkan di kolom kanan; lebar kartu dibatasi agar tidak menempel ke tepi panel.
- KPI “Dana terhimpun bersih” pada Monitoring & Audit Log menggunakan kontainer flex dengan batas lebar dan ukuran angka responsif agar nominal Rupiah tidak keluar dari kartu.
- Reviewer yang dinonaktifkan oleh Administrator langsung ditolak saat membuka area Reviewer, termasuk pada sesi login yang masih aktif.
- Panel reviewer harus berisi tepat dua ID reviewer yang berbeda, dua akun aktif, dua institusi reviewer yang berbeda, dan tidak satu institusi dengan peneliti. Panel tidak lengkap/duplikat/konflik tidak dapat menghasilkan payout-ready.
- Keputusan payout-ready hanya tercapai setelah kedua reviewer menyetujui. Satu persetujuan hanya menghasilkan status menunggu reviewer kedua.
- Penugasan panel yang sudah final tetap menjadi rekam keputusan immutable walaupun akun reviewer kemudian dinonaktifkan. Administrator tidak dapat mengganti panel final untuk menghapus keputusan; panel yang belum final dan kehilangan reviewer aktif harus ditugaskan ulang.
- Laporan konflik kepentingan tidak dapat dikirim berulang setelah panel diblokir. Reviewer yang melaporkan konflik tidak dapat ditugaskan kembali pada pengajuan yang sama.
- Keputusan reviewer lama tetap tersedia pada Riwayat Keputusan setelah penugasan ulang, dengan label panel sebelumnya.
- Pembagian nominal milestone menggunakan pembulatan Rupiah dengan selisih ditempatkan pada milestone terakhir; setiap milestone wajib minimal Rp1.000 dan total nominal harus sama dengan target.
- Pengajuan milestone memeriksa ketersediaan dua reviewer aktif sebelum menerima/menyimpan dokumen pengajuan, agar tidak meninggalkan unggahan yatim ketika panel belum tersedia.

## Pengujian tambahan V1.5.2

- `qa/run_visual_smoke.py` menjalankan browser Chromium pada 15 lebar viewport (320–1600 px) untuk Beranda, Login, dan Daftar, serta 10 lebar viewport untuk KPI Monitoring Admin. Pemeriksaan mencakup horizontal overflow, tinggi tombol autentikasi, status tombol aktif, pemusatan kartu, pemuatan aset, dan overflow nominal Rupiah.
- `qa/business_logic_smoke.php` menjalankan sembilan skenario langsung terhadap fungsi agregasi keputusan reviewer: dua persetujuan, menunggu persetujuan kedua, panel satu reviewer, ID duplikat, institusi reviewer duplikat, konflik institusi peneliti, revisi minor, penolakan, dan konflik kepentingan.
- PHPUnit feature tests berjumlah 121 metode. PHPUnit belum dijalankan pada lingkungan penyusunan karena Composer dan direktori `vendor` tidak tersedia; test suite disertakan untuk dijalankan di komputer lokal.

## Pembaruan visual V1.5.1

- Logo ResFund utama menggunakan PNG transparan `public/assets/images/resfund-logo.png`; versi putih untuk sidebar/footer menggunakan `resfund-logo-white.png`; simbol aplikasi terpisah tersedia pada `resfund-mark.png` dan `favicon.png`.
- Header publik dibuat satu baris pada desktop, nama akun tidak memaksa tautan navigasi turun, dan pada lebar 1060 px ke bawah navigasi beralih ke menu mobile.
- Tombol Masuk dan Daftar mempunyai ukuran serta bentuk konsisten. Hanya halaman yang sedang aktif yang memakai latar biru.
- Halaman Login dan Daftar memakai logo, heading, isi panel kiri, dan posisi layout yang sama. Daftar akun demo kini mencantumkan Administrator.
- Garis dekoratif pendek sebelum eyebrow beranda dihapus. Panel kiri Login/Daftar pada mobile tetap memiliki kontras yang cukup sampai seluruh poin selesai.
- Ikon antarmuka menggunakan satu set SVG lokal `public/assets/images/resfund-icons.svg` melalui Blade component `<x-icon>`; bukan karakter Unicode/emoji sebagai pengganti ikon.
- Statistik Monitoring & Audit Log menghitung dana terhimpun bersih dari kampanye ACTIVE/FUNDED/COMPLETED dan mengecualikan kampanye FAILED yang mengikuti mekanisme refund.

### Aset logo PNG

- `public/assets/images/resfund-logo.png` — lockup logo utama untuk area publik.
- `public/assets/images/resfund-logo-white.png` — lockup putih untuk sidebar dan footer gelap.
- `public/assets/images/resfund-mark.png` — simbol ResFund saja untuk autentikasi dan identitas kecil.
- `public/assets/images/favicon.png` — favicon PNG.

## Area Administrator

Area Administrator hanya dapat dibuka oleh akun dengan role `administrator`. Menu mengikuti struktur IA yang diberikan:

| Menu IA | Halaman | URL |
|---|---|---|
| Dashboard Admin | Ringkasan pengguna, validasi, reviewer, dan penugasan | `/administrator/dashboard` |
| Manajemen Pengguna | Daftar akun, pencarian, filter role/status, detail, aktifkan/tangguhkan | `/administrator/pengguna` |
| Validasi Peneliti | Antrean calon peneliti, detail identitas, keputusan validasi dan catatan | `/administrator/validasi-peneliti` |
| Manajemen Reviewer | Daftar reviewer, bidang keahlian, institusi, tambah akun, aktif/nonaktif | `/administrator/reviewer` |
| Penugasan Reviewer | Daftar pengajuan, detail panel, penugasan/penugasan ulang dua reviewer | `/administrator/penugasan-reviewer` |
| Monitoring & Audit Log | Ringkasan kampanye dan tabel aktivitas sistem/admin yang hanya-baca | `/administrator/monitoring-audit-log` |

### Manajemen Pengguna

- Daftar pengguna demo dan akun yang dibuat pada sesi prototype.
- Pencarian nama, email, dan institusi; filter role serta status akun.
- Detail akun memperlihatkan identitas, role, institusi, nomor akademik, status validasi, dan tanggal pendaftaran.
- Administrator dapat mengaktifkan atau menangguhkan akun. Role akun tidak dapat diubah melalui menu ini.
- Peneliti yang belum divalidasi tidak dapat diaktifkan dari Manajemen Pengguna; prosesnya harus melalui Validasi Peneliti.
- Akun Administrator yang sedang digunakan dilindungi agar tidak dapat ditangguhkan dari sesinya sendiri.

### Validasi Peneliti

- Antrean peneliti berdasarkan status menunggu verifikasi, terverifikasi, atau ditolak.
- Detail menampilkan nama, email, jenis peneliti, NIDN/NIM, dan institusi.
- Keputusan Setujui atau Tolak wajib disertai catatan pemeriksaan.
- Jika disetujui, status validasi menjadi terverifikasi dan akun aktif. Jika ditolak, status validasi menjadi ditolak dan akses akun ditangguhkan.
- Pemeriksaan SSO/PDDikti belum terhubung; data validasi yang tampil adalah data demonstrasi.

### Manajemen Reviewer

- Daftar reviewer menampilkan nama, email, institusi, dan keahlian.
- Administrator dapat menambahkan akun Reviewer dengan email unik, institusi, bidang keahlian, nomor telepon, dan kata sandi awal.
- Reviewer baru dapat login setelah dibuat. Akun disimpan dalam sesi prototype.
- Reviewer dapat dinonaktifkan atau diaktifkan kembali. Reviewer nonaktif ditolak saat login dan semua request pada area Reviewer diblokir, termasuk dari sesi yang sebelumnya masih aktif.
- Pendaftaran publik tetap tidak menyediakan role Reviewer atau Administrator.

### Penugasan Reviewer

- Daftar menunjukkan proyek, peneliti, milestone, panel, status agregat, dan tenggat.
- Detail pengajuan memperlihatkan panel reviewer saat ini dan status keputusan masing-masing.
- Administrator memilih tepat dua reviewer aktif untuk satu panel.
- Kedua reviewer harus berasal dari institusi berbeda dan tidak boleh berasal dari institusi peneliti.
- Reviewer yang melaporkan konflik kepentingan tidak dapat dipilih kembali untuk pengajuan yang sama.
- Penugasan ulang menyimpan snapshot panel dan keputusan sebelumnya pada riwayat panel. Panel baru memulai penilaian baru; keputusan panel lama tidak digabungkan dengan hasil panel baru.
- Administrator mengelola panel, tetapi tidak mengisi rubrik, tidak mengubah keputusan reviewer, dan tidak mengeksekusi payout.

### Monitoring & Audit Log

- Halaman terpisah, bukan bagian tersembunyi dari Dashboard Admin.
- Menampilkan ringkasan kampanye demo dan log aktivitas terbaru.
- Log memuat waktu, aktor, email aktor, tindakan, entitas, detail, tingkat, dan IP.
- Tersedia pencarian serta filter tingkat dan tindakan.
- Tidak ada tombol edit atau hapus pada catatan audit melalui antarmuka.
- Audit log pada prototipe disimpan di session dan bukan penyimpanan append-only yang aman untuk produksi.

## Akun demo bawaan

Akun demo bawaan menggunakan password `ResFund123!`.

| Peran | Email | Keterangan |
|---|---|---|
| Donatur | `donatur@resfund.test` | Nadia Putri |
| Peneliti | `peneliti@resfund.test` | Dr. Andi Prasetyo |
| Reviewer A | `reviewer@resfund.test` | Dr. Rina Kurniawati, Universitas Sriwijaya |
| Reviewer B | `reviewer2@resfund.test` | Prof. Budi Santoso, Institut Teknologi Sumatera |
| Administrator | `admin@resfund.test` | Administrator ResFund |

Reviewer tambahan yang dibuat melalui Manajemen Reviewer memiliki email dan kata sandi yang ditetapkan Administrator. Akun tersebut tersimpan dalam sesi browser yang sama.

## Aturan proses Reviewer yang dipertahankan

1. Satu pengajuan milestone dinilai oleh dua reviewer independen.
2. Setiap reviewer memberikan keputusan masing-masing dan tidak dapat mengubah keputusan reviewer lain.
3. Satu persetujuan belum cukup untuk membuat payout siap.
4. Payout hanya dapat diteruskan jika kedua reviewer menyetujui.
5. Revisi Minor atau Ditolak menahan pencairan.
6. Laporan konflik kepentingan memblokir panel sampai Administrator melakukan penugasan ulang.
7. Reviewer tidak melakukan transfer dana dan Administrator tidak mengubah hasil evaluasi.

## Pengujian

Folder `tests/Feature` mencakup 121 metode feature test pada area Publik, Autentikasi, Donatur, Peneliti, Reviewer, dan Administrator. Jalankan dengan `php artisan test` setelah dependensi lokal tersedia. Pengujian visual Chromium untuk header publik, Login/Daftar, dan kartu KPI Admin tersedia pada `qa/run_visual_smoke.py`; hasil serta screenshot preview desktop/mobile berada di `qa/visual-preview/`. Pemeriksaan logika agregasi reviewer tanpa Laravel tersedia pada `qa/business_logic_smoke.php`. Test Administrator mencakup:

- Login akun Administrator demo dan redirect ke Dashboard Admin.
- Pembatasan akses route Administrator untuk role selain Administrator.
- Render enam halaman IA Administrator.
- Persetujuan validasi peneliti dan pencatatan audit.
- Pencegahan aktivasi peneliti yang belum divalidasi melalui Manajemen Pengguna.
- Pembuatan akun Reviewer tambahan dan login menggunakan akun tersebut.
- Penugasan dua reviewer dan penolakan pasangan duplikat.
- Penangguhan akun yang kemudian ditolak saat login.
- Pendaftaran publik tidak menerima role Administrator.
- Tampilan UI tidak memunculkan kode layar konseptual.

**Batas verifikasi:** pemeriksaan sintaks PHP/JavaScript, referensi route/view, parsing CSS, browser smoke test visual, dan sembilan skenario agregasi reviewer dilakukan pada paket. PHPUnit dan HTTP end-to-end Laravel belum dapat dijalankan di lingkungan penyusunan karena Composer tidak tersedia dan folder `vendor` tidak disertakan. Rincian verifikasi serta batasannya tersedia pada `QA_REPORT_V1.5.2.md`. Data sesi prototype bukan pengganti pengujian integrasi produksi.

## Menjalankan proyek

Persyaratan: PHP 8.3+, Composer, ekstensi PHP yang dibutuhkan Laravel 13.

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Untuk upgrade dari V1.5.3, `composer.json` V1.5.4 identik byte-per-byte dengan V1.5.3, sehingga tidak memerlukan `composer install` ulang. Backup folder sebelumnya, salin isi `ResFund_V1_5_4` ke folder proyek yang sama, pertahankan `.env`, `vendor`, dan `storage`, kemudian jalankan:

```bash
php artisan optimize:clear
php artisan serve
```

Akses Dashboard Admin di `http://127.0.0.1:8000/administrator/dashboard` setelah login dengan akun Administrator demo.

---

## Patch V1.5.3 — Reviewer search icon alignment

Perbaikan posisi ikon pencarian pada halaman **Penugasan Saya** dan **Riwayat Keputusan**. Ikon SVG sekarang diposisikan di dalam input dan sejajar vertikal, bukan tampil pada baris terpisah di atas kolom pencarian.

Lihat `QA_REPORT_V1.5.3.md` untuk detail perbaikan ikon Reviewer. Composer dependencies tidak berubah dari V1.5.2.
