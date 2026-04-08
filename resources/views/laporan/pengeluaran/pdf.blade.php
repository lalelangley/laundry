<!DOCTYPE html>
<!-- FE-DOC: Template frontend untuk resources/views/laporan/pengeluaran/pdf.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur HTML, CSS, dan JavaScript tanpa mengubah behavior. -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran</title>
    <!-- FE-DOC: Blok CSS khusus halaman ini. -->
    <style>
        /* Reset dasar supaya hasil render PDF lebih konsisten antar elemen. */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* Tipografi dasar PDF dibuat netral dan aman untuk generator PDF. */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
        }

        /* ── HEADER ── */
        .header {
            background-color: #FBBF24;
            padding: 18px 28px 16px;
            margin-bottom: 0;
        }
        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-left h1 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 2px;
        }
        .header-left .subtitle {
            font-size: 10px;
            color: #555;
        }
        .header-right {
            text-align: right;
        }
        .header-right .period-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-right .period-value {
            font-size: 12px;
            font-weight: 700;
            color: #1a1a1a;
            margin-top: 2px;
        }

        /* ── META BAR ── */
        .meta-bar {
            background: #1a1a1a;
            padding: 7px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .meta-bar span {
            font-size: 9px;
            color: #aaa;
            letter-spacing: 0.3px;
        }
        .meta-bar .app-name {
            color: #FBBF24;
            font-weight: 700;
        }

        /* ── SUMMARY CARDS ── */
        .summary-row {
            display: flex;
            gap: 14px;
            padding: 0 28px;
            margin-bottom: 20px;
        }
        .card {
            /* Kartu kiri dan kanan memakai base yang sama, warna dibedakan lewat modifier. */
            flex: 1;
            border-radius: 10px;
            padding: 13px 16px;
            border-left: 4px solid #FBBF24;
            background: #FFFBEB;
        }
        .card.orange {
            border-left-color: #FB923C;
            background: #FFF7ED;
        }
        .card .card-label {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .card .card-value {
            font-size: 20px;
            font-weight: 700;
            color: #D97706;
            line-height: 1;
        }
        .card.orange .card-value {
            color: #EA580C;
        }
        .card .card-value-sm {
            font-size: 14px;
            font-weight: 700;
            color: #EA580C;
            line-height: 1;
        }

        /* ── SECTION TITLE ── */
        .section-title {
            padding: 0 28px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title span {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #E5E7EB;
        }

        /* ── TABLE ── */
        /* Bungkus tabel agar jarak kiri-kanan konsisten dengan header dan summary. */
        .table-wrapper { padding: 0 28px; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        thead tr {
            background-color: #FBBF24;
        }
        thead th {
            padding: 9px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }

        tbody tr {
            border-bottom: 1px solid #F3F4F6;
        }
        /* Zebra stripe membantu pembacaan saat data pengeluaran banyak. */
        tbody tr:nth-child(even) {
            background-color: #FFFBEB;
        }
        tbody td {
            padding: 9px 12px;
            color: #374151;
            vertical-align: middle;
        }
        tbody td.no-col {
            color: #9CA3AF;
            font-size: 10px;
            text-align: center;
            width: 36px;
        }
        tbody td.date-col {
            white-space: nowrap;
            color: #6B7280;
        }
        tbody td.nominal-col {
            text-align: right;
            font-weight: 700;
            color: #EA580C;
        }

        /* ── TFOOT ── */
        tfoot tr {
            background-color: #1F2937;
        }
        tfoot td {
            padding: 10px 12px;
            font-size: 11px;
            font-weight: 700;
            color: #F9FAFB;
        }
        tfoot td.right {
            text-align: right;
            color: #FBBF24;
            font-size: 12px;
        }

        /* ── EMPTY ── */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9CA3AF;
            font-size: 12px;
        }

        /* ── FOOTER ── */
        .footer {
            margin-top: 28px;
            padding: 12px 28px 0;
            border-top: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #9CA3AF;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
    <div class="header">
        {{-- Header PDF menampilkan judul laporan dan periode aktif --}}
        <div class="header-inner">
            <div class="header-left">
                <h1>Laporan Pengeluaran</h1>
                <p class="subtitle">Rekap pengeluaran berdasarkan periode yang dipilih</p>
            </div>
            <div class="header-right">
                <div class="period-label">Periode</div>
                <div class="period-value">
                    @if($dari && $sampai)
                        {{ \Carbon\Carbon::parse($dari)->translatedFormat('d M Y') }}
                        &ndash;
                        {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y') }}
                    @else
                        Semua Periode
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- META BAR --}}
{{-- FE-DOC: Meta bar biasanya menampilkan nama aplikasi, waktu cetak, atau konteks dokumen export. --}}
    <div class="meta-bar">
        <span class="app-name">{{ config('app.name', 'Laundry App') }}</span>
        <span>Dicetak: {{ $tanggal_cetak }} WIB</span>
    </div>

    {{-- SUMMARY CARDS --}}
{{-- FE-DOC: Summary cards menampilkan angka ringkas supaya insight utama terbaca sebelum masuk ke tabel. --}}
    <div class="summary-row">
        {{-- Summary dipakai untuk membaca total tanpa perlu melihat tabel rincian --}}
        <div class="card">
            <div class="card-label">Total Item Pengeluaran</div>
            <div class="card-value">{{ number_format($pengeluaran->count()) }}</div>
        </div>
        <div class="card orange">
            <div class="card-label">Total Pengeluaran</div>
            <div class="card-value-sm">Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- SECTION TITLE --}}
{{-- FE-DOC: Section title memisahkan ringkasan dan tabel agar struktur dokumen PDF lebih jelas. --}}
    <div class="section-title">
        <span>Rincian Pengeluaran</span>
    </div>

    {{-- TABLE --}}
{{-- FE-DOC: Tabel atau daftar utama berisi detail data hasil filter dan sorting. --}}
    <div class="table-wrapper">
        {{-- Struktur tabel sengaja sederhana agar aman saat dirender ke PDF --}}
        <table>
            <thead>
                <tr>
                    <th class="center" style="width:36px;">No</th>
                    <th style="width:90px;">Tanggal</th>
                    <th>Nama Pengeluaran</th>
                    <th>Catatan</th>
                    <th class="right" style="width:110px;">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengeluaran as $i => $p)
                <tr>
                    <td class="no-col">{{ $i + 1 }}</td>
                    <td class="date-col">
                        {{ \Carbon\Carbon::parse($p->tanggal_pengeluaran)->translatedFormat('d M Y') }}
                    </td>
                    <td style="font-weight:600;">{{ $p->nama_pengeluaran }}</td>
                    <td style="color:#6B7280;">{{ $p->catatan ?? '-' }}</td>
                    <td class="nominal-col">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        Tidak ada data pengeluaran pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if ($pengeluaran->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="4">TOTAL PENGELUARAN</td>
                    <td class="right">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- FOOTER --}}
{{-- FE-DOC: Footer dokumen dipakai untuk identitas laporan dan informasi cetak. --}}
    <div class="footer">
        <span>{{ config('app.name', 'Aplikasi') }} &bull; Laporan Pengeluaran</span>
        <span>{{ $pengeluaran->count() }} item tercatat &bull; Halaman 1</span>
    </div>

</body>
</html>
