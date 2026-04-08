{{-- FE-DOC: Template frontend untuk resources/views/laporan/pelanggan/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Pelanggan')
@section('content')
<div class="min-h-screen bg-gray-100 pb-24">

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl sticky top-0 z-20 shadow">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('laporan.index') }}" class="text-2xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-lg font-bold">Laporan Pelanggan</h1>
    </div>
    
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('laporan.pelanggan.export') }}?{{ http_build_query(array_merge(request()->all(), ['format' => 'pdf'])) }}"
           class="flex items-center gap-2 font-semibold bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-full transition-all text-sm">
            <i class="bi bi-file-earmark-pdf"></i>
            <span>PDF</span>
        </a>
        <a href="{{ route('laporan.pelanggan.export') }}?{{ http_build_query(request()->all()) }}" 
           class="flex items-center gap-2 font-semibold bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-full transition-all text-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            <span>Excel</span>
        </a>
    </div>
    </div>
</div>

{{-- FILTER & SEARCH --}}
<form method="GET" id="filterForm" class="px-5 mt-6 space-y-4">
    {{-- Date Range --}}
    <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
        <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 flex items-center gap-2 font-semibold min-w-0">
            <i class="bi bi-calendar-event"></i>
            <input type="date" 
                   name="dari" 
                   id="dari"
                   value="{{ request('dari') }}"
                   class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <span class="font-bold text-gray-700 hidden xl:block">s/d</span>
        <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 flex items-center gap-2 font-semibold min-w-0">
            <i class="bi bi-calendar-event"></i>
            <input type="date" 
                   name="sampai" 
                   id="sampai"
                   value="{{ request('sampai') }}"
                   class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <div class="flex flex-wrap gap-3">
        <div class="relative w-full sm:w-auto">
        <select name="sort" onchange="this.form.submit()" class="bg-white rounded-full px-4 py-3 pr-12 text-sm font-semibold outline-none shadow w-full sm:w-auto appearance-none">
            <option value="belanja_tertinggi" {{ request('sort', 'belanja_tertinggi') === 'belanja_tertinggi' ? 'selected' : '' }}>Belanja Tertinggi</option>
            <option value="belanja_terendah" {{ request('sort') === 'belanja_terendah' ? 'selected' : '' }}>Belanja Terendah</option>
            <option value="transaksi_terbanyak" {{ request('sort') === 'transaksi_terbanyak' ? 'selected' : '' }}>Transaksi Terbanyak</option>
            <option value="nama_az" {{ request('sort') === 'nama_az' ? 'selected' : '' }}>Nama A-Z</option>
        </select>
        <i class="bi bi-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
        </div>
        @if(request()->hasAny(['dari', 'sampai', 'sort']))
            <a href="{{ route('laporan.pelanggan.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 px-4 py-3 rounded-full transition-all"
               title="Reset">
                <i class="bi bi-arrow-clockwise font-bold"></i>
            </a>
        @endif
        </div>
    </div>

    {{-- Search --}}
{{-- FE-DOC: Search dipakai untuk pencarian cepat tanpa perlu membuka filter lanjutan. --}}
    <div class="bg-white rounded-3xl sm:rounded-full shadow flex flex-col sm:flex-row items-stretch sm:items-center px-4 py-3 gap-3">
        <i class="bi bi-search text-xl text-gray-400"></i>
        <input type="text" 
               name="q" 
               value="{{ request('q') }}"
               placeholder="Cari nama pelanggan atau no HP..."
               class="flex-1 outline-none bg-transparent font-semibold text-gray-700">
        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 px-5 py-2 rounded-full font-bold transition-all w-full sm:w-auto">
            Cari
        </button>
    </div>
</form>

