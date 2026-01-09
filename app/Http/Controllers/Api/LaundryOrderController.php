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
                'status_transaksi' => 'antrian',

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
    public function getOrders(Request $request)
    {
        $id = $request->query('id_pelanggan');

        if (!$id) {
            return response()->json([
                'status'  => false,
                'message' => 'id_pelanggan wajib dikirim'
            ], 400);
        }

        $orders = Transaksi::with([
                'detail.layanan',
                'detail.jenis',
                'detail.parfum',
                'delivery'
            ])
            ->where('id_pelanggan', $id)
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
            'delivery'
        ])
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
