# ResFund V1.5.4 — QA Report

## Ringkasan perubahan

V1.5.4 menambahkan motion dan interaksi ringan berbasis Vanilla JavaScript/CSS untuk area publik serta seluruh dashboard. Tidak ada dependensi baru, perubahan rute, perubahan role, atau perubahan business logic.

### Implementasi

| Area | Perilaku |
|---|---|
| Page entrance | Fade + pergeseran vertikal ringan saat halaman dimuat |
| Scroll reveal | Bagian utama dan kartu muncul ketika masuk viewport |
| Stagger | Kartu dalam grid muncul berurutan dengan jeda singkat |
| KPI count-up | Angka statistik bertambah halus sampai nilai akhir yang dirender server |
| Progress | Progress pendanaan/realisasi bergerak ke persentase aktual |
| Header | Bayangan header publik muncul saat scroll |
| Button feedback | Efek tekan ringan pada tombol dan tautan aksi |
| Hero parallax | Tilt kecil pada foto hero hanya untuk pointer presisi |
| Form focus | Label field mendapat penekanan saat input aktif |
| Flash/modal/details | Animasi masuk singkat |
| Reduced motion | Gerakan dinonaktifkan untuk pengguna yang meminta pengurangan gerakan |

Semua efek bersifat presentasional. Tidak ada handler motion yang mengubah status donasi, role, akses, keputusan reviewer, nominal, validasi formulir, atau data transaksi.

## Hasil pengujian

### Syntax dan aset

| Pemeriksaan | Hasil |
|---|---|
| PHP/Blade `php -l` | 93 file, tanpa syntax error |
| JavaScript `node --check` | 7 file, seluruhnya lolos |
| CSS parsing (`tinycss2`) | 6 file CSS, 0 parse error |
| `composer.json` | Identik dengan V1.5.3 |
| Dependensi | Tidak ada dependensi baru |
| Layout | 5 layout memuat `motion.css` dan `motion.js` satu kali |

### Browser smoke test — Chromium

`qa/run_motion_smoke.py` menjalankan preview dengan CSS/JS proyek dan memeriksa:

- Scroll reveal dan pembersihan transform setelah animasi.
- Count-up angka KPI sampai kembali pada string nilai final.
- Progress bar menuju target server-rendered.
- Shadow header setelah scroll.
- Umpan balik tekan tombol.
- Tidak ada horizontal overflow pada 1366×900 dan 390×844.
- Menu mobile terbuka dan tertutup dengan tombol Escape.
- Password toggle dan pergantian role Donatur/Peneliti tetap berjalan.
- Mode `prefers-reduced-motion` menonaktifkan animasi dan mempertahankan visibilitas konten.
- Tidak ada JavaScript console/page error.

**Hasil:** semua assertion lulus, `console_errors` kosong. Rincian mesin ada pada `qa/motion-preview/results.json` dan output teks `qa/motion-smoke-output-v1.5.4.txt`.

### Regresi visual area publik dan autentikasi

`qa/run_visual_smoke.py` memeriksa Beranda, Login, dan Daftar pada desktop 1600 px serta mobile 390 px. Hasil pemeriksaan mempertahankan:

- Logo termuat.
- Header desktop tidak overflow.
- Header mobile tetap berfungsi.
- Tombol Masuk/Daftar memiliki tinggi aktual 46 px.
- Hanya halaman autentikasi yang aktif menampilkan tombol biru.
- Kartu autentikasi tetap di tengah kolomnya.
- Tidak ada horizontal overflow.

Hasil tersimpan pada `qa/visual-preview/visual-smoke-results.json`.

### Regresi logika bisnis

`php qa/business_logic_smoke.php` dijalankan dan menghasilkan:

`PASS: 9 reviewer aggregation scenarios`

Motion tidak mengubah file controller/support business logic.

## Batasan pengujian

- Browser test menggunakan Chromium pada preview terisolasi dengan CSS dan JavaScript aktual, bukan sesi Laravel live yang terhubung ke `vendor`.
- PHPUnit Feature Test lengkap belum dijalankan pada lingkungan penyusunan karena folder `vendor` tidak tersedia. Sebanyak 121 Feature Test yang sudah ada tetap disertakan.
- Pengujian Payment Gateway live, SSO/PDDikti, email, serta database produksi tidak termasuk ruang lingkup patch motion.

## Upgrade

`composer.json` V1.5.4 identik dengan V1.5.3. Untuk memperbarui proyek lama, salin isi `ResFund_V1_5_4` ke folder proyek yang sama; pertahankan `.env`, `vendor`, dan `storage`. Jalankan `php artisan optimize:clear`, kemudian `php artisan serve`. Tidak perlu `composer install` ulang.
