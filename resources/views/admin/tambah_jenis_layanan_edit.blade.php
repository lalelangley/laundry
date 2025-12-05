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

    {{-- FORM TAMBAH --}}
    <form action="{{ route('layanan.jenis.add.edit', $from) }}" method="POST" enctype="multipart/form-data"
        class="bg-white p-5 rounded-2xl shadow space-y-6">
        @csrf

        {{-- GAMBAR --}}
        <div>
            <label class="font-semibold block mb-1">Gambar</label>
            <div class="flex items-center gap-4">
                <div class="w-24 h-24 bg-gray-200 flex rounded-xl items-center justify-center text-gray-500 text-4xl">
                    <i class="bi bi-person-circle"></i>
                </div>
                <label class="bg-yellow-400 px-6 py-3 rounded-xl text-white font-semibold cursor-pointer">
                    Pilih Gambar
                    <input type="file" name="gambar" class="hidden">
                </label>
            </div>
        </div>

        {{-- NAMA JENIS --}}
        <div>
            <label class="font-semibold block mb-1">Nama Jenis</label>
            <input type="text" name="nama_jenis" value="{{ old('nama_jenis') }}"
                class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>
        </div>

        {{-- SATUAN --}}
        <div>
            <label class="font-semibold block mb-1">Satuan</label>
            <div class="flex items-center gap-3">
                <select name="id_satuan" class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>
                    <option value="">-- Pilih Satuan --</option>
                    @foreach($satuan as $s)
                        <option value="{{ $s->id_satuan }}" {{ old('id_satuan', $jenis->id_satuan ?? '') == $s->id_satuan ? 'selected' : '' }}>
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('satuan.index') }}"
                    class="bg-green-600 px-6 py-3 rounded-2xl text-white font-semibold">
                    Tambah
                </a>
            </div>
        </div>

        {{-- HARGA --}}
        <div>
            <label class="font-semibold block mb-1">Harga</label>
            <input type="number" name="harga" value="{{ old('harga') }}"
                class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>
        </div>

        {{-- LAMA + LAMA SATUAN --}}
        <div>
            <label class="font-semibold block mb-1">Lama Pengerjaan</label>
            <div class="flex gap-3">
                <input type="number" name="lama" value="{{ old('lama') }}"
                    class="w-full bg-gray-100 p-4 rounded-2xl text-lg">

                <select name="lama_satuan" class="bg-gray-100 p-4 rounded-2xl text-lg">
                    <option value="Hari" {{ old('lama_satuan') == 'Hari' ? 'selected' : '' }}>Hari</option>
                    <option value="Jam" {{ old('lama_satuan') == 'Jam' ? 'selected' : '' }}>Jam</option>
                </select>
            </div>
        </div>

        {{-- KETERANGAN --}}
        <div>
            <label class="font-semibold block mb-1">Keterangan</label>
            <textarea name="keterangan" class="w-full bg-gray-100 p-4 rounded-2xl text-lg">{{ old('keterangan') }}</textarea>
        </div>

        {{-- BUTTON --}}
        <button type="submit"
            class="w-full bg-green-700 text-white py-4 rounded-2xl text-xl font-semibold">
            Simpan
        </button>

    </form>
</div>
@endsection
