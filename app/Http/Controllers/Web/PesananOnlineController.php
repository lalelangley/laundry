<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\BiayaTambahan; 
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
        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'pick_up')
            ->findOrFail($id);

        // ❌ HAPUS BARIS INI! JANGAN UPDATE STATUS!
        // $pesanan->update(['status_transaksi' => 'antrian']);

        // ✅ HANYA update delivery status
        Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->update(['status' => 'arrived_at_laundry']);
        
        // 🔔 FCM NOTIF - GANTI MESSAGE
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                foreach ($tokens as $token) {
                    FcmService::send(
                        $token, 
                        '📦 Cucian Sudah Sampai!', 
                        "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah tiba di laundry. Admin sedang memeriksa cucian Anda.", 
                        [
                            'transaksi_id' => (string) $pesanan->id_transaksi, 
                            'type' => 'arrived_at_laundry', 
                            'action' => 'open_detail'
                        ]
                    );
                }
            }
        }
    });

    // ✅ GANTI MESSAGE - Minta admin isi data
    return redirect()
        ->route('pesanan.online.detail', $id)
        ->with('success', 'Driver sampai laundry. Silakan isi data pesanan untuk mengirim invoice.');
}
public function driverArriveAtLaundryKasir($id)
{
    DB::transaction(function () use ($id) {
        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'pick_up')
            ->findOrFail($id);

        // ❌ HAPUS BARIS INI!
        // $pesanan->update(['status_transaksi' => 'antrian']);

        // ✅ HANYA update delivery status
        Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->update(['status' => 'arrived_at_laundry']);
        
        // 🔔 FCM NOTIF
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                foreach ($tokens as $token) {
                    FcmService::send(
                        $token, 
                        '📦 Cucian Sudah Sampai!', 
                        "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah tiba di laundry. Admin sedang memeriksa cucian Anda.", 
                        [
                            'transaksi_id' => (string) $pesanan->id_transaksi, 
                            'type' => 'arrived_at_laundry', 
                            'action' => 'open_detail'
                        ]
                    );
                }
            }
        }
    });

    return redirect()
        ->route('kasir.pesanan.online.detail', $id)
        ->with('success', 'Driver sampai laundry. Silakan isi data pesanan untuk mengirim invoice.');
}

   public function driverArriveAtLaundryAdmin2($id)
{
    DB::transaction(function () use ($id) {
        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'pick_up')
            ->findOrFail($id);

        // ❌ HAPUS BARIS INI!
        // $pesanan->update(['status_transaksi' => 'antrian']);

        // ✅ HANYA update delivery status
        Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->update(['status' => 'arrived_at_laundry']);
        
        // 🔔 FCM NOTIF
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                foreach ($tokens as $token) {
                    FcmService::send(
                        $token, 
                        '📦 Cucian Sudah Sampai!', 
                        "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah tiba di laundry. Admin sedang memeriksa cucian Anda.", 
                        [
                            'transaksi_id' => (string) $pesanan->id_transaksi, 
                            'type' => 'arrived_at_laundry', 
                            'action' => 'open_detail'
                        ]
                    );
                }
            }
        }
    });

    return redirect()
        ->route('admin2.pesanan.online.detail', $id)
        ->with('success', 'Driver sampai laundry. Silakan isi data pesanan untuk mengirim invoice.');
}

   // ==================== KASIR ====================
public function indexKasir(Request $request)
{
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
        'biayaTambahan'
    ])->findOrFail($id);


     // ✅ Master list biaya tambahan (untuk dropdown)
    $masterBiayaTambahan = \DB::table('biaya_tambahan')
        ->select('nama_biaya', 'nominal')
        ->whereNull('id_transaksi') // Hanya yang master/template
        ->orWhere('id_transaksi', 0)
        ->distinct()
        ->get();

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
    
    return view('kasir.pesanan_online.detail', compact('pesanan',  'deliveryPickup', 'deliveryAntar'));
    // ✅ SUDAH ADA di compact!
}
    
    public function terimaKasir($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'dikonfirmasi']);
    
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '✅ Pesanan Dikonfirmasi!', 
                    "Pesanan Anda (ORDER/{$pesanan->id_transaksi}) telah dikonfirmasi dan akan segera diproses.", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'dikonfirmasi']);
            }
        }
    }
    
    return redirect()->route('kasir.pesanan.online.index', ['tab' => 'dikonfirmasi'])->with('success', 'Pesanan berhasil diterima');
}

    
public function tolakKasir($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'ditolak']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '❌ Pesanan Ditolak', 
                    "Maaf, pesanan Anda (ORDER/{$pesanan->id_transaksi}) tidak dapat kami proses. Silakan hubungi CS kami.", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'ditolak', 'action' => 'open_detail']);
            }
        }
    }
    
    return redirect()->route('kasir.pesanan.online.index', ['tab' => 'ditolak'])
        ->with('success', 'Pesanan ditolak');
}
    

/**
 * KASIR - Proses Pesanan
 */
public function prosesKasir($id)
{
    try {
        DB::beginTransaction();
        
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        
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
                    'status' => 'accepted',
                    'waktu' => now(),
                    'catatan' => 'Auto-generated: Pesanan melewati estimasi saat proses',
                ]);
            }
            
            // 🔔 FCM NOTIF - TERLAMBAT
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send($token, '⚠️ Pesanan Melewati Estimasi!', 
                            "Maaf, cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati estimasi waktu dan akan segera diantar.", 
                            ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'terlambat', 'action' => 'open_detail']);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()
                ->route('kasir.pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk delivery.');
        }
        
        $pesanan->update(['status_transaksi' => 'proses']);
        
        // 🔔 FCM NOTIF - PROSES
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                foreach ($tokens as $token) {
                    FcmService::send($token, '🧺 Cucian Sedang Diproses!', 
                        "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sedang dalam proses pencucian.", 
                        ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'proses', 'action' => 'open_detail']);
                }
            }
        }
        
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
    
// ✅ 4. siapDiAmbilKasir - TAMBAH action: 'open_detail'
public function siapDiAmbilKasir($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '✨ Cucian Siap Diambil!', 
                    "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah siap diambil di laundry kami. Ditunggu ya!", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'siap_di_ambil', 'action' => 'open_detail']);
            }
        }
    }
    
    return redirect()->route('kasir.pesanan.online.index', ['tab' => 'siap_di_ambil'])
        ->with('success', 'Pesanan siap diambil');
}
    
public function selesaiKasir($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'selesai']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '🎊 Pesanan Selesai!', 
                    "Terima kasih! Pesanan Anda (ORDER/{$pesanan->id_transaksi}) telah selesai. Sampai jumpa lagi!", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'selesai', 'action' => 'open_detail']);
            }
        }
    }
    
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

     // ✅ Master list biaya tambahan (untuk dropdown)
    $masterBiayaTambahan = \DB::table('biaya_tambahan')
        ->select('nama_biaya', 'nominal')
        ->whereNull('id_transaksi') // Hanya yang master/template
        ->orWhere('id_transaksi', 0)
        ->distinct()
        ->get();

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
    
    return view('pesanan_online.detail', compact('pesanan', 'deliveryPickup','deliveryAntar'));
}
    
   public function terima($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'dikonfirmasi']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '✅ Pesanan Dikonfirmasi!', 
                    "Pesanan Anda (ORDER/{$pesanan->id_transaksi}) telah dikonfirmasi dan akan segera diproses.", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'dikonfirmasi', 'action' => 'open_detail']);
            }
        }
    }
    
    return redirect()->route('pesanan.online.index', ['tab' => 'dikonfirmasi'])
        ->with('success', 'Pesanan berhasil diterima');

}

