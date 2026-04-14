<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pelanggan</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #111827;
            background: #ffffff;
        }
        .page { padding: 24px 28px; }
        .header {
            background: linear-gradient(135deg, #facc15, #f59e0b);
            color: #111827;
            padding: 18px 22px;
            border-radius: 18px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 10px;
            color: #4b5563;
        }
        .meta {
            margin-top: 16px;
            display: table;
            width: 100%;
        }
        .meta-item {
            display: table-cell;
            width: 50%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .meta-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .meta-value {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }
        .summary {
            margin-top: 16px;
            padding: 12px 14px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 14px;
        }
        .summary strong { color: #c2410c; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        thead th {
            background: #111827;
            color: #f9fafb;
            font-size: 9px;
            text-transform: uppercase;
            padding: 10px 8px;
            text-align: left;
        }
        tbody td {
            border-bottom: 1px solid #e5e7eb;
            padding: 9px 8px;
            vertical-align: top;
        }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tfoot td {
            background: #fff7ed;
            border-top: 1px solid #fed7aa;
            font-weight: 700;
            padding: 10px 8px;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .muted { color: #6b7280; }
        .empty {
            text-align: center;
            padding: 26px;
            color: #9ca3af;
        }
        .footer {
            margin-top: 18px;
            font-size: 9px;
            color: #6b7280;
            display: table;
            width: 100%;
        }
        .footer span { display: table-cell; }
        .footer .right-text { text-align: right; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>Laporan Pelanggan</h1>
            <p>Rekap pelanggan berdasarkan total transaksi dan total belanja</p>
        </div>

        <div class="meta">
            <div class="meta-item">
                <div class="meta-label">Periode</div>
                <div class="meta-value">
                    {{ $dari ? \Carbon\Carbon::parse($dari)->format('d/m/Y') : '-' }}
                    -
                    {{ $sampai ? \Carbon\Carbon::parse($sampai)->format('d/m/Y') : '-' }}
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Tanggal Cetak</div>
                <div class="meta-value">{{ $tanggal_cetak }}</div>
            </div>
        </div>

        <div class="summary">
            Total pelanggan: <strong>{{ $totalPelanggan }}</strong>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Total transaksi: <strong>{{ number_format($totalTransaksi, 0, ',', '.') }}</strong>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Total belanja: <strong>Rp {{ number_format($totalBelanja, 0, ',', '.') }}</strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 36px;">No</th>
                    <th>Nama Pelanggan</th>
                    <th style="width: 110px;">No HP</th>
                    <th class="center" style="width: 90px;">Transaksi</th>
                    <th class="right" style="width: 120px;">Total Belanja</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->nama_pelanggan ?? '-' }}</strong><br>
                            <span class="muted">ID: {{ $item->id_pelanggan ?? '-' }}</span>
                        </td>
                        <td>{{ $item->no_hp ?: '-' }}</td>
                        <td class="center">{{ (int) ($item->total_transaksi ?? 0) }}</td>
                        <td class="right">Rp {{ number_format((float) ($item->total_belanja ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">Tidak ada data pelanggan pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="3">TOTAL</td>
                        <td class="center">{{ number_format($totalTransaksi, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="footer">
            <span>{{ config('app.name', 'Laundry App') }} • Export PDF Laporan Pelanggan</span>
            <span class="right-text">Halaman 1</span>
        </div>
    </div>
</body>
</html>
