# ResFund V1.2.1 — QA Report

## Ruang lingkup perbaikan

1. Memperbaiki tombol **Donasi sekarang**: Donatur yang telah login langsung diarahkan ke checkout; pengunjung diarahkan ke autentikasi dengan konteks proyek dipertahankan.
2. Menambahkan alur checkout, pemilihan metode Payment Gateway, halaman pembayaran simulasi, dan halaman konfirmasi.
3. Menambahkan validasi minimum donasi Rp10.000 dan batas maksimum sesuai sisa target kampanye.
4. Saat pembayaran disimulasikan berhasil, transaksi ditambahkan ke riwayat akun Donatur dan akumulasi dana proyek diperbarui pada session browser.
5. Mengubah halaman transparansi keuangan default agar menampilkan empat proyek, ringkasan agregat, pencarian, filter status, dan tautan detail per proyek.
6. Memisahkan tampilan keuangan menurut status: dana escrow untuk kampanye aktif, realisasi untuk proyek dengan pengeluaran dipublikasikan, serta jumlah refund untuk kampanye gagal.
7. Memperbaiki layout grafik kategori agar persentase donut dan label "Anggaran terealisasi" tidak saling menutupi.
8. Mengganti ilustrasi template pada halaman utama dengan foto bergaya dokumentasi penelitian, menambahkan foto Unsplash untuk proyek air, laboratorium, dan singkong, serta fallback foto lokal.

## Pemeriksaan statis yang berhasil

| Pemeriksaan | Hasil |
|---|---|
| PHP syntax lint | PASS — 57 file PHP |
| JavaScript syntax | PASS — 3 file JavaScript dengan `node --check` |
| CSS parser | PASS — 792 top-level rules, 0 parser errors |
| `composer.json` | PASS — JSON valid |
| Referensi route di Blade/PHP | PASS — semua nama route statis ditemukan pada daftar route yang dideklarasikan |
| Data proyek dan laporan publik | PASS — 4 proyek, 2 aktif, 1 funded, 1 failed |
| Rekonsiliasi ringkasan demo | PASS — escrow Rp83.100.000, realisasi Rp22.150.000, refund Rp21.000.000 |
| Transisi target kampanye | PASS — tambahan dana Rp17.500.000 pada proyek baterai mengubah progres dari 65% menjadi FUNDED/100% dalam data session |
| Aset lokal | PASS — foto fallback, stylesheet, dan script checkout tersedia |

## Feature test yang disediakan

Tersedia **45 test case** pada:

- `tests/Feature/PublicAndAuthenticationTest.php` (19 test)
- `tests/Feature/DonorDashboardTest.php` (20 test)
- `tests/Feature/DonationCheckoutTest.php` (6 test)

Kasus checkout baru mencakup direct-to-checkout bagi Donatur login, akses checkout yang memerlukan Donatur, pembuatan pembayaran simulasi, penambahan donasi ke riwayat, validasi jumlah melebihi sisa target, daftar multi-proyek pada transparansi publik, dan pencegahan laporan pengeluaran palsu pada kampanye aktif.

## Pengujian yang belum dapat dijalankan di lingkungan pembuatan

- `php artisan test` / PHPUnit belum dapat dieksekusi karena folder `vendor/` dan Composer tidak tersedia di lingkungan pembuatan.
- Pengujian browser end-to-end pada server Laravel belum dapat dijalankan karena dependency Laravel belum terpasang di lingkungan pembuatan.
- Foto Unsplash dimuat dari internet pada browser; jika gagal, elemen gambar menggunakan fallback lokal. Foto utama lokal merupakan foto ilustratif fotorealistik, bukan dokumentasi penelitian sebenarnya.

## Batasan perilaku prototype

- Payment Gateway tidak dihubungkan ke penyedia sungguhan. Tombol **Simulasikan pembayaran berhasil** mengubah status sesi menjadi `SETTLED` hanya untuk demonstrasi interaksi.
- Tidak ada uang sungguhan yang dipindahkan, tidak ada instruksi payout, dan tidak ada refund otomatis sungguhan.
- Data transaksi tambahan serta progres proyek yang berubah tersimpan di session browser, bukan database. Menghapus session atau berpindah browser akan menghilangkan data simulasi tersebut.
- Ringkasan empat proyek adalah data demonstrasi. Hanya proyek bioplastik yang memiliki ledger/pengeluaran contoh; proyek aktif tidak diberi pengeluaran fiktif dan tetap ditampilkan sebagai dana yang ditampung di escrow.

## Menjalankan pengujian pada komputer pengembang

Di folder proyek yang sudah memiliki `vendor/` dari V1.2, jalankan:

```bash
php artisan optimize:clear
php artisan test
php artisan serve
```

Tidak ada dependency runtime baru dibanding V1.2. Saat memperbarui folder V1.2 yang sudah terpasang, tidak perlu menjalankan `composer install` lagi selama `vendor/` lama masih utuh.
