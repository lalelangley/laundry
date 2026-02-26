@extends('layouts.master')

@section('title', 'pengeluaran')

@section('content')

<!-- HEADER -->
<div class="w-full bg-yellow-400 p-4 flex items-center justify-between rounded-b-3xl shadow-md">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold hover:opacity-70 transition">
            ←
        </a>
        <h1 class="text-xl font-bold tracking-wide">List Pengeluaran</h1>
    </div>

    <!-- Sort Dropdown -->
    <div class="relative">
        <button id="sortBtn" class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-md hover:shadow-lg transition">
            <i class="bi bi-funnel text-yellow-500"></i>
            <span class="text-sm font-semibold" id="sortLabel">Terbaru</span>
            <i class="bi bi-chevron-down text-sm"></i>
        </button>
        
        <div id="sortMenu" class="hidden absolute right-0 top-12 w-48 bg-white rounded-2xl shadow-xl overflow-hidden z-50">
            <button onclick="sortPengeluaran('terbaru')" class="sort-option w-full text-left px-4 py-3 hover:bg-yellow-50 transition flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-sort-down-alt text-yellow-500"></i>
                Terbaru
            </button>
            <button onclick="sortPengeluaran('terlama')" class="sort-option w-full text-left px-4 py-3 hover:bg-yellow-50 transition flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-sort-up text-yellow-500"></i>
                Terlama
            </button>
            <button onclick="sortPengeluaran('nominal-tertinggi')" class="sort-option w-full text-left px-4 py-3 hover:bg-yellow-50 transition flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-arrow-up-circle text-yellow-500"></i>
                Nominal Tertinggi
            </button>
            <button onclick="sortPengeluaran('nominal-terendah')" class="sort-option w-full text-left px-4 py-3 hover:bg-yellow-50 transition flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-arrow-down-circle text-yellow-500"></i>
                Nominal Terendah
            </button>
        </div>
    </div>
</div>

