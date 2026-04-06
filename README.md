# Kasmini Laundry

Aplikasi manajemen laundry berbasis Laravel untuk admin, admin2, kasir, pelanggan, dan integrasi notifikasi.

## Fitur Utama

- Login berbasis role admin dan kasir
- Transaksi laundry offline
- Checkout dan pembayaran
- Share hasil pembayaran via email dan Telegram
- Laporan transaksi dan laporan periodik versi PDF
- Privacy Policy dan webhook untuk bot Telegram

## Bukti Dokumen Rancangan

- ERD: [docs/ERD.md](docs/ERD.md)
- UML Use Case dan Class Diagram: [docs/UML.md](docs/UML.md)
- Mockup tampilan: [docs/MOCKUP.md](docs/MOCKUP.md)
- Dokumen pengujian: [docs/TESTING.md](docs/TESTING.md)

## Bukti Implementasi Penting

- Webhook Telegram: `POST /telegram/webhook`
- Privacy Policy bot: `GET /privacy-policy/telegram-bot`
- API share transaksi: `POST /api/transaksi/share`
- Export PDF laporan: tersedia pada modul laporan admin, admin2, dan kasir

## Menjalankan Project

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```
