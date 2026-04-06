@extends('layouts.master')

@section('content')
@php
$routePrefix = 'admin2';
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="min-h-screen bg-gray-50 pb-24">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        @php
        $from = request('from');
        $idTransaksi = request('id_transaksi');

        $backUrl = match ($from) {
            'transaksi' => route('admin2.transaksi.create'),
            'riwayat'   => route('admin2.riwayat.detail', ['id' => $idTransaksi]),
            default     => route('admin2.dashboard'),
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
                                    <button onclick="confirmDuplicate('{{ route('admin2.layanan.duplicate', $item->id_layanan) }}')"
                                            class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 transition-colors">
                                        <i class="bi bi-layers text-lg text-blue-600"></i>
                                        <span class="font-medium">Duplikat</span>
                                    </button>
                                </li>
                                <li class="border-t border-gray-100">
                                    <button onclick="confirmDelete('{{ route('admin2.layanan.destroy', $item->id_layanan) }}')"
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

                                   {{-- FIXED IMAGE SECTION --}}
                                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-white border-2 border-gray-200 flex-shrink-0 shadow-sm">
                                        {{-- ✅ FIXED IMAGE SECTION --}}
                                        <div class="w-20 h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl overflow-hidden flex-shrink-0 border-2 border-gray-200 group-hover:border-yellow-300 transition-all">
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
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-lg text-gray-800 capitalize truncate">
                                            {{ $jenis->nama_jenis }}
                                        </p>
                                        <p class="text-green-600 font-semibold">
                                            Rp {{ number_format($jenis->harga,0,',','.') }} / {{ $jenis->satuan->nama_satuan ?? '-' }}
                                        </p>
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
        <a href="{{ route('admin2.layanan.create', ['from'=>'transaksi']) }}"
        class="block bg-yellow-400 hover:bg-yellow-500 py-4 rounded-2xl font-bold text-black text-center shadow-lg hover:shadow-xl transition-all hover:scale-105 flex items-center justify-center gap-2">
            <i class="bi bi-plus-circle-fill text-xl"></i>
            Tambah Layanan
        </a>
    </div>
</div>

{{-- MODAL --}}
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
<script>
document.addEventListener("DOMContentLoaded", () => {
const modal = document.getElementById("modalLayanan");
const modalTitle = document.getElementById("modalTitle");
const qtyInput = document.getElementById("qtyInput");
const parfumSelect = document.getElementById("parfumSelect");
const btnSave = document.getElementById("btnSave");
const addJenisTransaksiUrl = "{{ route('admin2.transaksi.addJenis', ':id') }}";
const modalDuplicate = document.getElementById("modalDuplicate");
const btnConfirmDuplicate = document.getElementById("btnConfirmDuplicate");
const modalDelete = document.getElementById("modalDelete");
const formDelete = document.getElementById("formDelete");

// ================= MODAL UTAMA =================
function openModal(name, id, mode = "transaksi", riwayatId = null) {
        modal.classList.remove("hidden");
        modalTitle.innerText = name;
        btnSave.dataset.id = id;
        btnSave.dataset.mode = mode;
        btnSave.dataset.riwayat = riwayatId ?? "";
        qtyInput.value = "";
        parfumSelect.value = "";
        btnSave.disabled = false;
        btnSave.style.pointerEvents = 'auto';
    }

function closeModal() {
        modal.classList.add("hidden");
    }
    window.closeModal = closeModal;
    modal.addEventListener("click", e => {
        if (e.target === modal) closeModal();
    });

// ================= DUPLICATE =================
    window.confirmDuplicate = function(url) {
        modalDuplicate.classList.remove("hidden");
        btnConfirmDuplicate.onclick = () => {
            window.location.href = url;
        };
    };

    window.closeDuplicateModal = function() {
        modalDuplicate.classList.add("hidden");
    };

    modalDuplicate.addEventListener("click", e => {
        if (e.target === modalDuplicate) closeDuplicateModal();
    });

// ================= DELETE =================
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
            // Jangan proses jika klik jenis, dropdown, atau button
            if (e.target.closest('.jenis-item') || 
                e.target.closest('.dropdown-area') ||
                e.target.closest('button')) {
                return;
            }

            const from = "{{ request('from') }}";
            const idLayanan = card.dataset.id;
            console.log('🔵 Layanan Card Clicked | From:', from, '| ID:', idLayanan);

            // Hanya redirect ke edit jika BUKAN dari transaksi/riwayat
            if (from !== "transaksi" && from !== "riwayat") {
                console.log('➡️ Redirect to edit layanan:', idLayanan);
                window.location.href = `/admin2/layanan/${idLayanan}/edit`;
            }
        });
    });

