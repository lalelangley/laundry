<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\BiayaTambahan; // ✅ WAJIB INI
use Illuminate\Support\Facades\DB;
use App\Models\Pembayaran;
use App\Models\Delivery;
use App\Models\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\FcmToken;
use App\Services\FcmService;



class PesananOnlineController extends Controller
{

    public function driverArriveAtLaundry($id)
    {
        DB::transaction(function () use ($id) {

            $pesanan = Transaksi::where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'pick_up')
                ->findOrFail($id);

            // update transaksi ke ANTRIAN
            $pesanan->update([
                'status_transaksi' => 'antrian'
            ]);

            // update delivery pickup ke arrived_at_laundry
            Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->update([
                    'status' => 'arrived_at_laundry'
                ]);
        });

        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Driver sampai laundry, pesanan masuk antrian');
    }

    public function driverArriveAtLaundryKasir($id)
    {
        DB::transaction(function () use ($id) {

            $pesanan = Transaksi::where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'pick_up')
                ->findOrFail($id);

            // update transaksi ke ANTRIAN
            $pesanan->update([
                'status_transaksi' => 'antrian'
            ]);

            // update delivery pickup ke arrived_at_laundry
            Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->update([
                    'status' => 'arrived_at_laundry'
                ]);
        });

        return redirect()
            ->route('kasir.pesanan.online.detail', $id)
            ->with('success', 'Driver sampai laundry, pesanan masuk antrian');
    }

    public function driverArriveAtLaundryAdmin2($id)
    {
        DB::transaction(function () use ($id) {

            $pesanan = Transaksi::where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'pick_up')
                ->findOrFail($id);

            // update transaksi ke ANTRIAN
            $pesanan->update([
                'status_transaksi' => 'antrian'
            ]);

            // update delivery pickup ke arrived_at_laundry
            Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->update([
                    'status' => 'arrived_at_laundry'
                ]);
        });

        return redirect()
            ->route('admin2.pesanan.online.detail', $id)
            ->with('success', 'Driver sampai laundry, pesanan masuk antrian');
    }

   // ==================== KASIR ====================
// Di method index() - ADMIN
public function indexKasir(Request $request)
{
    // Auto check dulu
    $this->autoCheckTerlambat();
    $tab = $request->get('tab', 'pickup');
    $statusMap = $this->statusMap();

    $pickupNeedDriver = Transaksi::where('jenis_transaksi', 'online')
        ->where('status_transaksi', 'pick_up')
        ->where(function($q) {
            $q->whereDoesntHave('delivery', function($sub) {
                $sub->where('jenis', 'pickup');
            })
            ->orWhereHas('delivery', function($sub) {
                $sub->where('jenis', 'pickup')
                    ->whereNull('id_driver');
            });
        })
        ->count();

    if ($tab === 'pickup') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'pick_up')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'antrian') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where(function ($q) {
                $q->where('status_transaksi', 'antrian')
                    ->orWhere(function ($sub) {
                        $sub->where('status_transaksi', 'pick_up')
                            ->whereHas('delivery', function ($delivery) {
                                $delivery->where('jenis', 'pickup')
                                    ->where('status', 'arrived_at_laundry');
                            });
                    });
            })
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'selesai_dicuci') { // ✅ TAMBAHKAN INI
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'selesai_dicuci')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'siap_di_antar') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'siap_di_antar')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } else {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }
    }

    return view('kasir.pesanan_online.index', compact('pesanan', 'tab', 'pickupNeedDriver'));
}
    
       public function detailKasir($id)
{
    $pesanan = Transaksi::with([
        'pelanggan',
        'detail_transaksi.layanan',
        'detail_transaksi.jenis',
        'detail_transaksi.parfum',
        'detail_transaksi.satuan',
        'metodeBayar',
        'pembayaran',
        'biayaTambahan' // ✅ TAMBAHKAN RELASI INI
    ])->findOrFail($id);
    
    $biayaTambahan = BiayaTambahan::all();

    // ✅ LOAD DELIVERY PICKUP & ANTAR SECARA SPESIFIK
    $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'pickup')
        ->with('driver')
        ->latest('id_delivery')
        ->first();
    
    $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'antar')
        ->with('driver')
        ->latest('id_delivery')
        ->first();
    
    return view('kasir.pesanan_online.detail', compact('pesanan', 'biayaTambahan', 'deliveryPickup', 'deliveryAntar'));
}
    
    public function terimaKasir($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'dikonfirmasi']);
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'dikonfirmasi'])
            ->with('success', 'Pesanan berhasil diterima');
    }
    
    public function tolakKasir($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'ditolak']);
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'ditolak'])
            ->with('success', 'Pesanan ditolak');
    }
    
   public function prosesKasir($id)
{
    try {
        DB::beginTransaction();
        
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        if ($this->isPesananTerlambat($pesanan)) {
            $pesanan->update(['status_transaksi' => 'siap_di_antar']);
            
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
                    'catatan' => 'Auto-generated: Pesanan melewati estimasi saat proses',
                ]);
            }
            
            DB::commit();
            
            return redirect()
                ->route('kasir.pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk delivery.');
        }
        
        $pesanan->update(['status_transaksi' => 'proses']);
        
        DB::commit();
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error prosesKasir: " . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
    }
}

     public function selesaiDiCuciKasir($id)
    {
        try {
            DB::beginTransaction();
            
            $pesanan = Transaksi::with(['pelanggan'])
                ->where('jenis_transaksi', 'online')
                ->findOrFail($id);
            
            // ✅ CEK APAKAH PESANAN MELEWATI ESTIMASI
            $statusBaru = 'selesai_dicuci';
            $needDriverSelection = false;
            
            if ($this->isPesananTerlambat($pesanan)) {
                $statusBaru = 'siap_di_antar';
                $needDriverSelection = true;
                
                Log::info("⚠️ Pesanan {$pesanan->id_transaksi} melewati estimasi, otomatis set ke siap_di_antar");
            }
            
            $pesanan->update(['status_transaksi' => $statusBaru]);
            
            // ✅ KIRIM FCM NOTIFICATION
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                
                if ($tokens->isNotEmpty()) {
                    $title = $needDriverSelection 
                        ? '⚠️ Cucian Selesai - Akan Segera Diantar!'
                        : '🎉 Cucian Selesai Dicuci!';
                    
                    $body = $needDriverSelection
                        ? "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan akan segera diantar karena melewati waktu estimasi."
                        : "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan siap untuk diproses lebih lanjut.";
                    
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token,
                            $title,
                            $body,
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi,
                                'type' => $needDriverSelection ? 'siap_di_antar' : 'selesai_dicuci',
                                'action' => 'open_detail',
                            ]
                        );
                    }
                }
            }
            
            DB::commit();
            
            // ✅ REDIRECT KE PILIH DRIVER JIKA TERLAMBAT
            if ($needDriverSelection) {
                return redirect()
                    ->route('kasir.pesanan.online.list-driver', $id)
                    ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk pengiriman.');
            }
            
            return redirect()->route('kasir.pesanan.online.index', ['tab' => 'selesai_dicuci'])
                ->with('success', 'Pesanan selesai dicuci & notifikasi terkirim!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error selesaiDiCuciKasir: " . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal mengupdate status: ' . $e->getMessage());
        }
    }


    
    public function siapDiAmbilKasir($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Pesanan siap diambil');
    }
    
    public function selesaiKasir($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai']);
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'selesai'])
            ->with('success', 'Pesanan selesai');
    }
    
    public function bayarKasir(Request $request, $id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update([
            'status_bayar' => 'lunas',
            'total_bayar' => $request->jumlah_bayar
        ]);
        
        return redirect()->route('kasir.pesanan.online.detail', $id)
            ->with('success', 'Pembayaran berhasil');
    }
    
    public function destroyKasir($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->delete();
        
        return redirect()->route('kasir.pesanan.online.index')
            ->with('success', 'Pesanan berhasil dihapus');
    }
    
    // ==================== ADMIN ====================