public function proses($id)
{
    try {
        DB::beginTransaction();
        
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        
        // ✅ CEK APAKAH TERLAMBAT
        if ($this->isPesananTerlambat($pesanan)) {
            // ❌ TERLAMBAT → LANGSUNG SIAP DI ANTAR
            $pesanan->update(['status_transaksi' => 'siap_di_antar']);
            
            // Buat delivery antar jika belum ada
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'antar')
                ->first();
            
            if (!$existingDelivery) {
                Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi,
                    'id_driver' => null,
                    'jenis' => 'antar',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted',
                    'waktu' => now(),
                    'catatan' => 'Auto-generated: Pesanan melewati estimasi saat proses',
                ]);
            }
            
            // 🔔 FCM NOTIF - TERLAMBAT
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send($token, '⚠️ Pesanan Melewati Estimasi!', 
                            "Maaf, cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati estimasi waktu dan akan segera diantar.", 
                            ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'terlambat', 'action' => 'open_detail']);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()
                ->route('pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk delivery.');
        }
        
        // ✅ TIDAK TERLAMBAT → UPDATE KE PROSES
        $pesanan->update(['status_transaksi' => 'proses']);
        
        // 🔔 FCM NOTIF - PROSES
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                foreach ($tokens as $token) {
                    FcmService::send($token, '🧺 Cucian Sedang Diproses!', 
                        "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sedang dalam proses pencucian.", 
                        ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'proses', 'action' => 'open_detail']);
                }
            }
        }
        
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
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '✨ Cucian Siap Diambil!', 
                    "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah siap diambil di laundry kami. Ditunggu ya!", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'siap_di_ambil', 'action' => 'open_detail']);
            }
        }
    }
    
    return redirect()->route('pesanan.online.index', ['tab' => 'siap_di_ambil'])
        ->with('success', 'Pesanan siap diambil');
}
    
public function selesai($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'selesai']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '🎊 Pesanan Selesai!', 
                    "Terima kasih! Pesanan Anda (ORDER/{$pesanan->id_transaksi}) telah selesai. Sampai jumpa lagi!", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'selesai', 'action' => 'open_detail']);
            }
        }
    }
    
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
public function indexAdmin2(Request $request)
{
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

     // ✅ Master list biaya tambahan (untuk dropdown)
    $masterBiayaTambahan = \DB::table('biaya_tambahan')
        ->select('nama_biaya', 'nominal')
        ->whereNull('id_transaksi') // Hanya yang master/template
        ->orWhere('id_transaksi', 0)
        ->distinct()
        ->get();

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
    
    return view('admin2.pesanan_online.detail', compact('pesanan', 'deliveryPickup', 'deliveryAntar'));
    // ✅ SUDAH ADA di compact!
}
    
    public function terimaAdmin2($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'dikonfirmasi']);
        
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                foreach ($tokens as $token) {
                    FcmService::send($token, '✅ Pesanan Dikonfirmasi!', 
                        "Pesanan Anda (ORDER/{$pesanan->id_transaksi}) telah dikonfirmasi dan akan segera diproses.", 
                        ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'dikonfirmasi', 'action' => 'open_detail']);
                }
            }
        }

    return redirect()->route('admin2.pesanan.online.index', ['tab' => 'dikonfirmasi'])
        ->with('success', 'Pesanan berhasil diterima');
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

    public function tolakAdmin2($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'ditolak']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '❌ Pesanan Ditolak', 
                    "Maaf, pesanan Anda (ORDER/{$pesanan->id_transaksi}) tidak dapat kami proses. Silakan hubungi CS kami.", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'ditolak', 'action' => 'open_detail']);
            }
        }
    }

    return redirect()->route('admin2.pesanan.online.index', ['tab' => 'ditolak'])
        ->with('success', 'Pesanan ditolak');
}

/**
 * ADMIN2 - Proses Pesanan
 */
public function prosesAdmin2($id)
{
    try {
        DB::beginTransaction();
        
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        
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
                    'status' => 'accepted',
                    'waktu' => now(),
                    'catatan' => 'Auto-generated: Pesanan melewati estimasi saat proses',
                ]);
            }
            
            // 🔔 FCM NOTIF - TERLAMBAT
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send($token, '⚠️ Pesanan Melewati Estimasi!', 
                            "Maaf, cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati estimasi waktu dan akan segera diantar.", 
                            ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'terlambat', 'action' => 'open_detail']);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin2.pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver.');
        }
        
        $pesanan->update(['status_transaksi' => 'proses']);
        
        // 🔔 FCM NOTIF - PROSES
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                foreach ($tokens as $token) {
                    FcmService::send($token, '🧺 Cucian Sedang Diproses!', 
                        "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sedang dalam proses pencucian.", 
                        ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'proses', 'action' => 'open_detail']);
                }
            }
        }
        
        DB::commit();
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error prosesAdmin2: " . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
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
    
    // ✅ Parse tanggal estimasi dan set ke AKHIR HARI (23:59:59)
    $estimasi = \Carbon\Carbon::parse($pesanan->tgl_estimasi)->endOfDay();
    $now = \Carbon\Carbon::now();
    
    // ✅ Terlambat HANYA jika waktu sekarang LEBIH DARI estimasi
    $isTerlambat = $now->greaterThan($estimasi);
    
    // ✅ LOG untuk debugging
    \Log::info("🔍 Check Terlambat - Pesanan {$pesanan->id_transaksi}:");
    \Log::info("   📅 Estimasi: {$estimasi->format('Y-m-d H:i:s')}");
    \Log::info("   🕐 Sekarang: {$now->format('Y-m-d H:i:s')}");
    \Log::info("   " . ($isTerlambat ? "❌ TERLAMBAT" : "✅ BELUM TERLAMBAT"));
    
    return $isTerlambat;
}

   public function siapDiAmbilAdmin2($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '✨ Cucian Siap Diambil!', 
                    "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah siap diambil di laundry kami. Ditunggu ya!", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'siap_di_ambil', 'action' => 'open_detail']);
            }
        }
    }

    return redirect()->route('admin2.pesanan.online.index', ['tab' => 'siap_di_ambil'])
        ->with('success', 'Pesanan siap diambil');
}
    
public function selesaiAdmin2($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'selesai']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '🎊 Pesanan Selesai!', 
                    "Terima kasih! Pesanan Anda (ORDER/{$pesanan->id_transaksi}) telah selesai. Sampai jumpa lagi!", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'selesai', 'action' => 'open_detail']);
            }
        }
    }

    return redirect()->route('admin2.pesanan.online.index', ['tab' => 'selesai'])
        ->with('success', 'Pesanan selesai');
}
    
        public function siapDiAntar($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'siap_di_antar']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '🚚 Cucian Siap Diantar!', 
                    "Cucian Anda (ORDER/{$pesanan->id_transaksi}) akan segera diantar oleh driver kami.", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'siap_di_antar', 'action' => 'open_detail']);
            }
        }
    }
    
    return redirect()->route('pesanan.online.index', ['tab' => 'siap_di_antar'])
        ->with('success', 'Pesanan siap diantar');
}

