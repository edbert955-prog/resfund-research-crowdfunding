# Panduan Motion ResFund V1.5.4

Semua motion dibangun dengan Vanilla JavaScript dan CSS lokal. Tidak perlu menjalankan npm, Vite, atau memasang library animasi.

## Efek yang tersedia

1. Page entrance halus saat pindah halaman.
2. Scroll reveal untuk hero, panel, kartu, dan bagian penting.
3. Stagger pada item grid.
4. Count-up untuk statistik dan nominal ringkasan.
5. Progress bar reveal.
6. Header shadow berdasarkan posisi scroll.
7. Press feedback pada tombol dan tautan aksi.
8. Tilt/parallax sangat ringan pada hero foto untuk desktop pointer.
9. Focus cue pada field formulir.
10. Entrance untuk flash message, modal, dan details.
11. `prefers-reduced-motion` untuk menonaktifkan animasi.

## Lokasi file

- `public/assets/js/motion.js`
- `public/assets/css/motion.css`

Kedua file dipanggil dari layout publik, Donatur, Peneliti, Reviewer, dan Administrator. Query cache asset menggunakan `v=1.5.4`.

## Pengaturan intensitas

Durasi/easing dapat disesuaikan melalui custom properties di awal `motion.css`:

- `--rf-motion-fast`
- `--rf-motion-normal`
- `--rf-motion-slow`
- `--rf-motion-ease`

Jangan menghapus aturan `prefers-reduced-motion`; aturan tersebut menjaga aksesibilitas bagi pengguna yang meminta pengurangan gerakan.

## Catatan perilaku

Motion tidak melakukan intercept navigasi, tidak mengubah status transaksi, tidak menunda form submit, dan tidak menulis data ke session/database. Animasi angka hanya berjalan pada nilai KPI yang sudah dirender server dan mengembalikan teks ke nilai final yang sama.
