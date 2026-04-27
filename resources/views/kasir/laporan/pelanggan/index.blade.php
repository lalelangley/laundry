{{-- FE-DOC: Template frontend untuk resources/views/kasir/laporan/pelanggan/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Pelanggan')
@section('content')
<div class="min-h-screen bg-gray-100 pb-28">

<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl sticky top-0 z-20 shadow">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('kasir.laporan.index') }}" class="text-2xl font-bold hover:scale-110 transition-transform">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Pelanggan</h1>
        </div>
        <div class="hidden sm:inline-flex rounded-full bg-white/40 px-4 py-2 text-sm font-semibold text-gray-800">
            View only
        </div>
    </div>
</div>

<form method="GET" id="filterForm" class="px-6 mt-6 space-y-3">
    <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
        <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 flex items-center gap-2 font-semibold min-w-0">
            <i class="bi bi-calendar-event"></i>
            <input type="date" name="dari" id="dari" value="{{ request('dari') }}" class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <span class="font-bold text-gray-700 hidden xl:block">s/d</span>
        <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 flex items-center gap-2 font-semibold min-w-0">
            <i class="bi bi-calendar-event"></i>
            <input type="date" name="sampai" id="sampai" value="{{ request('sampai') }}" class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" id="resetBtn" class="bg-gray-200 hover:bg-gray-300 px-4 py-3 rounded-full transition-all" title="Reset Filter">
                <i class="bi bi-arrow-clockwise font-bold"></i>
            </button>
            <div class="relative w-full sm:w-auto">
                <select name="sort" id="sort" class="bg-white rounded-full px-4 py-3 pr-12 font-semibold shadow outline-none w-full sm:w-auto appearance-none">
                    <option value="belanja_tertinggi" {{ request('sort', 'belanja_tertinggi') === 'belanja_tertinggi' ? 'selected' : '' }}>Belanja Tertinggi</option>
                    <option value="belanja_terendah" {{ request('sort') === 'belanja_terendah' ? 'selected' : '' }}>Belanja Terendah</option>
                    <option value="transaksi_terbanyak" {{ request('sort') === 'transaksi_terbanyak' ? 'selected' : '' }}>Transaksi Terbanyak</option>
                    <option value="nama_az" {{ request('sort') === 'nama_az' ? 'selected' : '' }}>Nama A-Z</option>
                </select>
                <i class="bi bi-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl sm:rounded-full shadow flex flex-col sm:flex-row items-stretch sm:items-center px-4 py-3 gap-3">
        <i class="bi bi-search text-xl text-gray-400"></i>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama pelanggan atau no HP..." class="flex-1 outline-none bg-transparent font-semibold text-gray-700">
        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 px-5 py-2 rounded-full font-bold transition-all w-full sm:w-auto">
            Cari
        </button>
        @if(request()->hasAny(['q', 'dari', 'sampai', 'sort']))
            <a href="{{ route('kasir.laporan.pelanggan.index') }}" class="bg-gray-200 hover:bg-gray-300 px-5 py-2 rounded-full font-bold transition-all text-center w-full sm:w-auto">
                Reset
            </a>
        @endif
    </div>
</form>

<div class="px-6 mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl p-4 shadow border-2 border-yellow-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-yellow-100 rounded-full flex items-center justify-center shrink-0">
                <i class="bi bi-people text-yellow-600 text-xl"></i>
            </div>
            <div>
                <div class="text-xs text-gray-500 font-semibold">Total Pelanggan</div>
                <div class="text-2xl font-bold text-gray-800">{{ $summary['total_pelanggan'] }}</div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow border-2 border-orange-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                <i class="bi bi-receipt text-orange-600 text-xl"></i>
            </div>
            <div>
                <div class="text-xs text-gray-500 font-semibold">Total Transaksi</div>
                <div class="text-2xl font-bold text-orange-600">{{ $summary['total_transaksi'] }}</div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow border-2 border-green-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                <i class="bi bi-cash-stack text-green-600 text-xl"></i>
            </div>
            <div>
                <div class="text-xs text-gray-500 font-semibold">Total Belanja</div>
                <div class="text-base font-bold text-green-600 break-words">Rp {{ number_format($summary['total_belanja'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="px-6 mt-5">
    <div class="bg-white rounded-xl overflow-hidden shadow">
        <div class="overflow-x-auto">
            <div class="grid min-w-[860px] grid-cols-[56px_2fr_1.2fr_1fr_1.2fr] bg-yellow-400 text-center font-bold text-sm py-3 border-b border-yellow-500">
                <div>#</div>
                <div class="flex items-center justify-center gap-1"><i class="bi bi-person"></i> Pelanggan</div>
                <div class="flex items-center justify-center gap-1"><i class="bi bi-telephone"></i> No HP</div>
                <div class="flex items-center justify-center gap-1"><i class="bi bi-receipt"></i> Transaksi</div>
                <div class="flex items-center justify-center gap-1"><i class="bi bi-cash"></i> Total Belanja</div>
            </div>

            @forelse ($data as $i => $item)
                <div class="grid min-w-[860px] grid-cols-[56px_2fr_1.2fr_1fr_1.2fr] text-center py-4 border-b border-gray-100 hover:bg-yellow-50 transition-all text-sm">
                    <div class="text-gray-400 font-semibold flex items-center justify-center">{{ $data->firstItem() + $i }}</div>
                    <div class="px-3 flex items-center gap-3 text-left min-w-0">
                        <div class="w-11 h-11 rounded-full overflow-hidden bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center shrink-0">
                            @if($item->gambar ?? false)
                                <img src="{{ asset('storage/'.$item->gambar) }}" class="w-full h-full object-cover" alt="{{ $item->nama_pelanggan }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            @endif
                            <i class="bi bi-person text-white text-lg {{ ($item->gambar ?? false) ? 'hidden' : '' }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 break-words">{{ $item->nama_pelanggan }}</p>
                            @if(($topPelanggan->id_pelanggan ?? $data->first()?->id_pelanggan ?? null) == ($item->id_pelanggan ?? null))
                                <span class="inline-flex mt-1 items-center gap-1 text-[11px] font-bold bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                                    <i class="bi bi-trophy-fill"></i> Top Pelanggan
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-gray-600 font-semibold flex items-center justify-center px-2 break-all">{{ $item->no_hp ?: '-' }}</div>
                    <div class="font-semibold text-gray-800 flex items-center justify-center px-2">{{ $item->total_transaksi }}</div>
                    <div class="font-bold text-orange-600 flex items-center justify-center px-2 break-words">Rp {{ number_format($item->total_belanja, 0, ',', '.') }}</div>
                </div>
            @empty
                <div class="text-center py-20 text-gray-400">
                    <i class="bi bi-inbox text-6xl mb-3 block text-gray-300"></i>
                    <p class="font-semibold text-lg">Tidak ada data pelanggan</p>
                    <p class="text-sm mt-1">Coba ubah filter atau rentang tanggal</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@if ($data->hasPages())
<div class="px-6 mt-4 pb-4">
    <div class="px-5 mt-6 flex items-center justify-center gap-2">
        <p class="text-sm text-gray-500 font-semibold">
            Menampilkan {{ $data->firstItem() }}-{{ $data->lastItem() }} dari {{ $data->total() }} data
        </p>
        <div class="flex items-center gap-2">
            @if ($data->onFirstPage())
                <span class="px-3 py-2 rounded-full bg-gray-100 text-gray-400 text-sm font-semibold cursor-not-allowed"><i class="bi bi-chevron-left"></i></span>
            @else
                <a href="{{ $data->previousPageUrl() }}" class="px-3 py-2 rounded-full bg-yellow-400 hover:bg-yellow-500 text-sm font-semibold transition-all"><i class="bi bi-chevron-left"></i></a>
            @endif

            @foreach ($data->getUrlRange(max(1, $data->currentPage()-2), min($data->lastPage(), $data->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-bold transition-all {{ $page == $data->currentPage() ? 'bg-yellow-400 text-gray-900 shadow' : 'bg-white hover:bg-yellow-50 text-gray-600 shadow-sm' }}">
                    {{ $page }}
                </a>
            @endforeach

            @if ($data->hasMorePages())
                <a href="{{ $data->nextPageUrl() }}" class="px-3 py-2 rounded-full bg-yellow-400 hover:bg-yellow-500 text-sm font-semibold transition-all"><i class="bi bi-chevron-right"></i></a>
            @else
                <span class="px-3 py-2 rounded-full bg-gray-100 text-gray-400 text-sm font-semibold cursor-not-allowed"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    </div>
</div>
@endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const resetBtn = document.getElementById('resetBtn');
    const dariInput = document.getElementById('dari');
    const sampaiInput = document.getElementById('sampai');
    const sortInput = document.getElementById('sort');

    dariInput.addEventListener('change', function() {
        if (sampaiInput.value && this.value > sampaiInput.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            this.value = '';
            return;
        }
        if (this.value && sampaiInput.value) filterForm.submit();
    });

    sampaiInput.addEventListener('change', function() {
        if (dariInput.value && this.value < dariInput.value) {
            alert('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
            this.value = '';
            return;
        }
        if (this.value && dariInput.value) filterForm.submit();
    });

    resetBtn.addEventListener('click', function() {
        window.location.href = '{{ route("kasir.laporan.pelanggan.index") }}';
    });

    sortInput.addEventListener('change', function() {
        filterForm.submit();
    });
});
</script>
@endpush
