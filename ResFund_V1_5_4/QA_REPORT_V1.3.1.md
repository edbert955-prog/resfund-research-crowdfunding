# QA Report — ResFund Hi-Fi Interactive Prototype V1.3.1

## Ruang lingkup

V1.3.1 adalah patch antarmuka dan regression-check setelah masukan pengguna pada empat screenshot. Perubahan difokuskan pada seluruh area dashboard Donatur/Peneliti yang menggunakan layout sidebar bersama.

## Perbaikan yang diterapkan

1. Seluruh kode layar konseptual seperti S07–S20 dihapus dari teks yang dirender pada halaman Blade. Kode internal tetap boleh digunakan di dokumentasi pengembangan/traceability, tetapi tidak muncul pada antarmuka.
2. Scrollbar visual pada sidebar disembunyikan untuk browser Chromium/WebKit, Firefox, dan Edge; properti `overflow-y: auto` tetap aktif sehingga navigasi panjang tetap dapat digulir.
3. Tombol shortcut profil/notifikasi berbentuk kotak biru yang duplikatif di header Peneliti dihapus. Akses Profil & Validasi tetap tersedia melalui sidebar serta chip identitas pengguna.
4. Ikon centang pada banner verifikasi dan ikon flash sukses diberi pasangan warna foreground/background yang eksplisit agar tidak putih di atas putih.
5. Karakter `i` yang sebelumnya tampil sendiri pada callout informasi diganti dengan label teks seperti “Catatan”, “Ketentuan penting”, dan “Informasi”, atau SVG informasi yang konsisten.
6. Query-string aset statis dinaikkan ke `v=1.3.1` untuk menghindari cache CSS/JS lama.
7. Pesan login Peneliti tidak lagi menyebut versi prototype secara berlebihan.

## Pemeriksaan statis yang dijalankan

| Area | Pemeriksaan | Hasil |
|---|---|---|
| PHP | `php -l` pada source, Blade, route, config, middleware, dan tests | PASS — 69 file |
| JavaScript | `node --check` untuk seluruh JS lokal | PASS — 4 file |
| CSS | Parse CSS menggunakan `tinycss2` (termasuk nested media rules) | PASS — 0 parser/declaration error pada `app.css`, `donor-dashboard.css`, dan `researcher-dashboard.css` |
| Composer | Parse `composer.json` | PASS — JSON valid |
| Dependensi | Bandingkan blok `require` dan `require-dev` dengan V1.3 | IDENTIK — tidak perlu Composer install ulang |
| Route | Cocokkan 206 pemanggilan named-route pada Blade/controller dengan definisi route | PASS — tidak ditemukan route yang hilang |
| Aset lokal | Cocokkan referensi `asset('assets/...')` pada Blade dengan file di `public` | PASS — tidak ada aset lokal hilang |
| CSRF | Periksa form POST pada Blade | PASS — semua form POST terdeteksi memiliki `@csrf` |
| Kode layar UI | Cari pola S07–S20 pada seluruh Blade | PASS — tidak ada kode layar pada UI |
| Karakter `i` lepas | Cari `<span>i</span>` pada seluruh Blade | PASS — tidak ditemukan |
| Shortcut header Peneliti | Periksa `layouts/researcher.blade.php` | PASS — tidak ada tombol shortcut duplikatif |
| Sidebar | Pastikan `overflow-y:auto` tetap ada bersama scrollbar hiding | PASS — dapat scroll tanpa track visual |
| Test suite | Hitung test methods | 63 test method tersedia |

## Pemeriksaan aksesibilitas visual berbasis source

- Ikon sukses: putih pada latar hijau gelap `#176b4a`.
- Ikon informasi: putih pada latar biru `#245fce`.
- Ikon validasi akademik: hijau gelap `#145c3c` pada latar hijau pucat `#d9f1e2`.
- Langkah verifikasi selesai: hijau gelap `#145c3c` pada latar `#e5f5eb`.
- Callout tidak lagi mengandalkan glyph huruf tunggal yang bisa tampak seperti karakter acak.
- Sidebar tetap dapat digulir melalui wheel, touch, dan keyboard saat fokus berada pada navigasi.

