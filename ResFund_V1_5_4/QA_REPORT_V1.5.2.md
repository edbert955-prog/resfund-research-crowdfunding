# QA Report — ResFund Hi-Fi Interactive Prototype V1.5.2

## 1. Ringkasan

V1.5.2 adalah patch dari V1.5.1. Cakupan utama: memperbaiki tinggi tombol Masuk/Daftar, memusatkan kartu Login/Daftar pada sisi kanan, mencegah overflow angka Rupiah pada KPI Monitoring Administrator, dan memperkeras validasi penugasan serta keputusan Reviewer.

V1.5.2 mempertahankan satu proyek Laravel yang sama. File `composer.json` identik byte-per-byte dengan V1.5.1; tidak ada dependensi Composer baru.

## 2. Perbaikan visual

### Login/Daftar dan navbar publik

- Semua selector tautan navigasi generik mengecualikan `.nav-auth-button`, sehingga tombol autentikasi tidak lagi mewarisi padding vertikal 27 px.
- Tinggi aktual tombol Masuk dan Daftar di browser preview: 46 px.
- Tombol yang sesuai halaman aktif memakai biru; tombol yang tidak aktif memakai latar putih.
- Kartu autentikasi disejajarkan di tengah area kontennya pada sisi kanan.
- Layout diuji pada 15 lebar viewport: 1600, 1440, 1366, 1280, 1180, 1060, 1024, 960, 800, 760, 540, 430, 390, 360, dan 320 px.
- Tidak ditemukan horizontal overflow pada halaman Beranda, Login, dan Daftar dalam matriks tersebut.
- Pada 390 px, menu mobile dapat dibuka dengan tombol hamburger dan ditutup dengan tombol Escape; atribut `aria-expanded` ikut berubah.
- Aset logo PNG termuat pada setiap halaman preview.

### KPI Monitoring Administrator

- Kartu nominal menggunakan flex child dengan `min-width:0`, ukuran angka responsif, dan konten keterangan yang dapat membungkus.
- Browser preview menguji nominal `Rp105.250.000` serta keterangan refund pada 10 lebar viewport: 1600, 1440, 1366, 1280, 1180, 1024, 760, 540, 390, dan 320 px.
- Nilai `scrollWidth` nominal sama dengan `clientWidth` pada seluruh lebar yang diuji; tidak terjadi overflow dari kartu.
- Screenshot preview desktop tersedia di `qa/visual-preview/admin-monitoring-kpi-desktop.png`.

> Uji browser visual menggunakan Chromium dengan HTML preview terisolasi yang memuat CSS, SVG, logo, dan foto lokal dari proyek. Uji tersebut memverifikasi aturan visual dan interaksi menu pada preview; bukan uji HTTP end-to-end terhadap server Laravel yang sedang berjalan.

## 3. Audit logika bisnis yang diperbaiki

### Panel Reviewer

- Panel yang sah harus memuat tepat dua ID Reviewer yang berbeda.
- Kedua Reviewer harus tersedia dan aktif saat panel penilaian belum final.
- Institusi kedua Reviewer harus berbeda satu sama lain.
- Institusi Reviewer tidak boleh sama dengan institusi Peneliti.
- Duplikasi ID, panel satu orang, identitas Reviewer yang tidak ditemukan, institusi kosong/duplikat, atau konflik institusi menghasilkan status `unassigned` dan tidak dapat membuat payout-ready.
- Satu persetujuan menghasilkan status menunggu Reviewer kedua; payout hanya siap setelah kedua Reviewer menyetujui. ID Reviewer duplikat tidak dihitung sebagai dua penilai.
- Revisi Minor atau Ditolak menahan payout.
- Konflik kepentingan memblokir panel dan tidak dapat dilaporkan berulang pada panel yang sama.
- Reviewer yang dinonaktifkan oleh Administrator ditolak saat mengakses area Reviewer meskipun sesi login sebelumnya masih aktif.
- Panel yang belum final dan kehilangan Reviewer aktif menjadi tidak valid dan membutuhkan penugasan ulang.
- Keputusan panel yang sudah final tetap immutable bila akun Reviewer kemudian dinonaktifkan; Administrator tidak dapat mengganti panel final untuk menimpa/menghilangkan keputusan. Sesi Reviewer yang dinonaktifkan ditolak oleh guard controller pada semua halaman Reviewer.
- Penugasan ulang menyimpan panel dan keputusan sebelumnya pada `panel_history`; Riwayat Keputusan tetap dapat menampilkan keputusan Reviewer pada panel sebelumnya.