public function index(Request $request)
{ 
     $this->autoCheckTerlambat();
    $tab = $request->get('tab', 'pickup');
    $statusMap = $this->statusMap();

    $pickupNeedDriver = Transaksi::where('jenis_transaksi', 'online')
        ->where('status_transaksi', 'pick_up')
        ->where(function($q) {
            $q->whereDoesntHave('delivery', function($sub) {
                $sub->where('jenis', 'pickup');
            })
            ->orWhereHas('delivery', function($sub) {
                $sub->where('jenis', 'pickup')
                    ->whereNull('id_driver');
            });
        })
        ->count();

    if ($tab === 'pickup') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'pick_up')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'antrian') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where(function ($q) {
                $q->where('status_transaksi', 'antrian')
                    ->orWhere(function ($sub) {
                        $sub->where('status_transaksi', 'pick_up')
                            ->whereHas('delivery', function ($delivery) {
                                $delivery->where('jenis', 'pickup')
                                    ->where('status', 'arrived_at_laundry');
                            });
                    });
            })
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'selesai_dicuci') { // ✅ TAMBAHKAN INI
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'selesai_dicuci')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'siap_di_antar') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'siap_di_antar')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } else {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }
    }

    return view('pesanan_online.index', compact('pesanan', 'tab', 'pickupNeedDriver'));
}

    public function detail($id)
{
    $pesanan = Transaksi::with([
        'pelanggan',
        'detail_transaksi.layanan',
        'detail_transaksi.jenis',
        'detail_transaksi.parfum',
        'detail_transaksi.satuan',
        'metodeBayar',
        'pembayaran',
        'biayaTambahan'
    ])->findOrFail($id);
    
    $biayaTambahan = BiayaTambahan::all();

    // ✅ LOAD DELIVERY PICKUP & ANTAR
    $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'pickup')
        ->with('driver')
        ->latest('id_delivery')
        ->first();
    
    $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'antar')
        ->with('driver')
        ->latest('id_delivery')
        ->first();
    
    return view('pesanan_online.detail', compact('pesanan', 'biayaTambahan', 'deliveryPickup', 'deliveryAntar'));
}
    
    public function terima($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'dikonfirmasi']);
        
        return redirect()->route('pesanan.online.index', ['tab' => 'dikonfirmasi'])
            ->with('success', 'Pesanan berhasil diterima');
    }
    
    public function tolak($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'ditolak']);
        
        return redirect()->route('pesanan.online.index', ['tab' => 'ditolak'])
            ->with('success', 'Pesanan ditolak');
    }
    
    public function proses($id)
{
    try {
        DB::beginTransaction();
        
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        // ✅ CEK DULU sebelum update
        if ($this->isPesananTerlambat($pesanan)) {
            $pesanan->update(['status_transaksi' => 'siap_di_antar']);
            
            // ✅ Buat delivery record otomatis
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
                    'catatan' => 'Auto-generated: Pesanan melewati estimasi saat proses',
                ]);
            }
            
            DB::commit();
            
            return redirect()
                ->route('pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Langsung masuk siap diantar. Silakan pilih driver.');
        }
        
        // ✅ Kalau tidak terlambat, baru update ke proses
        $pesanan->update(['status_transaksi' => 'proses']);
        
        DB::commit();
        
        return redirect()->route('pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error proses: " . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
    }
}
public function selesaiDiCuci($id)
{
    try {
        DB::beginTransaction();
        
        $pesanan = Transaksi::with(['pelanggan'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        // ✅ CEK APAKAH PESANAN MELEWATI ESTIMASI
        $statusBaru = 'selesai_dicuci';
        $needDriverSelection = false;
        
        if ($this->isPesananTerlambat($pesanan)) {
            $statusBaru = 'siap_di_antar';
            $needDriverSelection = true;
            
            Log::info("⚠️ Pesanan {$pesanan->id_transaksi} melewati estimasi, otomatis set ke siap_di_antar");
        }
        
        // Update status
        $pesanan->update(['status_transaksi' => $statusBaru]);
        
        // ✅ KIRIM FCM NOTIFICATION
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        
        if ($idPelanggan) {
            // ✅ FIX: Ambil dari tabel fcm_tokens
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
                ->pluck('token')
                ->filter(); // Remove null/empty tokens
            
            Log::info("📍 Found " . $tokens->count() . " FCM tokens for pelanggan {$idPelanggan}");
            
            if ($tokens->isNotEmpty()) {
                $title = $needDriverSelection 
                    ? '⚠️ Cucian Selesai - Akan Segera Diantar!'
                    : '🎉 Cucian Selesai Dicuci!';
                
                $body = $needDriverSelection
                    ? "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan akan segera diantar karena melewati waktu estimasi."
                    : "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan siap untuk diproses lebih lanjut.";
                
                foreach ($tokens as $token) {
                    try {
                        // ✅ FIX: Gunakan 'transaksi_id' bukan 'order_id'
                        $result = FcmService::send(
                            $token,
                            $title,
                            $body,
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi, // ✅ FIX!
                                'type' => $needDriverSelection ? 'siap_di_antar' : 'selesai_dicuci',
                                'action' => 'open_detail', // ✅ TAMBAH INI
                            ]
                        );
                        
                        if ($result) {
                            Log::info("✅ FCM sent successfully to token: " . substr($token, 0, 20) . "...");
                        } else {
                            Log::warning("⚠️ FCM returned false for token: " . substr($token, 0, 20) . "...");
                        }
                        
                    } catch (\Exception $e) {
                        Log::error("❌ FCM Send Error: " . $e->getMessage());
                    }
                }
                
                Log::info("✅ FCM sent: Selesai Dicuci - Order {$pesanan->id_transaksi}");
            } else {
                Log::warning("⚠️ No FCM tokens found for pelanggan ID: {$idPelanggan}");
            }
        } else {
            Log::warning("⚠️ Pesanan {$pesanan->id_transaksi} tidak memiliki id_pelanggan");
        }
        
        DB::commit();
        
        // ✅ REDIRECT KE PILIH DRIVER JIKA TERLAMBAT
        if ($needDriverSelection) {
            return redirect()
                ->route('pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk pengiriman.');
        }
        
        return redirect()->route('pesanan.online.index', ['tab' => 'selesai_dicuci'])
            ->with('success', 'Pesanan selesai dicuci & notifikasi terkirim!');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error selesaiDiCuci: " . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Gagal mengupdate status: ' . $e->getMessage());
    }
}

    
    public function siapDiAmbil($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
        
        return redirect()->route('pesanan.online.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Pesanan siap diambil');
    }
    
    public function selesai($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai']);
        
        return redirect()->route('pesanan.online.index', ['tab' => 'selesai'])
            ->with('success', 'Pesanan selesai');
    }
    
    public function bayar(Request $request, $id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update([
            'status_bayar' => 'lunas',
            'total_bayar' => $request->jumlah_bayar
        ]);
        
        return redirect()->route('pesanan.online.detail', $id)
            ->with('success', 'Pembayaran berhasil');
    }
    
    /**
 * ===============================================
 * DELETE PESANAN ONLINE
 * ===============================================
 * Menghapus pesanan beserta semua relasinya
 */
public function destroy($id)
{
    try {
        // Cari transaksi online
        $pesanan = Transaksi::where('jenis_transaksi', 'online')
                           ->findOrFail($id);
        
        // Simpan info untuk log
        $customerName = $pesanan->nama_pelanggan ?? 'Guest';
        $orderId = $pesanan->id_transaksi;
        
        // Hapus relasi terkait (untuk menghindari foreign key constraint)
        // Jika sudah ada ON DELETE CASCADE di database, bagian ini bisa di-skip
        if ($pesanan->detail_transaksi()->count() > 0) {
            $pesanan->detail_transaksi()->delete();
        }
        
        if ($pesanan->delivery()->count() > 0) {
            $pesanan->delivery()->delete();
        }
        
        if ($pesanan->pembayaran()->count() > 0) {
            $pesanan->pembayaran()->delete();
        }
        
        // Hapus transaksi utama
        $pesanan->delete();
        
        // Log aktivitas
        \Log::info("Pesanan ORDER/{$orderId} ({$customerName}) berhasil dihapus oleh Admin: " . auth()->guard('admin')->user()->nama);
        
        return redirect()->route('pesanan.online.index')
            ->with('success', 'Pesanan berhasil dihapus!');
            
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::warning("Attempt to delete non-existent order: {$id}");
        
        return redirect()->route('pesanan.online.index')
            ->with('error', 'Pesanan tidak ditemukan!');
            
    } catch (\Illuminate\Database\QueryException $e) {
        // Error database (foreign key constraint, dll)
        \Log::error("Database error while deleting order {$id}: " . $e->getMessage());
        
        return redirect()->route('pesanan.online.index')
            ->with('error', 'Gagal menghapus pesanan. Data masih terkait dengan data lain.');
            
    } catch (\Exception $e) {
        // Error umum lainnya
        \Log::error("Error deleting order {$id}: " . $e->getMessage());
        
        return redirect()->route('pesanan.online.index')
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    // ==================== ADMIN2 ====================

    // Di method index() - ADMIN
public function indexAdmin2(Request $request)
{
     $this->autoCheckTerlambat();
    $tab = $request->get('tab', 'pickup');
    $statusMap = $this->statusMap();

    $pickupNeedDriver = Transaksi::where('jenis_transaksi', 'online')
        ->where('status_transaksi', 'pick_up')
        ->where(function($q) {
            $q->whereDoesntHave('delivery', function($sub) {
                $sub->where('jenis', 'pickup');
            })
            ->orWhereHas('delivery', function($sub) {
                $sub->where('jenis', 'pickup')
                    ->whereNull('id_driver');
            });
        })
        ->count();

    if ($tab === 'pickup') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'pick_up')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'antrian') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where(function ($q) {
                $q->where('status_transaksi', 'antrian')
                    ->orWhere(function ($sub) {
                        $sub->where('status_transaksi', 'pick_up')
                            ->whereHas('delivery', function ($delivery) {
                                $delivery->where('jenis', 'pickup')
                                    ->where('status', 'arrived_at_laundry');
                            });
                    });
            })
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'selesai_dicuci') { // ✅ TAMBAHKAN INI
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'selesai_dicuci')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } elseif ($tab === 'siap_di_antar') {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'siap_di_antar')
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }

    } else {
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
            ->orderByDesc('id_transaksi')
            ->get();

        foreach($pesanan as $p) {
            $p->setRelation('delivery', 
                \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                    ->with('driver')
                    ->orderByDesc('id_delivery')
                    ->get()
            );
        }
    }

    return view('admin2.pesanan_online.index', compact('pesanan', 'tab', 'pickupNeedDriver'));
}
    
    public function detailAdmin2($id)
    {
        $pesanan = Transaksi::with([
            'pelanggan',
            'detail_transaksi.layanan',
            'detail_transaksi.jenis',
            'detail_transaksi.parfum',
            'detail_transaksi.satuan',
            'metodeBayar',
            'pembayaran',
            'biayaTambahan'
        ])->findOrFail($id);
        
        $biayaTambahan = BiayaTambahan::all();

        // ✅ LOAD DELIVERY PICKUP & ANTAR
        $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->with('driver')
            ->latest('id_delivery')
            ->first();
        
        $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'antar')
            ->with('driver')
            ->latest('id_delivery')
            ->first();
        
        return view('admin2.pesanan_online.detail', compact('pesanan', 'biayaTambahan', 'deliveryPickup', 'deliveryAntar'));
    }
    
    public function terimaAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'dikonfirmasi']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'dikonfirmasi'])
            ->with('success', 'Pesanan berhasil diterima');
    }
    
    public function tolakAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'ditolak']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'ditolak'])
            ->with('success', 'Pesanan ditolak');
    }
    
    public function prosesAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'proses']);
        
        // ✅ AUTO DELIVERY JIKA TERLAMBAT
        if ($this->isPesananTerlambat($pesanan)) {
            $pesanan->update(['status_transaksi' => 'siap_di_antar']);
            
            return redirect()
                ->route('admin2.pesanan.online.list-driver', $id)
                ->with('info', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk delivery.');
        }
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
    }

   public function selesaiDiCuciAdmin2($id)
    {
        try {
            DB::beginTransaction();
            
            $pesanan = Transaksi::with(['pelanggan'])
                ->where('jenis_transaksi', 'online')
                ->findOrFail($id);
            
            // ✅ CEK APAKAH PESANAN MELEWATI ESTIMASI
            $statusBaru = 'selesai_dicuci';
            $needDriverSelection = false;
            
            if ($this->isPesananTerlambat($pesanan)) {
                $statusBaru = 'siap_di_antar';
                $needDriverSelection = true;
                
                Log::info("⚠️ Pesanan {$pesanan->id_transaksi} melewati estimasi, otomatis set ke siap_di_antar");
            }
            
            $pesanan->update(['status_transaksi' => $statusBaru]);
            
            // ✅ KIRIM FCM NOTIFICATION
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                
                if ($tokens->isNotEmpty()) {
                    $title = $needDriverSelection 
                        ? '⚠️ Cucian Selesai - Akan Segera Diantar!'
                        : '🎉 Cucian Selesai Dicuci!';
                    
                    $body = $needDriverSelection
                        ? "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan akan segera diantar karena melewati waktu estimasi."
                        : "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan siap untuk diproses lebih lanjut.";
                    
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token,
                            $title,
                            $body,
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi,
                                'type' => $needDriverSelection ? 'siap_di_antar' : 'selesai_dicuci',
                                'action' => 'open_detail',
                            ]
                        );
                    }
                }
            }
            
            DB::commit();
            
            // ✅ REDIRECT KE PILIH DRIVER JIKA TERLAMBAT
            if ($needDriverSelection) {
                return redirect()
                    ->route('admin2.pesanan.online.list-driver', $id)
                    ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk pengiriman.');
            }
            
            return redirect()->route('admin2.pesanan.online.index', ['tab' => 'selesai_dicuci'])
                ->with('success', 'Pesanan selesai dicuci & notifikasi terkirim!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error selesaiDiCuciAdmin2: " . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal mengupdate status: ' . $e->getMessage());
        }
    }

    /**
     * Helper function untuk cek apakah pesanan terlambat
     */
        private function isPesananTerlambat($pesanan)
{
    if (!$pesanan->tgl_estimasi) {
        return false;
    }
    
    // Parse tanggal estimasi dan set ke akhir hari (23:59:59)
    $estimasi = \Carbon\Carbon::parse($pesanan->tgl_estimasi)->endOfDay();
    $now = \Carbon\Carbon::now();
    
    // Terlambat jika waktu sekarang sudah melewati akhir hari estimasi
    $isTerlambat = $now->greaterThan($estimasi);
    
    // ✅ LOG untuk debugging
    if ($isTerlambat) {
        Log::info("⚠️ Pesanan {$pesanan->id_transaksi} TERLAMBAT - Estimasi: {$estimasi}, Sekarang: {$now}");
    }
    
    return $isTerlambat;
}

    public function siapDiAmbilAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Pesanan siap diambil');
    }
    
    public function selesaiAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'selesai'])
            ->with('success', 'Pesanan selesai');
    }
    
        public function siapDiAntar($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_antar']);
        
        return redirect()->route('pesanan.online.index', ['tab' => 'siap_di_antar'])
            ->with('success', 'Pesanan siap diantar');
    }

    public function siapDiAntarKasir($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_antar']);
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'siap_di_antar'])
            ->with('success', 'Pesanan siap diantar');
    }

    public function siapDiAntarAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_antar']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'siap_di_antar'])
            ->with('success', 'Pesanan siap diantar');
    }
    public function bayarAdmin2(Request $request, $id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update([
            'status_bayar' => 'lunas',
            'total_bayar' => $request->jumlah_bayar
        ]);
        
        return redirect()->route('admin2.pesanan.online.detail', $id)
            ->with('success', 'Pembayaran berhasil');
    }
    
    public function destroyAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->delete();
        
        return redirect()->route('admin2.pesanan.online.index')
            ->with('success', 'Pesanan berhasil dihapus');
    }
    
    // ==================== DELIVERY ONLINE ====================

    public function listDriver($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        $isPickup = false;
        $pickupDelivery = null;
        
        if ($pesanan->status_transaksi === 'pick_up') {
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->where('status', 'pending')
                ->first();
            
            if ($pickupDelivery) {
                $isPickup = true;
            }
        }
        
        $drivers = Driver::where('status', 'aktif')->get();
        
        return view('pesanan_online.listonlinedriver', compact('pesanan', 'drivers', 'isPickup', 'pickupDelivery'));
    }

    public function listDriverKasir($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        $isPickup = false;
        $pickupDelivery = null;
        
        if ($pesanan->status_transaksi === 'pick_up') {
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->where('status', 'pending')
                ->first();
            
            if ($pickupDelivery) {
                $isPickup = true;
            }
        }
        
        $drivers = Driver::where('status', 'aktif')->get();
        
        return view('kasir.pesanan_online.listonlinedriver', compact('pesanan', 'drivers', 'isPickup', 'pickupDelivery'));
    }

    public function listDriverAdmin2($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        $isPickup = false;
        $pickupDelivery = null;
        
        if ($pesanan->status_transaksi === 'pick_up') {
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->where('status', 'pending')
                ->first();
            
            if ($pickupDelivery) {
                $isPickup = true;
            }
        }
        
        $drivers = Driver::where('status', 'aktif')->get();
        
        return view('admin2.pesanan_online.listonlinedriver', compact('pesanan', 'drivers', 'isPickup', 'pickupDelivery'));
    }

    public function assignDriverPickup(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->where('status', 'pending')
                ->whereNull('id_driver')
                ->first();
            
            if (!$pickupDelivery) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->with('error', 'Delivery pickup tidak ditemukan, sudah diassign, atau tidak dalam status pending!');
            }

            $pickupDelivery->update([
                'id_driver' => $request->id_driver,
                'status'    => 'accepted',
                'waktu'     => now(),
                'catatan'   => $request->catatan_driver,
            ]);

            DB::commit();
            
            return redirect()
                ->route('pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditentukan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver pickup: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
        }
    }

    public function assignDriverPickupKasir(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->where('status', 'pending')
                ->whereNull('id_driver')
                ->first();
            
            if (!$pickupDelivery) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->with('error', 'Delivery pickup tidak ditemukan, sudah diassign, atau tidak dalam status pending!');
            }

            $pickupDelivery->update([
                'id_driver' => $request->id_driver,
                'status'    => 'accepted',
                'waktu'     => now(),
                'catatan'   => $request->catatan_driver,
            ]);

            DB::commit();
            
            return redirect()
                ->route('kasir.pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditentukan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver pickup: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
        }
    }

    public function assignDriverPickupAdmin2(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->where('status', 'pending')
                ->whereNull('id_driver')
                ->first();
            
            if (!$pickupDelivery) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->with('error', 'Delivery pickup tidak ditemukan, sudah diassign, atau tidak dalam status pending!');
            }

            $pickupDelivery->update([
                'id_driver' => $request->id_driver,
                'status'    => 'accepted',
                'waktu'     => now(),
                'catatan'   => $request->catatan_driver,
            ]);

            DB::commit();
            
            return redirect()
                ->route('admin2.pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditentukan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver pickup: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
        }
    }

    public function assignDriverAntar(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pesanan->update([
                'status_transaksi' => 'siap_di_antar'
            ]);

            Delivery::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_driver' => $request->id_driver,
                'jenis' => 'antar',
                'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                'status' => 'accepted',
                'waktu' => now(),
                'catatan' => $request->catatan_driver,
            ]);

            DB::commit();

            return redirect()
                ->route('pesanan.online.detail', $id)
                ->with('success', 'Driver delivery berhasil ditentukan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver antar: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
        }
    }

     public function assignDriverAntarAdmin2(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pesanan->update([
                'status_transaksi' => 'siap_di_antar'
            ]);

            Delivery::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_driver' => $request->id_driver,
                'jenis' => 'antar',
                'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                'status' => 'accepted',
                'waktu' => now(),
                'catatan' => $request->catatan_driver,
            ]);

            DB::commit();

            return redirect()
                ->route('admin2.pesanan.online.detail', $id)
                ->with('success', 'Driver delivery berhasil ditentukan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver antar: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
        }
    }

     public function assignDriverAntarKasir(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pesanan->update([
                'status_transaksi' => 'siap_di_antar'
            ]);

            Delivery::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_driver' => $request->id_driver,
                'jenis' => 'antar',
                'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                'status' => 'accepted',
                'waktu' => now(),
                'catatan' => $request->catatan_driver,
            ]);

            DB::commit();

            return redirect()
                ->route('kasir.pesanan.online.detail', $id)
                ->with('success', 'Driver delivery berhasil ditentukan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver antar: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
        }
    }

    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->where('status', 'pending')
            ->first();
        
        if ($pickupDelivery && $pesanan->status_transaksi === 'pick_up') {
            $pickupDelivery->update([
                'id_driver' => $request->id_driver,
                'status'    => 'accepted',
                'waktu'     => now(),
            ]);
            
            return redirect()
                ->route('pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditentukan!');
        } 
        else {
            $pesanan->update([
                'status_transaksi' => 'siap_di_antar'
            ]);

            Delivery::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_driver' => $request->id_driver,
                'jenis' => 'antar',
                'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                'status' => 'pending',
                'waktu' => now()
            ]);

            return redirect()
                ->route('pesanan.online.detail', $id)
                ->with('success', 'Driver berhasil ditentukan!');
        }
    }

    public function assignDriverKasir(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->where('status', 'pending')
            ->first();
        
        if ($pickupDelivery && $pesanan->status_transaksi === 'pick_up') {
            $pickupDelivery->update([
                'id_driver' => $request->id_driver,
                'status'    => 'accepted',
                'waktu'     => now(),
            ]);
            
            return redirect()
                ->route('pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditentukan!');
        } 
        else {
            $pesanan->update([
                'status_transaksi' => 'siap_di_antar'
            ]);

            Delivery::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_driver' => $request->id_driver,
                'jenis' => 'antar',
                'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                'status' => 'pending',
                'waktu' => now()
            ]);

            return redirect()
                ->route('kasir.pesanan.online.detail', $id)
                ->with('success', 'Driver berhasil ditentukan!');
        }
    }

    public function assignDriverAdmin2(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->where('status', 'pending')
            ->first();
        
        if ($pickupDelivery && $pesanan->status_transaksi === 'pick_up') {
            $pickupDelivery->update([
                'id_driver' => $request->id_driver,
                'status'    => 'accepted',
                'waktu'     => now(),
            ]);
            
            return redirect()
                ->route('pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditentukan!');
        } 
        else {
            $pesanan->update([
                'status_transaksi' => 'siap_di_antar'
            ]);

            Delivery::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_driver' => $request->id_driver,
                'jenis' => 'antar',
                'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                'status' => 'pending',
                'waktu' => now()
            ]);

            return redirect()
                ->route('admin2.pesanan.online.detail', $id)
                ->with('success', 'Driver berhasil ditentukan!');
        }
    }

    public function listDeliveryOnline()
    {
        $deliveries = Delivery::with(['transaksi.pelanggan', 'driver'])
            ->whereNull('id_driver')
            ->get();

        return view('pesanan_online.delivery.index', compact('deliveries'));
    }

     public function listDeliveryOnlineKasir()
    {
        $deliveries = Delivery::with(['transaksi.pelanggan', 'driver'])
            ->whereNull('id_driver')
            ->get();

        return view('kasir.pesanan_online.delivery.index', compact('deliveries'));
    }

     public function listDeliveryOnlineAdmin2()
    {
        $deliveries = Delivery::with(['transaksi.pelanggan', 'driver'])
            ->whereNull('id_driver')
            ->get();

        return view('admin2.pesanan_online.delivery.index', compact('deliveries'));
    }

        private function statusMap()
    {
        return [
            'pickup'        => ['pick_up'],
            'antrian'       => ['antrian'], // ✅ CHANGED FROM menunggu_konfirmasi
            'proses'        => ['proses'],
            'selesai_dicuci' => ['selesai_dicuci'],
            'siap_di_ambil' => ['siap_di_ambil'],
            'siap_di_antar' => ['siap_di_antar'],
            'selesai'       => ['selesai'],
            'ditolak'       => ['ditolak'],
        ];
    }
        
            public function updateData(Request $request, $id)
{
    // ===============================
    // ✅ VALIDASI
    // ===============================
    $request->validate([
        'id_detail.*'        => 'required|exists:detail_transaksi,id_detail_transaksi',
        'qty.*'              => 'required|numeric|min:0.01',
        'diskon'             => 'nullable|numeric|min:0',
        'tipe_diskon'        => 'nullable|in:nominal,percent',
        'keterangan'         => 'nullable|string',
        'foto_bukti'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        'tgl_estimasi'       => 'required|date|after_or_equal:today',
        'id_biaya_tambahan'  => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
        'ongkir_method'      => 'nullable|in:preset,manual',
        'ongkir_manual'      => 'nullable|numeric|min:0',
    ]);

    // ===============================
    // 🔍 AMBIL TRANSAKSI
    // ===============================
    $pesanan = Transaksi::with(['pelanggan'])
        ->where('jenis_transaksi', 'online')
        ->findOrFail($id);

    // ===============================
    // ✅ STEP 1: HITUNG SUBTOTAL ITEM
    // ===============================
    $subtotalItems = 0;

    foreach ($request->id_detail as $index => $idDetail) {
        $detail = DetailTransaksi::with('jenis')->findOrFail($idDetail);

        $qty      = $request->qty[$index];
        $harga    = $detail->jenis->harga;
        $idSatuan = $detail->jenis->id_satuan;
        $subtotal = $qty * $harga;

        $subtotalItems += $subtotal;

        $detail->update([
            'qty'           => $qty,
            'harga'         => $harga,
            'id_satuan'     => $idSatuan,
            'subtotal'      => $subtotal,
            'tgl_estimasi'  => $request->tgl_estimasi,
        ]);
    }

    // ===============================
    // ✅ STEP 2: ONGKIR
    // ===============================
    $biayaOngkir = 0;
    $idBiayaTambahanFinal = null;

    if ($request->filled('ongkir_method')) {
        if ($request->ongkir_method === 'manual' && $request->ongkir_manual > 0) {
            $biayaOngkir = $request->ongkir_manual;
            $biayaTambahan = BiayaTambahan::firstOrCreate(
                ['nominal' => $biayaOngkir],
                ['nominal' => $biayaOngkir]
            );
            $idBiayaTambahanFinal = $biayaTambahan->id_biaya_tambahan;
        } elseif ($request->ongkir_method === 'preset' && $request->filled('id_biaya_tambahan')) {
            $biayaTambahan = BiayaTambahan::find($request->id_biaya_tambahan);
            if ($biayaTambahan) {
                $biayaOngkir = $biayaTambahan->nominal;
                $idBiayaTambahanFinal = $biayaTambahan->id_biaya_tambahan;
            }
        }
    }

    // ===============================
    // ✅ STEP 3: DISKON
    // ===============================
    $diskon = $request->diskon ?? 0;
    $tipeDiskon = $request->tipe_diskon ?? 'nominal';

    if ($tipeDiskon === 'percent' && $diskon > 0) {
        $diskon = ($subtotalItems * $diskon) / 100;
    }

    // ===============================
    // ✅ STEP 4: TOTAL AKHIR
    // ===============================
    $totalAkhir = $subtotalItems + $biayaOngkir - $diskon;

    // ===============================
    // ✅ STEP 5: FOTO BUKTI
    // ===============================
    $fotoBuktiPath = $pesanan->foto_bukti;

    if ($request->hasFile('foto_bukti')) {
        if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
            Storage::disk('public')->delete($pesanan->foto_bukti);
        }
        $file = $request->file('foto_bukti');
        $filename = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
        $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
    }

    // ===============================
    // ✅ STEP 6: CEK PICKUP
    // ===============================
    $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'pickup')
        ->where('status', 'arrived_at_laundry')
        ->first();

    $statusBaru = $pesanan->status_transaksi;

    if ($pesanan->status_transaksi === 'pick_up' && $deliveryPickup) {
        $statusBaru = 'antrian';
    }

    // ===============================
    // ✅ STEP 7: UPDATE TRANSAKSI
    // ===============================
    $pesanan->update([
        'total_harga'        => $totalAkhir,
        'diskon'             => $diskon,
        'tipe_diskon'        => $tipeDiskon,
        'keterangan'         => $request->keterangan,
        'status_transaksi'   => $statusBaru,
        'foto_bukti'         => $fotoBuktiPath,
        'tgl_estimasi'       => $request->tgl_estimasi,
        'id_biaya_tambahan'  => $idBiayaTambahanFinal,
    ]);

    // ===============================
    // 🔔 STEP 8: KIRIM FCM - INVOICE
    // ===============================
    $fcmSent = false;
    $fcmMessage = '';
    
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

    Log::info("📍 updateData: Attempting FCM for order {$pesanan->id_transaksi}, pelanggan_id: {$idPelanggan}");

    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
            ->whereNotNull('token')
            ->pluck('token')
            ->toArray();
        
        Log::info("📍 updateData: Found " . count($tokens) . " FCM tokens for pelanggan {$idPelanggan}");
        
        if (!empty($tokens)) {
            // ✅ TITLE & BODY UNTUK INVOICE
            $title = '💰 Harga Pesanan Sudah Diisi!';
            $body = "ORDER/{$pesanan->id_transaksi} - Total: Rp " . number_format($totalAkhir, 0, ',', '.') . ". Tap untuk lihat detail pembayaran.";

            $fcmSentCount = 0;
            $fcmFailedCount = 0;

            foreach ($tokens as $token) {
                try {
                    // ✅ KIRIM DENGAN DATA INVOICE
                    $result = FcmService::send(
                        $token,
                        $title,
                        $body,
                        [
                            'transaksi_id' => (string) $pesanan->id_transaksi,
                            'type' => 'invoice', // ✅ TYPE = INVOICE!
                            'action' => 'open_invoice', // ✅ ACTION = OPEN_INVOICE!
                            'total_harga' => (string) $totalAkhir,
                            'status' => $statusBaru,
                        ]
                    );
                    
                    if ($result) {
                        $fcmSentCount++;
                        Log::info("✅ updateData: FCM INVOICE sent to token: " . substr($token, 0, 20) . "...");
                    } else {
                        $fcmFailedCount++;
                        Log::warning("⚠️ updateData: FCM returned false for token: " . substr($token, 0, 20) . "...");
                    }
                    
                    $fcmSent = true;
                    
                } catch (\Exception $e) {
                    $fcmFailedCount++;
                    Log::error("❌ updateData: FCM Send Error: " . $e->getMessage());
                }
            }
            
            Log::info("📊 updateData: FCM INVOICE Summary - Sent: {$fcmSentCount}, Failed: {$fcmFailedCount}");
            
        } else {
            Log::warning("⚠️ updateData: No valid FCM tokens");
            $fcmMessage = ' (Notifikasi tidak terkirim: token tidak ditemukan)';
        }
    } else {
        Log::warning("⚠️ updateData: No pelanggan_id");
        $fcmMessage = ' (Notifikasi tidak terkirim: pelanggan tidak ditemukan)';
    }

    // ===============================
    // ✅ RESPONSE
    // ===============================
    $successMessage = 'Data pesanan berhasil diperbarui' . ($fcmSent ? ' & notifikasi invoice terkirim!' : $fcmMessage);
    
    return redirect()
        ->route('pesanan.online.detail', $id)
        ->with('success', $successMessage);
}

