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
            'nominal' => 'nullable|numeric|min:0',
            'foto_bukti' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $transaksi = Transaksi::lockForUpdate()->findOrFail($idTransaksi);

            $metode = DB::table('metode_bayar')
                ->where('id_metode_bayar', $request->id_metode_bayar)
                ->value('nama_metode_bayar');

            if ($request->id_metode_bayar == 1) {
                $transaksi->update([
                    'id_metode_bayar' => 1,
                    'status_bayar' => $transaksi->status_bayar ?? 'belum_lunas'
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Metode pembayaran cash berhasil dipilih. Silakan bayar di kasir atau saat pengantaran.',
                    'metode_bayar' => $metode,
                    'status_bayar' => $transaksi->status_bayar ?? 'belum_lunas'
                ]);
            }

            if (!$request->nominal || !$request->hasFile('foto_bukti')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nominal dan foto bukti wajib untuk pembayaran non-cash'
                ], 422);
            }

            $sisaPembayaran = ($transaksi->total_bayar ?? 0) - ($transaksi->total_dibayar ?? 0);
            
            if ($request->nominal > $sisaPembayaran) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nominal pembayaran melebihi sisa tagihan',
                    'sisa_pembayaran' => $sisaPembayaran
                ], 422);
            }

            $fotoPath = $request->file('foto_bukti')
                ->store('bukti_pembayaran', 'public');

            $totalDibayarBaru = ($transaksi->total_dibayar ?? 0) + $request->nominal;
            $totalBayar = $transaksi->total_bayar ?? 0;
            
            $statusBayar = 'belum_lunas';
            $tglLunas = null;
            
            if ($totalDibayarBaru >= $totalBayar) {
                $statusBayar = 'lunas';
                $tglLunas = now()->toDateString();
            } elseif ($totalDibayarBaru > 0) {
                $statusBayar = 'dp';
            }

            Pembayaran::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'id_metode_bayar' => $request->id_metode_bayar,
                'tipe_pembayaran' => $statusBayar,
                'nominal' => $request->nominal,
                'foto_bukti' => $fotoPath,
                'keterangan' => $request->keterangan,
                'tanggal_bayar' => now()->toDateString()
            ]);

            $updateData = [
                'id_metode_bayar' => $request->id_metode_bayar,
                'status_bayar' => $statusBayar,
            ];
            
            if ($statusBayar === 'lunas') {
                $updateData['tgl_lunas'] = $tglLunas;
            }
            
            $transaksi->update($updateData);

            DB::table('transaksi')
                ->where('id_transaksi', $transaksi->id_transaksi)
                ->increment('total_dibayar', $request->nominal);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran berhasil dicatat dan menunggu verifikasi',
                'metode_bayar' => $metode,
                'status_bayar' => $statusBayar,
                'nominal_dibayar' => $request->nominal,
                'total_dibayar' => $totalDibayarBaru
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}