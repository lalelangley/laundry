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
            'nominal' => 'nullable|numeric|min:1',
            'foto_bukti' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {

            $transaksi = Transaksi::lockForUpdate()->findOrFail($idTransaksi);

            /**
             * 🔥 Tentukan total tagihan
             */
            $totalBayar = $transaksi->total_bayar > 0
                ? $transaksi->total_bayar
                : $transaksi->total_harga;

            if ($totalBayar <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaksi belum memiliki total tagihan.'
                ], 422);
            }

            /**
             * 🔥 Hitung total sudah dibayar dari tabel pembayaran
             */
            $totalDibayar = Pembayaran::where('id_transaksi', $transaksi->id_transaksi)
                ->sum('nominal');

            $sisaPembayaran = $totalBayar - $totalDibayar;

            // kalau sudah lunas — blok pembayaran
            if ($sisaPembayaran <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaksi sudah lunas.'
                ], 422);
            }

            $metode = DB::table('metode_bayar')
                ->where('id_metode_bayar', $request->id_metode_bayar)
                ->value('nama_metode_bayar');

            /**
             * =============================
             * CASH (hanya pilih metode)
             * =============================
             */
            if ($request->id_metode_bayar == 1) {

                $transaksi->update([
                    'id_metode_bayar' => 1,
                    'status_bayar' => $transaksi->status_bayar ?? 'belum_lunas'
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Metode cash dipilih.',
                    'total_tagihan' => $totalBayar,
                    'total_dibayar' => $totalDibayar,
                    'sisa_pembayaran' => $sisaPembayaran
                ]);
            }

            /**
             * =============================
             * TRANSFER
             * =============================
             */
            if (!$request->nominal || !$request->hasFile('foto_bukti')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nominal dan foto bukti wajib untuk transfer.'
                ], 422);
            }

            if ($request->nominal > $sisaPembayaran) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nominal melebihi sisa tagihan.',
                    'sisa' => $sisaPembayaran
                ], 422);
            }

            /**
             * upload bukti
             */
            $fotoPath = $request->file('foto_bukti')
                ->store('bukti_pembayaran', 'public');

            /**
             * simpan pembayaran
             */
            Pembayaran::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'id_metode_bayar' => $request->id_metode_bayar,
                'tipe_pembayaran' => 'lunas',
                'nominal' => $request->nominal,
                'foto_bukti' => $fotoPath,
                'keterangan' => $request->keterangan,
                'tanggal_bayar' => now()
            ]);

            /**
             * hitung ulang setelah bayar
             */
            $totalDibayarBaru = $totalDibayar + $request->nominal;
            $sisaBaru = $totalBayar - $totalDibayarBaru;

            /**
             * Tentukan status
             */
            if ($sisaBaru <= 0) {
                $statusBayar = 'lunas';
                $tglLunas = now();
            } elseif ($totalDibayarBaru > 0) {
                $statusBayar = 'dp';
                $tglLunas = null;
            } else {
                $statusBayar = 'belum_lunas';
                $tglLunas = null;
            }

            /**
             * update transaksi
             */
            $transaksi->update([
                'id_metode_bayar' => $request->id_metode_bayar,
                'status_bayar' => $statusBayar,
                'tgl_lunas' => $tglLunas
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran berhasil dicatat.',
                'metode_bayar' => $metode,
                'status_bayar' => $statusBayar,
                'total_tagihan' => $totalBayar,
                'total_dibayar' => $totalDibayarBaru,
                'sisa_pembayaran' => max(0, $sisaBaru),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('PAYMENT ERROR', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses pembayaran',
            ], 500);
        }
    }
}
