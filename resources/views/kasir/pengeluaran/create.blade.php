{{-- FE-DOC: Template frontend untuk resources/views/kasir/pengeluaran/create.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Data Pengeluaran')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
    <a href="{{ route('kasir.pengeluaran.index') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Data Pengeluaran</span>
</div>

{{-- WRAPPER --}}
<div class="p-5 pb-[160px] space-y-6">

    <form action="{{ route('kasir.pengeluaran.store') }}" method="POST">
        @csrf

        {{-- NAMA PENGELUARAN --}}
        <div>
            <label class="font-semibold text-lg">Nama Pengeluaran</label>
            <input type="text" name="nama_pengeluaran"
                class="w-full p-4 bg-gray-100 rounded-3xl mt-2 border focus:ring-2 focus:ring-yellow-400"
                placeholder="Masukkan nama pengeluaran" required>
        </div>

        {{-- CATATAN --}}
        <div>
            <label class="font-semibold text-lg">Catatan</label>
            <textarea name="catatan"
                class="w-full p-4 bg-gray-100 rounded-3xl mt-2 border focus:ring-2 focus:ring-yellow-400 h-32"
                placeholder="Tambahkan catatan (opsional)"></textarea>
        </div>

        {{-- NOMINAL --}}
        <div>
            <label class="font-semibold text-lg">Nominal</label>
            <input type="number" name="nominal"
                class="w-full p-4 bg-gray-100 rounded-3xl mt-2 border focus:ring-2 focus:ring-yellow-400"
                placeholder="Masukkan nominal" required>
        </div>

        {{-- TANGGAL --}}
        <div>
            <label class="font-semibold text-lg">Tanggal Pengeluaran</label>
            <input type="date" name="tanggal"
                value="{{ date('Y-m-d') }}"
                class="w-full p-4 bg-gray-100 rounded-3xl mt-2 border focus:ring-2 focus:ring-yellow-400"
                required>
        </div>

        {{-- BUTTON SIMPAN FIXED --}}
        <div class="fixed bottom-0 left-0 w-full bg-gray-100 px-6 py-5 desktop-docked-bar z-40 lg:bottom-4 lg:rounded-[28px]">
            <button type="submit"
                class="w-full bg-green-600 text-white py-4 rounded-3xl text-xl font-bold shadow hover:bg-green-700 transition">
                Simpan
            </button>
        </div>

    </form>

</div>

@endsection
