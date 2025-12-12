@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
@php
    $backUrl = request('from') === 'transaksi'
        ? route('transaksi.create')
        : route('admin.dashboard');
@endphp

<a href="{{ $backUrl }}" class="text-black text-3xl font-bold">
    <i class="bi bi-arrow-left"></i>
</a>

    <span class="text-2xl font-bold">Kelola Layanan</span>
</div>

{{-- SEARCH --}}
<div class="px-4 mt-6 flex items-center justify-between">
    <div class="flex items-center gap-3 bg-white px-4 py-3 rounded-2xl shadow border border-gray-200 w-full">
        <i class="bi bi-search text-yellow-500 text-xl"></i>
        <input id="searchInput"
               type="text"
               placeholder="Cari"
               class="bg-transparent w-full text-base outline-none">
    </div>

    <button class="ml-3 text-gray-600 flex flex-col items-center text-[12px] leading-tight">
        <i class="bi bi-arrow-down-up text-2xl"></i>
        Sort
    </button>
</div>

{{-- LIST LAYANAN --}}
<div id="layananList" class="mx-4 mt-6 space-y-7">

@forelse ($layananUtama as $item)
<div
    class="layanan-item bg-white p-5 rounded-3xl shadow-md border border-gray-200 relative cursor-pointer"
    data-id="{{ $item->id_layanan }}"
    data-name="{{ $item->nama_layanan }}"
    data-mode="{{ $from === 'transaksi' ? 'transaksi' : 'edit' }}"
>

    {{-- STOP CLICK BUBBLE ON DROPDOWN --}}
    <div class="absolute right-4 top-4 z-50 dropdown-area">
        <button class="dropdown-btn text-gray-700 text-2xl">
            <i class="bi bi-three-dots-vertical"></i>
        </button>

        <ul class="dropdown-menu hidden absolute right-0 top-10 w-40 bg-yellow-400 rounded-2xl shadow-xl overflow-hidden py-1 z-50">
            <li>
                <a href="{{ route('layanan.duplicate', $item->id_layanan) }}"
                   class="flex items-center gap-2 px-4 py-3 text-black text-sm font-medium hover:bg-yellow-300">
                    <i class="bi bi-layers text-lg"></i> Duplikat
                </a>
            </li>

            <li>
                <form action="{{ route('layanan.destroy', $item->id_layanan) }}"
                      method="POST"
                      onsubmit="return confirm('Yakin hapus layanan ini?')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="w-full flex items-center gap-2 px-4 py-3 text-red-600 text-sm font-medium hover:bg-yellow-300">
                        <i class="bi bi-trash text-lg"></i> Hapus
                    </button>
                </form>
            </li>
        </ul>
    </div>

    {{-- NAMA LAYANAN --}}
    <p class="text-xl font-bold capitalize pr-10 mb-3">
        {{ $item->nama_layanan }}
    </p>

    {{-- ICON PROSES --}}
    @php
        $icons = [
            'Cuci' => 'bi bi-droplet',
            'Kering' => 'bi bi-wind',
            'Setrika' => 'bi bi-iron'
        ];

        $raw = $item->proses ?? '';

        // Jika sudah JSON array seperti '["Cuci","Kering"]' -> decode
        if (is_string($raw) && Str::startsWith(trim($raw), '[')) {
            $steps = json_decode($raw, true) ?: [];
        } else {
            // fallback: bisa berupa comma separated string atau string dengan tanda kutip
            $clean = trim($raw, "[]\"' ");
            $parts = $clean === '' ? [] : explode(',', $clean);
            $steps = array_map('trim', $parts);
        }

        // Pastikan tidak ada elemen kosong
        $steps = array_values(array_filter($steps, fn($s) => $s !== '' && $s !== null));
    @endphp

    <div class="flex items-center gap-2 mt-1 mb-4 text-[15px] font-semibold">
        @foreach($steps as $i => $step)
            <div class="flex items-center gap-1">
                <i class="{{ $icons[$step] ?? 'bi bi-gear' }} text-yellow-500 text-xl"></i>
                <span>{{ $step }}</span>
            </div>

            @if ($i < count($steps) - 1)
                <span class="text-gray-400 font-bold mx-1">››</span>
            @endif
        @endforeach
    </div>


    {{-- LIST JENIS --}}
    @if($item->jenis->count() > 0)
        <div class="space-y-4">
            @foreach ($item->jenis as $jenis)
            <div class="flex items-start gap-4">

                <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-100 flex-shrink-0">
                    <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}"
                         class="w-full h-full object-cover">
                </div>

                <div class="flex-1">
                    <p class="font-semibold text-[17px] capitalize">
                        {{ $jenis->nama_jenis }}
                    </p>

                    <p class="text-gray-700 text-[15px]">
                        Rp{{ number_format($jenis->harga,0,',','.') }} / {{ $jenis->satuan->nama_satuan ?? '-' }}
                    </p>

                    <p class="text-gray-500 text-[13px] flex items-center gap-1">
                        <i class="bi bi-clock text-base"></i>
                        {{ $jenis->lama }} {{ $jenis->lama_satuan }}
                    </p>
                </div>

            </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-400 text-sm">Belum ada jenis layanan</p>
    @endif

