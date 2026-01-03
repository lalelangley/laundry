@extends('layouts.master')
@section('title', 'Kelola Pelanggan')
@section('content')

<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('admin.dashboard') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-gray-900">Kelola Pelanggan</span>
    </div>

    {{-- TABS CONTAINER - FULL WIDTH --}}
    <div class="px-8 pt-8 pb-12">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            
            {{-- TABS NAVIGATION --}}
            <div class="flex border-b border-gray-200 bg-gray-50">
                <button 
                    onclick="openTab('laporan')"
                    id="tab-laporan"
                    class="flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-yellow-400 bg-white text-gray-900">
                    <i class="bi bi-bar-chart-fill me-2"></i> Laporan
                </button>
                
                <button 
                    onclick="openTab('transaksi')"
                    id="tab-transaksi"
                    class="flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                    <i class="bi bi-credit-card-fill me-2"></i> Transaksi
                </button>
                
                <button 
                    onclick="openTab('data')"
                    id="tab-data"
                    class="flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                    <i class="bi bi-database-fill me-2"></i> Data & Export
                </button>
            </div>

            {{-- TAB CONTENT: LAPORAN --}}
            <div id="laporan" class="tab-content p-12">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-graph-up me-2"></i>Laporan Utama
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <a href="{{ route('laporan.transaksi.index') }}" 
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-receipt text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Laporan Transaksi</h4>
                            <p class="text-gray-600">Lihat semua transaksi yang telah dilakukan</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('laporan.pengeluaran.index') }}" 
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-wallet2 text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Laporan Pengeluaran</h4>
                            <p class="text-gray-600">Monitor dan analisis pengeluaran bisnis</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('laporan.pelanggan.index') }}" 
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-people text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Laporan Pelanggan</h4>
                            <p class="text-gray-600">Data lengkap tentang pelanggan Anda</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </a>
                </div>
            </div>

            {{-- TAB CONTENT: TRANSAKSI --}}
            <div id="transaksi" class="tab-content p-12 hidden">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-cash-stack me-2"></i>Informasi Transaksi
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <a href="{{ route('laporan.kasir.index') }}" 
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-person-badge text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Laporan Kasir</h4>
                            <p class="text-gray-600">Pantau performa kasir per periode</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('laporan.bayar.index') }}" 
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-credit-card-2-front text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Laporan Metode Bayar</h4>
                            <p class="text-gray-600">Analisis metode pembayaran pelanggan</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </a>
                    
                    {{-- ✅ TAMBAH LAPORAN DRIVER --}}
                    <a href="{{ route('laporan.driver.index') }}" 
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-truck text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Laporan Driver</h4>
                            <p class="text-gray-600">Pantau performa driver dan pengiriman</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </a>
                    
                    <a href="{{ route('laporan.satuan.index') }}" 
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-box-seam text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Laporan Satuan</h4>
                            <p class="text-gray-600">Detail laporan berdasarkan satuan produk</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </a>
                </div>
            </div>

            {{-- TAB CONTENT: DATA & EXPORT --}}
            <div id="data" class="tab-content p-12 hidden">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-file-earmark-arrow-down me-2"></i>Export & Backup Data
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <a href="#" 
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-file-earmark-excel text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Export Data Transaksi Ke Excel</h4>
                            <p class="text-gray-600">Download semua data transaksi dalam format Excel</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </a> 
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openTab(tabName) {
    // Sembunyikan semua tab content
    var tabs = document.getElementsByClassName('tab-content');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.add('hidden');
    }
    
    // Hapus styling aktif dari semua button
    document.getElementById('tab-laporan').className = 'flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100';
    document.getElementById('tab-transaksi').className = 'flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100';
    document.getElementById('tab-data').className = 'flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100';
    
    // Tampilkan tab yang dipilih
    document.getElementById(tabName).classList.remove('hidden');
    
    // Tambahkan styling aktif ke button yang diklik
    document.getElementById('tab-' + tabName).className = 'flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-yellow-400 bg-white text-gray-900';
}
</script>
@endsection