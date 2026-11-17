# ResFund Interactive Prototype — V1.2.1

Prototype Hi-Fi interaktif berbasis Laravel untuk mata kuliah Analisa dan Perancangan Perangkat Lunak (APPL).

V1.2.1 merupakan perbaikan atas V1.2 setelah pengujian penggunaan. Versi ini memperbaiki tombol Donasi Sekarang agar Donatur yang sudah login langsung masuk checkout, menambahkan halaman pembayaran simulasi, memperbarui riwayat donasi, serta mengubah transparansi keuangan menjadi daftar ringkasan beberapa proyek. Versi ini tetap merupakan prototype frontend, bukan sistem transaksi keuangan operasional.

## Ruang lingkup V1.2.1

| Kode IA | Halaman / Menu | Akses | Implementasi |
|---|---|---|---|
| S01 | Beranda ResFund | Publik | Sudah ada sejak V1.0 |
| S02 | Jelajahi Proyek, pencarian, filter | Publik | Sudah ada sejak V1.0 |
| S03 | Detail Proyek Riset | Publik | Foto penelitian, informasi proyek, milestone, tombol donasi yang terhubung ke checkout |
| S06 | Login dan Registrasi | Semua peran yang diizinkan | Simulasi sesi; role Donatur/Peneliti |
| S04 | Checkout / Formulir Donasi | Donatur | Nominal, metode gateway, escrow, validasi target |
| S05 | Pembayaran dan Konfirmasi | Donatur | Halaman Payment Gateway simulasi, status SETTLED, konfirmasi |
| S07 | Dashboard Donatur | Donatur | Ringkasan donasi, proyek, refund, notifikasi |
| S08 | Donasi Saya / Riwayat dan Detail Donasi | Donatur | Filter, status pembayaran/kampanye, detail/refund |
| S09 | Monitoring Proyek dan Milestone | Donatur | Status kampanye, target dana, timeline milestone, status review/pencairan |
| S10 | Ringkasan Keuangan Publik | Publik dan Donatur | Anggaran vs realisasi, komposisi kategori, tabel agregat; tanpa bukti sensitif |
| S11 | Ledger dan Preview Kuitansi | Donatur dengan transaksi terverifikasi pada proyek | Filter ledger, pagination, preview kuitansi menggunakan modal |
| S12 | Klarifikasi Audit | Donatur terdaftar dengan donasi sah pada proyek | Form pertanyaan, riwayat tiket, target respons 3 × 24 jam |
| — | Profil & Notifikasi | Donatur | Edit profil sesi, daftar notifikasi, tandai dibaca |

Sidebar dashboard mengikuti urutan menu pada sitemap yang diberikan: Dashboard Donatur; Donasi Saya / Riwayat; Monitoring Proyek; Laporan Keuangan; Ledger & Preview Kuitansi; Klarifikasi Audit; Profil & Notifikasi.

### Pemisahan hak akses S10 dan S11

S10 adalah ringkasan keuangan publik yang menampilkan beberapa proyek sekaligus. Menu ini tersedia dari website publik dan juga ditautkan dari sidebar dashboard Donatur. Setiap proyek menampilkan status finansial sesuai tahapnya: dana escrow untuk kampanye aktif, realisasi pengeluaran untuk proyek dengan ledger yang telah dipublikasikan, atau jumlah refund untuk kampanye yang gagal.

S11 berbeda dari S10: rincian transaksi ledger dan pratinjau kuitansi hanya dibuka bagi Donatur yang memiliki donasi berstatus berhasil/SETTLED dan terverifikasi pada proyek yang bersangkutan. Pengguna publik tidak dapat membuka data bukti sensitif.

### Alur checkout donasi V1.2.1

