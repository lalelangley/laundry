@extends('layouts.master')

@section('content')

<!-- HEADER -->
<div class="w-full bg-yellow-400 p-4 flex items-center">

    {{-- TOMBOL BACK --}}
    <a href="{{ route('admin.dashboard') }}" class="text-2xl mr-3">←</a>

    <h1 class="text-xl font-bold">Kelola Parfum</h1>
</div>

<div class="p-4 bg-gray-100 min-h-screen">

    <!-- SEARCH + SORT -->
    <div class="flex items-center gap-3 mb-4">

        <!-- SEARCH BOX -->
        <div class="flex items-center bg-white px-4 py-2 rounded-xl shadow w-full">
            <i class="bi bi-search text-gray-400 text-lg mr-2"></i>
            <input 
                type="text" 
                id="searchInput"
                placeholder="Cari"
                class="flex-1 bg-transparent focus:outline-none"
                onkeyup="filterParfum()"
            >
        </div>

        <!-- SORT BUTTON -->
        <button 
            id="sortBtn"
            class="bg-white px-4 py-2 rounded-xl shadow flex items-center gap-2"
            onclick="toggleSort()"
        >
            <i id="sortIcon" class="bi bi-arrow-down-up text-xl"></i>
            <span class="text-sm font-semibold">Sort</span>
        </button>

    </div>

    <!-- LIST PARFUM -->
    <div id="parfumList">
        @foreach($parfum as $p)
        <div class="parfum-item bg-white p-4 mb-3 flex items-center rounded-xl shadow">
            <span class="text-3xl mr-3">🧴</span>

            <p class="nama font-semibold flex-1">{{ $p->nama_parfum }}</p>

            <a href="{{ route('parfum.edit', $p->id_parfum) }}" class="text-blue-500 text-xl mr-3">
                <i class="bi bi-pencil-square"></i>
            </a>

            <form action="{{ route('parfum.destroy',$p->id_parfum) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="text-red-500 text-xl">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
        @endforeach
    </div>

    <!-- ADD BUTTON -->
    <a href="{{ route('parfum.create') }}">
        <button class="w-full bg-yellow-400 py-3 rounded-full mt-6 font-bold text-white">
            Tambah Parfum
        </button>
    </a>

</div>

<!-- JAVASCRIPT SEARCH + SORT -->
<script>
function filterParfum() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let items = document.querySelectorAll("#parfumList .parfum-item");

    items.forEach(item => {
        let nama = item.querySelector(".nama").innerText.toLowerCase();
        item.style.display = nama.includes(input) ? "" : "none";
    });
}

let sortAsc = true;

function toggleSort() {
    let items = [...document.querySelectorAll("#parfumList .parfum-item")];
    let list = document.getElementById("parfumList");

    items.sort((a, b) => {
        let namaA = a.querySelector(".nama").innerText.toLowerCase();
        let namaB = b.querySelector(".nama").innerText.toLowerCase();
        return sortAsc ? namaA.localeCompare(namaB) : namaB.localeCompare(namaA);
    });

    list.innerHTML = "";
    items.forEach(i => list.appendChild(i));

    // Ubah ikon sort
    document.getElementById("sortIcon").className = sortAsc 
        ? "bi bi-arrow-up-short text-xl"
        : "bi bi-arrow-down-short text-xl";

    sortAsc = !sortAsc;
}
</script>

@endsection
