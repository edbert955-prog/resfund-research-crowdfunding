# ResFund V1.5.3 — QA Report

## Ringkasan perubahan

V1.5.3 adalah patch visual terfokus untuk ikon pencarian pada dua halaman Reviewer:

- Penugasan Saya (`reviewer/assignments/index.blade.php`)
- Riwayat Keputusan (`reviewer/decisions/index.blade.php`)

### Akar masalah

Komponen `<x-icon>` merender elemen `<svg class="rf-icon">`, tetapi CSS sebelumnya memposisikan `.reviewer-search-field > span`. Selector tersebut tidak pernah mengenai SVG. Akibatnya SVG berada pada alur dokumen normal dan tampak sebagai baris tersendiri di atas input.

### Perbaikan

Selector diganti menjadi `.reviewer-search-field > .rf-icon` dengan `position:absolute`, posisi kiri 14 px, pusat vertikal 50%, ukuran 18 × 18 px, dan `pointer-events:none`. Input tetap memiliki padding kiri agar teks tidak bertumpuk dengan ikon.

Cache-busting stylesheet Reviewer dinaikkan ke `v=1.5.3` agar browser memuat CSS terbaru.

## Hasil pengujian

| Pengujian | Hasil |
|---|---:|
| PHP syntax | 95 file, tidak ada syntax error |
| JavaScript syntax | 6 file, lolos `node --check` |
| CSS parser | 1.937 top-level rules/tokens, 0 parser error |
| Visual smoke test publik/login/daftar/KPI Admin | Lulus pada skrip Chromium yang disertakan |
| Ikon pencarian Penugasan Saya | Lulus pada 11 lebar viewport |
| Ikon pencarian Riwayat Keputusan | Lulus pada 11 lebar viewport |
| Business logic smoke test reviewer | 9/9 skenario lulus |
| Composer configuration | Tidak berubah dari V1.5.2 |

Lebar viewport untuk kedua halaman Reviewer: 1600, 1440, 1366, 1280, 1024, 800, 760, 540, 390, 360, dan 320 px.

Setiap variasi memeriksa bahwa:

1. Ikon menggunakan posisi absolut di dalam field.
2. Titik tengah vertikal ikon sejajar dengan titik tengah input (selisih maksimal 1 px).
3. Ikon berada pada area padding kiri input.
4. Ikon tidak keluar dari batas input.
5. Tidak terjadi horizontal overflow pada halaman preview.

Screenshot hasil pengujian tersedia di `qa/visual-preview/` untuk tampilan desktop dan mobile kedua halaman.

## Batasan

Uji browser menggunakan Chromium dengan CSS dan SVG aktual dari proyek dalam preview terisolasi, bukan sesi Laravel live. PHPUnit penuh tidak dijalankan pada lingkungan ini karena dependensi `vendor` tidak tersedia. Perubahan V1.5.3 hanya pada CSS Reviewer dan URL cache stylesheet; kode bisnis serta dependensi Composer tidak diubah.
