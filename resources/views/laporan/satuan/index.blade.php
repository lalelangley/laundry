{{-- FE-DOC: Template frontend untuk resources/views/laporan/satuan/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Satuan')
@section('content')
<div class="min-h-screen bg-gray-100 pb-24">

    {{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
    <div class="bg-yellow-400 px-5 py-4 rounded-b-3xl sticky top-0 z-20">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('laporan.index') }}" class="text-2xl font-bold">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="text-lg font-bold">Laporan Satuan</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('laporan.satuan.export', ['dari' => $tglAwal, 'sampai' => $tglAkhir]) }}"
                   class="text-sm font-semibold bg-white px-3 py-2 rounded-full hover:bg-gray-100 transition flex items-center gap-1">
                    <i class="bi bi-file-earmark-excel text-green-600"></i>
                    <span>Excel</span>
                </a>
                <a href="{{ route('laporan.satuan.pdf', ['dari' => $tglAwal, 'sampai' => $tglAkhir, 'format' => 'pdf']) }}"
                   class="text-sm font-semibold bg-white px-3 py-2 rounded-full hover:bg-gray-100 transition flex items-center gap-1">
                    <i class="bi bi-file-earmark-pdf text-red-500"></i>
                    <span>PDF</span>
                </a>
            </div>
        </div>
    </div>

    {{-- FILTER TANGGAL --}}
{{-- FE-DOC: Dua input tanggal biasanya menjadi filter utama untuk semua data laporan per periode. --}}
    <form method="GET" class="px-5 mt-5 flex flex-col gap-3 xl:flex-row xl:items-center">
        <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 min-w-0">
            <p class="text-xs">Tanggal Awal</p>
            <input type="date" name="dari" value="{{ $tglAwal }}"
                   class="bg-transparent outline-none font-bold w-full">
        </div>
        <span class="font-bold hidden xl:block">&gt;</span>
        <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 min-w-0">
            <p class="text-xs">Tanggal Akhir</p>
            <input type="date" name="sampai" value="{{ $tglAkhir }}"
                   class="bg-transparent outline-none font-bold w-full">
        </div>
        <div class="relative w-full sm:w-auto">
            <select name="sort" onchange="this.form.submit()" class="bg-white rounded-full px-4 py-3 pr-12 text-sm font-semibold outline-none w-full sm:w-auto appearance-none">
                <option value="qty_tertinggi" {{ request('sort', 'qty_tertinggi') === 'qty_tertinggi' ? 'selected' : '' }}>Qty Tertinggi</option>
                <option value="qty_terendah" {{ request('sort') === 'qty_terendah' ? 'selected' : '' }}>Qty Terendah</option>
                <option value="nama_az" {{ request('sort') === 'nama_az' ? 'selected' : '' }}>Nama A-Z</option>
                <option value="nama_za" {{ request('sort') === 'nama_za' ? 'selected' : '' }}>Nama Z-A</option>
            </select>
            <i class="bi bi-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
        </div>
    </form>

    {{-- SUMMARY CARD --}}
    <div class="px-5 mt-5">
        <div class="bg-white rounded-xl p-4 shadow">
            <p class="text-sm text-gray-600">Total Qty Keseluruhan</p>
            <p class="text-2xl font-bold text-yellow-500">
                {{ number_format($totalQty) }}
            </p>
        </div>
    </div>

    {{-- INFO PAGINATION --}}
    @if ($data->total() > 0)
    <div class="px-5 mt-4">
        <p class="text-xs text-gray-500">
            Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }}
            dari {{ $data->total() }} satuan
        </p>
    </div>
    @endif

    {{-- LIST SATUAN --}}
    <div class="mt-3 space-y-4 px-5">
        @forelse ($data as $item)
            <div class="bg-white rounded-xl px-4 py-4 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center shadow">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-1 h-8 bg-yellow-400 rounded-full"></div>
                    <span class="font-bold text-lg break-words">{{ $item->nama_satuan }}</span>
                </div>
                <span class="font-bold text-lg text-yellow-500">
                    {{ number_format($item->total_qty) }}
                </span>
            </div>
        @empty
            <div class="bg-white rounded-xl px-4 py-8 text-center shadow">
                <i class="bi bi-inbox text-4xl text-gray-400"></i>
                <p class="text-gray-500 mt-2">Tidak ada data satuan</p>
            </div>
        @endforelse
    </div>

   {{-- PAGINATION --}}
{{-- FE-DOC: Pagination menjaga jumlah data per halaman tetap nyaman dibaca dan performa tetap ringan. --}}
@if ($data->hasPages())
<div class="px-6 mt-4 pb-4">
    <div class="px-5 mt-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-center">
        <p class="text-sm text-gray-500 font-semibold">
            Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }}
            dari {{ $data->total() }} satuan
        </p>
        <div class="flex flex-wrap items-center justify-center gap-2">
            {{-- Prev --}}
            @if ($data->onFirstPage())
                <span class="px-3 py-2 rounded-full bg-gray-100 text-gray-400 text-sm font-semibold cursor-not-allowed">
                    <i class="bi bi-chevron-left"></i>
                </span>
            @else
                <a href="{{ $data->previousPageUrl() }}&dari={{ $tglAwal }}&sampai={{ $tglAkhir }}"
                   class="px-3 py-2 rounded-full bg-yellow-400 hover:bg-yellow-500 text-sm font-semibold transition-all">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($data->getUrlRange(max(1, $data->currentPage()-2), min($data->lastPage(), $data->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}&dari={{ $tglAwal }}&sampai={{ $tglAkhir }}"
                   class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-bold transition-all
                          {{ $page == $data->currentPage()
                             ? 'bg-yellow-400 text-gray-900 shadow'
                             : 'bg-white hover:bg-yellow-50 text-gray-600 shadow-sm' }}">
                    {{ $page }}
                </a>
            @endforeach

            {{-- Next --}}
            @if ($data->hasMorePages())
                <a href="{{ $data->nextPageUrl() }}&dari={{ $tglAwal }}&sampai={{ $tglAkhir }}"
                   class="px-3 py-2 rounded-full bg-yellow-400 hover:bg-yellow-500 text-sm font-semibold transition-all">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <span class="px-3 py-2 rounded-full bg-gray-100 text-gray-400 text-sm font-semibold cursor-not-allowed">
                    <i class="bi bi-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
</div>
@endif

</div>
@endsection

@section('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
    document.querySelectorAll('input[type="date"]').forEach(el => {
        el.addEventListener('change', () => el.form.submit());
    });
</script>
@endsection