public function siapDiAntarKasir($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'siap_di_antar']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '🚚 Cucian Siap Diantar!', 
                    "Cucian Anda (ORDER/{$pesanan->id_transaksi}) akan segera diantar oleh driver kami.", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'siap_di_antar', 'action' => 'open_detail']);
            }
        }
    }
    
    return redirect()->route('kasir.pesanan.online.index', ['tab' => 'siap_di_antar'])
        ->with('success', 'Pesanan siap diantar');
}

    public function siapDiAntarAdmin2($id)
{
    $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
    $pesanan->update(['status_transaksi' => 'siap_di_antar']);
    
    // 🔔 FCM NOTIF
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                FcmService::send($token, '🚚 Cucian Siap Diantar!', 
                    "Cucian Anda (ORDER/{$pesanan->id_transaksi}) akan segera diantar oleh driver kami.", 
                    ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'siap_di_antar', 'action' => 'open_detail']);
            }
        }
    }

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
    $deliveryAntar = null;
    
    // ✅ LOGIKA YANG BENAR:
    // 1. Jika status = pick_up → PASTI PICKUP
    if ($pesanan->status_transaksi === 'pick_up') {
        $isPickup = true;
        
        // Cek apakah sudah ada delivery pickup (untuk info aja)
        $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->first(); // ✅ Tidak perlu filter status!
    }
    
    // 2. Jika status = siap_di_antar → PASTI DELIVERY (ANTAR)
    elseif ($pesanan->status_transaksi === 'siap_di_antar') {
        $isPickup = false;
        
        // Cek apakah sudah ada delivery antar (untuk info aja)
        $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'antar')
            ->first();
    }
    
    // 3. Status lain → Default ke pickup (fallback)
    else {
        $isPickup = true;
    }
    
    $drivers = Driver::where('status', 'aktif')->get();
    
    return view('pesanan_online.listonlinedriver', compact(
        'pesanan', 
        'drivers', 
        'isPickup', 
        'pickupDelivery',
        'deliveryAntar'
    ));
}


    public function listDriverKasir($id)
{
    $pesanan = Transaksi::with(['pelanggan', 'delivery'])
        ->where('jenis_transaksi', 'online')
        ->findOrFail($id);
    
    $isPickup = false;
    $pickupDelivery = null;
    $deliveryAntar = null;
    
    if ($pesanan->status_transaksi === 'pick_up') {
        $isPickup = true;
        $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->first();
    } elseif ($pesanan->status_transaksi === 'siap_di_antar') {
        $isPickup = false;
        $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'antar')
            ->first();
    } else {
        $isPickup = true;
    }
    
    $drivers = Driver::where('status', 'aktif')->get();
    
    return view('kasir.pesanan_online.listonlinedriver', compact(
        'pesanan', 
        'drivers', 
        'isPickup', 
        'pickupDelivery',
        'deliveryAntar'
    ));
}


   public function listDriverAdmin2($id)
{
    $pesanan = Transaksi::with(['pelanggan', 'delivery'])
        ->where('jenis_transaksi', 'online')
        ->findOrFail($id);
    
    $isPickup = false;
    $pickupDelivery = null;
    $deliveryAntar = null;
    
    if ($pesanan->status_transaksi === 'pick_up') {
        $isPickup = true;
        $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->first();
    } elseif ($pesanan->status_transaksi === 'siap_di_antar') {
        $isPickup = false;
        $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'antar')
            ->first();
    } else {
        $isPickup = true;
    }
    
    $drivers = Driver::where('status', 'aktif')->get();
    
    return view('admin2.pesanan_online.listonlinedriver', compact(
        'pesanan', 
        'drivers', 
        'isPickup', 
        'pickupDelivery',
        'deliveryAntar'
    ));
}

