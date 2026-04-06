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

/**
 * RiwayatController
 * Mengelola seluruh operasi riwayat transaksi untuk Admin, Kasir, dan Admin2.
 * Mencakup: tampil daftar, detail, ubah status, pembayaran, cetak nota, dan hapus transaksi.
 */
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
    // HELPER - QUERY DETAIL TRANSAKSI (REUSABLE)
    // Menggunakan COALESCE agar harga fallback ke j.harga jika d.harga = 0 / NULL
    // =============================
    private function queryDetail($id)
    {
        return DB::table('detail_transaksi as d')
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
                // FIX: pakai COALESCE agar jika d.harga = 0/NULL, fallback ke j.harga
                DB::raw('COALESCE(NULLIF(d.harga, 0), j.harga, 0) AS harga_efektif'),
                DB::raw('(d.qty * COALESCE(NULLIF(d.harga, 0), j.harga, 0)) AS total_harga')
            )
            ->where('d.id_transaksi', $id)
            ->get();
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

        if (!$transaksi) abort(404, 'Transaksi tidak ditemukan');

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)[
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'no_hp'          => $transaksi->no_hp,
                'gambar'         => null,
            ];

        // FIX: gunakan queryDetail helper yang sudah pakai COALESCE
        $detail = $this->queryDetail($id);

        return compact('transaksi', 'pelanggan', 'detail');
    }

    // =============================
    // HELPER METHOD - VALIDASI STATUS SEBELUM UBAH KE SIAP DIAMBIL
    // =============================
    private function validateDPBeforeStatusChange($transaksi, $newStatus)
    {
        if ($newStatus === 'siap_di_ambil') {
            if ($transaksi->status_bayar === 'DP') {
                return [
                    'valid'            => false,
                    'message'          => '⚠️ Pembayaran masih DP! Silakan lunasi pembayaran terlebih dahulu.',
                    'sisa_pembayaran'  => $transaksi->total_harga - $transaksi->total_bayar
                ];
            }
            if ($transaksi->status_bayar === 'belum_lunas') {
                return [
                    'valid'           => false,
                    'message'         => '⚠️ Belum ada pembayaran! Silakan lakukan pembayaran terlebih dahulu.',
                    'sisa_pembayaran' => $transaksi->total_harga
                ];
            }
        }

        return ['valid' => true];
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
    // CETAK NOTA HTML - ADMIN
    // =============================
    public function notaHtml($id)
    {
        $data = $this->getDataCetakNota($id);
        return view('riwayat.cetaknota', $data);
    }

    // =============================
    // ADMIN - TAMPILKAN RIWAYAT TRANSAKSI
    // =============================
    public function index(Request $request)
    {
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
            ->paginate(10);

        return view('riwayat.index', compact('riwayat', 'tab'));
    }

    // =============================
    // ADMIN - DETAIL TRANSAKSI
    // =============================
    public function detail($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404, 'Transaksi tidak ditemukan');

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)['nama_pelanggan' => $transaksi->nama_pelanggan, 'no_hp' => $transaksi->no_hp, 'gambar' => null];

        // FIX: gunakan queryDetail helper
        $detail = $this->queryDetail($id);

        // FIX: subtotal dari detail, fallback ke total_harga transaksi jika 0
        $subtotal = $detail->sum('total_harga');
        if ($subtotal <= 0) {
            $subtotal = $transaksi->total_harga ?? 0;
        }

        return view('riwayat.detail', compact('transaksi', 'pelanggan', 'detail', 'subtotal'));
    }

    // =============================
    // ADMIN - FORM EDIT DATA TRANSAKSI
    // =============================
    public function edit($id)
    {
        requirePermission('riwayat', 'edit');

        $riwayat   = Transaksi::with(['detail.jenis.satuan', 'detail.parfum', 'pelanggan'])->findOrFail($id);
        $detail    = $riwayat->detail;
        $pelanggan = $riwayat->pelanggan;
        $parfum    = Parfum::all();

        return view('riwayat.edit', compact('riwayat', 'detail', 'pelanggan', 'parfum'));
    }

    // =============================
    // ADMIN - UPDATE DATA TRANSAKSI
    // =============================
    public function update(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        $request->validate([
            'detail'             => 'required|array',
            'detail.*.qty'       => 'required|numeric|min:0.01',
            'detail.*.id_parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        $riwayat = Transaksi::findOrFail($id);

        foreach ($request->detail as $id_detail => $d) {
            $detail = DetailTransaksi::find($id_detail);
            if ($detail) {
                $detail->update([
                    'qty'       => $d['qty'],
                    'id_parfum' => $d['id_parfum'] ?? null,
                    'harga'     => $detail->harga,
                ]);
            }
        }

        // Hitung ulang total harga dengan COALESCE
        $total = DB::table('detail_transaksi as d')
            ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
            ->where('d.id_transaksi', $id)
            ->sum(DB::raw('d.qty * COALESCE(NULLIF(d.harga, 0), j.harga, 0)'));

        $riwayat->update(['total_harga' => $total]);

        return redirect()->route('riwayat.detail', ['id' => $id])
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    // =============================
    // ADMIN - UPDATE STATUS TRANSAKSI
    // =============================
    public function updateStatus(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        $request->validate([
            'status_transaksi' => 'required|in:antrian,proses,siap_di_ambil,selesai,batal'
        ]);

        $transaksi                    = Transaksi::findOrFail($id);
        $transaksi->status_transaksi  = $request->status_transaksi;
        $transaksi->save();

        return back()->with('success', 'Status berhasil diperbarui');
    }

    // =============================
    // ADMIN - HAPUS RIWAYAT TRANSAKSI
    // =============================
    public function destroy($id)
    {
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
        requirePermission('riwayat', 'edit');

        $trx = Transaksi::findOrFail($id);

        if ($trx->status_transaksi === 'antrian') {
            $trx->status_transaksi = 'proses';
            $trx->save();
        }

        return redirect()->route('riwayat.index', ['tab' => 'proses'])
            ->with('success', 'Transaksi berhasil diproses!');
    }

    // =============================
    // ADMIN - SELESAI ORDER
    // =============================
    public function selesaiOrder($id)
    {
        requirePermission('riwayat', 'edit');

        $trx = Transaksi::findOrFail($id);

        if (in_array($trx->status_transaksi, ['proses', 'siap_di_ambil'])) {
            $trx->status_transaksi = 'selesai';
            $trx->save();
        }

        return redirect()
            ->route('riwayat.index', ['tab' => 'selesai'])
            ->with('success', 'Transaksi berhasil diselesaikan!');
    }

    // =============================
    // ADMIN - SIAP DIAMBIL
    // =============================
    public function siapDiAmbil($id)
    {
        requirePermission('riwayat', 'edit');

        $trx                       = Transaksi::findOrFail($id);
        $trx->status_transaksi     = 'siap_di_ambil';
        $trx->save();

        return redirect()->route('riwayat.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Transaksi siap diambil!');
    }

    // =============================
    // ADMIN - BATAL ORDER
    // =============================
    public function batalOrder($id)
    {
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
                ->with('error', 'Transaksi tidak bisa dibatalkan karena sudah lunas atau selesai');
        }

        $transaksi->update(['status_transaksi' => 'batal']);

        return redirect()
            ->route('riwayat.index', ['tab' => 'batal'])
            ->with('success', 'Transaksi berhasil dibatalkan');
    }

    // =============================
    // ADMIN - BAYAR SUBMIT
    // =============================
    public function bayarSubmit(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        $trs = Transaksi::findOrFail($id);

        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1'
        ]);

        $jumlahBayarBaru = (float) $request->jumlah_bayar;
        $totalBayarLama  = (float) ($trs->total_bayar ?? 0);
        $subtotal        = (float) $trs->total_harga;
        $diskon          = (float) ($trs->diskon ?? 0);
        $totalTagihan    = $subtotal - $diskon;
        $totalBayarBaru  = $totalBayarLama + $jumlahBayarBaru;

        $trs->dp         = $totalBayarBaru < $totalTagihan
            ? $totalBayarBaru
            : ($trs->dp ?? $totalBayarBaru);
        $trs->total_bayar = $totalBayarBaru;

        $sisaTagihan = $totalTagihan - $totalBayarBaru;

        if ($sisaTagihan <= 0) {
            $trs->status_bayar = 'lunas';
            $trs->tgl_lunas    = now();
        } elseif ($totalBayarBaru > 0) {
            $trs->status_bayar = 'DP';
            $trs->tgl_lunas    = null;
        } else {
            $trs->status_bayar = 'belum bayar';
            $trs->tgl_lunas    = null;
        }

        $trs->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'       => true,
                'message'       => 'Pembayaran berhasil diperbarui',
                'dp_terbayar'   => $totalBayarBaru,
                'sisa_bayar'    => max(0, $sisaTagihan),
                'status_bayar'  => $trs->status_bayar,
                'total_tagihan' => $totalTagihan
            ]);
        }

        return redirect()->back()->with('success', 'Pembayaran berhasil diperbarui.');
    }

    // =============================
    // ADMIN - EDIT LAYANAN
    // =============================
    public function editLayanan($id)
    {
        requirePermission('riwayat', 'edit');

        $detail  = DetailTransaksi::with(['layanan', 'jenis.satuan', 'transaksi.pelanggan'])->findOrFail($id);
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
        requirePermission('riwayat', 'edit');

        $detail = DetailTransaksi::findOrFail($id);

        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'harga'      => 'required|numeric|min:0',
            'id_satuan'  => 'required|exists:satuan,id_satuan',
            'qty'        => 'required|numeric|min:1',
        ]);

        $detail->update($request->only(['nama_jenis', 'harga', 'id_satuan', 'qty']));

        $layanan = $detail->layanan;
        if ($request->filled('nama_layanan') || $request->filled('proses')) {
            $layanan->update([
                'nama_layanan' => $request->nama_layanan ?? $layanan->nama_layanan,
                'proses'       => isset($request->proses)
                    ? implode(',', $request->proses)
                    : $layanan->proses,
            ]);
        }

        $jenisBaru = session()->get("jenis_baru_{$layanan->id_layanan}", []);
        foreach ($jenisBaru as $jb) {
            JenisLayanan::create([
                'id_layanan'  => $layanan->id_layanan,
                'nama_jenis'  => $jb['nama'],
                'harga'       => $jb['harga'],
                'id_satuan'   => $jb['id_satuan'] ?? null,
                'lama'        => $jb['lama'] ?? null,
                'lama_satuan' => $jb['lama_satuan'] ?? null,
                'gambar'      => $jb['gambar'] ?? null,
            ]);
        }

        session()->forget("jenis_baru_{$layanan->id_layanan}");

        return redirect()->route('riwayat.edit', $detail->id_transaksi)
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    // =============================
    // ADMIN - TAMBAH LAYANAN KE RIWAYAT (via session)
    // =============================
    public function storeLayanan(Request $request, $id)
    {
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
    // ADMIN - ADD LAYANAN (langsung ke database)
    // =============================
    public function addLayanan(Request $request, $id)
    {
        requirePermission('riwayat', 'add');

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
                'harga'            => $jenis->harga, // Pastikan harga tersimpan dari jenis
            ]);

            // Hitung ulang total harga dengan COALESCE
            $transaksi              = Transaksi::findOrFail($id);
            $transaksi->total_harga = DB::table('detail_transaksi as d')
                ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
                ->where('d.id_transaksi', $id)
                ->sum(DB::raw('d.qty * COALESCE(NULLIF(d.harga, 0), j.harga, 0)'));
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
    // ADMIN - HALAMAN TAMBAH LAYANAN
    // =============================
    public function addLayananPage($id)
    {
        requirePermission('riwayat', 'view');

        $riwayat      = Transaksi::findOrFail($id);
        $layananUtama = Layanan::with('jenis.satuan')->get();
        $parfum       = Parfum::all();

        $jenisLayananData = [];
        foreach ($layananUtama as $layanan) {
            $jenisLayananData[$layanan->id_layanan] = $layanan->jenis->map(function ($jenis) {
                return [
                    'id'     => $jenis->id_jenis_layanan,
                    'nama'   => $jenis->nama_jenis,
                    'harga'  => $jenis->harga,
                    'satuan' => $jenis->satuan->nama_satuan ?? ''
                ];
            })->toArray();
        }

        return view('riwayat.addlayanan', compact('riwayat', 'layananUtama', 'parfum', 'jenisLayananData'));
    }

    // =============================
    // ADMIN - UPDATE DETAIL TRANSAKSI
    // =============================
    public function updateDetail(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        $request->validate([
            'qty'       => 'required|numeric|min:0.01',
            'id_parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        $detail = DetailTransaksi::findOrFail($id);
        $detail->update([
            'qty'       => $request->qty,
            'id_parfum' => $request->id_parfum
        ]);

        return response()->json([
            'success'       => true,
            'transaksi_id'  => $detail->id_transaksi
        ]);
    }

    // =============================
    // ADMIN - DELETE DETAIL TRANSAKSI
    // =============================
    public function deleteDetail($id)
    {
        requirePermission('riwayat', 'delete');

        $detail = DetailTransaksi::find($id);
        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail transaksi tidak ditemukan'
            ], 404);
        }

        $detail->delete();

        return response()->json(['success' => true]);
    }

    // ============================================================
    // =================== KASIR METHODS =========================
    // ============================================================

    // =============================
    // KASIR - TAMPILKAN RIWAYAT TRANSAKSI
    // =============================
    public function indexKasir(Request $request)
    {
        requirePermission('riwayat', 'view');

        $tab = $request->tab ?? 'antrian';

        $riwayat = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'offline')
            ->orderBy('id_transaksi', 'DESC');

        if (in_array($tab, ['antrian', 'proses', 'siap_di_ambil', 'selesai', 'batal'])) {
            $riwayat = $riwayat->where('status_transaksi', $tab);
        }

        $riwayat = $riwayat->paginate(10);

        return view('kasir.riwayat.index', compact('riwayat', 'tab'));
    }

    // =============================
    // KASIR - DETAIL TRANSAKSI
    // =============================
    public function detailKasir($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404, 'Transaksi tidak ditemukan');

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)[
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'no_hp'          => $transaksi->no_hp,
                'gambar'         => null
            ];

        // FIX: gunakan queryDetail helper
        $detail = $this->queryDetail($id);

        // FIX: subtotal dengan fallback ke total_harga transaksi
        $subtotal  = $detail->sum('total_harga');
        if ($subtotal <= 0) {
            $subtotal = $transaksi->total_harga ?? 0;
        }

        $dp        = $transaksi->total_bayar ?? 0;
        $diskon    = $transaksi->diskon ?? 0;
        $sisaBayar = max(0, $subtotal - $diskon - $dp);

        return view('kasir.riwayat.detail', compact(
            'transaksi', 'pelanggan', 'detail', 'subtotal', 'dp', 'diskon', 'sisaBayar'
        ));
    }

    // =============================
    // KASIR - BAYAR SUBMIT (delegasi ke method admin)
    // =============================
    public function bayarSubmitKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        return $this->bayarSubmit($request, $id);
    }

    // =============================
    // KASIR - PROSES PEMBAYARAN
    // =============================
    public function bayarKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        $trs = Transaksi::findOrFail($id);

        $request->validate([
            'jumlah_bayar'    => 'required|numeric|min:1',
            'diskon'          => 'nullable|numeric|min:0',
            'tipe_diskon'     => 'nullable|in:nominal,percent',
            'id_metode_bayar' => 'required|exists:metode_bayar,id_metode_bayar',
            'langsung_bayar'  => 'required|boolean'
        ]);

        $subtotal = $trs->total_harga;
        $diskon   = $request->diskon ?? 0;

        if ($request->tipe_diskon === 'percent') {
            $diskon = $subtotal * min($diskon, 100) / 100;
        }

        $totalTagihan = $subtotal - $diskon;
        $jumlahBayar  = $request->jumlah_bayar;

        if (is_null($trs->dp) && $jumlahBayar < $totalTagihan) {
            $trs->dp = $jumlahBayar;
        }

        $trs->total_bayar     = min(($trs->total_bayar ?? 0) + $jumlahBayar, $totalTagihan);
        $trs->diskon          = $diskon;
        $trs->id_metode_bayar = $request->id_metode_bayar;

        if ($trs->total_bayar >= $totalTagihan) {
            $trs->status_bayar = 'lunas';
            $trs->tgl_lunas    = now();
        } elseif ($trs->total_bayar > 0) {
            $trs->status_bayar = 'DP';
            $trs->tgl_lunas    = null;
        } else {
            $trs->status_bayar = 'belum bayar';
            $trs->tgl_lunas    = null;
        }
        $trs->save();

        return response()->json([
            'success'      => true,
            'total'        => $totalTagihan,
            'bayar'        => $jumlahBayar,
            'diskon'       => $diskon,
            'nama'         => $trs->pelanggan->nama ?? $trs->nama_pelanggan,
            'hp'           => $trs->pelanggan->no_hp ?? $trs->no_hp,
            'status_bayar' => $trs->status_bayar
        ]);
    }

    // =============================
    // KASIR - SHOW (alias ke detailKasir)
    // =============================
    public function showKasir($id)
    {
        requirePermission('riwayat', 'view');
        return $this->detailKasir($id);
    }

    // =============================
    // KASIR - FORM EDIT TRANSAKSI
    // =============================
    public function editKasir($id)
    {
        requirePermission('riwayat', 'edit');

        $riwayat   = Transaksi::with(['detail.jenis.satuan', 'detail.parfum', 'pelanggan'])->findOrFail($id);
        $detail    = $riwayat->detail;
        $pelanggan = $riwayat->pelanggan;
        $parfum    = Parfum::all();

        return view('kasir.riwayat.edit', compact('riwayat', 'detail', 'pelanggan', 'parfum'));
    }

    // =============================
    // KASIR - UPDATE TRANSAKSI
    // =============================
    public function updateKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        $request->validate([
            'detail'             => 'required|array',
            'detail.*.qty'       => 'required|numeric|min:0.01',
            'detail.*.id_parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        $riwayat = Transaksi::findOrFail($id);

        foreach ($request->detail as $id_detail => $d) {
            $detail = DetailTransaksi::find($id_detail);
            if ($detail) {
                $detail->update([
                    'qty'       => $d['qty'],
                    'id_parfum' => $d['id_parfum'] ?? null,
                    'harga'     => $detail->harga
                ]);
            }
        }

        // Hitung ulang total harga dengan COALESCE
        $total = DB::table('detail_transaksi as d')
            ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
            ->where('d.id_transaksi', $id)
            ->sum(DB::raw('d.qty * COALESCE(NULLIF(d.harga, 0), j.harga, 0)'));

        $riwayat->update(['total_harga' => $total]);

        return redirect()->route('kasir.riwayat.detail', ['id' => $id])
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    // =============================
    // KASIR - PROSES ORDER
    // =============================
    public function prosesOrderKasir($id)
    {
        requirePermission('riwayat', 'edit');

        $trx = Transaksi::findOrFail($id);
        if ($trx->status_transaksi === 'antrian') {
            $trx->status_transaksi = 'proses';
            $trx->save();
        }

        return redirect()->route('kasir.riwayat.index', ['tab' => 'proses'])
            ->with('success', 'Transaksi berhasil diproses!');
    }

    // =============================
    // KASIR - SELESAI ORDER
    // =============================
    public function selesaiOrderKasir($id)
    {
        requirePermission('riwayat', 'edit');

        $trx = Transaksi::findOrFail($id);
        if (in_array($trx->status_transaksi, ['proses', 'siap_di_ambil'])) {
            $trx->status_transaksi = 'selesai';
            $trx->save();
        }

        return redirect()->route('kasir.riwayat.index', ['tab' => 'selesai'])
            ->with('success', 'Transaksi berhasil diselesaikan!');
    }

    // =============================
    // KASIR - SIAP DIAMBIL
    // =============================
    public function siapDiAmbilKasir($id)
    {
        requirePermission('riwayat', 'edit');

        $trx                   = Transaksi::findOrFail($id);
        $trx->status_transaksi = 'siap_di_ambil';
        $trx->save();

        return redirect()->route('kasir.riwayat.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Transaksi siap diambil!');
    }

    // =============================
    // KASIR - BATAL ORDER
    // =============================
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

        return redirect()->route('kasir.riwayat.index', ['tab' => 'antrian'])
            ->with('success', 'Transaksi berhasil dibatalkan!');
    }

    // =============================
    // KASIR - MODAL BAYAR
    // =============================
    public function bayarModalKasir($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi = Transaksi::with(['pelanggan', 'detail.jenis.satuan', 'detail.parfum'])->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail    = $transaksi->detail;

        // FIX: subtotal dengan COALESCE fallback
        $subtotal  = $detail->sum(function ($d) {
            $harga = $d->harga > 0 ? $d->harga : ($d->jenis->harga ?? 0);
            return $d->qty * $harga;
        });
        if ($subtotal <= 0) {
            $subtotal = $transaksi->total_harga ?? 0;
        }

        $diskon    = $transaksi->diskon ?? 0;
        $dp        = $transaksi->total_bayar ?? 0;
        $sisaBayar = max(0, $subtotal - $diskon - $dp);

        return view('kasir.riwayat.modal_bayar', compact(
            'transaksi', 'pelanggan', 'detail', 'subtotal', 'dp', 'sisaBayar', 'diskon'
        ));
    }

    // =============================
    // KASIR - ACTIONS
    // =============================
    public function actionsKasir($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi = Transaksi::with(['pelanggan', 'detail.jenis.satuan', 'detail.parfum'])->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail    = $transaksi->detail;

        // FIX: subtotal dengan COALESCE fallback
        $subtotal = $detail->sum(function ($d) {
            $harga = $d->harga > 0 ? $d->harga : ($d->jenis->harga ?? 0);
            return $d->qty * $harga;
        });
        if ($subtotal <= 0) {
            $subtotal = $transaksi->total_harga ?? 0;
        }

        $diskon      = $transaksi->diskon ?? 0;
        $dp          = $transaksi->total_bayar ?? 0;
        $sisaBayar   = max(0, $subtotal - $diskon - $dp);
        $statusBayar = strtolower($transaksi->status_bayar ?? 'belum bayar');

        return view('kasir.riwayat.detail', compact(
            'transaksi', 'pelanggan', 'detail', 'subtotal', 'dp', 'sisaBayar', 'diskon', 'statusBayar'
        ));
    }

    // =============================
    // KASIR - ADD LAYANAN (langsung ke database)
    // =============================
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

            $jenis     = JenisLayanan::with('satuan')->findOrFail($validated['id_jenis_layanan']);
            $transaksi = Transaksi::findOrFail($id);

            $detail = $transaksi->detail()->create([
                'id_layanan'       => $validated['id_layanan'],
                'id_jenis_layanan' => $jenis->id_jenis_layanan,
                'id_parfum'        => $validated['parfum'] ?? null,
                'harga'            => $jenis->harga, // Pastikan harga tersimpan
                'qty'              => $validated['qty'],
                'id_satuan'        => $jenis->satuan->id_satuan ?? null,
            ]);

            // Hitung ulang total harga dengan COALESCE
            $transaksi->total_harga = DB::table('detail_transaksi as d')
                ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
                ->where('d.id_transaksi', $id)
                ->sum(DB::raw('d.qty * COALESCE(NULLIF(d.harga, 0), j.harga, 0)'));
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

    // =============================
    // KASIR - HALAMAN TAMBAH LAYANAN
    // =============================
    public function addLayananPageKasir($id)
    {
        requirePermission('riwayat', 'edit');

        $riwayat      = Transaksi::findOrFail($id);
        $layananUtama = Layanan::with('jenis.satuan')->get();
        $parfum       = Parfum::all();

        $jenisLayananData = [];
        foreach ($layananUtama as $layanan) {
            $jenisLayananData[$layanan->id_layanan] = $layanan->jenis->map(function ($jenis) {
                return [
                    'id'     => $jenis->id_jenis_layanan,
                    'nama'   => $jenis->nama_jenis,
                    'harga'  => $jenis->harga,
                    'satuan' => $jenis->satuan->nama_satuan ?? ''
                ];
            })->toArray();
        }

        return view('kasir.riwayat.addlayanan', compact('riwayat', 'layananUtama', 'parfum', 'jenisLayananData'));
    }

    // =============================
    // KASIR - HAPUS TRANSAKSI
    // =============================
    public function destroyKasir($id)
    {
        requirePermission('riwayat', 'delete');

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->detail()->delete();
        $transaksi->delete();

        return redirect()->route('kasir.riwayat.index')->with('success', 'Transaksi berhasil dihapus');
    }

    // ============================================================
    // =================== ADMIN2 METHODS ========================
    // ============================================================

    // =============================
    // ADMIN2 - TAMPILKAN RIWAYAT TRANSAKSI
    // =============================
    public function indexAdmin2(Request $request)
    {
        requirePermission('riwayat', 'view');

        $tab = $request->tab ?? 'antrian';

        $riwayat = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'offline')
            ->orderBy('id_transaksi', 'DESC');

        if (in_array($tab, ['antrian', 'proses', 'siap_di_ambil', 'selesai', 'batal'])) {
            $riwayat = $riwayat->where('status_transaksi', $tab);
        }

        $riwayat = $riwayat->paginate(10);

        return view('admin2.riwayat.index', compact('riwayat', 'tab'));
    }

    // =============================
    // ADMIN2 - DETAIL TRANSAKSI
    // =============================
    public function detailAdmin2($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404, 'Transaksi tidak ditemukan');

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)['nama_pelanggan' => $transaksi->nama_pelanggan, 'no_hp' => $transaksi->no_hp, 'gambar' => null];

        // FIX: gunakan queryDetail helper
        $detail = $this->queryDetail($id);

        // FIX: subtotal dengan fallback ke total_harga transaksi
        $subtotal  = $detail->sum('total_harga');
        if ($subtotal <= 0) {
            $subtotal = $transaksi->total_harga ?? 0;
        }

        $dp        = $transaksi->total_bayar ?? 0;
        $diskon    = $transaksi->diskon ?? 0;
        $sisaBayar = max(0, $subtotal - $diskon - $dp);

        return view('admin2.riwayat.detail', compact(
            'transaksi', 'pelanggan', 'detail', 'subtotal', 'dp', 'diskon', 'sisaBayar'
        ));
    }

    // =============================
    // ADMIN2 - BAYAR SUBMIT (delegasi ke method admin)
    // =============================
    public function bayarSubmitAdmin2(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        return $this->bayarSubmit($request, $id);
    }

    // =============================
    // ADMIN2 - BAYAR (delegasi ke method kasir)
    // =============================
    public function bayarAdmin2(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');
        return $this->bayarKasir($request, $id);
    }

    // =============================
    // ADMIN2 - SHOW (alias ke detailAdmin2)
    // =============================
    public function showAdmin2($id)
    {
        requirePermission('riwayat', 'view');
        return $this->detailAdmin2($id);
    }

    // =============================
    // ADMIN2 - FORM EDIT TRANSAKSI
    // =============================
    public function editAdmin2($id)
    {
        requirePermission('riwayat', 'edit');

        $riwayat   = Transaksi::with(['detail.jenis.satuan', 'detail.jenis.layanan', 'detail.parfum', 'pelanggan'])->findOrFail($id);
        $detail    = $riwayat->detail;
        $pelanggan = $riwayat->pelanggan;
        $parfum    = Parfum::all();

        return view('admin2.riwayat.edit', compact('riwayat', 'detail', 'pelanggan', 'parfum'));
    }

    // =============================
    // ADMIN2 - UPDATE TRANSAKSI
    // =============================
    public function updateAdmin2(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        $request->validate([
            'detail'             => 'required|array',
            'detail.*.qty'       => 'required|numeric|min:0.01',
            'detail.*.id_parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        $riwayat = Transaksi::findOrFail($id);

        foreach ($request->detail as $id_detail => $d) {
            $detail = DetailTransaksi::find($id_detail);
            if ($detail) {
                $detail->update([
                    'qty'       => $d['qty'],
                    'id_parfum' => $d['id_parfum'] ?? null,
                    'harga'     => $detail->harga
                ]);
            }
        }

        // Hitung ulang total harga dengan COALESCE
        $total = DB::table('detail_transaksi as d')
            ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
            ->where('d.id_transaksi', $id)
            ->sum(DB::raw('d.qty * COALESCE(NULLIF(d.harga, 0), j.harga, 0)'));

        $riwayat->update(['total_harga' => $total]);

        return redirect()->route('admin2.riwayat.detail', ['id' => $id])
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    // =============================
    // ADMIN2 - PROSES ORDER
    // =============================
    public function prosesOrderAdmin2($id)
    {
        requirePermission('riwayat', 'edit');

        $trx = Transaksi::findOrFail($id);
        if ($trx->status_transaksi === 'antrian') {
            $trx->status_transaksi = 'proses';
            $trx->save();
        }

        return redirect()->route('admin2.riwayat.index', ['tab' => 'proses'])
            ->with('success', 'Transaksi berhasil diproses!');
    }

    // =============================
    // ADMIN2 - SELESAI ORDER
    // =============================
    public function selesaiOrderAdmin2($id)
    {
        requirePermission('riwayat', 'edit');

        $trx = Transaksi::findOrFail($id);
        if (in_array($trx->status_transaksi, ['proses', 'siap_di_ambil'])) {
            $trx->status_transaksi = 'selesai';
            $trx->save();
        }

        return redirect()->route('admin2.riwayat.index', ['tab' => 'selesai'])
            ->with('success', 'Transaksi berhasil diselesaikan!');
    }

    // =============================
    // ADMIN2 - SIAP DIAMBIL
    // =============================
    public function siapDiAmbilAdmin2($id)
    {
        requirePermission('riwayat', 'edit');

        $trx                   = Transaksi::findOrFail($id);
        $trx->status_transaksi = 'siap_di_ambil';
        $trx->save();

        return redirect()->route('admin2.riwayat.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Transaksi siap diambil!');
    }

    // =============================
    // ADMIN2 - BATAL ORDER
    // =============================
    public function batalOrderAdmin2($id)
    {
        requirePermission('riwayat', 'delete');

        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status_bayar === 'lunas') {
            return redirect()
                ->route('admin2.riwayat.index')
                ->with('error', 'Transaksi sudah lunas dan tidak bisa dibatalkan');
        }

        $transaksi->update(['status_transaksi' => 'batal']);

        return redirect()->route('admin2.riwayat.index', ['tab' => 'antrian'])
            ->with('success', 'Transaksi berhasil dibatalkan!');
    }

    // =============================
    // ADMIN2 - MODAL BAYAR
    // =============================
    public function bayarModalAdmin2($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi = Transaksi::with(['pelanggan', 'detail.jenis.satuan', 'detail.parfum'])->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail    = $transaksi->detail;

        // FIX: subtotal dengan COALESCE fallback
        $subtotal  = $detail->sum(function ($d) {
            $harga = $d->harga > 0 ? $d->harga : ($d->jenis->harga ?? 0);
            return $d->qty * $harga;
        });
        if ($subtotal <= 0) {
            $subtotal = $transaksi->total_harga ?? 0;
        }

        $diskon    = $transaksi->diskon ?? 0;
        $dp        = $transaksi->total_bayar ?? 0;
        $sisaBayar = max(0, $subtotal - $diskon - $dp);

        return view('admin2.riwayat.modal_bayar', compact(
            'transaksi', 'pelanggan', 'detail', 'subtotal', 'dp', 'sisaBayar', 'diskon'
        ));
    }

    // =============================
    // ADMIN2 - ACTIONS
    // =============================
    public function actionsAdmin2($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi = Transaksi::with(['pelanggan', 'detail.jenis.satuan', 'detail.parfum'])->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail    = $transaksi->detail;

        // FIX: subtotal dengan COALESCE fallback
        $subtotal = $detail->sum(function ($d) {
            $harga = $d->harga > 0 ? $d->harga : ($d->jenis->harga ?? 0);
            return $d->qty * $harga;
        });
        if ($subtotal <= 0) {
            $subtotal = $transaksi->total_harga ?? 0;
        }

        $diskon      = $transaksi->diskon ?? 0;
        $dp          = $transaksi->total_bayar ?? 0;
        $sisaBayar   = max(0, $subtotal - $diskon - $dp);
        $statusBayar = strtolower($transaksi->status_bayar ?? 'belum bayar');

        return view('admin2.riwayat.detail', compact(
            'transaksi', 'pelanggan', 'detail', 'subtotal', 'dp', 'sisaBayar', 'diskon', 'statusBayar'
        ));
    }

    // =============================
    // ADMIN2 - ADD LAYANAN (langsung ke database)
    // =============================
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
                'harga'            => $jenis->harga, // Pastikan harga tersimpan
            ]);

            // Hitung ulang total harga dengan COALESCE
            $transaksi              = Transaksi::findOrFail($id);
            $transaksi->total_harga = DB::table('detail_transaksi as d')
                ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
                ->where('d.id_transaksi', $id)
                ->sum(DB::raw('d.qty * COALESCE(NULLIF(d.harga, 0), j.harga, 0)'));
            $transaksi->save();

            return response()->json(['success' => true, 'message' => 'Layanan berhasil ditambahkan']);
        } catch (\Exception $e) {
            \Log::error('Error addLayananAdmin2: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // =============================
    // ADMIN2 - HALAMAN TAMBAH LAYANAN
    // =============================
    public function addLayananPageAdmin2($id)
    {
        requirePermission('riwayat', 'view');

        $riwayat      = Transaksi::findOrFail($id);
        $layananUtama = Layanan::with('jenis.satuan')->get();
        $parfum       = Parfum::all();

        $jenisLayananData = [];
        foreach ($layananUtama as $layanan) {
            $jenisLayananData[$layanan->id_layanan] = $layanan->jenis->map(function ($jenis) {
                return [
                    'id'     => $jenis->id_jenis_layanan,
                    'nama'   => $jenis->nama_jenis,
                    'harga'  => $jenis->harga,
                    'satuan' => $jenis->satuan->nama_satuan ?? ''
                ];
            })->toArray();
        }

        return view('admin2.riwayat.addlayanan', compact('riwayat', 'layananUtama', 'parfum', 'jenisLayananData'));
    }

    // =============================
    // ADMIN2 - DELETE DETAIL TRANSAKSI
    // =============================
    public function deleteDetailAdmin2($id)
    {
        requirePermission('riwayat', 'delete');

        try {
            $detail      = DetailTransaksi::findOrFail($id);
            $idTransaksi = $detail->id_transaksi;
            $detail->delete();

            // Hitung ulang total harga dengan COALESCE
            $transaksi              = Transaksi::findOrFail($idTransaksi);
            $transaksi->total_harga = DB::table('detail_transaksi as d')
                ->leftJoin('jenis_layanan as j', 'j.id_jenis_layanan', '=', 'd.id_jenis_layanan')
                ->where('d.id_transaksi', $idTransaksi)
                ->sum(DB::raw('d.qty * COALESCE(NULLIF(d.harga, 0), j.harga, 0)'));
            $transaksi->save();

            return response()->json(['success' => true, 'message' => 'Layanan berhasil dihapus']);
        } catch (\Exception $e) {
            \Log::error('Error deleteDetailAdmin2: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // =============================
    // ADMIN2 - HAPUS TRANSAKSI
    // =============================
    public function destroyAdmin2($id)
    {
        requirePermission('riwayat', 'delete');

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->detail()->delete();
        $transaksi->delete();

        return redirect()->route('admin2.riwayat.index')->with('success', 'Transaksi berhasil dihapus');
    }
}