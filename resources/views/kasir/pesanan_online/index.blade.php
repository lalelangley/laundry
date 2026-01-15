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
            <div class="flex gap-2 justify-between">
                @php
                    // ✅ DEFINISIKAN $tabs DULU DI AWAL
                    $tabs = [
                        'pickup' => [
                            'label' => 'Pickup',
                            'icon'  => 'truck',
                            'badge_color' => 'bg-yellow-500'
                        ],
                        'antrian' => [
                            'label' => 'Antrian',
                            'icon'  => 'hourglass-split',
                            'badge_color' => 'bg-orange-500'
                        ],
                        'proses' => [
                            'label' => 'Proses',
                            'icon'  => 'arrow-repeat',
                            'badge_color' => 'bg-purple-500'
                        ],
                        'selesai_dicuci' => [
                            'label' => 'Selesai Dicuci',
                            'icon'  => 'check-circle-fill',
                            'badge_color' => 'bg-blue-500'
                        ],
                        'siap_di_ambil' => [
                            'label' => 'Siap Diambil',
                            'icon'  => 'check-circle',
                            'badge_color' => 'bg-teal-500'
                        ],
                        'siap_di_antar' => [
                            'label' => 'Siap Diantar',
                            'icon'  => 'bicycle',
                            'badge_color' => 'bg-indigo-500'
                        ],
                        'selesai' => [
                            'label' => 'Selesai',
                            'icon'  => 'check-all',
                            'badge_color' => 'bg-green-500'
                        ],
                        'ditolak' => [
                            'label' => 'Ditolak',
                            'icon'  => 'x-circle',
                            'badge_color' => 'bg-red-500'
                        ],
                    ];

                    // ✅ HITUNG PICKUP YANG BUTUH DRIVER
                    if(!isset($pickupNeedDriver)) {
                        $pickupNeedDriver = $pesanan->where('status_transaksi', 'pick_up')
                            ->filter(function($p) {
                                $delivery = $p->delivery ? $p->delivery->where('jenis', 'pickup')->first() : null;
                                return !$delivery || !$delivery->id_driver;
                            })
                            ->count();
                    }
                @endphp

                @foreach ($tabs as $key => $data)
                    <a href="{{ route('kasir.pesanan.online.index', ['tab' => $key]) }}"
                    class="relative flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl whitespace-nowrap font-semibold transition-all
                            {{ $tab == $key 
                                ? 'bg-yellow-400 text-gray-900 shadow-sm' 
                                : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}">
                        <i class="bi bi-{{ $data['icon'] }} text-lg"></i>
                        <span class="hidden sm:inline">{{ $data['label'] }}</span>
                        
                        {{-- 🔔 ALERT BADGE untuk Pickup --}}
                        @if($key == 'pickup' && $pickupNeedDriver > 0)
                            <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1.5 
                                        bg-red-500 text-white text-xs font-bold rounded-full 
                                        shadow-lg animate-pulse border-2 border-white">
                                {{ $pickupNeedDriver }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ========================================
             PESANAN LIST
        ======================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5" id="pesananList">
            @forelse ($pesanan as $p)
                @php
                    // ✅ DELIVERY DATA - AMBIL SEKALI DI AWAL
                    $deliveryPickup = $p->delivery ? $p->delivery->where('jenis', 'pickup')->first() : null;
                    $deliveryAntar = $p->delivery ? $p->delivery->where('jenis', 'antar')->first() : null;
                    
                    // ✅ TERLAMBAT CHECK
                    $terlambat = false;
                    if ($p->tgl_estimasi) {
                        $estimasi = \Carbon\Carbon::parse($p->tgl_estimasi);
                        $today = \Carbon\Carbon::today();
                        $terlambat = $today->greaterThan($estimasi);
                    }
                    
                    // ✅ PAYMENT DATA
                    $totalHarga = $p->total_harga ?? 0;
                    $totalBayar = $p->total_bayar ?? 0;
                    $sisaBayar = $totalHarga - $totalBayar;
                    
                    $isPaid = false;
                    $isDP = false;
                    $isUnpaid = false;
                    
                    if ($totalBayar >= $totalHarga && $totalHarga > 0) {
                        $isPaid = true;
                    } elseif ($totalBayar > 0 && $totalBayar < $totalHarga) {
                        $isDP = true;
                    } else {
                        $isUnpaid = true;
                    }
                @endphp
                
                <div data-nama="{{ strtolower($p->nama_pelanggan ?? '') }}" 
                     data-id="{{ $p->id_transaksi }}">
                    <a href="{{ route('kasir.pesanan.online.detail', $p->id_transaksi) }}" 
                       class="block group">
                        <div class="relative bg-white shadow-sm rounded-2xl p-6 
                                    hover:shadow-md transition-all duration-300 border border-gray-200
                                    @if($p->status_transaksi == 'pick_up') hover:border-yellow-300
                                    @elseif($p->status_transaksi == 'antrian') hover:border-orange-300
                                    @elseif($p->status_transaksi == 'proses') hover:border-purple-300
                                    @elseif($p->status_transaksi == 'selesai_dicuci') hover:border-blue-300
                                    @elseif($p->status_transaksi == 'siap_di_ambil') hover:border-teal-300
                                    @elseif($p->status_transaksi == 'siap_di_antar') hover:border-indigo-300
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
                                
                                {{-- ✅ DRIVER PICKUP INFO (HANYA JIKA SUDAH ADA DRIVER) --}}
                                @if($deliveryPickup && $deliveryPickup->id_driver && $deliveryPickup->driver)
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-person-badge text-orange-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Driver Pickup</p>
                                        <p class="font-semibold text-gray-800">{{ $deliveryPickup->driver->nama_driver }}</p>
                                    </div>
                                </div>
                                @endif

                                {{-- ✅ DRIVER DELIVERY INFO (HANYA JIKA SUDAH ADA DRIVER) --}}
                                @if($deliveryAntar && $deliveryAntar->id_driver && $deliveryAntar->driver)
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-person-badge text-indigo-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Driver Delivery</p>
                                        <p class="font-semibold text-gray-800">{{ $deliveryAntar->driver->nama_driver }}</p>
                                    </div>
                                </div>
                                @endif
                                
                            </div>

                            {{-- Status Badges --}}
                            <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                                
                                {{-- ROW 1: Transaction & Delivery Status --}}
                                <div class="flex flex-wrap gap-2">
                                    
                                    {{-- Transaction Status Badge --}}
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg 
                                                text-xs font-semibold
                                                @if ($p->status_transaksi == 'pick_up') bg-yellow-100 text-yellow-700
                                                @elseif ($p->status_transaksi == 'antrian') bg-orange-100 text-orange-700
                                                @elseif ($p->status_transaksi == 'proses') bg-purple-100 text-purple-700
                                                @elseif ($p->status_transaksi == 'selesai_dicuci') bg-blue-100 text-blue-700
                                                @elseif ($p->status_transaksi == 'siap_di_ambil') bg-teal-100 text-teal-700
                                                @elseif ($p->status_transaksi == 'siap_di_antar') bg-indigo-100 text-indigo-700
                                                @elseif ($p->status_transaksi == 'selesai') bg-green-100 text-green-700
                                                @else bg-red-100 text-red-700
                                                @endif">
                                        <i class="bi bi-clipboard-check"></i>
                                        {{ ucfirst(str_replace('_', ' ', $p->status_transaksi)) }}
                                    </span>

                                    {{-- ✅ TERLAMBAT BADGE --}}
                                    @if($terlambat && !in_array($p->status_transaksi, ['selesai', 'ditolak']))
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        Terlambat
                                    </span>
                                    @endif

                                    {{-- ========== ALERT PICKUP (STATUS: PICK_UP) ========== --}}
                                    @if($p->status_transaksi == 'pick_up')
                                        @if(!$deliveryPickup)
                                            {{-- Belum Ada Delivery --}}
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                        bg-red-600 text-white shadow-md animate-pulse">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                Buat Delivery & Isi Driver!
                                            </span>
                                        @elseif($deliveryPickup && !$deliveryPickup->id_driver)
                                            {{-- Ada Delivery, Belum Ada Driver --}}
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                        bg-red-500 text-white shadow-md animate-pulse">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                Isi Driver Pickup!
                                            </span>
                                        @elseif($deliveryPickup && $deliveryPickup->id_driver && $deliveryPickup->status == 'pending')
                                            {{-- Ada Driver, Masih Pending --}}
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                        bg-orange-500 text-white shadow-md animate-pulse">
                                                <i class="bi bi-clock-history"></i>
                                                Driver Belum Accept!
                                            </span>
                                        @endif
                                    @endif

                                    {{-- ✅ PICKUP STATUS BADGE (HANYA TAMPIL JIKA SUDAH ADA DRIVER & STATUS BUKAN PENDING) --}}
                                    @if($deliveryPickup && $deliveryPickup->id_driver && $deliveryPickup->status != 'pending')
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold
                                                    @if($deliveryPickup->status == 'accepted') bg-blue-100 text-blue-700
                                                    @elseif($deliveryPickup->status == 'on_the_way_to_pickup') bg-indigo-100 text-indigo-700
                                                    @elseif($deliveryPickup->status == 'picked_up') bg-purple-100 text-purple-700
                                                    @elseif($deliveryPickup->status == 'on_the_way_to_laundry') bg-violet-100 text-violet-700
                                                    @elseif($deliveryPickup->status == 'arrived_at_laundry') bg-green-100 text-green-700
                                                    @else bg-gray-100 text-gray-700
                                                    @endif">
                                            <i class="bi bi-arrow-down-circle"></i>
                                            @if($deliveryPickup->status == 'accepted')
                                                Driver Ditentukan
                                            @elseif($deliveryPickup->status == 'on_the_way_to_pickup')
                                                Menuju Pelanggan
                                            @elseif($deliveryPickup->status == 'picked_up')
                                                Cucian Dijemput
                                            @elseif($deliveryPickup->status == 'on_the_way_to_laundry')
                                                Menuju Laundry
                                            @elseif($deliveryPickup->status == 'arrived_at_laundry')
                                                Tiba di Laundry
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $deliveryPickup->status)) }}
                                            @endif
                                        </span>
                                    @endif

                                    {{-- ========== ALERT PROSES (STATUS: PROSES) ========== --}}
                                    @if($p->status_transaksi == 'proses')
                                        @if(!$deliveryAntar)
                                            {{-- Info: Siapkan Delivery --}}
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                        bg-blue-600 text-white shadow-md">
                                                <i class="bi bi-info-circle-fill"></i>
                                                Siapkan Delivery
                                            </span>
                                        @elseif($deliveryAntar && !$deliveryAntar->id_driver)
                                            {{-- Ada Delivery, Belum Ada Driver --}}
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                        bg-orange-500 text-white shadow-md animate-pulse">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                Isi Driver Delivery!
                                            </span>
                                        @endif
                                    @endif

                                    {{-- ========== ALERT DELIVERY (STATUS: SIAP_DI_ANTAR) ========== --}}
                                    @if($p->status_transaksi == 'siap_di_antar')
                                        @if(!$deliveryAntar)
                                            {{-- Belum Ada Delivery --}}
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                        bg-red-600 text-white shadow-md animate-pulse">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                Buat Delivery & Isi Driver!
                                            </span>
                                        @elseif($deliveryAntar && !$deliveryAntar->id_driver)
                                            {{-- Ada Delivery, Belum Ada Driver --}}
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                        bg-red-500 text-white shadow-md animate-pulse">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                Isi Driver Delivery!
                                            </span>
                                        @elseif($deliveryAntar && $deliveryAntar->id_driver && $deliveryAntar->status == 'pending')
                                            {{-- Ada Driver, Masih Pending --}}
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                        bg-orange-500 text-white shadow-md animate-pulse">
                                                <i class="bi bi-clock-history"></i>
                                                Driver Belum Accept!
                                            </span>
                                        @endif
                                    @endif

                                    {{-- ✅ DELIVERY STATUS BADGE (JIKA SUDAH ADA DRIVER & STATUS BUKAN PENDING) --}}
                                    @if($deliveryAntar && $deliveryAntar->id_driver && $p->status_transaksi == 'siap_di_antar' && $deliveryAntar->status != 'pending')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold
                                                @if($deliveryAntar->status == 'accepted') bg-blue-100 text-blue-700
                                                @elseif($deliveryAntar->status == 'on_the_way_to_customer') bg-purple-100 text-purple-700
                                                @elseif($deliveryAntar->status == 'delivered') bg-green-100 text-green-700
                                                @else bg-gray-100 text-gray-700
                                                @endif">
                                        <i class="bi bi-arrow-up-circle"></i>
                                        @if($deliveryAntar->status == 'accepted')
                                            Driver Ditentukan
                                        @elseif($deliveryAntar->status == 'on_the_way_to_customer')
                                            Menuju Pelanggan
                                        @elseif($deliveryAntar->status == 'delivered')
                                            Sudah Diantar ✓
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $deliveryAntar->status)) }}
                                        @endif
                                    </span>
                                    @endif
                                    
                                </div>

                                {{-- ROW 2: Payment Status & Method --}}
                                <div class="flex flex-wrap gap-2 items-center">
                                    
                                    {{-- ✅ PAYMENT METHOD BADGE --}}
                                    @if($p->metodeBayar)
                                        @php
                                            $isCash = stripos($p->metodeBayar->nama_metode_bayar, 'cash') !== false || 
                                                     stripos($p->metodeBayar->nama_metode_bayar, 'tunai') !== false;
                                            $isTransfer = stripos($p->metodeBayar->nama_metode_bayar, 'transfer') !== false || 
                                                         stripos($p->metodeBayar->nama_metode_bayar, 'tf') !== false;
                                        @endphp
                                        
                                        @if($isCash)
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold
                                                        bg-green-50 border border-green-200 text-green-700">
                                                <i class="bi bi-cash-stack"></i>
                                                Cash
                                            </span>
                                        @elseif($isTransfer)
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold
                                                        bg-blue-50 border border-blue-200 text-blue-700">
                                                <i class="bi bi-bank"></i>
                                                Transfer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold
                                                        bg-gray-50 border border-gray-200 text-gray-700">
                                                <i class="bi bi-wallet2"></i>
                                                {{ $p->metodeBayar->nama_metode_bayar }}
                                            </span>
                                        @endif
                                    @endif

                                    {{-- ✅ PAYMENT STATUS BADGE --}}
                                    @if ($isUnpaid)
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                    bg-red-100 text-red-700 rounded-lg text-xs font-semibold
                                                    border border-red-200">
                                            <i class="bi bi-x-circle"></i>
                                            Belum Bayar
                                        </span>
                                    @elseif ($isDP)
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                    bg-yellow-100 text-yellow-700 rounded-lg text-xs font-semibold
                                                    border border-yellow-200">
                                            <i class="bi bi-cash"></i>
                                            DP (Rp {{ number_format($totalBayar, 0, ',', '.') }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 
                                                    bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg text-xs font-bold
                                                    shadow-md border border-green-600">
                                            <i class="bi bi-check-circle-fill"></i>
                                            LUNAS ✓
                                        </span>
                                        
                                        @if($p->pembayaran && $p->pembayaran->count() > 0)
                                            @php
                                                $lastPayment = $p->pembayaran->sortByDesc('created_at')->first();
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-1 
                                                        bg-green-50 text-green-600 rounded text-xs">
                                                <i class="bi bi-clock-history"></i>
                                                {{ $lastPayment->created_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    @endif   
                                </div>
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
    <input type="hidden" name="from_tab" value="{{ $tab }}">
</form>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Search functionality
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

// Delete confirmation with SweetAlert2
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
