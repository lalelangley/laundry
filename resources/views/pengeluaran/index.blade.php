@extends('layouts.master')

@section('title', 'pengeluaran')

@section('content')

<!-- HEADER -->
<div class="w-full bg-yellow-400 p-4 flex items-center justify-between rounded-b-3xl shadow-md">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold hover:opacity-70 transition">
            ←
        </a>
        <h1 class="text-xl font-bold tracking-wide">List Pengeluaran</h1>
    </div>

    <div class="text-sm font-semibold text-black/70 mr-2 select-none">
        Sort
    </div>
</div>

{{-- WRAPPER --}}
<div class="p-5 pb-[150px] bg-gray-100 min-h-screen">

    {{-- SEARCH --}}
    <div class="bg-white rounded-3xl p-4 shadow-sm flex items-center gap-3 mb-6">
        <i class="bi bi-search text-yellow-500 text-xl"></i>
        <input type="text"
               class="w-full bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400"
               placeholder="Cari pengeluaran...">
    </div>

    {{-- LIST / EMPTY STATE --}}
    @if ($pengeluaran->isEmpty())

        <div class="flex flex-col items-center justify-center mt-20 opacity-80">
            <i class="bi bi-search text-[90px] text-yellow-400 drop-shadow"></i>
            <p class="text-lg font-semibold text-gray-600 mt-3">Data Tidak Ditemukan</p>
        </div>

    @else

        <div class="space-y-5">

           @foreach ($pengeluaran as $item)
                <div class="bg-white p-6 rounded-3xl shadow-md relative overflow-hidden border border-gray-100 hover:shadow-lg transition">

                    {{-- Garis Kuning Kiri --}}
                    <div class="absolute left-0 top-0 h-full w-2 bg-yellow-400 rounded-l-3xl"></div>

                    {{-- STOP CLICK BUBBLE ON DROPDOWN --}}
                    <div class="absolute right-4 top-4 z-50 dropdown-area">
                        <button class="dropdown-btn text-gray-700 text-2xl">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>

                        <ul class="dropdown-menu hidden absolute right-0 top-10 w-40 bg-yellow-400 rounded-2xl shadow-xl overflow-hidden py-1 z-50">
                            <li>
                                <a href="{{ route('pengeluaran.edit', $item->id_pengeluaran) }}"
                                   class="flex items-center gap-2 px-4 py-3 text-black text-sm font-medium hover:bg-yellow-300">
                                    <i class="bi bi-pencil text-lg"></i> Edit
                                </a>
                            </li>

                            <li>
                                <button type="button"
                                        onclick="confirmDelete({{ $item->id_pengeluaran }}, '{{ $item->nama_pengeluaran }}', '{{ number_format($item->nominal, 0, ',', '.') }}', '{{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('d/m/Y') : '-' }}')"
                                        class="w-full flex items-center gap-2 px-4 py-3 text-red-600 text-sm font-medium hover:bg-yellow-300">
                                    <i class="bi bi-trash text-lg"></i> Hapus
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="flex justify-between items-start">

                        {{-- TEXT --}}
                        <div class="ml-4 w-full">

                            {{-- JUDUL --}}
                            <p class="font-extrabold text-lg uppercase tracking-wide text-gray-800 leading-tight">
                                {{ $item->nama_pengeluaran }}
                            </p>

                            {{-- TGL --}}
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('l, d/m/Y') : '-' }}
                            </p>

                            {{-- CATATAN --}}
                            <div class="bg-gray-100 p-3 rounded-xl mt-3 border border-gray-200">
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    {{ $item->catatan ?? '-' }}
                                </p>
                            </div>

                            {{-- NOMINAL --}}
                            <p class="font-bold mt-4 text-lg text-gray-800">
                                Rp{{ number_format($item->nominal, 0, ',', '.') }}
                            </p>

                        </div>

                    </div>

                </div>

                {{-- Hidden Form for Delete --}}
                <form id="deleteForm{{ $item->id_pengeluaran }}" 
                      action="{{ route('pengeluaran.destroy', $item->id_pengeluaran) }}" 
                      method="POST" 
                      style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </div>

    @endif

</div>

{{-- BUTTON TAMBAH --}}
<div class="fixed bottom-0 left-0 w-full bg-gray-100 px-6 py-5">
    <a href="{{ route('pengeluaran.create') }}"
       class="w-full block text-center bg-yellow-400 text-black py-4 rounded-3xl text-lg font-bold shadow hover:bg-yellow-500 transition">
        Tambah Pengeluaran
    </a>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Dropdown Toggle
document.querySelectorAll('.dropdown-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation(); // stop card click
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== this.nextElementSibling) {
                menu.classList.add('hidden');
            }
        });
        
        // Toggle current dropdown
        this.nextElementSibling.classList.toggle('hidden');
    });
});

document.addEventListener('click', function() {
    document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
});

// Confirm Delete Function
function confirmDelete(id, nama, nominal, tanggal) {
    // Close dropdown first
    document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
    
    Swal.fire({
        title: 'Hapus Pengeluaran?',
        html: `
            <div class="text-left">
                <p class="text-gray-600 mb-3">Anda akan menghapus pengeluaran berikut:</p>
                <div class="bg-gradient-to-br from-red-50 to-orange-50 border-2 border-red-200 rounded-2xl p-4 my-4 shadow-sm">
                    <div class="space-y-2">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-tag-fill text-red-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Nama Pengeluaran</p>
                                <p class="font-bold text-gray-800 text-base">${nama}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-2">
                            <i class="bi bi-calendar3 text-blue-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Tanggal</p>
                                <p class="font-semibold text-gray-700">${tanggal}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-2">
                            <i class="bi bi-cash-coin text-yellow-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Nominal</p>
                                <p class="font-bold text-yellow-700 text-lg">Rp ${nominal}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 mt-3">
                    <p class="text-sm text-blue-700 flex items-start gap-2">
                        <i class="bi bi-exclamation-circle text-blue-500 text-lg mt-0.5"></i>
                        <span>Data pengeluaran yang sudah dihapus tidak dapat dikembalikan.</span>
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
            confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:shadow-xl',
            cancelButton: 'rounded-xl px-6 py-3 font-bold'
        },
        backdrop: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus Pengeluaran...',
                html: `
                    <div class="flex flex-col items-center gap-3">
                        <i class="bi bi-hourglass-split text-5xl text-yellow-500 animate-pulse"></i>
                        <p class="text-gray-600">Mohon tunggu sebentar</p>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form
            document.getElementById('deleteForm' + id).submit();
        }
    });
}
</script>

@endsection