{{-- SUMMARY STATS --}}
<div class="px-5 mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div class="bg-white rounded-xl p-4 shadow border-2 border-yellow-400 text-center">
        <div class="text-xs text-gray-500 font-semibold">Total Pelanggan</div>
        <div class="text-2xl font-bold text-gray-800">{{ $summary['total_pelanggan'] }}</div>
    </div>
    
    <div class="bg-white rounded-xl p-4 shadow border-2 border-orange-400 text-center">
        <div class="text-xs text-gray-500 font-semibold">Total Transaksi</div>
        <div class="text-2xl font-bold text-orange-600">{{ $summary['total_transaksi'] }}</div>
    </div>
    
    <div class="bg-white rounded-xl p-4 shadow border-2 border-green-400 text-center">
        <div class="text-xs text-gray-500 font-semibold">Total Belanja</div>
        <div class="text-sm font-bold text-green-600">
            Rp {{ number_format($summary['total_belanja'], 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- TOP PELANGGAN --}}
@php $top = $topPelanggan ?? $data->first(); @endphp
@if($top)
<div class="mx-5 mt-5 bg-gradient-to-r from-yellow-100 to-orange-100 border-2 border-yellow-400 rounded-xl p-5 shadow-md">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4 min-w-0">
            <div class="w-16 h-16 rounded-full overflow-hidden bg-yellow-400 flex items-center justify-center border-4 border-white shadow">
                @if($top->gambar ?? false)
                    <img src="{{ asset('storage/'.$top->gambar) }}" class="w-full h-full object-cover" alt="{{ $top->nama_pelanggan }}">
                @else
                    <i class="bi bi-person text-white text-2xl"></i>
                @endif
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <i class="bi bi-trophy-fill text-yellow-500"></i>
                    <span class="text-xs font-bold text-gray-600 uppercase">Top Pelanggan</span>
                </div>
                <p class="text-lg font-bold text-gray-800">{{ $top->nama_pelanggan }}</p>
                <p class="text-sm font-semibold text-gray-600">{{ $top->no_hp }}</p>
            </div>
        </div>
        <div class="text-left md:text-right">
            <div class="text-xl md:text-2xl font-bold text-orange-600 break-words">
                Rp {{ number_format($top->total_belanja, 0, ',', '.') }}
            </div>
            <div class="text-sm font-semibold text-gray-600 mt-1">
                {{ $top->total_transaksi }} Transaksi
            </div>
        </div>
    </div>
</div>
@endif

{{-- LIST PELANGGAN --}}
<div class="mt-6 pb-6">
    @forelse($data as $index => $item)
        <div class="bg-white flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-5 py-4 border-b hover:bg-gray-50 transition-all">
            <div class="flex items-start gap-4 min-w-0">
                {{-- Ranking Badge --}}
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                    {{ $index == 0 ? 'bg-yellow-400 text-white' : '' }}
                    {{ $index == 1 ? 'bg-gray-300 text-white' : '' }}
                    {{ $index == 2 ? 'bg-orange-300 text-white' : '' }}
                    {{ $index > 2 ? 'bg-gray-100 text-gray-600' : '' }}">
                    {{ ($data->firstItem() ?? 1) + $index }}
                </div>
                
                {{-- Avatar --}}
                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center shadow">
                    @if($item->gambar ?? false)
                        <img src="{{ asset('storage/'.$item->gambar) }}" 
                             class="w-full h-full object-cover" 
                             alt="{{ $item->nama_pelanggan }}"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    @endif
                    <i class="bi bi-person text-white text-xl {{ ($item->gambar ?? false) ? 'hidden' : '' }}"></i>
                </div>
                
                {{-- Info --}}
                <div class="min-w-0">
                    <p class="font-bold text-gray-800 break-words">{{ $item->nama_pelanggan }}</p>
                    <p class="text-sm text-gray-500 font-semibold break-all">{{ $item->no_hp }}</p>
                    <div class="flex flex-wrap items-center gap-3 mt-1">
                        <span class="text-sm font-bold text-orange-600 break-words">
                            Rp {{ number_format($item->total_belanja, 0, ',', '.') }}
                        </span>
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full font-semibold">
                            {{ $item->total_transaksi }} Transaksi
                        </span>
                    </div>
                </div>
            </div>
            
            {{-- Arrow --}}
            <i class="bi bi-chevron-right text-gray-400 self-end sm:self-auto"></i>
        </div>
    @empty
        <div class="bg-white text-center py-20 text-gray-400">
            <i class="bi bi-inbox text-6xl mb-3 block text-gray-300"></i>
            <p class="font-semibold text-lg">Tidak ada data pelanggan</p>
            <p class="text-sm mt-1">Coba ubah filter atau rentang tanggal</p>
        </div>
    @endforelse
</div>

@if(method_exists($data, 'links'))
<div class="px-5 pb-6">
    {{ $data->appends(request()->query())->links() }}
</div>
@endif

</div>
@endsection

@push('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const dariInput = document.getElementById('dari');
    const sampaiInput = document.getElementById('sampai');

    // Auto submit when date changes
    dariInput.addEventListener('change', function() {
        if (this.value && sampaiInput.value) {
            filterForm.submit();
        }
    });

    sampaiInput.addEventListener('change', function() {
        if (this.value && dariInput.value) {
            filterForm.submit();
        }
    });

    // Validate date range
    dariInput.addEventListener('change', function() {
        if (sampaiInput.value && this.value > sampaiInput.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            this.value = '';
        }
    });

    sampaiInput.addEventListener('change', function() {
        if (dariInput.value && this.value < dariInput.value) {
            alert('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
            this.value = '';
        }
    });
});
</script>
@endpush
