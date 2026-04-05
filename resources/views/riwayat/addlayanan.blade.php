{{-- ============================================================
     HALAMAN: TAMBAH LAYANAN KE TRANSAKSI
     Deskripsi: Menampilkan daftar layanan yang tersedia untuk
     dipilih dan ditambahkan ke transaksi yang sedang diedit.
     Admin dapat mencari layanan, memilih jenis, mengisi qty,
     dan memilih parfum sebelum menyimpan ke transaksi.
     Role: Admin
============================================================ --}}
@extends('layouts.master')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    {{-- Animasi goyang untuk validasi form gagal --}}
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    .shake { animation: shake 0.5s; }

    {{-- Animasi muncul untuk alert modal --}}
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to   { opacity: 1; transform: scale(1); }
    }
    .alert-modal { animation: fadeIn 0.3s ease; }
</style>

<div class="min-h-screen bg-gray-50 pb-24">

    {{-- ========================================
         HEADER
         Tombol kembali ke halaman Edit Transaksi
    ======================================== --}}
    <div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
        <a href="{{ route('riwayat.edit', $riwayat->id_transaksi) }}"
           class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Kelola Layanan</span>
    </div>

    <div class="px-8 py-6 space-y-6">

        {{-- ========================================
             SEARCH BAR + SORT
             Pencarian layanan secara real-time berdasarkan nama
        ======================================== --}}
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

        {{-- ========================================
             DAFTAR LAYANAN
             Setiap kartu layanan dapat diklik untuk memilihnya.
             Klik pada jenis layanan akan langsung pre-select jenis tersebut di modal.
        ======================================== --}}
        <div id="layananList" class="space-y-5">
            @forelse ($layananUtama as $item)
                {{-- Kartu layanan utama --}}
                <div class="layanan-item bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 relative group cursor-pointer"
                     data-id="{{ $item->id_layanan }}"
                     data-name="{{ $item->nama_layanan }}">

                    <div class="p-6">
                        {{-- Header kartu: nama layanan + proses + dropdown aksi --}}
                        <div class="flex items-start justify-between mb-4 pb-4 border-b-2 border-gray-100">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-800 capitalize mb-2">{{ $item->nama_layanan }}</h3>

                                @php
                                    # Parse proses layanan dari JSON atau string CSV 
                                    $icons = ['Cuci' => 'bi bi-droplet', 'Kering' => 'bi bi-wind', 'Setrika' => 'bi bi-iron'];
                                    $raw   = $item->proses ?? '';
                                    if (is_string($raw) && Str::startsWith(trim($raw), '[')) {
                                        $steps = json_decode($raw, true) ?: [];
                                    } else {
                                        $parts = array_map('trim', explode(',', trim($raw, "[]\"' ")));
                                        $steps = array_filter($parts, fn($s) => $s !== '');
                                    }
                                @endphp

                                {{-- Badge proses layanan (contoh: Cuci → Kering → Setrika) --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    @foreach($steps as $i => $step)
                                        <div class="flex items-center gap-1.5 bg-yellow-50 px-3 py-1.5 rounded-lg">
                                            <i class="{{ $icons[$step] ?? 'bi bi-gear' }} text-yellow-600 text-lg"></i>
                                            <span class="text-sm font-semibold text-gray-700">{{ $step }}</span>
                                        </div>
                                        @if($i < count($steps) - 1)
                                            <i class="bi bi-chevron-right text-gray-300"></i>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            {{-- Dropdown aksi: Duplikat / Hapus layanan --}}
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

                        {{-- Daftar jenis layanan dalam kartu ini --}}
                        @if($item->jenis->count() > 0)
                            <div class="space-y-4">
                                @foreach($item->jenis as $jenis)
                                    {{-- Item jenis layanan: klik untuk pre-select di modal --}}
                                    <div class="jenis-item flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer"
                                         data-id-jenis="{{ $jenis->id_jenis_layanan }}"
                                         data-nama="{{ $jenis->nama_jenis }}">
                                        {{-- Gambar jenis layanan --}}
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
                                        {{-- Detail jenis layanan --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-lg text-gray-800 capitalize mb-1 truncate">{{ $jenis->nama_jenis }}</p>
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
                            {{-- State kosong jika belum ada jenis layanan --}}
                            <div class="text-center py-8">
                                <i class="bi bi-inbox text-4xl text-gray-300 mb-2"></i>
                                <p class="text-gray-400 text-sm">Belum ada jenis layanan</p>
                            </div>
                        @endif
                    </div>
                </div>

            @empty
                {{-- State kosong jika belum ada layanan sama sekali --}}
                <div class="text-center py-16">
                    <i class="bi bi-gear-wide-connected text-5xl text-gray-400 mb-4"></i>
                    <p class="text-xl text-gray-500 font-semibold">Tidak ada layanan</p>
                    <p class="text-gray-400 text-sm mt-2">Silahkan tambahkan layanan baru</p>
                </div>
            @endforelse
        </div>

        {{-- Tombol tambah layanan baru ke master layanan --}}
        <a href="{{ route('layanan.create', ['from' => 'transaksi']) }}"
           class="block bg-yellow-400 hover:bg-yellow-500 py-4 rounded-2xl font-bold text-black text-center shadow-lg hover:shadow-xl transition-all hover:scale-105 flex items-center justify-center gap-2">
            <i class="bi bi-plus-circle-fill text-xl"></i>
            Tambah Layanan
        </a>
    </div>
</div>

{{-- ========================================
     MODAL TAMBAH LAYANAN KE TRANSAKSI
     Form untuk memilih jenis layanan, mengisi qty, dan parfum
======================================== --}}
<div id="modalLayanan" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white modal-box w-full max-w-lg mx-auto rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-yellow-400 p-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-black text-center"></h2>
        </div>
        <div class="p-6 space-y-5">

            {{-- Dropdown Pilih Jenis Layanan --}}
            <div>
                <label class="block font-bold text-gray-700 mb-2">Pilih Jenis Layanan</label>
                <div class="relative">
                    <i class="bi bi-box-seam absolute left-4 top-1/2 transform -translate-y-1/2 text-yellow-600 text-xl z-10"></i>
                    <select id="jenisSelect" class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all appearance-none bg-white">
                        <option value="">Pilih Jenis Layanan</option>
                    </select>
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
                {{-- Info harga per satuan setelah jenis dipilih --}}
                <div id="hargaInfo" class="mt-2 text-sm text-gray-600 hidden">
                    <i class="bi bi-info-circle"></i>
                    <span id="hargaText"></span>
                </div>
            </div>

            {{-- Input Jumlah Qty --}}
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

            {{-- Dropdown Parfum --}}
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

            {{-- Tombol aksi modal --}}
            <div class="flex gap-3 pt-2">
                <button onclick="closeModal()"
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

{{-- ========================================
     CUSTOM ALERT MODAL
     Digunakan untuk menampilkan pesan error validasi form
     dengan tampilan yang lebih menarik dari alert() bawaan browser
======================================== --}}
<div id="alertModal" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[9999] hidden">
    <div class="alert-modal bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden">
        <div id="alertHeader" class="p-6 flex items-center justify-center">
            <div id="alertIcon" class="w-16 h-16 rounded-full flex items-center justify-center"></div>
        </div>
        <div class="px-6 pb-6 text-center">
            <h3 id="alertTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
            <p id="alertMessage" class="text-gray-600 mb-6"></p>
            <button id="alertButton"
                    class="w-full py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                OK, Mengerti
            </button>
        </div>
    </div>
</div>

{{-- ========================================
     MODAL KONFIRMASI DUPLIKAT LAYANAN
======================================== --}}
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

{{-- ========================================
     MODAL KONFIRMASI HAPUS LAYANAN MASTER
======================================== --}}
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

// =============================
// REFERENSI ELEMEN DOM
// =============================
const modal           = document.getElementById("modalLayanan");
const modalBox        = modal.querySelector(".modal-box");
const modalTitle      = document.getElementById("modalTitle");
const jenisSelect     = document.getElementById("jenisSelect");
const qtyInput        = document.getElementById("qtyInput");
const parfumSelect    = document.getElementById("parfumSelect");
const btnSave         = document.getElementById("btnSave");
const hargaInfo       = document.getElementById("hargaInfo");
const hargaText       = document.getElementById("hargaText");
const modalDuplicate  = document.getElementById("modalDuplicate");
const btnConfirmDuplicate = document.getElementById("btnConfirmDuplicate");
const modalDelete     = document.getElementById("modalDelete");
const formDelete      = document.getElementById("formDelete");

// Data jenis layanan dari controller (digunakan untuk populate dropdown)
const jenisLayananData = {!! json_encode($jenisLayananData ?? []) !!};

// =============================
// FUNGSI CUSTOM ALERT MODAL
// Menampilkan alert dengan tipe: 'warning', 'error', atau 'info'
// =============================
function showAlert(type, title, message) {
    const alertModal   = document.getElementById('alertModal');
    const alertHeader  = document.getElementById('alertHeader');
    const alertIcon    = document.getElementById('alertIcon');
    const alertTitle   = document.getElementById('alertTitle');
    const alertMessage = document.getElementById('alertMessage');
    const alertButton  = document.getElementById('alertButton');
    
    // Reset class sebelum diisi ulang
    alertIcon.className   = 'w-16 h-16 rounded-full flex items-center justify-center';
    alertButton.className = 'w-full py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all active:scale-95';
    
    // Atur tampilan berdasarkan tipe alert
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
    
    alertTitle.textContent   = title;
    alertMessage.textContent = message;
    alertModal.classList.remove('hidden');
    
    // Tombol OK: tutup alert dan fokus ke input bermasalah
    alertButton.onclick = () => {
        alertModal.classList.add('hidden');
        if (type === 'warning' || type === 'error') {
            if (message.includes('Jenis')) {
                setTimeout(() => jenisSelect.focus(), 100);
            } else {
                setTimeout(() => qtyInput.focus(), 100);
            }
        }
    };
    
    // Tutup alert saat klik backdrop
    alertModal.onclick = (e) => {
        if (e.target === alertModal) alertModal.classList.add('hidden');
    };
}

// =============================
// VALIDASI INPUT QTY
// Hanya boleh angka, titik desimal, max 2 desimal
// =============================
qtyInput.addEventListener('input', function (e) {
    let value = e.target.value;
    // Hapus karakter selain angka dan titik
    value = value.replace(/[^\d.,]/g, '').replace(',', '.');
    // Cegah lebih dari satu titik desimal
    const parts = value.split('.');
    if (parts.length > 2) value = parts[0] + '.' + parts.slice(1).join('');
    // Cegah leading zero (kecuali 0.xxx)
    if (value.length > 1 && value[0] === '0' && value[1] !== '.') {
        value = value.replace(/^0+/, '');
    }
    // Batasi 2 angka di belakang desimal
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    e.target.value = value;
});

// Validasi saat blur: kosongkan jika nilai tidak valid
qtyInput.addEventListener('blur', function (e) {
    const value = parseFloat(e.target.value);
    if (isNaN(value) || value < 0.01) {
        e.target.value = '';
        e.target.classList.add('border-red-500');
    } else {
        e.target.classList.remove('border-red-500');
    }
});

// Validasi paste: hanya izinkan angka yang valid
qtyInput.addEventListener('paste', function (e) {
    e.preventDefault();
    const paste  = (e.clipboardData || window.clipboardData).getData('text');
    const cleaned = paste.replace(/[^\d.,]/g, '').replace(',', '.');
    const number  = parseFloat(cleaned);
    if (!isNaN(number) && number >= 0.01) e.target.value = number.toString();
});

// =============================
// BUKA MODAL PILIH LAYANAN
// Jika preselectedJenisId diberikan, dropdown jenis langsung dipilih
// =============================
function openModal(name, idLayanan, riwayatId, preselectedJenisId = null) {
    modal.classList.remove("hidden");
    document.body.style.overflow = "hidden";
    modalTitle.innerText = name;
    btnSave.dataset.layanan = idLayanan;
    btnSave.dataset.riwayat = riwayatId;

    // Reset semua field form
    jenisSelect.innerHTML = '<option value="">Pilih Jenis Layanan</option>';
    qtyInput.value        = "";
    qtyInput.classList.remove('border-red-500');
    parfumSelect.value    = "";
    hargaInfo.classList.add("hidden");

    // Isi dropdown jenis layanan berdasarkan ID layanan yang dipilih
    const jenisOptions = jenisLayananData[idLayanan] || [];
    jenisOptions.forEach(jenis => {
        const option       = document.createElement('option');
        option.value       = jenis.id;
        option.textContent = `${jenis.nama} - Rp ${formatRupiah(jenis.harga)} / ${jenis.satuan}`;
        option.dataset.harga  = jenis.harga;
        option.dataset.satuan = jenis.satuan;
        jenisSelect.appendChild(option);
    });

    // Jika ada jenis yang sudah dipilih, pre-select dan fokus ke qty
    if (preselectedJenisId) {
        jenisSelect.value = preselectedJenisId;
        const event = new Event('change');
        jenisSelect.dispatchEvent(event);
        setTimeout(() => qtyInput.focus(), 100);
    } else {
        jenisSelect.focus();
    }
}

function closeModal() {
    modal.classList.add("hidden");
    document.body.style.overflow = "auto";
}
window.closeModal = closeModal;

// Format angka ke format Rupiah (tanpa simbol Rp)
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

// Tampilkan info harga per satuan saat jenis layanan dipilih
jenisSelect.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    if (this.value) {
        hargaText.textContent = `Harga: Rp ${formatRupiah(selected.dataset.harga)} per ${selected.dataset.satuan}`;
        hargaInfo.classList.remove("hidden");
    } else {
        hargaInfo.classList.add("hidden");
    }
});

// Tutup modal saat klik backdrop
modal.addEventListener("click", e => { if (e.target === modal) closeModal(); });
modalBox.addEventListener("click", e => e.stopPropagation());
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
});