1. Pengunjung memilih proyek dan menekan **Donasi sekarang**.
2. Jika belum login, pengguna diarahkan ke login/registrasi dengan konteks proyek tetap dibawa. Setelah berhasil masuk sebagai Donatur, sistem melanjutkan ke checkout.
3. Donatur yang sudah login langsung diarahkan ke checkout tanpa kembali ke detail proyek.
4. Checkout memvalidasi minimum donasi Rp10.000 dan memastikan nominal tidak melebihi sisa target kampanye.
5. Donatur memilih metode pembayaran umum yang disediakan oleh Payment Gateway (QRIS, Virtual Account, E-Wallet, atau kartu), tanpa transfer manual dan tanpa unggah bukti transfer.
6. Sistem menampilkan halaman pembayaran simulasi. Tombol **Simulasikan pembayaran berhasil** mengubah status menjadi SETTLED untuk menguji alur frontend.
7. Transaksi simulasi ditambahkan ke Donasi Saya, saldo terkumpul proyek bertambah pada sesi browser, dan halaman konfirmasi ditampilkan.

**Penting:** tidak ada QRIS/Virtual Account sungguhan, tidak ada komunikasi API Payment Gateway, dan tidak ada dana yang dipindahkan. Halaman tersebut hanya memvisualisasikan interaksi prototype. Untuk produksi, status SETTLED harus datang dari callback/webhook Payment Gateway terverifikasi.

## Fitur sesuai SKPL yang ditampilkan

- Donasi tercatat sebagai berhasil hanya pada data contoh dengan status `SETTLED`.
- Status pembayaran, status kampanye, status milestone, status review, dan status refund ditampilkan sebagai informasi yang berbeda.
- Kampanye berstatus `FAILED` menampilkan refund 100% ke metode pembayaran asal pada detail donasi.
- Kampanye aktif menampilkan dana yang masih berada di escrow dan tidak menampilkan pengeluaran yang belum ada.
- Halaman transparansi menampilkan empat proyek contoh dengan status aktif, tercapai, dan gagal; bukan hanya satu proyek.
- Kampanye `FUNDED` menampilkan bahwa dana berada dalam escrow dan pencairan dilakukan berdasarkan milestone, bukan seluruhnya sekaligus.
- Status pencairan milestone mempertimbangkan evaluasi dua reviewer; pencairan hanya boleh diteruskan apabila seluruh reviewer yang ditugaskan menyetujui.
- Ledger final bersifat read-only/append-only pada tampilan Donatur.
- Tombol setiap baris ledger membuka modal preview kuitansi. Dokumen yang ditampilkan merupakan contoh simulasi, bukan kuitansi asli.
- Form Klarifikasi Audit hanya menerima transaksi pada ledger proyek terkait dan menampilkan target respons 3 × 24 jam.

## Teknologi

- Laravel 13 (`laravel/framework:^13.0`)
- PHP 8.3 atau lebih baru
- Blade untuk template UI
- CSS custom dengan design tokens dan reusable components
- JavaScript vanilla untuk interaksi UI dan modal preview kuitansi
- Session file untuk autentikasi, profil, notifikasi, dan tiket audit simulasi
- Data proyek, donasi, dan ledger demonstrasi disimpan di class `app/Support`, bukan database
- Tidak memerlukan Node.js atau proses `npm run build`

Ekstensi PHP Laravel yang dibutuhkan termasuk Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, dan XML.

## Memperbarui proyek V1.2 yang sudah terpasang

**Tidak perlu membuat proyek Laravel baru dan tidak perlu menjalankan `composer install` lagi**, selama proyek V1.2 yang sudah ada mempunyai `vendor/` dan dependensi Laravel berhasil dipasang.

1. Buat salinan cadangan folder proyek V1.2 terlebih dahulu.
2. Ekstrak ZIP V1.2.1 ke folder sementara.
3. Salin isi folder proyek V1.2.1 ke folder proyek V1.2 yang sudah ada.
4. Pilih Replace/Overwrite untuk berkas aplikasi yang diperbarui.
5. **Jangan menimpa `.env` dan folder `vendor/` milik proyek yang sudah ada.**
6. Buka terminal pada folder proyek lama, lalu jalankan:

```bash
php artisan optimize:clear
php artisan serve
```

7. Buka `http://127.0.0.1:8000`.

V1.2.1 tidak menambahkan dependensi runtime baru dibandingkan V1.2. `composer.json` disertakan untuk dokumentasi proyek, tetapi pada pembaruan yang memakai `vendor/` V1.2 yang sudah ada, `composer install` tidak perlu diulang.

