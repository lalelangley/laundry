{{-- FE-DOC: Template frontend untuk resources/views/laporan/pengeluaran/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Pengeluaran')
@section('content')
<div class="min-h-screen bg-gray-100 pb-28">

{{-- Wrapper halaman utama untuk memberi ruang, background, dan jarak bawah --}}

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl sticky top-0 z-20 shadow">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('laporan.index') }}" class="text-2xl font-bold hover:scale-110 transition-transform">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Pengeluaran</h1>
        </div>
        {{-- Tombol export mengikuti filter yang sedang aktif lewat query string --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('laporan.pengeluaran.export') }}?{{ http_build_query(array_merge(request()->all(), ['format' => 'excel'])) }}"
               class="flex items-center gap-1.5 text-sm font-semibold bg-white bg-opacity-30 hover:bg-opacity-50 px-3 py-2 rounded-full transition-all">
                <i class="bi bi-file-earmark-spreadsheet text-green-700"></i>
                <span class="hidden sm:inline">Excel</span>
            </a>
            <a href="{{ route('laporan.pengeluaran.export') }}?{{ http_build_query(array_merge(request()->all(), ['format' => 'pdf'])) }}"
               class="flex items-center gap-1.5 text-sm font-semibold bg-white bg-opacity-30 hover:bg-opacity-50 px-3 py-2 rounded-full transition-all">
                <i class="bi bi-file-earmark-pdf text-red-600"></i>
                <span class="hidden sm:inline">PDF</span>
            </a>
        </div>
    </div>
</div>

{{-- FILTER --}}
{{-- FE-DOC: Area filter merangkum input penting agar user bisa mempersempit data yang tampil. --}}
{{-- FE-DOC: Area filter merangkum input tanggal, pencarian, sorting, dan reset agar user bisa mempersempit data. --}}
<form method="GET" id="filterForm" class="px-6 mt-6 space-y-3">
    {{-- Filter digabung dalam satu form GET supaya URL bisa dibagikan / di-refresh --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3 flex items-center gap-2 font-semibold">
            <i class="bi bi-calendar-event"></i>
            <input type="date"
                   name="dari"
                   id="dari"
                   value="{{ request('dari') }}"
                   class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <span class="font-bold text-gray-700">s/d</span>
        <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3 flex items-center gap-2 font-semibold">
            <i class="bi bi-calendar-event"></i>
            <input type="date"
                   name="sampai"
                   id="sampai"
                   value="{{ request('sampai') }}"
                   class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <button type="button"
                id="resetBtn"
                class="bg-gray-200 hover:bg-gray-300 px-4 py-3 rounded-full transition-all"
                title="Reset Filter">
            <i class="bi bi-arrow-clockwise font-bold"></i>
        </button>
        <select name="sort"
                id="sort"
                class="bg-white rounded-full px-4 py-3 font-semibold shadow outline-none">
            <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
            <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
            <option value="nominal_tertinggi" {{ request('sort') === 'nominal_tertinggi' ? 'selected' : '' }}>Nominal Tertinggi</option>
            <option value="nominal_terendah" {{ request('sort') === 'nominal_terendah' ? 'selected' : '' }}>Nominal Terendah</option>
        </select>
    </div>

    {{-- Search --}}
{{-- FE-DOC: Search dipakai untuk pencarian cepat tanpa perlu membuka filter lanjutan. --}}
    <div class="bg-white rounded-full shadow flex items-center px-4 py-3 gap-3">
        {{-- Area search dibuat kontras agar aksi cari mudah terlihat --}}
        <i class="bi bi-search text-xl text-gray-400"></i>
        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Cari nama pengeluaran..."
               class="flex-1 outline-none bg-transparent font-semibold text-gray-700">
        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 px-5 py-2 rounded-full font-bold transition-all">
            Cari
        </button>
        @if(request()->hasAny(['q', 'dari', 'sampai', 'sort']))
            <a href="{{ route('laporan.pengeluaran.index') }}"
               class="bg-gray-200 hover:bg-gray-300 px-5 py-2 rounded-full font-bold transition-all">
                Reset
            </a>
        @endif
    </div>
</form>

{{-- SUMMARY CARDS --}}
{{-- FE-DOC: Summary cards menampilkan angka ringkas supaya insight utama terbaca sebelum masuk ke tabel. --}}
<div class="px-6 mt-5 grid grid-cols-2 gap-4">
    {{-- Kartu ringkasan untuk memberi gambaran cepat tanpa baca tabel --}}
    <div class="bg-white rounded-xl p-4 shadow border-2 border-yellow-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-yellow-100 rounded-full flex items-center justify-center shrink-0">
                <i class="bi bi-receipt text-yellow-600 text-xl"></i>
            </div>
            <div>
                <div class="text-xs text-gray-500 font-semibold">Total Item</div>
                <div class="text-2xl font-bold text-gray-800">{{ $totalItem }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-4 shadow border-2 border-orange-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                <i class="bi bi-wallet2 text-orange-600 text-xl"></i>
            </div>
            <div>
                <div class="text-xs text-gray-500 font-semibold">Total Pengeluaran</div>
                <div class="text-base font-bold text-orange-600">
                    Rp {{ number_format($totalNominal, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE --}}
{{-- FE-DOC: Tabel atau daftar utama berisi detail data hasil filter dan sorting. --}}
<div class="px-6 mt-5">
    {{-- Tabel utama memakai grid custom agar kolom nomor lebih ramping dari kolom isi --}}
    <div class="bg-white rounded-xl overflow-hidden shadow">
        {{-- Table Header --}}
{{-- FE-DOC: Header tabel memberikan konteks kolom dan biasanya memuat ringkasan periode aktif. --}}
        <div class="grid grid-cols-[40px_1fr_1fr_1fr] bg-yellow-400 text-center font-bold text-sm py-3 border-b border-yellow-500">
            <div class="text-center">#</div>
            <div class="flex items-center justify-center gap-1">
                <i class="bi bi-calendar-event"></i> Tanggal
            </div>
            <div class="flex items-center justify-center gap-1">
                <i class="bi bi-tag"></i> Nama Pengeluaran
            </div>
            <div class="flex items-center justify-center gap-1">
                <i class="bi bi-cash"></i> Nominal
            </div>
        </div>

        @forelse ($pengeluaran as $i => $p)
            <div class="grid grid-cols-[40px_1fr_1fr_1fr] text-center py-4 border-b border-gray-100 hover:bg-yellow-50 transition-all text-sm">
                <div class="text-gray-400 font-semibold flex items-center justify-center">
                    {{ $pengeluaran->firstItem() + $i }}
                </div>
                <div class="text-gray-600 font-semibold flex items-center justify-center">
                    {{ \Carbon\Carbon::parse($p->tanggal_pengeluaran)->format('d/m/Y') }}
                </div>
                <div class="font-semibold text-gray-800 px-2 flex items-center justify-center">
                    {{ $p->nama_pengeluaran }}
                </div>
                <div class="font-bold text-orange-600 flex items-center justify-center">
                    Rp {{ number_format($p->nominal, 0, ',', '.') }}
                </div>
            </div>
        @empty
            <div class="text-center py-20 text-gray-400">
                <i class="bi bi-inbox text-6xl mb-3 block text-gray-300"></i>
                <p class="font-semibold text-lg">Tidak ada data pengeluaran</p>
                <p class="text-sm mt-1">Coba ubah filter atau rentang tanggal</p>
            </div>
        @endforelse
    </div>
</div>

{{-- PAGINATION --}}
{{-- FE-DOC: Pagination menjaga jumlah data per halaman tetap nyaman dibaca dan performa tetap ringan. --}}
@if ($pengeluaran->hasPages())
<div class="px-6 mt-4 pb-4">
        <div class="px-5 mt-6 flex items-center justify-center gap-2">
            {{-- Info jumlah data + navigasi halaman manual agar gaya tetap seragam --}}
            <p class="text-sm text-gray-500 font-semibold">
                Menampilkan {{ $pengeluaran->firstItem() }}–{{ $pengeluaran->lastItem() }}
                dari {{ $pengeluaran->total() }} data
        </p>
        <div class="flex items-center gap-2">
            {{-- Prev --}}
            @if ($pengeluaran->onFirstPage())
                <span class="px-3 py-2 rounded-full bg-gray-100 text-gray-400 text-sm font-semibold cursor-not-allowed">
                    <i class="bi bi-chevron-left"></i>
                </span>
            @else
                <a href="{{ $pengeluaran->previousPageUrl() }}"
                   class="px-3 py-2 rounded-full bg-yellow-400 hover:bg-yellow-500 text-sm font-semibold transition-all">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($pengeluaran->getUrlRange(max(1, $pengeluaran->currentPage()-2), min($pengeluaran->lastPage(), $pengeluaran->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}"
                   class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-bold transition-all
                          {{ $page == $pengeluaran->currentPage()
                             ? 'bg-yellow-400 text-gray-900 shadow'
                             : 'bg-white hover:bg-yellow-50 text-gray-600 shadow-sm' }}">
                    {{ $page }}
                </a>
            @endforeach

            {{-- Next --}}
            @if ($pengeluaran->hasMorePages())
                <a href="{{ $pengeluaran->nextPageUrl() }}"
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

@push('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cache elemen form untuk event filter dan validasi tanggal.
    const filterForm = document.getElementById('filterForm');
    const resetBtn   = document.getElementById('resetBtn');
    const dariInput  = document.getElementById('dari');
    const sampaiInput= document.getElementById('sampai');
    const sortInput  = document.getElementById('sort');

    // Tanggal mulai memicu validasi dulu, lalu submit otomatis jika rentang sudah lengkap.
    dariInput.addEventListener('change', function() {
        if (sampaiInput.value && this.value > sampaiInput.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            this.value = '';
            return;
        }
        if (this.value && sampaiInput.value) filterForm.submit();
    });

    // Tanggal akhir punya logika yang sama, tapi membandingkan arah sebaliknya.
    sampaiInput.addEventListener('change', function() {
        if (dariInput.value && this.value < dariInput.value) {
            alert('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
            this.value = '';
            return;
        }
        if (this.value && dariInput.value) filterForm.submit();
    });

    // Reset kembali ke halaman index tanpa parameter query.
    resetBtn.addEventListener('click', function() {
        window.location.href = '{{ route("laporan.pengeluaran.index") }}';
    });

    // Sort langsung submit supaya urutan hasil cepat berubah.
    sortInput.addEventListener('change', function() {
        filterForm.submit();
    });
});
</script>
@endpush
