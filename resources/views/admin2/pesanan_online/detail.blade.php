@extends('layouts.master')

@section('content')

<div class="min-h-screen bg-gray-50 pb-10">
    
    {{-- ========================================
         HEADER SECTION
    ======================================== --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('pesanan.online.index') }}" 
           class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="flex-1">
            <span class="text-2xl font-bold">Detail Pesanan</span>
            <p class="text-sm text-gray-800 mt-1">ORDER/{{ $pesanan->id_transaksi }}</p>
        </div>
    </div>

    <div class="px-8 py-6 max-w-7xl mx-auto">
        
        {{-- ========================================
             MAIN CONTENT GRID
        ======================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- LEFT COLUMN - Order Items & Details --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Order Items Card --}}
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-basket text-purple-600 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Item Pesanan</h2>
                    </div>

                    <div class="space-y-4">
                        @foreach($pesanan->detail_transaksi as $index => $detail)
                        <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-all">
                            <div class="w-12 h-12 bg-yellow-400 rounded-lg flex items-center justify-center flex-shrink-0 font-bold text-lg">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 text-lg mb-1">
                                    {{ $detail->layanan->nama_layanan ?? 'Layanan' }}
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="bi bi-tag"></i>
                                    {{ $detail->jenis->jenis ?? 'Regular' }}
                                </p>
                                <div class="flex flex-wrap gap-3 text-sm">
                                    <span class="inline-flex items-center gap-1 text-gray-700">
                                        <i class="bi bi-box"></i>
                                        <strong>{{ $detail->qty }}</strong> item
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-gray-700">
                                        <i class="bi bi-cash"></i>
                                        Rp {{ number_format($detail->harga, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 mb-1">Subtotal</p>
                                <p class="text-lg font-bold text-gray-800">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Customer Information Card --}}
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-person text-blue-600 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Informasi Pelanggan</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <p class="text-sm text-gray-500">Nama Pelanggan</p>
                            <p class="font-semibold text-gray-800 text-lg">
                                {{ $pesanan->nama_pelanggan ?? 'Guest' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm text-gray-500">No. Telepon</p>
                            <p class="font-semibold text-gray-800 text-lg">
                                <i class="bi bi-telephone text-green-600"></i>
                                {{ $pesanan->no_hp ?? '-' }}
                            </p>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <p class="text-sm text-gray-500">Alamat</p>
                            <p class="font-semibold text-gray-800">
                                <i class="bi bi-geo-alt text-red-600"></i>
                                {{ $pesanan->alamat ?? 'Tidak ada alamat' }}
                            </p>
                        </div>
                        @if($pesanan->catatan)
                        <div class="space-y-1 md:col-span-2">
                            <p class="text-sm text-gray-500">Catatan</p>
                            <p class="font-semibold text-gray-800 bg-yellow-50 p-3 rounded-lg border-l-4 border-yellow-400">
                                <i class="bi bi-chat-left-text text-yellow-600"></i>
                                {{ $pesanan->catatan }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN - Summary & Actions --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Status Card --}}
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-clipboard-check text-green-600 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Status</h2>
                    </div>

                    <div class="space-y-4">
                        {{-- Transaction Status --}}
                        <div class="space-y-2">
                            <p class="text-sm text-gray-500">Status Transaksi</p>
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white font-semibold w-full justify-center
                                @if ($pesanan->status_transaksi == 'menunggu_konfirmasi') bg-orange-500
                                @elseif ($pesanan->status_transaksi == 'dikonfirmasi') bg-blue-500
                                @elseif ($pesanan->status_transaksi == 'proses') bg-purple-600
                                @elseif ($pesanan->status_transaksi == 'siap_di_ambil') bg-teal-600
                                @elseif ($pesanan->status_transaksi == 'siap_di_antar') bg-gray-600
                                @elseif ($pesanan->status_transaksi == 'selesai') bg-green-600
                                @else bg-red-600
                                @endif">
                                <i class="bi bi-circle-fill text-xs"></i>
                                {{ ucfirst(str_replace('_', ' ', $pesanan->status_transaksi)) }}
                            </span>
                        </div>

                        {{-- Payment Status --}}
                        <div class="space-y-2">
                            <p class="text-sm text-gray-500">Status Pembayaran</p>
                            @if ($pesanan->status_bayar == 'belum_bayar' || $pesanan->status_bayar == 'belum_lunas')
                                <span class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 text-white rounded-lg font-semibold w-full justify-center">
                                    <i class="bi bi-x-circle"></i>
                                    Belum Bayar
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg font-semibold w-full justify-center">
                                    <i class="bi bi-check-circle"></i>
                                    Lunas
                                </span>
                            @endif
                        </div>

                        {{-- Order Date --}}
                        <div class="space-y-2 pt-4 border-t">
                            <p class="text-sm text-gray-500">Tanggal Pesan</p>
                            <p class="font-semibold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-calendar-date text-blue-600"></i>
                                {{ $pesanan->tgl_transaksi }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Payment Summary Card --}}
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-calculator text-yellow-600 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Ringkasan</h2>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal</span>
                            <span class="font-semibold">
                                Rp {{ number_format($pesanan->detail_transaksi->sum('subtotal'), 0, ',', '.') }}
                            </span>
                        </div>

                        @if($pesanan->diskon > 0)
                        <div class="flex justify-between text-red-600">
                            <span>Diskon</span>
                            <span class="font-semibold">
                                - Rp {{ number_format($pesanan->diskon, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif

                        <div class="pt-3 border-t-2 border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-gray-800">Total</span>
                                <span class="text-2xl font-bold text-yellow-600">
                                    Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons Card --}}
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h2>
                    
                    <div class="space-y-3">
                        
                        {{-- Menunggu Konfirmasi Actions --}}
                        @if($pesanan->status_transaksi == 'menunggu_konfirmasi')
                            <a href="{{ route('pesanan.online.terima', $pesanan->id_transaksi) }}"
                               onclick="return confirm('Terima pesanan ini?')"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-500 text-white rounded-lg shadow 
                                      hover:bg-green-600 transition-all font-semibold">
                                <i class="bi bi-check-lg"></i>
                                Terima Pesanan
                            </a>
                            <a href="{{ route('pesanan.online.tolak', $pesanan->id_transaksi) }}"
                               onclick="return confirm('Tolak pesanan ini?')"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500 text-white rounded-lg shadow 
                                      hover:bg-red-600 transition-all font-semibold">
                                <i class="bi bi-x-lg"></i>
                                Tolak Pesanan
                            </a>
                        @endif

                        {{-- Dikonfirmasi Action --}}
                        @if($pesanan->status_transaksi == 'dikonfirmasi')
                            <a href="{{ route('pesanan.online.proses', $pesanan->id_transaksi) }}"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-500 text-white rounded-lg shadow 
                                      hover:bg-blue-600 transition-all font-semibold">
                                <i class="bi bi-arrow-repeat"></i>
                                Mulai Proses
                            </a>
                        @endif

                        {{-- Proses Action --}}
                        @if($pesanan->status_transaksi == 'proses')
                            <a href="{{ route('pesanan.online.siap_di_ambil', $pesanan->id_transaksi) }}"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-teal-500 text-white rounded-lg shadow 
                                      hover:bg-teal-600 transition-all font-semibold">
                                <i class="bi bi-box-seam"></i>
                                Tandai Siap Diambil
                            </a>
                        @endif

                        {{-- Siap Diambil Action --}}
                        @if($pesanan->status_transaksi == 'siap_di_ambil')
                            <a href="{{ route('pesanan.online.selesai', $pesanan->id_transaksi) }}"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-500 text-white rounded-lg shadow 
                                      hover:bg-green-600 transition-all font-semibold">
                                <i class="bi bi-check-all"></i>
                                Selesaikan Pesanan
                            </a>
                        @endif

                        {{-- Print Button --}}
                        <button onclick="window.print()" 
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-600 text-white rounded-lg shadow 
                                       hover:bg-gray-700 transition-all font-semibold">
                            <i class="bi bi-printer"></i>
                            Cetak Nota
                        </button>

                        {{-- Back Button --}}
                        <a href="{{ route('pesanan.online.index') }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg shadow 
                                  hover:bg-gray-50 transition-all font-semibold">
                            <i class="bi bi-arrow-left"></i>
                            Kembali
                        </a>
                        
                    </div>
                </div>

            </div>

        </div>
        
    </div>
    
</div>

{{-- ========================================
     PRINT STYLES
======================================== --}}
<style>
@media print {
    .sticky, button, a[href*="route"] {
        display: none !important;
    }
    .bg-gray-50 {
        background: white !important;
    }
    .shadow-lg, .shadow-md {
        box-shadow: none !important;
    }
}
</style>

@endsection