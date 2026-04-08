<!DOCTYPE html>
<!-- FE-DOC: Template frontend untuk resources/views/laporan/satuan/satuan-pdf.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur HTML, CSS, dan JavaScript tanpa mengubah behavior. -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Satuan</title>
    <!-- FE-DOC: Blok CSS khusus halaman ini. -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #fff;
        }

        /* ── HEADER ── */
        .header {
            background-color: #FBBF24;
            padding: 20px 30px;
            border-radius: 0 0 16px 16px;
            margin-bottom: 24px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #444;
        }

        .header .period {
            text-align: right;
            font-size: 11px;
            color: #444;
        }

        .header .period strong {
            display: block;
            font-size: 13px;
            color: #1a1a1a;
            margin-top: 2px;
        }

        /* ── PRINT INFO ── */
        .print-info {
            font-size: 10px;
            color: #888;
            text-align: right;
            padding: 0 30px;
            margin-bottom: 16px;
        }

        /* ── SUMMARY CARD ── */
        .summary-wrapper {
            padding: 0 30px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: #FFFBEB;
            border: 1.5px solid #FBBF24;
            border-radius: 10px;
            padding: 14px 20px;
            display: inline-block;
            min-width: 220px;
        }

        .summary-card .label {
            font-size: 11px;
            color: #666;
            margin-bottom: 4px;
        }

        .summary-card .value {
            font-size: 22px;
            font-weight: 700;
            color: #D97706;
        }

        /* ── TABLE ── */
        .table-wrapper {
            padding: 0 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background-color: #FBBF24;
        }

        thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        thead th:last-child {
            text-align: right;
        }

        tbody tr {
            border-bottom: 1px solid #F3F4F6;
        }

        tbody tr:nth-child(even) {
            background-color: #FFFBEB;
        }

        tbody tr:hover {
            background-color: #FEF3C7;
        }

        tbody td {
            padding: 10px 14px;
            font-size: 12px;
            color: #1a1a1a;
        }

        tbody td:last-child {
            text-align: right;
            font-weight: 700;
            color: #D97706;
        }

        .no-col {
            width: 40px;
            color: #888;
            font-size: 11px;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9CA3AF;
            font-size: 13px;
        }

        /* ── FOOTER ── */
        .footer {
            margin-top: 32px;
            padding: 16px 30px 0;
            border-top: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #9CA3AF;
        }

        .footer .total-row {
            font-size: 12px;
            font-weight: 700;
            color: #1a1a1a;
        }

        /* ── TFOOT TOTAL ── */
        tfoot tr {
            background-color: #1a1a1a;
        }

        tfoot td {
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            color: #FBBF24;
        }

        tfoot td:last-child {
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
    <div class="header">
        <div class="header-top">
            <div>
                <h1>Laporan Satuan</h1>
                <p class="subtitle">Rekap qty berdasarkan satuan barang</p>
            </div>
            <div class="period">
                Periode
                <strong>{{ \Carbon\Carbon::parse($tglAwal)->translatedFormat('d M Y') }}
                    &ndash;
                    {{ \Carbon\Carbon::parse($tglAkhir)->translatedFormat('d M Y') }}
                </strong>
            </div>
        </div>
    </div>

    {{-- PRINT DATE --}}
    <div class="print-info">
        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
    </div>

    {{-- SUMMARY --}}
{{-- FE-DOC: Summary section menampilkan total atau agregasi hasil filter aktif. --}}
    <div class="summary-wrapper">
        <div class="summary-card">
            <div class="label">Total Qty Keseluruhan</div>
            <div class="value">{{ number_format($data->sum('total_qty')) }}</div>
        </div>
    </div>

    {{-- TABLE --}}
{{-- FE-DOC: Tabel atau daftar utama berisi detail data hasil filter dan sorting. --}}
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th class="no-col">No</th>
                    <th>Nama Satuan</th>
                    <th>Total Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $i => $item)
                <tr>
                    <td class="no-col">{{ $i + 1 }}</td>
                    <td>{{ $item->nama_satuan }}</td>
                    <td>{{ number_format($item->total_qty) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="empty-state">
                        Tidak ada data satuan pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if ($data->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL KESELURUHAN</td>
                    <td>{{ number_format($data->sum('total_qty')) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- FOOTER --}}
{{-- FE-DOC: Footer dokumen dipakai untuk identitas laporan dan informasi cetak. --}}
    <div class="footer">
        <span>Laporan Satuan &bull; {{ config('app.name', 'Aplikasi') }}</span>
        <span>{{ $data->count() }} jenis satuan tercatat</span>
    </div>

</body>
</html>