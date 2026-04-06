<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            $this->sendMessage($chatId,
                "Halo, selamat datang di Kasmini Laundry.\n\n" .
                "Bot ini digunakan untuk menerima notifikasi transaksi dan informasi layanan laundry.\n\n" .
                "Perintah yang tersedia:\n" .
                "/start - Mulai gunakan bot\n" .
                "/help - Bantuan penggunaan bot\n" .
                "/status - Info bot"
            );

            return response()->json(['ok' => true]);
        }

        if ($text === '/help') {
            $this->sendMessage($chatId,
                "Bantuan Kasmini Laundry Bot\n\n" .
                "Bot ini akan membantu mengirim ringkasan pembayaran dan info layanan.\n" .
                "Gunakan perintah berikut:\n" .
                "/start - Mulai gunakan bot\n" .
                "/help - Lihat bantuan\n" .
                "/status - Cek status bot"
            );

            return response()->json(['ok' => true]);
        }

        if ($text === '/status') {
            $this->sendMessage($chatId, 'Kasmini Laundry Bot aktif dan siap digunakan.');

            return response()->json(['ok' => true]);
        }

        $this->sendMessage($chatId,
            "Pesan diterima.\n\n" .
            "Gunakan /help untuk melihat perintah yang tersedia."
        );

        return response()->json(['ok' => true]);
    }

    private function sendMessage(int|string $chatId, string $text): void
    {
        $botToken = config('services.telegram.bot_token');

        if (empty($botToken)) {
            Log::warning('Telegram bot token kosong saat mencoba reply webhook.');
            return;
        }

        try {
            Http::asForm()
                ->timeout(15)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim balasan webhook Telegram', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
