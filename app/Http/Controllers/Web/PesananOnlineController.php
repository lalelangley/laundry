<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use App\Models\Delivery;
use App\Models\Driver;

class PesananOnlineController extends Controller
{
    // ==================== KASIR ====================
    
    public function indexKasir(Request $request)
    {
        $tab = $request->get('tab', 'pickup');
        $statusMap = $this->statusMap();

        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['pick_up'])
            ->orderByDesc('id_transaksi')
            ->get();

        return view('kasir.pesanan_online.index', compact('pesanan', 'tab'));
    }

    
    public function detailKasir($id)
    {
        $pesanan = Transaksi::with([
            'pelanggan',
            'detail_transaksi',
            'detail_transaksi.layanan',
            'detail_transaksi.jenis',
            'detail_transaksi.parfum',
            'detail_transaksi.satuan',
        ])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        return view('kasir.pesanan_online.detail', compact('pesanan'));
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
        Transaksi::where('jenis_transaksi', 'online')
            ->where('id_transaksi', $id)
            ->update(['status_transaksi' => 'proses']);

        return redirect()
            ->route('kasir.pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan diproses');
    }

    
    public function siapDiAmbilKasir($id)
    {
        Transaksi::where('jenis_transaksi', 'online')
            ->where('id_transaksi', $id)
            ->update(['status_transaksi' => 'siap_di_ambil']);

        return redirect()
            ->route('kasir.pesanan.online.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Pesanan siap diambil');
    }

    public function siapDiAntarKasir($id)
    {
        Transaksi::where('jenis_transaksi', 'online')
            ->where('id_transaksi', $id)
            ->update(['status_transaksi' => 'siap_di_antar']);

        return redirect()
            ->route('kasir.pesanan.online.index', ['tab' => 'siap_di_antar'])
            ->with('success', 'Pesanan siap diantar');
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
        $tab = $request->get('tab', 'pickup');
        $statusMap = $this->statusMap();

        // ✅ Gunakan statusMap untuk SEMUA tab termasuk pickup
        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['pick_up'])
            ->orderByDesc('id_transaksi')
            ->get();

        return view('pesanan_online.index', compact('pesanan', 'tab'));
    }

    public function detail($id)
    {
        $pesanan = Transaksi::with([
            'pelanggan',
            'detail_transaksi',
            'detail_transaksi.layanan',
            'detail_transaksi.jenis',
            'detail_transaksi.parfum',
            'detail_transaksi.satuan',
            'delivery'
        ])->findOrFail($id);
        
        return view('pesanan_online.detail', compact('pesanan'));
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
        
        return redirect()->route('pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
    }
    
    public function siapDiAmbil($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
        
        return redirect()->route('pesanan.online.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Pesanan siap diambil');
    }
    
    public function siapDiAntar($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_antar']);
        
        return redirect()->route('pesanan.online.index', ['tab' => 'siap_di_antar'])
            ->with('success', 'Pesanan siap diantar');
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
    
    public function indexAdmin2(Request $request)
    {
        $tab = $request->get('tab', 'pickup');
        $statusMap = $this->statusMap();

        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['pick_up'])
            ->orderByDesc('id_transaksi')
            ->get();

        return view('admin2.pesanan_online.index', compact('pesanan', 'tab'));
    }
    
    public function detailAdmin2($id)
    {
        $pesanan = Transaksi::with([
            'pelanggan',
            'detail_transaksi',
            'detail_transaksi.layanan',
            'detail_transaksi.jenis',
            'detail_transaksi.parfum',
            'detail_transaksi.satuan',
        ])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        return view('admin2.pesanan_online.detail', compact('pesanan'));
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
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
    }
    
    public function siapDiAmbilAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Pesanan siap diambil');
    }
    
    public function siapDiAntarAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_antar']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'siap_di_antar'])
            ->with('success', 'Pesanan siap diantar');
    }
    
    public function selesaiAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai']);
        
        return redirect()->route('admin2.pesanan.online.index', ['tab' => 'selesai'])
            ->with('success', 'Pesanan selesai');
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

    // ==================== DELIVERY FUNCTIONS ====================
    
    /**
     * LIST DRIVER UNTUK DELIVERY ANTAR
     */
    public function listDriver($id)
    {
        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        $drivers = Driver::where('status', 'aktif')->get();
        
        return view('pesanan_online.list_driver', compact('pesanan', 'drivers'));
    }

    /**
     * ASSIGN DRIVER UNTUK DELIVERY ANTAR
     */
    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        // Update status transaksi ke siap_di_antar
        $pesanan->update(['status_transaksi' => 'siap_di_antar']);

        // Buat record di tabel delivery
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
            ->with('success', 'Driver berhasil ditentukan! Pesanan siap untuk diantar.');
    }

    /**
     * LIST DRIVER UNTUK PICKUP
     */
    public function listDriverPickup($id)
    {
        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        $drivers = Driver::where('status', 'aktif')->get();
        
        return view('pesanan_online.list_driver_pickup', compact('pesanan', 'drivers'));
    }

    /**
     * ASSIGN DRIVER UNTUK PICKUP
     */
    public function assignDriverPickup(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);

        // Buat atau update record di tabel delivery
        Delivery::updateOrCreate(
            ['id_transaksi' => $pesanan->id_transaksi],
            [
                'id_driver' => $request->id_driver,
                'jenis' => 'pickup',
                'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                'status' => 'accepted',
                'waktu' => now()
            ]
        );

        return redirect()
            ->route('pesanan.online.index', ['tab' => 'pickup'])
            ->with('success', 'Driver berhasil dipilih untuk pickup!');
    }

    /**
     * DRIVER SAMPAI DI LAUNDRY (setelah pickup)
     */
    public function driverArrive($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        // Update delivery status
        $delivery = Delivery::where('id_transaksi', $id)->first();
        if ($delivery) {
            $delivery->update(['status' => 'arrived_at_laundry']);
        }
        
        // Update status transaksi ke proses langsung (tidak ke antrian)
        $pesanan->update(['status_transaksi' => 'proses']);

        return redirect()
            ->route('pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Driver sudah sampai. Pesanan masuk proses.');
    }

    public function listDeliveryOnline()
    {
        $deliveries = Delivery::with('transaksi.pelanggan')
            ->whereNull('id_driver')
            ->get();

        return view('pesanan_online.delivery.index', compact('deliveries'));
    }

    /**
     * UPDATE DATA PESANAN (ISI DATA PESANAN)
     */
    public function updateData(Request $request, $id)
    {
        $request->validate([
            'id_detail.*' => 'required|exists:detail_transaksi,id_detail_transaksi',
            'qty.*' => 'required|numeric|min:0.01',
            'id_satuan.*' => 'required|exists:satuan,id_satuan',
            'total_harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);

        // Update qty dan satuan untuk setiap detail_transaksi
        if ($request->has('id_detail')) {
            foreach ($request->id_detail as $index => $idDetail) {
                DetailTransaksi::where('id_detail_transaksi', $idDetail)->update([
                    'qty' => $request->qty[$index],
                    'id_satuan' => $request->id_satuan[$index],
                ]);
            }
        }

        // Update total harga dan keterangan di transaksi
        $pesanan->update([
            'total_harga' => $request->total_harga,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Data pesanan berhasil diperbarui!');
    }
    
    /**
     * KONFIRMASI PESANAN (kirim detail ke pelanggan via WhatsApp/SMS)
     */
    public function konfirmasiPesanan(Request $request, $id)
    {
        $request->validate([
            'metode_kirim' => 'required|in:whatsapp,sms'
        ]);

        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);

        // Ambil data dari tabel transaksi DAN pelanggan
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

        // Redirect berdasarkan metode
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

    // ==================== HELPER METHOD ====================
    
    /**
     * STATUS MAP untuk filter tab
     * ✅ Semua pesanan online otomatis masuk ke pickup
     */
    private function statusMap()
    {
        return [
            'pickup'         => ['pick_up'],
            'proses'         => ['proses'],
            'siap_diambil'   => ['siap_di_ambil'],
            'siap_diantar'   => ['siap_di_antar'],
            'selesai'        => ['selesai'],
            'ditolak'        => ['ditolak'],
        ];
    }
}