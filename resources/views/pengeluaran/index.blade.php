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
            <div class="space-y-5" id="pengeluaranContainer">

                @foreach ($pengeluaran as $item)
                    {{-- 
                        KARTU PENGELUARAN
                        overflow-visible diperlukan agar dropdown tidak terpotong border card.
                        data-* digunakan untuk sort, search, dan konfirmasi hapus.
                    --}}
                    <div class="pengeluaran-item bg-white p-6 rounded-3xl shadow-md relative border overflow-visible"
                         data-nama="{{ strtolower($item->nama_pengeluaran) }}"
                         data-tanggal="{{ $item->tanggal_pengeluaran }}"
                         data-nominal="{{ $item->nominal }}">

                        {{-- Aksen garis kuning di sisi kiri kartu --}}
                        <div class="absolute left-0 top-0 h-full w-2 bg-yellow-400 rounded-l-3xl"></div>

                        {{-- 
                            DROPDOWN AKSI (Edit / Hapus)
                            z-index inline tinggi untuk mencegah dropdown terpotong 
                            oleh stacking context kartu lain.
                        --}}
                        <div class="absolute right-4 top-4" style="z-index: 100;">
                            <button class="dropdown-btn text-gray-700 text-2xl hover:text-yellow-600 transition">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            {{-- 
                                FIX BUG: dropdown-menu tidak menggunakan overflow-hidden
                                agar klik pada tombol di dalamnya tidak terpotong event.
                                z-index 9999 via inline style untuk jaminan di atas elemen lain.
                            --}}
                            <ul class="dropdown-menu hidden absolute right-0 top-10 w-40 bg-yellow-400 rounded-2xl shadow-xl py-1" style="z-index: 9999;">
                                <li>
                                    <a href="{{ route('pengeluaran.edit', $item->id_pengeluaran) }}"
                                       class="flex items-center gap-2 px-4 py-3 text-black text-sm font-medium hover:bg-yellow-300">
                                        <i class="bi bi-pencil text-lg"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    {{-- 
                                        FIX BUG: Tombol hapus sekarang menggunakan onclick langsung
                                        (bukan event listener terpisah yang mungkin tidak terpanggil
                                        karena dropdown sudah ditutup lebih dulu oleh document click).
                                        Data diteruskan langsung ke fungsi confirmDelete().
                                    --}}
                                   <button type="button"
                                        class="btn-delete w-full flex items-center gap-2 px-4 py-3 text-red-500 text-sm font-medium hover:bg-red-50 transition"
                                        data-id="{{ $item->id_pengeluaran }}"
                                        data-nama="{{ addslashes($item->nama_pengeluaran) }}"
                                        data-nominal="{{ number_format($item->nominal, 0, ',', '.') }}"
                                        data-tanggal="{{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('d/m/Y') : '-' }}">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </li>
                            </ul>
                        </div>

                        {{-- Konten kartu: nama, tanggal, catatan, nominal --}}
                        <div class="flex justify-between items-start">
                            <div class="ml-4 w-full pr-12">

                                {{-- Nama pengeluaran --}}
                                <p class="font-extrabold text-lg uppercase tracking-wide text-gray-800 leading-tight">
                                    {{ $item->nama_pengeluaran }}
                                </p>

                                {{-- Tanggal pengeluaran --}}
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $item->tanggal_pengeluaran
                                        ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('l, d/m/Y')
                                        : '-' }}
                                </p>

                                {{-- Catatan (opsional, hanya tampil jika ada) --}}
                                @if($item->catatan)
                                    <div class="bg-gray-100 p-3 rounded-xl mt-3 border border-gray-200">
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            {{ $item->catatan }}
                                        </p>
                                    </div>
                                @endif

                                {{-- Nominal pengeluaran --}}
                                <p class="font-bold mt-4 text-lg text-gray-800">
                                    Rp{{ number_format($item->nominal, 0, ',', '.') }}
                                </p>

                            </div>
                        </div>

                        {{-- 
                            HIDDEN FORM DELETE
                            Form ini diletakkan DI LUAR container sort agar tidak
                            ikut terhapus saat sort memanggil container.innerHTML = ''.
                            Dengan meletakkan di dalam card, form ikut berpindah saat sort.
                        --}}
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

{{-- ========================================
     TOMBOL TAMBAH PENGELUARAN (FIXED BOTTOM)
======================================== --}}
<div class="fixed bottom-0 left-0 w-full bg-gray-100 px-6 py-5 z-30">
    <a href="{{ route('pengeluaran.create') }}"
       class="w-full block text-center bg-yellow-400 text-black py-4 rounded-3xl text-lg font-bold shadow hover:bg-yellow-500 transition">
        Tambah Pengeluaran
    </a>
</div>