// ==================== ASSIGN DRIVER PICKUP ====================

 public function assignDriverPickup(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->first();
            
            $driver = Driver::find($request->id_driver);
            
            // ✅ CREATE atau UPDATE delivery - STATUS ACCEPTED
            if (!$pickupDelivery) {
                $pickupDelivery = Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi,
                    'id_driver' => $request->id_driver,
                    'jenis' => 'pickup',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted', // ✅ FIXED: langsung accepted
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
                
                Log::info("✅ Created new pickup delivery with status ACCEPTED for transaksi {$pesanan->id_transaksi}");
            } else {
                $pickupDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status' => 'accepted', // ✅ FIXED: langsung accepted
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
                
                Log::info("✅ Updated existing pickup delivery to ACCEPTED for transaksi {$pesanan->id_transaksi}");
            }

            // 🔔 FCM NOTIF - KE PELANGGAN
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token, 
                            '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk pickup cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi, 
                                'type' => 'driver_assigned_pickup', 
                                'action' => 'open_detail'
                            ]
                        );
                    }
                    Log::info("✅ FCM sent to customer");
                }
            }

            // ✅ 🔔 FCM NOTIF - KE DRIVER
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)
                ->pluck('token')
                ->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send(
                            $token,
                            '📦 Tugas Pickup Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Pickup dari {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi' => (string) $pesanan->id_transaksi,
                                'id_delivery' => (string) $pickupDelivery->id_delivery,
                                'jenis' => 'pickup', // ✅ CRITICAL!
                                'type' => 'new_pickup_task', // ✅ CRITICAL!
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp' => $noHp,
                                'alamat' => $alamat,
                                'catatan' => $request->catatan_driver ?? '',
                                'action' => 'open_delivery_detail'
                            ]
                        );
                        
                        Log::info("✅ FCM sent to driver {$driver->nama_driver} (ID: {$driver->id_driver})");
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
            } else {
                Log::warning("⚠️ Driver {$driver->nama_driver} (ID: {$driver->id_driver}) tidak punya FCM token!");
            }

            DB::commit();
            
            return redirect()->route('pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditugaskan & notifikasi terkirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver pickup: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
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

            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->first();
            
            $driver = Driver::find($request->id_driver);
            
            if (!$pickupDelivery) {
                $pickupDelivery = Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi,
                    'id_driver' => $request->id_driver,
                    'jenis' => 'pickup',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
                
                Log::info("✅ Created new pickup delivery with status ACCEPTED for transaksi {$pesanan->id_transaksi}");
            } else {
                $pickupDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
                
                Log::info("✅ Updated existing pickup delivery to ACCEPTED for transaksi {$pesanan->id_transaksi}");
            }

            // 🔔 FCM NOTIF - KE PELANGGAN
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token, 
                            '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk pickup cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi, 
                                'type' => 'driver_assigned_pickup', 
                                'action' => 'open_detail'
                            ]
                        );
                    }
                    Log::info("✅ FCM sent to customer");
                }
            }

            // ✅ 🔔 FCM NOTIF - KE DRIVER
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)
                ->pluck('token')
                ->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send(
                            $token,
                            '📦 Tugas Pickup Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Pickup dari {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi' => (string) $pesanan->id_transaksi,
                                'id_delivery' => (string) $pickupDelivery->id_delivery,
                                'jenis' => 'pickup',
                                'type' => 'new_pickup_task',
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp' => $noHp,
                                'alamat' => $alamat,
                                'catatan' => $request->catatan_driver ?? '',
                                'action' => 'open_delivery_detail'
                            ]
                        );
                        
                        Log::info("✅ FCM sent to driver {$driver->nama_driver} (ID: {$driver->id_driver})");
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
            } else {
                Log::warning("⚠️ Driver {$driver->nama_driver} (ID: {$driver->id_driver}) tidak punya FCM token!");
            }

            DB::commit();
            
            return redirect()->route('kasir.pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditugaskan & notifikasi terkirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver pickup: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
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

            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->first();
            
            $driver = Driver::find($request->id_driver);
            
            if (!$pickupDelivery) {
                $pickupDelivery = Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi,
                    'id_driver' => $request->id_driver,
                    'jenis' => 'pickup',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
                
                Log::info("✅ Created new pickup delivery with status ACCEPTED for transaksi {$pesanan->id_transaksi}");
            } else {
                $pickupDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
                
                Log::info("✅ Updated existing pickup delivery to ACCEPTED for transaksi {$pesanan->id_transaksi}");
            }

            // 🔔 FCM NOTIF - KE PELANGGAN
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token, 
                            '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk pickup cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi, 
                                'type' => 'driver_assigned_pickup', 
                                'action' => 'open_detail'
                            ]
                        );
                    }
                    Log::info("✅ FCM sent to customer");
                }
            }

            // ✅ 🔔 FCM NOTIF - KE DRIVER
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)
                ->pluck('token')
                ->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send(
                            $token,
                            '📦 Tugas Pickup Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Pickup dari {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi' => (string) $pesanan->id_transaksi,
                                'id_delivery' => (string) $pickupDelivery->id_delivery,
                                'jenis' => 'pickup',
                                'type' => 'new_pickup_task',
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp' => $noHp,
                                'alamat' => $alamat,
                                'catatan' => $request->catatan_driver ?? '',
                                'action' => 'open_delivery_detail'
                            ]
                        );
                        
                        Log::info("✅ FCM sent to driver {$driver->nama_driver} (ID: {$driver->id_driver})");
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
            } else {
                Log::warning("⚠️ Driver {$driver->nama_driver} (ID: {$driver->id_driver}) tidak punya FCM token!");
            }

            DB::commit();
            
            return redirect()->route('admin2.pesanan.online.detail', $id)
                ->with('success', 'Driver pickup berhasil ditugaskan & notifikasi terkirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver pickup: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
        }
    }

    // ==================== ASSIGN DRIVER ANTAR - FIXED STATUS ====================

    public function assignDriverAntar(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'antar')
                ->first();

            $driver = Driver::find($request->id_driver);

            if ($existingDelivery) {
                $existingDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
                Log::info("✅ Updated existing delivery antar to ACCEPTED for transaksi {$pesanan->id_transaksi}");
            } else {
                $existingDelivery = Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi,
                    'id_driver' => $request->id_driver,
                    'jenis' => 'antar',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
                Log::info("✅ Created new delivery antar with status ACCEPTED for transaksi {$pesanan->id_transaksi}");
            }

            // 🔔 FCM NOTIF - KE PELANGGAN
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token, 
                            '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk mengantar cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi,
                                'type' => 'driver_assigned_delivery',
                                'action' => 'open_detail',
                            ]
                        );
                    }
                    Log::info("✅ FCM sent to customer");
                }
            }

            // ✅ 🔔 FCM NOTIF - KE DRIVER
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)
                ->pluck('token')
                ->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send(
                            $token,
                            '🚚 Tugas Delivery Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Antar ke {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi' => (string) $pesanan->id_transaksi,
                                'id_delivery' => (string) $existingDelivery->id_delivery,
                                'jenis' => 'antar', // ✅ CRITICAL!
                                'type' => 'new_delivery_task', // ✅ CRITICAL!
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp' => $noHp,
                                'alamat' => $alamat,
                                'catatan' => $request->catatan_driver ?? '',
                                'action' => 'open_delivery_detail'
                            ]
                        );
                        
                        Log::info("✅ FCM sent to driver {$driver->nama_driver} (ID: {$driver->id_driver})");
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
            } else {
                Log::warning("⚠️ Driver {$driver->nama_driver} (ID: {$driver->id_driver}) tidak punya FCM token!");
            }

            DB::commit();

            return redirect()
                ->route('pesanan.online.detail', $id)
                ->with('success', 'Driver delivery berhasil ditugaskan & notifikasi terkirim!');

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

            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'antar')
                ->first();

            $driver = Driver::find($request->id_driver);

            if ($existingDelivery) {
                $existingDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
            } else {
                $existingDelivery = Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi,
                    'id_driver' => $request->id_driver,
                    'jenis' => 'antar',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
            }

            // 🔔 FCM NOTIF - KE PELANGGAN
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token, 
                            '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk mengantar cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi,
                                'type' => 'driver_assigned_delivery',
                                'action' => 'open_detail',
                            ]
                        );
                    }
                }
            }

            // ✅ 🔔 FCM NOTIF - KE DRIVER
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)
                ->pluck('token')
                ->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send(
                            $token,
                            '🚚 Tugas Delivery Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Antar ke {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi' => (string) $pesanan->id_transaksi,
                                'id_delivery' => (string) $existingDelivery->id_delivery,
                                'jenis' => 'antar',
                                'type' => 'new_delivery_task',
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp' => $noHp,
                                'alamat' => $alamat,
                                'catatan' => $request->catatan_driver ?? '',
                                'action' => 'open_delivery_detail'
                            ]
                        );
                        
                        Log::info("✅ FCM sent to driver {$driver->nama_driver} (ID: {$driver->id_driver})");
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
            } else {
                Log::warning("⚠️ Driver {$driver->nama_driver} (ID: {$driver->id_driver}) tidak punya FCM token!");
            }

            DB::commit();

            return redirect()->route('kasir.pesanan.online.detail', $id)
                ->with('success', 'Driver delivery berhasil ditugaskan & notifikasi terkirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver antar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
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

            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'antar')
                ->first();

            $driver = Driver::find($request->id_driver);

            if ($existingDelivery) {
                $existingDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
            } else {
                $existingDelivery = Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi,
                    'id_driver' => $request->id_driver,
                    'jenis' => 'antar',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted', // ✅ FIXED
                    'waktu' => now(),
                    'catatan' => $request->catatan_driver,
                ]);
            }

            // 🔔 FCM NOTIF - KE PELANGGAN
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token, 
                            '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk mengantar cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi,
                                'type' => 'driver_assigned_delivery',
                                'action' => 'open_detail',
                            ]
                        );
                    }
                }
            }

            // ✅ 🔔 FCM NOTIF - KE DRIVER
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)
                ->pluck('token')
                ->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send(
                            $token,
                            '🚚 Tugas Delivery Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Antar ke {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi' => (string) $pesanan->id_transaksi,
                                'id_delivery' => (string) $existingDelivery->id_delivery,
                                'jenis' => 'antar',
                                'type' => 'new_delivery_task',
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp' => $noHp,
                                'alamat' => $alamat,
                                'catatan' => $request->catatan_driver ?? '',
                                'action' => 'open_delivery_detail'
                            ]
                        );
                        
                        Log::info("✅ FCM sent to driver {$driver->nama_driver} (ID: {$driver->id_driver})");
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
            } else {
                Log::warning("⚠️ Driver {$driver->nama_driver} (ID: {$driver->id_driver}) tidak punya FCM token!");
            }

            DB::commit();

            return redirect()->route('admin2.pesanan.online.detail', $id)
                ->with('success', 'Driver delivery berhasil ditugaskan & notifikasi terkirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver antar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
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
// ==================== ✅ FINAL FIXED updateData() METHOD ====================
/**
 * ===============================================
 * ✅ FIXED UPDATE DATA METHOD - NO SYNTAX ERROR
 * ===============================================
 * 
 * Copy method ini ke PesananOnlineController.php
 * Replace method updateData() yang ada
 */

public function updateData(Request $request, $id)
{
    Log::info("🔥 updateData CALLED - Order ID: " . $id);
    
    $request->validate([
        'id_detail.*'        => 'required|exists:detail_transaksi,id_detail_transaksi',
        'qty.*'              => 'required|numeric|min:0.01',
        'diskon'             => 'nullable|numeric|min:0',
        'tipe_diskon'        => 'nullable|in:nominal,percent',
        'keterangan'         => 'nullable|string',
        'foto_bukti'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        'tgl_estimasi'       => 'nullable|date|after_or_equal:today',
        'new_biaya_nama.*'   => 'nullable|string|max:255',
        'new_biaya_nominal.*' => 'nullable|numeric|min:0',
        'delete_biaya_id.*'  => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
    ]);

    try {
        DB::beginTransaction();
        
        $pesanan = Transaksi::with(['pelanggan'])->findOrFail($id);
        $statusAwal = $pesanan->status_transaksi;

        // ========================================
        // STEP 1: UPDATE QTY & HITUNG SUBTOTAL
        // ========================================
        $subtotalItems = 0;

        foreach ($request->id_detail as $index => $idDetail) {
            $detail = DetailTransaksi::findOrFail($idDetail);
            
            $qty = (float) $request->qty[$index];
            
            // ✅ CRITICAL FIX: Ambil harga dari field, bukan relasi!
            $harga = (float) $detail->harga;
            
            Log::info("Detail " . $idDetail . ": qty=" . $qty . ", harga=" . $harga);
            
            // Jika harga 0, coba ambil dari layanan
            if ($harga <= 0) {
                if ($detail->id_layanan) {
                    $layanan = \App\Models\Layanan::find($detail->id_layanan);
                    if ($layanan) {
                        $harga = (float) $layanan->harga;
                        Log::info("  Harga dari layanan: " . $harga);
                    }
                }
                
                if ($harga <= 0 && $detail->id_jenis_layanan) {
                    $jenisLayanan = \App\Models\JenisLayanan::find($detail->id_jenis_layanan);
                    if ($jenisLayanan) {
                        $harga = (float) $jenisLayanan->harga;
                        Log::info("  Harga dari jenis_layanan: " . $harga);
                    }
                }
                
                if ($harga <= 0) {
                    DB::rollBack();
                    $itemNumber = $index + 1;
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', '❌ Harga item #' . $itemNumber . ' tidak valid!');
                }
            }
            
            $detail->update([
                'qty' => $qty,
                'harga' => $harga,
            ]);
            
            $subtotal = round($qty * $harga, 0);
            $subtotalItems += $subtotal;
            
            Log::info("  Subtotal: " . $qty . " × " . $harga . " = " . $subtotal);
        }

        Log::info("💰 Total Subtotal: Rp " . number_format($subtotalItems, 0, ',', '.'));

        if ($subtotalItems <= 0) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '❌ Subtotal tidak boleh Rp 0!');
        }

        // ========================================
        // STEP 2: BIAYA TAMBAHAN
        // ========================================
        if ($request->filled('delete_biaya_id')) {
            $deleted = BiayaTambahan::whereIn('id_biaya_tambahan', $request->delete_biaya_id)->delete();
            Log::info("Deleted " . $deleted . " biaya tambahan");
        }

        if ($request->filled('new_biaya_nama') && $request->filled('new_biaya_nominal')) {
            foreach ($request->new_biaya_nama as $index => $nama) {
                $nominal = (float) ($request->new_biaya_nominal[$index] ?? 0);
                
                if (empty($nama) || $nominal <= 0) {
                    continue;
                }
                
                BiayaTambahan::create([
                    'id_transaksi' => $pesanan->id_transaksi,
                    'nama_biaya' => $nama,
                    'nominal' => $nominal,
                ]);
                
                Log::info("Added biaya: " . $nama . " = Rp " . $nominal);
            }
        }

        $biayaTambahan = (float) BiayaTambahan::where('id_transaksi', $pesanan->id_transaksi)
            ->sum('nominal');
        
        Log::info("💵 Biaya Tambahan: Rp " . number_format($biayaTambahan, 0, ',', '.'));

        // ========================================
        // STEP 3: DISKON
        // ========================================
        $diskon = (float) ($request->diskon ?? 0);
        $tipeDiskon = $request->tipe_diskon ?? 'nominal';

        if ($tipeDiskon === 'percent' && $diskon > 0) {
            $diskonPersen = $diskon;
            $diskon = round(($subtotalItems * $diskon) / 100, 0);
            Log::info("🏷️ Diskon: " . $diskonPersen . "% = Rp " . number_format($diskon, 0, ',', '.'));
        } else {
            Log::info("🏷️ Diskon: Rp " . number_format($diskon, 0, ',', '.'));
        }

        // ========================================
        // STEP 4: TOTAL AKHIR
        // ========================================
        $totalAkhir = $subtotalItems + $biayaTambahan - $diskon;
        
        Log::info("🧮 ==========================================");
        Log::info("   Subtotal:        Rp " . number_format($subtotalItems, 0, ',', '.'));
        Log::info("   Biaya Tambahan:  Rp " . number_format($biayaTambahan, 0, ',', '.'));
        Log::info("   Diskon:          Rp " . number_format($diskon, 0, ',', '.'));
        Log::info("   TOTAL AKHIR:     Rp " . number_format($totalAkhir, 0, ',', '.'));
        Log::info("🧮 ==========================================");

        // ========================================
        // STEP 5: FOTO BUKTI
        // ========================================
        $fotoBuktiPath = $pesanan->foto_bukti;
        if ($request->hasFile('foto_bukti')) {
            if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
                Storage::disk('public')->delete($pesanan->foto_bukti);
            }
            $file = $request->file('foto_bukti');
            $filename = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
            $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
            Log::info("Foto bukti uploaded: " . $fotoBuktiPath);
        }

        // ========================================
        // STEP 6: STATUS LOGIC
        // ========================================
        $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->where('status', 'arrived_at_laundry')
            ->first();

        $statusBaru = $statusAwal;

        if ($statusAwal === 'pick_up' && $deliveryPickup) {
            $statusBaru = 'antrian';
            Log::info("Status changed: pick_up → antrian");
        }

        $needDriverRedirect = false;
        if ($statusAwal === 'selesai_dicuci' && $this->isPesananTerlambat($pesanan)) {
            $statusBaru = 'siap_di_antar';
            $needDriverRedirect = true;
            
            Log::info("Pesanan terlambat! Status: selesai_dicuci → siap_di_antar");
            
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
                    'catatan' => 'Auto-generated: Melewati estimasi',
                ]);
                Log::info("Created delivery antar");
            }
        }

        // ========================================
        // STEP 7: UPDATE TRANSAKSI
        // ========================================
        $updateData = [
            'total_harga'      => $totalAkhir,
            'diskon'           => $diskon,
            'tipe_diskon'      => $tipeDiskon,
            'keterangan'       => $request->keterangan,
            'status_transaksi' => $statusBaru,
            'foto_bukti'       => $fotoBuktiPath,
        ];

        if ($request->filled('tgl_estimasi')) {
            $updateData['tgl_estimasi'] = $request->tgl_estimasi;
        }

        Log::info("Updating transaksi...");
        $pesanan->update($updateData);

        // ========================================
        // STEP 8: VERIFY
        // ========================================
        $pesanan->refresh();
        
        Log::info("VERIFICATION:");
        Log::info("  DB total_harga: Rp " . number_format($pesanan->total_harga, 0, ',', '.'));
        Log::info("  Expected:       Rp " . number_format($totalAkhir, 0, ',', '.'));
        
        if ($pesanan->total_harga != $totalAkhir) {
            Log::warning("MISMATCH! Force updating...");
            
            DB::table('transaksi')
                ->where('id_transaksi', $pesanan->id_transaksi)
                ->update([
                    'total_harga' => $totalAkhir,
                    'diskon' => $diskon,
                ]);
            
            $pesanan->refresh();
            Log::info("  After force: Rp " . number_format($pesanan->total_harga, 0, ',', '.'));
        }

        // ========================================
        // STEP 9: FCM NOTIFICATION
        // ========================================
        $fcmSent = false;
        $fcmMessage = '';
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
                ->whereNotNull('token')
                ->pluck('token')
                ->toArray();
            
            if (!empty($tokens)) {
                if ($needDriverRedirect) {
                    $title = '⚠️ Pesanan Melewati Estimasi!';
                    $body = "ORDER/" . $pesanan->id_transaksi . " melewati estimasi. Total: Rp " . number_format($totalAkhir, 0, ',', '.');
                } else {
                    $title = '💰 Harga Pesanan Sudah Diisi!';
                    $body = "ORDER/" . $pesanan->id_transaksi . " - Total: Rp " . number_format($totalAkhir, 0, ',', '.');
                }

                foreach ($tokens as $token) {
                    try {
                        FcmService::send($token, $title, $body, [
                            'transaksi_id' => (string) $pesanan->id_transaksi,
                            'type' => $needDriverRedirect ? 'terlambat' : 'invoice',
                            'action' => 'open_detail',
                            'total_harga' => (string) $totalAkhir,
                        ]);
                        $fcmSent = true;
                    } catch (\Exception $e) {
                        Log::error("FCM Error: " . $e->getMessage());
                    }
                }
            } else {
                $fcmMessage = ' (Notifikasi tidak terkirim: token tidak ditemukan)';
            }
        } else {
            $fcmMessage = ' (Notifikasi tidak terkirim: pelanggan tidak ditemukan)';
        }

        DB::commit();
        Log::info("✅ updateData SUCCESS - Total: Rp " . $totalAkhir);

        // ========================================
        // STEP 10: REDIRECT
        // ========================================
        if ($needDriverRedirect) {
            return redirect()
                ->route('pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver.');
        }

        $successMessage = 'Data pesanan berhasil diperbarui' . ($fcmSent ? ' & notifikasi terkirim!' : $fcmMessage);
        
        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', $successMessage);
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("❌ updateData ERROR");
        Log::error("Error: " . $e->getMessage());
        Log::error("File: " . $e->getFile() . ":" . $e->getLine());
        
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
    }
}
/**
 * ✅ FIXED updateDataKasir() - KASIR
 */
