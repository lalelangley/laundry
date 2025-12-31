@extends('layouts.master')

@section('content')

<div class="min-h-screen bg-gray-50">
    
    {{-- ========================================
         HEADER SECTION
    ======================================== --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('admin.dashboard') }}" 
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
                       class="w-full pl-12 pr-4 py-4 rounded-xl bg-white shadow-md outline-none focus:ring-2 focus:ring-yellow-400 transition-all text-base"
                       placeholder="Cari pesanan berdasarkan nama, no pesanan...">
            </div>
        </div>

        {{-- ========================================
             STATUS TABS
        ======================================== --}}
        <div class="mb-6 bg-white p-2 rounded-2xl shadow-lg overflow-x-auto">
            <div class="flex gap-2 flex-wrap justify-center">
                @php
                    $tabs = [
                        'menunggu_konfirmasi' => [
                            'label' => 'Menunggu', 
                            'icon' => 'hourglass-split'
                        ],
                        'dikonfirmasi' => [
                            'label' => 'Dikonfirmasi', 
                            'icon' => 'check-square'
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
                    <a href="{{ route('pesanan.online.index', ['tab' => $key]) }}"
                       class="flex-1 min-w-[120px] flex items-center justify-center gap-2 px-4 py-3 rounded-xl whitespace-nowrap font-semibold transition-all
                              {{ $tab == $key 
                                  ? 'bg-yellow-400 text-black shadow-md scale-105' 
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
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
                    <a href="{{ route('pesanan.online.detail', $p->id_transaksi) }}" 
                       class="block">
                        <div class="relative bg-white shadow-lg rounded-2xl p-6 
                                    hover:shadow-xl transition-all duration-300 hover:-translate-y-1 
                                    border-l-4
                                    @if($p->status_transaksi == 'menunggu_konfirmasi') border-orange-500
                                    @elseif($p->status_transaksi == 'dikonfirmasi') border-blue-500
                                    @elseif($p->status_transaksi == 'proses') border-purple-500
                                    @elseif($p->status_transaksi == 'siap_di_ambil') border-teal-500
                                    @elseif($p->status_transaksi == 'siap_di_antar') border-gray-500
                                    @elseif($p->status_transaksi == 'selesai') border-green-500
                                    @else border-red-500
                                    @endif">

                            {{-- Card Header --}}
                            <div class="flex justify-between items-start mb-4 pb-4 border-b-2 border-gray-100">
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
                                <form action="{{ route('pesanan.online.destroy', $p->id_transaksi) }}"
                                      method="POST"
                                      onclick="event.stopPropagation();"
                                      class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-500 text-white p-2 rounded-lg shadow 
                                                   hover:bg-red-600 hover:scale-110 transition-all"
                                            onclick="return confirm('Yakin ingin menghapus pesanan ini?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- Price Display --}}
                            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 
                                        px-4 py-3 rounded-xl mb-4 shadow-md">
                                <p class="text-sm font-semibold mb-1">Total Harga</p>
                                <p class="text-2xl font-bold">
                                    Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                </p>
                            </div>

                            {{-- Order Details --}}
                            <div class="space-y-3">
                                
                                {{-- Tanggal Pesan --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-calendar-date text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Tanggal Pesan</p>
                                        <p class="font-semibold text-gray-800">{{ $p->tgl_transaksi }}</p>
                                    </div>
                                </div>

                                {{-- No. Telepon --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-telephone text-green-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">No. Telepon</p>
                                        <p class="font-semibold text-gray-800">{{ $p->no_hp ?? '-' }}</p>
                                    </div>
                                </div>

                                {{-- Total Item --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-basket text-purple-600"></i>
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
                            <div class="mt-4 pt-4 border-t-2 border-gray-100 flex flex-wrap gap-2">
                                
                                {{-- Transaction Status Badge --}}
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg 
                                             text-white text-xs font-semibold
                                             @if ($p->status_transaksi == 'menunggu_konfirmasi') bg-orange-500
                                             @elseif ($p->status_transaksi == 'dikonfirmasi') bg-blue-500
                                             @elseif ($p->status_transaksi == 'proses') bg-purple-600
                                             @elseif ($p->status_transaksi == 'siap_di_ambil') bg-teal-600
                                             @elseif ($p->status_transaksi == 'siap_di_antar') bg-gray-600
                                             @elseif ($p->status_transaksi == 'selesai') bg-green-600
                                             @else bg-red-600
                                             @endif">
                                    <i class="bi bi-clipboard-check"></i>
                                    {{ ucfirst(str_replace('_', ' ', $p->status_transaksi)) }}
                                </span>

                                {{-- Payment Status Badge --}}
                                @if ($p->status_bayar == 'belum_bayar' || $p->status_bayar == 'belum_lunas')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                 bg-red-500 text-white rounded-lg text-xs font-semibold">
                                        <i class="bi bi-x-circle"></i>
                                        Belum Bayar
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                 bg-green-600 text-white rounded-lg text-xs font-semibold">
                                        <i class="bi bi-check-circle"></i>
                                        Lunas
                                    </span>
                                @endif
                                
                            </div>

                            {{-- Action Buttons --}}
                            <div class="mt-4 flex flex-wrap gap-2">
                                
                                {{-- Menunggu Konfirmasi Actions --}}
                                @if($p->status_transaksi == 'menunggu_konfirmasi')
                                    <form action="{{ route('pesanan.online.terima', $p->id_transaksi) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 font-semibold">
                                        <i class="bi bi-check-lg"></i> Terima
                                    </button>
                                </form>

                                    <a href="{{ route('pesanan.online.tolak', $p->id_transaksi) }}"
                                       onclick="event.preventDefault(); event.stopPropagation(); if(confirm('Tolak pesanan ini?')) window.location.href=this.href;"
                                       class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded-lg shadow 
                                              hover:bg-red-600 transition-all font-semibold">
                                        <i class="bi bi-x-lg"></i> Tolak
                                    </a>
                                @endif

                                {{-- Button: Proses --}}
                                @if($p->status_transaksi == 'dikonfirmasi')
                                    <a href="{{ route('pesanan.online.proses', $p->id_transaksi) }}"
                                       onclick="event.preventDefault(); event.stopPropagation(); window.location.href=this.href;"
                                       class="flex-1 text-center px-4 py-2 bg-blue-500 text-white rounded-lg shadow 
                                              hover:bg-blue-600 transition-all font-semibold">
                                        <i class="bi bi-arrow-repeat mr-1"></i>
                                        Proses
                                    </a>
                                @endif

                                {{-- Button: Siap Diambil --}}
                                @if($p->status_transaksi == 'proses')
                                    <a href="{{ route('pesanan.online.siap_di_ambil', $p->id_transaksi) }}"
                                       onclick="event.preventDefault(); event.stopPropagation(); window.location.href=this.href;"
                                       class="flex-1 text-center px-4 py-2 bg-teal-500 text-white rounded-lg shadow 
                                              hover:bg-teal-600 transition-all font-semibold">
                                        <i class="bi bi-box-seam mr-1"></i>
                                        Siap Diambil
                                    </a>
                                @endif

                                {{-- Button: Selesai --}}
                                @if($p->status_transaksi == 'siap_di_ambil')
                                    <a href="{{ route('pesanan.online.selesai', $p->id_transaksi) }}"
                                       onclick="event.preventDefault(); event.stopPropagation(); window.location.href=this.href;"
                                       class="flex-1 text-center px-4 py-2 bg-green-500 text-white rounded-lg shadow 
                                              hover:bg-green-600 transition-all font-semibold">
                                        <i class="bi bi-check-all mr-1"></i>
                                        Selesai
                                    </a>
                                @endif
                                
                            </div>

                        </div>
                    </a>
                </div>
            
            @empty
                {{-- Empty State --}}
                <div class="col-span-full flex flex-col items-center justify-center py-16">
                    <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                        <i class="bi bi-inbox text-5xl text-gray-400"></i>
                    </div>
                    <p class="text-xl text-gray-500 font-semibold">Tidak ada pesanan online</p>
                    <p class="text-gray-400 text-sm mt-2">Pesanan online akan muncul di sini</p>
                </div>
            @endforelse
            
        </div>
    </div>
</div>

{{-- ========================================
     JAVASCRIPT - SEARCH FUNCTIONALITY
======================================== --}}
<script>
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
</script>

@endsection