{{-- SweetAlert2 untuk dialog konfirmasi hapus --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

// =============================
// DROPDOWN TOGGLE
// =============================
document.addEventListener('click', function (e) {
    const deleteBtn = e.target.closest('.btn-delete');
    const dropBtn   = e.target.closest('.dropdown-btn');

    if (deleteBtn) {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
        confirmDelete(
            deleteBtn.dataset.id,
            deleteBtn.dataset.nama,
            deleteBtn.dataset.nominal,
            deleteBtn.dataset.tanggal
        );
        return;
    }

    if (dropBtn) {
        e.stopPropagation();
        const menu = dropBtn.nextElementSibling;
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m !== menu) m.classList.add('hidden');
        });
        menu.classList.toggle('hidden');
        return;
    }

    document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));

    if (!e.target.closest('#sortBtn') && !e.target.closest('#sortMenu')) {
        document.getElementById('sortMenu')?.classList.add('hidden');
    }
});

// =============================
// SORT MENU
// =============================
document.getElementById('sortBtn').addEventListener('click', function (e) {
    e.stopPropagation();
    document.getElementById('sortMenu').classList.toggle('hidden');
});

// =============================
// SORT FUNCTION
// Pakai appendChild bukan innerHTML = '' agar form tidak hilang
// =============================
function sortPengeluaran(type) {
    const container = document.getElementById('pengeluaranContainer');
    if (!container) return;

    const items = Array.from(container.querySelectorAll('.pengeluaran-item'));

    const labels = {
        'terbaru': 'Terbaru',
        'terlama': 'Terlama',
        'nominal-tertinggi': 'Nominal Tertinggi',
        'nominal-terendah': 'Nominal Terendah'
    };
    document.getElementById('sortLabel').textContent = labels[type] || 'Terbaru';

    items.sort(function (a, b) {
        switch (type) {
            case 'terbaru':
                return new Date(b.dataset.tanggal) - new Date(a.dataset.tanggal);
            case 'terlama':
                return new Date(a.dataset.tanggal) - new Date(b.dataset.tanggal);
            case 'nominal-tertinggi':
                return parseFloat(b.dataset.nominal) - parseFloat(a.dataset.nominal);
            case 'nominal-terendah':
                return parseFloat(a.dataset.nominal) - parseFloat(b.dataset.nominal);
            default:
                return 0;
        }
    });

    // Pindahkan node langsung tanpa hapus DOM
    items.forEach(item => container.appendChild(item));

    document.getElementById('sortMenu').classList.add('hidden');
}

// =============================
// SEARCH
// =============================
const searchInput          = document.getElementById('searchInput');
const emptySearch          = document.getElementById('emptySearch');
const pengeluaranContainer = document.getElementById('pengeluaranContainer');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const keyword    = this.value.toLowerCase().trim();
        let hasResults   = false;

        document.querySelectorAll('.pengeluaran-item').forEach(function (item) {
            const match = item.dataset.nama.includes(keyword);
            item.style.display = match ? '' : 'none';
            if (match) hasResults = true;
        });

        const showEmpty = !hasResults && keyword !== '';
        pengeluaranContainer?.classList.toggle('hidden', showEmpty);
        emptySearch.classList.toggle('hidden', !showEmpty);
    });
}

// =============================
// CONFIRM DELETE
// =============================
function confirmDelete(id, nama, nominal, tanggal) {
    Swal.fire({
        title: 'Hapus Pengeluaran?',
        html: `
            <div class="text-left">
                <p class="text-gray-600 mb-3">Anda akan menghapus pengeluaran berikut:</p>
                <div class="border-2 border-red-200 rounded-2xl p-4 my-4 bg-red-50">
                    <div class="space-y-2">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-tag-fill text-red-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Nama Pengeluaran</p>
                                <p class="font-bold text-gray-800">${nama}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="bi bi-calendar3 text-blue-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Tanggal</p>
                                <p class="font-semibold text-gray-700">${tanggal}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="bi bi-cash-coin text-yellow-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Nominal</p>
                                <p class="font-bold text-yellow-700 text-lg">Rp ${nominal}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-xl p-3">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Data yang sudah dihapus tidak dapat dikembalikan.
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor:  '#6b7280',
        confirmButtonText:  '<i class="bi bi-trash-fill me-2"></i>Ya, Hapus!',
        cancelButtonText:   '<i class="bi bi-x-circle me-2"></i>Batal',
        reverseButtons: true,
        width: '520px',
        customClass: {
            popup:         'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold',
            cancelButton:  'rounded-xl px-6 py-3 font-bold'
        }
    }).then(function (result) {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Menghapus...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        const form = document.getElementById('deleteForm' + id);
        if (form) {
            form.submit();
        } else {
            Swal.fire('Error', 'Form tidak ditemukan!', 'error');
        }
    });
}

// =============================
// FLASH MESSAGE
// =============================
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: @json(session('success')),
        showConfirmButton: false,
        timer: 2000,
        customClass: { popup: 'rounded-2xl' }
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: @json(session('error')),
        confirmButtonColor: '#dc2626',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold'
        }
    });
@endif
</script>

@endsection