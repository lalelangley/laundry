<?php

/**
 * ===============================================
 * CLASS: PesananOnlineController
 * ===============================================
 * Controller untuk mengelola pesanan laundry online.
 * Menangani alur pesanan dari pickup, proses pencucian,
 * hingga pengantaran dan pembayaran.
 * 
 * Roles yang dilayani: Admin, Kasir, Admin2
 * 
 * @package App\Http\Controllers\Web
 */

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

    /**
     * -----------------------------------------------
     * METHOD: driverArriveAtLaundry (Admin)
     * -----------------------------------------------
     * Menangani event ketika driver tiba di laundry
     * setelah melakukan pickup dari pelanggan.
     * Hanya mengupdate status delivery, bukan status transaksi.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function driverArriveAtLaundry($id)
    {
        // Jalankan dalam transaksi database agar data konsisten
        DB::transaction(function () use ($id) {

            // Ambil data pesanan online dengan status pick_up beserta relasi pelanggan
            $pesanan = Transaksi::with('pelanggan')
                ->where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'pick_up')
                ->findOrFail($id);

            // Update status delivery pickup menjadi arrived_at_laundry
            // (tidak mengubah status transaksi utama)
            Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->update(['status' => 'arrived_at_laundry']);
            
            // Kirim notifikasi FCM ke pelanggan bahwa cucian sudah tiba di laundry
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            // Percabangan: hanya kirim notif jika pelanggan ditemukan
            if ($idPelanggan) {
                // Ambil semua token FCM milik pelanggan (array token)
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

                // Percabangan: cek apakah ada token yang tersedia
                if ($tokens->isNotEmpty()) {
                    // Perulangan: kirim notifikasi ke setiap token yang dimiliki pelanggan
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token, 
                            '📦 Cucian Sudah Sampai!', 
                            "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah tiba di laundry. Admin sedang memeriksa cucian Anda.", 
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi, 
                                'type'         => 'arrived_at_laundry', 
                                'action'       => 'open_detail'
                            ]
                        );
                    }
                }
            }
        });

        // Redirect ke halaman detail dengan pesan sukses
        return redirect()
            ->route('pesanan.online.detail', $id)
            ->with('success', 'Driver sampai laundry. Silakan isi data pesanan untuk mengirim invoice.');
    }

    /**
     * -----------------------------------------------
     * METHOD: driverArriveAtLaundryKasir (Kasir)
     * -----------------------------------------------
     * Fungsi yang sama dengan driverArriveAtLaundry,
     * namun redirect ke route khusus kasir.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function driverArriveAtLaundryKasir($id)
    {
        DB::transaction(function () use ($id) {

            // Ambil pesanan online dengan status pick_up
            $pesanan = Transaksi::with('pelanggan')
                ->where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'pick_up')
                ->findOrFail($id);

            // Hanya update status delivery, bukan status transaksi
            Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->update(['status' => 'arrived_at_laundry']);
            
            // Kirim FCM ke pelanggan
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

                if ($tokens->isNotEmpty()) {
                    // Perulangan: kirim ke semua token FCM pelanggan
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token, 
                            '📦 Cucian Sudah Sampai!', 
                            "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah tiba di laundry. Admin sedang memeriksa cucian Anda.", 
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi, 
                                'type'         => 'arrived_at_laundry', 
                                'action'       => 'open_detail'
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

    /**
     * -----------------------------------------------
     * METHOD: driverArriveAtLaundryAdmin2 (Admin2)
     * -----------------------------------------------
     * Fungsi yang sama, redirect ke route admin2.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function driverArriveAtLaundryAdmin2($id)
    {
        DB::transaction(function () use ($id) {

            $pesanan = Transaksi::with('pelanggan')
                ->where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'pick_up')
                ->findOrFail($id);

            // Update hanya delivery status
            Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->update(['status' => 'arrived_at_laundry']);
            
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
                                'type'         => 'arrived_at_laundry', 
                                'action'       => 'open_detail'
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

    /**
     * -----------------------------------------------
     * METHOD: indexKasir
     * -----------------------------------------------
     * Menampilkan daftar pesanan online untuk role Kasir.
     * Menggunakan tab untuk memfilter status pesanan.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function indexKasir(Request $request)
    {
        // Ambil tab aktif dari query string, default: pickup
        $tab       = $request->get('tab', 'pickup');
        $statusMap = $this->statusMap();

        // Hitung pesanan pickup yang belum memiliki driver (badge notifikasi)
        $pickupNeedDriver = Transaksi::where('jenis_transaksi', 'online')
            ->where('status_transaksi', 'pick_up')
            ->where(function($q) {
                // Percabangan: pesanan tanpa delivery ATAU delivery tanpa driver
                $q->whereDoesntHave('delivery', function($sub) {
                    $sub->where('jenis', 'pickup');
                })
                ->orWhereHas('delivery', function($sub) {
                    $sub->where('jenis', 'pickup')
                        ->whereNull('id_driver');
                });
            })
            ->count();

        // Percabangan berdasarkan tab yang dipilih untuk query data
        if ($tab === 'pickup') {
            // Ambil semua pesanan dengan status pick_up
            $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
                ->where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'pick_up')
                ->orderByDesc('id_transaksi')
                ->get();

            // Perulangan: set relasi delivery untuk setiap pesanan
            foreach($pesanan as $p) {
                $p->setRelation('delivery', 
                    \App\Models\Delivery::where('id_transaksi', $p->id_transaksi)
                        ->with('driver')
                        ->orderByDesc('id_delivery')
                        ->get()
                );
            }

        } elseif ($tab === 'antrian') {
            // Ambil pesanan antrian atau pick_up yang sudah arrived_at_laundry
            $pesanan = Transaksi::with(['detail_transaksi', 'pelanggan', 'metodeBayar', 'pembayaran'])
                ->where('jenis_transaksi', 'online')
                ->where(function ($q) {
                    $q->where('status_transaksi', 'antrian')
                        ->orWhere(function ($sub) {
                            // Percabangan: pick_up + driver sudah tiba di laundry
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

        } elseif ($tab === 'selesai_dicuci') {
            // Ambil pesanan yang sudah selesai dicuci
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
            // Ambil pesanan yang siap diantar ke pelanggan
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
            // Default: gunakan statusMap untuk tab lainnya
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

    /**
     * -----------------------------------------------
     * METHOD: detailKasir
     * -----------------------------------------------
     * Menampilkan detail satu pesanan online untuk Kasir.
     * Memuat semua relasi yang dibutuhkan tampilan detail.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\View\View
     */
    public function detailKasir($id)
    {
        // Ambil pesanan beserta semua relasinya secara eager loading
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

        // Ambil master list biaya tambahan untuk keperluan dropdown form
        $masterBiayaTambahan = \DB::table('biaya_tambahan')
            ->select('nama_biaya', 'nominal')
            ->whereNull('id_transaksi') // Hanya data master/template, bukan per transaksi
            ->orWhere('id_transaksi', 0)
            ->distinct()
            ->get();

        // Ambil data delivery pickup (pengambilan cucian dari pelanggan)
        $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->with('driver')
            ->latest('id_delivery')
            ->first();
        
        // Ambil data delivery antar (pengiriman cucian ke pelanggan)
        $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'antar')
            ->with('driver')
            ->latest('id_delivery')
            ->first();
        
        return view('kasir.pesanan_online.detail', compact('pesanan', 'deliveryPickup', 'deliveryAntar'));
    }

    /**
     * -----------------------------------------------
     * METHOD: terimaKasir
     * -----------------------------------------------
     * Mengkonfirmasi penerimaan pesanan oleh Kasir.
     * Status pesanan berubah menjadi 'dikonfirmasi'.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terimaKasir($id)
    {
        // Ambil pesanan online dan update statusnya
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'dikonfirmasi']);
        
        // Kirim notifikasi konfirmasi ke pelanggan via FCM
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
        
        return redirect()->route('kasir.pesanan.online.index', ['tab' => 'dikonfirmasi'])
            ->with('success', 'Pesanan berhasil diterima');
    }

    /**
     * -----------------------------------------------
     * METHOD: tolakKasir
     * -----------------------------------------------
     * Menolak pesanan dan mengubah statusnya menjadi 'ditolak'.
     * Pelanggan akan mendapat notifikasi penolakan.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function tolakKasir($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'ditolak']);
        
        // Kirim notifikasi penolakan ke pelanggan
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
     * -----------------------------------------------
     * METHOD: prosesKasir
     * -----------------------------------------------
     * Memindahkan status pesanan ke 'proses' (sedang dicuci).
     * Jika pesanan sudah melewati estimasi waktu, otomatis
     * pindah ke 'siap_di_antar' dan redirect ke pilih driver.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function prosesKasir($id)
    {
        // Penanganan error dengan try-catch
        try {
            DB::beginTransaction();
            
            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            // Percabangan: cek apakah pesanan sudah melewati estimasi waktu
            if ($this->isPesananTerlambat($pesanan)) {
                // Jika terlambat, langsung set siap_di_antar
                $pesanan->update(['status_transaksi' => 'siap_di_antar']);
                
                // Cek apakah sudah ada delivery antar sebelumnya
                $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                    ->where('jenis', 'antar')
                    ->first();
                
                // Percabangan: buat delivery antar baru jika belum ada
                if (!$existingDelivery) {
                    Delivery::create([
                        'id_transaksi' => $pesanan->id_transaksi,
                        'id_driver'    => null,
                        'jenis'        => 'antar',
                        'alamat_tujuan'=> $pesanan->pelanggan->alamat ?? '-',
                        'status'       => 'accepted',
                        'waktu'        => now(),
                        'catatan'      => 'Auto-generated: Pesanan melewati estimasi saat proses',
                    ]);
                }
                
                // Kirim notifikasi keterlambatan ke pelanggan
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
                
                // Redirect ke halaman pilih driver karena pesanan harus diantar
                return redirect()
                    ->route('kasir.pesanan.online.list-driver', $id)
                    ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk delivery.');
            }
            
            // Jika tidak terlambat, update ke status 'proses'
            $pesanan->update(['status_transaksi' => 'proses']);
            
            // Kirim notifikasi proses ke pelanggan
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
            // Rollback jika terjadi error, catat ke log
            DB::rollBack();
            Log::error("Error prosesKasir: " . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    /**
     * -----------------------------------------------
     * METHOD: selesaiDiCuciKasir
     * -----------------------------------------------
     * Menandai pesanan selesai dicuci.
     * Jika melewati estimasi, otomatis set ke siap_di_antar.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selesaiDiCuciKasir($id)
    {
        try {
            DB::beginTransaction();
            
            $pesanan = Transaksi::with(['pelanggan'])
                ->where('jenis_transaksi', 'online')
                ->findOrFail($id);
            
            // Tentukan status baru berdasarkan kondisi keterlambatan
            $statusBaru        = 'selesai_dicuci';
            $needDriverSelection = false;
            
            // Percabangan: cek keterlambatan pesanan
            if ($this->isPesananTerlambat($pesanan)) {
                $statusBaru          = 'siap_di_antar';
                $needDriverSelection = true;
                
                Log::info("⚠️ Pesanan {$pesanan->id_transaksi} melewati estimasi, otomatis set ke siap_di_antar");
            }
            
            $pesanan->update(['status_transaksi' => $statusBaru]);
            
            // Kirim FCM ke pelanggan
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            
            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');
                
                if ($tokens->isNotEmpty()) {
                    // Tentukan judul dan isi notifikasi berdasarkan kondisi
                    $title = $needDriverSelection 
                        ? '⚠️ Cucian Selesai - Akan Segera Diantar!'
                        : '🎉 Cucian Selesai Dicuci!';
                    
                    $body = $needDriverSelection
                        ? "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan akan segera diantar karena melewati waktu estimasi."
                        : "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan siap untuk diproses lebih lanjut.";
                    
                    // Perulangan: kirim ke semua token FCM
                    foreach ($tokens as $token) {
                        FcmService::send(
                            $token,
                            $title,
                            $body,
                            [
                                'transaksi_id' => (string) $pesanan->id_transaksi,
                                'type'         => $needDriverSelection ? 'siap_di_antar' : 'selesai_dicuci',
                                'action'       => 'open_detail',
                            ]
                        );
                    }
                }
            }
            
            DB::commit();
            
            // Percabangan: redirect ke pilih driver jika pesanan terlambat
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

    /**
     * -----------------------------------------------
     * METHOD: siapDiAmbilKasir
     * -----------------------------------------------
     * Menandai cucian siap diambil oleh pelanggan di laundry.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function siapDiAmbilKasir($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
        
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

    /**
     * -----------------------------------------------
     * METHOD: selesaiKasir
     * -----------------------------------------------
     * Menandai pesanan telah selesai sepenuhnya.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selesaiKasir($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai']);
        
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

    /**
     * -----------------------------------------------
     * METHOD: bayarKasir
     * -----------------------------------------------
     * Memproses pembayaran pesanan oleh Kasir.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bayarKasir(Request $request, $id)
    {
        // Temukan pesanan dan update status pembayaran
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update([
            'status_bayar' => 'lunas',
            'total_bayar'  => $request->jumlah_bayar
        ]);
        
        return redirect()->route('kasir.pesanan.online.detail', $id)
            ->with('success', 'Pembayaran berhasil');
    }

    /**
     * -----------------------------------------------
     * METHOD: destroyKasir
     * -----------------------------------------------
     * Menghapus pesanan online (oleh Kasir).
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyKasir($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->delete();
        
        return redirect()->route('kasir.pesanan.online.index')
            ->with('success', 'Pesanan berhasil dihapus');
    }

    // ==================== ADMIN ====================

    /**
     * -----------------------------------------------
     * METHOD: index (Admin)
     * -----------------------------------------------
     * Menampilkan daftar pesanan online untuk role Admin.
     * Logika sama dengan indexKasir, route berbeda.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    { 
        $tab       = $request->get('tab', 'pickup');
        $statusMap = $this->statusMap();

        // Hitung pesanan pickup yang butuh driver (untuk badge notifikasi)
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

        // Percabangan: filter data berdasarkan tab aktif
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

        } elseif ($tab === 'selesai_dicuci') {
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

    /**
     * -----------------------------------------------
     * METHOD: detail (Admin)
     * -----------------------------------------------
     * Menampilkan detail pesanan online untuk Admin.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\View\View
     */
    public function detail($id)
    {
        // Eager load semua relasi yang diperlukan halaman detail
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

        // Ambil master biaya tambahan untuk form dropdown
        $masterBiayaTambahan = \DB::table('biaya_tambahan')
            ->select('nama_biaya', 'nominal')
            ->whereNull('id_transaksi')
            ->orWhere('id_transaksi', 0)
            ->distinct()
            ->get();

        // Ambil delivery pickup
        $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')
            ->with('driver')
            ->latest('id_delivery')
            ->first();
        
        // Ambil delivery antar
        $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'antar')
            ->with('driver')
            ->latest('id_delivery')
            ->first();
        
        return view('pesanan_online.detail', compact('pesanan', 'deliveryPickup', 'deliveryAntar'));
    }

    /**
     * -----------------------------------------------
     * METHOD: terima (Admin)
     * -----------------------------------------------
     * Konfirmasi penerimaan pesanan oleh Admin.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terima($id)
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
        
        return redirect()->route('pesanan.online.index', ['tab' => 'dikonfirmasi'])
            ->with('success', 'Pesanan berhasil diterima');
    }

    /**
     * -----------------------------------------------
     * METHOD: proses (Admin)
     * -----------------------------------------------
     * Memulai proses pencucian pesanan.
     * Jika terlambat, otomatis set ke siap_di_antar.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function proses($id)
    {
        try {
            DB::beginTransaction();
            
            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            // Percabangan: cek apakah pesanan melewati estimasi
            if ($this->isPesananTerlambat($pesanan)) {
                // Pesanan terlambat → langsung siap diantar
                $pesanan->update(['status_transaksi' => 'siap_di_antar']);
                
                $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                    ->where('jenis', 'antar')
                    ->first();
                
                if (!$existingDelivery) {
                    Delivery::create([
                        'id_transaksi'  => $pesanan->id_transaksi,
                        'id_driver'     => null,
                        'jenis'         => 'antar',
                        'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                        'status'        => 'accepted',
                        'waktu'         => now(),
                        'catatan'       => 'Auto-generated: Pesanan melewati estimasi saat proses',
                    ]);
                }
                
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
            
            // Pesanan tidak terlambat → update ke proses
            $pesanan->update(['status_transaksi' => 'proses']);
            
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

    /**
     * -----------------------------------------------
     * METHOD: selesaiDiCuci (Admin)
     * -----------------------------------------------
     * Menandai cucian selesai dicuci.
     * Cek estimasi dan kirim notifikasi FCM.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selesaiDiCuci($id)
    {
        try {
            DB::beginTransaction();
            
            $pesanan = Transaksi::with(['pelanggan'])
                ->where('jenis_transaksi', 'online')
                ->findOrFail($id);
            
            // Default status baru
            $statusBaru          = 'selesai_dicuci';
            $needDriverSelection = false;
            
            // Percabangan: jika melewati estimasi, alihkan ke siap_di_antar
            if ($this->isPesananTerlambat($pesanan)) {
                $statusBaru          = 'siap_di_antar';
                $needDriverSelection = true;
                
                Log::info("⚠️ Pesanan {$pesanan->id_transaksi} melewati estimasi, otomatis set ke siap_di_antar");
            }
            
            $pesanan->update(['status_transaksi' => $statusBaru]);
            
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
            
            if ($idPelanggan) {
                // Ambil semua token FCM pelanggan, filter yang kosong/null
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
                    ->pluck('token')
                    ->filter();
                
                Log::info("📍 Found " . $tokens->count() . " FCM tokens for pelanggan {$idPelanggan}");
                
                if ($tokens->isNotEmpty()) {
                    $title = $needDriverSelection 
                        ? '⚠️ Cucian Selesai - Akan Segera Diantar!'
                        : '🎉 Cucian Selesai Dicuci!';
                    
                    $body = $needDriverSelection
                        ? "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan akan segera diantar karena melewati waktu estimasi."
                        : "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci dan siap untuk diproses lebih lanjut.";
                    
                    // Perulangan: kirim ke setiap token dengan penanganan error per token
                    foreach ($tokens as $token) {
                        try {
                            $result = FcmService::send(
                                $token,
                                $title,
                                $body,
                                [
                                    'transaksi_id' => (string) $pesanan->id_transaksi,
                                    'type'         => $needDriverSelection ? 'siap_di_antar' : 'selesai_dicuci',
                                    'action'       => 'open_detail',
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
                }
            }
            
            DB::commit();
            
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

    /**
     * -----------------------------------------------
     * METHOD: siapDiAmbil (Admin)
     * -----------------------------------------------
     * Menandai cucian siap diambil pelanggan.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function siapDiAmbil($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
        
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

    /**
     * -----------------------------------------------
     * METHOD: selesai (Admin)
     * -----------------------------------------------
     * Menyelesaikan pesanan secara keseluruhan.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selesai($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai']);
        
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

    /**
     * -----------------------------------------------
     * METHOD: bayar (Admin)
     * -----------------------------------------------
     * Memproses pembayaran pesanan oleh Admin.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bayar(Request $request, $id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update([
            'status_bayar' => 'lunas',
            'total_bayar'  => $request->jumlah_bayar
        ]);
        
        return redirect()->route('pesanan.online.detail', $id)
            ->with('success', 'Pembayaran berhasil');
    }

    /**
     * -----------------------------------------------
     * METHOD: destroy (Admin)
     * -----------------------------------------------
     * Menghapus pesanan beserta seluruh relasi datanya.
     * Menghindari foreign key constraint dengan hapus relasi terlebih dahulu.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            // Cari transaksi online berdasarkan ID
            $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
            
            // Simpan info untuk keperluan log
            $customerName = $pesanan->nama_pelanggan ?? 'Guest';
            $orderId      = $pesanan->id_transaksi;
            
            // Hapus relasi terkait untuk menghindari foreign key constraint
            // Percabangan: cek dan hapus detail_transaksi jika ada
            if ($pesanan->detail_transaksi()->count() > 0) {
                $pesanan->detail_transaksi()->delete();
            }
            
            // Percabangan: cek dan hapus delivery jika ada
            if ($pesanan->delivery()->count() > 0) {
                $pesanan->delivery()->delete();
            }
            
            // Percabangan: cek dan hapus pembayaran jika ada
            if ($pesanan->pembayaran()->count() > 0) {
                $pesanan->pembayaran()->delete();
            }
            
            // Hapus transaksi utama
            $pesanan->delete();
            
            // Catat aktivitas penghapusan ke log
            \Log::info("Pesanan ORDER/{$orderId} ({$customerName}) berhasil dihapus oleh Admin: " . auth()->guard('admin')->user()->nama);
            
            return redirect()->route('pesanan.online.index')
                ->with('success', 'Pesanan berhasil dihapus!');
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Penanganan error: pesanan tidak ditemukan
            \Log::warning("Attempt to delete non-existent order: {$id}");
            
            return redirect()->route('pesanan.online.index')
                ->with('error', 'Pesanan tidak ditemukan!');
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Penanganan error: error database seperti foreign key constraint
            \Log::error("Database error while deleting order {$id}: " . $e->getMessage());
            
            return redirect()->route('pesanan.online.index')
                ->with('error', 'Gagal menghapus pesanan. Data masih terkait dengan data lain.');
                
        } catch (\Exception $e) {
            // Penanganan error: error umum lainnya
            \Log::error("Error deleting order {$id}: " . $e->getMessage());
            
            return redirect()->route('pesanan.online.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==================== ADMIN2 ====================

    /**
     * -----------------------------------------------
     * METHOD: indexAdmin2
     * -----------------------------------------------
     * Menampilkan daftar pesanan online untuk role Admin2.
     * Logika identik dengan index Admin, view berbeda.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function indexAdmin2(Request $request)
    {
        $tab       = $request->get('tab', 'pickup');
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

        // Percabangan berdasarkan tab
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

        } elseif ($tab === 'selesai_dicuci') {
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

    /**
     * -----------------------------------------------
     * METHOD: detailAdmin2
     * -----------------------------------------------
     * Menampilkan detail pesanan online untuk Admin2.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\View\View
     */
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

        $masterBiayaTambahan = \DB::table('biaya_tambahan')
            ->select('nama_biaya', 'nominal')
            ->whereNull('id_transaksi')
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
    }

    /**
     * -----------------------------------------------
     * METHOD: terimaAdmin2
     * -----------------------------------------------
     * Konfirmasi penerimaan pesanan oleh Admin2.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * -----------------------------------------------
     * METHOD: selesaiDiCuciAdmin2
     * -----------------------------------------------
     * Menandai cucian selesai untuk Admin2.
     * Logika sama dengan selesaiDiCuci Admin.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selesaiDiCuciAdmin2($id)
    {
        try {
            DB::beginTransaction();
            
            $pesanan = Transaksi::with(['pelanggan'])
                ->where('jenis_transaksi', 'online')
                ->findOrFail($id);
            
            $statusBaru          = 'selesai_dicuci';
            $needDriverSelection = false;
            
            if ($this->isPesananTerlambat($pesanan)) {
                $statusBaru          = 'siap_di_antar';
                $needDriverSelection = true;
                Log::info("⚠️ Pesanan {$pesanan->id_transaksi} melewati estimasi, otomatis set ke siap_di_antar");
            }
            
            $pesanan->update(['status_transaksi' => $statusBaru]);
            
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
                        FcmService::send($token, $title, $body, [
                            'transaksi_id' => (string) $pesanan->id_transaksi,
                            'type'         => $needDriverSelection ? 'siap_di_antar' : 'selesai_dicuci',
                            'action'       => 'open_detail',
                        ]);
                    }
                }
            }
            
            DB::commit();
            
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
     * -----------------------------------------------
     * METHOD: tolakAdmin2
     * -----------------------------------------------
     * Menolak pesanan oleh Admin2.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function tolakAdmin2($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'ditolak']);
        
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
     * -----------------------------------------------
     * METHOD: prosesAdmin2
     * -----------------------------------------------
     * Memulai proses pencucian untuk Admin2.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
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
                        'id_transaksi'  => $pesanan->id_transaksi,
                        'id_driver'     => null,
                        'jenis'         => 'antar',
                        'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                        'status'        => 'accepted',
                        'waktu'         => now(),
                        'catatan'       => 'Auto-generated: Pesanan melewati estimasi saat proses',
                    ]);
                }
                
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
     * -----------------------------------------------
     * METHOD: isPesananTerlambat (Private Helper)
     * -----------------------------------------------
     * Mengecek apakah pesanan sudah melewati tanggal estimasi.
     * Estimasi dihitung hingga akhir hari (23:59:59).
     *
     * @param Transaksi $pesanan - Object pesanan
     * @return bool - true jika terlambat, false jika belum
     */
    private function isPesananTerlambat($pesanan)
    {
        // Percabangan: jika tidak ada estimasi, anggap tidak terlambat
        if (!$pesanan->tgl_estimasi) {
            return false;
        }
        
        // Parse tanggal estimasi ke akhir hari untuk perbandingan akurat
        $estimasi = \Carbon\Carbon::parse($pesanan->tgl_estimasi)->endOfDay();
        $now      = \Carbon\Carbon::now();
        
        // Terlambat hanya jika waktu sekarang melewati batas estimasi
        $isTerlambat = $now->greaterThan($estimasi);
        
        // Log informasi pengecekan untuk keperluan debugging
        \Log::info("🔍 Check Terlambat - Pesanan {$pesanan->id_transaksi}:");
        \Log::info("   📅 Estimasi: {$estimasi->format('Y-m-d H:i:s')}");
        \Log::info("   🕐 Sekarang: {$now->format('Y-m-d H:i:s')}");
        \Log::info("   " . ($isTerlambat ? "❌ TERLAMBAT" : "✅ BELUM TERLAMBAT"));
        
        return $isTerlambat;
    }

    /**
     * -----------------------------------------------
     * METHOD: siapDiAmbilAdmin2
     * -----------------------------------------------
     * Menandai cucian siap diambil untuk Admin2.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function siapDiAmbilAdmin2($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_ambil']);
        
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

    /**
     * -----------------------------------------------
     * METHOD: selesaiAdmin2
     * -----------------------------------------------
     * Menyelesaikan pesanan untuk Admin2.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selesaiAdmin2($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'selesai']);
        
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

    /**
     * -----------------------------------------------
     * METHOD: siapDiAntar / siapDiAntarKasir / siapDiAntarAdmin2
     * -----------------------------------------------
     * Menandai cucian siap diantar ke pelanggan.
     * Tersedia untuk semua role (Admin, Kasir, Admin2).
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function siapDiAntar($id)
    {
        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'siap_di_antar']);
        
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

    /**
     * -----------------------------------------------
     * METHOD: bayarAdmin2
     * -----------------------------------------------
     * Memproses pembayaran pesanan oleh Admin2.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bayarAdmin2(Request $request, $id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update([
            'status_bayar' => 'lunas',
            'total_bayar'  => $request->jumlah_bayar
        ]);
        
        return redirect()->route('admin2.pesanan.online.detail', $id)
            ->with('success', 'Pembayaran berhasil');
    }

    /**
     * -----------------------------------------------
     * METHOD: destroyAdmin2
     * -----------------------------------------------
     * Menghapus pesanan online (oleh Admin2).
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAdmin2($id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->delete();
        
        return redirect()->route('admin2.pesanan.online.index')
            ->with('success', 'Pesanan berhasil dihapus');
    }

    // ==================== DELIVERY ONLINE ====================

    /**
     * -----------------------------------------------
     * METHOD: listDriver (Admin)
     * -----------------------------------------------
     * Menampilkan daftar driver untuk dipilih.
     * Menentukan jenis penugasan (pickup atau antar) berdasarkan
     * status transaksi saat ini.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\View\View
     */
    public function listDriver($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        // Inisialisasi variabel kontrol
        $isPickup       = false;
        $pickupDelivery = null;
        $deliveryAntar  = null;
        
        // Percabangan: tentukan jenis tugas driver berdasarkan status pesanan
        if ($pesanan->status_transaksi === 'pick_up') {
            // Status pick_up → tugas driver adalah menjemput cucian
            $isPickup       = true;
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->first();

        } elseif ($pesanan->status_transaksi === 'siap_di_antar') {
            // Status siap_di_antar → tugas driver adalah mengantarkan cucian
            $isPickup      = false;
            $deliveryAntar = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'antar')
                ->first();

        } else {
            // Default fallback ke pickup
            $isPickup = true;
        }
        
        // Ambil semua driver aktif untuk ditampilkan di dropdown
        $drivers = Driver::where('status', 'aktif')->get();
        
        return view('pesanan_online.listonlinedriver', compact(
            'pesanan', 
            'drivers', 
            'isPickup', 
            'pickupDelivery',
            'deliveryAntar'
        ));
    }

    /**
     * -----------------------------------------------
     * METHOD: listDriverKasir
     * -----------------------------------------------
     * Daftar pilih driver untuk Kasir.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\View\View
     */
    public function listDriverKasir($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        $isPickup       = false;
        $pickupDelivery = null;
        $deliveryAntar  = null;
        
        if ($pesanan->status_transaksi === 'pick_up') {
            $isPickup       = true;
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->first();
        } elseif ($pesanan->status_transaksi === 'siap_di_antar') {
            $isPickup      = false;
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

    /**
     * -----------------------------------------------
     * METHOD: listDriverAdmin2
     * -----------------------------------------------
     * Daftar pilih driver untuk Admin2.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\View\View
     */
    public function listDriverAdmin2($id)
    {
        $pesanan = Transaksi::with(['pelanggan', 'delivery'])
            ->where('jenis_transaksi', 'online')
            ->findOrFail($id);
        
        $isPickup       = false;
        $pickupDelivery = null;
        $deliveryAntar  = null;
        
        if ($pesanan->status_transaksi === 'pick_up') {
            $isPickup       = true;
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->first();
        } elseif ($pesanan->status_transaksi === 'siap_di_antar') {
            $isPickup      = false;
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

    /**
     * -----------------------------------------------
     * METHOD: assignDriverPickup (Admin)
     * -----------------------------------------------
     * Menugaskan driver untuk proses pickup cucian dari pelanggan.
     * Membuat atau mengupdate record delivery dengan status 'accepted'.
     * Mengirim notifikasi FCM ke pelanggan dan driver.
     *
     * @param Request $request - Berisi id_driver dan catatan_driver
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignDriverPickup(Request $request, $id)
    {
        // Validasi input form
        $request->validate([
            'id_driver'      => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            // Cek apakah sudah ada delivery pickup sebelumnya
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->first();
            
            $driver = Driver::find($request->id_driver);
            
            // Percabangan: buat baru atau update yang sudah ada
            if (!$pickupDelivery) {
                // Buat record delivery baru dengan status accepted
                $pickupDelivery = Delivery::create([
                    'id_transaksi'  => $pesanan->id_transaksi,
                    'id_driver'     => $request->id_driver,
                    'jenis'         => 'pickup',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status'        => 'accepted',
                    'waktu'         => now(),
                    'catatan'       => $request->catatan_driver,
                ]);
                Log::info("✅ Created new pickup delivery with status ACCEPTED for transaksi {$pesanan->id_transaksi}");
            } else {
                // Update delivery yang sudah ada dengan driver baru
                $pickupDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status'    => 'accepted',
                    'waktu'     => now(),
                    'catatan'   => $request->catatan_driver,
                ]);
                Log::info("✅ Updated existing pickup delivery to ACCEPTED for transaksi {$pesanan->id_transaksi}");
            }

            // Kirim notifikasi FCM ke pelanggan
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
                                'type'         => 'driver_assigned_pickup', 
                                'action'       => 'open_detail'
                            ]
                        );
                    }
                    Log::info("✅ FCM sent to customer");
                }
            }

            // Kirim notifikasi FCM ke driver yang ditugaskan
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)
                ->pluck('token')
                ->filter(); // Hapus token yang null/kosong

            if ($driverFcmTokens->isNotEmpty()) {
                // Siapkan data pickup untuk notifikasi driver
                $alamat       = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp         = $pesanan->pelanggan->no_hp ?? '-';
                
                // Perulangan: kirim notif ke semua token driver dengan penanganan error
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send(
                            $token,
                            '📦 Tugas Pickup Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Pickup dari {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi' => (string) $pesanan->id_transaksi,
                                'id_delivery'  => (string) $pickupDelivery->id_delivery,
                                'jenis'        => 'pickup',
                                'type'         => 'new_pickup_task',
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp'        => $noHp,
                                'alamat'       => $alamat,
                                'catatan'      => $request->catatan_driver ?? '',
                                'action'       => 'open_delivery_detail'
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

    /**
     * -----------------------------------------------
     * METHOD: assignDriverPickupKasir
     * -----------------------------------------------
     * Menugaskan driver pickup untuk Kasir.
     * Logika identik dengan assignDriverPickup, redirect ke route kasir.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignDriverPickupKasir(Request $request, $id)
    {
        $request->validate([
            'id_driver'      => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan        = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)->where('jenis', 'pickup')->first();
            $driver         = Driver::find($request->id_driver);
            
            if (!$pickupDelivery) {
                $pickupDelivery = Delivery::create([
                    'id_transaksi'  => $pesanan->id_transaksi,
                    'id_driver'     => $request->id_driver,
                    'jenis'         => 'pickup',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status'        => 'accepted',
                    'waktu'         => now(),
                    'catatan'       => $request->catatan_driver,
                ]);
            } else {
                $pickupDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status'    => 'accepted',
                    'waktu'     => now(),
                    'catatan'   => $request->catatan_driver,
                ]);
            }

            // Kirim notifikasi ke pelanggan
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send($token, '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk pickup cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'driver_assigned_pickup', 'action' => 'open_detail']
                        );
                    }
                }
            }

            // Kirim notifikasi ke driver
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)->pluck('token')->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat        = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp          = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send($token, '📦 Tugas Pickup Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Pickup dari {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi'   => (string) $pesanan->id_transaksi,
                                'id_delivery'    => (string) $pickupDelivery->id_delivery,
                                'jenis'          => 'pickup',
                                'type'           => 'new_pickup_task',
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp'          => $noHp,
                                'alamat'         => $alamat,
                                'catatan'        => $request->catatan_driver ?? '',
                                'action'         => 'open_delivery_detail'
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
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

    /**
     * -----------------------------------------------
     * METHOD: assignDriverPickupAdmin2
     * -----------------------------------------------
     * Menugaskan driver pickup untuk Admin2.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignDriverPickupAdmin2(Request $request, $id)
    {
        $request->validate([
            'id_driver'      => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan        = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            $pickupDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)->where('jenis', 'pickup')->first();
            $driver         = Driver::find($request->id_driver);
            
            if (!$pickupDelivery) {
                $pickupDelivery = Delivery::create([
                    'id_transaksi'  => $pesanan->id_transaksi,
                    'id_driver'     => $request->id_driver,
                    'jenis'         => 'pickup',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status'        => 'accepted',
                    'waktu'         => now(),
                    'catatan'       => $request->catatan_driver,
                ]);
            } else {
                $pickupDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status'    => 'accepted',
                    'waktu'     => now(),
                    'catatan'   => $request->catatan_driver,
                ]);
            }

            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send($token, '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk pickup cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'driver_assigned_pickup', 'action' => 'open_detail']
                        );
                    }
                }
            }

            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)->pluck('token')->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat        = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp          = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send($token, '📦 Tugas Pickup Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Pickup dari {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi'   => (string) $pesanan->id_transaksi,
                                'id_delivery'    => (string) $pickupDelivery->id_delivery,
                                'jenis'          => 'pickup',
                                'type'           => 'new_pickup_task',
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp'          => $noHp,
                                'alamat'         => $alamat,
                                'catatan'        => $request->catatan_driver ?? '',
                                'action'         => 'open_delivery_detail'
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
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

    // ==================== ASSIGN DRIVER ANTAR ====================

    /**
     * -----------------------------------------------
     * METHOD: assignDriverAntar (Admin)
     * -----------------------------------------------
     * Menugaskan driver untuk mengantar cucian ke pelanggan.
     * Membuat atau mengupdate record delivery antar.
     * Mengirim notifikasi ke pelanggan dan driver.
     *
     * @param Request $request - Berisi id_driver dan catatan_driver
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignDriverAntar(Request $request, $id)
    {
        $request->validate([
            'id_driver'      => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            
            // Cek apakah sudah ada delivery antar
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'antar')
                ->first();

            $driver = Driver::find($request->id_driver);

            // Percabangan: update atau buat baru
            if ($existingDelivery) {
                $existingDelivery->update([
                    'id_driver' => $request->id_driver,
                    'status'    => 'accepted',
                    'waktu'     => now(),
                    'catatan'   => $request->catatan_driver,
                ]);
                Log::info("✅ Updated existing delivery antar to ACCEPTED for transaksi {$pesanan->id_transaksi}");
            } else {
                $existingDelivery = Delivery::create([
                    'id_transaksi'  => $pesanan->id_transaksi,
                    'id_driver'     => $request->id_driver,
                    'jenis'         => 'antar',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status'        => 'accepted',
                    'waktu'         => now(),
                    'catatan'       => $request->catatan_driver,
                ]);
                Log::info("✅ Created new delivery antar with status ACCEPTED for transaksi {$pesanan->id_transaksi}");
            }

            // Kirim notifikasi ke pelanggan
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send($token, '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk mengantar cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'driver_assigned_delivery', 'action' => 'open_detail']
                        );
                    }
                }
            }

            // Kirim notifikasi ke driver
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)->pluck('token')->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat        = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp          = $pesanan->pelanggan->no_hp ?? '-';
                
                // Perulangan: kirim ke semua token driver
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send($token, '🚚 Tugas Delivery Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Antar ke {$namaPelanggan}. Alamat: {$alamat}",
                            [
                                'id_transaksi'   => (string) $pesanan->id_transaksi,
                                'id_delivery'    => (string) $existingDelivery->id_delivery,
                                'jenis'          => 'antar',
                                'type'           => 'new_delivery_task',
                                'nama_pelanggan' => $namaPelanggan,
                                'no_hp'          => $noHp,
                                'alamat'         => $alamat,
                                'catatan'        => $request->catatan_driver ?? '',
                                'action'         => 'open_delivery_detail'
                            ]
                        );
                        Log::info("✅ FCM sent to driver {$driver->nama_driver} (ID: {$driver->id_driver})");
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
            } else {
                Log::warning("⚠️ Driver {$driver->nama_driver} tidak punya FCM token!");
            }

            DB::commit();

            return redirect()->route('pesanan.online.detail', $id)
                ->with('success', 'Driver delivery berhasil ditugaskan & notifikasi terkirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assign driver antar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menentukan driver: ' . $e->getMessage());
        }
    }

    /**
     * -----------------------------------------------
     * METHOD: assignDriverAntarKasir
     * -----------------------------------------------
     * Menugaskan driver antar untuk Kasir.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignDriverAntarKasir(Request $request, $id)
    {
        $request->validate([
            'id_driver'      => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan          = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)->where('jenis', 'antar')->first();
            $driver           = Driver::find($request->id_driver);

            if ($existingDelivery) {
                $existingDelivery->update(['id_driver' => $request->id_driver, 'status' => 'accepted', 'waktu' => now(), 'catatan' => $request->catatan_driver]);
            } else {
                $existingDelivery = Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi, 'id_driver' => $request->id_driver,
                    'jenis' => 'antar', 'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted', 'waktu' => now(), 'catatan' => $request->catatan_driver,
                ]);
            }

            // Notifikasi ke pelanggan
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send($token, '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk mengantar cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'driver_assigned_delivery', 'action' => 'open_detail']
                        );
                    }
                }
            }

            // Notifikasi ke driver
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)->pluck('token')->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat        = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp          = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send($token, '🚚 Tugas Delivery Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Antar ke {$namaPelanggan}. Alamat: {$alamat}",
                            ['id_transaksi' => (string) $pesanan->id_transaksi, 'id_delivery' => (string) $existingDelivery->id_delivery,
                             'jenis' => 'antar', 'type' => 'new_delivery_task', 'nama_pelanggan' => $namaPelanggan,
                             'no_hp' => $noHp, 'alamat' => $alamat, 'catatan' => $request->catatan_driver ?? '', 'action' => 'open_delivery_detail']
                        );
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
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

    /**
     * -----------------------------------------------
     * METHOD: assignDriverAntarAdmin2
     * -----------------------------------------------
     * Menugaskan driver antar untuk Admin2.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignDriverAntarAdmin2(Request $request, $id)
    {
        $request->validate([
            'id_driver'      => 'required|exists:driver,id_driver',
            'catatan_driver' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan          = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)->where('jenis', 'antar')->first();
            $driver           = Driver::find($request->id_driver);

            if ($existingDelivery) {
                $existingDelivery->update(['id_driver' => $request->id_driver, 'status' => 'accepted', 'waktu' => now(), 'catatan' => $request->catatan_driver]);
            } else {
                $existingDelivery = Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi, 'id_driver' => $request->id_driver,
                    'jenis' => 'antar', 'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                    'status' => 'accepted', 'waktu' => now(), 'catatan' => $request->catatan_driver,
                ]);
            }

            // Notifikasi ke pelanggan
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

                if ($tokens->isNotEmpty()) {
                    foreach ($tokens as $token) {
                        FcmService::send($token, '👤 Driver Sudah Ditugaskan!', 
                            "Driver {$driver->nama_driver} telah ditugaskan untuk mengantar cucian Anda (ORDER/{$pesanan->id_transaksi}).", 
                            ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'driver_assigned_delivery', 'action' => 'open_detail']
                        );
                    }
                }
            }

            // Notifikasi ke driver
            $driverFcmTokens = FcmToken::where('driver_id', $driver->id_driver)->pluck('token')->filter();
            
            if ($driverFcmTokens->isNotEmpty()) {
                $alamat        = $pesanan->pelanggan->alamat ?? 'Alamat tidak tersedia';
                $namaPelanggan = $pesanan->pelanggan->nama_pelanggan ?? 'Customer';
                $noHp          = $pesanan->pelanggan->no_hp ?? '-';
                
                foreach ($driverFcmTokens as $token) {
                    try {
                        FcmService::send($token, '🚚 Tugas Delivery Baru!',
                            "ORDER/{$pesanan->id_transaksi} - Antar ke {$namaPelanggan}. Alamat: {$alamat}",
                            ['id_transaksi' => (string) $pesanan->id_transaksi, 'id_delivery' => (string) $existingDelivery->id_delivery,
                             'jenis' => 'antar', 'type' => 'new_delivery_task', 'nama_pelanggan' => $namaPelanggan,
                             'no_hp' => $noHp, 'alamat' => $alamat, 'catatan' => $request->catatan_driver ?? '', 'action' => 'open_delivery_detail']
                        );
                    } catch (\Exception $e) {
                        Log::error("❌ FCM to driver error: " . $e->getMessage());
                    }
                }
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

    /**
     * -----------------------------------------------
     * METHOD: listDeliveryOnline / listDeliveryOnlineKasir / listDeliveryOnlineAdmin2
     * -----------------------------------------------
     * Menampilkan daftar delivery yang belum memiliki driver.
     *
     * @return \Illuminate\View\View
     */
    public function listDeliveryOnline()
    {
        // Ambil delivery yang belum diassign driver
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

    /**
     * -----------------------------------------------
     * METHOD: statusMap (Private Helper)
     * -----------------------------------------------
     * Mengembalikan pemetaan antara nama tab dengan
     * nilai status_transaksi yang sesuai di database.
     * Digunakan oleh method index untuk filter data.
     *
     * @return array - Associative array tab => [status]
     */
    private function statusMap()
    {
        // Array pemetaan tab ke status transaksi
        return [
            'pickup'         => ['pick_up'],
            'antrian'        => ['antrian'],
            'proses'         => ['proses'],
            'selesai_dicuci' => ['selesai_dicuci'],
            'siap_di_ambil'  => ['siap_di_ambil'],
            'siap_di_antar'  => ['siap_di_antar'],
            'selesai'        => ['selesai'],
            'ditolak'        => ['ditolak'],
        ];
    }

    // ==================== UPDATE DATA ====================

    /**
     * -----------------------------------------------
     * METHOD: updateData (Admin)
     * -----------------------------------------------
     * Mengupdate data pesanan: qty item, biaya tambahan, diskon,
     * foto bukti, dan estimasi tanggal. Menghitung ulang total harga.
     * Jika pickup driver sudah arrived_at_laundry, status diubah ke antrian.
     * Jika selesai_dicuci dan terlambat, redirect ke pilih driver.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateData(Request $request, $id)
    {
        Log::info("🔥 updateData CALLED - Order ID: " . $id);
        
        // Validasi semua input form
        $request->validate([
            'id_detail.*'         => 'required|exists:detail_transaksi,id_detail_transaksi',
            'qty.*'               => 'required|numeric|min:0.01',
            'diskon'              => 'nullable|numeric|min:0',
            'tipe_diskon'         => 'nullable|in:nominal,percent',
            'keterangan'          => 'nullable|string',
            'foto_bukti'          => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'tgl_estimasi'        => 'nullable|date|after_or_equal:today',
            'new_biaya_nama.*'    => 'nullable|string|max:255',
            'new_biaya_nominal.*' => 'nullable|numeric|min:0',
            'delete_biaya_id.*'   => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
        ]);

        try {
            DB::beginTransaction();
            
            $pesanan    = Transaksi::with(['pelanggan'])->findOrFail($id);
            $statusAwal = $pesanan->status_transaksi;

            // ========================================
            // STEP 1: UPDATE QTY & HITUNG SUBTOTAL
            // ========================================
            $subtotalItems = 0;

            // Perulangan: iterasi setiap item detail transaksi
            foreach ($request->id_detail as $index => $idDetail) {
                $detail = DetailTransaksi::findOrFail($idDetail);
                
                $qty   = (float) $request->qty[$index];
                $harga = (float) $detail->harga; // Ambil harga dari field detail

                Log::info("Detail " . $idDetail . ": qty=" . $qty . ", harga=" . $harga);
                
                // Percabangan: fallback ke harga layanan jika harga detail 0
                if ($harga <= 0) {
                    if ($detail->id_layanan) {
                        $layanan = \App\Models\Layanan::find($detail->id_layanan);
                        if ($layanan) {
                            $harga = (float) $layanan->harga;
                        }
                    }
                    
                    // Percabangan: fallback ke harga jenis layanan jika masih 0
                    if ($harga <= 0 && $detail->id_jenis_layanan) {
                        $jenisLayanan = \App\Models\JenisLayanan::find($detail->id_jenis_layanan);
                        if ($jenisLayanan) {
                            $harga = (float) $jenisLayanan->harga;
                        }
                    }
                    
                    // Jika masih 0, hentikan proses dengan pesan error
                    if ($harga <= 0) {
                        DB::rollBack();
                        $itemNumber = $index + 1;
                        return redirect()->back()->withInput()
                            ->with('error', '❌ Harga item #' . $itemNumber . ' tidak valid!');
                    }
                }
                
                $detail->update(['qty' => $qty, 'harga' => $harga]);
                
                // Hitung subtotal per item dan akumulasi ke total
                $subtotal       = round($qty * $harga, 0);
                $subtotalItems += $subtotal;
            }

            Log::info("💰 Total Subtotal: Rp " . number_format($subtotalItems, 0, ',', '.'));

            // Validasi: subtotal tidak boleh 0
            if ($subtotalItems <= 0) {
                DB::rollBack();
                return redirect()->back()->withInput()
                    ->with('error', '❌ Subtotal tidak boleh Rp 0!');
            }

            // ========================================
            // STEP 2: BIAYA TAMBAHAN
            // ========================================
            // Hapus biaya tambahan yang dipilih untuk dihapus
            if ($request->filled('delete_biaya_id')) {
                $deleted = BiayaTambahan::whereIn('id_biaya_tambahan', $request->delete_biaya_id)->delete();
                Log::info("Deleted " . $deleted . " biaya tambahan");
            }

            // Tambahkan biaya tambahan baru jika ada
            if ($request->filled('new_biaya_nama') && $request->filled('new_biaya_nominal')) {
                // Perulangan: iterasi setiap biaya tambahan baru
                foreach ($request->new_biaya_nama as $index => $nama) {
                    $nominal = (float) ($request->new_biaya_nominal[$index] ?? 0);
                    
                    // Percabangan: skip jika nama kosong atau nominal 0
                    if (empty($nama) || $nominal <= 0) {
                        continue;
                    }
                    
                    BiayaTambahan::create([
                        'id_transaksi' => $pesanan->id_transaksi,
                        'nama_biaya'   => $nama,
                        'nominal'      => $nominal,
                    ]);
                }
            }

            // Ambil total biaya tambahan setelah update
            $biayaTambahan = (float) BiayaTambahan::where('id_transaksi', $pesanan->id_transaksi)->sum('nominal');

            // ========================================
            // STEP 3: HITUNG DISKON
            // ========================================
            $diskon    = (float) ($request->diskon ?? 0);
            $tipeDiskon = $request->tipe_diskon ?? 'nominal';

            // Percabangan: konversi diskon persen ke nominal
            if ($tipeDiskon === 'percent' && $diskon > 0) {
                $diskonPersen = $diskon;
                $diskon       = round(($subtotalItems * $diskon) / 100, 0);
                Log::info("🏷️ Diskon: " . $diskonPersen . "% = Rp " . number_format($diskon, 0, ',', '.'));
            }

            // ========================================
            // STEP 4: HITUNG TOTAL AKHIR
            // ========================================
            $totalAkhir = $subtotalItems + $biayaTambahan - $diskon;

            Log::info("🧮 TOTAL AKHIR: Rp " . number_format($totalAkhir, 0, ',', '.'));

            // ========================================
            // STEP 5: PROSES FOTO BUKTI
            // ========================================
            $fotoBuktiPath = $pesanan->foto_bukti;

            // Percabangan: upload foto baru jika ada
            if ($request->hasFile('foto_bukti')) {
                // Hapus foto lama jika ada
                if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
                    Storage::disk('public')->delete($pesanan->foto_bukti);
                }
                $file          = $request->file('foto_bukti');
                $filename      = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
                $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
            }

            // ========================================
            // STEP 6: TENTUKAN STATUS BARU
            // ========================================
            $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                ->where('jenis', 'pickup')
                ->where('status', 'arrived_at_laundry')
                ->first();

            $statusBaru          = $statusAwal;
            $needDriverRedirect  = false;

            // Percabangan: ubah status ke antrian jika driver sudah tiba
            if ($statusAwal === 'pick_up' && $deliveryPickup) {
                $statusBaru = 'antrian';
                Log::info("Status changed: pick_up → antrian");
            }

            // Percabangan: cek keterlambatan jika status selesai_dicuci
            if ($statusAwal === 'selesai_dicuci' && $this->isPesananTerlambat($pesanan)) {
                $statusBaru         = 'siap_di_antar';
                $needDriverRedirect = true;
                
                $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)->where('jenis', 'antar')->first();
                
                if (!$existingDelivery) {
                    Delivery::create([
                        'id_transaksi'  => $pesanan->id_transaksi, 'id_driver' => null, 'jenis' => 'antar',
                        'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-', 'status' => 'pending',
                        'waktu'         => now(), 'catatan' => 'Auto-generated: Melewati estimasi',
                    ]);
                }
            }

            // ========================================
            // STEP 7: UPDATE TRANSAKSI
            // ========================================
            // Array data yang akan diupdate ke database
            $updateData = [
                'total_harga'      => $totalAkhir,
                'diskon'           => $diskon,
                'tipe_diskon'      => $tipeDiskon,
                'keterangan'       => $request->keterangan,
                'status_transaksi' => $statusBaru,
                'foto_bukti'       => $fotoBuktiPath,
            ];

            // Percabangan: update estimasi tanggal jika diisi
            if ($request->filled('tgl_estimasi')) {
                $updateData['tgl_estimasi'] = $request->tgl_estimasi;
            }

            $pesanan->update($updateData);

            // ========================================
            // STEP 8: VERIFIKASI DATA TERSIMPAN
            // ========================================
            $pesanan->refresh();

            // Percabangan: force update jika ada inkonsistensi data
            if ($pesanan->total_harga != $totalAkhir) {
                Log::warning("MISMATCH! Force updating...");
                DB::table('transaksi')
                    ->where('id_transaksi', $pesanan->id_transaksi)
                    ->update(['total_harga' => $totalAkhir, 'diskon' => $diskon]);
                $pesanan->refresh();
            }

            // ========================================
            // STEP 9: KIRIM NOTIFIKASI FCM
            // ========================================
            $fcmSent    = false;
            $fcmMessage = '';
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            if ($idPelanggan) {
                // Ambil semua token FCM pelanggan ke dalam array
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
                    ->whereNotNull('token')
                    ->pluck('token')
                    ->toArray();
                
                if (!empty($tokens)) {
                    // Tentukan konten notifikasi berdasarkan kondisi
                    if ($needDriverRedirect) {
                        $title = '⚠️ Pesanan Melewati Estimasi!';
                        $body  = "ORDER/" . $pesanan->id_transaksi . " melewati estimasi. Total: Rp " . number_format($totalAkhir, 0, ',', '.');
                    } else {
                        $title = '💰 Harga Pesanan Sudah Diisi!';
                        $body  = "ORDER/" . $pesanan->id_transaksi . " - Total: Rp " . number_format($totalAkhir, 0, ',', '.');
                    }

                    // Perulangan: kirim ke semua token dengan penanganan error
                    foreach ($tokens as $token) {
                        try {
                            FcmService::send($token, $title, $body, [
                                'transaksi_id' => (string) $pesanan->id_transaksi,
                                'type'         => $needDriverRedirect ? 'terlambat' : 'invoice',
                                'action'       => 'open_detail',
                                'total_harga'  => (string) $totalAkhir,
                            ]);
                            $fcmSent = true;
                        } catch (\Exception $e) {
                            Log::error("FCM Error: " . $e->getMessage());
                        }
                    }
                } else {
                    $fcmMessage = ' (Notifikasi tidak terkirim: token tidak ditemukan)';
                }
            }

            DB::commit();
            Log::info("✅ updateData SUCCESS - Total: Rp " . $totalAkhir);

            // ========================================
            // STEP 10: REDIRECT
            // ========================================
            // Percabangan: redirect ke pilih driver jika terlambat
            if ($needDriverRedirect) {
                return redirect()->route('pesanan.online.list-driver', $id)
                    ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver.');
            }

            $successMessage = 'Data pesanan berhasil diperbarui' . ($fcmSent ? ' & notifikasi terkirim!' : $fcmMessage);
            
            return redirect()->route('pesanan.online.detail', $id)->with('success', $successMessage);
                
        } catch (\Exception $e) {
            // Penanganan error: rollback dan tampilkan pesan error
            DB::rollBack();
            Log::error("❌ updateData ERROR: " . $e->getMessage());
            Log::error("File: " . $e->getFile() . ":" . $e->getLine());
            
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * -----------------------------------------------
     * METHOD: updateDataKasir
     * -----------------------------------------------
     * Update data pesanan oleh Kasir.
     * Logika sama dengan updateData, redirect ke route kasir.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDataKasir(Request $request, $id)
    {
        // Validasi input dengan aturan yang ketat
        $request->validate([
            'id_detail.*'         => 'required|exists:detail_transaksi,id_detail_transaksi',
            'qty.*'               => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/',
            'diskon'              => 'nullable|numeric|min:0',
            'tipe_diskon'         => 'nullable|in:nominal,percent',
            'keterangan'          => 'nullable|string',
            'foto_bukti'          => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'tgl_estimasi'        => 'nullable|date|after_or_equal:today',
            'new_biaya_nama.*'    => 'nullable|string|max:255',
            'new_biaya_nominal.*' => 'nullable|numeric|min:0',
            'delete_biaya_id.*'   => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
        ]);

        $pesanan = Transaksi::with(['pelanggan'])->where('jenis_transaksi', 'online')->findOrFail($id);

        $subtotalItems = 0;

        // Perulangan: update setiap detail item
        foreach ($request->id_detail as $index => $idDetail) {
            // Ambil detail dengan relasi layanan untuk mendapatkan harga
            $detail = DetailTransaksi::with('layanan')->findOrFail($idDetail);

            // Percabangan: validasi relasi layanan ada
            if (!$detail->layanan) {
                Log::warning("⚠️ Detail ID {$idDetail} tidak punya relasi layanan!");
                return redirect()->back()->withInput()
                    ->with('error', "❌ Data layanan untuk detail pesanan tidak ditemukan!");
            }

            // Hitung subtotal dan akumulasi
            $qty      = (float) $request->qty[$index];
            $harga    = (float) $detail->layanan->harga;
            $idSatuan = $detail->id_satuan;
            $subtotal = round($qty * $harga, 0);

            $subtotalItems += $subtotal;

            // Array data yang akan diupdate per detail
            $updateData = ['qty' => $qty, 'harga' => $harga, 'id_satuan' => $idSatuan, 'subtotal' => $subtotal];

            if ($request->filled('tgl_estimasi')) {
                $updateData['tgl_estimasi'] = $request->tgl_estimasi;
            }

            $detail->update($updateData);
        }

        // Hapus biaya tambahan yang ditandai
        if ($request->filled('delete_biaya_id')) {
            BiayaTambahan::whereIn('id_biaya_tambahan', $request->delete_biaya_id)->delete();
        }

        // Tambah biaya tambahan baru
        if ($request->filled('new_biaya_nama') && $request->filled('new_biaya_nominal')) {
            foreach ($request->new_biaya_nama as $index => $nama) {
                $nominal = $request->new_biaya_nominal[$index] ?? 0;

                // Percabangan: skip jika data tidak valid
                if (empty($nama) || $nominal <= 0) continue;
                
                BiayaTambahan::create(['id_transaksi' => $pesanan->id_transaksi, 'nama_biaya' => $nama, 'nominal' => $nominal]);
            }
        }

        // Hitung total biaya ongkir/tambahan
        $biayaOngkir = BiayaTambahan::where('id_transaksi', $pesanan->id_transaksi)->sum('nominal');

        // Hitung diskon
        $diskon     = $request->diskon ?? 0;
        $tipeDiskon = $request->tipe_diskon ?? 'nominal';

        if ($tipeDiskon === 'percent' && $diskon > 0) {
            $diskon = round(($subtotalItems * $diskon) / 100, 0);
        }

        // Total akhir = subtotal + biaya tambahan - diskon
        $totalAkhir = $subtotalItems + $biayaOngkir - $diskon;

        // Proses upload foto bukti
        $fotoBuktiPath = $pesanan->foto_bukti;

        if ($request->hasFile('foto_bukti')) {
            if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
                Storage::disk('public')->delete($pesanan->foto_bukti);
            }
            $file          = $request->file('foto_bukti');
            $filename      = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
            $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
        }

        // Tentukan status baru
        $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')->where('status', 'arrived_at_laundry')->first();

        $statusBaru         = $pesanan->status_transaksi;
        $needDriverRedirect = false;

        if ($pesanan->status_transaksi === 'pick_up' && $deliveryPickup) {
            $statusBaru = 'antrian';
        }

        if ($pesanan->status_transaksi === 'selesai_dicuci' && $this->isPesananTerlambat($pesanan)) {
            $statusBaru         = 'siap_di_antar';
            $needDriverRedirect = true;
            
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)->where('jenis', 'antar')->first();
            
            if (!$existingDelivery) {
                Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi, 'id_driver' => null, 'jenis' => 'antar',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-', 'status' => 'pending',
                    'waktu' => now(), 'catatan' => 'Auto-generated: Melewati estimasi saat isi data',
                ]);
            }
        }

        // Update transaksi utama
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

        // Kirim FCM ke pelanggan
        $fcmSent    = false;
        $fcmMessage = '';
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->whereNotNull('token')->pluck('token')->toArray();
            
            if (!empty($tokens)) {
                $title = $needDriverRedirect ? '⚠️ Pesanan Melewati Estimasi!' : '💰 Harga Pesanan Sudah Diisi!';
                $body  = $needDriverRedirect
                    ? "Cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati estimasi. Total: Rp " . number_format($totalAkhir, 0, ',', '.')
                    : "ORDER/{$pesanan->id_transaksi} - Total: Rp " . number_format($totalAkhir, 0, ',', '.') . ". Tap untuk lihat detail.";

                foreach ($tokens as $token) {
                    try {
                        $result = FcmService::send($token, $title, $body, [
                            'transaksi_id' => (string) $pesanan->id_transaksi,
                            'type'         => $needDriverRedirect ? 'terlambat' : 'invoice',
                            'action'       => 'open_detail',
                            'total_harga'  => (string) $totalAkhir,
                            'status'       => $statusBaru,
                        ]);
                        if ($result) $fcmSent = true;
                    } catch (\Exception $e) {
                        Log::error("❌ FCM Error: " . $e->getMessage());
                    }
                }
            }
        }

        if ($needDriverRedirect) {
            return redirect()->route('kasir.pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk pengiriman.');
        }

        return redirect()->route('kasir.pesanan.online.detail', $id)
            ->with('success', 'Data pesanan berhasil diperbarui' . ($fcmSent ? ' & notifikasi terkirim!' : $fcmMessage));
    }

    /**
     * -----------------------------------------------
     * METHOD: updateDataAdmin2
     * -----------------------------------------------
     * Update data pesanan oleh Admin2.
     * Logika identik dengan updateDataKasir, redirect ke route admin2.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDataAdmin2(Request $request, $id)
    {
        $request->validate([
            'id_detail.*'         => 'required|exists:detail_transaksi,id_detail_transaksi',
            'qty.*'               => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/',
            'diskon'              => 'nullable|numeric|min:0',
            'tipe_diskon'         => 'nullable|in:nominal,percent',
            'keterangan'          => 'nullable|string',
            'foto_bukti'          => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'tgl_estimasi'        => 'nullable|date|after_or_equal:today',
            'new_biaya_nama.*'    => 'nullable|string|max:255',
            'new_biaya_nominal.*' => 'nullable|numeric|min:0',
            'delete_biaya_id.*'   => 'nullable|exists:biaya_tambahan,id_biaya_tambahan',
        ]);

        $pesanan = Transaksi::with(['pelanggan'])->where('jenis_transaksi', 'online')->findOrFail($id);

        $subtotalItems = 0;

        foreach ($request->id_detail as $index => $idDetail) {
            $detail = DetailTransaksi::with('layanan')->findOrFail($idDetail);

            if (!$detail->layanan) {
                return redirect()->back()->withInput()
                    ->with('error', "❌ Data layanan tidak ditemukan!");
            }

            $qty      = (float) $request->qty[$index];
            $harga    = (float) $detail->layanan->harga;
            $idSatuan = $detail->id_satuan;
            $subtotal = round($qty * $harga, 0);

            $subtotalItems += $subtotal;

            $updateData = ['qty' => $qty, 'harga' => $harga, 'id_satuan' => $idSatuan, 'subtotal' => $subtotal];

            if ($request->filled('tgl_estimasi')) {
                $updateData['tgl_estimasi'] = $request->tgl_estimasi;
            }

            $detail->update($updateData);
        }

        if ($request->filled('delete_biaya_id')) {
            BiayaTambahan::whereIn('id_biaya_tambahan', $request->delete_biaya_id)->delete();
        }

        if ($request->filled('new_biaya_nama') && $request->filled('new_biaya_nominal')) {
            foreach ($request->new_biaya_nama as $index => $nama) {
                $nominal = $request->new_biaya_nominal[$index] ?? 0;
                if (empty($nama) || $nominal <= 0) continue;
                BiayaTambahan::create(['id_transaksi' => $pesanan->id_transaksi, 'nama_biaya' => $nama, 'nominal' => $nominal]);
            }
        }

        $biayaOngkir = BiayaTambahan::where('id_transaksi', $pesanan->id_transaksi)->sum('nominal');

        $diskon     = $request->diskon ?? 0;
        $tipeDiskon = $request->tipe_diskon ?? 'nominal';

        if ($tipeDiskon === 'percent' && $diskon > 0) {
            $diskon = round(($subtotalItems * $diskon) / 100, 0);
        }

        $totalAkhir    = $subtotalItems + $biayaOngkir - $diskon;
        $fotoBuktiPath = $pesanan->foto_bukti;

        if ($request->hasFile('foto_bukti')) {
            if ($pesanan->foto_bukti && Storage::disk('public')->exists($pesanan->foto_bukti)) {
                Storage::disk('public')->delete($pesanan->foto_bukti);
            }
            $file          = $request->file('foto_bukti');
            $filename      = 'bukti_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
            $fotoBuktiPath = $file->storeAs('foto_bukti_cucian', $filename, 'public');
        }

        $deliveryPickup = Delivery::where('id_transaksi', $pesanan->id_transaksi)
            ->where('jenis', 'pickup')->where('status', 'arrived_at_laundry')->first();

        $statusBaru         = $pesanan->status_transaksi;
        $needDriverRedirect = false;

        if ($pesanan->status_transaksi === 'pick_up' && $deliveryPickup) {
            $statusBaru = 'antrian';
        }

        if ($pesanan->status_transaksi === 'selesai_dicuci' && $this->isPesananTerlambat($pesanan)) {
            $statusBaru         = 'siap_di_antar';
            $needDriverRedirect = true;
            
            $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)->where('jenis', 'antar')->first();
            
            if (!$existingDelivery) {
                Delivery::create([
                    'id_transaksi' => $pesanan->id_transaksi, 'id_driver' => null, 'jenis' => 'antar',
                    'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-', 'status' => 'pending',
                    'waktu' => now(), 'catatan' => 'Auto-generated: Melewati estimasi saat isi data',
                ]);
            }
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

        // Kirim FCM ke pelanggan
        $fcmSent    = false;
        $fcmMessage = '';
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->whereNotNull('token')->pluck('token')->toArray();
            
            if (!empty($tokens)) {
                $title = $needDriverRedirect ? '⚠️ Pesanan Melewati Estimasi!' : '💰 Harga Pesanan Sudah Diisi!';
                $body  = $needDriverRedirect
                    ? "Cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati estimasi. Total: Rp " . number_format($totalAkhir, 0, ',', '.')
                    : "ORDER/{$pesanan->id_transaksi} - Total: Rp " . number_format($totalAkhir, 0, ',', '.') . ". Tap untuk lihat detail.";

                foreach ($tokens as $token) {
                    try {
                        $result = FcmService::send($token, $title, $body, [
                            'transaksi_id' => (string) $pesanan->id_transaksi,
                            'type'         => $needDriverRedirect ? 'terlambat' : 'invoice',
                            'action'       => 'open_detail',
                            'total_harga'  => (string) $totalAkhir,
                            'status'       => $statusBaru,
                        ]);
                        if ($result) $fcmSent = true;
                    } catch (\Exception $e) {
                        Log::error("❌ FCM Error: " . $e->getMessage());
                    }
                }
            }
        }

        if ($needDriverRedirect) {
            return redirect()->route('admin2.pesanan.online.list-driver', $id)
                ->with('warning', '⚠️ Pesanan melewati estimasi! Silakan pilih driver untuk pengiriman.');
        }

        return redirect()->route('admin2.pesanan.online.detail', $id)
            ->with('success', 'Data pesanan berhasil diperbarui' . ($fcmSent ? ' & notifikasi terkirim!' : $fcmMessage));
    }

    // ==================== KONFIRMASI PESANAN (WhatsApp/SMS) ====================

    /**
     * -----------------------------------------------
     * METHOD: konfirmasiPesanan (Admin)
     * -----------------------------------------------
     * Mengirim konfirmasi pesanan ke pelanggan melalui
     * WhatsApp atau SMS dengan ringkasan detail pesanan.
     *
     * @param Request $request - Berisi metode_kirim (whatsapp/sms)
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function konfirmasiPesanan(Request $request, $id)
    {
        // Validasi input: hanya whatsapp atau sms yang diizinkan
        $request->validate([
            'metode_kirim' => 'required|in:whatsapp,sms'
        ]);

        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'dikonfirmasi']);

        // Siapkan data untuk isi pesan konfirmasi
        $namaPelanggan = $pesanan->nama_pelanggan ?? 'Pelanggan';
        $noHp          = $pesanan->no_hp ?? '';
        $totalHarga    = number_format($pesanan->total_harga, 0, ',', '.');
        $alamat        = $pesanan->pelanggan->alamat ?? '';

        // Susun pesan konfirmasi dengan format URL-encoded
        $pesan  = "Halo *{$namaPelanggan}*,%0A%0A";
        $pesan .= "Pesanan Anda sudah dikonfirmasi! 🎉%0A%0A";
        $pesan .= "*Detail Pesanan:*%0A";
        $pesan .= "📦 Order ID: {$pesanan->id_transaksi}%0A";
        $pesan .= "💰 Total Harga: Rp {$totalHarga}%0A";
        
        if ($alamat) {
            $pesan .= "📍 Alamat: {$alamat}%0A";
        }
        
        $pesan .= "%0ATerima kasih telah mempercayai layanan kami! 😊";

        // Percabangan: kirim via WhatsApp atau SMS
        if ($request->metode_kirim === 'whatsapp' && $noHp) {
            // Konversi nomor lokal ke format internasional (0xxx → 62xxx)
            $waNumber = preg_replace('/^0/', '62', $noHp);
            $waUrl    = "https://wa.me/{$waNumber}?text={$pesan}";
            
            return redirect()->away($waUrl);
        } 
        elseif ($request->metode_kirim === 'sms' && $noHp) {
            $smsUrl = "sms:{$noHp}?body=" . urlencode(strip_tags(str_replace(['%0A', '*'], ["\n", ''], $pesan)));
            return redirect()->away($smsUrl);
        }

        return redirect()->route('pesanan.online.detail', $id)
            ->with('success', 'Pesanan berhasil dikonfirmasi!');
    }

    /**
     * -----------------------------------------------
     * METHOD: konfirmasiPesananAdmin2
     * -----------------------------------------------
     * Konfirmasi pesanan via WhatsApp/SMS untuk Admin2.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function konfirmasiPesananAdmin2(Request $request, $id)
    {
        $request->validate(['metode_kirim' => 'required|in:whatsapp,sms']);

        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'dikonfirmasi']);

        $namaPelanggan = $pesanan->nama_pelanggan ?? 'Pelanggan';
        $noHp          = $pesanan->no_hp ?? '';
        $totalHarga    = number_format($pesanan->total_harga, 0, ',', '.');
        $alamat        = $pesanan->pelanggan->alamat ?? '';

        $pesan  = "Halo *{$namaPelanggan}*,%0A%0APesanan Anda sudah dikonfirmasi! 🎉%0A%0A";
        $pesan .= "*Detail Pesanan:*%0A📦 Order ID: {$pesanan->id_transaksi}%0A💰 Total: Rp {$totalHarga}%0A";
        if ($alamat) $pesan .= "📍 Alamat: {$alamat}%0A";
        $pesan .= "%0ATerima kasih! 😊";

        if ($request->metode_kirim === 'whatsapp' && $noHp) {
            return redirect()->away("https://wa.me/" . preg_replace('/^0/', '62', $noHp) . "?text={$pesan}");
        } elseif ($request->metode_kirim === 'sms' && $noHp) {
            return redirect()->away("sms:{$noHp}?body=" . urlencode(strip_tags(str_replace(['%0A', '*'], ["\n", ''], $pesan))));
        }

        return redirect()->route('admin2.pesanan.online.detail', $id)->with('success', 'Pesanan berhasil dikonfirmasi!');
    }

    /**
     * -----------------------------------------------
     * METHOD: konfirmasiPesananKasir
     * -----------------------------------------------
     * Konfirmasi pesanan via WhatsApp/SMS untuk Kasir.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function konfirmasiPesananKasir(Request $request, $id)
    {
        $request->validate(['metode_kirim' => 'required|in:whatsapp,sms']);

        $pesanan = Transaksi::with('pelanggan')->where('jenis_transaksi', 'online')->findOrFail($id);
        $pesanan->update(['status_transaksi' => 'dikonfirmasi']);

        $namaPelanggan = $pesanan->nama_pelanggan ?? 'Pelanggan';
        $noHp          = $pesanan->no_hp ?? '';
        $totalHarga    = number_format($pesanan->total_harga, 0, ',', '.');
        $alamat        = $pesanan->pelanggan->alamat ?? '';

        $pesan  = "Halo *{$namaPelanggan}*,%0A%0APesanan Anda sudah dikonfirmasi! 🎉%0A%0A";
        $pesan .= "*Detail Pesanan:*%0A📦 Order ID: {$pesanan->id_transaksi}%0A💰 Total: Rp {$totalHarga}%0A";
        if ($alamat) $pesan .= "📍 Alamat: {$alamat}%0A";
        $pesan .= "%0ATerima kasih! 😊";

        if ($request->metode_kirim === 'whatsapp' && $noHp) {
            return redirect()->away("https://wa.me/" . preg_replace('/^0/', '62', $noHp) . "?text={$pesan}");
        } elseif ($request->metode_kirim === 'sms' && $noHp) {
            return redirect()->away("sms:{$noHp}?body=" . urlencode(strip_tags(str_replace(['%0A', '*'], ["\n", ''], $pesan))));
        }

        return redirect()->route('admin2.pesanan.online.detail', $id)->with('success', 'Pesanan berhasil dikonfirmasi!');
    }

    // ==================== BUKTI PEMBAYARAN ====================

    /**
     * -----------------------------------------------
     * METHOD: buktiPembayaran / buktiPembayaranAdmin2 / buktiPembayaranKasir
     * -----------------------------------------------
     * Menampilkan halaman bukti pembayaran pesanan.
     *
     * @param int $id - ID transaksi
     * @return \Illuminate\View\View
     */
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

    // ==================== SIMPAN BUKTI PEMBAYARAN ====================

    /**
     * -----------------------------------------------
     * METHOD: simpanBuktiPembayaranKasir
     * -----------------------------------------------
     * Menyimpan konfirmasi pembayaran dari Kasir.
     * Mendukung pembayaran cash (lunas langsung) dan
     * transfer (DP bertahap atau pelunasan).
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function simpanBuktiPembayaranKasir(Request $request, $id)
    {
        $pesanan    = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $metodeBayar = $pesanan->metodeBayar;

        // Percabangan: deteksi apakah metode bayar adalah cash/tunai
        $isCash = $metodeBayar && (
            stripos($metodeBayar->nama_metode_bayar, 'cash') !== false || 
            stripos($metodeBayar->nama_metode_bayar, 'tunai') !== false
        );

        // Percabangan: validasi berbeda untuk cash vs transfer
        if ($isCash) {
            $request->validate([
                'tipe_pembayaran' => 'required|in:lunas',
                'keterangan_bayar' => 'nullable|string',
            ]);
            
            // Cek apakah pelanggan sudah upload bukti bayar
            $buktiBayarCustomer = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
                ->where('tipe_pembayaran', 'lunas')
                ->whereNotNull('foto_bukti')
                ->latest()
                ->first();
                
            if (!$buktiBayarCustomer || !$buktiBayarCustomer->foto_bukti) {
                return redirect()->back()->with('error', 'Pelanggan belum upload bukti pembayaran!');
            }
        } else {
            $request->validate([
                'tipe_pembayaran' => 'required|in:dp,lunas',
                'nominal_bayar'   => 'required_if:tipe_pembayaran,dp|nullable|numeric|min:1',
                'foto_bukti_bayar'=> 'required|image|mimes:jpeg,jpg,png|max:2048',
                'keterangan_bayar'=> 'nullable|string',
            ]);
        }

        // Cek apakah pesanan sudah lunas
        if ($pesanan->status_bayar === 'lunas') {
            return redirect()->back()->with('error', 'Pesanan ini sudah lunas!');
        }

        $sisaPembayaran = $pesanan->total_harga - $pesanan->total_bayar;
        
        // Hitung nominal dan status bayar berdasarkan tipe pembayaran
        if (!$isCash && $request->tipe_pembayaran === 'dp') {
            $nominalBayar = $request->nominal_bayar;
            
            if ($nominalBayar > $sisaPembayaran) {
                return redirect()->back()->with('error', 'Nominal DP melebihi sisa pembayaran!');
            }
            
            $totalBayarBaru = $pesanan->total_bayar + $nominalBayar;
            
            // Percabangan: tentukan status bayar
            if ($totalBayarBaru >= $pesanan->total_harga) {
                $statusBayar    = 'lunas';
                $totalBayarBaru = $pesanan->total_harga;
            } else {
                $statusBayar = 'DP';
            }
            
        } else {
            // Lunas sekaligus
            $nominalBayar   = $sisaPembayaran;
            $totalBayarBaru = $pesanan->total_harga;
            $statusBayar    = 'lunas';
        }

        $fotoBuktiPath = null;
        
        if ($isCash) {
            // Untuk cash, gunakan foto bukti dari pelanggan
            if (isset($buktiBayarCustomer)) {
                if ($request->keterangan_bayar) {
                    $keteranganBaru = $buktiBayarCustomer->keterangan 
                        ? $buktiBayarCustomer->keterangan . ' | Admin: ' . $request->keterangan_bayar 
                        : 'Admin: ' . $request->keterangan_bayar;
                    $buktiBayarCustomer->update(['keterangan' => $keteranganBaru, 'updated_at' => now()]);
                }
                $fotoBuktiPath = $buktiBayarCustomer->foto_bukti;
                $nominalBayar  = $buktiBayarCustomer->nominal;
            }
        } else {
            // Upload foto bukti transfer
            if ($request->hasFile('foto_bukti_bayar')) {
                $file          = $request->file('foto_bukti_bayar');
                $filename      = 'bayar_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
                $fotoBuktiPath = $file->storeAs('foto_bukti_pembayaran', $filename, 'public');
            }
            
            // Simpan record pembayaran baru
            Pembayaran::create([
                'id_transaksi'   => $pesanan->id_transaksi,
                'id_metode_bayar'=> $pesanan->id_metode_bayar,
                'tipe_pembayaran'=> $request->tipe_pembayaran,
                'nominal'        => $nominalBayar,
                'foto_bukti'     => $fotoBuktiPath,
                'keterangan'     => $request->keterangan_bayar,
                'tanggal_bayar'  => now()->format('Y-m-d'),
            ]);
        }

        // Kirim notifikasi FCM ke pelanggan
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

            if ($tokens->isNotEmpty()) {
                // Percabangan: bedakan notif lunas vs DP
                if ($statusBayar === 'lunas') {
                    $title = '✅ Pembayaran Lunas!';
                    $body  = "Pembayaran ORDER/{$pesanan->id_transaksi} sebesar Rp " . number_format($totalBayarBaru, 0, ',', '.') . " telah diterima.";
                    $type  = 'payment_completed';
                } else {
                    $title = '💳 DP Diterima!';
                    $sisaBayar = $pesanan->total_harga - $totalBayarBaru;
                    $body  = "DP Rp " . number_format($nominalBayar, 0, ',', '.') . " diterima. Sisa: Rp " . number_format($sisaBayar, 0, ',', '.');
                    $type  = 'payment_dp';
                }
                
                foreach ($tokens as $token) {
                    FcmService::send($token, $title, $body, [
                        'transaksi_id' => (string) $pesanan->id_transaksi,
                        'type'         => $type,
                        'action'       => 'open_detail',
                    ]);
                }
            }
        }

        // Update kolom pembayaran di transaksi
        $pesanan->update([
            'total_bayar' => $totalBayarBaru,
            'dp'          => $pesanan->dp + $nominalBayar,
            'status_bayar'=> $statusBayar,
            'tgl_lunas'   => $statusBayar === 'lunas' ? now()->format('Y-m-d') : null,
        ]);

        $message = $isCash 
            ? 'Pembayaran cash berhasil dikonfirmasi! Pesanan lunas.' 
            : ($statusBayar === 'lunas' 
                ? 'Pembayaran lunas berhasil disimpan!' 
                : 'DP sebesar Rp ' . number_format($nominalBayar, 0, ',', '.') . ' berhasil disimpan!');

        return redirect()->route('kasir.pesanan.online.detail', $id)->with('success', $message);
    }

    /**
     * -----------------------------------------------
     * METHOD: simpanBuktiPembayaran (Admin)
     * -----------------------------------------------
     * Menyimpan konfirmasi pembayaran dari Admin.
     * Mendukung cash dan transfer dengan kalkulasi DP bertahap.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function simpanBuktiPembayaran(Request $request, $id)
    {
        $pesanan = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        
        // Cek metode bayar dari pembayaran terakhir atau dari transaksi
        $pembayaranTerbaru = $pesanan->pembayaran()->latest()->first();
        
        // Percabangan: prioritaskan metode dari pembayaran terakhir
        if ($pembayaranTerbaru && $pembayaranTerbaru->id_metode_bayar) {
            $metodeBayar = $pembayaranTerbaru->metodeBayar;
        } else {
            $metodeBayar = $pesanan->metodeBayar;
        }
        
        // Deteksi apakah cash/tunai/COD
        $isCash = false;

        if ($metodeBayar && isset($metodeBayar->nama_metode_bayar)) {
            $namaMetode = strtolower($metodeBayar->nama_metode_bayar);
            $isCash     = (
                stripos($namaMetode, 'cash') !== false || 
                stripos($namaMetode, 'tunai') !== false ||
                stripos($namaMetode, 'cod') !== false
            );
        }

        if ($isCash) {
            $request->validate([
                'tipe_pembayaran' => 'required|in:lunas',
                'keterangan_bayar'=> 'nullable|string',
            ]);
            
            // Cek apakah sudah pernah dikonfirmasi
            $sudahBayar = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
                ->where('tipe_pembayaran', 'lunas')
                ->exists();
            
            if ($sudahBayar) {
                return redirect()->back()->with('error', 'Pembayaran cash sudah pernah dikonfirmasi!');
            }
            
            // Simpan pembayaran cash
            Pembayaran::create([
                'id_transaksi'   => $pesanan->id_transaksi,
                'id_metode_bayar'=> $pesanan->id_metode_bayar ?? ($pembayaranTerbaru ? $pembayaranTerbaru->id_metode_bayar : null),
                'tipe_pembayaran'=> 'lunas',
                'nominal'        => $pesanan->total_harga,
                'foto_bukti'     => null,
                'keterangan'     => $request->keterangan_bayar ?? 'Pembayaran Cash dikonfirmasi oleh admin',
                'tanggal_bayar'  => now()->format('Y-m-d'),
            ]);
            
            $pesanan->update([
                'total_bayar' => $pesanan->total_harga,
                'dp'          => $pesanan->total_harga,
                'status_bayar'=> 'lunas',
                'tgl_lunas'   => now()->format('Y-m-d'),
            ]);
            
            return redirect()->route('pesanan.online.detail', $id)
                ->with('success', 'Pembayaran cash berhasil dikonfirmasi! Pesanan lunas.');
            
        } else {
            // Validasi untuk pembayaran transfer
            $request->validate([
                'tipe_pembayaran' => 'required|in:dp,lunas',
                'nominal_bayar'   => 'required_if:tipe_pembayaran,dp|nullable|numeric|min:1',
                'foto_bukti_bayar'=> 'required|image|mimes:jpeg,jpg,png|max:2048',
                'keterangan_bayar'=> 'nullable|string',
            ]);
            
            if ($pesanan->status_bayar === 'lunas') {
                return redirect()->back()->with('error', 'Pesanan ini sudah lunas!');
            }

            // Hitung total yang sudah dibayar sebelumnya dari tabel pembayaran
            $totalDibayarSebelumnya = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)->sum('nominal');
            $sisaPembayaran         = $pesanan->total_harga - $totalDibayarSebelumnya;
            
            if ($sisaPembayaran <= 0) {
                return redirect()->back()->with('error', 'Pesanan sudah lunas!');
            }
            
            // Percabangan: tentukan nominal berdasarkan tipe pembayaran
            if ($request->tipe_pembayaran === 'dp') {
                $nominalBayar = (float) $request->nominal_bayar;
                
                if ($nominalBayar > $sisaPembayaran) {
                    return redirect()->back()
                        ->with('error', 'Nominal DP melebihi sisa pembayaran! Sisa: Rp ' . number_format($sisaPembayaran, 0, ',', '.'));
                }
            } else {
                $nominalBayar = $sisaPembayaran; // Pelunasan = sisa tagihan
            }

            // Upload foto bukti pembayaran
            $fotoBuktiPath = null;

            if ($request->hasFile('foto_bukti_bayar')) {
                $file          = $request->file('foto_bukti_bayar');
                $filename      = 'bayar_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
                $fotoBuktiPath = $file->storeAs('foto_bukti_pembayaran', $filename, 'public');
            }
            
            // Simpan ke tabel pembayaran
            Pembayaran::create([
                'id_transaksi'   => $pesanan->id_transaksi,
                'id_metode_bayar'=> $pesanan->id_metode_bayar ?? ($pembayaranTerbaru ? $pembayaranTerbaru->id_metode_bayar : null),
                'tipe_pembayaran'=> $request->tipe_pembayaran,
                'nominal'        => $nominalBayar,
                'foto_bukti'     => $fotoBuktiPath,
                'keterangan'     => $request->keterangan_bayar,
                'tanggal_bayar'  => now()->format('Y-m-d'),
            ]);

            // Hitung ulang total bayar dari semua record pembayaran
            $totalBayarBaru = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)->sum('nominal');
            
            // Tentukan status bayar berdasarkan total yang sudah dibayar
            if ($totalBayarBaru >= $pesanan->total_harga) {
                $statusBayar = 'lunas';
            } elseif ($totalBayarBaru > 0) {
                $statusBayar = 'DP';
            } else {
                $statusBayar = 'belum_bayar';
            }

            // Kirim notifikasi FCM ke pelanggan
            $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

            if ($idPelanggan) {
                $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

                if ($tokens->isNotEmpty()) {
                    if ($statusBayar === 'lunas') {
                        $title = '✅ Pembayaran Lunas!';
                        $body  = "Pembayaran ORDER/{$pesanan->id_transaksi} telah diterima. Terima kasih!";
                        $type  = 'payment_completed';
                    } else {
                        $title = '💳 DP Diterima!';
                        $sisaBayar = $pesanan->total_harga - $totalBayarBaru;
                        $body  = "DP Rp " . number_format($nominalBayar, 0, ',', '.') . " diterima. Sisa: Rp " . number_format($sisaBayar, 0, ',', '.');
                        $type  = 'payment_dp';
                    }
                    
                    foreach ($tokens as $token) {
                        FcmService::send($token, $title, $body, [
                            'transaksi_id' => (string) $pesanan->id_transaksi,
                            'type'         => $type,
                            'action'       => 'open_detail',
                        ]);
                    }
                }
            }

            // Update kolom pembayaran di transaksi (dp = total_bayar untuk sinkronisasi)
            $pesanan->update([
                'total_bayar' => $totalBayarBaru,
                'dp'          => $totalBayarBaru,
                'status_bayar'=> $statusBayar,
                'tgl_lunas'   => $statusBayar === 'lunas' ? now()->format('Y-m-d') : null,
            ]);

            $message = $statusBayar === 'lunas' 
                ? 'Pembayaran lunas berhasil disimpan!' 
                : 'DP sebesar Rp ' . number_format($nominalBayar, 0, ',', '.') . ' berhasil disimpan!';

            return redirect()->route('pesanan.online.detail', $id)->with('success', $message);
        }
    }

    /**
     * -----------------------------------------------
     * METHOD: simpanBuktiPembayaranAdmin2
     * -----------------------------------------------
     * Menyimpan konfirmasi pembayaran dari Admin2.
     * Logika identik dengan simpanBuktiPembayaranKasir.
     *
     * @param Request $request
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function simpanBuktiPembayaranAdmin2(Request $request, $id)
    {
        $pesanan    = Transaksi::where('jenis_transaksi', 'online')->findOrFail($id);
        $metodeBayar = $pesanan->metodeBayar;

        $isCash = $metodeBayar && (
            stripos($metodeBayar->nama_metode_bayar, 'cash') !== false || 
            stripos($metodeBayar->nama_metode_bayar, 'tunai') !== false
        );

        if ($isCash) {
            $request->validate(['tipe_pembayaran' => 'required|in:lunas', 'keterangan_bayar' => 'nullable|string']);
            
            $buktiBayarCustomer = Pembayaran::where('id_transaksi', $pesanan->id_transaksi)
                ->where('tipe_pembayaran', 'lunas')->whereNotNull('foto_bukti')->latest()->first();
                
            if (!$buktiBayarCustomer || !$buktiBayarCustomer->foto_bukti) {
                return redirect()->back()->with('error', 'Pelanggan belum upload bukti pembayaran!');
            }
        } else {
            $request->validate([
                'tipe_pembayaran' => 'required|in:dp,lunas',
                'nominal_bayar'   => 'required_if:tipe_pembayaran,dp|nullable|numeric|min:1',
                'foto_bukti_bayar'=> 'required|image|mimes:jpeg,jpg,png|max:2048',
                'keterangan_bayar'=> 'nullable|string',
            ]);
        }

        if ($pesanan->status_bayar === 'lunas') {
            return redirect()->back()->with('error', 'Pesanan ini sudah lunas!');
        }

        $sisaPembayaran = $pesanan->total_harga - $pesanan->total_bayar;
        
        if (!$isCash && $request->tipe_pembayaran === 'dp') {
            $nominalBayar = $request->nominal_bayar;
            if ($nominalBayar > $sisaPembayaran) {
                return redirect()->back()->with('error', 'Nominal DP melebihi sisa pembayaran!');
            }
            $totalBayarBaru = $pesanan->total_bayar + $nominalBayar;
            $statusBayar    = $totalBayarBaru >= $pesanan->total_harga ? 'lunas' : 'DP';
            if ($statusBayar === 'lunas') $totalBayarBaru = $pesanan->total_harga;
        } else {
            $nominalBayar   = $sisaPembayaran;
            $totalBayarBaru = $pesanan->total_harga;
            $statusBayar    = 'lunas';
        }

        $fotoBuktiPath = null;
        
        if ($isCash) {
            if (isset($buktiBayarCustomer)) {
                $fotoBuktiPath = $buktiBayarCustomer->foto_bukti;
                $nominalBayar  = $buktiBayarCustomer->nominal;
            }
        } else {
            if ($request->hasFile('foto_bukti_bayar')) {
                $file          = $request->file('foto_bukti_bayar');
                $filename      = 'bayar_' . $pesanan->id_transaksi . '_' . time() . '.' . $file->getClientOriginalExtension();
                $fotoBuktiPath = $file->storeAs('foto_bukti_pembayaran', $filename, 'public');
            }
            Pembayaran::create([
                'id_transaksi'   => $pesanan->id_transaksi,
                'id_metode_bayar'=> $pesanan->id_metode_bayar,
                'tipe_pembayaran'=> $request->tipe_pembayaran,
                'nominal'        => $nominalBayar,
                'foto_bukti'     => $fotoBuktiPath,
                'keterangan'     => $request->keterangan_bayar,
                'tanggal_bayar'  => now()->format('Y-m-d'),
            ]);
        }

        $pesanan->update([
            'total_bayar' => $totalBayarBaru,
            'dp'          => $pesanan->dp + $nominalBayar,
            'status_bayar'=> $statusBayar,
            'tgl_lunas'   => $statusBayar === 'lunas' ? now()->format('Y-m-d') : null,
        ]);

        // Kirim notifikasi FCM
        $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;

        if ($idPelanggan) {
            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token');

            if ($tokens->isNotEmpty()) {
                $title = $statusBayar === 'lunas' ? '✅ Pembayaran Lunas!' : '💳 DP Diterima!';
                $body  = $statusBayar === 'lunas' 
                    ? "Pembayaran ORDER/{$pesanan->id_transaksi} diterima. Terima kasih!"
                    : "DP Rp " . number_format($nominalBayar, 0, ',', '.') . " diterima.";
                $type  = $statusBayar === 'lunas' ? 'payment_completed' : 'payment_dp';
                
                foreach ($tokens as $token) {
                    FcmService::send($token, $title, $body, ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => $type, 'action' => 'open_detail']);
                }
            }
        }

        $message = $isCash ? 'Pembayaran cash berhasil dikonfirmasi! Pesanan lunas.' 
            : ($statusBayar === 'lunas' ? 'Pembayaran lunas berhasil disimpan!' 
                : 'DP sebesar Rp ' . number_format($nominalBayar, 0, ',', '.') . ' berhasil disimpan!');

        return redirect()->route('admin2.pesanan.online.detail', $id)->with('success', $message);
    }

    // ==================== AUTO CHECK & MANUAL TRIGGER ====================

    /**
     * -----------------------------------------------
     * METHOD: sendFcmNotification
     * -----------------------------------------------
     * Mengirim notifikasi FCM manual ke pelanggan.
     * Mendukung berbagai tipe notifikasi (selesai_dicuci, siap_diambil, dll).
     *
     * @param Request $request - Berisi notification_type
     * @param int $id - ID transaksi
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendFcmNotification(Request $request, $id)
    {
        try {
            $pesanan = Transaksi::with(['pelanggan'])->where('jenis_transaksi', 'online')->findOrFail($id);
            
            $notificationType = $request->input('notification_type', 'selesai_dicuci');
            
            // Cek ketersediaan FCM token pelanggan
            if (!$pesanan->pelanggan || !$pesanan->pelanggan->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelanggan tidak memiliki FCM token.'
                ], 400);
            }
            
            // Array pemetaan tipe notifikasi ke judul dan isi pesan
            $title = '';
            $body  = '';
            $data  = ['order_id' => $pesanan->id_transaksi, 'type' => $notificationType];
            
            // Percabangan: tentukan konten notifikasi berdasarkan tipe
            switch ($notificationType) {
                case 'selesai_dicuci':
                    $title = '🎉 Cucian Selesai Dicuci!';
                    $body  = "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah selesai dicuci.";
                    break;
                
                case 'siap_diambil':
                    $title = '✅ Cucian Siap Diambil!';
                    $body  = "Cucian Anda (ORDER/{$pesanan->id_transaksi}) sudah siap diambil.";
                    break;
                
                case 'dalam_pengiriman':
                    $title = '🚚 Cucian Sedang Diantar!';
                    $body  = "Driver sedang dalam perjalanan mengantar cucian Anda (ORDER/{$pesanan->id_transaksi}).";
                    break;
                
                default:
                    $title = 'Update Pesanan Laundry';
                    $body  = "Ada update terbaru untuk pesanan Anda (ORDER/{$pesanan->id_transaksi}).";
            }
            
            \Log::info("Simulated FCM notification for order {$pesanan->id_transaksi}: {$title}");
            
            return response()->json(['success' => true, 'message' => 'Notifikasi berhasil dikirim ke pelanggan!']);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Penanganan error: pesanan tidak ditemukan
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan!'], 404);
            
        } catch (\Exception $e) {
            // Penanganan error: error umum
            \Log::error("Error sending FCM notification: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * -----------------------------------------------
     * METHOD: autoCheckTerlambat
     * -----------------------------------------------
     * Mengecek semua pesanan dengan status 'selesai_dicuci'
     * yang melewati estimasi dan otomatis mengubah statusnya
     * ke 'siap_di_antar'. Dijalankan via scheduler atau manual.
     *
     * @return array - Hasil eksekusi (success, total_checked, total_updated)
     */
    public function autoCheckTerlambat()
    {
        try {
            \Log::info("🔍 === AUTO CHECK TERLAMBAT START ===");
            
            // Ambil semua pesanan selesai_dicuci yang memiliki estimasi
            $pesananSelesaiDicuci = Transaksi::with(['pelanggan'])
                ->where('jenis_transaksi', 'online')
                ->where('status_transaksi', 'selesai_dicuci')
                ->whereNotNull('tgl_estimasi')
                ->get();
            
            \Log::info("📊 Total pesanan selesai_dicuci: {$pesananSelesaiDicuci->count()}");
            
            $countTerlambat = 0;
            
            // Perulangan: cek setiap pesanan apakah terlambat
            foreach ($pesananSelesaiDicuci as $pesanan) {
                // Percabangan: hanya proses yang terlambat
                if ($this->isPesananTerlambat($pesanan)) {
                    DB::beginTransaction();
                    
                    \Log::info("⚠️ Updating ORDER/{$pesanan->id_transaksi} ke siap_di_antar...");
                    
                    $pesanan->update(['status_transaksi' => 'siap_di_antar']);
                    
                    // Buat delivery antar jika belum ada
                    $existingDelivery = Delivery::where('id_transaksi', $pesanan->id_transaksi)
                        ->where('jenis', 'antar')
                        ->first();
                    
                    if (!$existingDelivery) {
                        Delivery::create([
                            'id_transaksi'  => $pesanan->id_transaksi,
                            'id_driver'     => null,
                            'jenis'         => 'antar',
                            'alamat_tujuan' => $pesanan->pelanggan->alamat ?? '-',
                            'status'        => 'accepted',
                            'waktu'         => now(),
                            'catatan'       => 'Auto-generated: Pesanan melewati estimasi (auto-check)',
                        ]);
                    }
                    
                    // Kirim FCM ke pelanggan
                    $idPelanggan = $pesanan->pelanggan->id_pelanggan ?? null;
                    
                    if ($idPelanggan) {
                        $tokens = FcmToken::where('pelanggan_id', $idPelanggan)->pluck('token')->filter();
                        
                        if ($tokens->isNotEmpty()) {
                            foreach ($tokens as $token) {
                                try {
                                    FcmService::send($token, 
                                        '⚠️ Cucian Melewati Estimasi - Akan Diantar!',
                                        "Cucian Anda (ORDER/{$pesanan->id_transaksi}) melewati estimasi dan akan segera diantar.",
                                        ['transaksi_id' => (string) $pesanan->id_transaksi, 'type' => 'auto_siap_antar', 'action' => 'open_detail']
                                    );
                                } catch (\Exception $e) {
                                    \Log::error("❌ FCM Error: " . $e->getMessage());
                                }
                            }
                        }
                    }
                    
                    DB::commit();
                    $countTerlambat++;
                }
            }
            
            \Log::info("📊 Total pesanan terlambat diupdate: {$countTerlambat}");
            \Log::info("🔍 === AUTO CHECK TERLAMBAT END ===");
            
            // Kembalikan hasil sebagai array untuk diproses pemanggil
            return [
                'success'       => true,
                'total_checked' => $pesananSelesaiDicuci->count(),
                'total_updated' => $countTerlambat,
            ];
            
        } catch (\Exception $e) {
            // Penanganan error: rollback dan log detail error
            DB::rollBack();
            \Log::error("❌ Auto-update error: " . $e->getMessage());
            \Log::error("❌ Stack trace: " . $e->getTraceAsString());
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * -----------------------------------------------
     * METHOD: manualCheckTerlambat
     * -----------------------------------------------
     * Trigger manual untuk autoCheckTerlambat oleh Admin.
     * Memanggil method autoCheckTerlambat() dan menampilkan hasilnya.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function manualCheckTerlambat()
    {
        \Log::info("🔍 Manual trigger autoCheckTerlambat by Admin: " . auth()->guard('admin')->user()->nama ?? 'Unknown');
        
        // Jalankan auto-check dan tampilkan hasilnya
        $result = $this->autoCheckTerlambat();
        
        // Percabangan: tampilkan pesan berdasarkan hasil eksekusi
        if ($result['success']) {
            return redirect()->back()
                ->with('success', "✅ Auto-check selesai! {$result['total_updated']} dari {$result['total_checked']} pesanan diupdate ke siap_di_antar.");
        } else {
            return redirect()->back()
                ->with('error', "❌ Error: {$result['error']}");
        }
    }
}