### Proposal, milestone, dan pencairan

- Nominal milestone dihitung dalam Rupiah; selisih pembulatan dialokasikan ke milestone terakhir agar total nominal tepat sama dengan target.
- Setiap milestone harus bernilai minimal Rp1.000.
- Pengajuan pencairan milestone memeriksa kapasitas dua Reviewer aktif sebelum menerima dan menyimpan dokumen pengajuan.
- Ketentuan dasar SKPL tetap dipertahankan: 2–5 milestone dengan total persentase 100%, durasi kampanye 15–90 hari, pencairan awal maksimal 30% setelah SPTJM, pengajuan termin berikutnya membutuhkan luaran teknis dan pertanggungjawaban keuangan sebelumnya, serta pencairan hanya setelah seluruh Reviewer menyetujui.
- Pengeluaran hanya boleh dicatat terhadap milestone yang dananya telah dicairkan; nominal tidak boleh melebihi sisa dana milestone.
- Entri Final Ledger bersifat read-only dari antarmuka.

## 4. Hasil pemeriksaan statis

| Pemeriksaan | Hasil |
|---|---:|
| File PHP yang diperiksa dengan `php -l` | 95 file, tanpa syntax error |
| File JavaScript yang diperiksa dengan `node --check` | 6 file, tanpa syntax error |
| File CSS yang diparse | 5 file |
| Qualified CSS rules | 1.887 |
| CSS parse errors | 0 |
| Nama route terdaftar yang dibandingkan dengan referensi Blade/controller | 69 deklarasi / 69 referensi, tidak ada yang hilang |
| Kode layar konseptual S01–S20 yang muncul pada Blade UI | Tidak ditemukan |
| Composer configuration | Identik byte-per-byte dengan V1.5.1 |

## 5. Uji logika langsung dan feature tests

- `qa/business_logic_smoke.php` dijalankan langsung dengan PHP terhadap fungsi `ReviewerPrototypeData::aggregate()`; 9 skenario lulus: dua approval, menunggu Reviewer kedua, panel satu Reviewer, ID duplikat, institusi Reviewer duplikat, konflik institusi Peneliti, Revisi Minor, Ditolak, dan konflik kepentingan.
- Direktori `tests/Feature` berisi 121 metode test untuk area Publik, Autentikasi, Donatur, Peneliti, Reviewer, dan Administrator. Penambahan V1.5.2 mencakup akun Reviewer nonaktif, panel yang kehilangan Reviewer aktif, panel duplikat/tidak lengkap, keputusan final, riwayat panel yang diarsipkan, serta nominal milestone minimal.

### Batasan yang belum dapat diuji di lingkungan penyusunan

Composer tidak tersedia pada lingkungan penyusunan dan folder `vendor` tidak ada. Oleh karena itu `php artisan test` / PHPUnit dan HTTP end-to-end Laravel tidak dijalankan di sini. 121 metode feature test disertakan agar dapat dieksekusi pada komputer lokal setelah dependensi proyek tersedia. Jangan menganggap hasil smoke test visual atau agregasi murni sebagai pengganti pengujian integrasi penuh.

## 6. Cara upgrade

1. Backup folder proyek V1.5.1.
2. Ekstrak ZIP V1.5.2.
3. Salin isi folder `ResFund_V1_5_2` ke folder proyek Laravel yang sama dan pilih Replace Files.
4. Pertahankan `.env`, `vendor`, dan `storage`.
5. Jalankan:

```bash
php artisan optimize:clear
php artisan serve
```

Tidak perlu `composer install` ulang karena `composer.json` V1.5.2 identik dengan V1.5.1.

## 7. Batasan prototipe

Akun, donasi, pembayaran, penugasan, keputusan, audit log, dan aktivitas lain masih menggunakan data demonstrasi/session prototype. Payment Gateway live, SSO/PDDikti, database operasional, email/WhatsApp notification, dan layanan penyimpanan dokumen produksi belum terintegrasi. Prototipe ini adalah artefak UI/UX interaktif untuk demonstrasi, bukan sistem transaksi siap produksi.
