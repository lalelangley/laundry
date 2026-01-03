@extends('layouts.master')

@section('title', 'Pengaturan')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
    <a href="{{ route('admin2.dashboard') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Pengaturan</span>
</div>

<div class="p-4 space-y-4 pb-24">

    {{-- FOTO OUTLET --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl">
        <div class="flex flex-col items-center">
            <div class="relative w-32 h-32 mb-4">
                <div class="w-full h-full rounded-full overflow-hidden bg-gray-200 flex items-center justify-center border-4 border-yellow-400">
                    <img
                        id="previewOutletImage"
                        src="{{ 
                            !empty($pengaturan['foto_outlet']) 
                                ? asset('storage/' . $pengaturan['foto_outlet']) 
                                : asset('images/default-outlet.png') 
                        }}"
                        class="w-full h-full object-cover"
                    >
                    @if(!empty($pengaturan['foto_outlet']))
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            document.getElementById('btnHapusFoto').classList.remove('hidden');
                        });
                    </script>
                    @endif
                </div>
                <button onclick="document.getElementById('inputFotoOutlet').click()" class="absolute bottom-0 right-0 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                    <i class="bi bi-camera-fill text-white text-lg"></i>
                </button>
                <button id="btnHapusFoto" class="absolute top-0 right-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center shadow-lg border-2 border-white hidden">
                    <i class="bi bi-x-lg text-white text-sm"></i>
                </button>
            </div>
            <input type="file" id="inputFotoOutlet" accept="image/*" class="hidden">
        </div>
    </div>

    {{-- NAMA OUTLET --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                <i class="bi bi-shop text-white text-xl"></i>
            </div>
            <span class="text-lg font-bold">Nama Outlet</span>
        </div>
        <input type="text" id="namaOutlet" value="{{ $pengaturan['nama_outlet'] ?? '' }}"
               class="w-full p-4 rounded-2xl border-2 border-gray-200 bg-gray-50 text-gray-800 font-medium focus:border-yellow-400 focus:outline-none transition">
    </div>

    {{-- ALAMAT OUTLET --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                <i class="bi bi-geo-alt-fill text-white text-xl"></i>
            </div>
            <span class="text-lg font-bold">Alamat Outlet</span>
        </div>
        <textarea id="alamatOutlet" rows="3" placeholder="Masukkan alamat lengkap outlet" 
                  class="w-full p-4 rounded-2xl border-2 border-gray-200 bg-gray-50 text-gray-800 font-medium focus:border-yellow-400 focus:outline-none transition resize-none">{{ $pengaturan['alamat_outlet'] ?? '' }}</textarea>
    </div>

    {{-- SETTING OMZET --}}
    <button id="btnSettingOmzet" class="w-full bg-white rounded-3xl p-5 shadow-xl transition-all hover:scale-[1.02] cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="bi bi-calculator-fill text-white text-xl"></i>
                </div>
                <div class="text-left">
                    <p class="text-lg font-bold">Berdasarkan Transaksi Selesai</p>
                    <p class="text-sm text-gray-500">Atur perhitungan omzet</p>
                </div>
            </div>
            <i class="bi bi-chevron-right text-yellow-400 text-2xl"></i>
        </div>
    </button>

    {{-- METODE PEMBAYARAN --}}
    <a href="{{ route('admin2.pengaturan.metode') }}" class="block w-full bg-white rounded-3xl p-5 shadow-xl transition-all hover:scale-[1.02] cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="bi bi-credit-card-fill text-white text-xl"></i>
                </div>
                <div class="text-left">
                    <p class="text-lg font-bold">Metode Pembayaran</p>
                    <p class="text-sm text-gray-500">Kelola metode pembayaran</p>
                </div>
            </div>
            <i class="bi bi-chevron-right text-yellow-400 text-2xl"></i>
        </div>
    </a>

    {{-- BACKUP & RESTORE --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                <i class="bi bi-shield-fill-check text-white text-xl"></i>
            </div>
            <span class="text-lg font-bold">Backup & Restore</span>
        </div>
        
        <div class="grid grid-cols-2 gap-3">
            <button id="btnBackup" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl shadow-lg hover:shadow-xl transition active:scale-95">
                <i class="bi bi-cloud-upload-fill text-white text-3xl mb-2"></i>
                <span class="text-white font-bold">Backup</span>
                <span class="text-xs text-white opacity-90">Simpan data</span>
            </button>
            
            <button id="btnRestore" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg hover:shadow-xl transition active:scale-95">
                <i class="bi bi-cloud-download-fill text-white text-3xl mb-2"></i>
                <span class="text-white font-bold">Restore</span>
                <span class="text-xs text-white opacity-90">Pulihkan data</span>
            </button>
        </div>
        
        <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-200">
            <p class="text-xs text-blue-700 flex items-start gap-2">
                <i class="bi bi-info-circle-fill mt-0.5"></i>
                <span>Backup data secara berkala untuk menghindari kehilangan data penting</span>
            </p>
        </div>
    </div>

</div>

{{-- BUTTON SIMPAN FIXED --}}
<div class="fixed bottom-0 left-0 w-full bg-white px-5 py-4 shadow-2xl z-50 border-t-2 border-gray-100">
    <button id="btnSimpanPengaturan" class="w-full py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-2xl text-lg shadow-lg active:scale-95 transition flex items-center justify-center gap-2">
        <i class="bi bi-check-circle-fill text-xl"></i>
        Simpan Pengaturan
    </button>
</div>

{{-- POPUP SUCCESS --}}
<div id="popupSuccess" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center animate__animated animate__zoomIn">
        <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="bi bi-check2 text-white text-6xl"></i>
        </div>
        <h1 class="text-2xl font-bold mb-2">Berhasil!</h1>
        <p class="text-gray-600 mb-6">Pengaturan telah disimpan</p>
        <button id="btnCloseSuccess" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-2xl transition">
            Tutup
        </button>
    </div>
</div>

{{-- POPUP LOADING --}}
<div id="popupLoading" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center">
        <div class="w-20 h-20 border-4 border-yellow-400 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-lg font-semibold text-gray-700">Menyimpan data...</p>
    </div>
</div>

{{-- POPUP BACKUP/RESTORE --}}
<div id="popupBackup" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center animate__animated animate__fadeInUp">
        <div id="backupIcon" class="w-24 h-24 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="bi bi-cloud-upload-fill text-white text-5xl"></i>
        </div>
        <h1 id="backupTitle" class="text-2xl font-bold mb-2">Backup Data</h1>
        <p id="backupMessage" class="text-gray-600 mb-6">Data berhasil di-backup!</p>
        <button id="btnCloseBackup" class="w-full py-3 bg-yellow-400 hover:bg-yellow-500 text-white font-bold rounded-2xl transition">
            Tutup
        </button>
    </div>
</div>

{{-- MODAL SETTING OMZET --}}
<div id="modalSettingOmzet" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate__animated animate__fadeInUp">
        <div class="bg-yellow-400 px-5 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Setting Omzet</h2>
            <button id="closeModalOmzet" class="text-white text-2xl font-bold">✕</button>
        </div>
        
        <div class="p-6 space-y-4">
            <button id="optionTransaksiSelesai" class="w-full p-5 rounded-2xl border-2 border-yellow-400 bg-yellow-50 flex items-center gap-4 transition hover:bg-yellow-100">
                <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-check-circle-fill text-white text-xl"></i>
                </div>
                <div class="text-left flex-1">
                    <p class="font-bold text-lg">Berdasarkan Transaksi Selesai</p>
                    <p class="text-sm text-gray-600">Omzet dihitung saat transaksi selesai</p>
                </div>
            </button>
            
            <button id="optionTransaksiLunas" class="w-full p-5 rounded-2xl border-2 border-gray-200 bg-white flex items-center gap-4 transition hover:bg-gray-50">
                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-circle text-white text-xl"></i>
                </div>
                <div class="text-left flex-1">
                    <p class="font-bold text-lg">Berdasarkan Transaksi Lunas</p>
                    <p class="text-sm text-gray-600">Omzet dihitung saat transaksi lunas</p>
                </div>
            </button>
        </div>
        
        <div class="px-6 pb-6">
            <button id="btnSimpanOmzet" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-2xl transition">
                Simpan
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {

    /* ===============================
       FOTO OUTLET
    =============================== */
    const inputFoto = document.getElementById('inputFotoOutlet');
    const previewImage = document.getElementById('previewOutletImage');
    const btnHapusFoto = document.getElementById('btnHapusFoto');
    let fotoOutlet = null;

    inputFoto.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;

        fotoOutlet = file;
        const reader = new FileReader();
        reader.onload = ev => {
            previewImage.src = ev.target.result;
            btnHapusFoto.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });

    btnHapusFoto.addEventListener('click', () => {
        previewImage.src = "{{ asset('images/default-outlet.png') }}";
        inputFoto.value = '';
        fotoOutlet = null;
        btnHapusFoto.classList.add('hidden');
    });

    /* ===============================
       SIMPAN PENGATURAN
    =============================== */
    const btnSimpan = document.getElementById('btnSimpanPengaturan');
    const popupLoading = document.getElementById('popupLoading');
    const popupSuccess = document.getElementById('popupSuccess');
    const btnCloseSuccess = document.getElementById('btnCloseSuccess');

    btnSimpan.addEventListener('click', async () => {
        const namaOutlet = document.getElementById('namaOutlet').value.trim();
        const alamatOutlet = document.getElementById('alamatOutlet').value.trim();

        if (!namaOutlet || !alamatOutlet) {
            alert('Nama & alamat outlet wajib diisi');
            return;
        }

        popupLoading.classList.remove('hidden');

        try {
            const formData = new FormData();
            formData.append('nama_outlet', namaOutlet);
            formData.append('alamat_outlet', alamatOutlet);
            if (fotoOutlet) formData.append('foto_outlet', fotoOutlet);

            const res = await fetch("{{ route('admin2.pengaturan.update') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: formData
            });

            if (!res.ok) throw new Error('Gagal');

            popupLoading.classList.add('hidden');
            popupSuccess.classList.remove('hidden');

        } catch (e) {
            popupLoading.classList.add('hidden');
            alert('Gagal menyimpan pengaturan');
        }
    });

    btnCloseSuccess.addEventListener('click', () => {
        popupSuccess.classList.add('hidden');
    });

    /* ===============================
       BACKUP
    =============================== */
    document.getElementById('btnBackup').addEventListener('click', async () => {
        popupLoading.classList.remove('hidden');
        await fetch("{{ route('admin2.pengaturan.backup') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
        });
        popupLoading.classList.add('hidden');
        document.getElementById('popupBackup').classList.remove('hidden');
    });

    document.getElementById('btnRestore').addEventListener('click', async () => {
        if (!confirm('Restore data?')) return;
        popupLoading.classList.remove('hidden');
        await fetch("{{ route('admin2.pengaturan.restore') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
        });
        location.reload();
    });

    document.getElementById('btnCloseBackup')
        .addEventListener('click', () =>
            document.getElementById('popupBackup').classList.add('hidden')
        );

    /* ===============================
       SETTING OMZET
    =============================== */
    const modalOmzet = document.getElementById('modalSettingOmzet');
    let selectedOmzet = "{{ $pengaturan['hitung_omzet_dari'] ?? 'selesai' }}";

    document.getElementById('btnSettingOmzet')
        .addEventListener('click', () => modalOmzet.classList.remove('hidden'));

    document.getElementById('closeModalOmzet')
        .addEventListener('click', () => modalOmzet.classList.add('hidden'));

    document.getElementById('optionTransaksiSelesai')
        .addEventListener('click', () => selectedOmzet = 'selesai');

    document.getElementById('optionTransaksiLunas')
        .addEventListener('click', () => selectedOmzet = 'lunas');

    document.getElementById('btnSimpanOmzet')
        .addEventListener('click', async () => {
            popupLoading.classList.remove('hidden');
            modalOmzet.classList.add('hidden');

            await fetch("{{ route('admin2.pengaturan.omzet') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ hitung_omzet_dari: selectedOmzet })
            });

            popupLoading.classList.add('hidden');
            popupSuccess.classList.remove('hidden');
        });

});
</script>
@endsection