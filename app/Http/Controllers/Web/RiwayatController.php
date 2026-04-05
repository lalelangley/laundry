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
use App\Models\Layanan;
use App\Models\Parfum;

class RiwayatController extends Controller
{
    // =============================
    // HELPER METHOD - GET NAMA KASIR
    // =============================
    private function getNamaKasir($idKasir)
    {
        if (!$idKasir) return 'System';
        
        $admin = \App\Models\Admin::find($idKasir);
        if ($admin) return $admin->nama;
        
        $kasir = \App\Models\Kasir::find($idKasir);
        if ($kasir) return $kasir->nama_kasir;
        
        return 'Unknown';
    }

    // =============================
    // HELPER METHOD - GET DATA UNTUK CETAK NOTA
    // =============================
    private function getDataCetakNota($id)
    {
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404);

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)[
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'no_hp'          => $transaksi->no_hp,
                'gambar'         => null,
            ];

        $detail = DB::table('detail_transaksi as d')
            ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
            ->leftJoin('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
            ->leftJoin('satuan as s', 's.id_satuan', '=', 'j.id_satuan')
            ->select(
                'd.*',
                'j.nama_jenis',
                'j.harga as harga_jenis',
                'l.nama_layanan',
                's.nama_satuan as satuan',
                DB::raw('(d.qty * d.harga) AS total_harga')
            )
            ->where('d.id_transaksi', $id)
            ->get();

        return compact('transaksi', 'pelanggan', 'detail');
    }

    // =============================
    // CETAK NOTA - ADMIN
    // =============================
    public function cetakNota($id)
    {
        requirePermission('riwayat', 'view');
        $data = $this->getDataCetakNota($id);
        return view('riwayat.cetaknota', $data);
    }

    // =============================
    // CETAK NOTA - KASIR
    // =============================
    public function cetakNotaKasir($id)
    {
        requirePermission('riwayat', 'view');
        $data = $this->getDataCetakNota($id);
        return view('riwayat.cetaknota', $data);
    }

    // =============================
    // CETAK NOTA - ADMIN2
    // =============================
    public function cetakNotaAdmin2($id)
    {
        requirePermission('riwayat', 'view');
        $data = $this->getDataCetakNota($id);
        return view('riwayat.cetaknota', $data);
    }

    // =============================
    // ADMIN - TAMPILKAN RIWAYAT
    // =============================
    public function index(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('riwayat', 'view');
        
        $tab = $request->get('tab', 'antrian');
        $allowedTabs = [
            'antrian',
            'proses',
            'siap_di_ambil',
            'pick_up',
            'siap_di_antar',
            'selesai',
            'batal'
        ];

        if (!in_array($tab, $allowedTabs)) {
            $tab = 'antrian';
        }

        $riwayat = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'offline')
            ->where('status_transaksi', $tab)
            ->orderBy('id_transaksi', 'DESC')
            ->get();

        return view('riwayat.index', compact('riwayat', 'tab'));
    }

    // =============================
    // ADMIN - DETAIL TRANSAKSI
    // =============================
    public function detail($id)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('riwayat', 'view');
        
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404);

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)['nama_pelanggan'=>$transaksi->nama_pelanggan,'no_hp'=>$transaksi->no_hp,'gambar'=>null];

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

    // =============================
    // ADMIN - FORM EDIT DATA
    // =============================
    public function edit($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $riwayat = Transaksi::with(['detail.jenis.satuan', 'detail.parfum', 'pelanggan'])
            ->findOrFail($id);
        $detail = $riwayat->detail;
        $pelanggan = $riwayat->pelanggan;
        $parfum = Parfum::all();

        return view('riwayat.edit', compact(
            'riwayat',
            'detail',
            'pelanggan',
            'parfum'
        ));
    }

    // =============================
    // ADMIN - UPDATE DATA TRANSAKSI
    // =============================
    public function update(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $riwayat = Transaksi::findOrFail($id);

        foreach ($request->detail as $id_detail => $d) {
            $detail = DetailTransaksi::find($id_detail);
            if ($detail) {
                $detail->update([
                    'qty' => $d['qty'],
                    'id_parfum' => $d['id_parfum'] ?? null,
                    'harga' => $detail->harga,
                ]);
            }
        }

        $total = DetailTransaksi::where('id_transaksi', $id)
            ->sum(DB::raw('qty * harga'));
        $riwayat->update(['total_harga' => $total]);

        return redirect()->route('riwayat.detail', ['id' => $id])
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    // =============================
    // ADMIN - UPDATE STATUS TRANSAKSI
    // =============================
    public function updateStatus(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $request->validate(['status_transaksi' => 'required']);
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->status_transaksi = $request->status_transaksi;
        $transaksi->save();

        return back()->with('success', 'Status berhasil diperbarui');
    }

    // =============================
    // ADMIN - HAPUS RIWAYAT
    // =============================
    public function destroy($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('riwayat', 'delete');
        
        Transaksi::findOrFail($id)->delete();
        return redirect()
            ->route('riwayat.index')
            ->with('success', 'Riwayat transaksi berhasil dihapus');
    }

    // =============================
    // ADMIN - PROSES ORDER
    // =============================
    public function prosesOrder($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        if ($trx->status_transaksi === 'antrian') {
            $trx->status_transaksi = 'proses';
            $trx->save();
        }
        return redirect()->route('riwayat.index',['tab'=>'proses'])
            ->with('success','Transaksi berhasil diproses!');
    }

    // =============================
    // ADMIN - SELESAI ORDER
    // =============================
    public function selesaiOrder($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        if (in_array($trx->status_transaksi, ['proses', 'siap_di_ambil'])) {
            $trx->status_transaksi = 'selesai';
            $trx->save();
        }
        return redirect()
            ->route('riwayat.index', ['tab' => 'selesai'])
            ->with('success','Transaksi berhasil diselesaikan!');
    }

    // =============================
    // ADMIN - SIAP DIAMBIL
    // =============================
    public function siapDiAmbil($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        $trx->status_transaksi = 'siap_di_ambil';
        $trx->save();
        return redirect()->route('riwayat.index',['tab'=>'siap_di_ambil'])
            ->with('success','Transaksi siap diambil!');
    }

    // =============================
    // ADMIN - BATAL ORDER
    // =============================
    public function batalOrder($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('riwayat', 'delete');
        
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status_transaksi === 'batal') {
            return redirect()
                ->route('riwayat.index', ['tab' => 'batal'])
                ->with('info', 'Transaksi ini sudah dibatalkan');
        }

        if (
            $transaksi->status_bayar === 'lunas' ||
            $transaksi->status_transaksi === 'selesai'
        ) {
            return redirect()
                ->route('riwayat.index')
                ->with('error', 'Transaksi tidak bisa dibatalkan');
        }

        $transaksi->update([
            'status_transaksi' => 'batal'
        ]);

        return redirect()
            ->route('riwayat.index', ['tab' => 'batal'])
            ->with('success', 'Transaksi berhasil dibatalkan');
    }

    // =============================
    // ADMIN - BAYAR SUBMIT
    // =============================
    public function bayarSubmit(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $trs = Transaksi::findOrFail($id);
        
        $validated = $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1'
        ]);
        
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
        } elseif ($totalBayarBaru > 0) {
            $trs->status_bayar = 'DP';
            $trs->tgl_lunas = null;
        } else {
            $trs->status_bayar = 'belum bayar';
            $trs->tgl_lunas = null;
        }
        
        $trs->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diperbarui',
                'dp_terbayar' => $totalBayarBaru,
                'sisa_bayar' => max(0, $sisaTagihan),
                'status_bayar' => $trs->status_bayar,
                'total_tagihan' => $totalTagihan
            ]);
        }

        return redirect()->back()->with('success','Pembayaran berhasil diperbarui.');
    }

    // =============================
    // ADMIN - EDIT LAYANAN
    // =============================
    public function editLayanan($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $detail = DetailTransaksi::with([
            'layanan',
            'jenis.satuan',
            'transaksi.pelanggan'
        ])->findOrFail($id);
        $riwayat = $detail->transaksi;

        if (!$riwayat) {
            abort(404, 'Transaksi tidak ditemukan');
        }

        $pelanggan = $riwayat->pelanggan ?? (object)[
            'nama'   => $riwayat->nama_pelanggan,
            'no_hp'  => $riwayat->no_hp,
            'gambar' => null
        ];

        return view('riwayat.edit', [
            'detail'    => collect([$detail]),
            'riwayat'   => $riwayat,
            'pelanggan' => $pelanggan,
            'parfum'    => Parfum::all(),
            'satuan'    => Satuan::all(),
        ]);
    }

    // =============================
    // ADMIN - UPDATE LAYANAN
    // =============================
    public function updateLayanan(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $detail = DetailTransaksi::findOrFail($id);
        $request->validate([
            'nama_jenis'=>'required|string|max:255',
            'harga'=>'required|numeric',
            'id_satuan'=>'required|exists:satuan,id_satuan',
            'qty'=>'required|numeric|min:1',
        ]);

        $detail->update($request->only(['nama_jenis','harga','id_satuan','qty']));

        $layanan = $detail->layanan;
        if($request->filled('nama_layanan') || $request->filled('proses')) {
            $layanan->update([
                'nama_layanan'=>$request->nama_layanan ?? $layanan->nama_layanan,
                'proses'=>isset($request->proses)?implode(',',$request->proses):$layanan->proses,
            ]);
        }

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

        return redirect()->route('riwayat.edit', $detail->id_transaksi)
            ->with('success','Layanan berhasil diperbarui.');
    }

    // =============================
    // ADMIN - TAMBAH LAYANAN KE RIWAYAT
    // =============================
    public function storeLayanan(Request $request, $id)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('riwayat', 'add');
        
        $request->validate([
            'id_layanan' => 'required|exists:layanan,id_layanan',
            'qty'        => 'required|numeric|min:0.01',
            'parfum'     => 'nullable|exists:parfum,id_parfum',
        ]);

        $layanan = Layanan::with('jenis')->findOrFail($request->id_layanan);
        $jenis   = $layanan->jenis->first();

        if (!$jenis) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis layanan tidak ditemukan'
            ], 422);
        }

        $key  = "riwayat_{$id}/layanan";
        $data = session()->get($key, []);
        $data[] = [
            'id_layanan' => $layanan->id_layanan,
            'qty'        => $request->qty,
            'id_parfum'  => $request->parfum,
        ];
        session()->put($key, $data);

        return response()->json([
            'success'  => true,
            'redirect' => route('riwayat.edit', $id)
        ]);
    }

    // =============================
    // ADMIN - ADD LAYANAN
    // =============================
    public function addLayanan(Request $request, $id)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('riwayat', 'add');
        
        try {
            $request->validate([
                'id_layanan' => 'required|exists:layanan,id_layanan',
                'id_jenis_layanan' => 'required|exists:jenis_layanan,id_jenis_layanan',
                'qty'        => 'required|numeric|min:0.01',
                'parfum'     => 'nullable|exists:parfum,id_parfum',
            ]);

            $jenis = JenisLayanan::findOrFail($request->id_jenis_layanan);

            DetailTransaksi::create([
                'id_transaksi'     => $id,
                'id_layanan'       => $request->id_layanan,
                'id_jenis_layanan' => $jenis->id_jenis_layanan,
                'qty'              => $request->qty,
                'id_parfum'        => $request->parfum,
                'harga'            => $jenis->harga,
            ]);

            $transaksi = Transaksi::findOrFail($id);
            $transaksi->total_harga = DetailTransaksi::where('id_transaksi', $id)
                ->sum(DB::raw('qty * harga'));
            $transaksi->save();

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error addLayanan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================
    // ADMIN - ADD LAYANAN PAGE
    // =============================
    public function addLayananPage($id)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('riwayat', 'view');
        
        $riwayat = Transaksi::findOrFail($id);
        $layananUtama = Layanan::with('jenis.satuan')->get();
        $parfum = Parfum::all();

        $jenisLayananData = [];
        foreach ($layananUtama as $layanan) {
            $jenisLayananData[$layanan->id_layanan] = $layanan->jenis->map(function($jenis) {
                return [
                    'id' => $jenis->id_jenis_layanan,
                    'nama' => $jenis->nama_jenis,
                    'harga' => $jenis->harga,
                    'satuan' => $jenis->satuan->nama_satuan ?? ''
                ];
            })->toArray();
        }

        return view('riwayat.addlayanan', compact(
            'riwayat',
            'layananUtama',
            'parfum',
            'jenisLayananData'
        ));
    }

    // =============================
    // ADMIN - UPDATE DETAIL
    // =============================
    public function updateDetail(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('riwayat', 'edit');
        
        $detail = DetailTransaksi::findOrFail($id);
        $detail->update([
            'qty' => $request->qty,
            'id_parfum' => $request->id_parfum
        ]);

        return response()->json([
            'success' => true,
            'transaksi_id' => $detail->id_transaksi
        ]);
    }

    // =============================
    // ADMIN - DELETE DETAIL
    // =============================
    public function deleteDetail($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('riwayat', 'delete');
        
        $detail = DetailTransaksi::find($id);
        if(!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail transaksi tidak ditemukan'
            ]);
        }

        $detail->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    // =============================
    // KASIR - INDEX
    // =============================
    public function indexKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('riwayat', 'view');
        
        $tab = $request->tab ?? 'antrian';
        $riwayat = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'offline')
            ->orderBy('id_transaksi', 'DESC')
            ->get();

        if (in_array($tab, ['antrian','proses','siap_di_ambil','selesai','batal'])) {
            $riwayat = $riwayat->where('status_transaksi', $tab);
        }

        return view('kasir.riwayat.index', compact('riwayat','tab'));
    }

    // =============================
    // KASIR - DETAIL TRANSAKSI
    // =============================
    public function detailKasir($id)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('riwayat', 'view');
        
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404);

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)[
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'no_hp'  => $transaksi->no_hp,
                'gambar' => null
            ];

        $detail = DB::table('detail_transaksi as d')
            ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
            ->leftJoin('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
            ->leftJoin('satuan as s', 's.id_satuan', '=', 'j.id_satuan')
            ->select(
                'd.*',
                'd.id_detail_transaksi as id_detail',
                'j.nama_jenis',
                'j.harga as harga_jenis',
                'j.keterangan as keterangan_jenis',
                'l.nama_layanan',
                's.nama_satuan as satuan',
                DB::raw('(d.qty * d.harga) AS total_harga')
            )
            ->where('d.id_transaksi', $id)
            ->get();

        $subtotal = $detail->sum('total_harga');
        $dp = $transaksi->total_bayar ?? 0;
        $diskon = $transaksi->diskon ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;

        return view('kasir.riwayat.detail', compact(
            'transaksi','pelanggan','detail','subtotal','dp','diskon','sisaBayar'
        ));
    }

    // =============================
    // KASIR - METHODS (Semua dengan permission check)
    // =============================
    
    public function bayarSubmitKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        return $this->bayarSubmit($request, $id);
    }

    public function bayarKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        
        $trs = Transaksi::findOrFail($id);
        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1',
            'diskon'       => 'nullable|numeric|min:0',
            'tipe_diskon'  => 'nullable|in:nominal,percent',
            'id_metode_bayar'=>'required|exists:metode_bayar,id_metode_bayar',
            'langsung_bayar'=>'required|boolean'
        ]);

        $subtotal = $trs->total_harga;
        $diskon = $request->diskon ?? 0;

        if ($request->tipe_diskon === 'percent') {
            $diskon = $subtotal * min($diskon,100)/100;
        }

        $totalTagihan = $subtotal - $diskon;
        $jumlahBayar = $request->jumlah_bayar;

        if (is_null($trs->dp) && $jumlahBayar < $totalTagihan) {
            $trs->dp = $jumlahBayar;
        }

        $trs->total_bayar = min(($trs->total_bayar ?? 0) + $jumlahBayar, $totalTagihan);
        $trs->diskon = $diskon;
        $trs->id_metode_bayar = $request->id_metode_bayar;

        if ($trs->total_bayar >= $totalTagihan) {
            $trs->status_bayar = 'lunas';
            $trs->tgl_lunas = now();
        } elseif ($trs->total_bayar > 0) {
            $trs->status_bayar = 'DP';
            $trs->tgl_lunas = null;
        } else {
            $trs->status_bayar = 'belum bayar';
            $trs->tgl_lunas = null;
        }
        $trs->save();

        return response()->json([
            'success' => true,
            'total' => $totalTagihan,
            'bayar' => $jumlahBayar,
            'diskon' => $diskon,
            'nama' => $trs->pelanggan->nama ?? $trs->nama_pelanggan,
            'hp'   => $trs->pelanggan->no_hp ?? $trs->no_hp,
            'status_bayar' => $trs->status_bayar
        ]);
    }

    public function showKasir($id)
    {
        requirePermission('riwayat', 'view');
        return $this->detailKasir($id);
    }

    public function editKasir($id)
    {
        requirePermission('riwayat', 'edit');
        
        $riwayat = Transaksi::with(['detail.jenis.satuan','detail.parfum','pelanggan'])
            ->findOrFail($id);
        $detail = $riwayat->detail;
        $pelanggan = $riwayat->pelanggan;
        $parfum = Parfum::all();

        return view('kasir.riwayat.edit', compact('riwayat','detail','pelanggan','parfum'));
    }

    public function updateKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        
        $riwayat = Transaksi::findOrFail($id);

        foreach ($request->detail as $id_detail => $d) {
            $detail = DetailTransaksi::find($id_detail);
            if($detail){
                $detail->update([
                    'qty' => $d['qty'],
                    'id_parfum' => $d['id_parfum'] ?? null,
                    'harga' => $detail->harga
                ]);
            }
        }

        $total = DetailTransaksi::where('id_transaksi',$id)
            ->sum(DB::raw('qty * harga'));
        $riwayat->update(['total_harga'=>$total]);

        return redirect()->route('kasir.riwayat.detail',['id'=>$id])
            ->with('success','Transaksi berhasil diperbarui.');
    }

    public function prosesOrderKasir($id)
    {
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        if($trx->status_transaksi === 'antrian'){
            $trx->status_transaksi = 'proses';
            $trx->save();
        }
        return redirect()->route('kasir.riwayat.index',['tab'=>'proses'])
            ->with('success','Transaksi berhasil diproses!');
    }

    public function selesaiOrderKasir($id)
    {
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        if(in_array($trx->status_transaksi,['proses','siap_di_ambil'])){
            $trx->status_transaksi = 'selesai';
            $trx->save();
        }
        return redirect()->route('kasir.riwayat.index',['tab'=>'selesai'])
            ->with('success','Transaksi berhasil diselesaikan!');
    }

    public function siapDiAmbilKasir($id)
    {
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        $trx->status_transaksi = 'siap_di_ambil';
        $trx->save();
        return redirect()->route('kasir.riwayat.index',['tab'=>'siap_di_ambil'])
            ->with('success','Transaksi siap diambil!');
    }

    public function batalOrderKasir($id)
    {
        requirePermission('riwayat', 'delete');
        
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status_bayar === 'lunas') {
            return redirect()
                ->route('kasir.riwayat.index')
                ->with('error', 'Transaksi sudah lunas dan tidak bisa dibatalkan');
        }

        $transaksi->update(['status_transaksi' => 'batal']);

        return redirect()->route('kasir.riwayat.index',['tab'=>'antrian'])
            ->with('success','Transaksi berhasil dibatalkan!');
    }

    public function bayarModalKasir($id)
    {
        requirePermission('riwayat', 'view');
        
        $transaksi = Transaksi::with(['pelanggan','detail.jenis.satuan','detail.parfum'])
            ->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail = $transaksi->detail;
        $subtotal = $detail->sum(function($d){ return $d->qty * $d->harga; });
        $diskon = $transaksi->diskon ?? 0;
        $dp = $transaksi->total_bayar ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;

        return view('kasir.riwayat.modal_bayar', compact(
            'transaksi','pelanggan','detail','subtotal','dp','sisaBayar','diskon'
        ));
    }

    public function actionsKasir($id)
    {
        requirePermission('riwayat', 'view');
        
        $transaksi = Transaksi::with(['pelanggan','detail.jenis.satuan','detail.parfum'])
            ->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail = $transaksi->detail;
        $subtotal = $detail->sum(fn($d) => $d->qty * $d->harga);
        $diskon = $transaksi->diskon ?? 0;
        $dp = $transaksi->total_bayar ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;
        $statusBayar = strtolower($transaksi->status_bayar ?? 'belum bayar');

        return view('kasir.riwayat.detail', compact(
            'transaksi','pelanggan','detail','subtotal','dp','sisaBayar','diskon','statusBayar'
        ));
    }

    public function addLayananKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        
        try {
            $validated = $request->validate([
                'id_layanan'       => 'required|exists:layanan,id_layanan',
                'id_jenis_layanan' => 'required|exists:jenis_layanan,id_jenis_layanan',
                'qty'              => 'required|numeric|min:0.01',
                'parfum'           => 'nullable|exists:parfum,id_parfum',
            ]);

            $jenis = JenisLayanan::with('satuan')->findOrFail($validated['id_jenis_layanan']);
            $transaksi = Transaksi::findOrFail($id);

            $detail = $transaksi->detail()->create([
                'id_layanan'       => $validated['id_layanan'],
                'id_jenis_layanan' => $jenis->id_jenis_layanan,
                'id_parfum'        => $validated['parfum'] ?? null,
                'harga'            => $jenis->harga,
                'qty'              => $validated['qty'],
                'id_satuan'        => $jenis->satuan->id_satuan ?? null,
            ]);

            $transaksi->total_harga = $transaksi->detail()->sum(DB::raw('harga * qty'));
            $transaksi->save();

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil ditambahkan',
                'data'    => ['detail' => $detail, 'total_harga' => $transaksi->total_harga]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error Add Layanan Kasir:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function addLayananPageKasir($id)
    {
        requirePermission('riwayat', 'edit');
        
        $riwayat = Transaksi::findOrFail($id);
        $layananUtama = Layanan::with('jenis.satuan')->get();
        $parfum = Parfum::all();

        $jenisLayananData = [];
        foreach ($layananUtama as $layanan) {
            $jenisLayananData[$layanan->id_layanan] = $layanan->jenis->map(function($jenis) {
                return ['id' => $jenis->id_jenis_layanan, 'nama' => $jenis->nama_jenis, 'harga' => $jenis->harga, 'satuan' => $jenis->satuan->nama_satuan ?? ''];
            })->toArray();
        }

        return view('kasir.riwayat.addlayanan', compact('riwayat','layananUtama','parfum','jenisLayananData'));
    }

    public function destroyKasir($id)
    {
        requirePermission('riwayat', 'delete');
        
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->detail()->delete();
        $transaksi->delete();

        return redirect()->route('kasir.riwayat.index')->with('success', 'Transaksi berhasil dihapus');
    }

    // =============================
    // ADMIN2 - METHODS (Semua dengan permission check)
    // =============================
    
    public function indexAdmin2(Request $request)
    {
        requirePermission('riwayat', 'view');
        
        $tab = $request->tab ?? 'antrian';
        $riwayat = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'offline')
            ->orderBy('id_transaksi', 'DESC')
            ->get();

        if (in_array($tab, ['antrian','proses','siap_di_ambil','selesai','batal'])) {
            $riwayat = $riwayat->where('status_transaksi', $tab);
        }

        return view('admin2.riwayat.index', compact('riwayat','tab'));
    }

    public function detailAdmin2($id)
    {
        requirePermission('riwayat', 'view');
        
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404);

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)['nama_pelanggan' => $transaksi->nama_pelanggan, 'no_hp' => $transaksi->no_hp, 'gambar' => null];

        $detail = DB::table('detail_transaksi as d')
            ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
            ->leftJoin('layanan as l', 'l.id_layanan', '=', 'd.id_layanan')
            ->leftJoin('satuan as s', 's.id_satuan', '=', 'j.id_satuan')
            ->select('d.*','d.id_detail_transaksi as id_detail','j.nama_jenis','j.harga as harga_jenis',
                'j.keterangan as keterangan_jenis','l.nama_layanan','s.nama_satuan as satuan',
                DB::raw('(d.qty * d.harga) AS total_harga'))
            ->where('d.id_transaksi', $id)
            ->get();

        $subtotal  = $detail->sum('total_harga');
        $dp        = $transaksi->total_bayar ?? 0;
        $diskon    = $transaksi->diskon ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;

        return view('admin2.riwayat.detail', compact('transaksi','pelanggan','detail','subtotal','dp','diskon','sisaBayar'));
    }

    public function bayarSubmitAdmin2(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        return $this->bayarSubmit($request, $id);
    }

    public function bayarAdmin2(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        return $this->bayarKasir($request, $id);
    }

    public function showAdmin2($id)
    {
        requirePermission('riwayat', 'view');
        return $this->detailAdmin2($id);
    }

    public function editAdmin2($id)
    {
        requirePermission('riwayat', 'edit');
        
        $riwayat = Transaksi::with(['detail.jenis.satuan','detail.jenis.layanan','detail.parfum','pelanggan'])->findOrFail($id);
        $detail = $riwayat->detail;
        $pelanggan = $riwayat->pelanggan;
        $parfum = Parfum::all();

        return view('admin2.riwayat.edit', compact('riwayat', 'detail', 'pelanggan', 'parfum'));
    }

    public function updateAdmin2(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        
        $riwayat = Transaksi::findOrFail($id);

        foreach ($request->detail as $id_detail => $d) {
            $detail = DetailTransaksi::find($id_detail);
            if($detail){
                $detail->update(['qty' => $d['qty'], 'id_parfum' => $d['id_parfum'] ?? null, 'harga' => $detail->harga]);
            }
        }

        $total = DetailTransaksi::where('id_transaksi',$id)->sum(DB::raw('qty * harga'));
        $riwayat->update(['total_harga'=>$total]);

        return redirect()->route('admin2.riwayat.detail',['id'=>$id])->with('success','Transaksi berhasil diperbarui.');
    }

    public function prosesOrderAdmin2($id)
    {
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        if($trx->status_transaksi === 'antrian'){ $trx->status_transaksi = 'proses'; $trx->save(); }
        return redirect()->route('admin2.riwayat.index',['tab'=>'proses'])->with('success','Transaksi berhasil diproses!');
    }

    public function selesaiOrderAdmin2($id)
    {
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        if(in_array($trx->status_transaksi,['proses','siap_di_ambil'])){ $trx->status_transaksi = 'selesai'; $trx->save(); }
        return redirect()->route('admin2.riwayat.index',['tab'=>'selesai'])->with('success','Transaksi berhasil diselesaikan!');
    }

    public function siapDiAmbilAdmin2($id)
    {
        requirePermission('riwayat', 'edit');
        
        $trx = Transaksi::findOrFail($id);
        $trx->status_transaksi = 'siap_di_ambil';
        $trx->save();
        return redirect()->route('admin2.riwayat.index',['tab'=>'siap_di_ambil'])->with('success','Transaksi siap diambil!');
    }

    public function batalOrderAdmin2($id)
    {
        requirePermission('riwayat', 'delete');
        
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status_bayar === 'lunas') {
            return redirect()->route('admin2.riwayat.index')->with('error', 'Transaksi sudah lunas dan tidak bisa dibatalkan');
        }

        $transaksi->update(['status_transaksi' => 'batal']);
        return redirect()->route('admin2.riwayat.index',['tab'=>'antrian'])->with('success','Transaksi berhasil dibatalkan!');
    }

    public function bayarModalAdmin2($id)
    {
        requirePermission('riwayat', 'view');
        
        $transaksi = Transaksi::with(['pelanggan','detail.jenis.satuan','detail.parfum'])->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail    = $transaksi->detail;
        $subtotal  = $detail->sum(function($d){ return $d->qty * $d->harga; });
        $diskon    = $transaksi->diskon ?? 0;
        $dp        = $transaksi->total_bayar ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;

        return view('admin2.riwayat.modal_bayar', compact('transaksi','pelanggan','detail','subtotal','dp','sisaBayar','diskon'));
    }

    public function actionsAdmin2($id)
    {
        requirePermission('riwayat', 'view');
        
        $transaksi = Transaksi::with(['pelanggan','detail.jenis.satuan','detail.parfum'])->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail    = $transaksi->detail;
        $subtotal  = $detail->sum(fn($d) => $d->qty * $d->harga);
        $diskon    = $transaksi->diskon ?? 0;
        $dp        = $transaksi->total_bayar ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;
        $statusBayar = strtolower($transaksi->status_bayar ?? 'belum bayar');

        return view('admin2.riwayat.detail', compact('transaksi','pelanggan','detail','subtotal','dp','sisaBayar','diskon','statusBayar'));
    }

    public function addLayananAdmin2(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        
        try {
            $request->validate([
                'id_layanan'       => 'required|exists:layanan,id_layanan',
                'id_jenis_layanan' => 'required|exists:jenis_layanan,id_jenis_layanan',
                'qty'              => 'required|numeric|min:0.01',
                'parfum'           => 'nullable|exists:parfum,id_parfum',
            ]);

            $jenis = JenisLayanan::findOrFail($request->id_jenis_layanan);

            DetailTransaksi::create([
                'id_transaksi'     => $id,
                'id_layanan'       => $request->id_layanan,
                'id_jenis_layanan' => $jenis->id_jenis_layanan,
                'qty'              => $request->qty,
                'id_parfum'        => $request->parfum,
                'harga'            => $jenis->harga,
            ]);

            $transaksi = Transaksi::findOrFail($id);
            $transaksi->total_harga = DetailTransaksi::where('id_transaksi', $id)->sum(DB::raw('qty * harga'));
            $transaksi->save();

            return response()->json(['success' => true, 'message' => 'Layanan berhasil ditambahkan']);
        } catch (\Exception $e) {
            \Log::error('Error addLayananAdmin2: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function addLayananPageAdmin2($id)
    {
        requirePermission('riwayat', 'view');
        
        $riwayat = Transaksi::findOrFail($id);
        $layananUtama = Layanan::with('jenis.satuan')->get();
        $parfum = Parfum::all();

        $jenisLayananData = [];
        foreach ($layananUtama as $layanan) {
            $jenisLayananData[$layanan->id_layanan] = $layanan->jenis->map(function($jenis) {
                return ['id' => $jenis->id_jenis_layanan, 'nama' => $jenis->nama_jenis, 'harga' => $jenis->harga, 'satuan' => $jenis->satuan->nama_satuan ?? ''];
            })->toArray();
        }

        return view('admin2.riwayat.addlayanan', compact('riwayat','layananUtama','parfum','jenisLayananData'));
    }

    public function deleteDetailAdmin2($id)
    {
        requirePermission('riwayat', 'delete');
        
        try {
            $detail      = DetailTransaksi::findOrFail($id);
            $idTransaksi = $detail->id_transaksi;
            $detail->delete();

            $transaksi = Transaksi::findOrFail($idTransaksi);
            $transaksi->total_harga = DetailTransaksi::where('id_transaksi', $idTransaksi)->sum(DB::raw('qty * harga'));
            $transaksi->save();

            return response()->json(['success' => true, 'message' => 'Layanan berhasil dihapus']);
        } catch (\Exception $e) {
            \Log::error('Error deleteDetailAdmin2: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroyAdmin2($id)
    {
        requirePermission('riwayat', 'delete');
        
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->detail()->delete();
        $transaksi->delete();

        return redirect()->route('admin2.riwayat.index')->with('success', 'Transaksi berhasil dihapus');
    }

    private function validateDPBeforeStatusChange($transaksi, $newStatus)
    {
        if ($newStatus === 'siap_di_ambil') {
            if ($transaksi->status_bayar === 'DP') {
                return ['valid' => false, 'message' => '⚠️ Pembayaran masih DP! Silakan lunasi pembayaran terlebih dahulu.', 'sisa_pembayaran' => $transaksi->total_harga - $transaksi->total_bayar];
            }
            if ($transaksi->status_bayar === 'belum_lunas') {
                return ['valid' => false, 'message' => '⚠️ Belum ada pembayaran! Silakan lakukan pembayaran terlebih dahulu.', 'sisa_pembayaran' => $transaksi->total_harga];
            }
        }
        return ['valid' => true];
    }

public function notaHtml($id)
{
    $data = $this->getDataCetakNota($id);
    return view('riwayat.cetaknota', $data);
}
}