// ================= KLIK JENIS LAYANAN (FIXED) =================
    document.querySelectorAll('.jenis-item').forEach(item => {
        item.addEventListener('click', e => {
            e.stopPropagation();

            const from = "{{ request('from') }}";
            const idLayanan = item.dataset.idLayanan;
            const idJenis = item.dataset.idJenis;
            const namaJenis = item.dataset.nama;

            console.log('🟢 Jenis Clicked:', namaJenis);
            console.log('  - From:', from);
            console.log('  - ID Layanan:', idLayanan);
            console.log('  - ID Jenis:', idJenis);

            // ✅ HANYA BUKA MODAL JIKA DARI TRANSAKSI ATAU RIWAYAT
            if (from === "transaksi" || from === "riwayat") {
                const riwayatId = "{{ request('id_transaksi') }}";
                console.log('📦 Opening modal for transaction');
                openModal(namaJenis, idJenis, from, riwayatId || null);
            } 
            // ✅ SELAIN ITU (termasuk dari dashboard) → REDIRECT KE EDIT LAYANAN
            else {
                console.log('➡️ Redirect to edit layanan:', idLayanan);
                window.location.href = `/admin2/layanan/${idLayanan}/edit`;
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
            console.log('🔥 Button Simpan diklik!');

            const qty = qtyInput.value.trim();
            if (!qty || qty <= 0) {
                alert("Qty wajib diisi dan harus lebih dari 0");
                return;
            }

            const parfum = parfumSelect.value || null;
            const idJenis = btnSave.dataset.id;
            const mode = btnSave.dataset.mode;
            const idRiwayat = btnSave.dataset.riwayat;

            console.log('📦 Data yang akan dikirim:');
            console.log('  - ID Jenis:', idJenis);
            console.log('  - Qty:', qty);
            console.log('  - Parfum:', parfum);
            console.log('  - Mode:', mode);
            console.log('  - ID Riwayat:', idRiwayat);

            if (mode === "riwayat" && !idRiwayat) {
                alert("ID transaksi tidak ditemukan");
                return;
            }

            // Disable button
            btnSave.disabled = true;
            btnSave.textContent = "Menyimpan...";

            // Build URL
            let url = addJenisTransaksiUrl.replace(':id', idJenis);
            console.log('🌐 URL Request:', url);

            // Send request
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
                console.log('📡 Response Status:', res.status);
                console.log('📡 Response OK:', res.ok);
                return res.text().then(text => {
                    console.log('📄 Response Text:', text);
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
                console.log('✅ Parsed Data:', data);
                if (data.success) {
                    console.log('🎉 Sukses! Redirecting...');
                    const redirectUrl = mode === "transaksi"
                        ? "{{ route('admin2.transaksi.create') }}"
                        : `/admin2/riwayat/${idRiwayat}/edit`;
                    console.log('🔀 Redirect ke:', redirectUrl);
                    window.location.href = redirectUrl;
                } else {
                    throw new Error(data.message || 'Gagal menyimpan');
                }
            })
            .catch(err => {
                console.error('❌ Error:', err);
                alert("Gagal menyimpan layanan: " + err.message);
                // Re-enable button
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
