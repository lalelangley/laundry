@extends('layouts.master')
@section('title', 'Laporan')
@section('content')

<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('kasir.dashboard') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-gray-900">Laporan</span>
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
                    <i class="bi bi-database-fill me-2"></i> Akses Data
                </button>
            </div>

            {{-- TAB CONTENT: LAPORAN --}}
            <div id="laporan" class="tab-content p-12">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-graph-up me-2"></i>Laporan Utama
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <a href="{{ route('kasir.laporan.transaksi.index') }}" 
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
                    
                    <a href="{{ route('kasir.laporan.pengeluaran.index') }}" 
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
                    
                    <a href="{{ route('kasir.laporan.pelanggan.index') }}" 
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
                    {{-- ✅ GANTI: Laporan Kasir → Laporan Driver --}}
                    <a href="{{ route('kasir.laporan.driver.index') }}" 
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
                    
                    <a href="{{ route('kasir.laporan.bayar.index') }}" 
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
                    
                    <a href="{{ route('kasir.laporan.satuan.index') }}" 
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

            {{-- TAB CONTENT: AKSES DATA --}}
            <div id="data" class="tab-content p-12 hidden">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-eye-fill me-2"></i>Akses Data
                </h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <div class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl text-left w-full">
                        <div class="w-20 h-20 flex items-center justify-center bg-gray-100 rounded-2xl flex-shrink-0">
                            <i class="bi bi-lock-fill text-gray-600 text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Mode Lihat Saja</h4>
                            <p class="text-gray-600">Kasir hanya dapat melihat laporan. Fitur export data dinonaktifkan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}

</style>

<script>
// ✅ FIXED: Function untuk switch tab
function openTab(tabName) {
    // Sembunyikan semua tab content
    const tabs = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.add('hidden');
    }
    
    // Reset semua button tab ke state default
    const tabButtons = ['tab-laporan', 'tab-transaksi', 'tab-data'];
    const defaultClass = 'flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100';
    const activeClass = 'flex-1 px-8 py-5 text-center font-semibold text-lg border-b-4 border-yellow-400 bg-white text-gray-900';
    
    tabButtons.forEach(function(btnId) {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.className = defaultClass;
        }
    });
    
    // Tampilkan tab yang diklik
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
    }
    
    // Aktifkan button tab yang diklik
    const activeBtn = document.getElementById('tab-' + tabName);
    if (activeBtn) {
        activeBtn.className = activeClass;
    }
}

// ✅ Event listeners setelah DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set tab pertama aktif saat load
    openTab('laporan');
});
</script>
@endsection
