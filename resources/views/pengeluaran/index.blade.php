{{-- FE-DOC: Template frontend untuk resources/views/pengeluaran/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
{{-- ============================================================
     HALAMAN: LIST PENGELUARAN
     Deskripsi: Menampilkan daftar pengeluaran dengan fitur
     pencarian real-time, sorting, edit, dan hapus.
     Bug Fix: Tombol hapus tidak berfungsi akibat event delegation
     yang menutup dropdown sebelum handler delete terpanggil.
     Role: Admin
============================================================ --}}
@extends('layouts.master')

@section('title', 'pengeluaran')

@section('content')

{{-- ========================================
     HEADER
     Judul halaman + tombol kembali + dropdown sort
======================================== --}}
<div class="w-full bg-yellow-400 p-4 flex items-center justify-between rounded-b-3xl shadow-md">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold hover:opacity-70 transition">
            ←
        </a>
        <h1 class="text-xl font-bold tracking-wide">List Pengeluaran</h1>
    </div>

    {{-- Dropdown Sort: filter urutan tampilan data --}}
    <div class="relative">
        <button id="sortBtn" class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-md hover:shadow-lg transition">
            <i class="bi bi-funnel text-yellow-500"></i>
            <span class="text-sm font-semibold" id="sortLabel">Terbaru</span>
            <i class="bi bi-chevron-down text-sm"></i>
        </button>
        
        <div id="sortMenu" class="hidden absolute right-0 top-12 w-48 bg-white rounded-2xl shadow-xl overflow-hidden z-50">
            <button onclick="sortPengeluaran('terbaru')" class="sort-option w-full text-left px-4 py-3 hover:bg-yellow-50 transition flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-sort-down-alt text-yellow-500"></i> Terbaru
            </button>
            <button onclick="sortPengeluaran('terlama')" class="sort-option w-full text-left px-4 py-3 hover:bg-yellow-50 transition flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-sort-up text-yellow-500"></i> Terlama
            </button>
            <button onclick="sortPengeluaran('nominal-tertinggi')" class="sort-option w-full text-left px-4 py-3 hover:bg-yellow-50 transition flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-arrow-up-circle text-yellow-500"></i> Nominal Tertinggi
            </button>
            <button onclick="sortPengeluaran('nominal-terendah')" class="sort-option w-full text-left px-4 py-3 hover:bg-yellow-50 transition flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-arrow-down-circle text-yellow-500"></i> Nominal Terendah
            </button>
        </div>
    </div>
</div>

{{-- ========================================
     WRAPPER KONTEN UTAMA
======================================== --}}
<div class="p-5 pb-[150px] bg-gray-100 min-h-screen">

    {{-- ========================================
         SEARCH BAR
         Pencarian real-time berdasarkan nama pengeluaran
    ======================================== --}}
    <div class="bg-white rounded-3xl p-4 shadow-sm flex items-center gap-3 mb-6">
        <i class="bi bi-search text-yellow-500 text-xl"></i>
        <input type="text"
               id="searchInput"
               class="w-full bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400"
               placeholder="Cari pengeluaran...">
    </div>

    {{-- ========================================
         LIST PENGELUARAN
         Menampilkan kartu per item pengeluaran.
         State kosong ditampilkan jika tidak ada data.
    ======================================== --}}
    <div id="pengeluaranList">
        @if ($pengeluaran->isEmpty())
            {{-- State kosong: tidak ada data pengeluaran --}}
            <div class="flex flex-col items-center justify-center mt-20 opacity-80">
                <i class="bi bi-search text-[90px] text-yellow-400 drop-shadow"></i>
                <p class="text-lg font-semibold text-gray-600 mt-3">Data Tidak Ditemukan</p>
            </div>

        @else

            <div class="space-y-4" id="pengeluaranContainer">

                @foreach ($pengeluaran as $item)
                    <div class="pengeluaran-item bg-white rounded-2xl shadow-md hover:shadow-lg transition-all hover:-translate-y-0.5 flex items-center overflow-visible relative"
                         data-nama="{{ strtolower($item->nama_pengeluaran) }}"
                         data-tanggal="{{ $item->tanggal_pengeluaran }}"
                         data-nominal="{{ $item->nominal }}">

                        {{-- Garis Kuning Kiri --}}
                        <div class="absolute left-0 top-0 h-full w-2 bg-yellow-400 rounded-l-2xl flex-shrink-0"></div>

                        {{-- KONTEN --}}
                        <div class="flex-1 min-w-0 pl-6 pr-4 py-4">
                            <p class="text-sm font-semibold text-gray-500 mb-0.5">
                                {{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->locale('id')->translatedFormat('l, d/m/Y') : '-' }}
                            </p>
                            <p class="text-base font-bold text-gray-800 truncate uppercase tracking-wide">
                                {{ $item->nama_pengeluaran }}
                            </p>
                            <p class="text-sm font-bold text-yellow-600 mt-0.5">
                                Rp{{ number_format($item->nominal, 0, ',', '.') }}
                            </p>
                            @if($item->catatan)
                            <p class="text-xs text-gray-400 mt-1 truncate">{{ $item->catatan }}</p>
                            @endif
                        </div>

                        {{-- ACTIONS --}}
                        <div class="flex items-center gap-2 pr-4 flex-shrink-0">
                            <a href="{{ route('pengeluaran.edit', $item->id_pengeluaran) }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white w-10 h-10 rounded-xl flex items-center justify-center shadow-md hover:shadow-lg transition-all hover:scale-110">
                                <i class="bi bi-pencil-fill"></i>
                            </a>

                            {{-- Ganti: wrap button dalam form, pakai confirmDelete dari master --}}
                            <form action="{{ route('pengeluaran.destroy', $item->id_pengeluaran) }}"
                                method="POST"
                                style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        onclick="confirmDelete(this, 'pengeluaran')"
                                        data-nama="{{ $item->nama_pengeluaran }}"
                                        data-tanggal="{{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('d/m/Y') : '-' }}"
                                        data-harga="Rp{{ number_format($item->nominal, 0, ',', '.') }}"
                                        class="bg-red-500 hover:bg-red-600 text-white w-10 h-10 rounded-xl flex items-center justify-center shadow-md hover:shadow-lg transition-all hover:scale-110">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>

                        {{-- Hidden Form DELETE --}}
                        <form id="deleteForm{{ $item->id_pengeluaran }}"
                              action="{{ route('pengeluaran.destroy', $item->id_pengeluaran) }}"
                              method="POST"
                              style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>

                    </div>
                @endforeach

            </div>
        @endif
    </div>

    {{-- State kosong saat hasil pencarian tidak ditemukan --}}
    <div id="emptySearch" class="hidden flex flex-col items-center justify-center mt-20 opacity-80">
        <i class="bi bi-search text-[90px] text-yellow-400 drop-shadow"></i>
        <p class="text-lg font-semibold text-gray-600 mt-3">Tidak Ada Hasil</p>
        <p class="text-sm text-gray-500 mt-1">Coba kata kunci lain</p>
    </div>

</div>

@if(method_exists($pengeluaran, 'links'))
<div class="px-5 pb-24 bg-gray-100">
    {{ $pengeluaran->links() }}
</div>
@endif

{{-- ========================================
     TOMBOL TAMBAH PENGELUARAN (FIXED BOTTOM)
======================================== --}}
<div class="fixed bottom-0 left-0 w-full bg-gray-100 px-6 py-5 z-30 desktop-docked-bar lg:bottom-4 lg:rounded-[28px]">
    <a href="{{ route('pengeluaran.create') }}"
       class="w-full block text-center bg-yellow-400 text-black py-4 rounded-3xl text-lg font-bold shadow hover:bg-yellow-500 transition">
        Tambah Pengeluaran
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>

// SORT MENU TOGGLE
document.getElementById('sortBtn').addEventListener('click', function (e) {
    e.stopPropagation();
    document.getElementById('sortMenu').classList.toggle('hidden');
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('#sortBtn') && !e.target.closest('#sortMenu')) {
        document.getElementById('sortMenu')?.classList.add('hidden');
    }
});

// SEARCH
const searchInput          = document.getElementById('searchInput');
const emptySearch          = document.getElementById('emptySearch');
const pengeluaranContainer = document.getElementById('pengeluaranContainer');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const keyword  = this.value.toLowerCase().trim();
        let hasResults = false;

        document.querySelectorAll('.pengeluaran-item').forEach(function (item) {
            const match = item.dataset.nama.includes(keyword);
            item.style.display = match ? '' : 'none';
            if (match) hasResults = true;
        });

        const showEmpty = !hasResults && keyword !== '';
        pengeluaranContainer?.classList.toggle('hidden', showEmpty);
        emptySearch?.classList.toggle('hidden', !showEmpty);
    });
}

// SORT
function sortPengeluaran(type) {
    const container = document.getElementById('pengeluaranContainer');
    if (!container) return;

    const items = Array.from(container.querySelectorAll('.pengeluaran-item'));
    const labels = {
        'terbaru'          : 'Terbaru',
        'terlama'          : 'Terlama',
        'nominal-tertinggi': 'Nominal Tertinggi',
        'nominal-terendah' : 'Nominal Terendah'
    };
    document.getElementById('sortLabel').textContent = labels[type] || 'Terbaru';

    items.sort(function (a, b) {
        switch (type) {
            case 'terbaru'          : return new Date(b.dataset.tanggal) - new Date(a.dataset.tanggal);
            case 'terlama'          : return new Date(a.dataset.tanggal) - new Date(b.dataset.tanggal);
            case 'nominal-tertinggi': return parseFloat(b.dataset.nominal) - parseFloat(a.dataset.nominal);
            case 'nominal-terendah' : return parseFloat(a.dataset.nominal) - parseFloat(b.dataset.nominal);
            default: return 0;
        }
    });

    items.forEach(item => container.appendChild(item));
    document.getElementById('sortMenu').classList.add('hidden');
}

</script>