{{-- WRAPPER --}}
<div class="p-5 pb-[150px] bg-gray-100 min-h-screen">

    {{-- SEARCH --}}
    <div class="bg-white rounded-3xl p-4 shadow-sm flex items-center gap-3 mb-6">
        <i class="bi bi-search text-yellow-500 text-xl"></i>
        <input type="text"
               id="searchInput"
               class="w-full bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400"
               placeholder="Cari pengeluaran...">
    </div>

    {{-- LIST / EMPTY STATE --}}
    <div id="pengeluaranList">
        @if ($pengeluaran->isEmpty())

            <div class="flex flex-col items-center justify-center mt-20 opacity-80">
                <i class="bi bi-search text-[90px] text-yellow-400 drop-shadow"></i>
                <p class="text-lg font-semibold text-gray-600 mt-3">Data Tidak Ditemukan</p>
            </div>

        @else

            <div class="space-y-5" id="pengeluaranContainer">

                @foreach ($pengeluaran as $item)
                    {{-- ✅ overflow-visible supaya dropdown tidak terpotong card --}}
                    <div class="pengeluaran-item bg-white p-6 rounded-3xl shadow-md relative border overflow-visible"
                         data-nama="{{ strtolower($item->nama_pengeluaran) }}"
                         data-tanggal="{{ $item->tanggal_pengeluaran }}"
                         data-nominal="{{ $item->nominal }}">

                        {{-- Garis Kuning Kiri --}}
                        <div class="absolute left-0 top-0 h-full w-2 bg-yellow-400 rounded-l-3xl"></div>

                        {{-- Dropdown Menu --}}
                        {{-- ✅ style z-index pakai inline supaya tidak dibatasi stacking context --}}
                        <div class="absolute right-4 top-4" style="z-index: 100;">
                            <button class="dropdown-btn text-gray-700 text-2xl hover:text-yellow-600 transition">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            {{-- ✅ z-index tinggi via inline style --}}
                            <ul class="dropdown-menu hidden absolute right-0 top-10 w-40 bg-yellow-400 rounded-2xl shadow-xl overflow-hidden py-1" style="z-index: 9999;">
                                <li>
                                    {{-- ✅ stopPropagation di Edit supaya tidak nutup dropdown sebelum navigate --}}
                                    <a href="{{ route('pengeluaran.edit', $item->id_pengeluaran) }}"
                                       onclick="event.stopPropagation()"
                                       class="flex items-center gap-2 px-4 py-3 text-black text-sm font-medium hover:bg-yellow-300">
                                        <i class="bi bi-pencil text-lg"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    {{-- ✅ stopPropagation di Hapus supaya event tidak bubble ke document --}}
                                    <button type="button"
                                        class="btn-delete w-full flex items-center gap-2 px-4 py-3 text-red-600 text-sm font-medium hover:bg-yellow-300"
                                        data-id="{{ $item->id_pengeluaran }}"
                                        data-nama="{{ $item->nama_pengeluaran }}"
                                        data-nominal="{{ number_format($item->nominal, 0, ',', '.') }}"
                                        data-tanggal="{{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('d/m/Y') : '-' }}">
                                        <i class="bi bi-trash text-lg"></i> Hapus
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="flex justify-between items-start">

                            {{-- TEXT --}}
                            <div class="ml-4 w-full pr-12">

                                {{-- JUDUL --}}
                                <p class="font-extrabold text-lg uppercase tracking-wide text-gray-800 leading-tight">
                                    {{ $item->nama_pengeluaran }}
                                </p>

                                {{-- TGL --}}
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('l, d/m/Y') : '-' }}
                                </p>

                                {{-- CATATAN --}}
                                @if($item->catatan)
                                <div class="bg-gray-100 p-3 rounded-xl mt-3 border border-gray-200">
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        {{ $item->catatan }}
                                    </p>
                                </div>
                                @endif

                                {{-- NOMINAL --}}
                                <p class="font-bold mt-4 text-lg text-gray-800">
                                    Rp{{ number_format($item->nominal, 0, ',', '.') }}
                                </p>

                            </div>

                        </div>

                        {{-- Hidden Form DELETE ada di dalam card supaya ikut saat sort --}}
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

    <!-- Empty Search State -->
    <div id="emptySearch" class="hidden flex flex-col items-center justify-center mt-20 opacity-80">
        <i class="bi bi-search text-[90px] text-yellow-400 drop-shadow"></i>
        <p class="text-lg font-semibold text-gray-600 mt-3">Tidak Ada Hasil</p>
        <p class="text-sm text-gray-500 mt-1">Coba kata kunci lain</p>
    </div>

</div>

{{-- BUTTON TAMBAH --}}
<div class="fixed bottom-0 left-0 w-full bg-gray-100 px-6 py-5 z-30">
    <a href="{{ route('pengeluaran.create') }}"
       class="w-full block text-center bg-yellow-400 text-black py-4 rounded-3xl text-lg font-bold shadow hover:bg-yellow-500 transition">
        Tambah Pengeluaran
    </a>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ==================== DROPDOWN TOGGLE (event delegation) ====================
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.dropdown-btn');

    if (btn) {
        e.stopPropagation();
        const targetMenu = btn.nextElementSibling;

        // Tutup semua dropdown lain
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m !== targetMenu) m.classList.add('hidden');
        });

        // Toggle dropdown yang diklik
        targetMenu.classList.toggle('hidden');
        return;
    }

    // Klik di luar dropdown & sort menu: tutup semua
    // ✅ Cek apakah klik berasal dari dalam .dropdown-menu
    // Kalau iya, JANGAN tutup supaya onclick di tombol sempat terpanggil
    const insideMenu = e.target.closest('.dropdown-menu');
    if (!insideMenu) {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
    }

    if (!e.target.closest('#sortBtn') && !e.target.closest('#sortMenu')) {
        document.getElementById('sortMenu').classList.add('hidden');
    }
});

// ==================== SORT MENU TOGGLE ====================
const sortBtn = document.getElementById('sortBtn');
const sortMenu = document.getElementById('sortMenu');

sortBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    sortMenu.classList.toggle('hidden');
});

