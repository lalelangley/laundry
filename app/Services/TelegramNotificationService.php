<?php

namespace App\Services;

use App\Models\Transaksi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * ========================================================
     * SERVICE NOTIFIKASI TELEGRAM
     * Fungsi:
     * - Mengirim pesan ke Telegram Bot API
     * - Membuat format pesan notifikasi estimasi selesai
     * - Membuat format pesan notifikasi siap diambil
     *
     * Konsep:
     * - Class dan static method
     * - Percabangan validasi token/chat id
     * - Penanganan error dengan try-catch
     * - Class-object Http facade dan model Transaksi
     * ========================================================
     */

    public static function sendMessage(string|int $chatId, string $text): bool
    {
        // [METHOD] Ambil token bot dari file konfigurasi services.php.
        $botToken = config('services.telegram.bot_token');

        // [PERCABANGAN] Hentikan proses bila token atau chat id kosong.
        if (empty($botToken) || empty($chatId)) {
            Log::warning('TelegramNotificationService: token atau chat id kosong.', [
                'chat_id' => $chatId,
            ]);

            return false;
        }

        try {
            // [CLASS-OBJECT + METHOD] Http facade digunakan sebagai object client request.
            $response = Http::asForm()
                ->timeout(15)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                ]);

            // [PERCABANGAN] Cek apakah Telegram API menerima request dengan sukses.
            if (!$response->successful()) {
                Log::warning('TelegramNotificationService: Telegram API menolak request.', [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            // [PENANGANAN ERROR] Tangkap semua error agar aplikasi tidak crash.
            Log::error('TelegramNotificationService: gagal kirim Telegram.', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public static function sendEstimasiSelesaiNotification(Transaksi $transaksi, bool $akanDiantar = false): bool
    {
        // [OBJECT] Mengambil chat id dari relasi pelanggan pada object transaksi.
        $chatId = trim((string) ($transaksi->pelanggan?->telegram_chat_id ?? ''));

        // [PERCABANGAN] Jika pelanggan belum menautkan Telegram, notif tidak dikirim.
        if ($chatId === '') {
            Log::info('TelegramNotificationService: pelanggan belum menautkan Telegram.', [
                'id_transaksi' => $transaksi->id_transaksi,
                'id_pelanggan' => $transaksi->id_pelanggan,
            ]);

            return false;
        }

        // [PERCABANGAN] Format estimasi jika kolom tanggal tersedia.
        $estimasi = $transaksi->tgl_estimasi
            ? \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y H:i')
            : '-';

        // [PERCABANGAN] Isi pesan dibedakan jika status akan diantar atau tidak.
        $message = $akanDiantar
            ? "Halo {$transaksi->nama_pelanggan},\n\nPesanan laundry Anda dengan ID #{$transaksi->id_transaksi} sudah selesai dicuci. Karena sudah melewati estimasi {$estimasi}, pesanan akan segera diproses untuk diantar.\n\nTerima kasih telah menggunakan Kasmini Laundry."
            : "Halo {$transaksi->nama_pelanggan},\n\nPesanan laundry Anda dengan ID #{$transaksi->id_transaksi} sudah selesai dicuci sesuai estimasi {$estimasi}.\nStatus saat ini: siap diproses lebih lanjut.\n\nTerima kasih telah menggunakan Kasmini Laundry.";

        // [METHOD] Kirim pesan final menggunakan helper sendMessage().
        return self::sendMessage($chatId, $message);
    }

    public static function sendReadyForPickupNotification(Transaksi $transaksi): bool
    {
        // [OBJECT] Mengambil chat id pelanggan dari relasi transaksi.
        $chatId = trim((string) ($transaksi->pelanggan?->telegram_chat_id ?? ''));

        // [PERCABANGAN] Notifikasi dibatalkan bila chat id belum tersedia.
        if ($chatId === '') {
            Log::info('TelegramNotificationService: pelanggan belum menautkan Telegram untuk notif siap diambil.', [
                'id_transaksi' => $transaksi->id_transaksi,
                'id_pelanggan' => $transaksi->id_pelanggan,
            ]);

            return false;
        }

        // [METHOD] Format tanggal estimasi agar lebih mudah dibaca user.
        $estimasi = $transaksi->tgl_estimasi
            ? \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y H:i')
            : '-';

        // [STRING CONCATENATION] Susun pesan multiline untuk pelanggan.
        $message = "Halo {$transaksi->nama_pelanggan},\n\n" .
            "Pesanan laundry Anda dengan ID #{$transaksi->id_transaksi} sudah siap diambil.\n" .
            "Estimasi selesai: {$estimasi}\n" .
            "Silakan datang ke Kasmini Laundry untuk mengambil pesanan Anda.\n\n" .
            "Terima kasih telah menggunakan Kasmini Laundry.";

        // [METHOD] Delegasikan pengiriman ke method inti.
        return self::sendMessage($chatId, $message);
    }
}
