@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50">
    <style>
        .delete-btn {
            pointer-events: auto !important;
            z-index: 9999 !important;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s ease;
        }
        
        .card-wrapper:hover .delete-btn {
            opacity: 1;
            transform: scale(1);
        }
        
        .delete-btn:hover {
            transform: scale(1.15) !important;
            box-shadow: 0 10px 25px -5px rgba(220, 38, 38, 0.5), 
                        0 8px 10px -6px rgba(220, 38, 38, 0.4) !important;
        }
        
        .delete-btn:active {
            transform: scale(0.95) !important;
        }
    </style>
    
    {{-- ========================================
         HEADER SECTION
    ======================================== --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-md sticky top-0 z-10">
        <a href="{{ route('admin2.dashboard') }}" 
           class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Riwayat Transaksi</span>
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
                       placeholder="Cari transaksi berdasarkan nama, no nota...">
            </div>
        </div>

        {{-- ========================================
             STATUS TABS
        ======================================== --}}
        <div class="mb-6 bg-white p-2 rounded-2xl shadow-sm border border-gray-200 overflow-x-auto">
            <div class="flex gap-2 justify-between">
                @php
                    $tabs = [
                        'antrian' => [
                            'label' => 'Antrian', 
                            'icon' => 'clock-history'
                        ],
                        'proses' => [
                            'label' => 'Proses', 
                            'icon' => 'arrow-repeat'
                        ],
                        'siap_di_ambil' => [
                            'label' => 'Siap Diambil', 
                            'icon' => 'check-circle'
                        ],
                        'selesai' => [
                            'label' => 'Selesai', 
                            'icon' => 'check-all'
                        ],
                        'batal' => [
                            'label' => 'Batal', 
                            'icon' => 'x-circle'
                        ]
                    ];
                @endphp

                @foreach ($tabs as $key => $data)
                    <a href="{{ route('admin2.riwayat.index', ['tab' => $key]) }}"
                       class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl whitespace-nowrap font-semibold transition-all
                              {{ $tab == $key 
                                  ? 'bg-yellow-400 text-gray-900 shadow-sm' 
                                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}">
                        <i class="bi bi-{{ $data['icon'] }} text-lg"></i>
                        <span class="hidden sm:inline">{{ $data['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ========================================
             TRANSACTION LIST
        ======================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5" id="riwayatList">
            @forelse ($riwayat as $t)
                {{-- ✅ CARD WRAPPER WITH HOVER EFFECT --}}
                <div data-nama="{{ strtolower($t->nama_pelanggan ?? '') }}" 
                     data-id="{{ $t->id_transaksi }}"
                     class="relative group card-wrapper">

                    {{-- DELETE BUTTON - MUNCUL SAAT HOVER --}}
                    <div class="absolute top-4 right-4 z-50" style="pointer-events: auto;">
                        <button type="button"
                                data-id="{{ $t->id_transaksi }}"
                                data-nama="{{ addslashes($t->nama_pelanggan ?? 'Guest') }}"
                                data-total="{{ number_format($t->total_harga, 0, ',', '.') }}"
                                data-tanggal="{{ $t->tgl_transaksi }}"
                                class="delete-btn bg-red-600 text-white p-3 rounded-xl 
                                       hover:bg-red-700 shadow-lg border-2 border-white
                                       cursor-pointer">
                            <i class="bi bi-trash-fill text-lg"></i>
                        </button>
                    </div>

                    {{-- CARD CONTENT --}}
                    <a href="{{ route('admin2.riwayat.detail', $t->id_transaksi) }}" class="block">
                        <div class="relative bg-white shadow-sm rounded-2xl p-6 
                                    hover:shadow-md transition-all duration-300 border border-gray-200
                                    @if($t->status_transaksi == 'antrian') hover:border-slate-300
                                    @elseif($t->status_transaksi == 'proses') hover:border-blue-300
                                    @elseif($t->status_transaksi == 'siap_di_ambil') hover:border-teal-300
                                    @elseif($t->status_transaksi == 'selesai') hover:border-green-300
                                    @else hover:border-red-300
                                    @endif">
                            
                            {{-- Card Header --}}
                            <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100">
                                <div class="flex-1 pr-12">
                                    <h2 class="font-bold text-xl text-gray-800 mb-1">
                                        {{ $t->nama_pelanggan }}
                                    </h2>
                                    <p class="text-gray-500 text-sm flex items-center gap-1">
                                        <i class="bi bi-receipt-cutoff"></i>
                                        TRX/{{ $t->id_transaksi }}
                                    </p>
                                </div>
                            </div>

                            {{-- Price Display --}}
                            <div class="bg-yellow-50 border border-yellow-200 text-gray-900 
                                        px-4 py-3 rounded-xl mb-4">
                                <p class="text-sm font-semibold mb-1 text-gray-600">Total Harga</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                                </p>
                            </div>

                            {{-- Transaction Details --}}
                            <div class="space-y-3">
                                {{-- Tanggal Masuk --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-calendar-date text-blue-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Tanggal Masuk</p>
                                        <p class="font-semibold text-gray-800">{{ $t->tgl_transaksi }}</p>
                                    </div>
                                </div>

                                {{-- Estimasi Selesai --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-calendar-check text-green-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Estimasi Selesai</p>
                                        <p class="font-semibold text-gray-800">{{ $t->tgl_estimasi }}</p>
                                    </div>
                                </div>

                                {{-- Diskon (if exists) --}}
                                @if($t->diskon > 0)
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-percent text-red-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Diskon</p>
                                        <p class="font-semibold text-red-600">
                                            - Rp {{ number_format($t->diskon, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Status Badges --}}
                            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2">
                                {{-- Transaction Status Badge --}}
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg 
                                             text-xs font-semibold
                                             @if ($t->status_transaksi == 'antrian') bg-slate-100 text-slate-700
                                             @elseif ($t->status_transaksi == 'proses') bg-blue-100 text-blue-700
                                             @elseif ($t->status_transaksi == 'pick_up') bg-purple-100 text-purple-700
                                             @elseif ($t->status_transaksi == 'siap_di_ambil') bg-teal-100 text-teal-700
                                             @elseif ($t->status_transaksi == 'siap_di_antar') bg-indigo-100 text-indigo-700
                                             @elseif ($t->status_transaksi == 'selesai') bg-green-100 text-green-700
                                             @else bg-red-100 text-red-700
                                             @endif">
                                    <i class="bi bi-clipboard-check"></i>
                                    {{ ucfirst(str_replace('_', ' ', $t->status_transaksi)) }}
                                </span>

                                {{-- Payment Status Badge --}}
                                @if ($t->status_bayar == 'belum_lunas')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                 bg-red-100 text-red-700 rounded-lg text-xs font-semibold">
                                        <i class="bi bi-x-circle"></i>
                                        Belum Bayar
                                    </span>
                                @elseif ($t->status_bayar == 'DP')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-semibold">
                                        <i class="bi bi-cash"></i>
                                        DP
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
                    <p class="text-xl text-gray-600 font-semibold">Tidak ada transaksi</p>
                    <p class="text-gray-400 text-sm mt-2">Transaksi akan muncul di sini</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Hidden Forms for Delete --}}
@foreach($riwayat as $t)
<form id="deleteForm{{ $t->id_transaksi }}" 
      action="{{ route('admin2.riwayat.destroy', $t->id_transaksi) }}" 
      method="POST" 
      style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let items = document.querySelectorAll('#riwayatList > div');
    
    items.forEach(function(item) {
        let nama = item.getAttribute('data-nama');
        let id = item.getAttribute('data-id');
        
        if (nama && id && (nama.includes(filter) || id.includes(filter))) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

// DELETE BUTTON HANDLER
document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 Setting up delete buttons');
    
    const deleteButtons = document.querySelectorAll('.delete-btn');
    console.log('Found buttons:', deleteButtons.length);
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const transaksiId = this.getAttribute('data-id');
            const namaPelanggan = this.getAttribute('data-nama');
            const totalHarga = this.getAttribute('data-total');
            const tanggalTransaksi = this.getAttribute('data-tanggal');
            
            console.log('🔴 DELETE CLICKED!', transaksiId, namaPelanggan);
            
            Swal.fire({
                title: 'Hapus Transaksi?',
                html: `
                    <div class="text-left">
                        <p class="text-gray-600 mb-3">Anda akan menghapus transaksi berikut:</p>
                        <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-200 rounded-xl p-4 my-4 shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="bi bi-receipt-cutoff text-red-600 text-xl"></i>
                                <p class="font-bold text-red-700 text-lg">TRX/${transaksiId}</p>
                            </div>
                            <div class="space-y-1.5 ml-7">
                                <p class="text-gray-800 font-semibold">${namaPelanggan}</p>
                                <p class="text-gray-600 text-sm">
                                    <i class="bi bi-calendar3 text-blue-500"></i> ${tanggalTransaksi}
                                </p>
                                <p class="text-yellow-700 font-bold text-lg">
                                    <i class="bi bi-cash-coin text-yellow-600"></i> Rp ${totalHarga}
                                </p>
                            </div>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3">
                            <p class="text-sm text-blue-700 flex items-start gap-2">
                                <i class="bi bi-info-circle text-blue-500 text-lg mt-0.5"></i>
                                <span>Data transaksi yang sudah dihapus tidak dapat dikembalikan lagi.</span>
                            </p>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-trash-fill me-2"></i>Ya, Hapus Transaksi!',
                cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Batal',
                reverseButtons: true,
                width: '600px',
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
                        title: 'Menghapus Transaksi...',
                        html: '<div class="flex flex-col items-center"><i class="bi bi-hourglass-split text-4xl text-yellow-500 animate-pulse mb-2"></i><p class="text-gray-600">Mohon tunggu sebentar</p></div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    document.getElementById('deleteForm' + transaksiId).submit();
                }
            });
            
            return false;
        }, true);
    });
});
</script>

@endsection