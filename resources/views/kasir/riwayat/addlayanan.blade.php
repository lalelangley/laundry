@extends('layouts.master')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="min-h-screen bg-gray-50 pb-24">
{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
   <a href="{{ route('kasir.riwayat.edit', $riwayat->id_transaksi) }}"
   class="text-black text-3xl font-bold hover:scale-110 transition-transform">
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
            <div class="layanan-item bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 relative group cursor-pointer"
                 data-id="{{ $item->id_layanan }}"
                 data-name="{{ $item->nama_layanan }}">
                {{-- HEADER --}}
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
                                    data-id-jenis="{{ $jenis->id_jenis_layanan }}"
                                    data-nama="{{ $jenis->nama_jenis }}">
                                    
                                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-white border-2 border-gray-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                                        @if(!empty($jenis->gambar))
                                            <img src="{{ asset('storage/' . $jenis->gambar) }}" 
                                                alt="{{ $jenis->nama_jenis }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.onerror=null; this.src='{{ asset('images/default.png') }}';">
                                        @else
                                            <i class="bi bi-image text-3xl text-gray-300"></i>
                                        @endif
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

        {{-- TAMBAH --}}
        <a href="{{ route('kasir.layanan.create', ['from'=>'transaksi']) }}"
           class="block bg-yellow-400 hover:bg-yellow-500 py-4 rounded-2xl font-bold text-black text-center shadow-lg hover:shadow-xl transition-all hover:scale-105 flex items-center justify-center gap-2">
            <i class="bi bi-plus-circle-fill text-xl"></i>
            Tambah Layanan
        </a>
    </div>
</div>

{{-- MODAL LAYANAN --}}
<div id="modalLayanan" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white modal-box w-full max-w-lg mx-auto rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-yellow-400 p-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-black text-center"></h2>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <label class="block font-bold text-gray-700 mb-2">Pilih Jenis Layanan</label>
                <div class="relative">
                    <i class="bi bi-box-seam absolute left-4 top-1/2 transform -translate-y-1/2 text-yellow-600 text-xl z-10"></i>
                    <select id="jenisSelect" class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all appearance-none bg-white">
                        <option value="">Pilih Jenis Layanan</option>
                    </select>
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
                <div id="hargaInfo" class="mt-2 text-sm text-gray-600 hidden">
                    <i class="bi bi-info-circle"></i>
                    <span id="hargaText"></span>
                </div>
            </div>

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
                <button id="btnSave" class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-black py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DUPLICATE --}}
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

