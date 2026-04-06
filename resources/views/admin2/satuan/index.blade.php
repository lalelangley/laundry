@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- HEADER -->
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('admin2.dashboard') }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Kelola Satuan</span>
    </div>

    <div class="px-8 py-6 space-y-6">
        <!-- SEARCH + SORT -->
        <div class="flex items-center gap-3">
            <!-- SEARCH BOX -->
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Cari satuan..."
                    class="w-full pl-12 pr-4 py-4 rounded-xl bg-white shadow-md outline-none focus:ring-2 focus:ring-yellow-400 transition-all"
                    onkeyup="filterSatuan()"
                >
            </div>

            <!-- SORT BUTTON -->
            <button 
                id="sortBtn"
                class="bg-white px-6 py-4 rounded-xl shadow-md hover:shadow-lg flex items-center gap-2 hover:bg-gray-50 transition-all"
                onclick="toggleSort()"
            >
                <i id="sortIcon" class="bi bi-arrow-down-up text-xl"></i>
                <span class="font-semibold hidden sm:inline">Sort</span>
            </button>
        </div>

        <!-- LIST SATUAN -->
        <div id="satuanList" class="space-y-4">
            @forelse($satuan as $s)
            <div class="satuan-item bg-white p-5 rounded-2xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 flex items-center gap-4"
                 data-id="{{ $s->id_satuan }}">
                
                <!-- ICON -->
                <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-basket3 text-2xl text-yellow-600"></i>
                </div>

                <!-- NAMA SATUAN -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-500 mb-1">Nama Satuan</p>
                    <p class="nama text-lg font-bold text-gray-800 truncate">{{ $s->nama_satuan }}</p>
                </div>

                <!-- ACTIONS -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin2.satuan.edit', $s->id_satuan) }}" 
                       class="bg-green-700 hover:bg-blue-600 text-white w-10 h-10 rounded-xl flex items-center justify-center shadow-md hover:shadow-lg transition-all hover:scale-110">
                        <i class="bi bi-pencil-fill"></i>
                    </a>

                    <form action="{{ route('admin2.satuan.destroy', $s->id_satuan) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                onclick="confirmDelete(this, 'satuan')"
                                data-nama="{{ $s->nama_satuan }}"
                                class="bg-red-700 hover:bg-red-600 text-white w-10 h-10 rounded-xl flex items-center justify-center shadow-md hover:shadow-lg transition-all hover:scale-110">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full flex flex-col items-center justify-center py-16">
                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                    <i class="bi bi-box-seam text-5xl text-gray-400"></i>
                </div>
                <p class="text-xl text-gray-500 font-semibold">Tidak ada satuan</p>
                <p class="text-gray-400 text-sm mt-2">Silahkan tambahkan satuan baru</p>
            </div>
            @endforelse
        </div>

        @if(method_exists($satuan, 'links'))
        <div class="pt-2">
            {{ $satuan->links() }}
        </div>
        @endif

        <!-- ADD BUTTON -->
        @php
        $params = [];
        if (request()->has('from')) {
            $params['from'] = request('from');
        }
        if (request()->has('id_layanan')) {
            $params['id_layanan'] = request('id_layanan');
        }
        if (request()->has('id')) {
            $params['id'] = request('id');
        }
        @endphp

        <a href="{{ route('admin2.satuan.create', $params) }}"
           class="block bg-yellow-400 hover:bg-yellow-500 py-4 rounded-2xl font-bold text-black text-center shadow-lg hover:shadow-xl transition-all hover:scale-105 flex items-center justify-center gap-2">
            <i class="bi bi-plus-circle-fill text-xl"></i>
            Tambah Satuan
        </a>
    </div>
</div>

<!-- JAVASCRIPT SEARCH + SORT -->
<script>
function filterSatuan() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let items = document.querySelectorAll("#satuanList .satuan-item");

    items.forEach(item => {
        let nama = item.querySelector(".nama").innerText.toLowerCase();
        item.style.display = nama.includes(input) ? "" : "none";
    });
}

let sortAsc = true;

function toggleSort() {
    let items = [...document.querySelectorAll("#satuanList .satuan-item")];
    let list = document.getElementById("satuanList");

    items.sort((a, b) => {
        let namaA = a.querySelector(".nama").innerText.toLowerCase();
        let namaB = b.querySelector(".nama").innerText.toLowerCase();
        return sortAsc ? namaA.localeCompare(namaB) : namaB.localeCompare(namaA);
    });

    list.innerHTML = "";
    items.forEach(i => list.appendChild(i));

    document.getElementById("sortIcon").className = sortAsc 
        ? "bi bi-arrow-up text-xl"
        : "bi bi-arrow-down text-xl";

    sortAsc = !sortAsc;
}
</script>

@endsection
