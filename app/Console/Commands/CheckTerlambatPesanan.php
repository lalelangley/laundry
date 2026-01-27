<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaksi;
use App\Models\Delivery;
use App\Models\FcmToken;
use App\Services\FcmService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckTerlambatPesanan extends Command
{
    protected $signature = 'pesanan:check-terlambat';
    protected $description = 'Check pesanan selesai_dicuci yang melewati estimasi';

    public function handle()
    {
        Log::info("🔍 Checking terlambat pesanan...");
        
        // Ambil semua pesanan dengan status selesai_dicuci
        $pesananList = Transaksi::with(['pelanggan'])
            ->where('status_transaksi', 'selesai_dicuci')
            ->where('jenis_transaksi', 'online')
            ->whereNotNull('tgl_estimasi')
            ->get();
        
        $totalProcessed = 0;
        
        foreach ($pesananList as $pesanan) {
            // Cek apakah terlambat
            if ($this->isPesananTerlambat($pesanan)) {
                try {
                    DB::beginTransaction();
                    
                    Log::info("⚠️ Pesanan {$pesanan->id_transaksi} TERLAMBAT! Auto-move ke siap_di_antar");
                    
                    // Update status
                    $pesanan->update(['status_transaksi' => 'siap_di_antar']);
                    
                    // Buat delivery record jika belum ada
                    $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                        ->where('jenis', 'antar')
                        ->first();
                    
                    if (!$existingDelivery) {
                        Delivery::create([
                            'id_transaksi' => $pesanan->id_transaksi,
                            'id_driver' => null,
                            'jenis' => 'antar',
                            'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                            'status' => 'pending',
                            'waktu' => now(),
                            'catatan' => 'Auto-generated: Melewati estimasi (scheduler)',
                        ]);
                        Log::info("✅ Created delivery antar record");
                    }
                    
                    // Kirim FCM notification
                    $this->sendTerlambatNotification($pesanan);
                    
                    DB::commit();
                    $totalProcessed++;
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("❌ Error processing pesanan {$pesanan->id_transaksi}: " . $e->getMessage());
                }
            }
        }
        
        Log::info("✅ Checked {$pesananList->count()} pesanan, processed {$totalProcessed} terlambat");
        $this->info("Processed {$totalProcessed} terlambat pesanan");
        
        return 0;
    }
    
    private function isPesananTerlambat($pesanan)
    {
        if (!$pesanan->tgl_estimasi) {
            return false;
        }
        
        $estimasi = Carbon::parse($pesanan->tgl_estimasi);
        $now = Carbon::now();
        
        return $now->greaterThan($estimasi);
    }
    
    private function sendTerlambatNotification($pesanan)
    {
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        
        if (!$idPelanggan) {
            return;
        }
        
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
            ->whereNotNull('token')
            ->pluck('token');
        
        if ($tokens->isEmpty()) {
            Log::warning("⚠️ No FCM tokens for pelanggan {$idPelanggan}");
            return;
        }
        
        $title = '⚠️ Cucian Melewati Estimasi!';
        $body = "Cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati waktu estimasi dan akan segera diantar. Mohon maaf atas keterlambatan.";
        
        foreach ($tokens as $token) {
            try {
                FcmService::send(
                    $token,
                    $title,
                    $body,
                    [
                        'transaksi_id' => (string) $pesanan->id_transaksi,
                        'type' => 'terlambat',
                        'action' => 'open_detail',
                    ]
                );
                Log::info("✅ FCM sent to token: " . substr($token, 0, 20) . "...");
            } catch (\Exception $e) {
                Log::error("❌ FCM Error: " . $e->getMessage());
            }
        }
    }
}