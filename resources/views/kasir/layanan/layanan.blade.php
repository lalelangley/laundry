@extends('layouts.master')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="min-h-screen bg-gray-50 pb-24">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        @php
            $from = request('from');
            $idTransaksi = request('id_transaksi');
            $backUrl = match ($from) {
                'transaksi' => route('kasir.transaksi.create'),
                'riwayat'   => route('kasir.dashboard'),
                default     => route('kasir.dashboard'),
            };
        @endphp

        <a href="{{ $backUrl }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Kelola Layanan</span>
    </div>

    <div class="px-8 py-6 space-y-6">
        {{-- SEARCH --}}
        <div class="flex items-center gap-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                <input id="searchInput" type="text" placeholder="Cari layanan..."
                    class="w-full pl-12 pr-4 py-4 rounded-xl bg-white shadow-md outline-none focus:ring-2 focus:ring-yellow-400 transition-all">
            </div>
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
                 data-mode="transaksi">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4 pb-4 border-b-2 border-gray-100">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 capitalize mb-2">{{ $item->nama_layanan }}</h3>

                            @php
                                $icons = ['Cuci'=>'bi bi-droplet','Kering'=>'bi bi-wind','Setrika'=>'bi bi-iron'];
                                $raw = $item->proses ?? '';
                                if(is_string($raw) && Str::startsWith(trim($raw),'[')){
                                    $steps = json_decode($raw,true) ?: [];
                                } else {
                                    $parts = array_map('trim', explode(',', trim($raw,"[]\"' ")));
                                    $steps = array_filter($parts, fn($s)=>$s!=='');
                                }
                            @endphp
                            @if(count($steps) > 0)
                                <div class="flex items-center gap-2 flex-wrap mb-3">
                                    @foreach($steps as $i=>$step)
                                        <div class="flex items-center gap-1.5 bg-yellow-50 px-3 py-1.5 rounded-lg">
                                            <i class="{{ $icons[$step] ?? 'bi bi-gear' }} text-yellow-600 text-lg"></i>
                                            <span class="text-sm font-semibold text-gray-700">{{ $step }}</span>
                                        </div>
                                        @if($i < count($steps)-1)
                                            <i class="bi bi-chevron-right text-gray-300"></i>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- DROPDOWN --}}
                        <div class="dropdown-area relative">
                            <button class="dropdown-btn text-gray-600 hover:text-gray-800 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="bi bi-three-dots-vertical text-xl"></i>
                            </button>
                            <ul class="dropdown-menu hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-xl z-50">
                                <li>
                                    <button onclick="confirmDuplicate('{{ route('kasir.layanan.duplicate', $item->id_layanan) }}')"
                                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-yellow-50">
                                        <i class="bi bi-layers text-blue-600"></i>
                                        <span>Duplikat</span>
                                    </button>
                                </li>

                                <li class="border-t">
                                    <button onclick="confirmDelete('{{ route('kasir.layanan.destroy', $item->id_layanan) }}')"
                                        class="w-full flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50">
                                        <i class="bi bi-trash"></i>
                                        <span>Hapus</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- JENIS --}}
                    @if($item->jenis->count() > 0)
                        <div class="space-y-4">
                            @foreach($item->jenis as $jenis)
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-white border-2 border-gray-200 flex-shrink-0">
                                        <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}" class="w-full h-full object-cover" alt="{{ $jenis->nama_jenis }}">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-lg text-gray-800 capitalize mb-1 truncate">{{ $jenis->nama_jenis }}</p>
                                        <p class="text-green-600 font-semibold mb-2">
                                            Rp {{ number_format($jenis->harga,0,',','.') }} / {{ $jenis->satuan->nama_satuan ?? '-' }}
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
            <div class="text-center py-16">
                <i class="bi bi-gear-wide-connected text-5xl text-gray-400 mb-4"></i>
                <p class="text-xl text-gray-500 font-semibold">Tidak ada layanan</p>
                <p class="text-gray-400 text-sm mt-2">Silahkan tambahkan layanan baru</p>
            </div>
            @endforelse
        </div>

        {{-- TAMBAH LAYANAN --}}
        <a href="{{ route('kasir.layanan.layanan_create', ['from'=>'transaksi']) }}"
        class="block bg-yellow-400 hover:bg-yellow-500 py-4 rounded-2xl font-bold text-black text-center shadow-lg">
            <i class="bi bi-plus-circle-fill text-xl"></i>
            Tambah Layanan
        </a>
    </div>
</div>

