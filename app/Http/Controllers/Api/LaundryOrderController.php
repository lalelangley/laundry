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
            'alamat_kirim' => 'nullable|string|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pelanggan = Pelanggan::find($request->id_pelanggan);

        // alamat kirim fleksibel
        $alamatKirim = $request->alamat_kirim ?: $pelanggan->alamat;

        // Buat transaksi awal
        $transaksi = Transaksi::create([
            'id_pelanggan'     => $pelanggan->id_pelanggan,
            'status_transaksi' => 0,
            'total_harga'      => 0,
            'total_bayar'      => 0,
            'tgl_transaksi'    => now()->toDateString(),
            'keterangan'       => $request->keterangan,
        ]);

    
        foreach ($request->items as $item) {
            DetailTransaksi::create([
                'id_transaksi'    => $transaksi->id_transaksi,
                'id_layanan'      => $item['id_layanan'],
                'id_jenis'        => $item['id_jenis'],
                'id_parfum'       => $item['id_parfum'],
                'qty'             => 0,
                'harga'           => 0,
                'total_harga'     => 0,
                'status_transaksi'=> 0,
                'tgl_transaksi'   => now()->toDateString(),
            ]);
        }

        // Delivery
        Delivery::create([
            'id_transaksi'  => $transaksi->id_transaksi,
            'jenis'         => 'pickup',
            'alamat_tujuan' => $alamatKirim,
            'status'        => 'pending'
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
        $id = $request->query('id_pelanggan');

        if (!$id) {
            return response()->json([
                'status' => false,
                'message' => 'id_pelanggan wajib dikirim sebagai query parameter (?id_pelanggan=1)'
            ], 400);
        }

        $orders = Transaksi::with('detail')
            ->where('id_pelanggan', $id)
            ->orderBy('id_transaksi', 'DESC')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $orders
        ]);
    }

    // ======================
    // LIST ORDER UNTUK RIWAYAT (RINGKAS)
    // ======================
    public function getOrdersList(Request $request)
    {
        $id = $request->query('id_pelanggan');

        if (!$id) {
            return response()->json([
                'status' => false,
                'message' => 'id_pelanggan wajib dikirim sebagai query parameter (?id_pelanggan=1)'
            ], 400);
        }

        $orders = Transaksi::withCount('detail')
            ->where('id_pelanggan', $id)
            ->orderBy('id_transaksi', 'DESC')
            ->get(['id_transaksi', 'tgl_transaksi']);

        return response()->json([
            'status' => true,
            'data'   => $orders
        ]);
    }

    // ======================
    // DETAIL ORDER (VERSI TERBAIK)
    // ======================
    public function getOrderDetail($id)
    {
        $order = Transaksi::with(['detail', 'delivery'])
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
