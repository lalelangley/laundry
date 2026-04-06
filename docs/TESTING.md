# Dokumen Pengujian Kasmini Laundry

Dokumen ini menjadi bukti prosedur pengujian black box, white box, dan grey box.

## Black Box Testing

Fokus:
- Validasi login berdasarkan role
- Validasi form pelanggan
- Validasi transaksi checkout
- Validasi share email dan Telegram

Contoh skenario:
- Input role login kosong harus gagal
- Input email admin tidak valid harus gagal
- Input nomor HP pelanggan tidak valid harus gagal
- Share Telegram tanpa chat ID valid harus gagal
- Share email dengan konfigurasi benar harus berhasil

## White Box Testing

Fokus:
- Percabangan role login admin/kasir
- Percabangan status bayar lunas/DP/belum lunas
- Percabangan share channel email/telegram
- Error handling untuk mailer dan Telegram API

Bagian kode yang diuji:
- `AuthWebController::processLogin()`
- `TransaksiController::bayarKasir()`
- `TransaksiController::shareKasir()`
- `TelegramWebhookController::handle()`

## Grey Box Testing

Fokus:
- Integrasi frontend checkout dengan backend transaksi
- Integrasi route web, API share transaksi, dan service email/Telegram
- Validasi permission role dengan middleware

## Bukti Otomatisasi

Repo juga menyertakan automated feature test tambahan untuk:
- akses route privacy policy
- respons webhook Telegram
- daftar route dokumentasi dan webhook
