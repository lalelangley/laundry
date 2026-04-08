{{-- FE-DOC: Template frontend untuk resources/views/kasir/layanan/layanan.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Data Layanan')

@section('content')
@php
$routePrefix = 'kasir';
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- FE-DOC: Blok CSS khusus halaman ini. --}}

<style>
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .shake {
        animation: shake 0.5s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .alert-modal {
        animation: fadeIn 0.3s ease;
    }
</style>

<div class="min-h-screen bg-gray-50 pb-24">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        @php
            $from = request()->query('from', 'dashboard');
            $idTransaksi = request()->query('id_transaksi');

            $backUrl = match ($from) {
                'transaksi' => route('kasir.transaksi.create'),
                'riwayat'   => route('kasir.riwayat.detail', ['id' => $idTransaksi]),
                default     => route('kasir.dashboard'),
            };
        @endphp

        <a href="{{ $backUrl }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Kelola Layanan</span>
    </div>

    <div class="px-8 py-6 space-y-6">
        {{-- SEARCH + SORT --}}
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
                            <div class="flex items-center gap-2 flex-wrap">
                                @foreach($steps as $i=>$step)
                                    <div class="flex items-center gap-1.5 bg-yellow-50 px-3 py-1.5 rounded-lg">
                                        <i class="{{ $icons[$step] ?? 'bi bi-gear' }} text-yellow-600 text-lg"></i>
                                        <span class="text-sm font-semibold text-gray-700">{{ $step }}</span>
                                    </div>
                                    @if($i < count($steps)-1) <i class="bi bi-chevron-right text-gray-300"></i> @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- DROPDOWN --}}
                        <div class="dropdown-area relative">
                            <button class="dropdown-btn text-gray-600 hover:text-gray-800 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="bi bi-three-dots-vertical text-xl"></i>
                            </button>
                            <ul class="dropdown-menu hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 z-50">
                                <li>
                                    <button onclick="confirmDuplicate('{{ route('kasir.layanan.duplicate', $item->id_layanan) }}')"
                                            class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 transition-colors">
                                        <i class="bi bi-layers text-lg text-blue-600"></i>
                                        <span class="font-medium">Duplikat</span>
                                    </button>
                                </li>
                                <li class="border-t border-gray-100">
                                    <button onclick="confirmDelete('{{ route('kasir.layanan.destroy', $item->id_layanan) }}')"
                                            class="w-full flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="bi bi-trash text-lg"></i>
                                        <span class="font-medium">Hapus</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- JENIS --}}
                    @if($item->jenis->count() > 0)
                        <div class="space-y-4">
                            @foreach($item->jenis as $jenis)
                                <div class="jenis-item flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer"
                                    data-id-layanan="{{ $item->id_layanan }}"
                                    data-id-jenis="{{ $jenis->id_jenis_layanan }}"
                                    data-nama="{{ $jenis->nama_jenis }}">

                                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-200 flex-shrink-0 shadow-sm">
                                        @if(!empty($jenis->gambar))
                                            <img src="{{ asset('storage/' . $jenis->gambar) }}"
                                                alt="{{ $jenis->nama_jenis }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gradient-to-br from-yellow-100 to-yellow-200\'><i class=\'bi bi-image text-3xl text-yellow-400\'></i></div>';">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="bi bi-image text-3xl text-gray-300"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-lg text-gray-800 capitalize truncate">{{ $jenis->nama_jenis }}</p>
                                        <p class="text-green-600 font-semibold">Rp {{ number_format($jenis->harga,0,',','.') }} / {{ $jenis->satuan->nama_satuan ?? '-' }}</p>
                                        <div class="flex items-center gap-1.5 text-gray-500 text-sm">
                                            <i class="bi bi-clock"></i>
                                            {{ $jenis->lama }} {{ $jenis->lama_satuan }}
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

        @if(method_exists($layananUtama, 'links'))
        <div class="pt-2">
            {{ $layananUtama->links() }}
        </div>
        @endif

        {{-- TAMBAH --}}
       <a href="{{ route('kasir.layanan.create', ['from' => $from]) }}"
        class="block bg-yellow-400 hover:bg-yellow-500 py-4 rounded-2xl font-bold text-black text-center shadow-lg hover:shadow-xl transition-all hover:scale-105 flex items-center justify-center gap-2">
            <i class="bi bi-plus-circle-fill text-xl"></i>
            Tambah Layanan
        </a>
    </div>
