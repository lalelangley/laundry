<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class PesananOnlineController extends Controller
{
    // ==================== KASIR ====================
    
    public function indexKasir(Request $request)
    {
        $tab = $request->get('tab', 'menunggu_konfirmasi');

        // Mapping tab ke status_transaksi di DB
        $statusMap = [
            'menunggu_konfirmasi' => ['antrian'], // sesuaikan
            'dikonfirmasi' => ['dikonfirmasi'],
            'proses' => ['proses'],
            'siap_di_ambil' => ['siap_di_ambil'],
            'siap_di_antar' => ['siap_di_antar'],
            'selesai' => ['selesai'],
            'ditolak' => ['batal'],
        ];

        $pesanan = Transaksi::with('detail_transaksi')
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
        
        return view('kasir.pesanan_online.index', compact('pesanan', 'tab'));
    }
    
    public function detailKasir($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'detail_transaksi.layanan', 'detail_transaksi.jenis'])
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

    // Mapping tab ke status_transaksi di DB
    $statusMap = [
        'menunggu_konfirmasi' => ['antrian'], // sesuaikan
        'dikonfirmasi' => ['dikonfirmasi'],
        'proses' => ['proses'],
        'siap_di_ambil' => ['siap_di_ambil'],
        'siap_di_antar' => ['siap_di_antar'],
        'selesai' => ['selesai'],
        'ditolak' => ['batal'],
    ];

    $pesanan = Transaksi::with('detail_transaksi')
        ->where('jenis_transaksi', 'online')
        ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
        ->orderBy('tgl_transaksi', 'desc')
        ->get();

    return view('pesanan_online.index', compact('pesanan', 'tab'));
}


public function detail($id)
{
    $pesanan = Transaksi::with(['pelanggan', 'detail_transaksi.layanan', 'detail_transaksi.jenis'])
        ->where('jenis_transaksi', 'online')
        ->findOrFail($id);
    
    return view('pesanan_online.detail', compact('pesanan')); // 🔥 FIX INI
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

        // Mapping tab ke status_transaksi di DB
        $statusMap = [
            'menunggu_konfirmasi' => ['antrian'], // sesuaikan
            'dikonfirmasi' => ['dikonfirmasi'],
            'proses' => ['proses'],
            'siap_di_ambil' => ['siap_di_ambil'],
            'siap_di_antar' => ['siap_di_antar'],
            'selesai' => ['selesai'],
            'ditolak' => ['batal'],
        ];

        $pesanan = Transaksi::with('detail_transaksi')
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
            ->orderBy('tgl_transaksi', 'desc')
            ->get();

        return view('admin2.pesanan_online.index', compact('pesanan', 'tab'));
    }
    
    public function detailAdmin2($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'detail_transaksi.layanan', 'detail_transaksi.jenis'])
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
}