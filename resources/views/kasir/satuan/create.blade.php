{{-- FE-DOC: Template frontend untuk resources/views/kasir/satuan/create.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Tambah Satuan')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="w-full bg-yellow-400 p-4 flex items-center">
    <a href="{{ route('kasir.satuan.index') }}" class="text-2xl mr-3">←</a>
    <h1 class="text-xl font-bold">Tambah Satuan</h1>
</div>

<div class="p-4 bg-white min-h-screen">

    <form action="{{ route('kasir.satuan.store') }}" method="POST">
        @csrf

        {{-- Hidden input --}}
       <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="id_layanan" value="{{ request('id_layanan') }}">
        <input type="hidden" name="id_jenis" value="{{ request('id_jenis') }}">  {{-- HARUS ADA --}}

        <label class="block font-bold mb-2 text-lg">Nama Satuan</label>
        <input type="text" name="nama_satuan"
            class="w-full p-4 rounded-2xl bg-gray-200 text-lg outline-none">

        {{-- ERROR MESSAGE --}}
        @error('nama_satuan')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
        @enderror

        <button type="submit"
            class="w-full bg-green-600 py-4 rounded-full mt-10 font-bold text-white text-xl">
            Simpan
        </button>
    </form>
</div>

@endsection