// =============================
// KLIK KARTU LAYANAN
// Buka modal tanpa jenis pre-selected
// =============================
document.querySelectorAll(".layanan-item").forEach(card => {
    card.addEventListener("click", e => {
        // Jangan buka modal jika sedang di dalam dropdown atau jenis item
        if (!modal.classList.contains("hidden")) return;
        if (e.target.closest(".dropdown-area"))  return;
        if (e.target.closest(".jenis-item"))     return;

        openModal(card.dataset.name, card.dataset.id, "{{ $riwayat->id_transaksi }}");
    });
});

// =============================
// KLIK ITEM JENIS LAYANAN
// Buka modal dengan jenis yang sudah pre-selected
// =============================
document.querySelectorAll(".jenis-item").forEach(item => {
    item.addEventListener("click", e => {
        e.stopPropagation(); // Jangan propagate ke kartu layanan induk

        const idLayanan   = item.closest('.layanan-item').dataset.id;
        const namaLayanan = item.closest('.layanan-item').dataset.name;
        const idJenis     = item.dataset.idJenis || item.dataset.id;

        openModal(namaLayanan, idLayanan, "{{ $riwayat->id_transaksi }}", idJenis);
    });
});

// =============================
// TOMBOL SIMPAN - SUBMIT KE CONTROLLER
// Validasi input lalu kirim data ke addLayanan endpoint
// =============================
btnSave.addEventListener("click", async e => {
    e.preventDefault();
    e.stopPropagation();

    const idJenis  = jenisSelect.value;
    let qtyValue   = qtyInput.value.trim();

    // Validasi: jenis layanan wajib dipilih
    if (!idJenis) {
        jenisSelect.classList.add('border-red-500', 'shake');
        setTimeout(() => jenisSelect.classList.remove('shake'), 500);
        showAlert('warning', 'Oops! Jenis Layanan Belum Dipilih', 'Mohon pilih jenis layanan terlebih dahulu sebelum melanjutkan.');
        return;
    }
    jenisSelect.classList.remove('border-red-500');

    // Validasi: qty wajib diisi
    if (!qtyValue) {
        qtyInput.classList.add('border-red-500', 'shake');
        setTimeout(() => qtyInput.classList.remove('shake'), 500);
        showAlert('warning', 'Oops! Kuantitas Belum Diisi', 'Mohon isi jumlah kuantitas terlebih dahulu. Minimal 0.01');
        return;
    }

    const qty = parseFloat(qtyValue);

    // Validasi: format angka harus valid
    if (isNaN(qty)) {
        qtyInput.classList.add('border-red-500', 'shake');
        setTimeout(() => qtyInput.classList.remove('shake'), 500);
        showAlert('error', 'Format Tidak Valid!', 'Gunakan format angka yang benar. Contoh: 1, 2.5, atau 10.75');
        return;
    }

    // Validasi: qty minimal 0.01
    if (qty < 0.01) {
        qtyInput.classList.add('border-red-500', 'shake');
        setTimeout(() => qtyInput.classList.remove('shake'), 500);
        showAlert('warning', 'Kuantitas Terlalu Kecil!', 'Jumlah minimal adalah 0.01. Silakan masukkan nilai yang lebih besar.');
        return;
    }

    qtyInput.classList.remove('border-red-500');

    const idRiwayat = btnSave.dataset.riwayat;
    const idLayanan = btnSave.dataset.layanan;
    const parfum    = parfumSelect.value || '';

    // Siapkan form data untuk dikirim
    const formData = new FormData();
    formData.append('id_layanan',       idLayanan);
    formData.append('id_jenis_layanan', idJenis);
    formData.append('qty',              qty);
    formData.append('parfum',           parfum);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    // Nonaktifkan tombol selama proses simpan
    btnSave.disabled     = true;
    btnSave.textContent  = "Menyimpan...";

    try {
        const res = await fetch(`/admin/riwayat/${idRiwayat}/add-layanan`, {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        });

        if (!res.ok) {
            const errorData = await res.json();
            showAlert('error', 'Gagal Menyimpan!', errorData.message || `Server error: ${res.status}`);
            btnSave.disabled = false;
            btnSave.textContent = "Simpan";
            return;
        }

        const data = await res.json();

        if (data.success) {
            // Redirect ke halaman edit setelah berhasil
            window.location.href = `/admin/riwayat/${idRiwayat}/edit`;
        } else {
            showAlert('error', 'Gagal Menambah Layanan', data.message || 'Terjadi kesalahan saat menyimpan data.');
            btnSave.disabled    = false;
            btnSave.textContent = "Simpan";
        }
    } catch (err) {
        console.error('Error simpan layanan:', err);
        showAlert('error', 'Gagal Menyimpan!', 'Terjadi kesalahan: ' + err.message);
        btnSave.disabled    = false;
        btnSave.textContent = "Simpan";
    }
});

