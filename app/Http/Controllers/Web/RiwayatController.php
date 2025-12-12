<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use App\Models\DetailTransaksi;
use App\Models\JenisLayanan;
use App\Models\Pelanggan;
use App\Models\Satuan;

class RiwayatController extends Controller
{
    /** TAMPILKAN RIWAYAT */
    public function index(Request $request)
    {
        $tab = $request->tab ?? 'antrian';
        $riwayat = Transaksi::with('pelanggan')
            ->where('status_transaksi', $tab)
            ->orderBy('id_transaksi', 'DESC')
            ->get();

        return view('riwayat.index', compact('riwayat', 'tab'));
    }

    /** FORM EDIT DATA */
    public function edit($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        return view('riwayat.edit', compact('transaksi'));
    }

    /** UPDATE DATA TRANSAKSI */
    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status_transaksi !== 'antrian') {
            return redirect()->back()->with('error', 'Transaksi tidak bisa diedit karena sudah diproses.');
        }

        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'tanggal' => 'required|date',
            'total_harga' => 'required|numeric',
            'status_transaksi' => 'required|string'
        ]);

        $transaksi->update($request->only([
            'nama_pelanggan','no_hp','alamat','tanggal','total_harga','status_transaksi'
        ]));

        return redirect()->route('riwayat.index')
                         ->with('success', 'Transaksi berhasil diperbarui!');
    }

    /** UPDATE STATUS TRANSAKSI */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status_transaksi' => 'required']);
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->status_transaksi = $request->status_transaksi;
        $transaksi->save();

        return back()->with('success', 'Status berhasil diperbarui');
    }

    /** HAPUS RIWAYAT */
    public function destroy($id)
    {
        Transaksi::findOrFail($id)->delete();
        return back()->with('success', 'Riwayat transaksi berhasil dihapus');
    }

    /** DETAIL TRANSAKSI */
    public function detail($id)
    {
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*','mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)['nama'=>$transaksi->nama_pelanggan,'no_hp'=>$transaksi->no_hp,'gambar'=>null];

        $detail = DB::table('detail_transaksi as d')
            ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
            ->leftJoin('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
            ->leftJoin('satuan as s', 's.id_satuan', '=', 'j.id_satuan')
            ->select('d.*','d.id_detail_transaksi as id_detail','j.nama_jenis','j.harga as harga_jenis',
                     'j.keterangan as keterangan_jenis','l.nama_layanan','s.nama_satuan as satuan',
                     DB::raw('(d.qty * d.harga) AS total_harga'))
            ->where('d.id_transaksi', $id)
            ->get();

        $subtotal = $detail->sum('total_harga');

        return view('riwayat.detail', compact('transaksi','pelanggan','detail','subtotal'));
    }

    /** PROSES ORDER */
    public function prosesOrder($id)
    {
        $trx = Transaksi::findOrFail($id);
        if ($trx->status_transaksi === 'antrian') {
            $trx->status_transaksi = 'proses';
            $trx->save();
        }
        return redirect()->route('riwayat.index',['tab'=>'proses'])
                         ->with('success','Transaksi berhasil diproses!');
    }

    /** SELESAI ORDER */
    public function selesaiOrder($id)
    {
        $trx = Transaksi::findOrFail($id);
        if ($trx->status_transaksi === 'proses') {
            $trx->status_transaksi = 'selesai';
            $trx->save();
        }
        return redirect()->route('riwayat.index',['tab'=>'selesai'])
                         ->with('success','Transaksi berhasil diselesaikan!');
    }

    /** SIAP DIAMBIL */
    public function siapDiAmbil($id)
    {
        $trx = Transaksi::findOrFail($id);
        $trx->status_transaksi = 'siap_di_ambil';
        $trx->save();
        return redirect()->route('riwayat.index',['tab'=>'siap_di_ambil'])
                         ->with('success','Transaksi siap diambil!');
    }

    /** BAYAR SUBMIT */
    public function bayarSubmit(Request $request, $id)
    {
        $trs = Transaksi::findOrFail($id);
        $request->validate(['jumlah_bayar'=>'required|numeric|min:1']);

        $jumlahBayarBaru = (float)$request->jumlah_bayar;
        $totalBayarLama = (float)($trs->total_bayar ?? 0);
        $subtotal = (float)$trs->total_harga;
        $diskon = (float)($trs->diskon ?? 0);

        $totalTagihan = $subtotal - $diskon;
        $totalBayarBaru = $totalBayarLama + $jumlahBayarBaru;

        $trs->dp = $totalBayarBaru < $totalTagihan ? $totalBayarBaru : ($trs->dp ?? $totalBayarBaru);
        $trs->total_bayar = $totalBayarBaru;

        $sisaTagihan = $totalTagihan - $totalBayarBaru;

        if ($sisaTagihan <= 0) {
            $trs->status_bayar = 'lunas';
            $trs->tgl_lunas = now();
            if ($trs->status_transaksi == 'selesai') {
                $trs->status_transaksi = 'siap_di_ambil';
            }
        } elseif ($totalBayarBaru > 0) {
            $trs->status_bayar = 'DP';
            $trs->tgl_lunas = null;
        } else {
            $trs->status_bayar = 'belum bayar';
            $trs->tgl_lunas = null;
        }

        $trs->save();
        return redirect()->back()->with('success','Pembayaran berhasil diperbarui.');
    }