## Instalasi baru dari ZIP V1.2.1

Jika ini instalasi Laravel pertama di komputer, gunakan PHP 8.3+ dan Composer, lalu:

```bash
copy .env.example .env
composer install
php artisan key:generate
php artisan serve
```

Di Windows, `install-and-run.bat` membantu menjalankan proyek. Script akan melewati Composer jika folder `vendor/autoload.php` sudah tersedia.

## Akun demo

- Email: `donatur@resfund.test`
- Kata sandi: `ResFund123!`

Akun demo menampilkan data donasi, refund, milestone, ledger, notifikasi, dan satu tiket klarifikasi contoh. Pengguna yang mendaftar dengan email lain dapat masuk ke dashboard, tetapi akan melihat empty state karena data donasi tidak dibuat otomatis.

## Alur yang dapat dicoba

1. Buka detail proyek baterai dan tekan **Donasi sekarang**.
2. Login dengan akun demo. Karena akun sudah login sebagai Donatur, sistem langsung membuka checkout.
3. Masukkan nominal (misalnya Rp250.000), pilih QRIS/Virtual Account/E-Wallet/kartu, lalu centang persetujuan escrow.
4. Lanjutkan ke halaman Payment Gateway simulasi dan tekan **Simulasikan pembayaran berhasil**. Transaksi akan muncul di Donasi Saya dan progres proyek pada sesi browser bertambah.
5. Dashboard Donatur menampilkan total donasi berhasil, jumlah proyek, proyek aktif, refund, transaksi terbaru, progres singkat, dan notifikasi.
6. Buka **Donasi Saya / Riwayat** untuk mencari transaksi dan menyaring status pembayaran. Buka Detail untuk melihat referensi transaksi atau refund.
7. Buka **Monitoring Proyek** dan pilih proyek. Lihat target pendanaan dan timeline milestone, termasuk status reviewer dan pencairan.
8. Buka **Laporan Keuangan** dari sidebar Donatur. Halaman ini tetap menampilkan ringkasan publik/agregat dan tidak membuka ledger sensitif.
9. Buka **Ledger & Preview Kuitansi**. Pilih proyek bioplastik yang memiliki ledger; gunakan pencarian/filter kategori, pagination, lalu klik **Lihat kuitansi** untuk membuka modal preview.
10. Buka **Klarifikasi Audit**. Pilih proyek bioplastik dan buat pertanyaan atas salah satu transaksi. Tiket disimpan pada session prototype dan menampilkan target respons 3 × 24 jam.
11. Buka **Profil & Notifikasi** untuk mengubah nama/nomor telepon dan menandai notifikasi telah dibaca.
12. Gunakan menu **Kembali ke Website** untuk menuju area publik.

## Design system dan accessibility

Materi Pertemuan 12 diterapkan melalui:

- CSS design tokens untuk warna, permukaan, teks, border, status, spacing, dan bayangan.
- Reusable Blade layout untuk sidebar, topbar, notifikasi, tombol, tabel, status badge, dan panel.
- Tipografi dashboard diperbesar dibanding versi awal; label form jelas, tabel dan informasi sekunder ditingkatkan keterbacaannya.
- Focus-visible, skip link, label form, pesan validasi, status yang menggunakan teks/ikon (bukan warna saja), dan tombol kontrol dengan target sentuh yang nyaman.
- Sidebar desktop berubah menjadi menu drawer pada layar kecil.
- Modal preview kuitansi mendukung tombol tutup, Escape, dan pengembalian fokus ke tombol pemicu.
- Preferensi `prefers-reduced-motion` diperhatikan.

Materi P12 menyebutkan prinsip Hi-Fi prototype: visual consistency, functional interaction, navigation clarity, business flow alignment, responsive layout, dan accessibility. Materi juga membahas design tokens/reusable components serta prinsip POUR dan kontras teks normal WCAG minimal 4.5:1.

## Pengujian

Feature test disediakan di:
- `tests/Feature/PublicAndAuthenticationTest.php`
- `tests/Feature/DonorDashboardTest.php`
- `tests/Feature/DonationCheckoutTest.php`

