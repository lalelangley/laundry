@extends('layouts.master')

@section('content')

<div class="min-h-screen bg-gray-50">
    
    {{-- ========================================
         HEADER SECTION
    ======================================== --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-md sticky top-0 z-10">
        <a href="{{ route('admin.dashboard') }}" 
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
                    <a href="{{ route('riwayat.index', ['tab' => $key]) }}"
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
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
            
            @forelse ($riwayat as $t)
                <a href="{{ route('riwayat.detail', $t->id_transaksi) }}" 
                   class="block group">
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
                            <div class="flex-1">
                                <h2 class="font-bold text-xl text-gray-800 mb-1">
                                    {{ $t->nama_pelanggan }}
                                </h2>
                                <p class="text-gray-500 text-sm flex items-center gap-1">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    TRX/{{ $t->id_transaksi }}
                                </p>
                            </div>
                            
                            {{-- Delete Button (hover to show) --}}
                            <button type="button"
                                    onclick="event.preventDefault(); event.stopPropagation(); confirmDelete({{ $t->id_transaksi }}, '{{ $t->nama_pelanggan }}')"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-red-50 text-red-600 p-2 rounded-lg hover:bg-red-500 hover:text-white hover:scale-110 transition-all">
                                <i class="bi bi-trash-fill"></i>
                            </button>
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
      action="{{ route('riwayat.destroy', $t->id_transaksi) }}" 
      method="POST" 
      style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(transaksiId, namaPelanggan) {
    Swal.fire({
        title: 'Hapus Transaksi?',
        html: `<div class="text-gray-600">
                    <p class="mb-2">Anda akan menghapus transaksi:</p>
                    <div class="bg-red-50 border-2 border-red-200 rounded-xl p-3 my-3">
                        <p class="font-bold text-red-700 text-lg">TRX/${transaksiId}</p>
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
                title: 'Menghapus Transaksi...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form
            document.getElementById('deleteForm' + transaksiId).submit();
        }
    });
}
</script>

@endsection