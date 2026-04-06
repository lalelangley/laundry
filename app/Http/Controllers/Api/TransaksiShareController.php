<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransaksiShareController extends Controller
{
    public function share(Request $request)
    {
        $validated = $request->validate([
            'id_transaksi' => 'required|exists:transaksi,id_transaksi',
            'channel' => 'required|in:email,telegram',
            'recipient' => 'nullable|string|max:255',
        ]);

        $pelanggan = $request->user();

        if (!$pelanggan || !isset($pelanggan->id_pelanggan)) {
            return response()->json([
                'success' => false,
                'message' => 'User API tidak valid.',
            ], 403);
        }

        $transaksi = Transaksi::with(['detail.layanan', 'detail.jenis', 'pelanggan'])
            ->where('id_transaksi', $validated['id_transaksi'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        if ($validated['channel'] === 'email') {
            return $this->shareToEmail($transaksi, $validated['recipient'] ?? null);
        }

        return $this->shareToTelegram($transaksi, $validated['recipient'] ?? null);
    }

    private function shareToEmail(Transaksi $transaksi, ?string $recipient)
    {
        $recipient = trim((string) ($recipient ?: ($transaksi->pelanggan?->email ?? '')));

        if ($recipient === '') {
            return response()->json([
                'success' => false,
                'message' => 'Email tujuan belum diisi.',
            ], 422);
        }

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Format email tujuan tidak valid.',
            ], 422);
        }

        try {
            Mail::send('emails.transaksi-share', [
                'transaksi' => $transaksi,
            ], function ($mail) use ($recipient, $transaksi) {
                $mail->to($recipient)
                    ->subject('Ringkasan Pembayaran Transaksi #' . $transaksi->id_transaksi);
            });
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim share transaksi via API email', [
                'id_transaksi' => $transaksi->id_transaksi,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ke email.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan pembayaran berhasil dikirim ke email.',
        ]);
    }

    private function shareToTelegram(Transaksi $transaksi, ?string $recipient)
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = trim((string) ($recipient ?? config('services.telegram.default_chat_id')));

        if (empty($botToken)) {
            return response()->json([
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum dikonfigurasi.',
            ], 422);
        }

        if ($chatId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Chat ID Telegram belum diisi.',
            ], 422);
        }

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $this->buildShareMessage($transaksi),
                ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim share transaksi via API Telegram', [
                'id_transaksi' => $transaksi->id_transaksi,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke Telegram gagal.',
            ], 500);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ke Telegram.',
                'telegram_response' => $response->json(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan pembayaran berhasil dikirim ke Telegram.',
        ]);
    }

    private function buildShareMessage(Transaksi $transaksi): string
    {
        $detailLines = $transaksi->detail->map(function ($item) {
            $subtotal = (float) $item->harga * (float) $item->qty;
            $layanan = $item->layanan?->nama_layanan ?? 'Layanan';
            $jenis = $item->jenis?->nama_jenis ? ' (' . $item->jenis->nama_jenis . ')' : '';

            return '- ' . $layanan . $jenis . ': ' .
                $item->qty . ' x Rp' . number_format((float) $item->harga, 0, ',', '.') .
                ' = Rp' . number_format($subtotal, 0, ',', '.');
        })->implode("\n");

        return "Halo {$transaksi->nama_pelanggan},\n\n" .
            "Pembayaran transaksi laundry Anda berhasil dicatat.\n" .
            "ID Transaksi: {$transaksi->id_transaksi}\n" .
            "Tanggal: " . \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d/m/Y H:i') . "\n" .
            "Status Bayar: " . strtoupper((string) $transaksi->status_bayar) . "\n" .
            "Total Harga: Rp" . number_format((float) $transaksi->total_harga, 0, ',', '.') . "\n" .
            "Diskon: Rp" . number_format((float) $transaksi->diskon, 0, ',', '.') . "\n" .
            "Total Bayar: Rp" . number_format((float) $transaksi->total_bayar, 0, ',', '.') . "\n" .
            (!empty($transaksi->tgl_estimasi) ? "Estimasi Selesai: " . \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y H:i') . "\n" : '') .
            (!empty($detailLines) ? "\nDetail Layanan:\n{$detailLines}\n" : '') .
            "\nTerima kasih telah menggunakan layanan kami.";
    }
}
