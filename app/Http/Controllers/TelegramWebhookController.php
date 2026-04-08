<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Telegram webhook received', $payload);

        $message = data_get($payload, 'message');
        $chatId = data_get($message, 'chat.id');
        $text = trim((string) data_get($message, 'text', ''));

        if (!$chatId) {
            return response()->json(['ok' => true]);
        }

        if ($text === '/start') {
            TelegramNotificationService::sendMessage($chatId,
                "Halo, selamat datang di Kasmini Laundry.\n\n" .
                "Bot ini digunakan untuk menerima notifikasi transaksi dan informasi layanan laundry.\n\n" .
                "Perintah yang tersedia:\n" .
                "/start - Mulai gunakan bot\n" .
                "/help - Bantuan penggunaan bot\n" .
                "/status - Info bot\n" .
                "/link 08xxxxxxxxxx - Hubungkan bot ke nomor HP pelanggan"
            );

            return response()->json(['ok' => true]);
        }

        if ($text === '/help') {
            TelegramNotificationService::sendMessage($chatId,
                "Bantuan Kasmini Laundry Bot\n\n" .
                "Bot ini akan membantu mengirim ringkasan pembayaran, info layanan, dan notifikasi pesanan selesai.\n" .
                "Gunakan perintah berikut:\n" .
                "/start - Mulai gunakan bot\n" .
                "/help - Lihat bantuan\n" .
                "/status - Cek status bot\n" .
                "/link 08xxxxxxxxxx - Hubungkan akun Telegram ke nomor pelanggan"
            );

            return response()->json(['ok' => true]);
        }

        if ($text === '/status') {
            $pelanggan = Pelanggan::where('telegram_chat_id', (string) $chatId)->first();

            $statusText = $pelanggan
                ? "Kasmini Laundry Bot aktif.\nAkun Telegram ini sudah terhubung ke pelanggan: {$pelanggan->nama_pelanggan} ({$pelanggan->no_hp})."
                : "Kasmini Laundry Bot aktif.\nAkun Telegram ini belum terhubung ke data pelanggan. Gunakan /link 08xxxxxxxxxx.";

            TelegramNotificationService::sendMessage($chatId, $statusText);

            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/link ')) {
            $noHp = trim((string) str_replace('/link', '', $text));

            return $this->linkTelegramToPelanggan($chatId, $noHp, data_get($message, 'from.username'));
        }

        if (preg_match('/^[0-9+\-\s]{10,20}$/', $text)) {
            return $this->linkTelegramToPelanggan($chatId, preg_replace('/\s+/', '', $text), data_get($message, 'from.username'));
        }

        TelegramNotificationService::sendMessage($chatId,
            "Pesan diterima.\n\nGunakan /help untuk melihat perintah yang tersedia."
        );

        return response()->json(['ok' => true]);
    }

    private function linkTelegramToPelanggan(int|string $chatId, string $noHp, ?string $username = null)
    {
        $normalizedNoHp = preg_replace('/[^0-9]/', '', $noHp);
        $pelanggan = Pelanggan::where('no_hp', $normalizedNoHp)->first();

        if (!$pelanggan) {
            TelegramNotificationService::sendMessage(
                $chatId,
                "Nomor HP {$normalizedNoHp} tidak ditemukan di data pelanggan.\nPastikan nomor yang dikirim sama dengan yang terdaftar di Kasmini Laundry."
            );

            return response()->json(['ok' => true]);
        }

        $pelanggan->update([
            'telegram_chat_id' => (string) $chatId,
            'telegram_username' => $username ? '@' . ltrim($username, '@') : null,
        ]);

        TelegramNotificationService::sendMessage(
            $chatId,
            "Berhasil.\nAkun Telegram ini sekarang terhubung ke pelanggan {$pelanggan->nama_pelanggan} ({$pelanggan->no_hp}).\nAnda akan menerima notifikasi saat pesanan selesai."
        );

        return response()->json(['ok' => true]);
    }
}
