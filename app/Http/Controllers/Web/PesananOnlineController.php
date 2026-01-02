<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use App\Models\Delivery;
use App\Models\Driver;

class PesananOnlineController extends Controller
{
    // ==================== KASIR ====================
    
    public function indexKasir(Request $request)
    {
        $tab = $request->get('tab', 'menunggu_konfirmasi');
        $statusMap = $this->statusMap();

        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
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
            'detail_transaksi.jenis',  // ✅ sesuai model
            'detail_transaksi.parfum',
            'detail_transaksi.satuan'
        ])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        return view('kasir.pesanan_online.detail', compact('pesanan'));
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
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'proses'])
            ->with('success', 'Pesanan dalam proses');
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
            'jumlah_bayar' => $request->jumlah_bayar
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
        $tab = $request->get('tab', 'menunggu_konfirmasi');
        $statusMap = $this->statusMap();

        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
            ->orderByDesc('id_transaksi')
            ->get();

        return view('pesanan_online.index', compact('pesanan', 'tab'));
    }

    public function detail($id)
    {
        // Show detail page
        // ✅ PERBAIKAN: eager loading lengkap dengan relasi yang benar
        $pesanan = Transaksi::with([
            'detail_transaksi',
            'detail_transaksi.layanan',
            'detail_transaksi.jenis',  // ✅ pakai 'jenis' bukan 'jenisLayanan'
            'detail_transaksi.parfum',
            'detail_transaksi.satuan',
            'pelanggan'
        ])->findOrFail($id);
        
        return view('pesanan_online.detail', compact('pesanan'));
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
            'jumlah_bayar' => $request->jumlah_bayar
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
        $tab = $request->get('tab', 'menunggu_konfirmasi');
        $statusMap = $this->statusMap();

        $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan'])
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
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
            'detail_transaksi.jenis',  // ✅ sesuai model
            'detail_transaksi.parfum',
            'detail_transaksi.satuan'
        ])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        return view('admin2.pesanan_online.detail', compact('pesanan'));
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
            'jumlah_bayar' => $request->jumlah_bayar
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

    public function listDeliveryOnline()
    {
        $deliveries = Delivery::with('transaksi')
            ->whereNull('id_driver')
            ->get();

        return view('pesanan_online.delivery.index', compact('deliveries'));
    }
    
    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver'
        ]);

        Delivery::where('id_delivery', $id)->update([
            'id_driver' => $request->id_driver,
            'status'    => 'accepted'
        ]);

        return redirect()
            ->route('pesanan.online.delivery')
            ->with('success', 'Driver berhasil ditugaskan');
    }

    private function statusMap()
    {
        return [
            'menunggu_konfirmasi' => ['antrian'],
            'dikonfirmasi'        => ['dikonfirmasi'],
            'proses'              => ['proses'],
            'siap_di_ambil'       => ['siap_di_ambil'],
            'siap_di_antar'       => ['siap_di_antar'],
            'selesai'             => ['selesai'],
            'ditolak'             => ['ditolak'],
        ];
    }
    
    /**
     * UPDATE DATA PESANAN (ISI DATA PESANAN)
     */
    public function updateData(Request $request, $id)
    {
        $request->validate([
            'total_qty'   => 'required|numeric|min:0.01',
            'total_harga' => 'required|numeric|min:0',
            'satuan_text' => 'nullable|string|max:50',
            'keterangan'  => 'nullable|string',
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')
            ->findOrFail($id);

        $pesanan->update([
            'total_qty'   => $request->total_qty,
            'total_harga' => $request->total_harga,
            'satuan_text' => $request->satuan_text ?? 'Kg',
            'keterangan'  => $request->keterangan,
        ]);

        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Data pesanan berhasil diperbarui!');
    }
    
    /**
     * KONFIRMASI PESANAN
     * (kirim detail ke pelanggan via WhatsApp/SMS)
     */
    public function konfirmasiPesanan(Request $request, $id)
    {
        $request->validate([
            'metode_kirim' => 'required|in:whatsapp,sms'
        ]);

        $pesanan = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);

        // Update status
        $pesanan->update([
            'status_transaksi' => 'dikonfirmasi'
        ]);

        // Siapkan pesan
        $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Pelanggan';
        $noHp = $pesanan->pelanggan->no_hp ?? '';
        $totalQty = $pesanan->total_qty ?? '-';
        $satuan = $pesanan->satuan_text ?? 'item';
        $totalHarga = number_format($pesanan->total_harga, 0, ',', '.');

        $pesan = "Halo *{$namaPelanggan}*,%0A%0A";
        $pesan .= "Pesanan Anda sudah dikonfirmasi! 🎉%0A%0A";
        $pesan .= "*Detail Pesanan:*%0A";
        $pesan .= "📦 Order ID: {$pesanan->id_transaksi}%0A";
        $pesan .= "📋 Total Item: {$totalQty} {$satuan}%0A";
        $pesan .= "💰 Total Harga: Rp {$totalHarga}%0A%0A";
        $pesan .= "Terima kasih telah mempercayai layanan kami! 😊";

        // Redirect berdasarkan metode
        if ($request->metode_kirim === 'whatsapp' && $noHp) {
            // Format nomor WA (hapus 0 di depan, tambah 62)
            $waNumber = preg_replace('/^0/', '62', $noHp);
            $waUrl = "https://wa.me/{$waNumber}?text={$pesan}";
            
            return redirect()->away($waUrl);
        } 
        elseif ($request->metode_kirim === 'sms' && $noHp) {
            // SMS URL (untuk Android/iOS)
            $smsUrl = "sms:{$noHp}?body=" . urlencode(strip_tags(str_replace(['%0A', '*'], ["\n", ''], $pesan)));
            
            return redirect()->away($smsUrl);
        }

        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Pesanan berhasil dikonfirmasi!');
    }
}