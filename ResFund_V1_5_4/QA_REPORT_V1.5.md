# Laporan QA — ResFund Hi-Fi Interactive Prototype V1.5

Tanggal pemeriksaan: 17 November 2026  
Ruang lingkup: penambahan Area Administrator dan regresi statis paket sebelumnya.

## 1. Cakupan Administrator

Halaman yang dibuat mengikuti Information Architecture yang diberikan:

1. Dashboard Admin.
2. Manajemen Pengguna.
3. Validasi Peneliti.
4. Manajemen Reviewer.
5. Penugasan Reviewer.
6. Monitoring & Audit Log (halaman tersendiri).

Area Publik, Donatur, Peneliti, dan Reviewer dari V1.4.1 dipertahankan.

## 2. Pemeriksaan otomatis yang berhasil

| Pemeriksaan | Hasil | Catatan |
|---|---|---|
| PHP syntax | PASS | 29 file PHP pada `app`, `routes`, `bootstrap`, dan `tests` lolos `php -l`. |
| JavaScript syntax | PASS | Semua 5 file JavaScript pada `public/assets/js` lolos `node --check`. |
| CSS parsing | PASS | 2.221 aturan CSS pada 5 file; 0 parser error dengan parser CSS. |
| Composer JSON | PASS | `composer.json` valid sebagai JSON. |
| Composer dependencies | PASS | Bagian `require`, `require-dev`, `autoload`, dan `autoload-dev` sama dengan V1.4.1. Tidak ada dependensi baru. |
| Route references Administrator | PASS | 14 nama route Administrator yang dipakai Blade cocok dengan route yang dideklarasikan. |
| Admin templates | PASS | 9 Blade view Administrator ditemukan. Tidak ada kode layar konseptual seperti S19/S20 yang ditampilkan pada UI Administrator. |
| Flash icon | PASS | Ikon pesan informasi memakai SVG, bukan karakter huruf `i` tunggal. |

## 3. Skenario feature test yang disertakan

Terdapat 108 metode feature test pada seluruh proyek, termasuk 12 test Administrator pada `tests/Feature/AdministratorDashboardTest.php`:

- Login Administrator demo mengarah ke Dashboard Admin.
- Route Administrator menolak Donatur, Peneliti, dan Reviewer.
- Enam halaman pengelolaan Administrator dapat dirender.
- Administrator dapat menyetujui validasi peneliti dan mencatat aktivitas.
- Peneliti yang belum tervalidasi tidak dapat diaktifkan melalui Manajemen Pengguna.
- Administrator dapat membuat akun Reviewer baru dan akun tersebut dapat login pada sesi yang sama.
- Penugasan ulang menerima dua reviewer independen.
- Pasangan reviewer duplikat ditolak.
- Monitoring & Audit Log menampilkan aktivitas dan tidak menyediakan aksi edit/hapus.
- Akun yang ditangguhkan ditolak ketika mencoba login berikutnya.
- Registrasi publik tidak dapat memilih role Administrator.
- UI Administrator tidak menampilkan kode layar konseptual.

## 4. Pemeriksaan logika bisnis yang ditinjau

- Administrator hanya dapat membuka route dengan middleware role `administrator`.
- Validasi NIDN/NIM dan keputusan persetujuan/penolakan berada di menu Validasi Peneliti, bukan sekadar perubahan status pengguna.
- Akun Reviewer yang dinonaktifkan ikut diblokir dari login/akses area Reviewer.
- Penugasan panel memerlukan tepat dua reviewer aktif.
- Kedua reviewer harus berasal dari institusi berbeda dan bukan institusi peneliti.
- Reviewer yang telah melaporkan konflik kepentingan tidak dapat ditugaskan kembali ke pengajuan yang sama.
- Penugasan ulang menyimpan snapshot panel/keputusan sebelumnya, lalu memulai putaran evaluasi baru. Keputusan panel lama tidak digabung dengan panel baru.
- Administrator mengelola panel tetapi tidak dapat mengirim keputusan rubrik atas nama reviewer dan tidak memiliki fungsi eksekusi payout.
- Audit log ditampilkan read-only melalui antarmuka.

## 5. Batas pengujian

PHPUnit belum dieksekusi karena paket ini tidak menyertakan folder `vendor` dan lingkungan pembuatan tidak menyediakan instalasi dependensi Composer. Browser end-to-end juga belum dapat diklaim lulus pada lingkungan ini. Karena itu, hasil PASS di atas adalah pemeriksaan sintaks, konfigurasi, parsing CSS, dan keterlacakan route/view; 108 feature test disediakan agar dapat dijalankan pada mesin lokal dengan dependensi Laravel terpasang.

Data Administrator, pengguna, validasi, reviewer, penugasan, dan audit log pada V1.5 masih berbasis session prototype. Validasi SSO/PDDikti, penyimpanan database produksi, email/WhatsApp, dan integrasi Payment Gateway live belum tersedia. Audit log session prototype tidak boleh dianggap sebagai audit log produksi yang tahan manipulasi.

## 6. Cara menjalankan pengujian pada komputer lokal

```bash
php artisan test
```

Untuk upgrade dari V1.4.1, dependensi Composer tidak berubah sehingga `composer install` tidak perlu diulang selama folder `vendor` lama masih lengkap dan sesuai.
