@extends('layouts.admin')

@section('content')
<h4>Delivery Belum Ada Driver</h4>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID Delivery</th>
            <th>Nama Pelanggan</th>
            <th>Jenis</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($deliveries as $d)
        <tr>
            <td>{{ $d->id_delivery }}</td>
            <td>{{ $d->transaksi->nama_pelanggan ?? '-' }}</td>
            <td>{{ $d->jenis }}</td>
            <td>{{ $d->status }}</td>
            <td>
                <a href="{{ route('onlineorder.detail', $d->id_delivery) }}"
                   class="btn btn-sm btn-primary">
                    Detail
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Tidak ada delivery</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
