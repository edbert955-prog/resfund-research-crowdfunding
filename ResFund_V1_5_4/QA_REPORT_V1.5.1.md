# QA Report — ResFund V1.5.1

## Ringkasan

V1.5.1 memperbaiki tampilan berdasarkan tangkapan layar pengguna, terutama header publik yang membungkus navigasi, ketidakkonsistenan tombol Masuk/Daftar, ukuran logo yang berbeda, dan ikon antarmuka yang masih memakai glyph karakter. Area Administrator dan fitur versi sebelumnya tetap dipertahankan.

## Perubahan yang diverifikasi

1. **Logo PNG:** logo utama, logo putih untuk permukaan gelap, simbol aplikasi, dan favicon disediakan sebagai aset PNG lokal.
2. **Navbar desktop:** elemen navigasi, dashboard role, identitas akun, dan tombol Keluar tetap satu baris pada desktop. Pada lebar 1060 px ke bawah, navigasi berubah menjadi menu mobile.
3. **Login/Daftar:** kedua tombol mempunyai ukuran konsisten. Pada route Login, hanya tombol Masuk berwarna biru; pada route Daftar, hanya tombol Daftar berwarna biru.
4. **Layout autentikasi:** logo form, ukuran kartu logo, heading, konten panel kiri, dan posisi panel kiri diseragamkan.
5. **Akun demo:** Administrator (`admin@resfund.test`) dicantumkan bersama Donatur, Peneliti, Reviewer A, dan Reviewer B.
6. **Dekorasi beranda:** garis pendek sebelum eyebrow dihapus.
7. **Ikon:** ikon UI menggunakan Blade component `<x-icon>` dan SVG lokal `resfund-icons.svg`. Logo brand tidak lagi dibangun dari teks/emoji.
8. **Audit metric:** total dana terhimpun bersih mengecualikan kampanye FAILED yang mengikuti aturan All-or-Nothing refund. Nilai kampanye gagal ditampilkan terpisah sebagai jumlah yang dikecualikan.

## Hasil pemeriksaan statis

| Pemeriksaan | Hasil |
|---|---|
| PHP syntax pada source `app`, `bootstrap`, `config`, `database`, `routes`, `tests`, dan `artisan` | 40 file, 0 parse error |
| JavaScript (`node --check`) | Seluruh file JS di `public/assets/js` lolos |
| CSS parsing (tinycss2) | Seluruh stylesheet lolos, 0 parse error |
| SVG sprite | XML valid |
| PNG logo/mark/favicon | Seluruh aset dapat dibuka dan didekode |
| Route references pada Blade | 67 nama route yang dipanggil cocok dengan route yang dideklarasikan (pemeriksaan statis) |
| Ikon yang direferensikan | Nama simbol statis cocok dengan ID dalam SVG sprite; nama dinamis menggunakan daftar ID yang tersedia |
| Composer dependency | `composer.json` V1.5.1 sama dengan V1.5; `require`, `require-dev`, dan autoload tidak berubah |

## Hasil visual smoke test browser

Browser headless digunakan pada preview terisolasi yang memakai stylesheet, logo PNG, foto, dan sprite SVG dari proyek. Ini menguji layout/common markup, bukan rendering Laravel Blade live.

| Halaman | Desktop 1600 px | Mobile 390 px |
|---|---|---|
| Beranda | Lolos | Lolos |
| Login | Lolos | Lolos |
| Daftar | Lolos | Lolos |

Pengujian mengonfirmasi:

- `body.scrollWidth` dan `documentElement.scrollWidth` sama dengan lebar viewport (tidak ada horizontal overflow) pada desktop dan mobile.
- Seluruh gambar logo/mark/foto yang digunakan pada preview berhasil dimuat.
- Seluruh item navigasi desktop memiliki posisi vertikal satu baris.
- Login: Masuk aktif biru dan Daftar netral.
- Daftar: Daftar aktif biru dan Masuk netral.
- Panel kiri dan kartu autentikasi Login/Daftar mempunyai posisi awal yang sama pada desktop.
- Empat poin panel kiri pada mobile tetap berada pada area latar gelap sehingga kontras tidak hilang.
- Menu mobile dapat dibuka, `aria-expanded` berubah, dan tombol Escape menutup menu kembali.

Responsive width smoke check dilakukan pada 1600, 1440, 1280, 1180, 1100, 1080, 1060, 1050, 1000, 960, 900, 760, 540, dan 390 px. Tidak ditemukan overflow horizontal. Navigasi desktop tetap satu baris pada lebar di atas 1060 px; pada 1060 px ke bawah tombol menu mobile ditampilkan.

Screenshot dan hasil mesin tersedia pada `qa/visual-preview/`.

## Batas pengujian

- Folder `vendor` tidak disertakan dan Composer tidak tersedia pada lingkungan pembuatan. Karena itu, `php artisan test`/PHPUnit dan kompilasi Blade oleh runtime Laravel belum dijalankan di sini.
- Visual smoke test browser menggunakan preview HTML terisolasi dengan CSS dan aset asli, bukan sesi Laravel yang berjalan. Pengujian penuh setelah menyalin ke komputer pengguna tetap disarankan.
- Data akun, transaksi, keputusan, dan audit log prototipe masih mengikuti penyimpanan session sebagaimana versi sebelumnya; bukan backend operasional.

## Cara upgrade

1. Backup folder proyek V1.5.
2. Ekstrak ZIP V1.5.1.
3. Salin isi `ResFund_V1_5_1` ke folder proyek Laravel yang sama dan pilih Replace Files.
4. Pertahankan `.env`, `vendor`, dan `storage`.
5. Jalankan:

```bash
php artisan optimize:clear
php artisan serve
```

Tidak perlu `composer install` ulang karena konfigurasi dependensi Composer tidak berubah.
