{{-- FE-DOC: Template frontend untuk resources/views/admin2/transaksi/pelanggan.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Pilih Pelanggan')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
    <a href="{{ route('admin2.transaksi.create') }}" 
       class="text-black text-3xl font-bold hover:scale-110 transition-transform">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Pilih Pelanggan</span>
</div>

{{-- SEARCH --}}
{{-- FE-DOC: Input pencarian ini membantu user menemukan data spesifik berdasarkan kata kunci. --}}
<form method="GET" class="px-5 mt-5">
    <div class="bg-white rounded-2xl px-4 py-3 flex flex-col gap-3 shadow hover:shadow-lg transition-all lg:flex-row lg:items-center">
        <i class="bi bi-search text-yellow-500 text-xl lg:mr-1"></i>
        <input type="text"
               placeholder="Cari pelanggan..." 
               class="w-full focus:outline-none text-lg"
               name="search"
               value="{{ request('search') }}">
        <select name="sort" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold outline-none sm:w-auto">
            <option value="nama_asc" {{ request('sort', 'nama_asc') === 'nama_asc' ? 'selected' : '' }}>Nama A-Z</option>
            <option value="nama_desc" {{ request('sort') === 'nama_desc' ? 'selected' : '' }}>Nama Z-A</option>
            <option value="terbaru" {{ request('sort') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
            <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
        </select>
        <button class="w-full bg-yellow-400 hover:bg-yellow-500 px-5 py-3 rounded-xl font-semibold transition-all sm:w-auto sm:py-2.5 lg:min-w-[120px]">Terapkan</button>
    </div>
</form>

{{-- LIST --}}
<div class="px-5 mt-6 space-y-4 mb-28 lg:mb-36" id="listPelanggan">
    @foreach ($pelanggan as $item)
    <a href="{{ route('admin2.transaksi.setPelanggan', $item->id_pelanggan) }}"
       class="block bg-white rounded-2xl px-4 py-4 flex gap-3 items-center shadow 
              hover:shadow-xl hover:bg-yellow-50 hover:scale-[1.01] transition-all duration-300">
        
        {{-- ✅ FIXED: Foto Pelanggan --}}
        <div class="w-16 h-16 rounded-xl overflow-hidden border border-gray-300 shadow-sm hover:scale-110 transition-transform flex-shrink-0">
            @if ($item->gambar)
                <img src="{{ asset('images/' . $item->gambar) }}"
                     alt="{{ $item->nama_pelanggan }}"
                     class="w-full h-full object-cover"
                     onerror="this.onerror=null; this.src='{{ asset('images/default-user.png') }}';">
            @else
                <img src="{{ asset('images/default-user.png') }}"
                     alt="Default"
                     class="w-full h-full object-cover">
            @endif
        </div>

        {{-- Detail --}}
        <div class="flex-1">
            <div class="text-xl font-semibold">{{ $item->nama_pelanggan }}</div>
            <div class="flex items-center text-gray-600 text-base mt-1">
                <i class="bi bi-envelope me-2"></i>{{ $item->email ?? '-' }}
            </div>
            <div class="flex items-center text-gray-600 text-base">
                <i class="bi bi-telephone me-2"></i>{{ $item->no_hp }}
            </div>
        </div>
    </a>
    @endforeach
</div>

@if(method_exists($pelanggan, 'links'))
<div class="px-5 pb-28">
    {{ $pelanggan->appends(request()->query())->links() }}
</div>
@endif

{{-- BUTTON TAMBAH --}}
<div class="fixed bottom-5 left-0 right-0 px-6 desktop-docked-bar z-40">
    <a href="{{ route('admin2.transaksi.pelanggan.create') }}?from=transaksi"
       class="bg-yellow-400 w-full block text-center py-4 rounded-full text-lg font-semibold 
              shadow-lg hover:bg-yellow-500 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 lg:max-w-sm lg:ml-auto">
        Tambah Pelanggan
    </a>
</div>

@endsection