public function updateDataAdmin2(Request $request, $id)
{
        $request->validate([
        'id_detail.*' => 'required|exists:detail_transaksi,id_detail_transaksi',
        'qty.*'       => 'required|numeric|min:0.01',
        'diskon'      => 'nullable|numeric|min:0',
        'tipe_diskon' => 'nullable|in:nominal,percent',
        'keterangan'  => 'nullable|string',
        'foto_bukti'  => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        'tgl_estimasi' => 'required|date|after_or_equal:today',
        'id_biaya_tambahan' => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
        'ongkir_method' => 'nullable|in:preset,manual',  // ✅ BARU
        'ongkir_manual' => 'nullable|numeric|min:0',     // ✅ BARU
    ]);

    $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);

    // ✅ STEP 1: HITUNG SUBTOTAL DARI ITEMS
    $subtotalItems = 0;

    foreach ($request->id_detail as $index => $idDetail) {
        $detail = DetailTransaksi::with('jenis')->findOrFail($idDetail);

        $qty       = $request->qty[$index];
        $harga     = $detail->jenis->harga;
        $idSatuan  = $detail->jenis->id_satuan;
        $subtotal  = $qty * $harga;

        $subtotalItems += $subtotal;

        $detail->update([
            'qty'        => $qty,
            'harga'      => $harga,
            'id_satuan'  => $idSatuan,
            'subtotal'   => $subtotal,
            'tgl_estimasi' => $request->tgl_estimasi,
        ]);
    }

    // ✅ STEP 2: AMBIL BIAYA ONGKIR (JIKA ADA)
    $biayaOngkir = 0;
    $idBiayaTambahanFinal = null;

    // Cek metode ongkir yang dipilih
    if ($request->filled('ongkir_method')) {
        if ($request->ongkir_method === 'manual' && $request->filled('ongkir_manual') && $request->ongkir_manual > 0) {
            // ✅ ONGKIR MANUAL - Buat/Update BiayaTambahan baru
            $biayaOngkir = $request->ongkir_manual;
            
            // Cek apakah sudah ada biaya tambahan dengan nominal yang sama
            $biayaTambahanManual = BiayaTambahan::where('nominal', $biayaOngkir)
                ->first();
            
            if (!$biayaTambahanManual) {
                // Buat baru jika belum ada
                $biayaTambahanManual = BiayaTambahan::create([
                    'nominal' => $biayaOngkir,
                ]);
            }
            
            $idBiayaTambahanFinal = $biayaTambahanManual->id_biaya_tambahan;
            
        } elseif ($request->ongkir_method === 'preset' && $request->filled('id_biaya_tambahan')) {
            // ✅ ONGKIR DARI LIST
            $biayaTambahan = BiayaTambahan::find($request->id_biaya_tambahan);
            if ($biayaTambahan) {
                $biayaOngkir = $biayaTambahan->nominal;
                $idBiayaTambahanFinal = $biayaTambahan->id_biaya_tambahan;
            }
        }
    }

    // ✅ STEP 3: HITUNG DISKON
    $diskon = $request->diskon ?? 0;
    $tipeDiskon = $request->tipe_diskon ?? 'nominal';

    if ($tipeDiskon === 'percent' && $diskon > 0) {
        // Diskon persen dihitung dari subtotal items (TIDAK termasuk ongkir)
        $diskon = ($subtotalItems * $diskon) / 100;
    }

    // ✅ STEP 4: HITUNG TOTAL AKHIR
    // Total = Subtotal Items + Biaya Ongkir - Diskon
    $totalAkhir = $subtotalItems + $biayaOngkir - $diskon;

    // ✅ STEP 5: UPLOAD FOTO BUKTI (JIKA ADA)
    $fotoBuktiPath = $pesanan->foto_bukti;
    
    if ($request->hasFile('foto_bukti')) {
        if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
            Storage::disk('public')->delete($pesanan->foto_bukti);
        }

        $file = $request->file('foto_bukti');
        $filename = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
        $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
    }

    // ✅ STEP 6: CEK STATUS DELIVERY PICKUP
    $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'pickup')
        ->where('status', 'arrived_at_laundry')
        ->first();

    $statusBaru = $pesanan->status_transaksi;
    
    if ($pesanan->status_transaksi === 'pick_up' && $deliveryPickup) {
        $statusBaru = 'antrian';
    }

    // ✅ STEP 7: UPDATE TRANSAKSI
    $pesanan->update([
        'total_harga' => $totalAkhir,
        'diskon'      => $diskon,
        'tipe_diskon' => $tipeDiskon,
        'keterangan'  => $request->keterangan,
        'status_transaksi' => $statusBaru,
        'foto_bukti'  => $fotoBuktiPath,
        'tgl_estimasi' => $request->tgl_estimasi,
        'id_biaya_tambahan' => $idBiayaTambahanFinal,
    ]);

    $message = 'Data pesanan berhasil diperbarui!';
    if ($statusBaru === 'antrian') {
        $message = 'Data pesanan berhasil diperbarui dan pesanan masuk ke antrian!';
    }

    return redirect()
        ->route('admin2.pesanan.online.detail', $id)
        ->with('success', $message);
}
public function updateDataKasir(Request $request, $id)
{
    $request->validate([
    'id_detail.*' => 'required|exists:detail_transaksi,id_detail_transaksi',
    'qty.*'       => 'required|numeric|min:0.01',
    'diskon'      => 'nullable|numeric|min:0',
    'tipe_diskon' => 'nullable|in:nominal,percent',
    'keterangan'  => 'nullable|string',
    'foto_bukti'  => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
    'tgl_estimasi' => 'required|date|after_or_equal:today',
    'id_biaya_tambahan' => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
    'ongkir_method' => 'nullable|in:preset,manual',  // ✅ BARU
    'ongkir_manual' => 'nullable|numeric|min:0',     // ✅ BARU
]);

    $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);

    // ✅ STEP 1: HITUNG SUBTOTAL DARI ITEMS
    $subtotalItems = 0;

    foreach ($request->id_detail as $index => $idDetail) {
        $detail = DetailTransaksi::with('jenis')->findOrFail($idDetail);

        $qty       = $request->qty[$index];
        $harga     = $detail->jenis->harga;
        $idSatuan  = $detail->jenis->id_satuan;
        $subtotal  = $qty * $harga;

        $subtotalItems += $subtotal;

        $detail->update([
            'qty'        => $qty,
            'harga'      => $harga,
            'id_satuan'  => $idSatuan,
            'subtotal'   => $subtotal,
            'tgl_estimasi' => $request->tgl_estimasi,
        ]);
    }

    // ✅ STEP 2: AMBIL BIAYA ONGKIR (JIKA ADA)
    $biayaOngkir = 0;
    $idBiayaTambahanFinal = null;

    // Cek metode ongkir yang dipilih
    if ($request->filled('ongkir_method')) {
        if ($request->ongkir_method === 'manual' && $request->filled('ongkir_manual') && $request->ongkir_manual > 0) {
            // ✅ ONGKIR MANUAL - Buat/Update BiayaTambahan baru
            $biayaOngkir = $request->ongkir_manual;
            
            // Cek apakah sudah ada biaya tambahan dengan nominal yang sama
            $biayaTambahanManual = BiayaTambahan::where('nominal', $biayaOngkir)
                ->first();
            
            if (!$biayaTambahanManual) {
                // Buat baru jika belum ada
                $biayaTambahanManual = BiayaTambahan::create([
                    'nominal' => $biayaOngkir,
                ]);
            }
            
            $idBiayaTambahanFinal = $biayaTambahanManual->id_biaya_tambahan;
            
        } elseif ($request->ongkir_method === 'preset' && $request->filled('id_biaya_tambahan')) {
            // ✅ ONGKIR DARI LIST
            $biayaTambahan = BiayaTambahan::find($request->id_biaya_tambahan);
            if ($biayaTambahan) {
                $biayaOngkir = $biayaTambahan->nominal;
                $idBiayaTambahanFinal = $biayaTambahan->id_biaya_tambahan;
            }
        }
    }

    // ✅ STEP 3: HITUNG DISKON
    $diskon = $request->diskon ?? 0;
    $tipeDiskon = $request->tipe_diskon ?? 'nominal';

    if ($tipeDiskon === 'percent' && $diskon > 0) {
        // Diskon persen dihitung dari subtotal items (TIDAK termasuk ongkir)
        $diskon = ($subtotalItems * $diskon) / 100;
    }

    // ✅ STEP 4: HITUNG TOTAL AKHIR
    // Total = Subtotal Items + Biaya Ongkir - Diskon
    $totalAkhir = $subtotalItems + $biayaOngkir - $diskon;

    // ✅ STEP 5: UPLOAD FOTO BUKTI (JIKA ADA)
    $fotoBuktiPath = $pesanan->foto_bukti;
    
    if ($request->hasFile('foto_bukti')) {
        if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
            Storage::disk('public')->delete($pesanan->foto_bukti);
        }

        $file = $request->file('foto_bukti');
        $filename = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
        $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
    }

    // ✅ STEP 6: CEK STATUS DELIVERY PICKUP
    $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'pickup')
        ->where('status', 'arrived_at_laundry')
        ->first();

    $statusBaru = $pesanan->status_transaksi;
    
    if ($pesanan->status_transaksi === 'pick_up' && $deliveryPickup) {
        $statusBaru = 'antrian';
    }

    // ✅ STEP 7: UPDATE TRANSAKSI
    $pesanan->update([
        'total_harga' => $totalAkhir,
        'diskon'      => $diskon,
        'tipe_diskon' => $tipeDiskon,
        'keterangan'  => $request->keterangan,
        'status_transaksi' => $statusBaru,
        'foto_bukti'  => $fotoBuktiPath,
        'tgl_estimasi' => $request->tgl_estimasi,
        'id_biaya_tambahan' => $idBiayaTambahanFinal,
    ]);

    $message = 'Data pesanan berhasil diperbarui!';
    if ($statusBaru === 'antrian') {
        $message = 'Data pesanan berhasil diperbarui dan pesanan masuk ke antrian!';
    }

    return redirect()
        ->route('kasir.pesanan.online.detail', $id)
        ->with('success', $message);
}


    public function konfirmasiPesanan(Request $request, $id)
    {
        $request->validate([
            'metode_kirim' => 'required|in:whatsapp,sms'
        ]);

        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);

        $pesanan->update([
            'status_transaksi' => 'dikonfirmasi'
        ]);

        $namaPelanggan = $pesanan->nama_pelanggan ?? 'Pelanggan';
        $noHp = $pesanan->no_hp ?? '';
        $totalHarga = number_format($pesanan->total_harga, 0, ',', '.');
        $alamat = $pesanan->pelanggan->alamat ?? '';

        $pesan = "Halo *{$namaPelanggan}*,%0A%0A";
        $pesan .= "Pesanan Anda sudah dikonfirmasi! 🎉%0A%0A";
        $pesan .= "*Detail Pesanan:*%0A";
        $pesan .= "📦 Order ID: {$pesanan->id_transaksi}%0A";
        $pesan .= "💰 Total Harga: Rp {$totalHarga}%0A";
        
        if ($alamat) {
            $pesan .= "📍 Alamat: {$alamat}%0A";
        }
        
        $pesan .= "%0ATerima kasih telah mempercayai layanan kami! 😊";

        if ($request->metode_kirim === 'whatsapp' && $noHp) {
            $waNumber = preg_replace('/^0/', '62', $noHp);
            $waUrl = "https://wa.me/{$waNumber}?text={$pesan}";
            
            return redirect()->away($waUrl);
        } 
        elseif ($request->metode_kirim === 'sms' && $noHp) {
            $smsUrl = "sms:{$noHp}?body=" . urlencode(strip_tags(str_replace(['%0A', '*'], ["\n", ''], $pesan)));
            
            return redirect()->away($smsUrl);
        }

        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Pesanan berhasil dikonfirmasi!');
    }

     public function konfirmasiPesananAdmin2(Request $request, $id)
    {
        $request->validate([
            'metode_kirim' => 'required|in:whatsapp,sms'
        ]);

        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);

        $pesanan->update([
            'status_transaksi' => 'dikonfirmasi'
        ]);

        $namaPelanggan = $pesanan->nama_pelanggan ?? 'Pelanggan';
        $noHp = $pesanan->no_hp ?? '';
        $totalHarga = number_format($pesanan->total_harga, 0, ',', '.');
        $alamat = $pesanan->pelanggan->alamat ?? '';

        $pesan = "Halo *{$namaPelanggan}*,%0A%0A";
        $pesan .= "Pesanan Anda sudah dikonfirmasi! 🎉%0A%0A";
        $pesan .= "*Detail Pesanan:*%0A";
        $pesan .= "📦 Order ID: {$pesanan->id_transaksi}%0A";
        $pesan .= "💰 Total Harga: Rp {$totalHarga}%0A";
        
        if ($alamat) {
            $pesan .= "📍 Alamat: {$alamat}%0A";
        }
        
        $pesan .= "%0ATerima kasih telah mempercayai layanan kami! 😊";

        if ($request->metode_kirim === 'whatsapp' && $noHp) {
            $waNumber = preg_replace('/^0/', '62', $noHp);
            $waUrl = "https://wa.me/{$waNumber}?text={$pesan}";
            
            return redirect()->away($waUrl);
        } 
        elseif ($request->metode_kirim === 'sms' && $noHp) {
            $smsUrl = "sms:{$noHp}?body=" . urlencode(strip_tags(str_replace(['%0A', '*'], ["\n", ''], $pesan)));
            
            return redirect()->away($smsUrl);
        }

        return redirect()
            ->route('admin2.pesanan.online.detail', $id)
            ->with('success', 'Pesanan berhasil dikonfirmasi!');
    }

     public function konfirmasiPesananKasir(Request $request, $id)
    {
        $request->validate([
            'metode_kirim' => 'required|in:whatsapp,sms'
        ]);

        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);

        $pesanan->update([
            'status_transaksi' => 'dikonfirmasi'
        ]);

        $namaPelanggan = $pesanan->nama_pelanggan ?? 'Pelanggan';
        $noHp = $pesanan->no_hp ?? '';
        $totalHarga = number_format($pesanan->total_harga, 0, ',', '.');
        $alamat = $pesanan->pelanggan->alamat ?? '';

        $pesan = "Halo *{$namaPelanggan}*,%0A%0A";
        $pesan .= "Pesanan Anda sudah dikonfirmasi! 🎉%0A%0A";
        $pesan .= "*Detail Pesanan:*%0A";
        $pesan .= "📦 Order ID: {$pesanan->id_transaksi}%0A";
        $pesan .= "💰 Total Harga: Rp {$totalHarga}%0A";
        
        if ($alamat) {
            $pesan .= "📍 Alamat: {$alamat}%0A";
        }
        
        $pesan .= "%0ATerima kasih telah mempercayai layanan kami! 😊";

        if ($request->metode_kirim === 'whatsapp' && $noHp) {
            $waNumber = preg_replace('/^0/', '62', $noHp);
            $waUrl = "https://wa.me/{$waNumber}?text={$pesan}";
            
            return redirect()->away($waUrl);
        } 
        elseif ($request->metode_kirim === 'sms' && $noHp) {
            $smsUrl = "sms:{$noHp}?body=" . urlencode(strip_tags(str_replace(['%0A', '*'], ["\n", ''], $pesan)));
            
            return redirect()->away($smsUrl);
        }

        return redirect()
            ->route('admin2.pesanan.online.detail', $id)
            ->with('success', 'Pesanan berhasil dikonfirmasi!');
    }

    public function buktiPembayaran($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        return view('pesanan_online.bukti_pembayaran', compact('pesanan'));
    }

    public function buktiPembayaranAdmin2($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        return view('admin2.pesanan_online.bukti_pembayaran', compact('pesanan'));
    }


    public function buktiPembayaranKasir($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'metodeBayar', 'pembayaran'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        return view('kasir.pesanan_online.bukti_pembayaran', compact('pesanan'));
    }

    public function simpanBuktiPembayaran(Request $request, $id)
{
    $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
    
    $metodeBayar = $pesanan->metodeBayar;
    $isCash = $metodeBayar && (stripos($metodeBayar->nama_metode_bayar, 'cash') !== false || stripos($metodeBayar->nama_metode_bayar, 'tunai') !== false);

    if ($isCash) {
        // ✅ UNTUK CASH: HANYA KONFIRMASI LUNAS
        $request->validate([
            'tipe_pembayaran' => 'required|in:lunas',
            'keterangan_bayar' => 'nullable|string',
        ]);
        
        // ✅ CEK APAKAH SUDAH ADA BUKTI DARI CUSTOMER (FOTO SELFIE DGN CUCIAN)
        $buktiBayarCustomer = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
            ->where('tipe_pembayaran', 'lunas')
            ->whereNotNull('foto_bukti')
            ->latest()
            ->first();
        
        // ✅ UNTUK CASH: Foto bukti bisa null (konfirmasi verbal)
        // Tidak wajib ada foto dari customer
        
        $fotoBuktiPath = $buktiBayarCustomer ? $buktiBayarCustomer->foto_bukti : null;
        $nominalBayar = $pesanan->total_harga; // Selalu full amount untuk cash
        
        // ✅ JIKA SUDAH ADA PEMBAYARAN DARI CUSTOMER, UPDATE
        if ($buktiBayarCustomer) {
            if ($request->keterangan_bayar) {
                $keteranganBaru = $buktiBayarCustomer->keterangan 
                    ? $buktiBayarCustomer->keterangan . ' | Admin: ' . $request->keterangan_bayar 
                    : 'Admin: ' . $request->keterangan_bayar;
                
                $buktiBayarCustomer->update([
                    'keterangan' => $keteranganBaru,
                    'updated_at' => now(),
                ]);
            }
        } else {
            // ✅ BUAT PEMBAYARAN BARU UNTUK CASH (tanpa foto)
            Pembayaran::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_metode_bayar' => $pesanan->id_metode_bayar,
                'tipe_pembayaran' => 'lunas',
                'nominal' => $nominalBayar,
                'foto_bukti' => null, // ✅ Null untuk cash (konfirmasi verbal)
                'keterangan' => $request->keterangan_bayar ?? 'Pembayaran Cash dikonfirmasi oleh admin',
                'tanggal_bayar' => now()->format('Y-m-d'),
            ]);
        }
        
        // ✅ UPDATE TRANSAKSI KE LUNAS
        $pesanan->update([
            'total_bayar' => $pesanan->total_harga,
            'dp' => 0,
            'status_bayar' => 'lunas',
            'tgl_lunas' => now()->format('Y-m-d'),
        ]);
        
        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Pembayaran cash berhasil dikonfirmasi! Pesanan lunas.');
            
    } else {
        // ✅ UNTUK TRANSFER/NON-CASH
        $request->validate([
            'tipe_pembayaran' => 'required|in:dp,lunas',
            'nominal_bayar' => 'required_if:tipe_pembayaran,dp|nullable|numeric|min:1',
            'foto_bukti_bayar' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'keterangan_bayar' => 'nullable|string',
        ]);
        
        if ($pesanan->status_bayar === 'lunas') {
            return redirect()
                ->back()
                ->with('error', 'Pesanan ini sudah lunas!');
        }

        $sisaPembayaran = $pesanan->total_harga - $pesanan->total_bayar;
        
        if ($request->tipe_pembayaran === 'dp') {
            $nominalBayar = $request->nominal_bayar;
            
            if ($nominalBayar > $sisaPembayaran) {
                return redirect()
                    ->back()
                    ->with('error', 'Nominal DP melebihi sisa pembayaran!');
            }
            
            $totalBayarBaru = $pesanan->total_bayar + $nominalBayar;
            
            if ($totalBayarBaru >= $pesanan->total_harga) {
                $statusBayar = 'lunas';
                $totalBayarBaru = $pesanan->total_harga;
            } else {
                $statusBayar = 'DP';
            }
            
        } else {
            $nominalBayar = $sisaPembayaran;
            $totalBayarBaru = $pesanan->total_harga;
            $statusBayar = 'lunas';
        }

        $fotoBuktiPath = null;
        
        if ($request->hasFile('foto_bukti_bayar')) {
            $file = $request->file('foto_bukti_bayar');
            $filename = 'bayar_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
            $fotoBuktiPath = $file->storeAs('foto_bukti_pembayaran', $filename, 'public');
        }
        
        Pembayaran::create([
            'id_transaksi' => $pesanan->id_transaksi,
            'id_metode_bayar' => $pesanan->id_metode_bayar,
            'tipe_pembayaran' => $request->tipe_pembayaran,
            'nominal' => $nominalBayar,
            'foto_bukti' => $fotoBuktiPath,
            'keterangan' => $request->keterangan_bayar,
            'tanggal_bayar' => now()->format('Y-m-d'),
        ]);

        $pesanan->update([
            'total_bayar' => $totalBayarBaru,
            'dp' => $pesanan->dp + $nominalBayar,
            'status_bayar' => $statusBayar,
            'tgl_lunas' => $statusBayar === 'lunas' ? now()->format('Y-m-d') : null,
        ]);

        $message = $statusBayar === 'lunas' 
            ? 'Pembayaran lunas berhasil disimpan!' 
            : 'DP sebesar Rp ' . number_format($nominalBayar, 0, ',', '.') . ' berhasil disimpan!';

        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', $message);
    }
}

    public function simpanBuktiPembayaranKasir(Request $request, $id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        $metodeBayar = $pesanan->metodeBayar;
        $isCash = $metodeBayar && (stripos($metodeBayar->nama_metode_bayar, 'cash') !== false || stripos($metodeBayar->nama_metode_bayar, 'tunai') !== false);

        if ($isCash) {
            $request->validate([
                'tipe_pembayaran' => 'required|in:lunas',
                'keterangan_bayar' => 'nullable|string',
            ]);
            
            $buktiBayarCustomer = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
                ->where('tipe_pembayaran', 'lunas')
                ->whereNotNull('foto_bukti')
                ->latest()
                ->first();
                
            if (!$buktiBayarCustomer || !$buktiBayarCustomer->foto_bukti) {
                return redirect()
                    ->back()
                    ->with('error', 'Pelanggan belum upload bukti pembayaran!');
            }
        } else {
            $request->validate([
                'tipe_pembayaran' => 'required|in:dp,lunas',
                'nominal_bayar' => 'required_if:tipe_pembayaran,dp|nullable|numeric|min:1',
                'foto_bukti_bayar' => 'required|image|mimes:jpeg,jpg,png|max:2048',
                'keterangan_bayar' => 'nullable|string',
            ]);
        }

        if ($pesanan->status_bayar === 'lunas') {
            return redirect()
                ->back()
                ->with('error', 'Pesanan ini sudah lunas!');
        }

        $sisaPembayaran = $pesanan->total_harga - $pesanan->total_bayar;
        
        if (!$isCash && $request->tipe_pembayaran === 'dp') {
            $nominalBayar = $request->nominal_bayar;
            
            if ($nominalBayar > $sisaPembayaran) {
                return redirect()
                    ->back()
                    ->with('error', 'Nominal DP melebihi sisa pembayaran!');
            }
            
            $totalBayarBaru = $pesanan->total_bayar + $nominalBayar;
            
            if ($totalBayarBaru >= $pesanan->total_harga) {
                $statusBayar = 'lunas';
                $totalBayarBaru = $pesanan->total_harga;
            } else {
                $statusBayar = 'DP';
            }
            
        } else {
            $nominalBayar = $sisaPembayaran;
            $totalBayarBaru = $pesanan->total_harga;
            $statusBayar = 'lunas';
        }

        $fotoBuktiPath = null;
        
        if ($isCash) {
            if (isset($buktiBayarCustomer)) {
                if ($request->keterangan_bayar) {
                    $keteranganBaru = $buktiBayarCustomer->keterangan 
                        ? $buktiBayarCustomer->keterangan . ' | Admin: ' . $request->keterangan_bayar 
                        : 'Admin: ' . $request->keterangan_bayar;
                    
                    $buktiBayarCustomer->update([
                        'keterangan' => $keteranganBaru,
                        'updated_at' => now(),
                    ]);
                }
                
                $fotoBuktiPath = $buktiBayarCustomer->foto_bukti;
                $nominalBayar = $buktiBayarCustomer->nominal;
            }
        } else {
            if ($request->hasFile('foto_bukti_bayar')) {
                $file = $request->file('foto_bukti_bayar');
                $filename = 'bayar_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
                $fotoBuktiPath = $file->storeAs('foto_bukti_pembayaran', $filename, 'public');
            }
            
            // ✅ FIX: TAMBAHKAN id_metode_bayar
            Pembayaran::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_metode_bayar' => $pesanan->id_metode_bayar, // ✅ TAMBAH INI!
                'tipe_pembayaran' => $request->tipe_pembayaran,
                'nominal' => $nominalBayar,
                'foto_bukti' => $fotoBuktiPath,
                'keterangan' => $request->keterangan_bayar,
                'tanggal_bayar' => now()->format('Y-m-d'),
            ]);
        }

        $pesanan->update([
            'total_bayar' => $totalBayarBaru,
            'dp' => $pesanan->dp + $nominalBayar,
            'status_bayar' => $statusBayar,
            'tgl_lunas' => $statusBayar === 'lunas' ? now()->format('Y-m-d') : null,
        ]);

        $message = $isCash 
            ? 'Pembayaran cash berhasil dikonfirmasi! Pesanan lunas.' 
            : ($statusBayar === 'lunas' 
                ? 'Pembayaran lunas berhasil disimpan!' 
                : 'DP sebesar Rp ' . number_format($nominalBayar, 0, ',', '.') . ' berhasil disimpan!');

        return redirect()
            ->route('kasir.pesanan.online.detail', $id)
            ->with('success', $message);
    }

    public function simpanBuktiPembayaranAdmin2(Request $request, $id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        $metodeBayar = $pesanan->metodeBayar;
        $isCash = $metodeBayar && (stripos($metodeBayar->nama_metode_bayar, 'cash') !== false || stripos($metodeBayar->nama_metode_bayar, 'tunai') !== false);

        if ($isCash) {
            $request->validate([
                'tipe_pembayaran' => 'required|in:lunas',
                'keterangan_bayar' => 'nullable|string',
            ]);
            
            $buktiBayarCustomer = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
                ->where('tipe_pembayaran', 'lunas')
                ->whereNotNull('foto_bukti')
                ->latest()
                ->first();
                
            if (!$buktiBayarCustomer || !$buktiBayarCustomer->foto_bukti) {
                return redirect()
                    ->back()
                    ->with('error', 'Pelanggan belum upload bukti pembayaran!');
            }
        } else {
            $request->validate([
                'tipe_pembayaran' => 'required|in:dp,lunas',
                'nominal_bayar' => 'required_if:tipe_pembayaran,dp|nullable|numeric|min:1',
                'foto_bukti_bayar' => 'required|image|mimes:jpeg,jpg,png|max:2048',
                'keterangan_bayar' => 'nullable|string',
            ]);
        }

        if ($pesanan->status_bayar === 'lunas') {
            return redirect()
                ->back()
                ->with('error', 'Pesanan ini sudah lunas!');
        }

        $sisaPembayaran = $pesanan->total_harga - $pesanan->total_bayar;
        
        if (!$isCash && $request->tipe_pembayaran === 'dp') {
            $nominalBayar = $request->nominal_bayar;
            
            if ($nominalBayar > $sisaPembayaran) {
                return redirect()
                    ->back()
                    ->with('error', 'Nominal DP melebihi sisa pembayaran!');
            }
            
            $totalBayarBaru = $pesanan->total_bayar + $nominalBayar;
            
            if ($totalBayarBaru >= $pesanan->total_harga) {
                $statusBayar = 'lunas';
                $totalBayarBaru = $pesanan->total_harga;
            } else {
                $statusBayar = 'DP';
            }
            
        } else {
            $nominalBayar = $sisaPembayaran;
            $totalBayarBaru = $pesanan->total_harga;
            $statusBayar = 'lunas';
        }

        $fotoBuktiPath = null;
        
        if ($isCash) {
            if (isset($buktiBayarCustomer)) {
                if ($request->keterangan_bayar) {
                    $keteranganBaru = $buktiBayarCustomer->keterangan 
                        ? $buktiBayarCustomer->keterangan . ' | Admin: ' . $request->keterangan_bayar 
                        : 'Admin: ' . $request->keterangan_bayar;
                    
                    $buktiBayarCustomer->update([
                        'keterangan' => $keteranganBaru,
                        'updated_at' => now(),
                    ]);
                }
                
                $fotoBuktiPath = $buktiBayarCustomer->foto_bukti;
                $nominalBayar = $buktiBayarCustomer->nominal;
            }
        } else {
            if ($request->hasFile('foto_bukti_bayar')) {
                $file = $request->file('foto_bukti_bayar');
                $filename = 'bayar_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
                $fotoBuktiPath = $file->storeAs('foto_bukti_pembayaran', $filename, 'public');
            }
            
            // ✅ FIX: TAMBAHKAN id_metode_bayar
            Pembayaran::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'id_metode_bayar' => $pesanan->id_metode_bayar, // ✅ TAMBAH INI!
                'tipe_pembayaran' => $request->tipe_pembayaran,
                'nominal' => $nominalBayar,
                'foto_bukti' => $fotoBuktiPath,
                'keterangan' => $request->keterangan_bayar,
                'tanggal_bayar' => now()->format('Y-m-d'),
            ]);
        }

        $pesanan->update([
            'total_bayar' => $totalBayarBaru,
            'dp' => $pesanan->dp + $nominalBayar,
            'status_bayar' => $statusBayar,
            'tgl_lunas' => $statusBayar === 'lunas' ? now()->format('Y-m-d') : null,
        ]);

        $message = $isCash 
            ? 'Pembayaran cash berhasil dikonfirmasi! Pesanan lunas.' 
            : ($statusBayar === 'lunas' 
                ? 'Pembayaran lunas berhasil disimpan!' 
                : 'DP sebesar Rp ' . number_format($nominalBayar, 0, ',', '.') . ' berhasil disimpan!');

        return redirect()
            ->route('admin2.pesanan.online.detail', $id)
            ->with('success', $message);
    }
    /**
 * ===============================================
 * KIRIM FCM NOTIFICATION - CUCIAN SELESAI DICUCI
 * ===============================================
 */
