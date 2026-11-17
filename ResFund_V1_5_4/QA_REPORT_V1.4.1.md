# Laporan QA — ResFund V1.4.1

## Ruang lingkup perubahan

- Menambahkan menu **Riwayat Keputusan** untuk Reviewer.
- Menambahkan menu **Profil & Notifikasi** untuk Reviewer.
- Menambahkan route GET riwayat, GET profil, POST pembaruan profil, POST tandai notifikasi, dan POST tandai semua.
- Menambahkan tampilan riwayat yang dibatasi pada keputusan milik reviewer yang sedang login.
- Menambahkan profil dengan identitas akademik baca-saja, validasi nomor telepon, serta preferensi notifikasi.
- Menambahkan notifikasi berbasis status penugasan, keputusan pribadi, pembaruan panel, konflik kepentingan, dan tenggat.
- Mempertahankan aturan dual approval dan area Donatur/Peneliti yang sudah ada.

## Pemeriksaan yang dijalankan

| Pemeriksaan | Hasil |
|---|---|
| PHP lint pada app, routes, tests, dan bootstrap | PASS — semua file yang diperiksa tidak memiliki syntax error |
| JavaScript syntax check | PASS — semua file JS melalui `node --check` |
| CSS parsing | PASS — parser tidak menemukan error pada `reviewer-dashboard.css` |
| Keseimbangan kurung CSS | PASS — brace count seimbang |
| Route references pada Blade Reviewer | PASS — seluruh nama route yang dipakai tersedia pada deklarasi route Reviewer/Publik |
| Kode layar internal pada UI baru | PASS — tidak ada label S17/S18/S19/S20 di konten halaman baru |
| Pembatasan riwayat | Implementasi mengambil `reviews[reviewer_id]` dari assignment yang ditugaskan ke reviewer itu |
| Preferensi dan status baca | Disimpan per reviewer di session |

## Feature tests

Folder `tests/Feature` sekarang berisi **96 test method**. Sembilan test ditambahkan untuk:

1. Kehadiran menu Riwayat Keputusan dan Profil & Notifikasi.
2. Tampilan empty state sebelum ada keputusan.
3. Riwayat keputusan hanya milik reviewer yang sedang login.
4. Tampilan identitas dan preferensi profil.
5. Pembaruan nomor telepon dan preferensi tanpa perubahan role.
6. Validasi nomor telepon.
7. Pembuatan notifikasi keputusan dan penandaan telah dibaca.
8. Penandaan seluruh notifikasi sebagai telah dibaca.
9. Pembatasan akses route untuk non-Reviewer.

**Catatan:** feature test tersedia tetapi belum dieksekusi pada lingkungan pembuatan karena folder `vendor` dan Composer tidak tersedia. Laravel runtime/browser end-to-end juga tidak dapat dijalankan di lingkungan tersebut. Oleh karena itu, tidak ada klaim bahwa PHPUnit atau pengujian browser telah lulus. Jalankan `php artisan test` pada komputer lokal setelah dependensi proyek tersedia.

## Catatan business logic

- Riwayat menyimpan keputusan atau deklarasi konflik dari reviewer yang sedang login saja.
- Keputusan reviewer lain tidak ditampilkan sebagai catatan privat di riwayat pribadi, walaupun status agregat panel tetap diperlihatkan.
- Nama, email, institusi, bidang keahlian, dan role Reviewer tidak dapat diubah dari menu profil; nomor telepon dapat diperbarui.
- Preferensi notifikasi dan status baca bersifat session-only. Belum ada email/WhatsApp notification service.
- Keputusan final tetap terkunci dan tidak mengubah aturan bahwa payout membutuhkan persetujuan seluruh reviewer yang ditugaskan.

## Upgrade

`composer.json` `require` dan `require-dev` tidak berubah dibanding V1.4. Tidak perlu `composer install` ulang apabila folder `vendor` dari V1.4 masih tersedia. Pertahankan `.env`, `vendor`, dan `storage` saat menyalin file revisi.
