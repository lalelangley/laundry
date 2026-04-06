@extends('layouts.master')
@section('content')

{{-- ============================================================
     HALAMAN: RIWAYAT TRANSAKSI
     Deskripsi: Menampilkan daftar transaksi offline berdasarkan
     status (tab), dilengkapi pencarian dan fitur hapus transaksi.
     Role: Admin
     Paging: 10 data per halaman (dari controller)
============================================================ --}}

<div class="min-h-screen bg-gray-50">

    <style>
        {{-- Animasi tombol hapus muncul saat hover pada card --}}
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
         Tombol kembali ke Dashboard + judul halaman
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
             FLASH MESSAGE - SUCCESS / ERROR / INFO
             Menampilkan notifikasi hasil aksi dari controller
        ======================================== --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-green-600 text-xl"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-xl flex items-center gap-3">
                <i class="bi bi-x-circle-fill text-red-600 text-xl"></i>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 p-4 bg-blue-100 border border-blue-300 text-blue-800 rounded-xl flex items-center gap-3">
                <i class="bi bi-info-circle-fill text-blue-600 text-xl"></i>
                <span class="font-semibold">{{ session('info') }}</span>
            </div>
        @endif

        {{-- ========================================
             SEARCH BAR
             Mencari transaksi berdasarkan nama pelanggan atau no nota
             Pencarian dilakukan secara real-time via JavaScript
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
             STATUS TABS / FILTER TAB
             Tab untuk memfilter transaksi berdasarkan status.
             Tab aktif ditandai dengan background kuning.
        ======================================== --}}
        <div class="mb-6 bg-white p-2 rounded-2xl shadow-sm border border-gray-200 overflow-x-auto">
            <div class="flex gap-2 justify-between">
                @php
                    # Daftar tab beserta label dan icon-nya 
                    $tabs = [
                        'antrian'       => ['label' => 'Antrian',     'icon' => 'clock-history'],
                        'proses'        => ['label' => 'Proses',      'icon' => 'arrow-repeat'],
                        'siap_di_ambil' => ['label' => 'Siap Diambil','icon' => 'check-circle'],
                        'selesai'       => ['label' => 'Selesai',     'icon' => 'check-all'],
                        'batal'         => ['label' => 'Batal',       'icon' => 'x-circle'],
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
             DAFTAR TRANSAKSI (GRID CARD)
             Menampilkan kartu transaksi dalam grid responsif.
             Setiap kartu memiliki tombol hapus yang muncul saat hover.
        ======================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5" id="riwayatList">
            @forelse ($riwayat as $t)
                {{-- CARD WRAPPER - Pembungkus kartu dengan efek hover --}}
                <div data-nama="{{ strtolower($t->nama_pelanggan ?? '') }}" 
                     data-id="{{ $t->id_transaksi }}"
                     class="relative group card-wrapper">

                    {{-- TOMBOL HAPUS - Hanya muncul saat kartu di-hover --}}
                    <div class="absolute top-4 right-4 z-50" style="pointer-events: auto;">
                        <button type="button"
                                data-id="{{ $t->id_transaksi }}"
                                data-nama="{{ addslashes($t->nama_pelanggan ?? 'Guest') }}"
                                data-total="{{ number_format($t->total_harga, 0, ',', '.') }}"
                                data-tanggal="{{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y') }}"
                                class="delete-btn bg-red-600 text-white p-3 rounded-xl 
                                       hover:bg-red-700 shadow-lg border-2 border-white
                                       cursor-pointer">
                            <i class="bi bi-trash-fill text-lg"></i>
                        </button>
                    </div>

                    {{-- LINK KE DETAIL TRANSAKSI --}}
                    <a href="{{ route('riwayat.detail', $t->id_transaksi) }}" class="block">
                        <div class="relative bg-white shadow-sm rounded-2xl p-6 
                                    hover:shadow-md transition-all duration-300 border border-gray-200
                                    @if($t->status_transaksi == 'antrian')       hover:border-slate-300
                                    @elseif($t->status_transaksi == 'proses')    hover:border-blue-300
                                    @elseif($t->status_transaksi == 'siap_di_ambil') hover:border-teal-300
                                    @elseif($t->status_transaksi == 'selesai')   hover:border-green-300
                                    @else                                         hover:border-red-300
                                    @endif">
                            
                            {{-- Header Kartu: Nama Pelanggan & No Nota --}}
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

                            {{-- Kotak Total Harga --}}
                            <div class="bg-yellow-50 border border-yellow-200 text-gray-900 
                                        px-4 py-3 rounded-xl mb-4">
                                <p class="text-sm font-semibold mb-1 text-gray-600">Total Harga</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                                </p>
                            </div>

                            {{-- Detail Transaksi: Tanggal Masuk, Estimasi, Diskon --}}
                            <div class="space-y-3">
                                {{-- Tanggal Masuk --}}
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-calendar-date text-blue-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Tanggal Masuk</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Estimasi Selesai (opsional, tampil jika ada) --}}
                                @if($t->tgl_estimasi)
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="bi bi-calendar-check text-green-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-500 text-xs">Estimasi Selesai</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($t->tgl_estimasi)->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                                @endif

                                {{-- Diskon (opsional, tampil jika ada) --}}
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

                            {{-- Badge Status Transaksi & Status Pembayaran --}}
                            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2">
                                {{-- Badge Status Transaksi --}}
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg 
                                             text-xs font-semibold
                                             @if ($t->status_transaksi == 'antrian')       bg-slate-100  text-slate-700
                                             @elseif ($t->status_transaksi == 'proses')    bg-blue-100   text-blue-700
                                             @elseif ($t->status_transaksi == 'pick_up')   bg-purple-100 text-purple-700
                                             @elseif ($t->status_transaksi == 'siap_di_ambil') bg-teal-100 text-teal-700
                                             @elseif ($t->status_transaksi == 'siap_di_antar') bg-indigo-100 text-indigo-700
                                             @elseif ($t->status_transaksi == 'selesai')   bg-green-100  text-green-700
                                             @else                                          bg-red-100    text-red-700
                                             @endif">
                                    <i class="bi bi-clipboard-check"></i>
                                    {{ ucfirst(str_replace('_', ' ', $t->status_transaksi)) }}
                                </span>

                                {{-- Badge Status Pembayaran --}}
                                @if ($t->status_bayar == 'belum_lunas' || $t->status_bayar == 'belum bayar')
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
                {{-- STATE KOSONG: Tampil jika tidak ada data transaksi pada tab aktif --}}
                <div class="col-span-full flex flex-col items-center justify-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 border border-gray-200">
                        <i class="bi bi-inbox text-5xl text-gray-400"></i>
                    </div>
                    <p class="text-xl text-gray-600 font-semibold">Tidak ada transaksi</p>
                    <p class="text-gray-400 text-sm mt-2">Transaksi akan muncul di sini</p>
                </div>
            @endforelse
        </div>

        {{-- ========================================
             PAGINATION
             Navigasi halaman untuk daftar transaksi.
             Hanya tampil jika data lebih dari 10 (diatur di controller).
             Tab aktif diteruskan agar tab tidak berubah saat pindah halaman.
        ======================================== --}}
        @if($riwayat->hasPages())
        <div class="mt-8 flex justify-center">
            <nav class="inline-flex items-center gap-1 bg-white rounded-2xl shadow-sm border border-gray-200 p-2">

                {{-- Tombol Halaman Sebelumnya --}}
                @if($riwayat->onFirstPage())
                    <span class="px-4 py-2 rounded-xl text-gray-400 cursor-not-allowed bg-gray-50 font-semibold text-sm">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $riwayat->previousPageUrl() }}&tab={{ $tab }}"
                       class="px-4 py-2 rounded-xl text-gray-700 hover:bg-yellow-100 font-semibold text-sm transition-all">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @endif

                {{-- Nomor Halaman --}}
                @foreach($riwayat->getUrlRange(1, $riwayat->lastPage()) as $page => $url)
                    @if($page == $riwayat->currentPage())
                        {{-- Halaman aktif --}}
                        <span class="px-4 py-2 rounded-xl bg-yellow-400 text-gray-900 font-bold text-sm shadow-sm">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}&tab={{ $tab }}"
                           class="px-4 py-2 rounded-xl text-gray-700 hover:bg-yellow-100 font-semibold text-sm transition-all">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Tombol Halaman Berikutnya --}}
                @if($riwayat->hasMorePages())
                    <a href="{{ $riwayat->nextPageUrl() }}&tab={{ $tab }}"
                       class="px-4 py-2 rounded-xl text-gray-700 hover:bg-yellow-100 font-semibold text-sm transition-all">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @else
                    <span class="px-4 py-2 rounded-xl text-gray-400 cursor-not-allowed bg-gray-50 font-semibold text-sm">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                @endif
            </nav>
        </div>

        {{-- Info jumlah data yang ditampilkan --}}
        <div class="mt-3 text-center text-sm text-gray-500">
            Menampilkan {{ $riwayat->firstItem() }}–{{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} transaksi
        </div>
        @endif

    </div>
</div>

{{-- ========================================
     HIDDEN FORM DELETE
     Form tersembunyi untuk setiap transaksi,
     digunakan oleh SweetAlert saat konfirmasi hapus.
======================================== --}}
@foreach($riwayat as $t)
<form id="deleteForm{{ $t->id_transaksi }}" 
      action="{{ route('riwayat.destroy', $t->id_transaksi) }}" 
      method="POST" 
      style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach

{{-- SweetAlert2 untuk dialog konfirmasi hapus --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// =============================
// FITUR PENCARIAN REAL-TIME
// Memfilter kartu transaksi berdasarkan nama pelanggan atau ID transaksi
// =============================
document.getElementById('searchInput').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    const items  = document.querySelectorAll('#riwayatList > div');
    
    items.forEach(function (item) {
        const nama = item.getAttribute('data-nama') || '';
        const id   = item.getAttribute('data-id')   || '';
        
        // Tampilkan jika nama atau ID mengandung kata kunci pencarian
        item.style.display = (nama.includes(filter) || id.includes(filter)) ? '' : 'none';
    });
});

// =============================
// HANDLER TOMBOL HAPUS TRANSAKSI
// Menampilkan dialog konfirmasi SweetAlert sebelum menghapus
// =============================
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            // Hentikan event agar tidak meneruskan ke link kartu di belakangnya
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Ambil data dari atribut tombol hapus
            const transaksiId      = this.getAttribute('data-id');
            const namaPelanggan    = this.getAttribute('data-nama');
            const totalHarga       = this.getAttribute('data-total');
            const tanggalTransaksi = this.getAttribute('data-tanggal');
            
            // Tampilkan dialog konfirmasi dengan detail transaksi
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
                cancelButtonColor:  '#6b7280',
                confirmButtonText:  '<i class="bi bi-trash-fill me-2"></i>Ya, Hapus Transaksi!',
                cancelButtonText:   '<i class="bi bi-x-circle me-2"></i>Batal',
                reverseButtons: true,
                width: '600px',
                customClass: {
                    popup:         'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:shadow-xl',
                    cancelButton:  'rounded-xl px-6 py-3 font-bold'
                }
            }).then(function (result) {
                if (result.isConfirmed) {
                    // Tampilkan loading saat proses hapus berlangsung
                    Swal.fire({
                        title: 'Menghapus Transaksi...',
                        html: '<div class="flex flex-col items-center"><i class="bi bi-hourglass-split text-4xl text-yellow-500 animate-pulse mb-2"></i><p class="text-gray-600">Mohon tunggu sebentar</p></div>',
                        allowOutsideClick: false,
                        allowEscapeKey:    false,
                        showConfirmButton:  false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    // Submit hidden form untuk menghapus via DELETE method
                    document.getElementById('deleteForm' + transaksiId).submit();
                }
            });
            
            return false;
        }, true);
    });
});
</script>

@endsection