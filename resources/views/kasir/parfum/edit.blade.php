{{-- FE-DOC: Template frontend untuk resources/views/kasir/parfum/edit.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Edit Parfum')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="w-full bg-yellow-400 p-4 flex items-center">
    <a href="{{ route('kasir.parfum.index') }}" class="text-2xl mr-3">←</a>
    <h1 class="text-xl font-bold">Edit Parfum</h1>
</div>

<div class="p-4 bg-white min-h-screen">

    <form action="{{ route('kasir.parfum.update', $parfum->id_parfum) }}" method="POST">
        @csrf
        @method('PUT')

        <label class="block font-bold mb-2 text-lg">Nama Parfum</label>

        <input type="text" name="nama_parfum"
            value="{{ old('nama_parfum', $parfum->nama_parfum) }}"
            class="w-full p-4 rounded-2xl bg-gray-200 text-lg outline-none @error('nama_parfum') border-2 border-red-500 @enderror">

        {{-- ERROR MESSAGE --}}
        @error('nama_parfum')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
        @enderror

        <button type="submit"
            class="w-full bg-green-600 py-4 rounded-full mt-10 font-bold text-white text-xl">
            Simpan
        </button>
    </form>

</div>

@endsection