{{-- FE-DOC: Template frontend untuk resources/views/admin2/laporan/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Kelola Pelanggan')
@section('content')

<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('admin2.dashboard') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
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
{{-- FE-DOC: Tab laporan memuat navigasi ke modul laporan utama. --}}
            <div id="laporan" class="tab-content p-12">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-graph-up me-2"></i>Laporan Utama
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <a href="{{ route('admin2.laporan.transaksi.index') }}" 
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
                    
                    <a href="{{ route('admin2.laporan.pengeluaran.index') }}" 
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
                    
                    <a href="{{ route('admin2.laporan.pelanggan.index') }}" 
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
{{-- FE-DOC: Tab transaksi berisi laporan yang berhubungan dengan proses operasional dan pembayaran. --}}
            <div id="transaksi" class="tab-content p-12 hidden">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-cash-stack me-2"></i>Informasi Transaksi
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <a href="{{ route('admin2.laporan.kasir.index') }}" 
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
                    
                    <a href="{{ route('admin2.laporan.bayar.index') }}" 
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
                    <a href="{{ route('admin2.laporan.driver.index') }}" 
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
                    
                    <a href="{{ route('admin2.laporan.satuan.index') }}" 
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
{{-- FE-DOC: Tab data dan export menampung aksi download atau backup data. --}}
            <div id="data" class="tab-content p-12 hidden">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-8">
                    <i class="bi bi-file-earmark-arrow-down me-2"></i>Export & Backup Data
                </h3>
                
                {{-- Kartu export memakai grid responsif agar layout tetap lega di semua ukuran layar --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <button type="button" onclick="openExportModal()"
                       class="flex items-center gap-6 p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-yellow-400 hover:shadow-xl transition-all text-left w-full">
                        {{-- Ikon memakai warna brand supaya user langsung paham ini aksi export utama --}}
                        <div class="w-20 h-20 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex-shrink-0">
                            <i class="bi bi-file-earmark-arrow-down text-white text-4xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-xl text-gray-900 mb-2">Export Data Transaksi</h4>
                            <p class="text-gray-600">Download laporan transaksi dalam format Excel atau PDF</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-gray-400"></i>
                    </button> 
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ✅ MODAL POPUP EXPORT TRANSAKSI --}}
<div id="exportModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center px-4 z-50 hidden">
    {{-- Wrapper modal dibuat besar dan center-aligned agar form export terasa fokus --}}
    <div class="bg-[#fcfcfb] rounded-[32px] shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-hidden animate-fadeIn export-modal-shell">
        <div class="overflow-y-auto max-h-[95vh]">
            <form action="{{ route('admin2.laporan.transaksi.export') }}" method="POST" class="p-8 md:p-10 space-y-6" data-no-loading>
                @csrf
                {{-- Nilai hidden ini menentukan backend harus kirim Excel atau PDF --}}
                <input type="hidden" name="format" id="exportFormat" value="excel">
                
                {{-- Pilihan filter_type akan memetakan export ke kolom tanggal yang sesuai --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-3 text-[15px] font-bold text-gray-900">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                            <i class="bi bi-calendar2-week"></i>
                        </span>
                        Filter Berdasarkan
                    </label>
                    <select name="filter_type" id="filterType"
                        class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-2xl focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition bg-white shadow-sm">
                        <option value="tanggal_masuk">Tanggal Masuk Pesanan</option>
                        <option value="tanggal_selesai">Tanggal Selesai</option>
                        <option value="tanggal_bayar">Tanggal Bayar</option>
                    </select>
                </div>

                {{-- Grid 2 kolom dipakai untuk pasangan tanggal awal dan akhir --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 text-[15px] font-bold text-gray-900">
                            <span class="w-8 h-8 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center">
                                <i class="bi bi-calendar-check"></i>
                            </span>
                            Tanggal Awal
                        </label>
                        <input type="date" name="tanggal_awal" required
                            value="{{ date('Y-m-d', strtotime('-30 days')) }}"
                            class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-2xl focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition bg-white shadow-sm">
                    </div>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 text-[15px] font-bold text-gray-900">
                            <span class="w-8 h-8 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center">
                                <i class="bi bi-calendar-range"></i>
                            </span>
                            Tanggal Akhir
                        </label>
                        <input type="date" name="tanggal_akhir" required
                            value="{{ date('Y-m-d') }}"
                            class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-2xl focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition bg-white shadow-sm">
                    </div>
                </div>

                {{-- Status pembayaran disediakan sebagai dropdown tunggal agar form tetap ringkas --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-3 text-[15px] font-bold text-gray-900">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                            <i class="bi bi-cash-coin"></i>
                        </span>
                        Status Pembayaran
                    </label>
                    <select name="status_bayar"
                        class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-2xl focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition bg-white shadow-sm">
                        <option value="semua">Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="DP">DP (Down Payment)</option>
                    </select>
                </div>

                {{-- Nama file tetap editable supaya user bisa memberi nama export sendiri --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-3 text-[15px] font-bold text-gray-900">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                            <i class="bi bi-file-earmark-text"></i>
                        </span>
                        Nama File (Opsional)
                    </label>
                    <input type="text" name="nama_file" 
                        placeholder="Contoh: Laporan_Transaksi_Januari_2026"
                        value="Laporan_Transaksi_{{ date('d_m_Y') }}"
                        class="w-full px-5 py-4 text-base border-2 border-gray-200 rounded-2xl focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition bg-white shadow-sm placeholder:text-gray-400">
                    <p class="flex items-start gap-2 text-sm text-gray-500">
                        <i class="bi bi-exclamation-circle-fill text-yellow-500 mt-0.5"></i>
                        <span>Kosongkan untuk menggunakan nama file default dengan tanggal otomatis</span>
                    </p>
                </div>

                <div class="border-t border-gray-200 my-6"></div>

                {{-- Panel info ini meniru area catatan kuning pada mockup referensi --}}
                <div class="bg-[#fff9db] border-2 border-yellow-200 rounded-2xl p-5">
                    <div class="flex gap-3">
                        <i class="bi bi-lightbulb-fill text-yellow-600 text-xl flex-shrink-0 mt-0.5"></i>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p class="font-semibold text-gray-900">Informasi Export:</p>
                            <ul class="space-y-1 list-disc list-inside ml-2">
                                <li>File akan didownload dalam format Excel (.xlsx) atau PDF (.pdf)</li>
                                <li>Filter tanggal dan status pembayaran akan ikut diterapkan ke hasil export</li>
                                <li>Proses export mungkin memakan waktu jika data sangat banyak</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Tombol dipisah jelas: batal, excel, dan pdf --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="button" onclick="closeExportModal()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-lg py-4 rounded-2xl transition shadow-sm hover:shadow flex items-center justify-center gap-2">
                        <i class="bi bi-x-circle"></i>
                        <span>Batal</span>
                    </button>
                    
                    <button type="submit" onclick="setExportFormat('excel')"
                        class="flex-1 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold text-lg py-4 rounded-2xl transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        <span>Download Excel</span>
                    </button>

                    <button type="submit" onclick="setExportFormat('pdf')"
                        class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold text-lg py-4 rounded-2xl transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Download PDF</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- FE-DOC: Blok CSS khusus halaman ini. --}}
<style>
/* Efek popup sederhana saat modal pertama kali ditampilkan. */
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

/* Border halus menjaga modal tetap kontras di atas backdrop. */
.export-modal-shell {
    border: 1px solid rgba(229, 231, 235, 0.9);
}

/* Scrollbar custom hanya untuk body modal export admin2. */
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

{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}

<script>
// ✅ Function untuk switch tab
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

// Membuka modal export dan mencegah body ikut scroll.
function openExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

// Menutup modal dengan transisi singkat agar terasa lebih halus.
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

// Hidden input format diisi sesuai tombol aksi yang ditekan user.
function setExportFormat(format) {
    const formatInput = document.getElementById('exportFormat');
    if (formatInput) {
        formatInput.value = format;
    }
}

// ✅ Event listeners setelah DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    // Klik backdrop modal = close modal.
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeExportModal();
            }
        });
    }
    
    // Escape disupport supaya interaksi keyboard tetap enak dipakai.
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeExportModal();
        }
    });
});
</script>
@endsection
