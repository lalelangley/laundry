@extends('layouts.master')
@section('title', 'Kelola Pelanggan')
@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
    <a href="{{ route('admin.dashboard') }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Kelola Pelanggan</span>
</div>

{{-- SEARCH + SORT --}}
<div class="px-5 mt-5">
    <div class="bg-white rounded-2xl px-4 py-3 flex items-center shadow">
        <i class="bi bi-search text-yellow-500 text-xl mr-3"></i>
        <input type="text" placeholder="Cari" class="w-full focus:outline-none text-lg" name="search">
        <button class="ml-3 text-gray-500 text-sm flex flex-col items-center">
            <i class="bi bi-arrow-down-up text-xl"></i>
            <span>Sort</span>
        </button>
    </div>
</div>

{{-- LIST PELANGGAN --}}
<div class="px-5 mt-5 space-y-4 mb-24">
    @foreach ($pelanggan as $item)
    <div class="bg-white rounded-2xl px-4 py-4 flex gap-3 items-center shadow justify-between relative group cursor-pointer"
         onclick="window.location='{{ route('pelanggan.edit', $item->id_pelanggan) }}'">
        <div class="flex gap-3 items-center">
            {{-- Foto --}}
            @if ($item->gambar)
            <img src="{{ asset('images/' . $item->gambar) }}"
                 alt="{{ $item->nama_pelanggan }}"
                 class="w-16 h-16 rounded-xl object-cover"
                 onerror="this.onerror=null; this.src='{{ asset('images/default-user.png') }}';">
            @else
            <div class="w-16 h-16 bg-gray-200 rounded-xl flex items-center justify-center">
                <i class="bi bi-camera text-3xl text-gray-400"></i>
            </div>
            @endif

            {{-- Detail --}}
            <div>
                <div class="text-xl font-semibold">{{ $item->nama_pelanggan }}</div>
                <div class="flex items-center text-gray-600 text-base">
                    <i class="bi bi-envelope me-2"></i>{{ $item->email }}
                </div>
                <div class="flex items-center text-gray-600 text-base">
                    <i class="bi bi-telephone me-2"></i>{{ $item->no_hp }}
                </div>
            </div>
        </div>

        {{-- Tombol hapus (muncul saat hover) --}}
        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">
            <form action="{{ route('pelanggan.destroy', $item->id_pelanggan) }}" method="POST" class="inline delete-form">
                @csrf
                @method('DELETE')
                <button type="button"
                        onclick="event.stopPropagation(); confirmDelete(this)"
                        data-nama="{{ $item->nama_pelanggan }}"
                        data-email="{{ $item->email }}"
                        class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold shadow-md transition-all hover:scale-110">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

{{-- BUTTON TAMBAH --}}
<div class="fixed bottom-5 left-0 right-0 px-6">
    <a href="{{ route('pelanggan.create') }}"
       class="bg-yellow-400 w-full block text-center py-4 rounded-full text-xl font-semibold shadow-lg hover:bg-yellow-500 transition-all">
        Tambah Pelanggan
    </a>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JAVASCRIPT DELETE -->
<script>
function confirmDelete(button) {
    const namaPelanggan = button.getAttribute('data-nama');
    const emailPelanggan = button.getAttribute('data-email');
    const form = button.closest('form');
    
    Swal.fire({
        title: 'Hapus Pelanggan?',
        html: `
            <div class="text-left">
                <p class="mb-2">Apakah Anda yakin ingin menghapus pelanggan ini?</p>
                <div class="bg-gray-100 p-3 rounded-lg mt-3">
                    <p class="font-semibold text-lg text-yellow-600">${namaPelanggan}</p>
                    <p class="text-sm text-gray-600"><i class="bi bi-envelope me-1"></i>${emailPelanggan}</p>
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
        backdrop: true,
        customClass: {
            confirmButton: 'px-6 py-3 rounded-xl font-semibold shadow-lg',
            cancelButton: 'px-6 py-3 rounded-xl font-semibold shadow-lg',
            popup: 'rounded-2xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus Pelanggan...',
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
}
</script>

@endsection