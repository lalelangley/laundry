<?php

    namespace App\Http\Controllers\Web;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Models\Pelanggan;
    use App\Models\Layanan;
    use App\Models\Transaksi;
    use App\Models\MetodeBayar;
    use App\Models\Parfum;
    use App\Http\Controllers\Web\SatuanParfumController;

    class TransaksiController extends Controller
    {
        // ==========================
        // 1. HALAMAN AWAL TRANSAKSI
        // ==========================
        public function index(Request $request)
        {
            if (!$request->has('keep')) {
                session()->forget('detail_transaksi');
            }

            return view('admin.transaksi.index', [
                'pelanggan' => session('pelanggan'),
                'detail'    => session('detail_transaksi', []),
            ]);
        }

        // ==========================
        // 2. HALAMAN CREATE CHECKOUT
        // ==========================
        public function create()
        {
            $pelanggan = session('pelanggan');
            $detail    = session('detail_transaksi', []);
            $keterangan = session('keterangan_transaksi');

            $total = 0;
            foreach ($detail as $d) {
                $total += $d['harga'] * $d['qty'];
            }

            return view('transaksi.create', compact('pelanggan','detail','total','keterangan'));
        }


        // ==========================
        // 3. PILIH PELANGGAN
        // ==========================
        public function pilihPelanggan()
        {
            $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();
            return view('transaksi.pelanggan', compact('pelanggan'));
        }


        public function setPelanggan($id)
        {
            $p = Pelanggan::find($id);
            if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

            session([
                'pelanggan' => [
                    'id_pelanggan'   => $p->id_pelanggan,
                    'nama_pelanggan' => $p->nama_pelanggan,
                    'no_hp'          => $p->no_hp,
                ]
            ]);

            return redirect()->route('transaksi.create');
        }


        // ==========================
        // 4. TAMBAH LAYANAN KE KERANJANG
        // ==========================
        public function addLayanan(Request $request, $id)
        {
            $layanan = Layanan::with(['jenis.satuan'])->find($id);

            if (!$layanan) {
                return response()->json(['error' => 'Layanan tidak ditemukan'], 404);
            }

            // Ambil jenis layanan aktif
            $jenis = $layanan->jenis->first();
            if (!$jenis) {
                return response()->json(['error' => 'Jenis layanan tidak ditemukan'], 404);
            }

            // Ambil keranjang
            $cart = session()->get('detail_transaksi', []);

            // Tambahkan item baru
            $cart[] = [
                'id_layanan'   => $layanan->id_layanan,
                'id_jenis'     => $jenis->id_jenis,
                'nama_layanan' => $layanan->nama_layanan,
                'jenis'        => $jenis->nama_jenis,
                'satuan'       => $jenis->satuan->nama_satuan ?? '-',
                'qty'          => $request->qty ?? 1,
                'harga'        => $jenis->harga,
                'parfum'       => $request->parfum ?? null,
                'parfum_nama'  => $request->parfum_nama ?? null, // ← TAMBAHKAN INI
            ];

            session()->put('detail_transaksi', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil ditambahkan',
            ]);
        }


        // ==========================
        // 5. REMOVE LAYANAN
        // ==========================
        public function remove($id)
        {
            $cart = session('detail_transaksi', []);

            // remove item spesifik (bukan semua)
            $cart = array_values(array_filter($cart, function ($i) use ($id) {
                return $i['id_layanan'] != $id;
            }));

            session()->put('detail_transaksi', $cart);

            return back();
        }


    // ==========================
    // 6. HALAMAN CHECKOUT
    // ==========================
    public function checkout()
    {
        $pelanggan = session('pelanggan');
        $detail    = session('detail_transaksi', []);
        $metode_bayar = session('metode_bayar');  // atau ambil dari DB jika bukan session
        $totalHarga   = session('total_harga');   // atau hitung ulang
        $keterangan = session('keterangan_transaksi');

        if (!$pelanggan) {
            return redirect()->route('transaksi.pelanggan')
                ->with('error', 'Silakan pilih pelanggan terlebih dahulu.');
        }

        $totalHarga = 0;
        foreach ($detail as $d) {
            $totalHarga += $d['harga'] * $d['qty'];
        }

        $metode_bayar = MetodeBayar::all();

        return view('transaksi.checkout', compact(
            'pelanggan',
            'detail',
            'totalHarga',
            'metode_bayar',
            'keterangan'
        ));
    }

        // ==========================
        // 7. BAYAR & SIMPAN TRANSAKSI
        // ==========================
       public function bayar(Request $request)
{
    $pelanggan = session('pelanggan');
    $detail    = session('detail_transaksi', []);

    if (!$pelanggan || empty($detail)) {
        return response()->json(['error' => 'Transaksi tidak valid'], 400);
    }

    // Hitung total awal
    $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

    // Hitung diskon
    $diskon = $request->diskon ?? 0;
    if ($request->tipe_diskon == 'percent') {
        $diskon = $totalAwal * ($diskon / 100);
    }

    $totalAkhir = max($totalAwal - $diskon, 0);

    // LOGIKA BAYAR
    $langsung = $request->langsung_bayar == 1;

    if ($langsung) {
        $jumlahBayar = $totalAkhir;
        $statusBayar = 1;
        $tglLunas    = now();
    } else {
        $jumlahBayar = $request->jumlah_bayar ?? 0;
        $statusBayar = $jumlahBayar >= $totalAkhir ? 1 : 0;
        $tglLunas    = $statusBayar ? now() : null;
    }

    // SIMPAN TRANSAKSI
    $trans = Transaksi::create([
        'id_pelanggan'     => $pelanggan['id_pelanggan'],
        'nama_pelanggan'   => $pelanggan['nama_pelanggan'],
        'no_hp'            => $pelanggan['no_hp'],
        'total_harga'      => $totalAwal,
        'total_bayar'      => $jumlahBayar,
        'diskon'           => $diskon,
        'tipe_diskon'      => $request->tipe_diskon,
        'status_bayar'     => $statusBayar,
        'status_transaksi' => 0,
        'keterangan'       => $request->keterangan ?? '',
        'tgl_transaksi'    => now(),
        'tgl_estimasi'     => $request->tgl_estimasi,
        'tgl_lunas'        => $tglLunas,
        'id_kasir'         => auth()->id(),
        'id_metode_bayar'  => $request->id_metode_bayar,
    ]);

    // SIMPAN DETAIL
    foreach ($detail as $d) {

        $namaParfum = null;
        if (!empty($d['parfum'])) {
            $parfumDb = Parfum::find($d['parfum']);
            $namaParfum = $parfumDb->nama_satuan ?? null;
        }

        $trans->detail()->create([
            'id_layanan'   => $d['id_layanan'],
            'id_jenis'     => $d['id_jenis'],
            'nama_layanan' => $d['nama_layanan'],
            'nama_jenis'   => $d['jenis'],
            'nama_parfum'  => $namaParfum,
            'harga'        => $d['harga'],
            'qty'          => $d['qty'],
            'total_harga'  => $d['harga'] * $d['qty'],
            'satuan'       => $d['satuan'],
            'keterangan'   => $request->keterangan ?? '', // ⬅ FIX PALING PENTING
        ]);
    }

    // Clear session
    session()->forget(['pelanggan', 'detail_transaksi', 'keterangan_transaksi']);

    return response()->json([
        'success' => true,
        'total'   => $totalAkhir,
        'bayar'   => $jumlahBayar,
        'nama'    => $pelanggan['nama_pelanggan'],
        'hp'      => $pelanggan['no_hp'],
        'status_bayar' => $statusBayar,
        'diskon'  => $diskon,
        'total_bayar' => $jumlahBayar,
        'tgl_lunas' => $tglLunas,
    ]);
}


    // ==========================
    // 8. PRINT STRUK
    // ==========================
    public function print($id)
    {
        $transaksi = Transaksi::with('pelanggan', 'detail')->findOrFail($id);
        return view('transaksi.print', compact('transaksi'));
    }

        public function riwayat()
    {
        $data = Transaksi::with('detail')->orderBy('id_transaksi', 'DESC')->get();
        return view('admin.transaksi.riwayat', compact('data'));
    }
        public function updateKeterangan(Request $request)
    {
        session(['keterangan_transaksi' => $request->keterangan]);
        return response()->json(['success' => true]);
    }

}
