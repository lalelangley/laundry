@extends('layouts.master')

@section('content')

<div class="min-h-screen bg-gray-50">
    
    {{-- ========================================
         HEADER SECTION
    ======================================== --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('kasir.dashboard') }}" 
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
                       class="w-full pl-12 pr-4 py-4 rounded-xl bg-white shadow-md outline-none focus:ring-2 focus:ring-yellow-400 transition-all text-base"
                       placeholder="Cari transaksi berdasarkan nama, no nota...">
            </div>
        </div>

        {{-- ========================================
             STATUS TABS
        ======================================== --}}
        <div class="mb-6 bg-white p-2 rounded-2xl shadow-lg overflow-x-auto">
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
                    <a href="{{ route('kasir.riwayat.index', ['tab' => $key]) }}"
                       class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl whitespace-nowrap font-semibold transition-all
                              {{ $tab == $key 
                                  ? 'bg-yellow-400 text-black shadow-md scale-105' 
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
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
                <div class="group relative">
                    <a href="{{ route('kasir.riwayat.detail', $t->id_transaksi) }}" 
                       class="block">
                        <div class="relative bg-white shadow-lg rounded-2xl p-6 
                                    hover:shadow-xl transition-all duration-300 hover:-translate-y-1 
                                    border-l-4
                                    @if($t->status_transaksi == 'antrian') border-gray-500
                                    @elseif($t->status_transaksi == 'proses') border-blue-500
                                    @elseif($t->status_transaksi == 'siap_di_ambil') border-teal-500
                                    @elseif($t->status_transaksi == 'selesai') border-green-500
                                    @else border-red-500
                                    @endif">

                            {{-- Card Header --}}
                            <div class="flex justify-between items-start mb-4 pb-4 border-b-2 border-gray-100">
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
                                <form action="{{ route('kasir.riwayat.destroy', $t->id_transaksi) }}"
                                      method="POST"
                                      onclick="event.stopPropagation();"
                                      class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-500 text-white p-2 rounded-lg shadow 
                                                   hover:bg-red-600 hover:scale-110 transition-all"
                                            onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- Price Display --}}
                            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 
                                        px-4 py-3 rounded-xl mb-4 shadow-md">
                                <p class="text-sm font-semibold mb-1">Total Harga</p>
                                <p class="text-2xl font-bold">
                                    Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                                </p>
                            </div>

                            {{-- Transaction Details --}}
                            <div class="space-y-3">
                                
                                {{-- Tanggal Masuk --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-calendar-date text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Tanggal Masuk</p>
                                        <p class="font-semibold text-gray-800">{{ $t->tgl_transaksi }}</p>
                                    </div>
                                </div>

                                {{-- Estimasi Selesai --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-calendar-check text-green-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Estimasi Selesai</p>
                                        <p class="font-semibold text-gray-800">{{ $t->tgl_estimasi }}</p>
                                    </div>
                                </div>

                                {{-- Diskon (if exists) --}}
                                @if($t->diskon > 0)
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-percent text-red-600"></i>
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
                            <div class="mt-4 pt-4 border-t-2 border-gray-100 flex flex-wrap gap-2">
                                
                                {{-- Transaction Status Badge --}}
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg 
                                             text-white text-xs font-semibold
                                             @if ($t->status_transaksi == 'antrian') bg-gray-600
                                             @elseif ($t->status_transaksi == 'proses') bg-blue-500
                                             @elseif ($t->status_transaksi == 'pick_up') bg-purple-600
                                             @elseif ($t->status_transaksi == 'siap_di_ambil') bg-teal-600
                                             @elseif ($t->status_transaksi == 'siap_di_antar') bg-indigo-600
                                             @elseif ($t->status_transaksi == 'selesai') bg-green-600
                                             @else bg-red-600
                                             @endif">
                                    <i class="bi bi-clipboard-check"></i>
                                    {{ ucfirst(str_replace('_', ' ', $t->status_transaksi)) }}
                                </span>

                                {{-- Payment Status Badge --}}
                                @if ($t->status_bayar == 'belum_lunas')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                 bg-red-500 text-white rounded-lg text-xs font-semibold">
                                        <i class="bi bi-x-circle"></i>
                                        Belum Bayar
                                    </span>
                                @elseif ($t->status_bayar == 'DP')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                 bg-yellow-400 text-gray-800 rounded-lg text-xs font-semibold">
                                        <i class="bi bi-cash"></i>
                                        DP
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
                                
                                {{-- Button: Proses --}}
                                @if($t->status_transaksi == 'antrian')
                                    <a href="{{ route('kasir.riwayat.proses', $t->id_transaksi) }}"
                                       onclick="event.preventDefault(); event.stopPropagation(); window.location.href=this.href;"
                                       class="flex-1 text-center px-4 py-2 bg-blue-500 text-white rounded-lg shadow 
                                              hover:bg-blue-600 transition-all font-semibold">
                                        <i class="bi bi-arrow-repeat mr-1"></i>
                                        Proses
                                    </a>
                                @endif

                                {{-- Button: Siap Diambil --}}
                                @if($t->status_transaksi == 'proses')
                                    <a href="{{ route('kasir.riwayat.siap_di_ambil', $t->id_transaksi) }}"
                                       onclick="event.preventDefault(); event.stopPropagation(); window.location.href=this.href;"
                                       class="flex-1 text-center px-4 py-2 bg-teal-500 text-white rounded-lg shadow 
                                              hover:bg-teal-600 transition-all font-semibold">
                                        <i class="bi bi-check-circle mr-1"></i>
                                        Siap Diambil
                                    </a>
                                @endif

                                {{-- Button: Selesai --}}
                                @if($t->status_transaksi == 'siap_di_ambil')
                                    <a href="{{ route('kasir.riwayat.selesai', $t->id_transaksi) }}"
                                       onclick="event.preventDefault(); event.stopPropagation(); window.location.href=this.href;"
                                       class="flex-1 text-center px-4 py-2 bg-green-500 text-white rounded-lg shadow 
                                              hover:bg-green-600 transition-all font-semibold">
                                        <i class="bi bi-check-all mr-1"></i>
                                        Selesai
                                    </a>
                                @endif

                                {{-- Button: Batal --}}
                                @if(!in_array($t->status_transaksi, ['selesai', 'batal']))
                                    <a href="{{ route('kasir.riwayat.batal', $t->id_transaksi) }}"
                                       onclick="event.preventDefault(); event.stopPropagation(); if(confirm('Yakin ingin membatalkan transaksi ini?')) window.location.href=this.href;"
                                       class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded-lg shadow 
                                              hover:bg-red-600 transition-all font-semibold">
                                        <i class="bi bi-x-circle mr-1"></i>
                                        Batal
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
                    <p class="text-xl text-gray-500 font-semibold">Tidak ada transaksi</p>
                    <p class="text-gray-400 text-sm mt-2">Transaksi akan muncul di sini</p>
                </div>
            @endforelse
            
        </div>
        
    </div>
    
</div>

@endsection