// =============================
// DROPDOWN AKSI (3 TITIK)
// Klik tombol dropdown untuk tampilkan/sembunyikan menu
// =============================
document.querySelectorAll(".dropdown-area").forEach(area => {
    area.addEventListener("click", e => e.stopPropagation());
});

document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", function (e) {
        e.stopPropagation();
        const menu = this.nextElementSibling;
        // Tutup semua dropdown lain
        document.querySelectorAll(".dropdown-menu").forEach(m => {
            if (m !== menu) m.classList.add("hidden");
        });
        menu.classList.toggle("hidden");
    });
});

// Tutup semua dropdown saat klik di luar
document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu").forEach(m => m.classList.add("hidden"));
});

// =============================
// MODAL DUPLIKAT LAYANAN
// =============================
window.confirmDuplicate = function (url) {
    modalDuplicate.classList.remove("hidden");
    btnConfirmDuplicate.onclick = () => window.location.href = url;
};
window.closeDuplicateModal = function () {
    modalDuplicate.classList.add("hidden");
};
modalDuplicate.addEventListener("click", e => {
    if (e.target === modalDuplicate) closeDuplicateModal();
});

// =============================
// MODAL HAPUS LAYANAN MASTER
// =============================
window.confirmDelete = function (url) {
    modalDelete.classList.remove("hidden");
    formDelete.action = url;
};
window.closeDeleteModal = function () {
    modalDelete.classList.add("hidden");
};
modalDelete.addEventListener("click", e => {
    if (e.target === modalDelete) closeDeleteModal();
});

// =============================
// PENCARIAN LAYANAN REAL-TIME
// Filter kartu layanan berdasarkan nama
// =============================
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.layanan-item').forEach(item => {
            const name = item.dataset.name.toLowerCase();
            item.style.display = name.includes(searchTerm) ? 'block' : 'none';
        });
    });
}

}); // end DOMContentLoaded
</script>
@endsection