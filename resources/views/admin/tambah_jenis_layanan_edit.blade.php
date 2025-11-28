@extends('layouts.master')

@section('content')
{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('layanan.edit', $from ?? 0) }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Tambah Jenis Layanan Baru</span>
</div>

<div class="px-5 mt-6">

    {{-- ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="bg-red-500 text-white p-3 rounded-xl mb-5">
            <ul class="ml-4 list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM TAMBAH JENIS BARU --}}
    <form action="{{ route('layanan.jenis.add.edit', $from) }}" method="POST" enctype="multipart/form-data" class="bg-white p-5 rounded-2xl shadow">
    @csrf



        <div class="mb-5">
            <label class="font-semibold">Nama Jenis</label>
            <input type="text" name="nama_jenis" value="{{ old('nama_jenis') }}" class="w-full p-3 border rounded-xl mt-1" required>
        </div>

        <div class="mb-5">
            <label class="font-semibold">Satuan</label>
            <select name="id_satuan" class="w-full p-3 border rounded-xl mt-1" required>
            <option value="">-- Pilih Satuan --</option>
            @foreach($satuan as $s)
                <option value="{{ $s->id_satuan }}" {{ old('id_satuan', $jenis->id_satuan ?? '') == $s->id_satuan ? 'selected' : '' }}>
                    {{ $s->nama_satuan }}
                </option>
            @endforeach
        </select>
        </div>

        <div class="mb-5">
            <label class="font-semibold">Harga</label>
            <input type="number" name="harga" value="{{ old('harga') }}" class="w-full p-3 border rounded-xl mt-1" required>
        </div>

        <div class="mb-5">
            <label class="font-semibold">Lama</label>
            <input type="number" name="lama" value="{{ old('lama') }}" class="w-full p-3 border rounded-xl mt-1">
        </div>

        <div class="mb-5">
            <label class="font-semibold">Lama Satuan</label>
            <input type="text" name="lama_satuan" value="{{ old('lama_satuan') }}" class="w-full p-3 border rounded-xl mt-1">
        </div>

        <div class="mb-5">
            <label class="font-semibold">Keterangan</label>
            <textarea name="keterangan" class="w-full p-3 border rounded-xl mt-1">{{ old('keterangan') }}</textarea>
        </div>

        <div class="mb-5">
            <label class="font-semibold">Gambar</label>
            <input type="file" name="gambar" class="w-full p-3 border rounded-xl mt-1">
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 px-6 py-2 rounded-xl font-semibold text-white">
                Tambah Jenis Baru
            </button>
        </div>
    </form>

</div>
@endsection
