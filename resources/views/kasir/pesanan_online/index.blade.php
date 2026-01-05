@extends('layouts.master')

@section('content')

<div class="min-h-screen bg-gray-50">
    
    {{-- ========================================
         HEADER SECTION
    ======================================== --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-md sticky top-0 z-10">
        <a href="{{ route('kasir.dashboard') }}" 
           class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Pesanan Online</span>
    </div>

    <div class="px-8 py-6">
        
        {{-- ========================================
             SEARCH BAR
        ======================================== --}}
        <div class="mb-6">
            <div class="relative">
                <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                <input type="text" 
                       id="searchInput"
                       class="w-full pl-12 pr-4 py-4 rounded-xl bg-white shadow-sm border border-gray-200 outline-none focus:ring-2 focus:ring-yellow-300 focus:border-yellow-400 transition-all text-base"
                       placeholder="Cari pesanan berdasarkan nama, no pesanan...">
            </div>
        </div>

        {{-- ========================================
             STATUS TABS
        ======================================== --}}
        <div class="mb-6 bg-white p-2 rounded-2xl shadow-sm border border-gray-200 overflow-x-auto">
            <div class="flex gap-2 flex-wrap justify-center">
                @php
                    $tabs = [
                        'menunggu_konfirmasi' => [
                            'label' => 'Menunggu', 
                            'icon' => 'hourglass-split'
                        ],
                        'proses' => [
                            'label' => 'Proses', 
                            'icon' => 'arrow-repeat'
                        ],
                        'siap_di_ambil' => [
                            'label' => 'Siap Diambil', 
                            'icon' => 'box-seam'
                        ],
                        'siap_di_antar' => [
                            'label' => 'Siap Diantar', 
                            'icon' => 'truck'
                        ],
                        'selesai' => [
                            'label' => 'Selesai', 
                            'icon' => 'check-all'
                        ],
                        'ditolak' => [
                            'label' => 'Ditolak', 
                            'icon' => 'x-circle'
                        ]
                    ];
                @endphp
                
                @foreach ($tabs as $key => $data)
                    <a href="{{ route('kasir.pesanan.online.index', ['tab' => $key]) }}"
                       class="flex-1 min-w-[120px] flex items-center justify-center gap-2 px-4 py-3 rounded-xl whitespace-nowrap font-semibold transition-all
                              {{ $tab == $key 
                                  ? 'bg-yellow-400 text-gray-900 shadow-sm' 
                                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}">
                        <i class="bi bi-{{ $data['icon'] }} text-lg"></i>
                        <span class="hidden md:inline">{{ $data['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ========================================
             PESANAN LIST
        ======================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5" id="pesananList">
            
            @forelse ($pesanan as $p)
                <div class="group relative" 
                     data-nama="{{ strtolower($p->nama_pelanggan ?? '') }}" 
                     data-id="{{ $p->id_transaksi }}">
                    <a href="{{ route('kasir.pesanan.online.detail', $p->id_transaksi) }}" 
                       class="block">
                        <div class="relative bg-white shadow-sm rounded-2xl p-6 
                                    hover:shadow-md transition-all duration-300 border border-gray-200
                                    @if($p->status_transaksi == 'menunggu_konfirmasi') hover:border-orange-300
                                    @elseif($p->status_transaksi == 'dikonfirmasi') hover:border-blue-300
                                    @elseif($p->status_transaksi == 'proses') hover:border-purple-300
                                    @elseif($p->status_transaksi == 'siap_di_ambil') hover:border-teal-300
                                    @elseif($p->status_transaksi == 'siap_di_antar') hover:border-gray-300
                                    @elseif($p->status_transaksi == 'selesai') hover:border-green-300
                                    @else hover:border-red-300
                                    @endif">

                            {{-- Card Header --}}
                            <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100">
                                <div class="flex-1">
                                    <h2 class="font-bold text-xl text-gray-800 mb-1">
                                        {{ $p->nama_pelanggan ?? 'Guest' }}
                                    </h2>
                                    <p class="text-gray-500 text-sm flex items-center gap-1">
                                        <i class="bi bi-cart-check"></i>
                                        ORDER/{{ $p->id_transaksi }}
                                    </p>
                                </div>

                                {{-- Delete Button (hover to show) --}}
                                <button type="button"
                                        onclick="event.preventDefault(); event.stopPropagation(); confirmDelete({{ $p->id_transaksi }}, '{{ $p->nama_pelanggan ?? 'Guest' }}')"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-red-50 text-red-600 p-2 rounded-lg hover:bg-red-500 hover:text-white hover:scale-110 transition-all">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>

                            {{-- Price Display --}}
                            <div class="bg-yellow-50 border border-yellow-200 text-gray-900 
                                        px-4 py-3 rounded-xl mb-4">
                                <p class="text-sm font-semibold mb-1 text-gray-600">Total Harga</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                </p>
                            </div>

                            {{-- Order Details --}}
                            <div class="space-y-3">
                                
                                {{-- Tanggal Pesan --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-calendar-date text-blue-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Tanggal Pesan</p>
                                        <p class="font-semibold text-gray-800">{{ $p->tgl_transaksi }}</p>
                                    </div>
                                </div>

                                {{-- No. Telepon --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-telephone text-green-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">No. Telepon</p>
                                        <p class="font-semibold text-gray-800">{{ $p->no_hp ?? '-' }}</p>
                                    </div>
                                </div>

                                {{-- Total Item --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-basket text-purple-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Total Item</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ $p->detail_transaksi->sum('qty') }} item
                                        </p>
                                    </div>
                                </div>
                                
                            </div>

                            {{-- Status Badges --}}
                            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2">
                                
                                {{-- Transaction Status Badge --}}
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg 
                                             text-xs font-semibold
                                             @if ($p->status_transaksi == 'menunggu_konfirmasi') bg-orange-100 text-orange-700
                                             @elseif ($p->status_transaksi == 'dikonfirmasi') bg-blue-100 text-blue-700
                                             @elseif ($p->status_transaksi == 'proses') bg-purple-100 text-purple-700
                                             @elseif ($p->status_transaksi == 'siap_di_ambil') bg-teal-100 text-teal-700
                                             @elseif ($p->status_transaksi == 'siap_di_antar') bg-gray-100 text-gray-700
                                             @elseif ($p->status_transaksi == 'selesai') bg-green-100 text-green-700
                                             @else bg-red-100 text-red-700
                                             @endif">
                                    <i class="bi bi-clipboard-check"></i>
                                    {{ ucfirst(str_replace('_', ' ', $p->status_transaksi)) }}
                                </span>

                                {{-- Payment Status Badge --}}
                                @if ($p->status_bayar == 'belum_bayar' || $p->status_bayar == 'belum_lunas')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                 bg-red-100 text-red-700 rounded-lg text-xs font-semibold">
                                        <i class="bi bi-x-circle"></i>
                                        Belum Bayar
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                 bg-green-100 text-green-700 rounded-lg text-xs font-semibold">
                                        <i class="bi bi-check-circle"></i>
                                        Lunas
                                    </span>
                                @endif
                                
                            </div>

                        </div>
                    </a>
                </div>
            
            @empty
                {{-- Empty State --}}
                <div class="col-span-full flex flex-col items-center justify-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 border border-gray-200">
                        <i class="bi bi-inbox text-5xl text-gray-400"></i>
                    </div>
                    <p class="text-xl text-gray-600 font-semibold">Tidak ada pesanan online</p>
                    <p class="text-gray-400 text-sm mt-2">Pesanan online akan muncul di sini</p>
                </div>
            @endforelse
            
        </div>
    </div>
</div>

{{-- Hidden Forms for Delete --}}
@foreach($pesanan as $p)
<form id="deleteForm{{ $p->id_transaksi }}" 
      action="{{ route('kasir.pesanan.online.destroy', $p->id_transaksi) }}" 
      method="POST" 
      style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach

{{-- ========================================
     JAVASCRIPT - SWEETALERT2 & SEARCH
======================================== --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Search Functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let items = document.querySelectorAll('#pesananList > div');
    
    items.forEach(function(item) {
        let nama = item.getAttribute('data-nama');
        let id = item.getAttribute('data-id');
        
        if (nama.includes(filter) || id.includes(filter)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

// Confirm Delete
function confirmDelete(pesananId, namaPelanggan) {
    Swal.fire({
        title: 'Hapus Pesanan?',
        html: `<div class="text-gray-600">
                    <p class="mb-2">Anda akan menghapus pesanan:</p>
                    <div class="bg-red-50 border-2 border-red-200 rounded-xl p-3 my-3">
                        <p class="font-bold text-red-700 text-lg">ORDER/${pesananId}</p>
                        <p class="text-gray-700 mt-1">${namaPelanggan}</p>
                    </div>
                    <p class="text-sm text-gray-500">
                        <i class="bi bi-info-circle text-blue-500"></i>
                        Data yang sudah dihapus tidak dapat dikembalikan.
                    </p>
                </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-trash-fill"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg',
            cancelButton: 'rounded-xl px-6 py-3 font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus Pesanan...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form
            document.getElementById('deleteForm' + pesananId).submit();
        }
    });
}
</script>

@endsection