public function updateDataKasir(Request $request, $id)
{
    $request->validate([
        'id_detail.*'        => 'required|exists:detail_transaksi,id_detail_transaksi',
        'qty.*'              => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/',
        'diskon'             => 'nullable|numeric|min:0',
        'tipe_diskon'        => 'nullable|in:nominal,percent',
        'keterangan'         => 'nullable|string',
        'foto_bukti'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        'tgl_estimasi'       => 'nullable|date|after_or_equal:today',
        'new_biaya_nama.*'   => 'nullable|string|max:255',
        'new_biaya_nominal.*' => 'nullable|numeric|min:0',
        'delete_biaya_id.*'  => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
    ]);

    $pesanan = Transaksi::with(['pelanggan'])
        ->where('jenis_transaksi', 'online')
        ->findOrFail($id);

    $subtotalItems = 0;

    foreach ($request->id_detail as $index => $idDetail) {
        // ✅ FIX: Use 'layanan' instead of 'jenis'
        $detail = DetailTransaksi::with('layanan')->findOrFail($idDetail);

        if (!$detail->layanan) {
            Log::warning("⚠️ Detail ID {$idDetail} tidak punya relasi layanan!");
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "❌ Data layanan untuk detail pesanan tidak ditemukan! Silakan hubungi admin.");
        }

        $qty      = (float) $request->qty[$index];
        $harga    = (float) $detail->layanan->harga;
        $idSatuan = $detail->id_satuan;
        $subtotal = round($qty * $harga, 0);

        $subtotalItems += $subtotal;

        $updateData = [
            'qty'       => $qty,
            'harga'     => $harga,
            'id_satuan' => $idSatuan,
            'subtotal'  => $subtotal,
        ];

        if ($request->filled('tgl_estimasi')) {
            $updateData['tgl_estimasi'] = $request->tgl_estimasi;
        }

        $detail->update($updateData);
    }

    // STEP 2-5 (same as updateData)
    if ($request->filled('delete_biaya_id')) {
        BiayaTambahan::whereIn('id_biaya_tambahan', $request->delete_biaya_id)->delete();
        Log::info("✅ Deleted biaya tambahan: " . implode(', ', $request->delete_biaya_id));
    }

    if ($request->filled('new_biaya_nama') && $request->filled('new_biaya_nominal')) {
        foreach ($request->new_biaya_nama as $index => $nama) {
            $nominal = $request->new_biaya_nominal[$index] ?? 0;
            if (empty($nama) || $nominal <= 0) continue;
            
            BiayaTambahan::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'nama_biaya' => $nama,
                'nominal' => $nominal,
            ]);
            
            Log::info("✅ Created biaya tambahan: {$nama} - Rp {$nominal} for transaksi {$pesanan->id_transaksi}");
        }
    }

    $biayaOngkir = BiayaTambahan::where('id_transaksi', $pesanan->id_transaksi)->sum('nominal');

    $diskon = $request->diskon ?? 0;
    $tipeDiskon = $request->tipe_diskon ?? 'nominal';
    if ($tipeDiskon === 'percent' && $diskon > 0) {
        $diskon = round(($subtotalItems * $diskon) / 100, 0);
    }

    $totalAkhir = $subtotalItems + $biayaOngkir - $diskon;

    $fotoBuktiPath = $pesanan->foto_bukti;
    if ($request->hasFile('foto_bukti')) {
        if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
            Storage::disk('public')->delete($pesanan->foto_bukti);
        }
        $file = $request->file('foto_bukti');
        $filename = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
        $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
    }

    // ✅ STEP 6: AUTO-REDIRECT
    $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'pickup')
        ->where('status', 'arrived_at_laundry')
        ->first();

    $statusBaru = $pesanan->status_transaksi;

    if ($pesanan->status_transaksi === 'pick_up' && $deliveryPickup) {
        $statusBaru = 'antrian';
    }

    $needDriverRedirect = false;
    if ($pesanan->status_transaksi === 'selesai_dicuci' && $this->isPesananTerlambat($pesanan)) {
        $statusBaru = 'siap_di_antar';
        $needDriverRedirect = true;
        
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
                'catatan' => 'Auto-generated: Melewati estimasi saat isi data',
            ]);
        }
        
        Log::info("⚠️ Order {$pesanan->id_transaksi} melewati estimasi → auto siap_di_antar");
    }

    $updateDataTransaksi = [
        'total_harga'      => $totalAkhir,
        'diskon'           => $diskon,
        'tipe_diskon'      => $tipeDiskon,
        'keterangan'       => $request->keterangan,
        'status_transaksi' => $statusBaru,
        'foto_bukti'       => $fotoBuktiPath,
    ];

    if ($request->filled('tgl_estimasi')) {
        $updateDataTransaksi['tgl_estimasi'] = $request->tgl_estimasi;
    }

    $pesanan->update($updateDataTransaksi);

    // STEP 8: FCM
    $fcmSent = false;
    $fcmMessage = '';
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
            ->whereNotNull('token')
            ->pluck('token')
            ->toArray();
        
        if (!empty($tokens)) {
            if ($needDriverRedirect) {
                $title = '⚠️ Pesanan Melewati Estimasi!';
                $body = "Cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati estimasi dan akan segera diantar. Total: Rp " . number_format($totalAkhir, 0, ',', '.');
            } else {
                $title = '💰 Harga Pesanan Sudah Diisi!';
                $body = "ORDER/{$pesanan->id_transaksi} - Total: Rp " . number_format($totalAkhir, 0, ',', '.') . ". Tap untuk lihat detail.";
            }

            foreach ($tokens as $token) {
                try {
                    $result = FcmService::send($token, $title, $body, [
                        'transaksi_id' => (string) $pesanan->id_transaksi,
                        'type' => $needDriverRedirect ? 'terlambat' : 'invoice',
                        'action' => 'open_detail',
                        'total_harga' => (string) $totalAkhir,
                        'status' => $statusBaru,
                    ]);
                    
                    if ($result) {
                        $fcmSent = true;
                    }
                } catch (\Exception $e) {
                    Log::error("❌ FCM Error: " . $e->getMessage());
                }
            }
        } else {
            $fcmMessage = ' (Notifikasi tidak terkirim: token tidak ditemukan)';
        }
    } else {
        $fcmMessage = ' (Notifikasi tidak terkirim: pelanggan tidak ditemukan)';
    }

    if ($needDriverRedirect) {
        return redirect()
            ->route('kasir.pesanan.online.list-driver', $id)
            ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk pengiriman.');
    }

    $successMessage = 'Data pesanan berhasil diperbarui' . ($fcmSent ? ' & notifikasi terkirim!' : $fcmMessage);
    
    return redirect()
        ->route('kasir.pesanan.online.detail', $id)
        ->with('success', $successMessage);
}

