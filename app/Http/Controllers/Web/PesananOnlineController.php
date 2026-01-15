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
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'proses']);
        
        // ✅ AUTO DELIVERY JIKA TERLAMBAT
        if ($this->isPesananTerlambat($pesanan)) {
            $pesanan->update(['status_transaksi' => 'siap_di_antar']);
            
            return redirect()
                ->route('kasir.pesanan.online.list-driver', $id)
                ->with('info', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk delivery.');
        }
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
    }

    public function selesaiDiCuciKasir($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai_dicuci']);
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'selesai_dicuci'])
            ->with('success', 'Pesanan selesai dicuci');
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
    // Di method index() - ADMIN
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
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'proses']);
        
        // ✅ AUTO DELIVERY JIKA TERLAMBAT
        if ($this->isPesananTerlambat($pesanan)) {
            $pesanan->update(['status_transaksi' => 'siap_di_antar']);
            
            return redirect()
                ->route('pesanan.online.list-driver', $id)
                ->with('info', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk delivery.');
        }
        
        return redirect()->route('pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
    }

    public function selesaiDiCuci($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai_dicuci']);
        
        return redirect()->route('pesanan.online.index', ['tab' => 'selesai_dicuci'])
            ->with('success', 'Pesanan selesai dicuci');
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
    
    public function destroy($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->delete();
        
        return redirect()->route('pesanan.online.index')
            ->with('success', 'Pesanan berhasil dihapus');
    }
    
    // ==================== ADMIN2 ====================

    // Di method index() - ADMIN
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
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai_dicuci']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'selesai_dicuci'])
            ->with('success', 'Pesanan selesai dicuci');
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
    $request->validate([
        'id_detail.*' => 'required|exists:detail_transaksi,id_detail_transaksi',
        'qty.*'       => 'required|numeric|min:0.01',
        'diskon'      => 'nullable|numeric|min:0',
        'tipe_diskon' => 'nullable|in:nominal,percent',
        'keterangan'  => 'nullable|string',
        'foto_bukti'  => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        'tgl_estimasi' => 'required|date|after_or_equal:today',
        'id_biaya_tambahan' => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
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
    if ($request->filled('id_biaya_tambahan')) {
        $biayaTambahan = BiayaTambahan::find($request->id_biaya_tambahan);
        if ($biayaTambahan) {
            $biayaOngkir = $biayaTambahan->nominal;
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
        'id_biaya_tambahan' => $request->filled('id_biaya_tambahan') ? $request->id_biaya_tambahan : null,
    ]);

    $message = 'Data pesanan berhasil diperbarui!';
    if ($statusBaru === 'antrian') {
        $message = 'Data pesanan berhasil diperbarui dan pesanan masuk ke antrian!';
    }

    return redirect()
        ->route('pesanan.online.detail', $id)
        ->with('success', $message);
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
    if ($request->filled('id_biaya_tambahan')) {
        $biayaTambahan = BiayaTambahan::find($request->id_biaya_tambahan);
        if ($biayaTambahan) {
            $biayaOngkir = $biayaTambahan->nominal;
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
        'id_biaya_tambahan' => $request->filled('id_biaya_tambahan') ? $request->id_biaya_tambahan : null,
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
    if ($request->filled('id_biaya_tambahan')) {
        $biayaTambahan = BiayaTambahan::find($request->id_biaya_tambahan);
        if ($biayaTambahan) {
            $biayaOngkir = $biayaTambahan->nominal;
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
        'id_biaya_tambahan' => $request->filled('id_biaya_tambahan') ? $request->id_biaya_tambahan : null,
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
            ->route('pesanan.online.detail', $id)
            ->with('success', $message);
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
        
    private function isPesananTerlambat($pesanan)
    {
        if (!$pesanan->tgl_estimasi) {
            return false;
        }
        
        $estimasi = \Carbon\Carbon::parse($pesanan->tgl_estimasi);
        $today = \Carbon\Carbon::today();
        
        return $today->greaterThan($estimasi);
    }
    
}
