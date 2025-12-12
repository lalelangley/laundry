@extends('layouts.master')

@section('content')

<div class="min-h-screen bg-gray-50 pb-24">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        @php
            $backUrl = request('from') === 'transaksi'
                ? route('transaksi.create')
                : route('admin.dashboard');
        @endphp

        <a href="{{ $backUrl }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>

        <span class="text-2xl font-bold">Kelola Layanan</span>
    </div>

    <div class="px-8 py-6 space-y-6">
        {{-- SEARCH + SORT --}}
        <div class="flex items-center gap-3">
            <!-- SEARCH BOX -->
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                <input id="searchInput"
                       type="text"
                       placeholder="Cari layanan..."
                       class="w-full pl-12 pr-4 py-4 rounded-xl bg-white shadow-md outline-none focus:ring-2 focus:ring-yellow-400 transition-all">
            </div>

            <!-- SORT BUTTON -->
            <button class="bg-white px-6 py-4 rounded-xl shadow-md hover:shadow-lg flex items-center gap-2 hover:bg-gray-50 transition-all">
                <i class="bi bi-arrow-down-up text-xl"></i>
                <span class="font-semibold hidden sm:inline">Sort</span>
            </button>
        </div>

        {{-- LIST LAYANAN --}}
        <div id="layananList" class="space-y-5">
            @forelse ($layananUtama as $item)
            <div class="layanan-item bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 relative group"
                 data-id="{{ $item->id_layanan }}"
                 data-name="{{ $item->nama_layanan }}"
                 data-mode="{{ request('from') === 'transaksi' ? 'transaksi' : 'edit' }}">

                <div class="p-6">
                    {{-- HEADER CARD --}}
                    <div class="flex items-start justify-between mb-4 pb-4 border-b-2 border-gray-100">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 capitalize mb-2">
                                {{ $item->nama_layanan }}
                            </h3>

                            {{-- ICON PROSES --}}
                            @php
                                $icons = [
                                    'Cuci' => 'bi bi-droplet',
                                    'Kering' => 'bi bi-wind',
                                    'Setrika' => 'bi bi-iron'
                                ];

                                $raw = $item->proses ?? '';

                                if (is_string($raw) && Str::startsWith(trim($raw), '[')) {
                                    $steps = json_decode($raw, true) ?: [];
                                } else {
                                    $clean = trim($raw, "[]\"' ");
                                    $parts = $clean === '' ? [] : explode(',', $clean);
                                    $steps = array_map('trim', $parts);
                                }

                                $steps = array_values(array_filter($steps, fn($s) => $s !== '' && $s !== null));
                            @endphp

                            <div class="flex items-center gap-2 flex-wrap">
                                @foreach($steps as $i => $step)
                                    <div class="flex items-center gap-1.5 bg-yellow-50 px-3 py-1.5 rounded-lg">
                                        <i class="{{ $icons[$step] ?? 'bi bi-gear' }} text-yellow-600 text-lg"></i>
                                        <span class="text-sm font-semibold text-gray-700">{{ $step }}</span>
                                    </div>

                                    @if ($i < count($steps) - 1)
                                        <i class="bi bi-chevron-right text-gray-300"></i>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- DROPDOWN MENU --}}
                        <div class="dropdown-area relative">
                            <button class="dropdown-btn text-gray-600 hover:text-gray-800 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="bi bi-three-dots-vertical text-xl"></i>
                            </button>

                            <ul class="dropdown-menu hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 z-50">
                                <li>
                                    <button onclick="confirmDuplicate('{{ route('layanan.duplicate', $item->id_layanan) }}')"
                                       class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 transition-colors">
                                        <i class="bi bi-layers text-lg text-blue-600"></i>
                                        <span class="font-medium">Duplikat</span>
                                    </button>
                                </li>

                                <li class="border-t border-gray-100">
                                    <button onclick="confirmDelete('{{ route('layanan.destroy', $item->id_layanan) }}')"
                                            class="w-full flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="bi bi-trash text-lg"></i>
                                        <span class="font-medium">Hapus</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- LIST JENIS --}}
                    @if($item->jenis->count() > 0)
                        <div class="space-y-4">
                            @foreach ($item->jenis as $jenis)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                <!-- GAMBAR -->
                                <div class="w-20 h-20 rounded-xl overflow-hidden bg-white border-2 border-gray-200 flex-shrink-0">
                                    <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}"
                                         class="w-full h-full object-cover"
                                         alt="{{ $jenis->nama_jenis }}">
                                </div>

                                <!-- INFO -->
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-lg text-gray-800 capitalize mb-1 truncate">
                                        {{ $jenis->nama_jenis }}
                                    </p>

                                    <p class="text-green-600 font-semibold mb-2">
                                        Rp {{ number_format($jenis->harga, 0, ',', '.') }} / {{ $jenis->satuan->nama_satuan ?? '-' }}
                                    </p>

                                    <div class="flex items-center gap-1.5 text-gray-500 text-sm">
                                        <i class="bi bi-clock"></i>
                                        <span>{{ $jenis->lama }} {{ $jenis->lama_satuan }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="bi bi-inbox text-4xl text-gray-300 mb-2"></i>
                            <p class="text-gray-400 text-sm">Belum ada jenis layanan</p>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full flex flex-col items-center justify-center py-16">
                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                    <i class="bi bi-gear-wide-connected text-5xl text-gray-400"></i>
                </div>
                <p class="text-xl text-gray-500 font-semibold">Tidak ada layanan</p>
                <p class="text-gray-400 text-sm mt-2">Silahkan tambahkan layanan baru</p>
            </div>
            @endforelse
        </div>

        {{-- BUTTON TAMBAH --}}
        <a href="{{ route('layanan.create', ['from' => request('from')]) }}"
           class="block bg-yellow-400 hover:bg-yellow-500 py-4 rounded-2xl font-bold text-black text-center shadow-lg hover:shadow-xl transition-all hover:scale-105 flex items-center justify-center gap-2">
            <i class="bi bi-plus-circle-fill text-xl"></i>
            Tambah Layanan
        </a>
    </div>
</div>

{{-- ====================== MODAL LAYANAN ====================== --}}
<div id="modalLayanan"
  class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">

    <div id="modalBox"
         class="bg-white w-full max-w-lg mx-auto rounded-2xl shadow-2xl overflow-hidden">

        <!-- MODAL HEADER -->
        <div class="bg-yellow-400 p-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-black text-center"></h2>
        </div>

        <!-- MODAL BODY -->
        <div class="p-6 space-y-5">
            <!-- QTY INPUT -->
            <div>
                <label class="block font-bold text-gray-700 mb-2">Jumlah Kuantitas</label>
                <div class="relative">
                    <i class="bi bi-123 absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                    <input id="qtyInput" type="number" step="0.01"
                           class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all"
                           placeholder="Masukkan qty">
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="bi bi-info-circle"></i> Gunakan tanda titik (.) untuk angka desimal
                </p>
            </div>

            <!-- PARFUM SELECT -->
            <div>
                <label class="block font-bold text-gray-700 mb-2">Pilih Parfum</label>
                <div class="relative">
                    <i class="bi bi-flower1 absolute left-4 top-1/2 transform -translate-y-1/2 text-pink-400 text-xl z-10"></i>
                    <select id="parfumSelect"
                            class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all appearance-none bg-white">
                        <option value="">Pilih Parfum</option>
                        @foreach ($parfum as $p)
                            <option value="{{ $p->id_parfum }}">{{ $p->nama_parfum }}</option>
                        @endforeach
                    </select>
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="flex gap-3 pt-2">
                <button onclick="document.getElementById('modalLayanan').classList.add('hidden')"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">
                    Batal
                </button>
                <button id="btnSave"
                        class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-black py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ====================== MODAL KONFIRMASI DUPLIKAT ====================== --}}
