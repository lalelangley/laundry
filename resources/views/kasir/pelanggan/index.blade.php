@extends('layouts.master')
@section('title', 'Kelola Pelanggan')
@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
    <a href="{{ route('kasir.dashboard') }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Kelola Pelanggan</span>
</div>

{{-- SEARCH + SORT --}}
<div class="px-5 mt-5">
    <div class="bg-white rounded-2xl px-4 py-3 flex items-center shadow">
        <i class="bi bi-search text-yellow-500 text-xl mr-3"></i>
        <input type="text" placeholder="Cari"
               class="w-full focus:outline-none text-lg"
               name="search">
        <button class="ml-3 text-gray-500 text-sm flex flex-col items-center">
            <i class="bi bi-arrow-down-up text-xl"></i>
            <span>Sort</span>
        </button>
    </div>
</div>

{{-- LIST PELANGGAN --}}
<div class="px-5 mt-5 space-y-4 mb-24">
    @foreach ($pelanggan as $item)
    <div class="bg-white rounded-2xl px-4 py-4 flex gap-3 items-center shadow justify-between relative group cursor-pointer"
         onclick="window.location='{{ route('kasir.pelanggan.edit', $item->id_pelanggan) }}'">
        <div class="flex gap-3 items-center">
            {{-- Foto --}}
            @if ($item->gambar)
            <img src="{{ asset('images/' . $item->gambar) }}"
                 alt="{{ $item->nama_pelanggan }}"
                 class="w-16 h-16 rounded-xl object-cover shadow-sm"
                 onerror="this.onerror=null; this.src='{{ asset('images/default-user.png') }}';">
            @else
            <div class="w-16 h-16 bg-gray-200 rounded-xl flex items-center justify-center">
                <i class="bi bi-camera text-3xl text-gray-400"></i>
            </div>
            @endif

            {{-- Detail --}}
            <div>
                <div class="text-xl font-semibold">{{ $item->nama_pelanggan }}</div>
                <div class="flex items-center text-gray-600 text-base">
                    <i class="bi bi-envelope me-2"></i>{{ $item->email }}
                </div>
                <div class="flex items-center text-gray-600 text-base">
                    <i class="bi bi-telephone me-2"></i>{{ $item->no_hp }}
                </div>
            </div>
        </div>

        {{-- Tombol hapus (muncul saat hover) --}}
        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">
            <form action="{{ route('kasir.pelanggan.destroy', $item->id_pelanggan) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button"
                        onclick="event.stopPropagation(); confirmDelete(this, 'pelanggan')"
                        data-nama="{{ $item->nama_pelanggan }}"
                        data-email="{{ $item->email }}"
                        data-nohp="{{ $item->no_hp }}"
                        class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold shadow-md transition-all hover:scale-110">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

{{-- BUTTON TAMBAH --}}
<div class="fixed bottom-5 left-0 right-0 px-6">
    <a href="{{ route('kasir.pelanggan.create') }}"
       class="bg-yellow-400 w-full block text-center py-4 rounded-full text-xl font-semibold shadow-lg hover:bg-yellow-500 transition-all">
        Tambah Pelanggan
    </a>
</div>

@endsection