{{-- ================= MODAL TAMBAH LAYANAN ================= --}}
<div id="modalLayanan" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white w-full max-w-lg mx-auto rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-yellow-400 p-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-black text-center"></h2>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <label class="block font-bold text-gray-700 mb-2">Jumlah Kuantitas</label>
                <div class="relative">
                    <i class="bi bi-123 absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                    <input id="qtyInput" type="number" step="0.01" class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all" placeholder="Masukkan qty">
                </div>
            </div>
            <div>
                <label class="block font-bold text-gray-700 mb-2">Pilih Parfum</label>
                <div class="relative">
                    <i class="bi bi-flower1 absolute left-4 top-1/2 transform -translate-y-1/2 text-pink-400 text-xl z-10"></i>
                    <select id="parfumSelect" class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all appearance-none bg-white">
                        <option value="">Pilih Parfum</option>
                        @foreach ($parfum as $p)
                            <option value="{{ $p->id_parfum }}">{{ $p->nama_parfum }}</option>
                        @endforeach
                    </select>
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="closeModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">Batal</button>
                <button id="btnSave" class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-black py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all"
                        data-mode="{{ request('from') }}" data-id="" data-transaksi="{{ request('id_transaksi') }}">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL DUPLICATE ================= --}}
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
                <button onclick="closeDuplicateModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">Batal</button>
                <button id="btnConfirmDuplicate" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">Ya, Duplikat</button>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL DELETE ================= --}}
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
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">Batal</button>
                    <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">Ya, Hapus</button>
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
    const qtyInput = document.getElementById("qtyInput");
    const parfumSelect = document.getElementById("parfumSelect");
    const btnSave = document.getElementById("btnSave");

    const modalDuplicate = document.getElementById("modalDuplicate");
    const btnConfirmDuplicate = document.getElementById("btnConfirmDuplicate");
    const modalDelete = document.getElementById("modalDelete");
    const formDelete = document.getElementById("formDelete");

    function openModal(name, id, mode = "transaksi") {
        modal.classList.remove("hidden");
        modalTitle.innerText = name;
        btnSave.dataset.id = id;
        btnSave.dataset.mode = mode;
        qtyInput.value = "";
        parfumSelect.value = "";
        qtyInput.focus();
    }
    window.openModal = openModal;

    function closeModal() { modal.classList.add("hidden"); }
    window.closeModal = closeModal;
    modal.addEventListener("click", e => { if(e.target===modal) closeModal(); });

    window.confirmDuplicate = function(url) {
        modalDuplicate.classList.remove("hidden");
        btnConfirmDuplicate.onclick = () => { window.location.href = url; };
    };
    window.closeDuplicateModal = () => modalDuplicate.classList.add("hidden");
    modalDuplicate.addEventListener("click", e => { if(e.target===modalDuplicate) closeDuplicateModal(); });

    window.confirmDelete = function(url) {
        modalDelete.classList.remove("hidden");
        formDelete.action = url;
    };
    window.closeDeleteModal = () => modalDelete.classList.add("hidden");
    modalDelete.addEventListener("click", e => { if(e.target===modalDelete) closeDeleteModal(); });

    // Klik layanan untuk modal
document.querySelectorAll(".layanan-item").forEach(card => {
    card.addEventListener("click", (e) => {
        if(e.target.closest(".dropdown-area") || e.target.closest("button")) return;

        const mode = card.dataset.mode; // ambil dari data-mode
        const idLayanan = card.dataset.id;
        const nama = card.dataset.name;

        if(mode === "transaksi") {
            openModal(nama, idLayanan, "transaksi");
        } else {
            window.location.href = "{{ url('/kasir/layanan') }}/" + idLayanan + "/edit";
        }
    });
});


    // Dropdown
    document.querySelectorAll(".dropdown-area").forEach(area => area.addEventListener("click", e => e.stopPropagation()));
    document.querySelectorAll(".dropdown-btn").forEach(btn => {
        btn.addEventListener("click", function(e){
            e.stopPropagation();
            const menu = this.nextElementSibling;
            document.querySelectorAll(".dropdown-menu").forEach(m => { if(m!==menu) m.classList.add("hidden"); });
            menu.classList.toggle("hidden");
        });
    });
    document.addEventListener("click", () => { document.querySelectorAll(".dropdown-menu").forEach(m => m.classList.add("hidden")); });

    // Simpan layanan via fetch
    btnSave.addEventListener("click", () => {
        const qty = qtyInput.value.trim();
        const parfum = parfumSelect.value || null;
        if(!qty || qty<=0) return alert("Masukkan qty yang valid!");
        const idLayanan = btnSave.dataset.id;
        fetch(`/kasir/transaksi/add-layanan/${idLayanan}`, {
            method:"POST",
            headers:{
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Content-Type":"application/json",
                "Accept":"application/json"
            },
            body: JSON.stringify({qty, parfum})
        }).then(res=>res.json()).then(res=>{
            if(res.success) window.location.href="{{ route('kasir.transaksi.create') }}";
            else alert(res.message || "Gagal menambahkan layanan!");
        }).catch(err=>{
            console.error(err);
            alert("Terjadi kesalahan saat menambahkan layanan.");
        });
    });

});
</script>
@endsection