public function editLayanan($id)
{
    $detail = DetailTransaksi::with('layanan', 'jenis', 'transaksi')->findOrFail($id);

    $trx = $detail->transaksi ?? null;

    if ($trx && $trx->id_pelanggan) {
        $pelanggan = Pelanggan::find($trx->id_pelanggan);
    } elseif ($trx) {
        $pelanggan = (object)[
            'nama' => $trx->nama_pelanggan,
            'no_hp' => $trx->no_hp,
            'gambar' => null
        ];
    } else {
        $pelanggan = (object)[
            'nama' => 'Pelanggan Tidak Diketahui',
            'no_hp' => '-',
            'gambar' => null
        ];
    }

    $parfum = DB::table('parfum')->get();
    $satuan = Satuan::all();

    return view('riwayat.edit', compact('detail', 'pelanggan', 'parfum', 'satuan'));

}


    /** UPDATE LAYANAN */
    public function updateLayanan(Request $request, $id)
    {
        $detail = DetailTransaksi::findOrFail($id);
        $request->validate([
            'nama_jenis'=>'required|string|max:255',
            'harga'=>'required|numeric',
            'id_satuan'=>'required|exists:satuan,id_satuan',
            'qty'=>'required|numeric|min:1',
        ]);

        $detail->update($request->only(['nama_jenis','harga','id_satuan','qty']));

        // update layanan utama kalau ada
        $layanan = $detail->layanan;
        if($request->filled('nama_layanan') || $request->filled('proses')) {
            $layanan->update([
                'nama_layanan'=>$request->nama_layanan ?? $layanan->nama_layanan,
                'proses'=>isset($request->proses)?implode(',',$request->proses):$layanan->proses,
            ]);
        }

        // jenis baru dari session
        $jenisBaru = session()->get("jenis_baru_{$layanan->id_layanan}",[]);
        foreach($jenisBaru as $jb){
            JenisLayanan::create([
                'id_layanan'=>$layanan->id_layanan,
                'nama_jenis'=>$jb['nama'],
                'harga'=>$jb['harga'],
                'id_satuan'=>$jb['id_satuan'] ?? null,
                'lama'=>$jb['lama'] ?? null,
                'lama_satuan'=>$jb['lama_satuan'] ?? null,
                'gambar'=>$jb['gambar'] ?? null,
            ]);
        }
        session()->forget("jenis_baru_{$layanan->id_layanan}");

        return redirect()->route('riwayat.detail',$detail->id_transaksi)
                         ->with('success','Layanan berhasil diperbarui.');
    }
}