</div>
@empty
<p class="text-center text-gray-500">Tidak ada layanan.</p>
@endforelse
</div>

{{-- BUTTON TAMBAH --}}
<div class="px-4 mb-10">
    <a href="{{ route('layanan.create', ['from' => request('from')]) }}"
       class="block mt-6 bg-yellow-400 text-white text-lg font-bold py-3 rounded-2xl shadow-md text-center">
        <i class="bi bi-plus-circle"></i> Tambah Layanan
    </a>
</div>

{{-- ====================== MODAL ====================== --}}
<div id="modalLayanan"
     class="fixed inset-0 bg-black/40 hidden z-[9999] flex justify-center items-center p-4">

    <div id="modalBox"
         class="bg-yellow-400 w-full max-w-lg mx-auto p-6 rounded-3xl shadow-xl">

        <h2 id="modalTitle" class="text-2xl font-bold text-center mb-4"></h2>

        <label class="text-left block font-semibold">Masukan jumlah Kuantitas</label>

        <div class="flex items-center bg-white p-3 rounded-xl mt-2 mb-2 gap-3">
            <i class="bi bi-cup-straw text-2xl text-gray-500"></i>
            <input id="qtyInput" type="number"
                   class="w-full bg-transparent text-lg outline-none"
                   placeholder="Qty">
        </div>

        <p class="text-xs text-gray-700 mb-4">(Gunakan tanda titik (.) untuk angka desimal)</p>

        <label class="text-left block font-semibold">Pilih Parfum</label>

        <div class="bg-white rounded-xl mt-2 flex items-center px-3">
            <i class="bi bi-bag-heart-fill text-2xl text-red-500"></i>
            <select id="parfumSelect"
                    class="w-full p-3 bg-transparent text-lg outline-none">
                <option value="">Pilih Parfum</option>
                @foreach ($parfum as $p)
                    <option value="{{ $p->id_parfum }}">{{ $p->nama_parfum }}</option>
                @endforeach
            </select>
        </div>

        <button id="btnSave"
                class="bg-gray-900 text-white w-full mt-6 py-3 rounded-2xl text-lg font-bold shadow-lg">
            Simpan
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modalLayanan");
    const modalTitle = document.getElementById("modalTitle");
    const qtyInput = document.getElementById("qtyInput");
    const parfumSelect = document.getElementById("parfumSelect");
    const btnSave = document.getElementById("btnSave");

    function openModal(name, id) {
        modal.classList.remove("hidden");
        modalTitle.innerText = name;
        btnSave.dataset.id = id;
        qtyInput.value = "";
        parfumSelect.value = "";
    }

    function closeModal() {
        modal.classList.add("hidden");
    }

    modal.addEventListener("click", e => {
        if (e.target === modal) closeModal();
    });

    document.querySelectorAll(".layanan-item").forEach(card => {
    card.addEventListener("click", function () {

        const mode = this.dataset.mode;
        const id   = this.dataset.id;
        const name = this.dataset.name;

        if (mode === "edit") {
            // ➜ Jika dari kelola layanan → buka halaman edit
            window.location.href = `/admin/layanan/${id}/edit`;
            return;
        }

        if (mode === "transaksi") {
            // ➜ Jika dari transaksi → buka popup qty
            openModal(name, id);
        }

    });
});


    document.querySelectorAll(".dropdown-area").forEach(area => {
        area.addEventListener("click", function(e){
            e.stopPropagation();
        });
    });

    btnSave.addEventListener("click", () => {

        const qty = qtyInput.value.trim();
        const parfum = parfumSelect.value;

        if (!qty || qty <= 0) {
            alert("Masukkan qty valid.");
            return;
        }

        const id = btnSave.dataset.id;

        const parfumNama = parfumSelect.options[parfumSelect.selectedIndex].text;
        const url = `/admin/transaksi/add-layanan/${id}?qty=${qty}&parfum=${parfum}&parfum_nama=${encodeURIComponent(parfumNama)}`;


        fetch(url)
            .then(res => res.json())
            .then(() => {
                closeModal();
                window.location.href = "/admin/transaksi/create";
            })
            .catch(err => {
                console.error("ERROR:", err);
                alert("Gagal menambah layanan.");
            });
    });
    document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", function (e) {
        e.stopPropagation();

        const menu = this.nextElementSibling;

        // Tutup semua menu lain biar ga numpuk
        document.querySelectorAll(".dropdown-menu").forEach(m => {
            if (m !== menu) m.classList.add("hidden");
        });

        // Toggle menu ini
        menu.classList.toggle("hidden");
    });
});

// Biar klik di dalam menu tidak memicu klik kartu
document.querySelectorAll(".dropdown-menu").forEach(menu => {
    menu.addEventListener("click", function (e) {
        e.stopPropagation();
    });
});


});
</script>
@endsection
