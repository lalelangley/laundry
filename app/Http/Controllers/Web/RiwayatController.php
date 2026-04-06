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
    // Mengambil nama kasir berdasarkan ID dari tabel admin atau kasir
    // =============================
    private function getNamaKasir($idKasir)
    {
        // Jika tidak ada ID kasir, kembalikan 'System'
        if (!$idKasir) return 'System';

        // Cek di tabel admin terlebih dahulu
        $admin = \App\Models\Admin::find($idKasir);
        if ($admin) return $admin->nama;

        // Jika tidak ditemukan di admin, cek di tabel kasir
        $kasir = \App\Models\Kasir::find($idKasir);
        if ($kasir) return $kasir->nama_kasir;

        // Jika tidak ditemukan di kedua tabel
        return 'Unknown';
    }

    // =============================
    // HELPER METHOD - GET DATA UNTUK CETAK NOTA
    // Mengambil data transaksi, pelanggan, dan detail layanan untuk keperluan cetak nota
    // =============================
    private function getDataCetakNota($id)
    {
        // Ambil data transaksi beserta metode pembayaran dengan JOIN
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        // Jika transaksi tidak ditemukan, tampilkan halaman 404
        if (!$transaksi) abort(404, 'Transaksi tidak ditemukan');

        // Tambahkan nama kasir ke objek transaksi
        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        // Ambil data pelanggan; jika tidak ada relasi pelanggan, gunakan data inline dari transaksi
        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)[
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'no_hp'          => $transaksi->no_hp,
                'gambar'         => null,
            ];

        // Ambil detail layanan dari transaksi dengan JOIN ke jenis_layanan, layanan, dan satuan
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
                DB::raw('(d.qty * d.harga) AS total_harga')  // Hitung total per item
            )
            ->where('d.id_transaksi', $id)
            ->get();

        return compact('transaksi', 'pelanggan', 'detail');
    }

    // =============================
    // HELPER METHOD - VALIDASI STATUS SEBELUM UBAH KE SIAP DIAMBIL
    // Memastikan pembayaran sudah lunas sebelum status diubah ke siap_di_ambil
    // =============================
    private function validateDPBeforeStatusChange($transaksi, $newStatus)
    {
        // Cek apakah pembayaran masih DP saat ingin ubah ke siap_di_ambil
        if ($newStatus === 'siap_di_ambil') {
            if ($transaksi->status_bayar === 'DP') {
                return [
                    'valid'            => false,
                    'message'          => '⚠️ Pembayaran masih DP! Silakan lunasi pembayaran terlebih dahulu.',
                    'sisa_pembayaran'  => $transaksi->total_harga - $transaksi->total_bayar
                ];
            }
            // Cek apakah belum ada pembayaran sama sekali
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
    // Menampilkan halaman cetak nota untuk role Admin
    // =============================
    public function cetakNota($id)
    {
        // Periksa permission view riwayat
        requirePermission('riwayat', 'view');
        $data = $this->getDataCetakNota($id);
        return view('riwayat.cetaknota', $data);
    }

    // =============================
    // CETAK NOTA - KASIR
    // Menampilkan halaman cetak nota untuk role Kasir
    // =============================
    public function cetakNotaKasir($id)
    {
        requirePermission('riwayat', 'view');
        $data = $this->getDataCetakNota($id);
        return view('riwayat.cetaknota', $data);
    }

    // =============================
    // CETAK NOTA - ADMIN2
    // Menampilkan halaman cetak nota untuk role Admin2
    // =============================
    public function cetakNotaAdmin2($id)
    {
        requirePermission('riwayat', 'view');
        $data = $this->getDataCetakNota($id);
        return view('riwayat.cetaknota', $data);
    }

    // =============================
    // CETAK NOTA HTML - ADMIN
    // Menampilkan nota dalam format HTML (untuk preview/print langsung)
    // =============================
    public function notaHtml($id)
    {
        $data = $this->getDataCetakNota($id);
        return view('riwayat.cetaknota', $data);
    }

    // =============================
    // ADMIN - TAMPILKAN RIWAYAT TRANSAKSI
    // Menampilkan daftar transaksi offline berdasarkan tab status dengan paging
    // =============================
    public function index(Request $request)
    {
        // Periksa permission view riwayat
        requirePermission('riwayat', 'view');

        // Ambil tab aktif dari query string, default ke 'antrian'
        $tab = $request->get('tab', 'antrian');

        // Daftar tab/status yang diizinkan
        $allowedTabs = [
            'antrian',
            'proses',
            'siap_di_ambil',
            'pick_up',
            'siap_di_antar',
            'selesai',
            'batal'
        ];

        // Reset ke 'antrian' jika tab tidak valid
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'antrian';
        }

        // Ambil data transaksi dengan relasi pelanggan, filter berdasarkan status dan jenis offline
        // Gunakan paginate() untuk fitur paging antar halaman
        $riwayat = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'offline')
            ->where('status_transaksi', $tab)
            ->orderBy('id_transaksi', 'DESC')
            ->paginate(10);  // Tampilkan 10 data per halaman

        return view('riwayat.index', compact('riwayat', 'tab'));
    }

    // =============================
    // ADMIN - DETAIL TRANSAKSI
    // Menampilkan halaman detail satu transaksi beserta rincian layanan dan pembayaran
    // =============================
    public function detail($id)
    {
        // Periksa permission view riwayat
        requirePermission('riwayat', 'view');

        // Ambil transaksi dengan JOIN ke metode_bayar
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        // Tampilkan 404 jika transaksi tidak ditemukan
        if (!$transaksi) abort(404, 'Transaksi tidak ditemukan');

        // Tambahkan nama kasir ke data transaksi
        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        // Ambil data pelanggan, fallback ke data inline jika tidak ada relasi
        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)['nama_pelanggan' => $transaksi->nama_pelanggan, 'no_hp' => $transaksi->no_hp, 'gambar' => null];

        // Ambil detail layanan dengan JOIN ke jenis_layanan, layanan, satuan
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
                DB::raw('(d.qty * d.harga) AS total_harga')  // Hitung total harga per item
            )
            ->where('d.id_transaksi', $id)
            ->get();

        // Hitung subtotal dari semua item detail
        $subtotal = $detail->sum('total_harga');

        return view('riwayat.detail', compact('transaksi', 'pelanggan', 'detail', 'subtotal'));
    }

    // =============================
    // ADMIN - FORM EDIT DATA TRANSAKSI
    // Menampilkan form edit untuk mengubah qty dan parfum pada detail transaksi
    // =============================
    public function edit($id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        // Ambil transaksi beserta relasi detail, jenis layanan, satuan, parfum, dan pelanggan
        $riwayat   = Transaksi::with(['detail.jenis.satuan', 'detail.parfum', 'pelanggan'])->findOrFail($id);
        $detail    = $riwayat->detail;
        $pelanggan = $riwayat->pelanggan;
        $parfum    = Parfum::all();  // Ambil semua pilihan parfum

        return view('riwayat.edit', compact('riwayat', 'detail', 'pelanggan', 'parfum'));
    }

    // =============================
    // ADMIN - UPDATE DATA TRANSAKSI
    // Menyimpan perubahan qty dan parfum pada detail transaksi, lalu hitung ulang total harga
    // =============================
    public function update(Request $request, $id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        // Validasi: detail harus berupa array
        $request->validate([
            'detail'             => 'required|array',
            'detail.*.qty'       => 'required|numeric|min:0.01',
            'detail.*.id_parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        $riwayat = Transaksi::findOrFail($id);

        // Iterasi setiap item detail dan perbarui qty serta parfum
        foreach ($request->detail as $id_detail => $d) {
            $detail = DetailTransaksi::find($id_detail);
            if ($detail) {
                $detail->update([
                    'qty'       => $d['qty'],
                    'id_parfum' => $d['id_parfum'] ?? null,
                    'harga'     => $detail->harga,  // Harga tidak diubah
                ]);
            }
        }

        // Hitung ulang total harga transaksi berdasarkan detail yang diperbarui
        $total = DetailTransaksi::where('id_transaksi', $id)
            ->sum(DB::raw('qty * harga'));
        $riwayat->update(['total_harga' => $total]);

        return redirect()->route('riwayat.detail', ['id' => $id])
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    // =============================
    // ADMIN - UPDATE STATUS TRANSAKSI
    // Mengubah status transaksi melalui form/dropdown status
    // =============================
    public function updateStatus(Request $request, $id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        // Validasi: status_transaksi wajib diisi dan harus salah satu nilai yang valid
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
    // Menghapus transaksi secara permanen dari sistem
    // =============================
    public function destroy($id)
    {
        // Periksa permission delete riwayat
        requirePermission('riwayat', 'delete');

        // Cari transaksi; tampilkan 404 jika tidak ditemukan
        Transaksi::findOrFail($id)->delete();

        return redirect()
            ->route('riwayat.index')
            ->with('success', 'Riwayat transaksi berhasil dihapus');
    }

    // =============================
    // ADMIN - PROSES ORDER
    // Mengubah status transaksi dari 'antrian' ke 'proses'
    // =============================
    public function prosesOrder($id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        $trx = Transaksi::findOrFail($id);

        // Hanya ubah status jika saat ini masih 'antrian'
        if ($trx->status_transaksi === 'antrian') {
            $trx->status_transaksi = 'proses';
            $trx->save();
        }

        return redirect()->route('riwayat.index', ['tab' => 'proses'])
            ->with('success', 'Transaksi berhasil diproses!');
    }

    // =============================
    // ADMIN - SELESAI ORDER
    // Mengubah status transaksi dari 'proses' atau 'siap_di_ambil' ke 'selesai'
    // =============================
    public function selesaiOrder($id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        $trx = Transaksi::findOrFail($id);

        // Hanya ubah ke 'selesai' jika status sebelumnya adalah 'proses' atau 'siap_di_ambil'
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
    // Mengubah status transaksi menjadi 'siap_di_ambil'
    // =============================
    public function siapDiAmbil($id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        $trx                       = Transaksi::findOrFail($id);
        $trx->status_transaksi     = 'siap_di_ambil';
        $trx->save();

        return redirect()->route('riwayat.index', ['tab' => 'siap_di_ambil'])
            ->with('success', 'Transaksi siap diambil!');
    }

    // =============================
    // ADMIN - BATAL ORDER
    // Membatalkan transaksi; tidak bisa dibatalkan jika sudah lunas atau sudah selesai
    // =============================
    public function batalOrder($id)
    {
        // Periksa permission delete riwayat
        requirePermission('riwayat', 'delete');

        $transaksi = Transaksi::findOrFail($id);

        // Jika transaksi sudah berstatus 'batal', kembalikan dengan pesan info
        if ($transaksi->status_transaksi === 'batal') {
            return redirect()
                ->route('riwayat.index', ['tab' => 'batal'])
                ->with('info', 'Transaksi ini sudah dibatalkan');
        }

        // Transaksi yang sudah lunas atau selesai tidak dapat dibatalkan
        if (
            $transaksi->status_bayar === 'lunas' ||
            $transaksi->status_transaksi === 'selesai'
        ) {
            return redirect()
                ->route('riwayat.index')
                ->with('error', 'Transaksi tidak bisa dibatalkan karena sudah lunas atau selesai');
        }

        // Ubah status transaksi menjadi 'batal'
        $transaksi->update(['status_transaksi' => 'batal']);

        return redirect()
            ->route('riwayat.index', ['tab' => 'batal'])
            ->with('success', 'Transaksi berhasil dibatalkan');
    }

    // =============================
    // ADMIN - BAYAR SUBMIT
    // Memproses pembayaran (DP atau lunas) dan memperbarui status pembayaran
    // =============================
    public function bayarSubmit(Request $request, $id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        $trs = Transaksi::findOrFail($id);

        // Validasi: jumlah_bayar wajib diisi dan harus angka positif
        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1'
        ]);

        // Kalkulasi total bayar baru
        $jumlahBayarBaru = (float) $request->jumlah_bayar;
        $totalBayarLama  = (float) ($trs->total_bayar ?? 0);
        $subtotal        = (float) $trs->total_harga;
        $diskon          = (float) ($trs->diskon ?? 0);
        $totalTagihan    = $subtotal - $diskon;
        $totalBayarBaru  = $totalBayarLama + $jumlahBayarBaru;

        // Perbarui nilai DP jika belum melebihi total tagihan
        $trs->dp         = $totalBayarBaru < $totalTagihan
            ? $totalBayarBaru
            : ($trs->dp ?? $totalBayarBaru);
        $trs->total_bayar = $totalBayarBaru;

        // Hitung sisa tagihan setelah pembayaran baru
        $sisaTagihan = $totalTagihan - $totalBayarBaru;

        // Tentukan status pembayaran berdasarkan sisa tagihan
        if ($sisaTagihan <= 0) {
            // Pembayaran lunas
            $trs->status_bayar = 'lunas';
            $trs->tgl_lunas    = now();
        } elseif ($totalBayarBaru > 0) {
            // Baru sebagian (DP)
            $trs->status_bayar = 'DP';
            $trs->tgl_lunas    = null;
        } else {
            // Belum ada pembayaran
            $trs->status_bayar = 'belum bayar';
            $trs->tgl_lunas    = null;
        }

        $trs->save();

        // Jika request dari AJAX atau JSON, kembalikan response JSON
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
    // Menampilkan form edit untuk satu item detail layanan tertentu
    // =============================
    public function editLayanan($id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        // Ambil detail transaksi beserta relasi layanan, jenis, satuan, dan transaksi induk
        $detail  = DetailTransaksi::with(['layanan', 'jenis.satuan', 'transaksi.pelanggan'])->findOrFail($id);
        $riwayat = $detail->transaksi;

        // Tampilkan 404 jika transaksi induk tidak ditemukan
        if (!$riwayat) {
            abort(404, 'Transaksi tidak ditemukan');
        }

        // Gunakan data pelanggan dari relasi, fallback ke data inline transaksi
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
    // Menyimpan perubahan data layanan pada detail transaksi
    // =============================
    public function updateLayanan(Request $request, $id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        $detail = DetailTransaksi::findOrFail($id);

        // Validasi input form edit layanan
        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'harga'      => 'required|numeric|min:0',
            'id_satuan'  => 'required|exists:satuan,id_satuan',
            'qty'        => 'required|numeric|min:1',
        ]);

        // Perbarui data detail transaksi
        $detail->update($request->only(['nama_jenis', 'harga', 'id_satuan', 'qty']));

        // Perbarui data layanan induk jika ada perubahan nama atau proses
        $layanan = $detail->layanan;
        if ($request->filled('nama_layanan') || $request->filled('proses')) {
            $layanan->update([
                'nama_layanan' => $request->nama_layanan ?? $layanan->nama_layanan,
                'proses'       => isset($request->proses)
                    ? implode(',', $request->proses)
                    : $layanan->proses,
            ]);
        }

        // Tambahkan jenis layanan baru yang tersimpan di session (jika ada)
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

        // Hapus data session jenis baru setelah disimpan
        session()->forget("jenis_baru_{$layanan->id_layanan}");

        return redirect()->route('riwayat.edit', $detail->id_transaksi)
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    // =============================
    // ADMIN - TAMBAH LAYANAN KE RIWAYAT (via session)
    // Menyimpan data layanan baru ke session sebelum disimpan permanen
    // =============================
    public function storeLayanan(Request $request, $id)
    {
        // Periksa permission add riwayat
        requirePermission('riwayat', 'add');

        // Validasi input tambah layanan
        $request->validate([
            'id_layanan' => 'required|exists:layanan,id_layanan',
            'qty'        => 'required|numeric|min:0.01',
            'parfum'     => 'nullable|exists:parfum,id_parfum',
        ]);

        $layanan = Layanan::with('jenis')->findOrFail($request->id_layanan);
        $jenis   = $layanan->jenis->first();

        // Pastikan layanan memiliki jenis yang terdaftar
        if (!$jenis) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis layanan tidak ditemukan'
            ], 422);
        }

        // Simpan ke session sementara sebelum form disubmit
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
    // Menambahkan layanan baru langsung ke tabel detail_transaksi dan perbarui total harga
    // =============================
    public function addLayanan(Request $request, $id)
    {
        // Periksa permission add riwayat
        requirePermission('riwayat', 'add');

        try {
            // Validasi input sebelum menyimpan ke database
            $request->validate([
                'id_layanan'       => 'required|exists:layanan,id_layanan',
                'id_jenis_layanan' => 'required|exists:jenis_layanan,id_jenis_layanan',
                'qty'              => 'required|numeric|min:0.01',
                'parfum'           => 'nullable|exists:parfum,id_parfum',
            ]);

            $jenis = JenisLayanan::findOrFail($request->id_jenis_layanan);

            // Simpan detail layanan baru ke database
            DetailTransaksi::create([
                'id_transaksi'     => $id,
                'id_layanan'       => $request->id_layanan,
                'id_jenis_layanan' => $jenis->id_jenis_layanan,
                'qty'              => $request->qty,
                'id_parfum'        => $request->parfum,
                'harga'            => $jenis->harga,
            ]);

            // Hitung ulang total harga transaksi
            $transaksi              = Transaksi::findOrFail($id);
            $transaksi->total_harga = DetailTransaksi::where('id_transaksi', $id)
                ->sum(DB::raw('qty * harga'));
            $transaksi->save();

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            // Catat error ke log untuk debugging
            \Log::error('Error addLayanan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================
    // ADMIN - HALAMAN TAMBAH LAYANAN
    // Menampilkan halaman form untuk menambahkan layanan baru ke transaksi
    // =============================
    public function addLayananPage($id)
    {
        // Periksa permission view riwayat
        requirePermission('riwayat', 'view');

        $riwayat      = Transaksi::findOrFail($id);
        $layananUtama = Layanan::with('jenis.satuan')->get();
        $parfum       = Parfum::all();

        // Susun data jenis layanan per layanan untuk keperluan dropdown dinamis di view
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
    // Memperbarui qty dan parfum pada satu item detail transaksi via AJAX
    // =============================
    public function updateDetail(Request $request, $id)
    {
        // Periksa permission edit riwayat
        requirePermission('riwayat', 'edit');

        // Validasi input update detail
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
    // Menghapus satu item layanan dari detail transaksi
    // =============================
    public function deleteDetail($id)
    {
        // Periksa permission delete riwayat
        requirePermission('riwayat', 'delete');

        // Cari detail; kembalikan error jika tidak ditemukan
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
    // Menampilkan daftar transaksi untuk role Kasir dengan paging
    // =============================
    public function indexKasir(Request $request)
    {
        // Periksa permission view riwayat
        requirePermission('riwayat', 'view');

        $tab = $request->tab ?? 'antrian';

        // Ambil data transaksi offline dengan relasi pelanggan dan paging
        $riwayat = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'offline')
            ->orderBy('id_transaksi', 'DESC');

        // Filter berdasarkan status tab yang valid
        if (in_array($tab, ['antrian', 'proses', 'siap_di_ambil', 'selesai', 'batal'])) {
            $riwayat = $riwayat->where('status_transaksi', $tab);
        }

        // Terapkan paging 10 data per halaman
        $riwayat = $riwayat->paginate(10);

        return view('kasir.riwayat.index', compact('riwayat', 'tab'));
    }

    // =============================
    // KASIR - DETAIL TRANSAKSI
    // Menampilkan halaman detail transaksi untuk role Kasir
    // =============================
    public function detailKasir($id)
    {
        // Periksa permission view riwayat
        requirePermission('riwayat', 'view');

        // Ambil transaksi dengan JOIN ke metode_bayar
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('metode_bayar as mb', 'mb.id_metode_bayar', '=', 't.id_metode_bayar')
            ->select('t.*', 'mb.nama_metode_bayar')
            ->where('t.id_transaksi', $id)
            ->first();

        if (!$transaksi) abort(404, 'Transaksi tidak ditemukan');

        $transaksi->nama_kasir = $this->getNamaKasir($transaksi->id_kasir);

        // Ambil data pelanggan dengan fallback ke data inline
        $pelanggan = $transaksi->id_pelanggan
            ? DB::table('pelanggan')->where('id_pelanggan', $transaksi->id_pelanggan)->first()
            : (object)[
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'no_hp'          => $transaksi->no_hp,
                'gambar'         => null
            ];

        // Ambil detail layanan transaksi
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

        // Kalkulasi nilai pembayaran
        $subtotal  = $detail->sum('total_harga');
        $dp        = $transaksi->total_bayar ?? 0;
        $diskon    = $transaksi->diskon ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;  // Sisa yang belum dibayar

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
    // Memproses pembayaran dengan opsi diskon, metode bayar, dan pengecekan status
    // =============================
    public function bayarKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        $trs = Transaksi::findOrFail($id);

        // Validasi input form pembayaran kasir
        $request->validate([
            'jumlah_bayar'    => 'required|numeric|min:1',
            'diskon'          => 'nullable|numeric|min:0',
            'tipe_diskon'     => 'nullable|in:nominal,percent',
            'id_metode_bayar' => 'required|exists:metode_bayar,id_metode_bayar',
            'langsung_bayar'  => 'required|boolean'
        ]);

        $subtotal = $trs->total_harga;
        $diskon   = $request->diskon ?? 0;

        // Hitung diskon berdasarkan tipe (persen atau nominal)
        if ($request->tipe_diskon === 'percent') {
            $diskon = $subtotal * min($diskon, 100) / 100;
        }

        $totalTagihan = $subtotal - $diskon;
        $jumlahBayar  = $request->jumlah_bayar;

        // Set DP jika ini pembayaran pertama dan belum lunas
        if (is_null($trs->dp) && $jumlahBayar < $totalTagihan) {
            $trs->dp = $jumlahBayar;
        }

        // Akumulasi pembayaran, maksimal sebesar total tagihan
        $trs->total_bayar     = min(($trs->total_bayar ?? 0) + $jumlahBayar, $totalTagihan);
        $trs->diskon          = $diskon;
        $trs->id_metode_bayar = $request->id_metode_bayar;

        // Tentukan status pembayaran
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
    // Menyimpan perubahan qty dan parfum untuk role Kasir
    // =============================
    public function updateKasir(Request $request, $id)
    {
        requirePermission('riwayat', 'edit');

        // Validasi input sebelum update
        $request->validate([
            'detail'             => 'required|array',
            'detail.*.qty'       => 'required|numeric|min:0.01',
            'detail.*.id_parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        $riwayat = Transaksi::findOrFail($id);

        // Iterasi dan perbarui setiap item detail
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

        // Hitung ulang total harga
        $total = DetailTransaksi::where('id_transaksi', $id)->sum(DB::raw('qty * harga'));
        $riwayat->update(['total_harga' => $total]);

        return redirect()->route('kasir.riwayat.detail', ['id' => $id])
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    // =============================
    // KASIR - PROSES ORDER
    // Mengubah status dari 'antrian' ke 'proses' untuk role Kasir
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
    // Membatalkan transaksi; tidak bisa jika sudah lunas
    // =============================
    public function batalOrderKasir($id)
    {
        requirePermission('riwayat', 'delete');

        $transaksi = Transaksi::findOrFail($id);

        // Transaksi yang sudah lunas tidak dapat dibatalkan
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
    // Menampilkan modal pembayaran dengan kalkulasi subtotal, DP, dan sisa bayar
    // =============================
    public function bayarModalKasir($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi = Transaksi::with(['pelanggan', 'detail.jenis.satuan', 'detail.parfum'])->findOrFail($id);
        $pelanggan = $transaksi->pelanggan;
        $detail    = $transaksi->detail;

        // Hitung subtotal, DP, diskon, dan sisa bayar untuk ditampilkan di modal
        $subtotal  = $detail->sum(function ($d) { return $d->qty * $d->harga; });
        $diskon    = $transaksi->diskon ?? 0;
        $dp        = $transaksi->total_bayar ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;

        return view('kasir.riwayat.modal_bayar', compact(
            'transaksi', 'pelanggan', 'detail', 'subtotal', 'dp', 'sisaBayar', 'diskon'
        ));
    }

    // =============================
    // KASIR - ACTIONS (alias ke detailKasir dengan data tambahan)
    // =============================
    public function actionsKasir($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi   = Transaksi::with(['pelanggan', 'detail.jenis.satuan', 'detail.parfum'])->findOrFail($id);
        $pelanggan   = $transaksi->pelanggan;
        $detail      = $transaksi->detail;
        $subtotal    = $detail->sum(fn ($d) => $d->qty * $d->harga);
        $diskon      = $transaksi->diskon ?? 0;
        $dp          = $transaksi->total_bayar ?? 0;
        $sisaBayar   = $subtotal - $diskon - $dp;
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
            // Validasi input tambah layanan kasir
            $validated = $request->validate([
                'id_layanan'       => 'required|exists:layanan,id_layanan',
                'id_jenis_layanan' => 'required|exists:jenis_layanan,id_jenis_layanan',
                'qty'              => 'required|numeric|min:0.01',
                'parfum'           => 'nullable|exists:parfum,id_parfum',
            ]);

            $jenis     = JenisLayanan::with('satuan')->findOrFail($validated['id_jenis_layanan']);
            $transaksi = Transaksi::findOrFail($id);

            // Tambahkan detail layanan ke transaksi
            $detail = $transaksi->detail()->create([
                'id_layanan'       => $validated['id_layanan'],
                'id_jenis_layanan' => $jenis->id_jenis_layanan,
                'id_parfum'        => $validated['parfum'] ?? null,
                'harga'            => $jenis->harga,
                'qty'              => $validated['qty'],
                'id_satuan'        => $jenis->satuan->id_satuan ?? null,
            ]);

            // Hitung ulang total harga transaksi
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

    // =============================
    // KASIR - HALAMAN TAMBAH LAYANAN
    // =============================
    public function addLayananPageKasir($id)
    {
        requirePermission('riwayat', 'edit');

        $riwayat      = Transaksi::findOrFail($id);
        $layananUtama = Layanan::with('jenis.satuan')->get();
        $parfum       = Parfum::all();

        // Susun data jenis layanan untuk dropdown dinamis di view
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
    // Menghapus transaksi beserta seluruh detail layanannya
    // =============================
    public function destroyKasir($id)
    {
        requirePermission('riwayat', 'delete');

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->detail()->delete();  // Hapus semua detail terlebih dahulu
        $transaksi->delete();

        return redirect()->route('kasir.riwayat.index')->with('success', 'Transaksi berhasil dihapus');
    }

    // ============================================================
    // =================== ADMIN2 METHODS ========================
    // ============================================================

    // =============================
    // ADMIN2 - TAMPILKAN RIWAYAT TRANSAKSI
    // Menampilkan daftar transaksi untuk role Admin2 dengan paging
    // =============================
    public function indexAdmin2(Request $request)
    {
        requirePermission('riwayat', 'view');

        $tab = $request->tab ?? 'antrian';

        // Ambil dan filter transaksi offline berdasarkan tab status
        $riwayat = Transaksi::with('pelanggan')
            ->where('jenis_transaksi', 'offline')
            ->orderBy('id_transaksi', 'DESC');

        if (in_array($tab, ['antrian', 'proses', 'siap_di_ambil', 'selesai', 'batal'])) {
            $riwayat = $riwayat->where('status_transaksi', $tab);
        }

        // Terapkan paging 10 data per halaman
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

        // Kalkulasi ringkasan pembayaran
        $subtotal  = $detail->sum('total_harga');
        $dp        = $transaksi->total_bayar ?? 0;
        $diskon    = $transaksi->diskon ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;

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

        // Validasi input sebelum update
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

        // Hitung ulang total harga transaksi
        $total = DetailTransaksi::where('id_transaksi', $id)->sum(DB::raw('qty * harga'));
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
    // Membatalkan transaksi; tidak bisa jika sudah lunas
    // =============================
    public function batalOrderAdmin2($id)
    {
        requirePermission('riwayat', 'delete');

        $transaksi = Transaksi::findOrFail($id);

        // Tidak bisa membatalkan transaksi yang sudah lunas
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
        $subtotal  = $detail->sum(function ($d) { return $d->qty * $d->harga; });
        $diskon    = $transaksi->diskon ?? 0;
        $dp        = $transaksi->total_bayar ?? 0;
        $sisaBayar = $subtotal - $diskon - $dp;

        return view('admin2.riwayat.modal_bayar', compact(
            'transaksi', 'pelanggan', 'detail', 'subtotal', 'dp', 'sisaBayar', 'diskon'
        ));
    }

    // =============================
    // ADMIN2 - ACTIONS (alias ke detailAdmin2 dengan data tambahan)
    // =============================
    public function actionsAdmin2($id)
    {
        requirePermission('riwayat', 'view');

        $transaksi   = Transaksi::with(['pelanggan', 'detail.jenis.satuan', 'detail.parfum'])->findOrFail($id);
        $pelanggan   = $transaksi->pelanggan;
        $detail      = $transaksi->detail;
        $subtotal    = $detail->sum(fn ($d) => $d->qty * $d->harga);
        $diskon      = $transaksi->diskon ?? 0;
        $dp          = $transaksi->total_bayar ?? 0;
        $sisaBayar   = $subtotal - $diskon - $dp;
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
            // Validasi input tambah layanan
            $request->validate([
                'id_layanan'       => 'required|exists:layanan,id_layanan',
                'id_jenis_layanan' => 'required|exists:jenis_layanan,id_jenis_layanan',
                'qty'              => 'required|numeric|min:0.01',
                'parfum'           => 'nullable|exists:parfum,id_parfum',
            ]);

            $jenis = JenisLayanan::findOrFail($request->id_jenis_layanan);

            // Simpan detail layanan baru
            DetailTransaksi::create([
                'id_transaksi'     => $id,
                'id_layanan'       => $request->id_layanan,
                'id_jenis_layanan' => $jenis->id_jenis_layanan,
                'qty'              => $request->qty,
                'id_parfum'        => $request->parfum,
                'harga'            => $jenis->harga,
            ]);

            // Hitung ulang total harga transaksi
            $transaksi              = Transaksi::findOrFail($id);
            $transaksi->total_harga = DetailTransaksi::where('id_transaksi', $id)
                ->sum(DB::raw('qty * harga'));
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

        // Susun data jenis layanan per layanan untuk dropdown dinamis
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
    // Menghapus satu item layanan dari detail transaksi dan hitung ulang total
    // =============================
    public function deleteDetailAdmin2($id)
    {
        requirePermission('riwayat', 'delete');

        try {
            $detail      = DetailTransaksi::findOrFail($id);
            $idTransaksi = $detail->id_transaksi;
            $detail->delete();

            // Hitung ulang total harga setelah item dihapus
            $transaksi              = Transaksi::findOrFail($idTransaksi);
            $transaksi->total_harga = DetailTransaksi::where('id_transaksi', $idTransaksi)
                ->sum(DB::raw('qty * harga'));
            $transaksi->save();

            return response()->json(['success' => true, 'message' => 'Layanan berhasil dihapus']);
        } catch (\Exception $e) {
            \Log::error('Error deleteDetailAdmin2: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // =============================
    // ADMIN2 - HAPUS TRANSAKSI
    // Menghapus transaksi beserta seluruh detail layanannya
    // =============================
    public function destroyAdmin2($id)
    {
        requirePermission('riwayat', 'delete');

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->detail()->delete();  // Hapus semua detail terlebih dahulu
        $transaksi->delete();

        return redirect()->route('admin2.riwayat.index')->with('success', 'Transaksi berhasil dihapus');
    }
}