@extends('layouts.master')
@section('title', 'Metode Pembayaran')
@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
    <a href="{{ route('admin2.pengaturan.index') }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Metode Pembayaran</span>
</div>

<div class="p-4 space-y-4 pb-24">

    {{-- BUTTON TAMBAH - Conditional --}}
    @if($permissions['can_add'] ?? true)
    <button id="btnTambahMetode" class="w-full py-4 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-white font-bold rounded-2xl text-lg shadow-lg active:scale-95 transition flex items-center justify-center gap-2">
        <i class="bi bi-plus-circle-fill text-xl"></i>
        Tambah Metode Pembayaran
    </button>
    @else
    <div class="w-full py-4 bg-gray-300 text-gray-500 font-bold rounded-2xl text-lg shadow-lg opacity-50 cursor-not-allowed flex items-center justify-center gap-2">
        <i class="bi bi-plus-circle-fill text-xl"></i>
        Tambah Metode Pembayaran
    </div>
    @endif

    {{-- LIST METODE PEMBAYARAN --}}
    <div class="space-y-3">
        @forelse($metode as $item)
        <div class="bg-white rounded-2xl p-4 shadow-lg flex items-center justify-between hover:shadow-xl transition">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="bi bi-credit-card-fill text-white text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-lg">{{ $item->nama_metode_bayar }}</p>
                    <p class="text-xs text-gray-500">ID: {{ $item->id_metode_bayar }}</p>
                </div>
            </div>
            
            {{-- DELETE BUTTON - Conditional --}}
            @if($permissions['can_delete'] ?? false)
            <form action="{{ route('admin2.pengaturan.metode.delete', $item->id_metode_bayar) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button"
                        onclick="confirmDeleteMetode(this, '{{ $item->nama_metode_bayar }}')"
                        data-nama="{{ $item->nama_metode_bayar }}"
                        data-id="{{ $item->id_metode_bayar }}"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl transition active:scale-95 hover:scale-110">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </form>
            @else
            <button onclick="showNoAccessPopup()" 
                    class="px-4 py-2 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed opacity-50">
                <i class="bi bi-trash-fill"></i>
            </button>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl p-8 shadow-lg text-center">
            <i class="bi bi-inbox text-6xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 font-semibold">Belum ada metode pembayaran</p>
        </div>
        @endforelse
    </div>

</div>

{{-- MODAL TAMBAH METODE --}}
<div id="modalTambah" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate__animated animate__fadeInUp">
        <div class="bg-yellow-400 px-5 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Tambah Metode Pembayaran</h2>
            <button id="closeModal" class="text-white text-2xl font-bold hover:scale-110 transition">✕</button>
        </div>
        
        <div class="p-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Metode</label>
            <input type="text" id="inputNamaMetode" 
                   placeholder="Contoh: Transfer Bank, QRIS, Gopay" 
                   class="w-full p-4 rounded-2xl border-2 border-gray-200 bg-gray-50 text-gray-800 font-medium focus:border-yellow-400 focus:outline-none transition">
        </div>
        
        <div class="px-6 pb-6">
            <button id="btnSimpan" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-2xl transition active:scale-95">
                Simpan
            </button>
        </div>
    </div>
</div>

{{-- POPUP NO ACCESS --}}
<div id="popupNoAccess" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center animate__animated animate__shakeX">
        <div class="w-24 h-24 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="bi bi-shield-x text-white text-5xl"></i>
        </div>
        <h1 class="text-2xl font-bold mb-2 text-red-600">Akses Ditolak</h1>
        <p class="text-gray-600 mb-6">Anda tidak memiliki izin untuk melakukan aksi ini</p>
        <button id="btnCloseNoAccess" class="w-full py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-2xl transition">
            Tutup
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    // ✅ Show success/error message if exists
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#22c55e',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#ef4444'
        });
    @endif

    // ✅ PASS PERMISSIONS FROM PHP TO JS
    const permissions = @json($permissions ?? []);
    console.log('🔐 Permissions:', permissions);

    /* ===============================
       POPUPS
    =============================== */
    const modalTambah = document.getElementById('modalTambah');
    const popupNoAccess = document.getElementById('popupNoAccess');

    // Show No Access Popup
    window.showNoAccessPopup = function() {
        popupNoAccess.classList.remove('hidden');
    };

    document.getElementById('btnCloseNoAccess').addEventListener('click', () => {
        popupNoAccess.classList.add('hidden');
    });

    /* ===============================
       TAMBAH METODE
    =============================== */
    const btnTambahMetode = document.getElementById('btnTambahMetode');
    if (btnTambahMetode) {
        btnTambahMetode.addEventListener('click', () => {
            // ✅ CHECK PERMISSION
            if (!permissions.can_add) {
                showNoAccessPopup();
                return;
            }
            modalTambah.classList.remove('hidden');
        });
    }

    document.getElementById('closeModal').addEventListener('click', () => {
        modalTambah.classList.add('hidden');
        document.getElementById('inputNamaMetode').value = '';
    });

    document.getElementById('btnSimpan').addEventListener('click', async () => {
        const namaMetode = document.getElementById('inputNamaMetode').value.trim();

        if (!namaMetode) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Nama metode pembayaran wajib diisi',
                confirmButtonColor: '#EAB308'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            html: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        modalTambah.classList.add('hidden');

        try {
            const res = await fetch("{{ route('admin2.pengaturan.metode.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ nama_metode_bayar: namaMetode })
            });

            const data = await res.json();

            if (data.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Metode pembayaran berhasil ditambahkan',
                    confirmButtonColor: '#22c55e'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menambahkan metode pembayaran',
                    confirmButtonColor: '#ef4444'
                });
            }

        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan: ' + e.message,
                confirmButtonColor: '#ef4444'
            });
        }
    });

    /* ===============================
       HAPUS METODE - Using Reusable Function
    =============================== */
    window.confirmDeleteMetode = function(button, namaMetode) {
        // ✅ CHECK PERMISSION
        if (!permissions.can_delete) {
            showNoAccessPopup();
            return;
        }

        const form = button.closest('form');
        const idMetode = button.getAttribute('data-id');

        Swal.fire({
            title: 'Hapus Metode Pembayaran?',
            html: `
                <div class="text-left">
                    <p class="text-gray-600 mb-3">Apakah Anda yakin ingin menghapus metode pembayaran:</p>
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-200 rounded-xl p-4 my-4 shadow-sm">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-credit-card-fill text-red-600 text-xl"></i>
                            <p class="font-bold text-red-700 text-lg">${namaMetode}</p>
                        </div>
                        <p class="text-sm text-gray-600 ml-7">ID: ${idMetode}</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-700 flex items-start gap-2">
                            <i class="bi bi-exclamation-circle text-blue-500 text-lg mt-0.5"></i>
                            <span>Data yang sudah dihapus tidak dapat dikembalikan.</span>
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
        }).then((result) => {
            if (result.isConfirmed) {
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

                // Submit form
                form.submit();
            }
        });
    };
});
</script>
@endsection