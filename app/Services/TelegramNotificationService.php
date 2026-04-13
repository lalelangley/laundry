<?php

namespace App\Services;

use App\Models\Transaksi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * Service ini dipakai untuk kirim pesan Telegram.
     *
     * Tugas utamanya:
     * - kirim pesan ke Bot API
     * - buat pesan estimasi selesai
     * - buat pesan siap diambil
     *
     * Kaitan dengan unit kompetensi:
     * - Unit 3: menjalankan integrasi eksternal ke Telegram Bot API
     * - Unit 4: memakai struktur service agar kode lebih rapi dan reusable
     * - Unit 5: menyusun string, percabangan status, dan helper method
     * - Unit 6: menjadi dokumentasi service notifikasi
     * - Unit 7: memakai log dan try-catch untuk melacak error kirim pesan
     */

    public static function sendMessage(string|int $chatId, string $text): bool
    {
        // Ambil token bot dari konfigurasi.
        $botToken = config('services.telegram.bot_token');

        // Stop jika token atau chat id belum ada.
        if (empty($botToken) || empty($chatId)) {
            Log::warning('TelegramNotificationService: token atau chat id kosong.', [
                'chat_id' => $chatId,
            ]);

            return false;
        }

        try {
            // Kirim request ke Telegram.
            $response = Http::asForm()
                ->timeout(15)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                ]);

            // Jika Telegram menolak request, catat log lalu hentikan.
            if (!$response->successful()) {
                Log::warning('TelegramNotificationService: Telegram API menolak request.', [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                    'status' => $response->status(),
                ]);

                return false;
            }

            Log::info('TelegramNotificationService: pesan berhasil dikirim.', [
                'chat_id' => $chatId,
                'telegram_response' => $response->json(),
            ]);

            return true;
        } catch (\Throwable $e) {
            // Tangkap error agar aplikasi tidak ikut gagal.
            Log::error('TelegramNotificationService: gagal kirim Telegram.', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public static function sendEstimasiSelesaiNotification(Transaksi $transaksi, bool $akanDiantar = false): bool
    {
        // Ambil chat id pelanggan dari relasi transaksi.
        $chatId = trim((string) ($transaksi->pelanggan?->telegram_chat_id ?? ''));

        // Jika pelanggan belum link Telegram, notif tidak dikirim.
        if ($chatId === '') {
            Log::info('TelegramNotificationService: pelanggan belum menautkan Telegram.', [
                'id_transaksi' => $transaksi->id_transaksi,
                'id_pelanggan' => $transaksi->id_pelanggan,
            ]);

            return false;
        }

        // Format tanggal estimasi agar mudah dibaca.
        $estimasi = $transaksi->tgl_estimasi
            ? \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y')
            : '-';

        // Isi pesan beda tergantung status pengantaran.
        $message = $akanDiantar
            ? "Halo {$transaksi->nama_pelanggan},\n\nPesanan laundry Anda dengan ID #{$transaksi->id_transaksi} sudah selesai dicuci. Karena sudah melewati estimasi {$estimasi}, pesanan akan segera diproses untuk diantar.\n\nTerima kasih telah menggunakan Kasmini Laundry."
            : "Halo {$transaksi->nama_pelanggan},\n\nPesanan laundry Anda dengan ID #{$transaksi->id_transaksi} sudah selesai dicuci sesuai estimasi {$estimasi}.\nStatus saat ini: siap diproses lebih lanjut.\n\nTerima kasih telah menggunakan Kasmini Laundry.";

        // Kirim pesan final.
        return self::sendMessage($chatId, $message);
    }

    public static function sendReadyForPickupNotification(Transaksi $transaksi): bool
    {
        // Ambil chat id pelanggan dari relasi transaksi.
        $chatId = trim((string) ($transaksi->pelanggan?->telegram_chat_id ?? ''));

        // Batalkan jika chat id belum tersedia.
        if ($chatId === '') {
            Log::info('TelegramNotificationService: pelanggan belum menautkan Telegram untuk notif siap diambil.', [
                'id_transaksi' => $transaksi->id_transaksi,
                'id_pelanggan' => $transaksi->id_pelanggan,
            ]);

            return false;
        }

        // Format tanggal estimasi agar mudah dibaca.
        $estimasi = $transaksi->tgl_estimasi
            ? \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y')
            : '-';

        $totalBayar = (float) ($transaksi->total_bayar ?? 0);
        $statusBayar = strtolower((string) ($transaksi->status_bayar ?? ''));
        // Baris status pembayaran dipisah agar isi pesan bisa berubah sesuai kondisi transaksi.
        $paymentLine = $statusBayar === 'lunas' || $totalBayar > 0
            ? "Pembayaran Anda sudah tercatat. Pesanan sudah bisa diambil di outlet.\n"
            : "Pesanan hanya bisa diambil setelah pembayaran minimal DP tercatat.\n";

        if ($statusBayar === 'dp' || ($totalBayar > 0 && $statusBayar !== 'lunas')) {
            $paymentLine .= "Jika masih ada sisa pembayaran, silakan dilunasi saat pengambilan.\n";
        }

        // Susun isi pesan untuk pelanggan.
        $message = "Halo {$transaksi->nama_pelanggan},\n\n" .
            "Pesanan laundry Anda dengan ID #{$transaksi->id_transaksi} sudah siap diambil.\n" .
            "Estimasi selesai: {$estimasi}\n" .
            $paymentLine . "\n" .
            "Silakan datang ke Kasmini Laundry untuk mengambil pesanan Anda.\n\n" .
            "Terima kasih telah menggunakan Kasmini Laundry.";

        // Kirim pesan.
        return self::sendMessage($chatId, $message);
    }
}
