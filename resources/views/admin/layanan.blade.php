@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('admin.dashboard') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-xl font-bold">Kelola Layanan</span>
</div>

{{-- SEARCH --}}
<div class="px-4 mt-4 flex items-center justify-between">
    <div class="flex items-center gap-2 bg-white px-4 py-3 rounded-xl shadow border border-gray-200 w-full">
        <i class="bi bi-search text-yellow-500 text-lg"></i>
        <input id="searchInput" type="text" placeholder="Cari"
               class="bg-transparent w-full text-sm outline-none">
    </div>

    <button class="ml-3 text-gray-500 flex flex-col items-center text-[10px]">
        <i class="bi bi-arrow-down-up text-lg"></i>
        Sort
    </button>
</div>


{{-- LIST LAYANAN --}}
<div id="layananList" class="mx-4 mt-4 space-y-5">

@forelse ($layananUtama as $item)
    <div class="layanan-item bg-white p-4 rounded-2xl shadow border border-gray-200 relative">

        {{-- DROPDOWN --}}
        <div class="absolute right-4 top-4">
            <button class="dropdown-btn text-gray-700 text-xl">
                <i class="bi bi-three-dots-vertical"></i>
            </button>

            <ul class="dropdown-menu hidden absolute right-0 mt-2 w-40 bg-yellow-400 rounded-2xl shadow-xl overflow-hidden py-1 z-50">

                <li>
                    <a href="{{ route('layanan.duplicate', $item->id_layanan) }}"
                       class="flex items-center gap-2 px-4 py-3 text-black text-sm font-medium hover:bg-yellow-300">
                        <i class="bi bi-layers"></i> Duplikat
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
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </li>

            </ul>
        </div>


        {{-- NAMA LAYANAN --}}
        <p class="text-lg font-bold capitalize pr-10">{{ $item->nama_layanan }}</p>

        {{-- ICON PROSES --}}
        <div class="flex items-center gap-1 mt-1 mb-3 text-[13px] font-medium">
            @php
                $icons = [
                    'Cuci' => 'bi bi-droplet',
                    'Kering' => 'bi bi-wind',
                    'Setrika' => 'bi bi-iron',
                ];
                $steps = explode(',', $item->proses);
            @endphp

            @foreach($steps as $i => $p)
                <div class="flex items-center gap-1">
                    <i class="{{ $icons[$p] ?? 'bi bi-gear' }} text-yellow-500 text-base"></i>
                    <span>{{ $p }}</span>
                </div>

                @if ($i < count($steps)-1)
                    <span class="text-gray-400 font-bold mx-1">››</span>
                @endif
            @endforeach
        </div>

        {{-- DAFTAR JENIS --}}
        @if($item->jenis->count() > 0)
            <div class="space-y-3">

                @foreach ($item->jenis as $jenis)
                <a href="{{ route('jenis.edit', $jenis->id_jenis_layanan) }}"
                   class="flex gap-3 p-3 rounded-xl bg-white shadow-sm border border-gray-200">

                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                        <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}"
                             class="w-full h-full object-cover">
                    </div>

                    <div class="flex-1">
                        <p class="font-semibold text-base capitalize">{{ $jenis->nama_jenis }}</p>

                        <p class="text-gray-700 text-sm">
                            Rp{{ number_format($jenis->harga, 0, ',', '.') }} / {{ $jenis->satuan }}
                        </p>

                        <p class="text-gray-500 text-[12px] flex items-center gap-1">
                            <i class="bi bi-clock"></i>
                            {{ $jenis->lama }} {{ $jenis->lama_satuan }}
                        </p>
                    </div>

                </a>
                @endforeach

            </div>
        @else
            <p class="text-gray-400 text-xs">Belum ada jenis layanan</p>
        @endif

    </div>

@empty
    <p class="text-center text-gray-500 text-sm mt-10">Belum ada layanan.</p>
@endforelse

</div>


{{-- BUTTON TAMBAH --}}
<div class="px-4 mb-8">
    <a href="{{ route('layanan.create') }}"
       class="block mt-6 bg-yellow-400 text-white text-base font-semibold py-3 rounded-2xl shadow text-center">
        <i class="bi bi-plus-circle"></i> Tambah Layanan
    </a>
</div>

@endsection


@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {

    // -------------------------
    // DROPDOWN FUNCTION
    // -------------------------
    document.querySelectorAll(".dropdown-btn").forEach(btn => {
        btn.addEventListener("click", function(){
            const menu = this.nextElementSibling;
            menu.classList.toggle("hidden");
        });
    });

    // Klik di luar → tutup semua dropdown
    document.addEventListener("click", function (e) {
        document.querySelectorAll(".dropdown-menu").forEach(menu => {
            if (!menu.contains(e.target) && !menu.previousElementSibling.contains(e.target)) {
                menu.classList.add("hidden");
            }
        });
    });


    // -------------------------
    // SEARCH FUNCTION
    // -------------------------
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener("input", function(){
        const value = this.value.toLowerCase();
        document.querySelectorAll(".layanan-item").forEach(item => {
            const name = item.querySelector("p").innerText.toLowerCase();
            item.style.display = name.includes(value) ? "block" : "none";
        });
    });

});
</script>
@endsection