public function sendFcmNotification(Request $request, $id)
{
    try {
        $pesanan = Transaksi::with(['pelanggan'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        $notificationType = $request->input('notification_type', 'selesai_dicuci');
        
        // Cek apakah pelanggan punya FCM token
        if (!$pesanan->pelanggan || !$pesanan->pelanggan->fcm_token) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak memiliki FCM token. Pastikan pelanggan sudah login di aplikasi mobile.'
            ], 400);
        }
        
        // Prepare notification data
        $title = '';
        $body = '';
        $data = [
            'order_id' => $pesanan->id_transaksi,
            'type' => $notificationType
        ];
        
        switch ($notificationType) {
            case 'selesai_dicuci':
                $title = '🎉 Cucian Selesai Dicuci!';
                $body = "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan siap untuk diproses lebih lanjut.";
                break;
            
            case 'siap_diambil':
                $title = '✅ Cucian Siap Diambil!';
                $body = "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah siap diambil di laundry kami.";
                break;
            
            case 'dalam_pengiriman':
                $title = '🚚 Cucian Sedang Diantar!';
                $body = "Driver kami sedang dalam perjalanan mengantar cucian Anda (ORDER/{$pesanan->id_transaksi}).";
                break;
            
            default:
                $title = 'Update Pesanan Laundry';
                $body = "Ada update terbaru untuk pesanan Anda (ORDER/{$pesanan->id_transaksi}).";
        }
        
        // TODO: Kirim FCM Notification
        // Implementasi menggunakan package firebase/php-jwt atau guzzle
        // Contoh:
        /*
        $fcmToken = $pesanan->pelanggan->fcm_token;
        
        $notification = [
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
            'badge' => '1',
        ];
        
        $fcmData = [
            'to' => $fcmToken,
            'notification' => $notification,
            'data' => $data,
            'priority' => 'high'
        ];
        
        $headers = [
            'Authorization: key=' . env('FCM_SERVER_KEY'),
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmData));
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);
        
        if (isset($response['success']) && $response['success'] == 1) {
            \Log::info("FCM notification sent successfully to order {$pesanan->id_transaksi}");
            
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dikirim ke pelanggan!'
            ]);
        } else {
            throw new \Exception('FCM response error: ' . json_encode($response));
        }
        */
        
        // SEMENTARA: Simulasi sukses (hapus ini setelah implementasi FCM sebenarnya)
        \Log::info("Simulated FCM notification for order {$pesanan->id_transaksi}: {$title}");
        
        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dikirim ke pelanggan!'
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Pesanan tidak ditemukan!'
        ], 404);
        
    } catch (\Exception $e) {
        \Log::error("Error sending FCM notification: " . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage()
        ], 500);
    }
}
public function autoCheckTerlambat()
{
    try {
        $sekarang = now();
        
        $pesananTerlambat = Transaksi::with(['pelanggan'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'selesai_dicuci')
            ->where('tgl_estimasi', '<', $sekarang)
            ->get();
        
        foreach ($pesananTerlambat as $pesanan) {
            DB::beginTransaction();
            
            $pesanan->update(['status_transaksi' => 'siap_di_antar']);
            
            // Kirim notif
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
                    ->pluck('token')
                    ->filter();
                
                if ($tokens->isNotEmpty()) {
                    $title = '⚠️ Cucian Melewati Estimasi - Akan Diantar!';
                    $body = "Cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati waktu estimasi dan akan segera diantar.";
                    
                    foreach ($tokens as $token) {
                        try {
                            FcmService::send($token, $title, $body, [
                                'transaksi_id' => (string) $pesanan->id_transaksi,
                                'type' => 'auto_siap_antar',
                                'action' => 'open_detail',
                            ]);
                        } catch (\Exception $e) {
                            Log::error("❌ FCM Error: " . $e->getMessage());
                        }
                    }
                }
            }
            
            DB::commit();
            Log::info("✅ Auto-update pesanan {$pesanan->id_transaksi} ke siap_di_antar");
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("❌ Auto-update error: " . $e->getMessage());
    }
}
    
}
