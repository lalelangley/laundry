{{-- FE-DOC: Template frontend untuk resources/views/admin2/satuan/edit.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Edit Satuan')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="w-full bg-yellow-400 p-4 flex items-center">
    <a href="{{ route('admin2.satuan.index') }}" class="text-2xl mr-3">←</a>
    <h1 class="text-xl font-bold">Edit Satuan</h1>
</div>

<div class="p-4 bg-white min-h-screen">

    <form action="{{ route('admin2.satuan.update', $satuan->id_satuan) }}" method="POST">
        @csrf
        @method('PUT')

        <label class="block font-bold mb-2 text-lg">Nama Satuan</label>

        <input type="text" name="nama_satuan"
            value="{{ $satuan->nama_satuan }}"
            class="w-full p-4 rounded-2xl bg-gray-200 text-lg outline-none">

        {{-- ERROR MESSAGE --}}
        @error('nama_satuan')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
        @enderror

        <!-- BUTTON -->
        <button type="submit"
            class="w-full bg-green-600 py-4 rounded-full mt-10 font-bold text-white text-xl">
            Update
        </button>

    </form>

</div>

@endsection
