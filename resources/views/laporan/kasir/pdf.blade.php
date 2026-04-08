<!DOCTYPE html>
<!-- FE-DOC: Template frontend untuk resources/views/laporan/kasir/pdf.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur HTML, CSS, dan JavaScript tanpa mengubah behavior. -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kasir</title>
    <!-- FE-DOC: Blok CSS khusus halaman ini. -->
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { margin: 0 0 8px; font-size: 20px; text-align: center; }
        .meta { text-align: center; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #facc15; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total { font-weight: bold; background: #ffedd5; }
    </style>
</head>
<body>
    <h1>Laporan Kinerja Kasir</h1>
    <div class="meta">
        <div>Periode: {{ \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y') }}</div>
        <div>Dicetak: {{ $tanggal_cetak }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kasir</th>
                <th>No HP</th>
                <th>Antrian</th>
                <th>Proses</th>
                <th>Siap Ambil</th>
                <th>Selesai</th>
                <th>Batal</th>
                <th>Total Transaksi</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_kasir }}</td>
                    <td>{{ $item->no_hp }}</td>
                    <td class="text-center">{{ $item->antrian }}</td>
                    <td class="text-center">{{ $item->proses }}</td>
                    <td class="text-center">{{ $item->siap_ambil }}</td>
                    <td class="text-center">{{ $item->selesai }}</td>
                    <td class="text-center">{{ $item->batal }}</td>
                    <td class="text-center">{{ $item->total_transaksi }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="8" class="text-right">TOTAL</td>
                <td class="text-center">{{ $totalTransaksi }}</td>
                <td class="text-right">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
