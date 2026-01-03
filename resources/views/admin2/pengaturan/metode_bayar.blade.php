@extends('layouts.master')

@section('title', 'Metode Pembayaran')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
    <a href="{{ route('admin2.pengaturan.index') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Metode Pembayaran</span>
</div>

<div class="p-4 space-y-4 pb-24">

    {{-- FORM TAMBAH METODE --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                <i class="bi bi-plus-circle-fill text-white text-2xl"></i>
            </div>
            <span class="text-lg font-bold">Tambah Metode Baru</span>
        </div>
        
        <div class="flex gap-3">
            <input type="text" id="inputMetodeBayar" placeholder="Masukkan nama metode pembayaran"
                   class="flex-1 p-4 rounded-2xl border-2 border-gray-200 bg-gray-50 text-gray-800 font-medium focus:border-yellow-400 focus:outline-none transition">
            <button id="btnTambahMetode" class="px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-2xl transition shadow-lg active:scale-95 flex items-center gap-2">
                <i class="bi bi-plus-lg text-xl"></i>
                Tambah
            </button>
        </div>
    </div>

    {{-- LIST METODE PEMBAYARAN --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                <i class="bi bi-credit-card-fill text-white text-xl"></i>
            </div>
            <span class="text-lg font-bold">Daftar Metode Pembayaran</span>
        </div>

        <ul id="listMetode" class="space-y-3">
            @forelse($metode as $m)
            <li class="flex justify-between items-center p-4 bg-gradient-to-r from-gray-50 to-white rounded-2xl border-2 border-gray-200 hover:border-yellow-400 transition group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center">
                        <i class="bi bi-credit-card text-white"></i>
                    </div>
                    <span class="font-semibold text-gray-800 group-hover:text-yellow-600 transition">{{ $m->nama_metode_bayar }}</span>
                </div>
                <button data-id="{{ $m->id_metode_bayar }}" class="hapusMetode p-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition active:scale-95 shadow">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </li>
            @empty
            <li class="text-center p-8 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300">
                <i class="bi bi-inbox text-5xl text-gray-300 mb-2"></i>
                <p class="text-gray-500 font-semibold">Belum ada metode pembayaran</p>
                <p class="text-sm text-gray-400">Tambahkan metode pembayaran di atas</p>
            </li>
            @endforelse
        </ul>
    </div>

</div>

{{-- POPUP SUCCESS --}}
<div id="popupSuccess" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center animate__animated animate__zoomIn">
        <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="bi bi-check2 text-white text-6xl"></i>
        </div>
        <h1 class="text-2xl font-bold mb-2">Berhasil!</h1>
        <p id="successMessage" class="text-gray-600 mb-6">Metode pembayaran berhasil ditambahkan</p>
        <button id="btnCloseSuccess" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-2xl transition">
            Tutup
        </button>
    </div>
</div>

{{-- POPUP LOADING --}}
<div id="popupLoading" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center">
        <div class="w-20 h-20 border-4 border-yellow-400 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-lg font-semibold text-gray-700">Memproses...</p>
    </div>
</div>

{{-- POPUP KONFIRMASI HAPUS --}}
<div id="popupKonfirmasi" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center animate__animated animate__shakeX">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-exclamation-triangle-fill text-red-500 text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Metode?</h3>
        <p class="text-gray-600 mb-1">Apakah Anda yakin ingin menghapus:</p>
        <p id="namaMetodeHapus" class="text-lg font-bold text-red-600 mb-6"></p>
        <input type="hidden" id="idMetodeHapus">
        
        <div class="flex gap-3">
            <button id="btnBatalHapus" class="flex-1 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold rounded-2xl transition">
                Batal
            </button>
            <button id="btnKonfirmasiHapus" class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-2xl transition">
                Hapus
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    
    /* ===============================
       ELEMENTS
    =============================== */
    const inputMetode = document.getElementById('inputMetodeBayar');
    const btnTambah = document.getElementById('btnTambahMetode');
    const listMetode = document.getElementById('listMetode');
    
    const popupSuccess = document.getElementById('popupSuccess');
    const successMessage = document.getElementById('successMessage');
    const btnCloseSuccess = document.getElementById('btnCloseSuccess');
    const popupLoading = document.getElementById('popupLoading');
    
    const popupKonfirmasi = document.getElementById('popupKonfirmasi');
    const namaMetodeHapus = document.getElementById('namaMetodeHapus');
    const idMetodeHapus = document.getElementById('idMetodeHapus');
    const btnBatalHapus = document.getElementById('btnBatalHapus');
    const btnKonfirmasiHapus = document.getElementById('btnKonfirmasiHapus');

    /* ===============================
       TAMBAH METODE
    =============================== */
    btnTambah.addEventListener('click', async () => {
        const nama = inputMetode.value.trim();
        
        if (!nama) {
            alert('Nama metode wajib diisi!');
            inputMetode.focus();
            return;
        }

        popupLoading.classList.remove('hidden');

        try {
            const res = await fetch("{{ route('admin2.pengaturan.metode.store') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ nama_metode_bayar: nama })
            });

            const data = await res.json();
            
            popupLoading.classList.add('hidden');

            if (data.status) {
                // Tambahkan ke list
                const li = document.createElement('li');
                li.className = 'flex justify-between items-center p-4 bg-gradient-to-r from-gray-50 to-white rounded-2xl border-2 border-gray-200 hover:border-yellow-400 transition group';
                li.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center">
                            <i class="bi bi-credit-card text-white"></i>
                        </div>
                        <span class="font-semibold text-gray-800 group-hover:text-yellow-600 transition">${nama}</span>
                    </div>
                    <button data-id="${data.id || ''}" class="hapusMetode p-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition active:scale-95 shadow">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                `;
                
                // Hapus empty state jika ada
                const emptyState = listMetode.querySelector('.border-dashed');
                if (emptyState) {
                    emptyState.remove();
                }
                
                listMetode.appendChild(li);
                inputMetode.value = '';
                
                successMessage.textContent = 'Metode pembayaran berhasil ditambahkan!';
                popupSuccess.classList.remove('hidden');
            } else {
                alert('Gagal menambahkan metode pembayaran!');
            }
        } catch (err) {
            popupLoading.classList.add('hidden');
            console.error(err);
            alert('Terjadi kesalahan saat menambahkan metode!');
        }
    });

    /* ===============================
       HAPUS METODE (dengan konfirmasi)
    =============================== */
    listMetode.addEventListener('click', (e) => {
        if (!e.target.closest('.hapusMetode')) return;
        
        const btn = e.target.closest('.hapusMetode');
        const id = btn.dataset.id;
        const nama = btn.closest('li').querySelector('span').textContent;
        
        namaMetodeHapus.textContent = nama;
        idMetodeHapus.value = id;
        popupKonfirmasi.classList.remove('hidden');
    });

    btnBatalHapus.addEventListener('click', () => {
        popupKonfirmasi.classList.add('hidden');
    });

    btnKonfirmasiHapus.addEventListener('click', async () => {
        const id = idMetodeHapus.value;
        
        popupKonfirmasi.classList.add('hidden');
        popupLoading.classList.remove('hidden');

        try {
            const res = await fetch(`/pengaturan/metode-bayar/${id}`, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Content-Type': 'application/json'
                }
            });

            const data = await res.json();
            
            popupLoading.classList.add('hidden');

            if (data.status) {
                // Hapus dari DOM
                const btn = document.querySelector(`.hapusMetode[data-id="${id}"]`);
                if (btn) {
                    btn.closest('li').remove();
                }
                
                // Jika list kosong, tampilkan empty state
                if (listMetode.children.length === 0) {
                    const emptyLi = document.createElement('li');
                    emptyLi.className = 'text-center p-8 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300';
                    emptyLi.innerHTML = `
                        <i class="bi bi-inbox text-5xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 font-semibold">Belum ada metode pembayaran</p>
                        <p class="text-sm text-gray-400">Tambahkan metode pembayaran di atas</p>
                    `;
                    listMetode.appendChild(emptyLi);
                }
                
                successMessage.textContent = 'Metode pembayaran berhasil dihapus!';
                popupSuccess.classList.remove('hidden');
            } else {
                alert(data.message || 'Gagal menghapus metode pembayaran!');
            }
        } catch (err) {
            popupLoading.classList.add('hidden');
            console.error(err);
            alert('Terjadi kesalahan saat menghapus metode!');
        }
    });

    /* ===============================
       CLOSE SUCCESS
    =============================== */
    btnCloseSuccess.addEventListener('click', () => {
        popupSuccess.classList.add('hidden');
    });

    /* ===============================
       ENTER KEY SUBMIT
    =============================== */
    inputMetode.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            btnTambah.click();
        }
    });

});
</script>
@endsection