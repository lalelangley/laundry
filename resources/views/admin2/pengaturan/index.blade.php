{{-- FE-DOC: Template frontend untuk resources/views/admin2/pengaturan/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Pengaturan')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
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
                    {{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
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
            <p class="text-xs text-gray-500 mt-3 text-center">
                Format gambar JPG, JPEG, PNG. Maksimal 2 MB.
            </p>
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
        
        <div class="grid grid-cols-2 gap-3 mb-4">
            {{-- BACKUP BUTTON - Conditional --}}
            @if($permissions['can_backup'])
            <button id="btnBackup" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl shadow-lg hover:shadow-xl transition active:scale-95">
                <i class="bi bi-cloud-upload-fill text-white text-3xl mb-2"></i>
                <span class="text-white font-bold">Backup</span>
                <span class="text-xs text-white opacity-90">Simpan data</span>
            </button>
            @else
            <div class="flex flex-col items-center justify-center p-4 bg-gray-200 rounded-2xl opacity-50 cursor-not-allowed">
                <i class="bi bi-cloud-upload-fill text-gray-400 text-3xl mb-2"></i>
                <span class="text-gray-500 font-bold">Backup</span>
                <span class="text-xs text-gray-400">Tidak diizinkan</span>
            </div>
            @endif
            
            {{-- RESTORE BUTTON - Conditional --}}
            @if($permissions['can_restore'])
            <button id="btnRestore" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg hover:shadow-xl transition active:scale-95">
                <i class="bi bi-cloud-download-fill text-white text-3xl mb-2"></i>
                <span class="text-white font-bold">Restore</span>
                <span class="text-xs text-white opacity-90">Pulihkan data</span>
            </button>
            @else
            <div class="flex flex-col items-center justify-center p-4 bg-gray-200 rounded-2xl opacity-50 cursor-not-allowed">
                <i class="bi bi-cloud-download-fill text-gray-400 text-3xl mb-2"></i>
                <span class="text-gray-500 font-bold">Restore</span>
                <span class="text-xs text-gray-400">Tidak diizinkan</span>
            </div>
            @endif
        </div>
        
        {{-- LIST BACKUP FILES --}}
        <div id="backupListContainer" class="mt-4 space-y-2 hidden">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-bold text-gray-700">Riwayat Backup:</p>
                <button id="btnRefreshBackups" class="text-yellow-500 text-sm hover:text-yellow-600">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            <div id="backupList" class="space-y-2 max-h-60 overflow-y-auto">
                <!-- Will be populated by JavaScript -->
            </div>
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
        <p id="successMessage" class="text-gray-600 mb-6">Pengaturan telah disimpan</p>
        <button id="btnCloseSuccess" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-2xl transition">
            Tutup
        </button>
    </div>
</div>

{{-- POPUP LOADING --}}
<div id="popupLoading" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center">
        <div class="w-20 h-20 border-4 border-yellow-400 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p id="loadingMessage" class="text-lg font-semibold text-gray-700">Menyimpan data...</p>
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

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {

    // ✅ PASS PERMISSIONS FROM PHP TO JS
    const permissions = @json($permissions);
    console.log('🔐 Permissions:', permissions);

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
       SIMPAN PENGATURAN - FIXED ERROR 403
    =============================== */
    const btnSimpan = document.getElementById('btnSimpanPengaturan');
    const popupLoading = document.getElementById('popupLoading');
    const popupSuccess = document.getElementById('popupSuccess');
    const btnCloseSuccess = document.getElementById('btnCloseSuccess');
    const loadingMessage = document.getElementById('loadingMessage');
    const successMessage = document.getElementById('successMessage');

    btnSimpan.addEventListener('click', async () => {
        const namaOutlet = document.getElementById('namaOutlet').value.trim();
        const alamatOutlet = document.getElementById('alamatOutlet').value.trim();

        if (!namaOutlet || !alamatOutlet) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Nama & alamat outlet wajib diisi',
                    confirmButtonColor: '#f59e0b'
                });
            } else {
                alert('Nama & alamat outlet wajib diisi');
            }
            return;
        }

        loadingMessage.textContent = 'Menyimpan pengaturan...';
        popupLoading.classList.remove('hidden');

        const formData = new FormData();
        formData.append('nama_outlet', namaOutlet);
        formData.append('alamat_outlet', alamatOutlet);
        if (fotoOutlet) {
            formData.append('foto_outlet', fotoOutlet);
        }

        // ✅ MENGGUNAKAN GLOBAL ERROR HANDLER
        const data = await fetchWithErrorHandling("{{ route('admin2.pengaturan.update') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: formData
        });

        popupLoading.classList.add('hidden');

        if (!data) return; // Error sudah ditangani oleh global handler

        if (data.status) {
            successMessage.textContent = data.message || 'Pengaturan telah disimpan';
            popupSuccess.classList.remove('hidden');
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data.message || 'Gagal menyimpan pengaturan',
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert(data.message || 'Gagal menyimpan pengaturan');
            }
        }
    });

    btnCloseSuccess.addEventListener('click', () => {
        popupSuccess.classList.add('hidden');
        location.reload(); // Reload untuk update gambar
    });

    /* ===============================
       LOAD BACKUP LIST - WITH PERMISSIONS
    =============================== */
    async function loadBackupList() {
        console.log('📡 Fetching backup list...');
        
        try {
            const timestamp = new Date().getTime();
            const res = await fetch("{{ route('admin2.pengaturan.backups.list') }}?t=" + timestamp);
            
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }
            
            const data = await res.json();
            console.log('📦 Data received:', data);
            
            const container = document.getElementById('backupList');
            const listContainer = document.getElementById('backupListContainer');
            
            if (!container || !listContainer) {
                console.error('❌ Element tidak ditemukan!');
                return;
            }
            
            if (data.status && data.backups && data.backups.length > 0) {
                console.log('✅ Menampilkan', data.backups.length, 'backup');
                
                listContainer.style.display = 'block';
                listContainer.classList.remove('hidden');
                container.innerHTML = '';
                
                data.backups.forEach(backup => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition';
                    
                    // ✅ BUILD HTML WITH CONDITIONAL DELETE BUTTON
                    let actionsHTML = `
                        <a href="{{ url('kasir/pengaturan/backups/download') }}/${backup.filename}" 
                           class="px-3 py-2 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition flex items-center gap-1"
                           download>
                            <i class="bi bi-download"></i>
                            Download
                        </a>
                    `;
                    
                    // ✅ ONLY SHOW DELETE BUTTON IF PERMISSION GRANTED
                    if (permissions.can_delete_backup) {
                        actionsHTML += `
                            <button onclick="deleteBackup('${backup.filename}')" 
                                    class="ml-2 px-3 py-2 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition flex items-center gap-1">
                                <i class="bi bi-trash"></i>
                                Hapus
                            </button>
                        `;
                    }
                    
                    div.innerHTML = `
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800">${backup.filename}</p>
                            <p class="text-xs text-gray-500">${backup.date} · ${backup.size}</p>
                        </div>
                        <div class="flex gap-2">
                            ${actionsHTML}
                        </div>
                    `;
                    container.appendChild(div);
                });
                
            } else {
                console.log('⚠️ Tidak ada backup');
                listContainer.classList.add('hidden');
            }
        } catch (e) {
            console.error('❌ Error loading backup list:', e);
        }
    }

