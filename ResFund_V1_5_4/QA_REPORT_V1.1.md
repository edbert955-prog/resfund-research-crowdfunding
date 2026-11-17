# QA Report — ResFund Interactive Prototype V1.1

## Ruang lingkup revisi

- Ukuran teks utama dinaikkan: body 17px, label form 16px, input 17px, dan sebagian besar teks informatif minimal 14px.
- Pendaftaran mewajibkan pilihan role eksplisit: Donatur atau Peneliti; tidak ada role yang terpilih otomatis.
- Kolom NIDN/NIM, status Dosen/Mahasiswa, dan perguruan tinggi hanya muncul serta diwajibkan untuk Peneliti.
- Reviewer dan Administrator tidak dapat mendaftar melalui form publik.
- Konteks donasi hanya dapat dilanjutkan dengan role Donatur.
- Login/registrasi tidak dapat digunakan untuk mengganti akun selama sesi masih login; pengguna harus logout terlebih dahulu.
- Logout meregenerasi session ID dan token CSRF.
- Tombol donasi tetap menjadi gerbang autentikasi; checkout dan Payment Gateway belum menjadi cakupan V1.1.

## Pemeriksaan yang berhasil

| Pemeriksaan | Hasil | Catatan |
|---|---|---|
| PHP syntax lint | PASS | Seluruh file PHP pada project lolos `php -l`. |
| JavaScript syntax | PASS | `node --check public/assets/js/app.js`. |
| composer.json | PASS | JSON valid; constraint Laravel 13 dan PHP 8.3+. |
| CSS parse | PASS | 627 top-level CSS rules diparse tanpa error oleh tinycss2. |
| SVG assets | PASS | 4 file SVG valid sebagai XML. |
| Route references | PASS | 12 nama route yang dipakai Blade dideklarasikan pada `routes/web.php`. |
| Local assets | PASS | Semua referensi aset lokal dari Blade ditemukan. |
| POST CSRF | PASS | Setiap view yang memiliki form POST memuat `@csrf`. |
| Role constraints | PASS | Controller hanya menerima `donatur` atau `peneliti`; Reviewer/Admin ditolak. |
| Donation-intent constraint | PASS | Intent donasi menolak pendaftaran Peneliti. |
| Contrast sampling | PASS | Pasangan warna inti yang diuji berada di atas 4.5:1. |

### Rasio kontras sampel

| Kombinasi | Rasio |
|---|---:|
| Teks utama #17263C pada putih | 15.23:1 |
| Teks isi #334155 pada putih | 10.35:1 |
| Teks sekunder #586A82 pada putih | 5.53:1 |
| Biru utama #245FCE pada putih | 5.83:1 |
| Putih pada navy #173B73 | 11.00:1 |
| Teks hint #405570 pada latar #F7FAFF | 7.30:1 |

## Feature tests yang disertakan

`tests/Feature/PublicAndAuthenticationTest.php` berisi 19 skenario untuk: akses halaman publik, pencarian dan filter, 404 slug, gerbang login donasi, validasi email/password, role wajib, validasi data akademik, pendaftaran Peneliti menunggu verifikasi, pembatasan role Reviewer/Admin, penolakan Peneliti pada intent donasi, duplikasi email, login ulang akun pendaftaran, login demo, akses donasi berdasarkan role, pencegahan pergantian akun melalui POST, logout, dan pembatasan informasi keuangan publik.

## Pemeriksaan yang belum dapat dieksekusi di lingkungan pembuatan

- `composer install` dan `php artisan test` **belum dijalankan** karena Composer tidak tersedia dan akses jaringan untuk mengunduh dependensi tidak aktif. Folder `vendor` karena itu tidak disertakan.
- Browser end-to-end terhadap aplikasi Laravel **belum dijalankan** karena aplikasi belum dapat diboot tanpa dependensi Laravel. Upaya browser lokal pada lingkungan ini diblokir kebijakan lingkungan (`ERR_BLOCKED_BY_ADMINISTRATOR`).
- Lingkungan PHP pemeriksaan juga tidak memiliki seluruh ekstensi yang diminta Laravel 13 (misalnya Mbstring, cURL, DOM, dan XML).

Setelah diekstrak pada komputer dengan PHP 8.3+ dan ekstensi yang diperlukan, jalankan `composer install`, `php artisan key:generate`, lalu `php artisan test`. Sesudah itu jalankan `php artisan serve` dan lakukan pemeriksaan manual pada desktop, tablet, dan smartphone.

## Batas prototipe

Login dan pendaftaran V1.1 adalah simulasi berbasis session, bukan autentikasi produksi. Data akun hasil pendaftaran hanya disimpan sementara di session dan tidak menggunakan database permanen. Payment Gateway, checkout, verifikasi email, SSO/PDDikti, serta dashboard privat belum terintegrasi.