/**
 * ✅ FIXED updateDataAdmin2() - ADMIN2
 */
public function updateDataAdmin2(Request $request, $id)
{
    $request->validate([
        'id_detail.*'        => 'required|exists:detail_transaksi,id_detail_transaksi',
        'qty.*'              => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/',
        'diskon'             => 'nullable|numeric|min:0',
        'tipe_diskon'        => 'nullable|in:nominal,percent',
        'keterangan'         => 'nullable|string',
        'foto_bukti'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        'tgl_estimasi'       => 'nullable|date|after_or_equal:today',
        'new_biaya_nama.*'   => 'nullable|string|max:255',
        'new_biaya_nominal.*' => 'nullable|numeric|min:0',
        'delete_biaya_id.*'  => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
    ]);

    $pesanan = Transaksi::with(['pelanggan'])
        ->where('jenis_transaksi', 'online')
        ->findOrFail($id);

    $subtotalItems = 0;

    foreach ($request->id_detail as $index => $idDetail) {
        // ✅ FIX: Use 'layanan' instead of 'jenis'
        $detail = DetailTransaksi::with('layanan')->findOrFail($idDetail);

        if (!$detail->layanan) {
            Log::warning("⚠️ Detail ID {$idDetail} tidak punya relasi layanan!");
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "❌ Data layanan untuk detail pesanan tidak ditemukan! Silakan hubungi admin.");
        }

        $qty      = (float) $request->qty[$index];
        $harga    = (float) $detail->layanan->harga;
        $idSatuan = $detail->id_satuan;
        $subtotal = round($qty * $harga, 0);

        $subtotalItems += $subtotal;

        $updateData = [
            'qty'       => $qty,
            'harga'     => $harga,
            'id_satuan' => $idSatuan,
            'subtotal'  => $subtotal,
        ];

        if ($request->filled('tgl_estimasi')) {
            $updateData['tgl_estimasi'] = $request->tgl_estimasi;
        }

        $detail->update($updateData);
    }

    // STEP 2-5 (same as others)
    if ($request->filled('delete_biaya_id')) {
        BiayaTambahan::whereIn('id_biaya_tambahan', $request->delete_biaya_id)->delete();
        Log::info("✅ Deleted biaya tambahan: " . implode(', ', $request->delete_biaya_id));
    }

    if ($request->filled('new_biaya_nama') && $request->filled('new_biaya_nominal')) {
        foreach ($request->new_biaya_nama as $index => $nama) {
            $nominal = $request->new_biaya_nominal[$index] ?? 0;
            if (empty($nama) || $nominal <= 0) continue;
            
            BiayaTambahan::create([
                'id_transaksi' => $pesanan->id_transaksi,
                'nama_biaya' => $nama,
                'nominal' => $nominal,
            ]);
            
            Log::info("✅ Created biaya tambahan: {$nama} - Rp {$nominal} for transaksi {$pesanan->id_transaksi}");
        }
    }

    $biayaOngkir = BiayaTambahan::where('id_transaksi', $pesanan->id_transaksi)->sum('nominal');

    $diskon = $request->diskon ?? 0;
    $tipeDiskon = $request->tipe_diskon ?? 'nominal';
    if ($tipeDiskon === 'percent' && $diskon > 0) {
        $diskon = round(($subtotalItems * $diskon) / 100, 0);
    }

    $totalAkhir = $subtotalItems + $biayaOngkir - $diskon;

    $fotoBuktiPath = $pesanan->foto_bukti;
    if ($request->hasFile('foto_bukti')) {
        if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
            Storage::disk('public')->delete($pesanan->foto_bukti);
        }
        $file = $request->file('foto_bukti');
        $filename = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
        $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
    }

    // ✅ STEP 6: AUTO-REDIRECT
    $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
        ->where('jenis', 'pickup')
        ->where('status', 'arrived_at_laundry')
        ->first();

    $statusBaru = $pesanan->status_transaksi;

    if ($pesanan->status_transaksi === 'pick_up' && $deliveryPickup) {
        $statusBaru = 'antrian';
    }

    $needDriverRedirect = false;
    if ($pesanan->status_transaksi === 'selesai_dicuci' && $this->isPesananTerlambat($pesanan)) {
        $statusBaru = 'siap_di_antar';
        $needDriverRedirect = true;
        
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
                'catatan' => 'Auto-generated: Melewati estimasi saat isi data',
            ]);
        }
        
        Log::info("⚠️ Order {$pesanan->id_transaksi} melewati estimasi → auto siap_di_antar");
    }

    $updateDataTransaksi = [
        'total_harga'      => $totalAkhir,
        'diskon'           => $diskon,
        'tipe_diskon'      => $tipeDiskon,
        'keterangan'       => $request->keterangan,
        'status_transaksi' => $statusBaru,
        'foto_bukti'       => $fotoBuktiPath,
    ];

    if ($request->filled('tgl_estimasi')) {
        $updateDataTransaksi['tgl_estimasi'] = $request->tgl_estimasi;
    }

    $pesanan->update($updateDataTransaksi);

    // STEP 8: FCM
    $fcmSent = false;
    $fcmMessage = '';
    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

    if ($idPelanggan) {
        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
            ->whereNotNull('token')
            ->pluck('token')
            ->toArray();
        
        if (!empty($tokens)) {
            if ($needDriverRedirect) {
                $title = '⚠️ Pesanan Melewati Estimasi!';
                $body = "Cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati estimasi dan akan segera diantar. Total: Rp " . number_format($totalAkhir, 0, ',', '.');
            } else {
                $title = '💰 Harga Pesanan Sudah Diisi!';
                $body = "ORDER/{$pesanan->id_transaksi} - Total: Rp " . number_format($totalAkhir, 0, ',', '.') . ". Tap untuk lihat detail.";
            }

            foreach ($tokens as $token) {
                try {
                    $result = FcmService::send($token, $title, $body, [
                        'transaksi_id' => (string) $pesanan->id_transaksi,
                        'type' => $needDriverRedirect ? 'terlambat' : 'invoice',
                        'action' => 'open_detail',
                        'total_harga' => (string) $totalAkhir,
                        'status' => $statusBaru,
                    ]);
                    
                    if ($result) {
                        $fcmSent = true;
                    }
                } catch (\Exception $e) {
                    Log::error("❌ FCM Error: " . $e->getMessage());
                }
            }
        } else {
            $fcmMessage = ' (Notifikasi tidak terkirim: token tidak ditemukan)';
        }
    } else {
        $fcmMessage = ' (Notifikasi tidak terkirim: pelanggan tidak ditemukan)';
    }

    if ($needDriverRedirect) {
        return redirect()
            ->route('admin2.pesanan.online.list-driver', $id)
            ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk pengiriman.');
    }

    $successMessage = 'Data pesanan berhasil diperbarui' . ($fcmSent ? ' & notifikasi terkirim!' : $fcmMessage);
    
    return redirect()
        ->route('admin2.pesanan.online.detail', $id)
        ->with('success', $successMessage);
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

        // 🔔 FCM NOTIF PEMBAYARAN
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                if ($statusBayar === 'lunas') {
                    $title = '✅ Pembayaran Lunas!';
                    $body = "Pembayaran ORDER/{$pesanan->id_transaksi} sebesar Rp " . number_format($totalBayarBaru, 0, ',', '.') . " telah diterima. Terima kasih!";
                    $type = 'payment_completed';
                } else {
                    $title = '💳 DP Diterima!';
                    $sisaBayar = $pesanan->total_harga - $totalBayarBaru;
                    $body = "DP Rp " . number_format($nominalBayar, 0, ',', '.') . " diterima. Sisa: Rp " . number_format($sisaBayar, 0, ',', '.');
                    $type = 'payment_dp';
                }
                
                foreach ($tokens as $token) {
                    FcmService::send($token, $title, $body, [
                        'transaksi_id' => (string) $pesanan->id_transaksi,
                        'type' => $type,
                        'action' => 'open_detail',
                    ]);
                }
            }
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