/* ===============================
   DELETE BACKUP FUNCTION - FIXED
=============================== */
window.deleteBackup = async function(filename) {
    // ✅ CHECK PERMISSION
    if (!permissions.can_delete_backup) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak',
                text: 'Anda tidak memiliki izin untuk menghapus backup',
                confirmButtonColor: '#ef4444'
            });
        }
        return;
    }
    
    console.log('🗑️ Deleting backup:', filename);
    
    const result = await Swal.fire({
        title: 'Hapus Backup?',
        html: `
            <div class="text-left">
                <p class="text-gray-600 mb-3">Apakah Anda yakin ingin menghapus file backup:</p>
                <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-200 rounded-xl p-4 my-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="bi bi-file-earmark-zip-fill text-red-600 text-xl"></i>
                        <p class="font-bold text-red-700 text-sm break-all">${filename}</p>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-blue-700 flex items-start gap-2">
                        <i class="bi bi-exclamation-circle text-blue-500 text-lg mt-0.5"></i>
                        <span>File backup yang sudah dihapus tidak dapat dikembalikan.</span>
                    </p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-trash-fill me-2"></i>Ya, Hapus!',
        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Batal',
        reverseButtons: true,
        width: '550px',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg',
            cancelButton: 'rounded-xl px-6 py-3 font-bold'
        }
    });
    
    if (!result.isConfirmed) return;
    
    // Show loading
    Swal.fire({
        title: 'Menghapus...',
        html: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Generate delete URL
    let deleteUrl;
    try {
        deleteUrl = "{{ route('admin2.pengaturan.backups.delete', ['filename' => '__FILENAME__']) }}".replace('__FILENAME__', filename);
    } catch (e) {
        const baseUrl = window.location.origin;
        deleteUrl = `${baseUrl}/admin2/pengaturan/backups/delete/${filename}`;
    }
    
    console.log('🗑️ Delete URL:', deleteUrl);
    
    // ✅ MENGGUNAKAN GLOBAL ERROR HANDLER
    const data = await fetchWithErrorHandling(deleteUrl, {
        method: "DELETE",
        headers: { 
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json",
            "Content-Type": "application/json"
        }
    });
    
    if (!data) return; // Error sudah ditangani oleh global handler
    
    if (data.status) {
        console.log('✅ Delete successful, refreshing list...');
        await loadBackupList();
        
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Backup berhasil dihapus',
            confirmButtonColor: '#22c55e',
            timer: 2000,
            timerProgressBar: true
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: data.message || 'Gagal menghapus backup',
            confirmButtonColor: '#ef4444'
        });
    }
};

    /* ===============================
       BACKUP & RESTORE - WITH PERMISSIONS CHECK
    =============================== */
    const btnBackup = document.getElementById('btnBackup');
    if (btnBackup) {
        btnBackup.addEventListener('click', async () => {
            // ✅ CHECK PERMISSION
            if (!permissions.can_backup) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: 'Anda tidak memiliki izin untuk membuat backup',
                        confirmButtonColor: '#ef4444'
                    });
                } else {
                    alert('⛔ Akses Ditolak\n\nAnda tidak memiliki izin untuk membuat backup');
                }
                return;
            }
            
            loadingMessage.textContent = 'Membuat backup...';
            popupLoading.classList.remove('hidden');
            
            // ✅ MENGGUNAKAN GLOBAL ERROR HANDLER
            const data = await fetchWithErrorHandling("{{ route('admin2.pengaturan.backup') }}", {
                method: "POST",
                headers: { 
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                }
            });
            
            popupLoading.classList.add('hidden');
            
            if (!data) return; // Error sudah ditangani oleh global handler
            
            if (data.status) {
                setTimeout(async () => {
                    await loadBackupList();
                    document.getElementById('backupTitle').textContent = 'Backup Berhasil!';
                    document.getElementById('backupMessage').textContent = data.message || 'Data berhasil di-backup!';
                    document.getElementById('popupBackup').classList.remove('hidden');
                }, 2000);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Backup Gagal!',
                        text: data.message || 'Gagal membuat backup',
                        confirmButtonColor: '#ef4444'
                    });
                } else {
                    alert('Backup gagal: ' + data.message);
                }
            }
        });
    }

    const btnRestore = document.getElementById('btnRestore');
    if (btnRestore) {
        btnRestore.addEventListener('click', async () => {
            // ✅ CHECK PERMISSION
            if (!permissions.can_restore) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: 'Anda tidak memiliki izin untuk restore database',
                        confirmButtonColor: '#ef4444'
                    });
                } else {
                    alert('⛔ Akses Ditolak\n\nAnda tidak memiliki izin untuk restore database');
                }
                return;
            }
            
            // ✅ CONFIRMATION
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Konfirmasi Restore',
                    html: 'Restore database ke backup terakhir?<br><br><strong>⚠️ Data saat ini akan diganti!</strong>',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Restore',
                    cancelButtonText: 'Batal'
                });
                
                if (!result.isConfirmed) return;
            } else {
                if (!confirm('Restore database ke backup terakhir?\n\n⚠️ Data saat ini akan diganti!')) return;
            }
            
            loadingMessage.textContent = 'Melakukan restore...';
            popupLoading.classList.remove('hidden');
            
            // ✅ MENGGUNAKAN GLOBAL ERROR HANDLER
            const data = await fetchWithErrorHandling("{{ route('admin2.pengaturan.restore') }}", {
                method: "POST",
                headers: { 
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                }
            });
            
            popupLoading.classList.add('hidden');
            
            if (!data) return; // Error sudah ditangani oleh global handler
            
            if (data.status) {
                document.getElementById('backupTitle').textContent = 'Restore Berhasil!';
                document.getElementById('backupMessage').textContent = data.message || 'Database berhasil dipulihkan!';
                document.getElementById('popupBackup').classList.remove('hidden');
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Restore Gagal!',
                        text: data.message || 'Gagal restore database',
                        confirmButtonColor: '#ef4444'
                    });
                } else {
                    alert('Restore gagal: ' + data.message);
                }
            }
        });
    }

    document.getElementById('btnCloseBackup').addEventListener('click', () => {
        document.getElementById('popupBackup').classList.add('hidden');
    });
    
    // ✅ LOAD BACKUP LIST SAAT HALAMAN DIBUKA
    loadBackupList();
    
    // ✅ REFRESH BUTTON
    document.getElementById('btnRefreshBackups')?.addEventListener('click', () => {
        console.log('🔄 Manual refresh...');
        loadBackupList();
    });

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
            loadingMessage.textContent = 'Menyimpan setting omzet...';
            popupLoading.classList.remove('hidden');
            modalOmzet.classList.add('hidden');

            // ✅ MENGGUNAKAN GLOBAL ERROR HANDLER
            const data = await fetchWithErrorHandling("{{ route('admin2.pengaturan.omzet') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ hitung_omzet_dari: selectedOmzet })
            });
            
            popupLoading.classList.add('hidden');
            
            if (!data) return; // Error sudah ditangani oleh global handler
            
            if (data.status) {
                successMessage.textContent = 'Pengaturan omzet disimpan!';
                popupSuccess.classList.remove('hidden');
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Gagal menyimpan setting omzet',
                        confirmButtonColor: '#ef4444'
                    });
                } else {
                    alert(data.message || 'Gagal menyimpan setting omzet');
                }
            }
        });

    /* ===============================
       LOGOUT HANDLER
    =============================== */
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', async () => {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Logout',
                    text: 'Apakah Anda yakin ingin keluar?',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal'
                });
                
                if (result.isConfirmed) {
                    window.location.href = "{{ route('admin2.logout') }}";
                }
            } else {
                if (confirm('Apakah Anda yakin ingin keluar?')) {
                    window.location.href = "{{ route('admin2.logout') }}";
                }
            }
        });
    }

});
</script>
@endsection