<div id="modalDuplicate" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-blue-500 p-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="bi bi-layers text-3xl"></i>
                Konfirmasi Duplikat
            </h2>
        </div>
        
        <div class="p-6">
            <p class="text-gray-700 text-lg mb-6">Apakah Anda yakin ingin menduplikat layanan ini?</p>
            
            <div class="flex gap-3">
                <button onclick="closeDuplicateModal()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">
                    Batal
                </button>
                <button id="btnConfirmDuplicate"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                    Ya, Duplikat
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ====================== MODAL KONFIRMASI HAPUS ====================== --}}
<div id="modalDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-red-500 p-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-3xl"></i>
                Konfirmasi Hapus
            </h2>
        </div>
        
        <div class="p-6">
            <p class="text-gray-700 text-lg mb-2">Apakah Anda yakin ingin menghapus layanan ini?</p>
            <p class="text-red-600 font-semibold mb-6">Tindakan ini tidak dapat dibatalkan!</p>
            
            <form id="formDelete" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modalLayanan");
    const modalTitle = document.getElementById("modalTitle");
    const modalImage = document.getElementById("modalImage");
    const modalImageContainer = document.getElementById("modalImageContainer");
    const qtyInput = document.getElementById("qtyInput");
    const parfumSelect = document.getElementById("parfumSelect");
    const btnSave = document.getElementById("btnSave");

    // MODAL DUPLICATE
    const modalDuplicate = document.getElementById("modalDuplicate");
    const btnConfirmDuplicate = document.getElementById("btnConfirmDuplicate");

    // MODAL DELETE
    const modalDelete = document.getElementById("modalDelete");
    const formDelete = document.getElementById("formDelete");

    function openModal(name, id) {
        modal.classList.remove("hidden");
        modalTitle.innerText = name;
        btnSave.dataset.id = id;
        qtyInput.value = "";
        parfumSelect.value = "";
        
        const card = document.querySelector(`.layanan-item[data-id="${id}"]`);
        const firstImage = card?.querySelector('img');
        
        if (firstImage && firstImage.src) {
            modalImage.src = firstImage.src;
            modalImageContainer.classList.remove("hidden");
        } else {
            modalImageContainer.classList.add("hidden");
        }
    }

    function closeModal() {
        modal.classList.add("hidden");
    }

    // DUPLICATE FUNCTIONS
    window.confirmDuplicate = function(url) {
        modalDuplicate.classList.remove("hidden");
        btnConfirmDuplicate.onclick = function() {
            window.location.href = url;
        };
    };

    window.closeDuplicateModal = function() {
        modalDuplicate.classList.add("hidden");
    };

    // DELETE FUNCTIONS
    window.confirmDelete = function(url) {
        modalDelete.classList.remove("hidden");
        formDelete.action = url;
    };

    window.closeDeleteModal = function() {
        modalDelete.classList.add("hidden");
    };

    // Close modals on backdrop click
    modalDuplicate.addEventListener("click", e => {
        if (e.target === modalDuplicate) closeDuplicateModal();
    });

    modalDelete.addEventListener("click", e => {
        if (e.target === modalDelete) closeDeleteModal();
    });

    modal.addEventListener("click", e => {
        if (e.target === modal) closeModal();
    });

    document.querySelectorAll('.layanan-item').forEach(card => {
        card.addEventListener('click', () => {
            
            const mode = card.dataset.mode;

            if (mode === 'transaksi') {
                openModal(card.dataset.name, card.dataset.id);
                return;
            }

            window.location.href =
        "{{ route('layanan.edit', ['id' => '__id__']) }}"
            .replace('__id__', card.dataset.id);
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

            document.querySelectorAll(".dropdown-menu").forEach(m => {
                if (m !== menu) m.classList.add("hidden");
            });

            menu.classList.toggle("hidden");
        });
    });

    document.querySelectorAll(".dropdown-menu").forEach(menu => {
        menu.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    });
});
</script>
@endsection