Terdapat 45 test case (19 autentikasi/area publik, 20 dashboard Donatur, dan 6 alur checkout). Jalankan di lingkungan yang sudah memiliki Composer, `vendor/`, ekstensi PHP yang dibutuhkan, dan PHPUnit:

```bash
php artisan test
```

Skenario dashboard mencakup akses berdasarkan role, menu IA, daftar/detail donasi, status dan refund, monitoring milestone, S10 publik/agregat, hak akses ledger, pagination/filter, preview kuitansi, klarifikasi audit, validasi transaksi yang dipilih, profil, dan notifikasi.

**Catatan pengujian:** pemeriksaan sintaks PHP/JS/CSS dan konsistensi route dapat dilakukan sebelum instalasi dependency. Feature test Laravel memerlukan `vendor/`; jika belum ada di komputer, jalankan `composer install` satu kali untuk instalasi baru atau pemasangan awal. Lihat `QA_REPORT_V1.2.1.md` untuk hasil pemeriksaan paket ini dan batasan yang belum diuji secara end-to-end.

## Foto dan aset visual

- Foto utama penelitian baterai/biomassa adalah aset ilustratif fotorealistik yang dibuat khusus untuk prototype, bukan dokumentasi kegiatan penelitian sebenarnya.
- Beberapa foto proyek menggunakan gambar Unsplash dan membutuhkan koneksi internet pada browser. Jika gambar eksternal gagal dimuat, tampilan menggunakan foto lokal proyek baterai sebagai fallback.
- Sumber foto Unsplash yang digunakan: [water sample bottles — The Tampa Bay Estuary Program / Joe Whalen](https://unsplash.com/photos/a-row-of-bottles-filled-with-liquid-sitting-on-a-shelf-DeR4Zt6rOB0), [laboratory researcher — National Cancer Institute](https://unsplash.com/photos/a-woman-in-white-lab-coat-and-gloves-working-on-a-machine-AnO1h192QBE), dan [cassava roots — Daniel Dan](https://unsplash.com/photos/pile-of-freshly-harvested-cassava-roots-sixDoAtyVwo). Foto digunakan sebagai ilustrasi tema proyek, bukan dokumentasi penelitian kelompok.

## Batasan prototype

- Data donasi, saldo escrow, ledger, status milestone, kuitansi, dan klarifikasi bersifat ilustratif.
- Login/session bukan autentikasi produksi dan belum memakai database.
- Profile, notifikasi, serta tiket klarifikasi hanya disimpan sementara dalam session.
- Tidak ada Payment Gateway live/sandbox, transfer dana, payout, automated refund, email, SSO/PDDikti, atau penyimpanan dokumen S3 yang benar-benar dijalankan.
- Status dua reviewer pada milestone merupakan data simulasi yang mencerminkan aturan SKPL, bukan proses review operasional.
- Modal kuitansi adalah representasi visual untuk menguji interaksi; bukan dokumen legal.

## Struktur penting

```text
app/Http/Controllers/       Controller publik, autentikasi, checkout, pembayaran simulasi, dan dashboard Donatur
app/Http/Middleware/        Pembatasan role Donatur
app/Support/                Data demonstrasi proyek, donasi, notifikasi, ledger
resources/views/donor/      Halaman S07–S12 dan Profil/Notifikasi
resources/views/layouts/    Layout publik dan layout dashboard Donatur
public/assets/css/          Design tokens dan styling
public/assets/js/           Interaksi UI, drawer mobile, checkout nominal donasi, preview kuitansi
routes/web.php              Route area publik, autentikasi, dan dashboard Donatur
tests/Feature/              Skenario pengujian
```

## Versi

`ResFund_Interactive_Prototype_V1.2.1` — memperbaiki alur Donasi Sekarang → Checkout → Payment Gateway simulasi → Konfirmasi; menambahkan pembaruan session untuk riwayat Donasi Saya dan progres proyek; mengganti S10 menjadi daftar ringkasan beberapa proyek; memperbaiki layout grafik agar persentase tidak menutupi label; serta mengganti ilustrasi template dengan foto penelitian yang lebih realistis.
