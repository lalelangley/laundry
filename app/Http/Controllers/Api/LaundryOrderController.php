<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pelanggan;
use App\Models\Layanan;
use App\Models\JenisLayanan;
use App\Models\Parfum;
use App\Models\Delivery;
use Illuminate\Support\Facades\Validator;

class LaundryOrderController extends Controller
{
    // ======================
    // GET DATA UNTUK DROPDOWN
    // ======================
    public function getOptions()
    {
        return response()->json([
            'status'  => true,
            'layanan' => Layanan::with('jenisLayanan')->get(),
            'parfum'  => Parfum::all(),
        ]);
    }

    // ======================
    // CREATE ORDER
    // ======================
public function createOrder(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',

        'items' => 'required|array|min:1',
        'items.*.id_layanan' => 'required|exists:layanan,id_layanan',
        'items.*.id_jenis'   => 'required|exists:jenis_layanan,id_jenis_layanan',
        'items.*.id_parfum'  => 'required|exists:parfum,id_parfum',

        'catatan'      => 'nullable|string',
        'alamat_kirim' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $pelanggan = Pelanggan::find($request->id_pelanggan);

    // Alamat
    $alamatKirim = $request->alamat_kirim ?? $pelanggan->alamat;

    // Buat transaksi awal (harga 0 dulu)
    $transaksi = Transaksi::create([
        'id_pelanggan'     => $pelanggan->id_pelanggan,
        'nama_pelanggan'   => $pelanggan->nama_pelanggan,
        'no_hp'            => $pelanggan->no_hp,
        'status_transaksi' => 0,
        'total_harga'      => 0,   // harga nanti
        'total_bayar'      => 0,
        'tgl_transaksi'    => date('Y-m-d'),
        'catatan'          => $request->catatan,
    ]);

    // Insert detail tanpa harga
    foreach ($request->items as $item) {
        $layanan = Layanan::find($item['id_layanan']);
        $jenis   = JenisLayanan::find($item['id_jenis']);
        $parfum  = Parfum::find($item['id_parfum']);

        DetailTransaksi::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'id_layanan'   => $layanan->id_layanan,
            'id_jenis'     => $jenis->id_jenis,
            'id_parfum'    => $parfum->id_parfum,
            'nama_layanan' => $layanan->nama_layanan,
            'nama_jenis'   => $jenis->nama_jenis,
            'nama_parfum'  => $parfum->nama_parfum,
            'qty'          => 0,    // nanti diisi karyawan
            'harga'        => 0,    // nanti diisi karyawan
            'total_harga'  => 0,    // nanti dihitung qty * harga
            'status_transaksi' => 0,
            'tgl_transaksi' => date('Y-m-d'),
        ]);
    }

    // Delivery
    Delivery::create([
        'id_transaksi'  => $transaksi->id_transaksi,
        'jenis'         => 'pickup',
        'alamat_tujuan' => $alamatKirim,
        'status'        => 'menunggu',
        'catatan'       => $request->catatan ?? 'Menunggu driver pickup cucian',
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Order berhasil dibuat (harga belum ditentukan)',
        'data'    => $transaksi
    ]);
}




    // ======================
    // GET ORDER BY PELANGGAN
    // ======================
    public function getOrders(Request $request)
    {
        $orders = Transaksi::with('detail')
            ->where('id_pelanggan', $request->id_pelanggan)
            ->orderBy('id_transaksi', 'DESC')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $orders
        ]);
    }

    // ======================
    // ORDER DETAIL
    // ======================
    public function orderDetail($id)
    {
        $order = Transaksi::with('detail')
            ->where('id_transaksi', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $order
        ]);
    }
}
