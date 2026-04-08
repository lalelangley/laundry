<!DOCTYPE html>
<!-- FE-DOC: Template frontend untuk resources/views/laporan/bayar/pdf.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur HTML, CSS, dan JavaScript tanpa mengubah behavior. -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Metode Bayar</title>
    <!-- FE-DOC: Blok CSS khusus halaman ini. -->
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { margin: 0 0 8px; font-size: 20px; text-align: center; }
        .meta { text-align: center; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #facc15; }
        .text-center { text-align: center; }
        .total { font-weight: bold; background: #ffedd5; }
    </style>
</head>
<body>
    <h1>Laporan Metode Bayar</h1>
    <div class="meta">
        <div>Periode: {{ \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y') }}</div>
        <div>Dicetak: {{ $tanggal_cetak }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Metode Pembayaran</th>
                <th>Total Penggunaan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_metode_bayar }}</td>
                    <td class="text-center">{{ $item->total_penggunaan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="2" class="text-center">TOTAL</td>
                <td class="text-center">{{ $totalPenggunaan }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
