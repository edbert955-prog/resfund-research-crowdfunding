# QA Report — ResFund Interactive Prototype V1.2

## 1. Ruang lingkup yang diperiksa

V1.2 menambahkan area Dashboard Donatur sesuai sitemap yang diberikan dan IA BAB V laporan ResFund:

- S07 Dashboard Donatur
- S08 Donasi Saya / Riwayat dan Detail Donasi
- S09 Monitoring Proyek dan Milestone
- S10 Laporan Keuangan / Ringkasan Keuangan Publik
- S11 Ledger dan Preview Kuitansi
- S12 Klarifikasi Audit
- Profil & Notifikasi

Area publik, autentikasi, dan halaman informasi V1.1 tetap dipertahankan.

## 2. Pemeriksaan otomatis yang berhasil dijalankan

| Pemeriksaan | Hasil | Catatan |
|---|---|---|
| PHP syntax lint | PASS | 26 file PHP diperiksa dengan `php -l`; tidak ditemukan syntax error. |
| JavaScript syntax | PASS | `app.js` dan `donor-dashboard.js` lolos `node --check`. |
| Composer manifest | PASS | `composer.json` berhasil dibaca sebagai JSON valid. |
| Route name references | PASS | 27 nama route yang dirujuk Blade ditemukan pada deklarasi route, termasuk route `donor.finance`. |
| Local asset references | PASS | 5 referensi aset lokal dari Blade ditemukan pada folder `public`. |
| CSS parsing | PASS | 2 berkas CSS berhasil diparse dengan `tinycss2`, tidak ditemukan parse error. |
| Demo data integrity | PASS | Semua slug donasi merujuk proyek yang tersedia; data ledger dan laporan publik konsisten. |
| Milestone allocation | PASS | Alokasi milestone untuk 4 proyek contoh berjumlah tepat 100% per proyek. |
| Feature test inventory | READY | 39 method test tersedia dalam dua berkas Feature Test; belum dieksekusi di lingkungan ini. |

### Konsistensi data demonstrasi

- 3 transaksi contoh: 2 berstatus SETTLED dan 1 berstatus REFUNDED.
- Total donasi SETTLED: Rp750.000.
- Total refund: Rp150.000.
- Proyek bioplastik memiliki 8 entri ledger contoh.
- Total 8 entri ledger: Rp22.150.000, sama dengan nilai actual_spent di laporan publik.
- Total per kategori ledger cocok dengan ringkasan publik: bahan habis pakai Rp12.000.000, alat penelitian Rp6.000.000, operasional lapangan Rp3.000.000, publikasi Rp1.150.000.
- Urutan transaksi pada dashboard dan riwayat diurutkan dari tanggal terbaru.

## 3. Pemeriksaan accessibility dan responsive secara statis

Materi Pertemuan 12 menekankan visual consistency, functional interaction, navigation clarity, business flow alignment, responsive layout, dan accessibility. V1.2 menerapkan pemeriksaan berikut:

- Ukuran body dan data tabel dashboard ditingkatkan; label formulir 15 px dan input 16 px.
- Teks sekunder dashboard menggunakan warna `#52647a` pada permukaan putih dengan rasio kontras sekitar 6.06:1, dan sekitar 5.65:1 pada latar `#f5f7fb`.
- Warna utama `#245fce` pada putih memiliki rasio sekitar 5.83:1.
- Status SETTLED, REFUNDED, WAITING_PAYMENT, ACTIVE, FAILED, dan COMPLETED memakai teks/status badge; informasi tidak hanya dibedakan dengan warna.
- Skip link, label input, pesan validasi, `aria-label`, `aria-current`, `aria-live`, dan focus-visible dipertahankan/dipakai pada halaman terkait.
- Sidebar pada layar kecil menjadi drawer; JavaScript mendukung Escape, fokus awal ke menu, dan perputaran fokus Tab di dalam drawer.
- Preview kuitansi menggunakan elemen `<dialog>`; tombol tutup, Escape bawaan browser, dan pengembalian fokus ke tombol pemicu didukung.
- Layout CSS memiliki breakpoint untuk tablet dan smartphone serta mendukung `prefers-reduced-motion`.

Catatan: angka kontras di atas merupakan pengukuran untuk kombinasi warna utama yang disebutkan, bukan sertifikasi audit WCAG penuh untuk seluruh halaman.

## 4. Feature test yang disertakan, tetapi belum dijalankan

Dua berkas test tersedia:

- `tests/Feature/PublicAndAuthenticationTest.php`
- `tests/Feature/DonorDashboardTest.php`

Cakupan test meliputi:

