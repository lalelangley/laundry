<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Menerima update dari Telegram bot.
     *
     * Alur singkat:
     * - baca payload masuk
     * - ambil data message, chat id, dan text
     * - jalankan perintah seperti /start, /help, /status, atau link nomor HP
     * - jika payload kosong atau tidak ada chat id, balas sukses tanpa proses lanjut
     *
     * Kaitan dengan unit kompetensi:
     * - Unit 3: menerima request webhook lalu mengeksekusi cabang perintah
     * - Unit 4: menerapkan validasi input minimum dan coding terstruktur
     * - Unit 6: method diberi dokumentasi agar alur bot mudah dipahami
     * - Unit 7: memakai try-catch dan log untuk membantu proses debugging webhook
     */
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            // Telegram mengirim banyak tipe update; project ini fokus ke payload `message`.
            $message = data_get($payload, 'message', []);

            Log::info('Telegram webhook received', $payload);

            $chatId = data_get($message, 'chat.id');
            $text = trim((string) data_get($message, 'text', ''));

            if (!$chatId) {
                return response()->json(['ok' => true]);
            }

            if ($text === '/start') {
                $code = $this->issueTelegramLinkCode($chatId, data_get($message, 'from.username'));

                $sent = TelegramNotificationService::sendMessage($chatId,
                    "Halo, selamat datang di Kasmini Laundry.\n\n" .
                    "Kode Telegram Anda: {$code}\n\n" .
                    "Silakan berikan kode ini ke kasir saat share transaksi. Setelah transaksi berhasil dibagikan, akun Telegram Anda akan otomatis terhubung dan notifikasi berikutnya akan masuk ke chat ini.\n\n" .
                    "Perintah yang tersedia:\n" .
                    "/start - Ambil kode Telegram terbaru\n" .
                    "/help - Bantuan penggunaan bot\n" .
                    "/status - Info bot"
                );

                Log::info('Telegram /start processed', [
                    'chat_id' => (string) $chatId,
                    'code' => $code,
                    'sent' => $sent,
                ]);

                return response()->json(['ok' => true]);
            }

            if ($text === '/help') {
                $sent = TelegramNotificationService::sendMessage($chatId,
                    "Bantuan Kasmini Laundry Bot\n\n" .
                    "Bot ini akan membantu mengirim ringkasan pembayaran, info layanan, dan notifikasi pesanan selesai.\n" .
                    "Kirim /start untuk mendapatkan kode Telegram, lalu masukkan kode itu di halaman share transaksi oleh kasir.\n" .
                    "Gunakan perintah berikut:\n" .
                    "/start - Ambil kode Telegram terbaru\n" .
                    "/help - Lihat bantuan\n" .
                    "/status - Cek status bot"
                );

                Log::info('Telegram /help processed', [
                    'chat_id' => (string) $chatId,
                    'sent' => $sent,
                ]);

                return response()->json(['ok' => true]);
            }

            if ($text === '/status') {
                $pelanggan = Pelanggan::where('telegram_chat_id', (string) $chatId)->first();

                $statusText = $pelanggan
                    ? "Kasmini Laundry Bot aktif.\nAkun Telegram ini sudah terhubung ke pelanggan: {$pelanggan->nama_pelanggan} ({$pelanggan->no_hp})."
                    : "Kasmini Laundry Bot aktif.\nAkun Telegram ini belum terhubung ke data pelanggan. Kirim /start untuk mendapatkan kode Telegram.";

                $sent = TelegramNotificationService::sendMessage($chatId, $statusText);

                Log::info('Telegram /status processed', [
                    'chat_id' => (string) $chatId,
                    'sent' => $sent,
                ]);

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
        } catch (\Throwable $e) {
            Log::error('Telegram webhook failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['ok' => false], 500);
        }
    }

    private function issueTelegramLinkCode(int|string $chatId, ?string $username = null): string
    {
        // Cache file dipakai agar kode link punya masa berlaku tanpa harus membuat tabel tambahan.
        $cacheStore = Cache::store('file');
        $chatKey = $this->telegramChatCacheKey((string) $chatId);
        $existingCode = $cacheStore->get($chatKey);

        if (is_string($existingCode) && $existingCode !== '') {
            $existingPayload = $cacheStore->get($this->telegramCodeCacheKey($existingCode));

            if (is_array($existingPayload) && (string) ($existingPayload['chat_id'] ?? '') === (string) $chatId) {
                $cacheStore->put($this->telegramCodeCacheKey($existingCode), [
                    'chat_id' => (string) $chatId,
                    'username' => $username ? '@' . ltrim($username, '@') : ($existingPayload['username'] ?? null),
                ], now()->addMinutes(30));
                $cacheStore->put($chatKey, $existingCode, now()->addMinutes(30));

                return $existingCode;
            }
        }

        do {
            $code = (string) random_int(100000, 999999);
            $cacheKey = $this->telegramCodeCacheKey($code);
        } while ($cacheStore->has($cacheKey));

        $cacheStore->put($cacheKey, [
            'chat_id' => (string) $chatId,
            'username' => $username ? '@' . ltrim($username, '@') : null,
        ], now()->addMinutes(30));
        $cacheStore->put($chatKey, $code, now()->addMinutes(30));

        return $code;
    }

    private function telegramCodeCacheKey(string $code): string
    {
        return 'telegram_link_code:' . $code;
    }

    private function telegramChatCacheKey(string $chatId): string
    {
        return 'telegram_chat_code:' . $chatId;
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
