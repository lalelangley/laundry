# Mockup Tampilan Aplikasi

Dokumen ini menjadi bukti mockup/wireframe tampilan utama aplikasi Kasmini Laundry.

## 1. Halaman Login

Komponen utama:
- Pilihan role pengguna
- Form login admin berbasis email
- Form login kasir berbasis nomor HP
- Tombol login

## 2. Halaman Checkout Transaksi Kasir

Komponen utama:
- Informasi pelanggan
- Detail order/layanan
- Estimasi selesai
- Metode bayar
- Input diskon nominal
- Popup pembayaran
- Popup share email dan Telegram

## 3. Halaman Laporan

Komponen utama:
- Filter tanggal
- Ringkasan data
- Tombol export PDF / Excel / CSV
- Tabel paginated

## 4. Halaman Riwayat Transaksi

Komponen utama:
- List transaksi
- Status pembayaran
- Detail pelanggan
- Tombol detail/print/share

## Catatan Mockup

- Desain final telah diimplementasikan dalam Blade view aplikasi.
- Halaman yang dapat dijadikan acuan implementasi:
  - `resources/views/auth/login.blade.php`
  - `resources/views/kasir/transaksi/checkout.blade.php`
  - `resources/views/kasir/riwayat/index.blade.php`
  - `resources/views/laporan/*`
