@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
    <a href="{{ route('admin.dashboard') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Kelola Layanan</span>
</div>

{{-- SEARCH --}}
<div class="px-4 mt-6 flex items-center justify-between">
    <div class="flex items-center gap-3 bg-white px-4 py-3 rounded-2xl shadow border border-gray-200 w-full">
        <i class="bi bi-search text-yellow-500 text-xl"></i>
        <input id="searchInput" 
               type="text" 
               placeholder="Cari"
               class="bg-transparent w-full text-base outline-none">
    </div>

    <button class="ml-3 text-gray-600 flex flex-col items-center text-[12px] leading-tight">
        <i class="bi bi-arrow-down-up text-2xl"></i>
        Sort
    </button>
</div>


{{-- LIST LAYANAN --}}
<div id="layananList" class="mx-4 mt-6 space-y-7">

@forelse ($layananUtama as $item)
<div class="layanan-item bg-white p-5 rounded-3xl shadow-md border border-gray-200 relative">

    {{-- DROPDOWN --}}
    <div class="absolute right-4 top-4">
        <button class="dropdown-btn text-gray-700 text-2xl">
            <i class="bi bi-three-dots-vertical"></i>
        </button>

        <ul class="dropdown-menu hidden absolute right-0 mt-2 w-40 bg-yellow-400 rounded-2xl shadow-xl overflow-hidden py-1 z-50">

            <li>
                <a href="{{ route('layanan.duplicate', $item->id_layanan) }}"
                   class="flex items-center gap-2 px-4 py-3 text-black text-sm font-medium hover:bg-yellow-300">
                    <i class="bi bi-layers text-lg"></i> Duplikat
                </a>
            </li>

            <li>
                <form action="{{ route('layanan.destroy', $item->id_layanan) }}"
                      method="POST"
                      onsubmit="return confirm('Yakin hapus layanan ini?')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="w-full flex items-center gap-2 px-4 py-3 text-red-600 text-sm font-medium hover:bg-yellow-300">
                        <i class="bi bi-trash text-lg"></i> Hapus
                    </button>
                </form>
            </li>

        </ul>
    </div>

    {{-- NAMA LAYANAN --}}
    <a href="{{ route('layanan.edit', $item->id_layanan) }}"
       class="text-xl font-bold capitalize pr-10 inline-block mb-3">
        {{ $item->nama_layanan }}
    </a>

    {{-- ICON PROSES --}}
    <div class="flex items-center gap-2 mt-1 mb-4 text-[15px] font-semibold">
        @php
            $icons = ['Cuci'=>'bi bi-droplet','Kering'=>'bi bi-wind','Setrika'=>'bi bi-iron'];
            $steps = explode(',', $item->proses);
        @endphp
        @foreach($steps as $i => $p)
            <div class="flex items-center gap-1">
                <i class="{{ $icons[$p] ?? 'bi bi-gear' }} text-yellow-500 text-xl"></i>
                <span>{{ $p }}</span>
            </div>
            @if ($i < count($steps)-1)
                <span class="text-gray-400 font-bold mx-1">››</span>
            @endif
        @endforeach
    </div>

    {{-- DAFTAR JENIS --}}
@if($item->jenis->count() > 0)
    <div class="space-y-4">
        @foreach ($item->jenis as $jenis)
            <a href="{{ route('layanan.edit', $item->id_layanan) }}"
            class="flex gap-4 p-4 rounded-2xl bg-white shadow border border-gray-200">

                <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-100 flex-shrink-0">
                    <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}"
                         class="w-full h-full object-cover">
                </div>

                <div class="flex-1">
                    <p class="font-semibold text-[17px] capitalize">{{ $jenis->nama_jenis }}</p>

                    <p class="text-gray-700 text-[15px]">
                        Rp{{ number_format($jenis->harga,0,',','.') }} / {{ $jenis->satuan }}
                    </p>

                    <p class="text-gray-500 text-[13px] flex items-center gap-1">
                        <i class="bi bi-clock text-base"></i>
                        {{ $jenis->lama }} {{ $jenis->lama_satuan }}
                    </p>
                </div>

            </a>
        @endforeach
    </div>
@else
    <p class="text-gray-400 text-sm">Belum ada jenis layanan</p>
@endif


</div>
@empty
<p class="text-center text-gray-500 text-sm mt-10">Belum ada layanan.</p>
@endforelse


</div>


{{-- BUTTON TAMBAH --}}
<div class="px-4 mb-10">
    <a href="{{ route('layanan.create') }}"
       class="block mt-6 bg-yellow-400 text-white text-lg font-bold py-3 rounded-2xl shadow-md text-center">
        <i class="bi bi-plus-circle"></i> Tambah Layanan
    </a>
</div>

@endsection


@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {

    // DROPDOWN
    document.querySelectorAll(".dropdown-btn").forEach(btn => {
        btn.addEventListener("click", function(){
            this.nextElementSibling.classList.toggle("hidden");
        });
    });

    document.addEventListener("click", function (e) {
        document.querySelectorAll(".dropdown-menu").forEach(menu => {
            if (!menu.contains(e.target) && !menu.previousElementSibling.contains(e.target)) {
                menu.classList.add("hidden");
            }
        });
    });

    // SEARCH
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener("input", function(){
        const value = this.value.toLowerCase();
        document.querySelectorAll(".layanan-item").forEach(item => {
            const name = item.querySelector("a").innerText.toLowerCase();
            item.style.display = name.includes(value) ? "block" : "none";
        });
    });

});
</script>
@endsection
