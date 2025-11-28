@extends('layouts.master')

@section('content')
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('layanan.edit', $from ?? 0) }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Edit Jenis Layanan</span>
</div>

<div class="px-5 mt-6">
    <form action="{{ route('layanan.jenis.update', $jenis->id_jenis_layanan) }}" method="POST" enctype="multipart/form-data" class="bg-white p-5 rounded-2xl shadow">
        @csrf
        @method('PUT')
        <input type="hidden" name="from" value="{{ $from }}">

        {{-- Nama Jenis --}}
        <div class="mb-5">
            <label class="font-semibold">Nama Jenis</label>
            <input type="text" name="nama_jenis" value="{{ old('nama_jenis', $jenis->nama_jenis) }}" class="w-full p-3 border rounded-xl mt-1" required>
        </div>

        {{-- Satuan --}}
        <div class="mb-5">
            <label class="font-semibold">Satuan</label>
            <select name="id_satuan" class="w-full p-3 border rounded-xl mt-1" required>
                @foreach($satuan as $s)
                    <option value="{{ $s->id_satuan }}" {{ old('id_satuan', $jenis->id_satuan) == $s->id_satuan ? 'selected' : '' }}>
                        {{ $s->nama_satuan }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Harga --}}
        <div class="mb-5">
            <label class="font-semibold">Harga</label>
            <input type="number" name="harga" value="{{ old('harga', $jenis->harga) }}" class="w-full p-3 border rounded-xl mt-1" required>
        </div>

        {{-- Lama --}}
        <div class="mb-5">
            <label class="font-semibold">Lama</label>
            <input type="number" name="lama" value="{{ old('lama', $jenis->lama) }}" class="w-full p-3 border rounded-xl mt-1" required>
        </div>

        {{-- Lama Satuan --}}
        <div class="mb-5">
            <label class="font-semibold">Lama Satuan</label>
            <input type="text" name="lama_satuan" value="{{ old('lama_satuan', $jenis->lama_satuan) }}" class="w-full p-3 border rounded-xl mt-1" required>
        </div>

        {{-- Keterangan --}}
        <div class="mb-5">
            <label class="font-semibold">Keterangan</label>
            <textarea name="keterangan" class="w-full p-3 border rounded-xl mt-1">{{ old('keterangan', $jenis->keterangan) }}</textarea>
        </div>

        {{-- Gambar --}}
        <div class="mb-5">
            <label class="font-semibold">Gambar</label>
            <input type="file" name="gambar" class="w-full p-3 border rounded-xl mt-1">
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 px-6 py-2 rounded-xl font-semibold text-white">
                Update Jenis
            </button>
        </div>
    </form>
</div>
@endsection
