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
                    <i class="bi bi-database-fill me-2"></i> Data & Export
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

            {{-- TAB CONTENT: DATA & EXPORT --}}
            <div id="data" class="tab-content p-12 hidden">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-file-earmark-arrow-down me-2"></i>Export & Backup Data
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <button type="button" onclick="openExportModal()"
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all text-left w-full">
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-file-earmark-excel text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Export Data Transaksi Ke Excel</h4>
                            <p class="text-gray-600">Download semua data transaksi dalam format Excel</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </button> 
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ✅ MODAL POPUP EXPORT EXCEL --}}
<div id="exportModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center px-4 z-50 hidden">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-hidden animate-fadeIn">
        
        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 px-8 py-6 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                    <i class="bi bi-file-earmark-excel text-white text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-white drop-shadow-sm">
                    Export Data Transaksi
                </h3>
            </div>
            <button onclick="closeExportModal()" 
                class="w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-xl transition text-white text-2xl font-bold">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="overflow-y-auto max-h-[calc(95vh-88px)]">
            <form action="{{ route('kasir.laporan.transaksi.export') }}" method="POST" class="p-8 space-y-6" data-no-loading>
                @csrf
                
                {{-- Filter Berdasarkan --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-lg font-bold text-gray-900">
                        <i class="bi bi-funnel-fill text-yellow-500"></i>
                        Filter Berdasarkan
                    </label>
                    <select name="filter_type" id="filterType"
                        class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition bg-gray-50 hover:bg-white">
                        <option value="tanggal_masuk">📅 Tanggal Masuk Pesanan</option>
                        <option value="tanggal_selesai">✅ Tanggal Selesai</option>
                        <option value="tanggal_bayar">💰 Tanggal Bayar</option>
                    </select>
                </div>

                {{-- Tanggal Awal & Akhir --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-base font-semibold text-gray-900">
                            <i class="bi bi-calendar-check text-yellow-500"></i>
                            Tanggal Awal
                        </label>
                        <input type="date" name="tanggal_awal" required
                            value="{{ date('Y-m-d', strtotime('-30 days')) }}"
                            class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition bg-gray-50 hover:bg-white">
                    </div>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-base font-semibold text-gray-900">
                            <i class="bi bi-calendar-x text-yellow-500"></i>
                            Tanggal Akhir
                        </label>
                        <input type="date" name="tanggal_akhir" required
                            value="{{ date('Y-m-d') }}"
                            class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition bg-gray-50 hover:bg-white">
                    </div>
                </div>

                {{-- Status Pembayaran --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-lg font-bold text-gray-900">
                        <i class="bi bi-cash-coin text-yellow-500"></i>
                        Status Pembayaran
                    </label>
                    <select name="status_bayar"
                        class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition bg-gray-50 hover:bg-white">
                        <option value="semua">📊 Semua Status</option>
                        <option value="lunas">✅ Lunas</option>
                        <option value="belum_lunas">⏳ Belum Lunas</option>
                        <option value="DP">💵 DP (Down Payment)</option>
                    </select>
                </div>

                {{-- Nama File --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-base font-semibold text-gray-900">
                        <i class="bi bi-file-text text-yellow-500"></i>
                        Nama File (Opsional)
                    </label>
                    <input type="text" name="nama_file" 
                        placeholder="Contoh: Laporan_Transaksi_Januari_2026"
                        value="Laporan_Transaksi_{{ date('d_m_Y') }}"
                        class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition bg-gray-50 hover:bg-white placeholder:text-gray-400">
                    <p class="flex items-start gap-2 text-sm text-gray-500">
                        <i class="bi bi-info-circle-fill text-yellow-500 mt-0.5"></i>
                        <span>Kosongkan untuk menggunakan nama file default dengan tanggal otomatis</span>
                    </p>
                </div>

                {{-- Divider --}}
                <div class="border-t border-gray-200 my-6"></div>

                {{-- Info Box --}}
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-5">
                    <div class="flex gap-3">
                        <i class="bi bi-lightbulb-fill text-yellow-600 text-xl flex-shrink-0 mt-0.5"></i>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p class="font-semibold text-gray-900">Informasi Export:</p>
                            <ul class="space-y-1 list-disc list-inside ml-2">
                                <li>File akan didownload dalam format Excel (.xlsx)</li>
                                <li>Data yang diexport hanya transaksi dengan status "Selesai"</li>
                                <li>Proses export mungkin memakan waktu jika data sangat banyak</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeExportModal()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-lg py-4 rounded-xl transition shadow-sm hover:shadow flex items-center justify-center gap-2">
                        <i class="bi bi-x-circle"></i>
                        <span>Batal</span>
                    </button>
                    
                    <button type="submit"
                        class="flex-1 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold text-lg py-4 rounded-xl transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <i class="bi bi-download"></i>
                        <span>Download Excel</span>
                    </button>
                </div>
            </form>
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

#exportModal .overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

#exportModal .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#exportModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

#exportModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
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

// ✅ Function buka modal export
function openExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

// ✅ Function tutup modal export
function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        const content = modal.querySelector('.bg-white');
        if (content) {
            content.style.opacity = '0';
            content.style.transform = 'scale(0.95) translateY(-20px)';
        }
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            if (content) {
                content.style.opacity = '';
                content.style.transform = '';
            }
        }, 200);
    }
}

// ✅ Event listeners setelah DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set tab pertama aktif saat load
    openTab('laporan');
    
    // Tutup modal ketika klik di luar area modal
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeExportModal();
            }
        });
    }
    
    // Tutup dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeExportModal();
        }
    });
});
</script>
@endsection