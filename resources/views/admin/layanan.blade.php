@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('admin.dashboard') }}" class="text-black text-2xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-xl font-bold">Kelola Layanan</span>
</div>

{{-- SEARCH --}}
<div class="px-4 mt-4 flex items-center justify-between">
    <div class="flex items-center gap-3 bg-white p-3 rounded-xl shadow border border-gray-200 w-full">
        <i class="bi bi-search text-yellow-500 text-xl"></i>
        <input type="text" placeholder="Cari" class="bg-transparent w-full outline-none">
    </div>

    <div class="ml-2 text-gray-500 flex flex-col items-center text-xs">
        <i class="bi bi-arrow-down-up text-xl"></i>
        Sort
    </div>
</div>

{{-- LIST LAYANAN --}}
<div class="mx-4 mt-4 space-y-6">

@forelse ($layananUtama as $item)
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200">

        {{-- NAMA LAYANAN + MENU --}}
        <div class="flex justify-between items-center mb-1">
            <h2 class="text-xl font-semibold">{{ $item->nama_layanan }}</h2>

            <div class="flex items-center gap-1 px-3 py-1 rounded-lg bg-yellow-100 text-yellow-700 text-sm font-medium">
                <i class="bi bi-list"></i> Menu
            </div>
        </div>

        {{-- ICON PROSES --}}
        <div class="flex items-center gap-2 mb-4">

            @php
                $icons = [
                    'Cuci'    => 'images/mesincuci.png',
                    'Kering'  => 'images/jemur.png',
                    'Setrika' => 'images/setrika.png',
                ];
            @endphp

            @foreach(explode(',', $item->proses) as $index => $p)
                @php $p = trim($p); @endphp

                <div class="flex items-center gap-1">
                    <img src="{{ asset($icons[$p] ?? 'images/default.png') }}"
                         class="w-5 h-5"
                         onerror="this.src='{{ asset('images/default.png') }}'">
                    <span class="text-sm font-medium">{{ $p }}</span>
                </div>

                @if($index < count(explode(',', $item->proses)) - 1)
                    <span class="font-bold text-gray-500">>></span>
                @endif
            @endforeach

        </div>

        {{-- DAFTAR JENIS --}}
        @if(isset($jenisLayanan[$item->nama_layanan]))
            <div class="space-y-4">

                @foreach ($jenisLayanan[$item->nama_layanan] as $jenis)

                    <div class="flex gap-3 p-3 rounded-xl bg-white shadow-sm border border-gray-200">

                        {{-- GAMBAR JENIS --}}
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                            <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='{{ asset('images/default.png') }}'">
                        </div>

                        {{-- DATA --}}
                        <div class="flex-1">
                            <p class="font-semibold text-lg">{{ $jenis->nama_jenis }}</p>

                            <p class="text-gray-700 font-medium">
                                Rp.{{ number_format($jenis->harga, 0, ',', '.') }}/ {{ $jenis->satuan }}
                            </p>

                            <p class="text-sm text-gray-500 flex items-center gap-1">
                                <i class="bi bi-clock"></i>
                                {{ $jenis->lama }} {{ $jenis->lama_satuan }}
                            </p>
                        </div>

                    </div>

                @endforeach

            </div>
        @else
            <p class="text-gray-400 text-sm">Belum ada jenis layanan</p>
        @endif

    </div>
@empty
    <p class="text-center text-gray-500 mt-10">Belum ada layanan.</p>
@endforelse

</div>

{{-- BUTTON TAMBAH --}}
<div class="px-4 mb-6">
    <a href="{{ route('layanan.create') }}"
       class="block mt-6 bg-yellow-400 text-white text-lg font-bold py-3 rounded-3xl shadow hover:bg-yellow-500 text-center">
        <i class="bi bi-plus-circle"></i> Tambah Layanan
    </a>
</div>

@endsection
