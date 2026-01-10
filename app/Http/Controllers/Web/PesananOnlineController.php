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

    public function driverArriveAtLaundry($id)
    {
        DB::transaction(function () use ($id) {

            $pesanan = Transaksi::where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'pickup')
                ->findOrFail($id);

            // update transaksi ke ANTRIAN
            $pesanan->update([
                'status_transaksi' => 'antrian'
            ]);

            // update delivery pickup
            Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->update([
                    'status' => 'arrive_at_laundry'
                ]);
        });

        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Driver sampai laundry, pesanan masuk antrian');
    }

    // ==================== KASIR ====================
    
    public function indexKasir(Request $request)
    {
        $tab = $request->get('tab', 'menunggu_konfirmasi');
        $statusMap = $this->statusMap();

        if ($tab === 'pickup') {
            // ✅ SAMA SEPERTI ADMIN - tampilkan pickup dengan status pending & accepted
            $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
                ->where('jenis_transaksi', 'online')
                ->whereHas('delivery', function ($q) {
                    $q->where('jenis', 'pickup')
                      ->whereIn('status', ['pending', 'accepted']);
                })
                ->orderByDesc('id_transaksi')
                ->get();

        } else {
            $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
                ->where('jenis_transaksi', 'online')
                ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
                ->orderByDesc('id_transaksi')
                ->get();
        }

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
            'delivery'
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
        $tab = $request->get('tab', 'menunggu_konfirmasi');
        $statusMap = $this->statusMap();

        if ($tab === 'pickup') {
            // ✅ TAMPILKAN PICKUP DENGAN STATUS PENDING
            $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
                ->where('jenis_transaksi', 'online')
                ->whereHas('delivery', function ($q) {
                    $q->where('jenis', 'pickup')
                      ->whereIn('status', ['pending']);
                })
                ->orderByDesc('id_transaksi')
                ->get();

        } else {
            $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
                ->where('jenis_transaksi', 'online')
                ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
                ->when($tab === 'menunggu_konfirmasi', function ($query) {
                    // hanya ambil yang delivery statusnya 'arrived_at_laundry'
                    $query->whereHas('delivery', function ($q) {
                        $q->where('status', 'arrived_at_laundry');
                    });
                })
                ->orderByDesc('id_transaksi')
                ->get();
        }

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
        $tab = $request->get('tab', 'menunggu_konfirmasi');
        $statusMap = $this->statusMap();

        if ($tab === 'pickup') {
            // ✅ SAMA SEPERTI ADMIN - tampilkan pickup dengan status pending & accepted
            $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
                ->where('jenis_transaksi', 'online')
                ->whereHas('delivery', function ($q) {
                    $q->where('jenis', 'pickup')
                      ->whereIn('status', ['pending', 'accepted']);
                })
                ->orderByDesc('id_transaksi')
                ->get();

        } else {
            $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'delivery'])
                ->where('jenis_transaksi', 'online')
                ->whereIn('status_transaksi', $statusMap[$tab] ?? ['antrian'])
                ->orderByDesc('id_transaksi')
                ->get();
        }

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
            'delivery'
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

    /**
     * LIST DRIVER UNTUK PICKUP
     * ❗ HANYA PILIH DRIVER
     */
    public function listDriverPickup($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'pickup')
            ->findOrFail($id);

        $drivers = Driver::where('status', 'aktif')->get();

        return view('pesanan_online.pickup.list_driver', compact('pesanan', 'drivers'));
    }

    /**
     * ASSIGN DRIVER UNTUK PICKUP
     * ❗ TIDAK MENGUBAH STATUS TRANSAKSI
     */
    public function assignDriverPickup(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);

        // Update pickup delivery yang sudah ada
        $updated = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->where('status', 'pending')
            ->update([
                'id_driver' => $request->id_driver,
                'status'    => 'accepted',
                'waktu'     => now(),
            ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Driver pickup gagal ditentukan. Pastikan ada delivery pickup dengan status pending.');
        }

        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Driver pickup berhasil ditentukan');
    }

    /**
     * TAMPILKAN LIST DRIVER UNTUK DELIVERY
     */
    public function listDriver($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        // Ambil semua driver yang aktif
        $drivers = Driver::where('status', 'aktif')->get();
        
        return view('pesanan_online.listonlinedriver', compact('pesanan', 'drivers'));
    }

    /**
     * ASSIGN DRIVER UNTUK DELIVERY
     */
    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        // Update status transaksi ke siap_di_antar
        $pesanan->update([
            'status_transaksi' => 'siap_di_antar'
        ]);

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

    public function listDeliveryOnline()
    {
        $deliveries = Delivery::with(['transaksi.pelanggan', 'driver'])
            ->whereNull('id_driver')
            ->get();

        return view('pesanan_online.delivery.index', compact('deliveries'));
    }

    private function statusMap()
    {
        return [
            'pickup'              => ['pickup'],
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
     * ✅ UPDATE QTY & SATUAN PER ITEM DI DETAIL_TRANSAKSI
     */
    public function updateData(Request $request, $id)
    {
        $request->validate([
            'id_detail.*' => 'required|exists:detail_transaksi,id_detail_transaksi',
            'qty.*'       => 'required|numeric|min:0.01',
            'keterangan'  => 'nullable|string',
        ]);

        $pesanan = Transaksi::where('jenis_transaksi', 'online')
            ->findOrFail($id);

        $totalHarga = 0;

        foreach ($request->id_detail as $index => $idDetail) {

            $detail = DetailTransaksi::with('jenis')
                ->findOrFail($idDetail);

            // ✅ DATA OTOMATIS DARI JENIS LAYANAN
            $qty       = $request->qty[$index];
            $harga     = $detail->jenis->harga;
            $idSatuan  = $detail->jenis->id_satuan;
            $subtotal  = $qty * $harga;

            $totalHarga += $subtotal;

            $detail->update([
                'qty'        => $qty,
                'harga'      => $harga,
                'id_satuan'  => $idSatuan,
                'subtotal'   => $subtotal,
            ]);
        }

        // ✅ UPDATE TOTAL TRANSAKSI
        $pesanan->update([
            'total_harga' => $totalHarga,
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
}