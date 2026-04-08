<!DOCTYPE html>
<!-- FE-DOC: Template PDF untuk export laporan transaksi. -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <!-- FE-DOC: Blok CSS khusus template PDF transaksi. -->
    <style>
        /* Reset dasar supaya hasil render PDF konsisten di dompdf. */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        /* Font DejaVu Sans dipilih karena aman untuk karakter latin saat generate PDF. */
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; font-size: 10px; background: #ffffff; }
        /* Page memberi padding global agar konten tidak terlalu mepet tepi kertas. */
        .page { padding: 24px 28px; }
        /* Header dibuat kontras agar judul laporan langsung menonjol saat dicetak. */
        .header { background: linear-gradient(135deg, #facc15, #f59e0b); color: #111827; padding: 18px 22px; border-radius: 18px; }
        .header h1 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .header p { font-size: 10px; color: #4b5563; }
        /* Meta memakai layout tabel agar lebih stabil dibanding flex di renderer PDF. */
        .meta { margin-top: 16px; display: table; width: 100%; }
        .meta-item { display: table-cell; width: 33.33%; padding: 10px 12px; border: 1px solid #e5e7eb; background: #f9fafb; }
        .meta-label { font-size: 8px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
        .meta-value { font-size: 11px; font-weight: 700; color: #111827; }
        /* Summary menampilkan total data, omzet, dan waktu cetak dalam satu blok cepat baca. */
        .summary { margin-top: 16px; padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px; }
        .summary strong { color: #c2410c; }
        /* Tabel menjadi bagian utama export, jadi border dan zebra stripe dibuat jelas. */
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        thead th { background: #111827; color: #f9fafb; font-size: 9px; text-transform: uppercase; padding: 10px 8px; text-align: left; }
        tbody td { border-bottom: 1px solid #e5e7eb; padding: 9px 8px; vertical-align: top; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .center { text-align: center; }
        .right { text-align: right; }
        .muted { color: #6b7280; }
        .empty { text-align: center; padding: 26px; color: #9ca3af; }
        /* Footer sederhana dipakai untuk identitas dokumen export. */
        .footer { margin-top: 18px; font-size: 9px; color: #6b7280; display: table; width: 100%; }
        .footer span { display: table-cell; }
        .footer .right-text { text-align: right; }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header laporan export transaksi -->
        <div class="header">
            <h1>Laporan Transaksi</h1>
            <p>Rekap transaksi laundry berdasarkan filter export yang dipilih</p>
        </div>

        <!-- Ringkasan filter aktif yang ikut menentukan isi PDF -->
        <div class="meta">
            <div class="meta-item">
                <div class="meta-label">Filter Tanggal</div>
                <div class="meta-value">{{ $filterTypeLabel }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Periode</div>
                <div class="meta-value">
                    {{ \Carbon\Carbon::parse($tanggalAwal)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d M Y') }}
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Status Bayar</div>
                <div class="meta-value">{{ $statusBayar === 'semua' ? 'Semua Status' : str_replace('_', ' ', $statusBayar) }}</div>
            </div>
        </div>

        <!-- Summary angka utama supaya user tidak harus scan seluruh tabel -->
        <div class="summary">
            Total data: <strong>{{ $data->count() }}</strong>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Total omzet: <strong>Rp {{ number_format($totalOmzet, 0, ',', '.') }}</strong>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Dicetak: <strong>{{ $tanggalCetak }}</strong>
        </div>

        <!-- Tabel rincian transaksi -->
        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 36px;">No</th>
                    <th style="width: 68px;">No Nota</th>
                    <th>Nama Pelanggan</th>
                    <th style="width: 88px;">Tgl Masuk</th>
                    <th style="width: 88px;">Tgl Lunas</th>
                    <th style="width: 90px;">Status Bayar</th>
                    <th style="width: 96px;">Status Transaksi</th>
                    <th style="width: 72px;">Jenis</th>
                    <th class="right" style="width: 96px;">Total Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>#{{ $item->id_transaksi }}</td>
                        <td>
                            <strong>{{ $item->nama_pelanggan ?? '-' }}</strong><br>
                            <span class="muted">{{ $item->no_hp ?? '-' }}</span>
                        </td>
                        <td>{{ $item->tgl_transaksi ? \Carbon\Carbon::parse($item->tgl_transaksi)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $item->tgl_lunas ? \Carbon\Carbon::parse($item->tgl_lunas)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $item->status_bayar ? str_replace('_', ' ', $item->status_bayar) : '-' }}</td>
                        <td>{{ $item->status_transaksi ? str_replace('_', ' ', $item->status_transaksi) : '-' }}</td>
                        <td>{{ ucfirst($item->jenis_transaksi ?? 'offline') }}</td>
                        <td class="right">Rp {{ number_format($item->total_bayar ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">Tidak ada data transaksi pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer identitas dokumen -->
        <div class="footer">
            <span>{{ config('app.name', 'Laundry App') }} • Export PDF Laporan Transaksi</span>
            <span class="right-text">Halaman 1</span>
        </div>
    </div>
</body>
</html>