- Akses guest dan pembatasan role Donatur.
- Tampilan dashboard dan seluruh menu IA.
- Urutan riwayat donasi dan pembedaan status pembayaran/kampanye.
- Detail donasi dan informasi refund.
- Monitoring proyek dan milestone.
- S10 ringkasan keuangan melalui area publik dan dashboard Donatur.
- Penolakan akses ledger bagi Donatur tanpa transaksi SETTLED/terverifikasi.
- Pagination/filter ledger serta render tombol preview kuitansi.
- Form klarifikasi audit, validasi ID ledger, dan larangan mengklarifikasi proyek tanpa ledger.
- Update profil dan status notifikasi.
- Alur login, registrasi, serta pembatasan role pada konteks donasi.

**Batas lingkungan:** Composer tidak tersedia dan folder `vendor/` tidak ada pada lingkungan penyusunan ZIP. Karena itu `php artisan test`, bootstrapping Laravel, route:list, dan HTTP end-to-end test belum dapat dijalankan di sini. Test cases sudah disiapkan, tetapi tidak boleh dianggap telah PASS sebelum dijalankan pada mesin yang memiliki dependensi Laravel.

Untuk menjalankan test pada komputer lokal setelah proyek terpasang:

```bash
php artisan test
```

## 5. Skenario smoke test manual yang disarankan

Gunakan akun demo `donatur@resfund.test` / `ResFund123!`.

1. Login dan pastikan diarahkan ke S07 Dashboard Donatur.
2. Periksa total donasi, jumlah proyek, refund, dan urutan transaksi terbaru.
3. Buka S08; cari/filter transaksi, buka detail transaksi SETTLED dan REFUNDED.
4. Buka S09; pilih proyek funded, active, dan failed; bandingkan status campaign dan milestone.
5. Buka S10 dari sidebar; pastikan halaman tetap memakai shell Donatur, tetapi hanya menampilkan data agregat.
6. Akses URL publik `/laporan-keuangan` pada jendela tanpa login; pastikan ringkasan tetap tersedia dan tidak menampilkan nomor kuitansi/ledger.
7. Buka S11; proyek bioplastik harus membuka 8 entri ledger. Coba filter kategori, pagination, dan tombol Lihat Kuitansi.
8. Coba membuka ledger proyek yang tidak memiliki donasi terverifikasi; akses harus ditolak.
9. Buka S12; periksa tiket contoh, ajukan klarifikasi pada transaksi ledger, dan periksa tiket baru beserta tenggat 72 jam.
10. Ubah profil, tandai satu notifikasi dibaca, lalu tandai semua notifikasi dibaca.
11. Uji drawer menu dan dialog kuitansi menggunakan keyboard, termasuk Tab, Shift+Tab, dan Escape.
12. Uji tampilan setidaknya pada lebar desktop 1440 px, tablet sekitar 768 px, dan smartphone sekitar 390 px.

## 6. Batasan yang diketahui

- Seluruh transaksi, ledger, milestone, notification, refund, dan ticket audit merupakan data contoh.
- Profil, notifikasi, dan klarifikasi disimpan sementara dalam session, bukan database permanen.
- Tidak ada Payment Gateway live/sandbox, proses payout/refund aktual, SSO/PDDikti, email/WhatsApp notification, atau integrasi Cloud Storage.
- Preview kuitansi adalah mock document untuk menguji interaksi, bukan bukti transaksi asli.
- Status reviewer dan payout dalam monitoring merupakan simulasi tampilan berdasarkan aturan SKPL.
- Hasil browser rendering dan test Laravel penuh belum dapat diverifikasi pada lingkungan pembuatan paket ini.

## 7. Pembaruan dari V1.1

- Menambahkan seluruh area dashboard Donatur S07–S12 dan Profil & Notifikasi.
- Memecah S07 dengan S08, bukan menggabungkan dashboard dan riwayat.
- Menempatkan S10 di sidebar Donatur, tetapi mempertahankan akses publik serta data agregat tanpa bukti sensitif.
- Menambahkan S11 dengan akses ledger per proyek yang dibatasi berdasarkan transaksi terverifikasi.
- Menambahkan modal preview kuitansi dan pagination ledger.
- Menambahkan S12 dengan validasi transaksi terkait, riwayat tiket, dan target respons 3 × 24 jam.
- Menambahkan route role middleware, demo data, dan test cases dashboard.
- Memperbesar teks sekunder dan menyempurnakan aksesibilitas drawer/modal.

**Kesimpulan QA:** pemeriksaan statis dan konsistensi data demonstrasi yang dapat dilakukan pada lingkungan ini lulus. Paket belum boleh dianggap telah lolos pengujian runtime Laravel atau E2E sebelum feature test dan smoke test dijalankan pada komputer lokal dengan dependensi terpasang.
