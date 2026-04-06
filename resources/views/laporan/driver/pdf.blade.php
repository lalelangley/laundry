<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Driver</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { margin: 0 0 8px; font-size: 20px; text-align: center; }
        .meta { text-align: center; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 7px; }
        th { background: #facc15; }
        .text-center { text-align: center; }
        .summary { margin-bottom: 14px; }
        .summary td { width: 25%; }
    </style>
</head>
<body>
    <h1>Laporan Driver</h1>
    <div class="meta">
        <div>Periode: {{ \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y') }}</div>
        <div>Dicetak: {{ $tanggal_cetak }}</div>
    </div>

    <table class="summary">
        <tr>
            <td>Total Driver: {{ $stats['total_driver_aktif'] }}</td>
            <td>Total Pengiriman: {{ $stats['total_pengiriman'] }}</td>
            <td>Pickup: {{ $stats['total_pickup'] }}</td>
            <td>Antar: {{ $stats['total_antar'] }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Driver</th>
                <th>No HP</th>
                <th>Total</th>
                <th>Pickup</th>
                <th>Antar</th>
                <th>Terkirim</th>
                <th>Gagal</th>
                <th>Dalam Proses</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_driver }}</td>
                    <td>{{ $item->no_telp }}</td>
                    <td class="text-center">{{ $item->total_pengiriman }}</td>
                    <td class="text-center">{{ $item->total_pickup }}</td>
                    <td class="text-center">{{ $item->total_antar }}</td>
                    <td class="text-center">{{ $item->terkirim }}</td>
                    <td class="text-center">{{ $item->gagal }}</td>
                    <td class="text-center">{{ $item->dalam_proses }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