// ==================== SEARCH FUNCTION ====================
const searchInput = document.getElementById('searchInput');
const emptySearch = document.getElementById('emptySearch');
const pengeluaranContainer = document.getElementById('pengeluaranContainer');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let hasResults = false;

        document.querySelectorAll('.pengeluaran-item').forEach(item => {
            const nama = item.dataset.nama;
            if (nama.includes(searchTerm)) {
                item.style.display = 'block';
                hasResults = true;
            } else {
                item.style.display = 'none';
            }
        });

        if (!hasResults && searchTerm !== '') {
            pengeluaranContainer.classList.add('hidden');
            emptySearch.classList.remove('hidden');
        } else {
            pengeluaranContainer.classList.remove('hidden');
            emptySearch.classList.add('hidden');
        }
    });
}

// ==================== DELETE BUTTON HANDLER ====================
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation(); // 🔥 ini kunci biar dropdown gak nutup dulu

        const id = this.dataset.id;
        const nama = this.dataset.nama;
        const nominal = this.dataset.nominal;
        const tanggal = this.dataset.tanggal;

        confirmDelete(id, nama, nominal, tanggal);
    });
});

// ==================== SORT FUNCTION ====================
function sortPengeluaran(type) {
    const container = document.getElementById('pengeluaranContainer');
    const items = Array.from(document.querySelectorAll('.pengeluaran-item'));
    const sortLabel = document.getElementById('sortLabel');

    items.sort((a, b) => {
        switch(type) {
            case 'terbaru':
                sortLabel.textContent = 'Terbaru';
                return new Date(b.dataset.tanggal) - new Date(a.dataset.tanggal);
            case 'terlama':
                sortLabel.textContent = 'Terlama';
                return new Date(a.dataset.tanggal) - new Date(b.dataset.tanggal);
            case 'nominal-tertinggi':
                sortLabel.textContent = 'Nominal Tertinggi';
                return parseFloat(b.dataset.nominal) - parseFloat(a.dataset.nominal);
            case 'nominal-terendah':
                sortLabel.textContent = 'Nominal Terendah';
                return parseFloat(a.dataset.nominal) - parseFloat(b.dataset.nominal);
            default:
                return 0;
        }
    });

    container.innerHTML = '';
    items.forEach(item => container.appendChild(item));

    sortMenu.classList.add('hidden');
}

// ==================== CONFIRM DELETE FUNCTION ====================
function confirmDelete(id, nama, nominal, tanggal) {
    // Tutup semua dropdown dulu
    document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));

    Swal.fire({
        title: 'Hapus Pengeluaran?',
        html: `
            <div class="text-left">
                <p class="text-gray-600 mb-3">Anda akan menghapus pengeluaran berikut:</p>
                <div class="bg-gradient-to-br from-red-50 to-orange-50 border-2 border-red-200 rounded-2xl p-4 my-4 shadow-sm">
                    <div class="space-y-2">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-tag-fill text-red-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Nama Pengeluaran</p>
                                <p class="font-bold text-gray-800 text-base">${nama}</p>
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
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 mt-3">
                    <p class="text-sm text-blue-700 flex items-start gap-2">
                        <i class="bi bi-exclamation-circle text-blue-500 text-lg mt-0.5"></i>
                        <span>Data pengeluaran yang sudah dihapus tidak dapat dikembalikan.</span>
                    </p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-trash-fill me-2"></i>Ya, Hapus!',
        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Batal',
        reverseButtons: true,
        width: '550px',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:shadow-xl',
            cancelButton: 'rounded-xl px-6 py-3 font-bold'
        },
        backdrop: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus Pengeluaran...',
                html: `
                    <div class="flex flex-col items-center gap-3">
                        <i class="bi bi-hourglass-split text-5xl text-yellow-500 animate-pulse"></i>
                        <p class="text-gray-600">Mohon tunggu sebentar</p>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const form = document.getElementById('deleteForm' + id);
            if (form) {
                form.submit();
            } else {
                Swal.fire('Error', 'Form tidak ditemukan!', 'error');
            }
        }
    });
}

// ==================== SUCCESS/ERROR ALERTS ====================
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        showConfirmButton: false,
        timer: 2000,
        customClass: {
            popup: 'rounded-2xl'
        }
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        confirmButtonColor: '#dc2626',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold'
        }
    });
@endif
</script>

@endsection