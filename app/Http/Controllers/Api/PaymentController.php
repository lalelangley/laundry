<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
   public function bayar(Request $request, $idTransaksi)
{
    $request->validate([
        'id_metode_bayar' => 'required|exists:metode_bayar,id_metode_bayar',
        'nominal' => 'nullable|numeric|min:1000',
        'foto_bukti' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'keterangan' => 'nullable|string'
    ]);

    DB::beginTransaction();

    try {
        $transaksi = Transaksi::lockForUpdate()->findOrFail($idTransaksi);

        // ambil nama metode
        $metode = DB::table('metode_bayar')
            ->where('id_metode_bayar', $request->id_metode_bayar)
            ->value('nama_metode_bayar');

        // ===============================
        // JIKA CASH (id = 1) → HANYA UPDATE STATUS
        // ===============================
        if ($request->id_metode_bayar == 1) {
            $transaksi->update([
                'id_metode_bayar' => 1,
                'status_bayar' => 'belum_lunas'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran cash dicatat, status belum lunas',
                'metode_bayar' => $metode,
                'status_bayar' => 'belum_lunas'
            ]);
        }

        // ===============================
        // VALIDASI KHUSUS TRANSFER (id = 2) / QRIS
        // ===============================
        if (!$request->nominal || !$request->hasFile('foto_bukti')) {
            return response()->json([
                'message' => 'Nominal dan foto bukti wajib untuk pembayaran non-cash'
            ], 422);
        }

        // ===============================
        // UPLOAD FOTO
        // ===============================
        $fotoPath = $request->file('foto_bukti')
            ->store('bukti_pembayaran', 'public');

        // ===============================
        // SIMPAN PEMBAYARAN (TRANSFER/QRIS)
        // ===============================
        Pembayaran::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'id_metode_bayar' => $request->id_metode_bayar,
            'tipe_pembayaran' => 'belum_lunas',
            'nominal' => $request->nominal,
            'foto_bukti' => $fotoPath,
            'keterangan' => $request->keterangan,
            'tanggal_bayar' => now()->toDateString()
        ]);

        // ===============================
        // UPDATE TRANSAKSI (TRANSFER/QRIS → LUNAS)
        // ===============================
        $transaksi->update([
            'id_metode_bayar' => $request->id_metode_bayar,
            'total_bayar' => $request->nominal,
            'status_bayar' => 'lunas',
            'tgl_lunas' => now()->toDateString()
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Pembayaran berhasil dicatat',
            'metode_bayar' => $metode,
            'status_bayar' => 'lunas'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Gagal memproses pembayaran',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