</div>

{{-- MODAL LAYANAN --}}
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
                    <input id="qtyInput" 
                           type="text" 
                           inputmode="decimal"
                           class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all" 
                           placeholder="Minimal 0.01">
                </div>
                <p class="text-xs text-gray-500 mt-1.5 ml-1">Minimal kuantitas: 0.01 (contoh: 1, 2.5, 10.75)</p>
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
                        data-mode="{{ $from }}" data-id="" data-transaksi="{{ $idTransaksi }}">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CUSTOM ALERT MODAL --}}
<div id="alertModal" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[9999] hidden">
    <div class="alert-modal bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden">
        <div id="alertHeader" class="p-6 flex items-center justify-center">
            <div id="alertIcon" class="w-16 h-16 rounded-full flex items-center justify-center">
                <!-- Icon will be injected here -->
            </div>
        </div>
        <div class="px-6 pb-6 text-center">
            <h3 id="alertTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
            <p id="alertMessage" class="text-gray-600 mb-6"></p>
            <button id="alertButton" class="w-full py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                OK, Mengerti
            </button>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI DUPLIKAT --}}
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

{{-- MODAL KONFIRMASI HAPUS --}}
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
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalLayanan");
    const modalTitle = document.getElementById("modalTitle");
    const qtyInput = document.getElementById("qtyInput");
    const parfumSelect = document.getElementById("parfumSelect");
    const btnSave = document.getElementById("btnSave");
    const addJenisTransaksiUrl = "{{ route('kasir.transaksi.addJenis', ':id') }}";
    const modalDuplicate = document.getElementById("modalDuplicate");
    const btnConfirmDuplicate = document.getElementById("btnConfirmDuplicate");
    const modalDelete = document.getElementById("modalDelete");
    const formDelete = document.getElementById("formDelete");

    const urlParams = new URLSearchParams(window.location.search);
    const fromParam = urlParams.get('from') || 'dashboard';
    
    console.log('🔍 From parameter:', fromParam);

    // ================= CUSTOM ALERT FUNCTION =================
    function showAlert(type, title, message) {
        const alertModal = document.getElementById('alertModal');
        const alertHeader = document.getElementById('alertHeader');
        const alertIcon = document.getElementById('alertIcon');
        const alertTitle = document.getElementById('alertTitle');
        const alertMessage = document.getElementById('alertMessage');
        const alertButton = document.getElementById('alertButton');
        
        // Reset classes
        alertIcon.className = 'w-16 h-16 rounded-full flex items-center justify-center';
        alertButton.className = 'w-full py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all active:scale-95';
        
        // Set content based on type
        if (type === 'warning') {
            alertHeader.className = 'p-6 flex items-center justify-center bg-gradient-to-br from-yellow-50 to-orange-50';
            alertIcon.classList.add('bg-gradient-to-br', 'from-yellow-400', 'to-orange-500', 'shadow-lg');
            alertIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-white text-3xl"></i>';
            alertButton.classList.add('bg-gradient-to-r', 'from-yellow-400', 'to-orange-500', 'text-white');
        } else if (type === 'error') {
            alertHeader.className = 'p-6 flex items-center justify-center bg-gradient-to-br from-red-50 to-pink-50';
            alertIcon.classList.add('bg-gradient-to-br', 'from-red-500', 'to-pink-600', 'shadow-lg');
            alertIcon.innerHTML = '<i class="bi bi-x-circle-fill text-white text-3xl"></i>';
            alertButton.classList.add('bg-gradient-to-r', 'from-red-500', 'to-pink-600', 'text-white');
        } else if (type === 'info') {
            alertHeader.className = 'p-6 flex items-center justify-center bg-gradient-to-br from-blue-50 to-cyan-50';
            alertIcon.classList.add('bg-gradient-to-br', 'from-blue-500', 'to-cyan-600', 'shadow-lg');
            alertIcon.innerHTML = '<i class="bi bi-info-circle-fill text-white text-3xl"></i>';
            alertButton.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-cyan-600', 'text-white');
        }
        
        alertTitle.textContent = title;
        alertMessage.textContent = message;
        
        // Show modal
        alertModal.classList.remove('hidden');
        
        // Close on button click
        alertButton.onclick = () => {
            alertModal.classList.add('hidden');
            // Focus back to qty input if it was an error
            if (type === 'warning' || type === 'error') {
                setTimeout(() => qtyInput.focus(), 100);
            }
        };
        
        // Close on backdrop click
        alertModal.onclick = (e) => {
            if (e.target === alertModal) {
                alertModal.classList.add('hidden');
            }
        };
    }

    // ================= VALIDASI QTY INPUT =================
    qtyInput.addEventListener('input', function(e) {
        let value = e.target.value;
        value = value.replace(/[^\d.,]/g, '');
        value = value.replace(',', '.');
        
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        if (value.length > 1 && value[0] === '0' && value[1] !== '.') {
            value = value.replace(/^0+/, '');
        }
        
        if (parts.length === 2 && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        
        e.target.value = value;
    });

    qtyInput.addEventListener('blur', function(e) {
        let value = parseFloat(e.target.value);
        
        if (isNaN(value) || value < 0.01) {
            e.target.value = '';
            e.target.classList.add('border-red-500');
        } else {
            e.target.classList.remove('border-red-500');
        }
    });

    qtyInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const cleaned = paste.replace(/[^\d.,]/g, '').replace(',', '.');
        
        const number = parseFloat(cleaned);
        if (!isNaN(number) && number >= 0.01) {
            e.target.value = number.toString();
        }
    });

    // ================= MODAL UTAMA =================
    function openModal(name, id, mode = "transaksi", riwayatId = null) {
        modal.classList.remove("hidden");
        modalTitle.innerText = name;
        btnSave.dataset.id = id;
        btnSave.dataset.mode = mode;
        btnSave.dataset.riwayat = riwayatId ?? "";
        qtyInput.value = "";
        qtyInput.classList.remove('border-red-500');
        parfumSelect.value = "";
        btnSave.disabled = false;
        btnSave.style.pointerEvents = 'auto';
        
        setTimeout(() => qtyInput.focus(), 100);
    }

    function closeModal() {
        modal.classList.add("hidden");
    }

    window.closeModal = closeModal;

    modal.addEventListener("click", e => {
        if (e.target === modal) closeModal();
    });

    // ================= DUPLICATE & DELETE =================
    window.confirmDuplicate = function(url) {
        modalDuplicate.classList.remove("hidden");
        btnConfirmDuplicate.onclick = () => window.location.href = url;
    };

    window.closeDuplicateModal = function() {
        modalDuplicate.classList.add("hidden");
    };

    modalDuplicate.addEventListener("click", e => {
        if (e.target === modalDuplicate) closeDuplicateModal();
    });

    window.confirmDelete = function(url) {
        modalDelete.classList.remove("hidden");
        formDelete.action = url;
    };

    window.closeDeleteModal = function() {
        modalDelete.classList.add("hidden");
    };

    modalDelete.addEventListener("click", e => {
        if (e.target === modalDelete) closeDeleteModal();
    });

    // ================= KLIK LAYANAN UTAMA =================
    document.querySelectorAll('.layanan-item').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.jenis-item') || 
                e.target.closest('.dropdown-area') ||
                e.target.closest('button')) {
                return;
            }

            const idLayanan = card.dataset.id;

            if (fromParam === "dashboard" || fromParam !== "transaksi" && fromParam !== "riwayat") {
                window.location.href = `/kasir/layanan/${idLayanan}/edit`;
            }
        });
    });

    // ================= KLIK JENIS LAYANAN =================
    document.querySelectorAll('.jenis-item').forEach(item => {
        item.addEventListener('click', e => {
            e.stopPropagation();

            const idLayanan = item.dataset.idLayanan;
            const idJenis = item.dataset.idJenis;
            const namaJenis = item.dataset.nama;

            if (fromParam === "transaksi" || fromParam === "riwayat") {
                const riwayatId = urlParams.get('id_transaksi');
                openModal(namaJenis, idJenis, fromParam, riwayatId || null);
            } else {
                window.location.href = `/kasir/layanan/${idLayanan}/edit`;
            }
        });
    });

    // ================= DROPDOWN =================
    document.querySelectorAll(".dropdown-area").forEach(area => {
        area.addEventListener("click", e => e.stopPropagation());
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

    document.addEventListener("click", () => {
        document.querySelectorAll(".dropdown-menu").forEach(m => m.classList.add("hidden"));
    });

    // ================= TOMBOL SIMPAN =================
    if (btnSave) {
        btnSave.addEventListener("click", function(e) {
            e.preventDefault();
            
            let qtyValue = qtyInput.value.trim();
            
            // Validasi qty kosong
            if (!qtyValue) {
                qtyInput.classList.add('border-red-500', 'shake');
                setTimeout(() => qtyInput.classList.remove('shake'), 500);
                showAlert('warning', 'Oops! Kuantitas Belum Diisi', 'Mohon isi jumlah kuantitas terlebih dahulu. Minimal 0.01');
                return;
            }
            
            // Parse dan validasi angka
            const qty = parseFloat(qtyValue);
            
            if (isNaN(qty)) {
                qtyInput.classList.add('border-red-500', 'shake');
                setTimeout(() => qtyInput.classList.remove('shake'), 500);
                showAlert('error', 'Format Tidak Valid!', 'Gunakan format angka yang benar. Contoh: 1, 2.5, atau 10.75');
                return;
            }
            
            if (qty < 0.01) {
                qtyInput.classList.add('border-red-500', 'shake');
                setTimeout(() => qtyInput.classList.remove('shake'), 500);
                showAlert('warning', 'Kuantitas Terlalu Kecil!', 'Jumlah minimal adalah 0.01. Silakan masukkan nilai yang lebih besar.');
                return;
            }
            
            qtyInput.classList.remove('border-red-500');

            const parfum = parfumSelect.value || null;
            const idJenis = btnSave.dataset.id;
            const mode = btnSave.dataset.mode;
            const idRiwayat = btnSave.dataset.riwayat;

            if (mode === "riwayat" && !idRiwayat) {
                showAlert('error', 'ID Transaksi Tidak Ditemukan', 'Terjadi kesalahan sistem. Silakan coba lagi.');
                return;
            }

            btnSave.disabled = true;
            btnSave.textContent = "Menyimpan...";

            let url = addJenisTransaksiUrl.replace(':id', idJenis);

            fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ qty, parfum })
            })
            .then(res => {
                return res.text().then(text => {
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}: ${text}`);
                    }
                    
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Response bukan JSON: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    const redirectUrl = mode === "transaksi"
                        ? "{{ route('kasir.transaksi.create') }}"
                        : `/kasir/riwayat/${idRiwayat}/edit`;
                    
                    window.location.href = redirectUrl;
                } else {
                    throw new Error(data.message || 'Gagal menyimpan');
                }
            })
            .catch(err => {
                console.error('❌ Error:', err);
                showAlert('error', 'Gagal Menyimpan!', 'Terjadi kesalahan: ' + err.message);
                
                btnSave.disabled = false;
                btnSave.textContent = "Simpan";
            });
        });
        
        btnSave.disabled = false;
    }

    // ================= SEARCH =================
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.layanan-item').forEach(item => {
                const name = item.dataset.name.toLowerCase();
                item.style.display = name.includes(searchTerm) ? 'block' : 'none';
            });
        });
    }

    console.log('✅ Script loaded successfully!');
});
</script>

@endsection