## Pemeriksaan kontras ikon status

| Foreground | Background | Rasio kontras | Hasil |
|---|---|---:|---|
| `#ffffff` | `#176b4a` (ikon sukses) | 6,48:1 | PASS |
| `#ffffff` | `#245fce` (ikon informasi) | 5,83:1 | PASS |
| `#145c3c` | `#d9f1e2` (identitas terverifikasi) | 6,71:1 | PASS |
| `#145c3c` | `#e5f5eb` (langkah selesai) | 7,08:1 | PASS |

Seluruh kombinasi di atas melampaui acuan 4,5:1 untuk teks normal pada materi accessibility.

## Pemeriksaan workflow dan business rule

Tidak ada perubahan pada aturan bisnis S13–S16 dalam patch UI ini. Pemeriksaan source dan test yang disertakan tetap mencakup:

- Proposal mempunyai 2–5 milestone, dengan total alokasi 100%.
- Durasi kampanye 15–90 hari.
- Pencairan awal memerlukan SPTJM dan dibatasi maksimal 30% dari dana terkumpul serta alokasi milestone pertama.
- Pengajuan termin berikutnya memerlukan milestone sebelumnya sudah dicairkan dan dokumen luaran teknis serta pertanggungjawaban keuangan.
- Pengajuan payout menunggu persetujuan minimal dua reviewer independen; prototipe tidak melakukan transfer nyata.
- Pengeluaran hanya dapat dicatat pada milestone yang dananya telah dicairkan dan tidak boleh melebihi sisa dana tersedia.
- Final Ledger tidak dapat dipublikasikan ulang dan bersifat read-only pada UI.
- Hak akses halaman Peneliti tetap dibatasi untuk role `peneliti`.

## Distribusi feature test

| File | Test method |
|---|---:|
| `PublicAndAuthenticationTest.php` | 19 |
| `DonationCheckoutTest.php` | 6 |
| `DonorDashboardTest.php` | 21 |
| `ResearcherDashboardTest.php` | 17 |
| **Total** | **63** |

Dua test tambahan memeriksa bahwa kode layar konseptual dan shortcut profil duplikatif tidak muncul di halaman Peneliti, serta bahwa status validasi tetap memiliki indikator teks/warna yang terbaca. Test Donatur juga memeriksa kode layar internal tidak dirender.

## Batas pengujian runtime

PHPUnit belum dapat dieksekusi di lingkungan pembuatan ZIP karena folder `vendor` tidak tersedia dan Composer tidak terpasang. `php artisan test` berhenti sebelum bootstrapping karena `vendor/autoload.php` tidak ditemukan. Browser automation untuk aplikasi lokal juga tidak dapat dijalankan di sandbox ini. Oleh sebab itu, laporan ini **tidak mengklaim** bahwa PHPUnit atau browser end-to-end telah lulus.

Setelah menggabungkan file V1.3.1 pada Laragon, jalankan:

```bash
php artisan test
php artisan optimize:clear
php artisan serve
```

Kemudian lakukan regression check manual: buka halaman dashboard dan profil Peneliti, scroll sidebar dari atas ke bawah, pastikan scrollbar track tidak terlihat, cek ikon centang, pastikan tombol shortcut duplikatif dan karakter `i` tidak muncul, lalu buka semua menu S13–S16 pada desktop 1440 px, tablet 768 px, dan mobile 390 px.

## Upgrade

`composer.json` tidak berubah dibanding V1.3. Jadi V1.3.1 dapat menimpa source V1.3 pada folder proyek yang sama tanpa menjalankan `composer install` ulang, selama `vendor` lama masih tersedia. Pertahankan `.env`, `vendor`, dan `storage`.
