<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pelanggan;
use App\Models\Layanan;
use App\Models\Parfum;
use App\Models\Delivery;
use App\Models\BiayaTambahan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LaundryOrderController extends Controller
{
    // ======================
    // GET DATA UNTUK DROPDOWN
    // ======================
    public function getOptions()
    {
        return response()->json([
            'status'  => true,
            'layanan' => Layanan::with('jenis')->get(),
            'parfum'  => Parfum::all(),
        ]);
    }

    // ======================
    // CREATE ORDER (ONLINE)
    // ======================
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',

            'items' => 'required|array|min:1',
            'items.*.id_layanan'        => 'required|exists:layanan,id_layanan',
            'items.*.id_jenis_layanan'  => 'required|exists:jenis_layanan,id_jenis_layanan',
            'items.*.id_parfum'         => 'required|exists:parfum,id_parfum',

            'keterangan'   => 'nullable|string',
            'alamat_kirim' => 'nullable|string|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $pelanggan = Pelanggan::findOrFail($request->id_pelanggan);

            $alamatKirim = $request->alamat_kirim ?: $pelanggan->alamat;

            // ======================
            // TRANSAKSI (HEADER)
            // ======================
            $transaksi = Transaksi::create([
                'id_pelanggan'     => $pelanggan->id_pelanggan,
                'nama_pelanggan'   => $pelanggan->nama_pelanggan,
                'no_hp'            => $pelanggan->no_hp,

                'jenis_transaksi'  => 'online',
                'status_transaksi' => 'pick_up',

                'total_harga'      => 0,
                'total_bayar'      => 0,
                'dp'               => 0,

                'tgl_transaksi'    => now()->toDateString(),
                'keterangan'       => $request->keterangan,
            ]);

            // ======================
            // DETAIL TRANSAKSI (FIX)
            // ======================
            if (empty($request->items)) {
                throw new \Exception('Item detail tidak boleh kosong');
            }

            foreach ($request->items as $item) {
                DetailTransaksi::create([
                    'id_transaksi'     => $transaksi->id_transaksi,
                    'id_layanan'       => $item['id_layanan'],
                    'id_jenis_layanan' => $item['id_jenis_layanan'],
                    'id_parfum'        => $item['id_parfum'],

                    // online → belum ditimbang
                    'id_satuan'        => isset($item['id_satuan'])
                        ? (string) $item['id_satuan']
                        : null,

                    'qty'              => is_numeric($item['qty'] ?? null)
                        ? $item['qty']
                        : null,

                    'harga'            => 0,
                    'tipe_diskon'      => null,
                ]);
            }

            // ======================
            // DELIVERY
            // ======================
            Delivery::create([
                'id_transaksi'  => $transaksi->id_transaksi,
                'jenis'         => 'pickup',
                'alamat_tujuan' => $alamatKirim,
                'status'        => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order berhasil dibuat (harga belum ditentukan)',
                'data'    => $transaksi
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat order',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ======================
    // GET ORDER LIST
    // ======================
    public function getOrders()
{
    $user = auth()->user();

    $orders = Transaksi::with([
            'detail.layanan',
            'detail.jenis',
            'detail.parfum',
            'delivery'
        ])
        ->where('id_pelanggan', $user->id_pelanggan)
        ->orderByDesc('id_transaksi')
        ->get();

    return response()->json([
        'status' => true,
        'data'   => $orders
    ]);
}

    // ======================
    // GET ORDER DETAIL
    // ======================
    public function getOrderDetail($id)
    {
        $order = Transaksi::with([
            'detail.layanan:id_layanan,nama_layanan',
            'detail.jenis:id_jenis_layanan,nama_jenis',
            'detail.parfum:id_parfum,nama_parfum',
            'delivery',
            'biayaTambahan' // ✅ TAMBAHKAN RELASI BIAYA TAMBAHAN (ONGKIR)
        ])
        ->where('id_transaksi', $id)
        ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        // ✅ HITUNG SUBTOTAL DARI DETAIL
        $subtotal = $order->detail->sum(function($item) {
            return $item->harga * ($item->qty ?? 1);
        });

        // ✅ AMBIL BIAYA ONGKIR
        $biayaOngkir = $order->biayaTambahan ? $order->biayaTambahan->biaya : 0;

        return response()->json([
            'status' => true,
            'data'   => [
                'order' => $order,
                'subtotal' => $subtotal,
                'biaya_ongkir' => $biayaOngkir,
                'diskon' => $order->diskon ?? 0,
                'tipe_diskon' => $order->tipe_diskon,
                'total_bayar' => $order->total_bayar,
                'total_item' => $order->detail->sum('qty') ?? $order->detail->count(),
                'tgl_estimasi' => $order->tgl_estimasi, // ✅ TAMBAHKAN ESTIMASI
            ]
        ]);
    }

    // ======================
    // GET INVOICE (ORDER SUDAH ADA HARGA)
    // ======================
   // ====================== 
// GET INVOICE (ORDER SUDAH ADA HARGA) 
// ====================== 
public function getInvoice($id) 
{ 
    $order = Transaksi::with([ 
        'detail.layanan', 
        'detail.jenis', 
        'detail.parfum', 
        'delivery', 
        'biayaTambahan' 
    ]) 
    ->where('id_transaksi', $id) 
    ->whereNotNull('total_harga') 
    ->where('total_harga', '>', 0) 
    ->first(); 

    if (!$order) { 
        return response()->json([ 
            'status' => false, 
            'message' => 'Invoice belum tersedia atau order tidak ditemukan' 
        ], 404); 
    } 

    /* ========================= 
     * SUBTOTAL 
     * ========================= */ 
    $subtotal = $order->detail->sum(function ($item) { 
        return $item->harga * ($item->qty ?? 1); 
    }); 

    /* ========================= 
     * BIAYA TAMBAHAN 
     * ========================= */ 
    $biayaTambahan = $order->biayaTambahan->biaya ?? 0; 

    /* ========================= 
     * DISKON 
     * ========================= */ 
    $nominalDiskon = 0; 
    if ($order->tipe_diskon === 'percent') { 
        $nominalDiskon = ($subtotal * $order->diskon) / 100; 
    } elseif ($order->tipe_diskon === 'nominal') { 
        $nominalDiskon = $order->diskon; 
    } 

    /* ========================= 
     * TOTAL 
     * ========================= */ 
    $totalBayar = max(0, ($subtotal + $biayaTambahan) - $nominalDiskon); 
    $totalDibayar = $order->dp ?? 0;
    $sisaPembayaran = max(0, $totalBayar - $totalDibayar); 

    return response()->json([ 
        'status' => true, 
        'data' => [ 
            'invoice_no' => 'INV-' . str_pad($order->id_transaksi, 6, '0', STR_PAD_LEFT), 
            'tanggal' => $order->tgl_transaksi, 
            'pelanggan' => [ 
                'nama' => $order->nama_pelanggan, 
                'hp' => $order->no_hp, 
            ], 
            'items' => $order->detail, 
            'subtotal' => $subtotal, 
            'biaya_tambahan' => $biayaTambahan, 
            'diskon' => $order->diskon ?? 0, 
            'tipe_diskon' => $order->tipe_diskon, 
            'nominal_diskon' => $nominalDiskon,
            'total_bayar' => $totalBayar,
            'total_dibayar' => $totalDibayar,
            'sisa_pembayaran' => $sisaPembayaran,
            'status_bayar' => $order->status_bayar ?? 'belum_lunas', 
            'delivery' => $order->delivery, 
            'total_item' => $order->detail->sum('qty') ?: $order->detail->count(), 
            'tgl_estimasi' => $order->tgl_estimasi, 
        ] 
    ]); 
}


    // ======================
    // GET LIST INVOICE (SUDAH ADA HARGA)
    // ======================
    public function getInvoiceList(Request $request)
    {
        $idPelanggan = $request->query('id_pelanggan');

        if (!$idPelanggan) {
            return response()->json([
                'status'  => false,
                'message' => 'id_pelanggan wajib dikirim'
            ], 400);
        }

        $invoices = Transaksi::select(
                'id_transaksi',
                'tgl_transaksi',
                'total_bayar',
                'status_bayar'
            )
            ->where('id_pelanggan', $idPelanggan)
            ->whereNotNull('total_harga')
            ->where('total_harga', '>', 0)
            ->orderByDesc('id_transaksi')
            ->get()
            ->map(function ($item) {
                return [
                    'id_transaksi' => $item->id_transaksi,
                    'invoice_no'   => 'INV-' . str_pad($item->id_transaksi, 6, '0', STR_PAD_LEFT),
                    'tanggal'      => $item->tgl_transaksi,
                    'total_bayar'  => $item->total_bayar,
                    'status_bayar' => $item->status_bayar,
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $invoices
        ]);
    }

    // ✅ PERBAIKAN METHOD pilihMetodePengambilan
// ✅ FINAL FIX - Delivery record hanya untuk 'antar', pickup tidak buat record

public function pilihMetodePengambilan(Request $request, $id_transaksi)
{
    // ================= VALIDASI =================
    $request->validate([
        'jenis_transaksi' => 'required|in:pickup,antar',
        'alamat_tujuan'   => 'required_if:jenis_transaksi,antar|string|max:255',
    ]);

    // ================= AMBIL TRANSAKSI =================
    $transaksi = Transaksi::where('id_transaksi', $id_transaksi)
        ->where('id_pelanggan', auth()->user()->id_pelanggan)
        ->where('status_transaksi', 'selesai_dicuci')
        ->first();

    if (!$transaksi) {
        return response()->json([
            'status'  => false,
            'message' => 'Transaksi tidak ditemukan atau belum selesai dicuci',
        ], 404);
    }

    // ================= TENTUKAN STATUS =================
    if ($request->jenis_transaksi === 'pickup') {

        // ---------- PICKUP ----------
        $transaksi->update([
            'status_transaksi' => 'siap_di_ambil',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Metode ambil sendiri berhasil dipilih',
            'data'    => [
                'id_transaksi'      => $transaksi->id_transaksi,
                'status_transaksi'  => 'siap_di_ambil',
                'metode'            => 'pickup',
                'delivery'          => null,
            ]
        ], 200);
    }

    // ================= ANTAR (DELIVERY) =================

    // Update status transaksi
    $transaksi->update([
        'status_transaksi' => 'siap_di_antar',
    ]);

    // BUAT DELIVERY BARU (TANPA UPDATE / DELETE DATA LAMA)
    $delivery = Delivery::create([
        'id_transaksi'  => $transaksi->id_transaksi,
        'jenis'         => 'antar',
        'alamat_tujuan' => $request->alamat_tujuan,
        'status'        => 'pending',
        'waktu'         => null, // diisi admin saat assign driver
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Metode antar berhasil dipilih',
        'data'    => [
            'id_transaksi'     => $transaksi->id_transaksi,
            'status_transaksi' => 'siap_di_antar',
            'metode'           => 'antar',
            'delivery'         => [
                'id_delivery'   => $delivery->id,
                'alamat_tujuan' => $delivery->alamat_tujuan,
                'status'        => $delivery->status,
            ],
        ]
    ], 200);
}

}