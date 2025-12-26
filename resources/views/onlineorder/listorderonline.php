<h4>Transaksi Belum Ada Harga</h4>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID Transaksi</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transaksi as $t)
        <tr>
            <td>{{ $t->id_transaksi }}</td>
            <td>{{ $t->pelanggan->nama ?? '-' }}</td>
            <td>Rp 0</td>
            <td>
                <a href="{{ route('riwayat.detail', $t->id_transaksi) }}"
                   class="btn btn-sm btn-warning">
                   Isi Harga
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">Tidak ada transaksi</td>
        </tr>
        @endforelse
    </tbody>
</table>