public function simpanBuktiPembayaran(Request $request, $id)
{
    $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
    
    // ✅ CEK METODE BAYAR
    $pembayaranTerbaru = $pesanan->pembayaran()->latest()->first();
    
    if ($pembayaranTerbaru && $pembayaranTerbaru->id_metode_bayar) {
        $metodeBayar = $pembayaranTerbaru->metodeBayar;
    } else {
        $metodeBayar = $pesanan->metodeBayar;
    }
    
    $isCash = false;
    if ($metodeBayar && isset($metodeBayar->nama_metode_bayar)) {
        $namaMetode = strtolower($metodeBayar->nama_metode_bayar);
        $isCash = (stripos($namaMetode, 'cash') !== false || 
                   stripos($namaMetode, 'tunai') !== false ||
                   stripos($namaMetode, 'cod') !== false);
    }

    if ($isCash) {
        // ✅ PEMBAYARAN CASH
        $request->validate([
            'tipe_pembayaran' => 'required|in:lunas',
            'keterangan_bayar' => 'nullable|string',
        ]);
        
        // Cek apakah sudah ada pembayaran
        $sudahBayar = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
            ->where('tipe_pembayaran', 'lunas')
            ->exists();
        
        if ($sudahBayar) {
            return redirect()
                ->back()
                ->with('error', 'Pembayaran cash sudah pernah dikonfirmasi!');
        }
        
        // Simpan pembayaran cash
        Pembayaran::create([
            'id_transaksi' => $pesanan->id_transaksi,
            'id_metode_bayar' => $pesanan->id_metode_bayar ?? ($pembayaranTerbaru ? $pembayaranTerbaru->id_metode_bayar : null),
            'tipe_pembayaran' => 'lunas',
            'nominal' => $pesanan->total_harga,
            'foto_bukti' => null,
            'keterangan' => $request->keterangan_bayar ?? 'Pembayaran Cash dikonfirmasi oleh admin',
            'tanggal_bayar' => now()->format('Y-m-d'),
        ]);
        
        // ✅ UPDATE TRANSAKSI - CASH LUNAS
        $pesanan->update([
            'total_bayar' => $pesanan->total_harga,
            'dp' => $pesanan->total_harga,
            'status_bayar' => 'lunas',
            'tgl_lunas' => now()->format('Y-m-d'),
        ]);
        
        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Pembayaran cash berhasil dikonfirmasi! Pesanan lunas.');
            
    } else {
        // ✅ PEMBAYARAN TRANSFER/NON-CASH
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

        // ✅ HITUNG TOTAL YANG SUDAH DIBAYAR (dari tabel pembayaran)
        $totalDibayarSebelumnya = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
            ->sum('nominal');
        
        $sisaPembayaran = $pesanan->total_harga - $totalDibayarSebelumnya;
        
        if ($sisaPembayaran <= 0) {
            return redirect()
                ->back()
                ->with('error', 'Pesanan sudah lunas!');
        }
        
        // Tentukan nominal yang akan dibayar
        if ($request->tipe_pembayaran === 'dp') {
            $nominalBayar = (float) $request->nominal_bayar;
            
            if ($nominalBayar > $sisaPembayaran) {
                return redirect()
                    ->back()
                    ->with('error', 'Nominal DP melebihi sisa pembayaran! Sisa: Rp ' . number_format($sisaPembayaran, 0, ',', '.'));
            }
            
        } else {
            // Pelunasan
            $nominalBayar = $sisaPembayaran;
        }

        // ✅ UPLOAD FOTO BUKTI
        $fotoBuktiPath = null;
        if ($request->hasFile('foto_bukti_bayar')) {
            $file = $request->file('foto_bukti_bayar');
            $filename = 'bayar_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
            $fotoBuktiPath = $file->storeAs('foto_bukti_pembayaran', $filename, 'public');
        }
        
        // ✅ SIMPAN KE TABEL PEMBAYARAN
        Pembayaran::create([
            'id_transaksi' => $pesanan->id_transaksi,
            'id_metode_bayar' => $pesanan->id_metode_bayar ?? ($pembayaranTerbaru ? $pembayaranTerbaru->id_metode_bayar : null),
            'tipe_pembayaran' => $request->tipe_pembayaran,
            'nominal' => $nominalBayar,
            'foto_bukti' => $fotoBuktiPath,
            'keterangan' => $request->keterangan_bayar,
            'tanggal_bayar' => now()->format('Y-m-d'),
        ]);

        // ✅ HITUNG ULANG TOTAL BAYAR (dari tabel pembayaran)
        $totalBayarBaru = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
            ->sum('nominal');
        
        // ✅ TENTUKAN STATUS BAYAR DENGAN BENAR
        if ($totalBayarBaru >= $pesanan->total_harga) {
            $statusBayar = 'lunas';
        } elseif ($totalBayarBaru > 0) {
            $statusBayar = 'DP';
        } else {
            $statusBayar = 'belum_bayar';
        }

        // 🔔 FCM NOTIF PEMBAYARAN
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                if ($statusBayar === 'lunas') {
                    $title = '✅ Pembayaran Lunas!';
                    $body = "Pembayaran ORDER/{$pesanan->id_transaksi} sebesar Rp " . number_format($totalBayarBaru, 0, ',', '.') . " telah diterima. Terima kasih!";
                    $type = 'payment_completed';
                } else {
                    $title = '💳 DP Diterima!';
                    $sisaBayar = $pesanan->total_harga - $totalBayarBaru;
                    $body = "DP Rp " . number_format($nominalBayar, 0, ',', '.') . " diterima. Sisa: Rp " . number_format($sisaBayar, 0, ',', '.');
                    $type = 'payment_dp';
                }
                
                foreach ($tokens as $token) {
                    FcmService::send($token, $title, $body, [
                        'transaksi_id' => (string) $pesanan->id_transaksi,
                        'type' => $type,
                        'action' => 'open_detail',
                    ]);
                }
            }
        }
        // ✅ UPDATE TRANSAKSI - SINKRONISASI total_bayar = dp
        $pesanan->update([
            'total_bayar' => $totalBayarBaru,
            'dp' => $totalBayarBaru, // ← PENTING: dp = total_bayar
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

        // 🔔 FCM NOTIF PEMBAYARAN
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
            if ($tokens->isNotEmpty()) {
                if ($statusBayar === 'lunas') {
                    $title = '✅ Pembayaran Lunas!';
                    $body = "Pembayaran ORDER/{$pesanan->id_transaksi} sebesar Rp " . number_format($totalBayarBaru, 0, ',', '.') . " telah diterima. Terima kasih!";
                    $type = 'payment_completed';
                } else {
                    $title = '💳 DP Diterima!';
                    $sisaBayar = $pesanan->total_harga - $totalBayarBaru;
                    $body = "DP Rp " . number_format($nominalBayar, 0, ',', '.') . " diterima. Sisa: Rp " . number_format($sisaBayar, 0, ',', '.');
                    $type = 'payment_dp';
                }
                
                foreach ($tokens as $token) {
                    FcmService::send($token, $title, $body, [
                        'transaksi_id' => (string) $pesanan->id_transaksi,
                        'type' => $type,
                        'action' => 'open_detail',
                    ]);
                }
            }
        }
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
        \Log::info("🔍 === AUTO CHECK TERLAMBAT START ===");
        
        // ✅ AMBIL SEMUA PESANAN selesai_dicuci
        $pesananSelesaiDicuci = Transaksi::with(['pelanggan'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'selesai_dicuci')
            ->whereNotNull('tgl_estimasi')
            ->get();
        
        \Log::info("📊 Total pesanan selesai_dicuci: {$pesananSelesaiDicuci->count()}");
        
        $countTerlambat = 0;
        
        // ✅ FILTER YANG TERLAMBAT PAKAI isPesananTerlambat()
        foreach ($pesananSelesaiDicuci as $pesanan) {
            if ($this->isPesananTerlambat($pesanan)) {
                DB::beginTransaction();
                
                \Log::info("⚠️ Updating ORDER/{$pesanan->id_transaksi} ke siap_di_antar...");
                
                $pesanan->update(['status_transaksi' => 'siap_di_antar']);
                
                // ✅ BUAT DELIVERY ANTAR JIKA BELUM ADA
                $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                    ->where('jenis', 'antar')
                    ->first();
                
                if (!$existingDelivery) {
                    Delivery::create([
                        'id_transaksi' => $pesanan->id_transaksi,
                        'id_driver' => null,
                        'jenis' => 'antar',
                        'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                        'status' => 'accepted',
                        'waktu' => now(),
                        'catatan' => 'Auto-generated: Pesanan melewati estimasi (auto-check)',
                    ]);
                    
                    \Log::info("✅ Created delivery antar for ORDER/{$pesanan->id_transaksi}");
                }
                
                // 🔔 KIRIM FCM NOTIF
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
                                \Log::error("❌ FCM Error: " . $e->getMessage());
                            }
                        }
                    }
                }
                
                DB::commit();
                $countTerlambat++;
                
                \Log::info("✅ Successfully updated ORDER/{$pesanan->id_transaksi} ke siap_di_antar");
            }
        }
        
        \Log::info("📊 Total pesanan terlambat diupdate: {$countTerlambat}");
        \Log::info("🔍 === AUTO CHECK TERLAMBAT END ===");
        
        return [
            'success' => true,
            'total_checked' => $pesananSelesaiDicuci->count(),
            'total_updated' => $countTerlambat,
        ];
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("❌ Auto-update error: " . $e->getMessage());
        \Log::error("❌ Stack trace: " . $e->getTraceAsString());
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}
  public function manualCheckTerlambat()
{
    \Log::info("🔍 Manual trigger autoCheckTerlambat by Admin: " . auth()->guard('admin')->user()->nama ?? 'Unknown');
    
    $result = $this->autoCheckTerlambat();
    
    if ($result['success']) {
        return redirect()
            ->back()
            ->with('success', "✅ Auto-check selesai! {$result['total_updated']} dari {$result['total_checked']} pesanan diupdate ke siap_di_antar.");
    } else {
        return redirect()
            ->back()
            ->with('error', "❌ Error: {$result['error']}");
    }
}  
}