{{-- MODAL DELETE --}}
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
// ✅ DEBUGGING CONSOLE
console.log('🔍 === PAGE LOADED DEBUG ===');
console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
console.log('User authenticated (from PHP):', {{ auth()->check() ? 'true' : 'false' }});
console.log('Current URL:', window.location.href);

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalLayanan");
    const modalBox = modal.querySelector(".modal-box");
    const modalTitle = document.getElementById("modalTitle");
    const jenisSelect = document.getElementById("jenisSelect");
    const qtyInput = document.getElementById("qtyInput");
    const parfumSelect = document.getElementById("parfumSelect");
    const btnSave = document.getElementById("btnSave");
    const hargaInfo = document.getElementById("hargaInfo");
    const hargaText = document.getElementById("hargaText");

    const modalDuplicate = document.getElementById("modalDuplicate");
    const btnConfirmDuplicate = document.getElementById("btnConfirmDuplicate");
    const modalDelete = document.getElementById("modalDelete");
    const formDelete = document.getElementById("formDelete");

    // Data dari controller
    const jenisLayananData = {!! json_encode($jenisLayananData ?? []) !!};
    console.log('📦 Jenis Layanan Data:', jenisLayananData);

    /* ================= MODAL UTAMA ================= */
    function openModal(name, idLayanan, riwayatId, preselectedJenisId = null) {
        console.log('🔓 Opening modal:', { name, idLayanan, riwayatId, preselectedJenisId });
        
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";

        modalTitle.innerText = name;
        btnSave.dataset.layanan = idLayanan;
        btnSave.dataset.riwayat = riwayatId;

        // Reset form
        jenisSelect.innerHTML = '<option value="">Pilih Jenis Layanan</option>';
        qtyInput.value = "";
        parfumSelect.value = "";
        hargaInfo.classList.add("hidden");

        // Populate jenis layanan
        const jenisOptions = jenisLayananData[idLayanan] || [];
        console.log('📋 Available jenis for layanan ' + idLayanan + ':', jenisOptions);
        
        jenisOptions.forEach(jenis => {
            const option = document.createElement('option');
            option.value = jenis.id;
            option.textContent = `${jenis.nama} - Rp ${formatRupiah(jenis.harga)} / ${jenis.satuan}`;
            option.dataset.harga = jenis.harga;
            option.dataset.satuan = jenis.satuan;
            jenisSelect.appendChild(option);
        });

        // Auto-select jenis jika ada
        if (preselectedJenisId) {
            jenisSelect.value = preselectedJenisId;
            const event = new Event('change');
            jenisSelect.dispatchEvent(event);
            qtyInput.focus();
        } else {
            jenisSelect.focus();
        }
    }

    function closeModal() {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }

    window.closeModal = closeModal;

    // Format Rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    // Show harga info when jenis selected
    jenisSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (this.value) {
            const harga = selected.dataset.harga;
            const satuan = selected.dataset.satuan;
            hargaText.textContent = `Harga: Rp ${formatRupiah(harga)} per ${satuan}`;
            hargaInfo.classList.remove("hidden");
        } else {
            hargaInfo.classList.add("hidden");
        }
    });

    modal.addEventListener("click", e => {
        if (e.target === modal) closeModal();
    });

    modalBox.addEventListener("click", e => e.stopPropagation());

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    /* ================= KLIK CARD LAYANAN ================= */
    document.querySelectorAll(".layanan-item").forEach(card => {
        card.addEventListener("click", e => {
            if (!modal.classList.contains("hidden")) return;
            if (e.target.closest(".dropdown-area")) return;
            if (e.target.closest(".jenis-item")) return;

            openModal(
                card.dataset.name,
                card.dataset.id,
                "{{ $riwayat->id_transaksi }}"
            );
        });
    });

    /* ================= KLIK JENIS LAYANAN ================= */
    document.querySelectorAll(".jenis-item").forEach(item => {
        item.addEventListener("click", e => {
            e.stopPropagation();

            const idLayanan = item.closest('.layanan-item').dataset.id;
            const namaLayanan = item.closest('.layanan-item').dataset.name;
            const idJenis = item.dataset.idJenis || item.dataset.id;
            const namaJenis = item.dataset.nama;

            console.log('🟢 Jenis clicked:', {
                idLayanan,
                namaLayanan,
                idJenis,
                namaJenis
            });

            openModal(
                namaLayanan,
                idLayanan,
                "{{ $riwayat->id_transaksi }}",
                idJenis
            );
        });
    });

    /* ================= SIMPAN (DENGAN SUPER DEBUG) ================= */
    btnSave.addEventListener("click", async e => {
        e.preventDefault();
        e.stopPropagation();

        console.log('💾 === SAVE BUTTON CLICKED ===');

        const idJenis = jenisSelect.value;
        const qty = qtyInput.value;

        // Validasi
        if (!idJenis) {
            console.error('❌ Jenis layanan tidak dipilih!');
            alert("Jenis layanan wajib dipilih!");
            jenisSelect.focus();
            return;
        }

        if (!qty || qty <= 0) {
            console.error('❌ Qty tidak valid:', qty);
            alert("Qty wajib diisi dan harus lebih dari 0!");
            qtyInput.focus();
            return;
        }

        const idRiwayat = btnSave.dataset.riwayat;
        const idLayanan = btnSave.dataset.layanan;
        const parfum = parfumSelect.value || '';

        console.log('📤 Request data:', {
            idRiwayat,
            idLayanan,
            idJenis,
            qty,
            parfum
        });

        // ✅ PAKAI FORMDATA
        const formData = new FormData();
        formData.append('id_layanan', idLayanan);
        formData.append('id_jenis_layanan', idJenis);
        formData.append('qty', qty);
        formData.append('parfum', parfum);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        console.log('📦 FormData contents:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
        }

        btnSave.disabled = true;
        btnSave.textContent = "Menyimpan...";

        try {
            const url = `/kasir/riwayat/${idRiwayat}/add-layanan`;
            console.log('🌐 Fetch URL:', url);

            const res = await fetch(url, {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin', // ✅ PENTING!
                body: formData
            });

            console.log('📡 Response status:', res.status);
            console.log('📡 Response headers:', Object.fromEntries(res.headers));

            const responseText = await res.text();
            console.log('📄 Raw response:', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
                console.log('✅ Parsed JSON:', data);
            } catch (parseError) {
                console.error('❌ JSON parse error:', parseError);
                console.error('Response was:', responseText);
                throw new Error('Invalid JSON response from server');
            }

            if (!res.ok) {
                console.error('❌ HTTP Error:', data);
                throw new Error(data.message || `Server error: ${res.status}`);
            }

            if (data.success) {
                console.log('✅ Success! Redirecting...');
                window.location.href = `/kasir/riwayat/${idRiwayat}/edit`;
            } else {
                console.error('❌ Success=false:', data);
                alert(data.message || "Gagal menambah layanan");
                btnSave.disabled = false;
                btnSave.textContent = "Simpan";
            }
        } catch (err) {
            console.error('❌ === FETCH ERROR ===');
            console.error('Error type:', err.constructor.name);
            console.error('Error message:', err.message);
            console.error('Stack trace:', err.stack);
            
            alert("Terjadi kesalahan: " + err.message);
            btnSave.disabled = false;
            btnSave.textContent = "Simpan";
        }
    });

    /* ================= DROPDOWN ================= */
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

    /* ================= DUPLICATE ================= */
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

    /* ================= DELETE ================= */
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

    /* ================= SEARCH ================= */
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
    
    console.log('✅ Script loaded successfully');
});
</script>
@endsection