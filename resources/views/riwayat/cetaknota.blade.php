{{-- HANYA FRAGMENT - tanpa DOCTYPE, head, body --}}
@php
    $subtotal     = $detail->sum('total_harga');
    $diskon       = $transaksi->diskon ?? 0;
    $totalTagihan = $subtotal - $diskon;
    $dp           = $transaksi->total_bayar ?? 0;
    $sisaBayar    = $totalTagihan - $dp;

    if ($sisaBayar <= 0)    $statusBayar = 'LUNAS';
    elseif ($dp > 0)        $statusBayar = 'DP';
    else                    $statusBayar = 'BELUM BAYAR';
@endphp

<div class="nota-wrapper">
    <div class="header">
        <div class="store-name">🧺 Laundry Bersih</div>
        <div class="store-info">
            Jl. Contoh No. 123, Kota Anda<br>
            Telp: 08xx-xxxx-xxxx
        </div>
    </div>
    <div class="dashed"></div>
    <div class="nota-title">* NOTA TRANSAKSI *</div>
    <div class="dashed"></div>
    <div class="section">
        <div class="row"><span class="lbl">No Nota</span><span class="val">TRX/{{ $transaksi->id_transaksi }}</span></div>
        <div class="row"><span class="lbl">Tgl Masuk</span><span class="val">{{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d/m/Y') }}</span></div>
        @if($transaksi->tgl_estimasi)
        <div class="row"><span class="lbl">Estimasi</span><span class="val">{{ \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y') }}</span></div>
        @endif
        @if($transaksi->tgl_lunas)
        <div class="row"><span class="lbl">Tgl Lunas</span><span class="val">{{ \Carbon\Carbon::parse($transaksi->tgl_lunas)->format('d/m/Y') }}</span></div>
        @endif
        <div class="row"><span class="lbl">Kasir</span><span class="val">{{ $transaksi->nama_kasir ?? 'Admin' }}</span></div>
    </div>
    <div class="dashed"></div>
    <div class="section">
        <div class="row"><span class="lbl">Pelanggan</span><span class="val">{{ $pelanggan->nama_pelanggan }}</span></div>
        <div class="row"><span class="lbl">No. HP</span><span class="val">{{ $pelanggan->no_hp ?? '-' }}</span></div>
    </div>
    <div class="dashed"></div>
    <div class="section">
        <div class="items-header"><span>Layanan</span><span>Subtotal</span></div>
        @foreach($detail as $item)
        <div class="item">
            <div class="item-name">{{ $item->nama_jenis }}</div>
            <div class="item-sub">{{ $item->nama_layanan }}</div>
            <div class="item-hitung">
                <span>{{ $item->qty }} {{ $item->satuan }} x Rp {{ number_format($item->harga_jenis, 0, ',', '.') }}</span>
                <span class="bold">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
    </div>
    <div class="dashed"></div>
    <div class="section totals">
        <div class="row"><span class="lbl">Subtotal</span><span class="val">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
        @if($diskon > 0)
        <div class="row"><span class="lbl">Diskon</span><span class="val">- Rp {{ number_format($diskon, 0, ',', '.') }}</span></div>
        @endif
        <div class="grand-total"><span>TOTAL</span><span>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span></div>
        @if($dp > 0)
        <div class="row dp-row"><span class="lbl">DP Terbayar</span><span class="val">Rp {{ number_format($dp, 0, ',', '.') }}</span></div>
        <div class="row sisa-row"><span class="lbl">Sisa Bayar</span><span class="val">Rp {{ number_format(max($sisaBayar, 0), 0, ',', '.') }}</span></div>
        @endif
        @if($transaksi->nama_metode_bayar)
        <div class="row"><span class="lbl">Metode Bayar</span><span class="val">{{ $transaksi->nama_metode_bayar }}</span></div>
        @endif
    </div>
    <div class="status-badge">{{ $statusBayar }}</div>
    @if($transaksi->keterangan)
    <div class="dashed"></div>
    <div class="section"><div style="font-size:10px;">Catatan: {{ $transaksi->keterangan }}</div></div>
    @endif
    <div class="dashed"></div>
    <div class="footer">
        <div class="thank-you">*** Terima Kasih ***</div>
        <div>Simpan nota sebagai bukti pengambilan</div>
        <div>Barang tidak diambil >30 hari bukan</div>
        <div>tanggung jawab kami</div>
        <div class="print-time">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>