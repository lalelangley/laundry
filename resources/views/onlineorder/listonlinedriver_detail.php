@extends('layouts.admin')

@section('content')
<h4>Detail Delivery</h4>

<table class="table">
    <tr>
        <th>Nama Pelanggan</th>
        <td>{{ $delivery->transaksi->nama_pelanggan }}</td>
    </tr>
    <tr>
        <th>No HP</th>
        <td>{{ $delivery->transaksi->no_hp }}</td>
    </tr>
    <tr>
        <th>Alamat Tujuan</th>
        <td>{{ $delivery->alamat_tujuan }}</td>
    </tr>
    <tr>
        <th>Jenis</th>
        <td>{{ $delivery->jenis }}</td>
    </tr>
</table>

<hr>

<form method="POST" action="{{ route('onlineorder.assign', $delivery->id_delivery) }}">
    @csrf

    <div class="form-group">
        <label>Pilih Driver</label>
        <select name="id_driver" class="form-control" required>
            <option value="">-- Pilih Driver --</option>
            @foreach($drivers as $driver)
                <option value="{{ $driver->id_driver }}">
                    {{ $driver->nama_driver }}
                </option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-success mt-3">
        Assign Driver
    </button>
</form>
@endsection
