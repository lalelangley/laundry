{{-- FE-DOC: Template frontend untuk resources/views/kasir/laporan/transaksi/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Laporan Transaksi')

@section('content')
<div class="min-h-screen bg-gray-100">

    {{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
    <div class="bg-yellow-400 px-5 py-5 md:px-8 md:py-6 rounded-b-3xl shadow">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 md:gap-4 min-w-0">
                <a href="{{ route('kasir.laporan.index') }}" class="text-2xl md:text-3xl font-bold hover:scale-110 transition-transform">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="text-xl md:text-2xl font-bold">Laporan Transaksi</h1>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:inline-flex rounded-full bg-white/40 px-4 py-2 text-sm font-semibold text-gray-800">
                    View only
                </div>
                <div class="flex gap-5 text-xl">
                    <button type="button" id="toggleFilter" class="hover:scale-110 transition-transform">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <button type="button" id="toggleSort" class="hover:scale-110 transition-transform">
                        <i class="bi bi-sort-down"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM FILTER --}}
    <form method="GET" action="{{ route('kasir.laporan.transaksi.index') }}" id="filterForm" class="px-5 md:px-8 mt-6 space-y-4">

        {{-- FILTER TANGGAL --}}
{{-- FE-DOC: Dua input tanggal biasanya menjadi filter utama untuk semua data laporan per periode. --}}
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
            <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-6 py-4 flex items-center gap-3 font-semibold min-w-0">
                <i class="bi bi-calendar-event"></i>
                <input type="date" 
                       name="dari" 
                       id="dari"
                       value="{{ request('dari', $tglAwal) }}"
                       class="bg-transparent outline-none w-full font-semibold cursor-pointer">
            </div>

            <span class="font-bold text-gray-700 hidden xl:block">s/d</span>

            <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-6 py-4 flex items-center gap-3 font-semibold min-w-0">
                <i class="bi bi-calendar-event"></i>
                <input type="date" 
                       name="sampai" 
                       id="sampai"
                       value="{{ request('sampai', $tglAkhir) }}"
                       class="bg-transparent outline-none w-full font-semibold cursor-pointer">
            </div>

            <div class="flex flex-wrap gap-3">
            <button type="button" 
                    id="resetDate"
                    class="bg-gray-200 hover:bg-gray-300 px-4 py-4 rounded-full transition-all"
                    title="Reset Tanggal">
                <i class="bi bi-arrow-clockwise font-bold"></i>
            </button>
            </div>
        </div>

        {{-- ADVANCED FILTERS (Collapsible) --}}
{{-- FE-DOC: Filter lanjutan disembunyikan default agar halaman tetap ringkas, lalu dibuka saat diperlukan. --}}
        <div id="advancedFilters" class="space-y-4 hidden">
            
            {{-- Status Transaksi --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <label class="block text-sm font-bold text-gray-700 mb-3">Status Transaksi</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status[]" value="antrian" 
                               {{ in_array('antrian', request('status', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded">
                        <span class="text-sm font-semibold">Antrian</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status[]" value="proses" 
                               {{ in_array('proses', request('status', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded">
                        <span class="text-sm font-semibold">Proses</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status[]" value="siap_di_ambil" 
                               {{ in_array('siap_di_ambil', request('status', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded">
                        <span class="text-sm font-semibold">Siap Diambil</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status[]" value="siap_di_antar" 
                               {{ in_array('siap_di_antar', request('status', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded">
                        <span class="text-sm font-semibold">Siap Diantar</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status[]" value="selesai" 
                               {{ in_array('selesai', request('status', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded">
                        <span class="text-sm font-semibold">Selesai</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status[]" value="batal" 
                               {{ in_array('batal', request('status', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded">
                        <span class="text-sm font-semibold">Batal</span>
                    </label>
                </div>
            </div>

            {{-- Status Pembayaran --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <label class="block text-sm font-bold text-gray-700 mb-3">Status Pembayaran</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="bayar[]" value="lunas" 
                               {{ in_array('lunas', request('bayar', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-orange-500 rounded">
                        <span class="text-sm font-semibold">Lunas</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="bayar[]" value="DP" 
                               {{ in_array('DP', request('bayar', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-orange-500 rounded">
                        <span class="text-sm font-semibold">DP</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="bayar[]" value="belum_lunas" 
                               {{ in_array('belum_lunas', request('bayar', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-orange-500 rounded">
                        <span class="text-sm font-semibold">Belum Lunas</span>
                    </label>
                </div>
            </div>

            {{-- Jenis Transaksi --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <label class="block text-sm font-bold text-gray-700 mb-3">Jenis Transaksi</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="jenis[]" value="online" 
                               {{ in_array('online', request('jenis', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded">
                        <span class="text-sm font-semibold">Online</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="jenis[]" value="offline" 
                               {{ in_array('offline', request('jenis', [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-500 rounded">
                        <span class="text-sm font-semibold">Offline</span>
                    </label>
                </div>
            </div>

        </div>

        {{-- SORT OPTIONS (Collapsible) --}}
{{-- FE-DOC: Opsi sorting dipisah ke panel sendiri supaya user bisa mengganti urutan data tanpa memenuhi area utama. --}}
        <div id="sortOptions" class="bg-white rounded-2xl shadow p-6 hidden">
            <label class="block text-sm font-bold text-gray-700 mb-3">Urutkan Berdasarkan</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="sort" value="terbaru" 
                           {{ request('sort', 'terbaru') == 'terbaru' ? 'checked' : '' }}
                           class="w-4 h-4 text-yellow-500">
                    <span class="text-sm font-semibold">Terbaru</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="sort" value="terlama" 
                           {{ request('sort') == 'terlama' ? 'checked' : '' }}
                           class="w-4 h-4 text-yellow-500">
                    <span class="text-sm font-semibold">Terlama</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="sort" value="nominal_tertinggi" 
                           {{ request('sort') == 'nominal_tertinggi' ? 'checked' : '' }}
                           class="w-4 h-4 text-yellow-500">
                    <span class="text-sm font-semibold">Nominal Tertinggi</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="sort" value="nominal_terendah" 
                           {{ request('sort') == 'nominal_terendah' ? 'checked' : '' }}
                           class="w-4 h-4 text-yellow-500">
                    <span class="text-sm font-semibold">Nominal Terendah</span>
                </label>
            </div>
        </div>

        {{-- SEARCH --}}
{{-- FE-DOC: Input pencarian ini membantu user menemukan data spesifik berdasarkan kata kunci. --}}
        <div class="bg-white rounded-3xl sm:rounded-full shadow flex flex-col sm:flex-row items-stretch sm:items-center px-6 py-4 gap-4">
            <i class="bi bi-search text-xl text-gray-400"></i>

            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama pelanggan / no nota / no HP..."
                class="flex-1 outline-none bg-transparent font-semibold text-gray-700"
            >

            <button type="submit"
                    class="bg-yellow-400 hover:bg-yellow-500 px-6 py-2 rounded-full font-bold transition-all w-full sm:w-auto">
                Cari
            </button>

            @if(request()->hasAny(['q', 'dari', 'sampai', 'status', 'bayar', 'jenis', 'sort']))
                <a href="{{ route('kasir.laporan.transaksi.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-full font-bold transition-all text-center w-full sm:w-auto">
                    Reset
                </a>
            @endif
        </div>

    </form>

    {{-- SUMMARY --}}
{{-- FE-DOC: Summary section menampilkan total atau agregasi hasil filter aktif. --}}
    <div class="px-5 md:px-8 mt-6">
        <div class="bg-white border-2 border-yellow-400 rounded-2xl p-6 font-semibold">
            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between text-base md:text-lg">
                <span>Total Omzet</span>
                <span class="text-orange-600 font-bold break-words">Rp {{ number_format($totalOmzet,0,',','.') }}</span>
            </div>
            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between text-base md:text-lg mt-2">
                <span>Jumlah Transaksi</span>
                <span class="text-yellow-600 font-bold">{{ $jumlah }}</span>
            </div>
        </div>
    </div>

    {{-- LIST TRANSAKSI --}}
{{-- FE-DOC: List transaksi memakai card layout supaya detail status, pembayaran, dan nominal lebih mudah discan. --}}
    <div class="px-5 md:px-8 mt-6 space-y-4 pb-10">

        @forelse ($transaksi as $t)
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all p-5 md:p-6 flex flex-col sm:flex-row gap-4 md:gap-6 border border-gray-200">

            {{-- AVATAR --}}
            <div class="w-16 h-16 rounded-full overflow-hidden bg-gradient-to-br from-yellow-400 to-orange-500 flex-shrink-0 flex items-center justify-center shadow-md">
                @if($t->pelanggan && !empty($t->pelanggan->gambar))
                    @php
                        $gambar = $t->pelanggan->gambar;
                        $paths = [
                            $gambar,
                            'pelanggan/' . $gambar,
                            'gambar_pelanggan/' . $gambar,
                            'images/pelanggan/' . $gambar,
                            ltrim($gambar, '/'),
                            str_replace('public/', '', $gambar),
                        ];
                        
                        $imageSrc = '';
                        foreach ($paths as $testPath) {
                            $fullPath = public_path('storage/' . $testPath);
                            if (file_exists($fullPath) && is_file($fullPath)) {
                                $imageSrc = asset('storage/' . $testPath);
                                break;
                            }
                        }
                    @endphp

                    @if($imageSrc)
                        <img 
                            class="w-full h-full object-cover"
                            src="{{ $imageSrc }}"
                            alt="{{ $t->nama_pelanggan }}"
                            loading="lazy"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                    @endif
                    <i class="bi bi-person text-3xl text-white {{ $imageSrc ? 'hidden' : '' }}"></i>
                @else
                    <i class="bi bi-person text-3xl text-white"></i>
                @endif
            </div>

            {{-- INFO --}}
            <div class="flex-1">
                <div class="flex flex-col gap-3 lg:flex-row lg:justify-between lg:items-start">
                    <div class="min-w-0">
                        <h3 class="text-lg md:text-xl font-bold text-gray-800 break-words">{{ $t->nama_pelanggan }}</h3>
                        
                        <div class="flex flex-wrap gap-2 mt-2">
                            @if($t->jenis_transaksi === 'online')
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">
                                    <i class="bi bi-globe"></i>
                                    Online
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                                    <i class="bi bi-shop"></i>
                                    Offline
                                </span>
                            @endif

                            {{-- Status Transaksi --}}
                            @php
                                $statusColors = [
                                    'antrian' => 'bg-gray-100 text-gray-700',
                                    'proses' => 'bg-yellow-100 text-yellow-700',
                                    'siap_di_ambil' => 'bg-orange-100 text-orange-700',
                                    'siap_di_antar' => 'bg-orange-100 text-orange-700',
                                    'selesai' => 'bg-green-100 text-green-700',
                                    'batal' => 'bg-red-100 text-red-700',
                                ];
                                $statusColor = $statusColors[$t->status_transaksi] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-flex items-center gap-1 px-3 py-1 {{ $statusColor }} text-xs font-semibold rounded-full capitalize">
                                {{ str_replace('_', ' ', $t->status_transaksi) }}
                            </span>

                            {{-- Status Pembayaran --}}
                            @php
                                $bayarColors = [
                                    'lunas' => 'bg-orange-100 text-orange-700',
                                    'DP' => 'bg-yellow-100 text-yellow-700',
                                    'belum_lunas' => 'bg-red-100 text-red-700',
                                ];
                                $bayarColor = $bayarColors[$t->status_bayar] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            @if($t->status_bayar)
                                <span class="inline-flex items-center gap-1 px-3 py-1 {{ $bayarColor }} text-xs font-semibold rounded-full capitalize">
                                    {{ $t->status_bayar === 'belum_lunas' ? 'Belum Lunas' : $t->status_bayar }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-left lg:text-right">
                        <span class="text-xl md:text-2xl font-bold text-orange-600 break-words">
                            Rp {{ number_format($t->total_bayar ?? 0, 0, ',', '.') }}
                        </span>
                        @if($t->total_harga && $t->total_harga != $t->total_bayar)
                            <div class="text-sm text-gray-500 line-through mt-1">
                                Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-hash text-yellow-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">No Nota</div>
                            <div class="font-semibold text-gray-800">TRX/{{ $t->id_transaksi }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-calendar-check text-yellow-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Tanggal Masuk</div>
                            <div class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>

                    @if($t->tgl_estimasi)
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-clock-history text-orange-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Estimasi Selesai</div>
                            <div class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($t->tgl_estimasi)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($t->tgl_lunas)
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-check-circle-fill text-orange-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Tanggal Lunas</div>
                            <div class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($t->tgl_lunas)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($t->diskon && $t->diskon > 0)
                    <div class="flex items-center gap-2 p-3 bg-red-50 rounded-lg">
                        <i class="bi bi-percent text-red-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Diskon</div>
                            <div class="font-semibold text-red-600">
                                @if($t->tipe_diskon === 'percent')
                                    {{ $t->diskon }}% (Rp {{ number_format($t->total_harga * $t->diskon / 100, 0, ',', '.') }})
                                @else
                                    Rp {{ number_format($t->diskon, 0, ',', '.') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($t->dp && $t->dp > 0)
                    <div class="flex items-center gap-2 p-3 bg-orange-50 rounded-lg">
                        <i class="bi bi-wallet2 text-orange-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">DP Terbayar</div>
                            <div class="font-semibold text-orange-600">
                                Rp {{ number_format($t->dp, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($t->keterangan)
                    <div class="flex items-center gap-2 p-3 bg-yellow-50 rounded-lg md:col-span-2">
                        <i class="bi bi-chat-left-text text-yellow-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Keterangan</div>
                            <div class="font-semibold text-gray-800">{{ $t->keterangan }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
            <div class="text-center text-gray-500 py-20 bg-white rounded-2xl">
                <i class="bi bi-inbox text-6xl mb-3 block text-gray-300"></i>
                <p class="font-semibold text-lg">Tidak ada transaksi</p>
                <p class="text-sm mt-1">Coba ubah filter atau rentang tanggal</p>
            </div>
        @endforelse

    </div>

    {{-- PAGINATION --}}
{{-- FE-DOC: Pagination menjaga jumlah data per halaman tetap nyaman dibaca dan performa tetap ringan. --}}
    @if($transaksi->hasPages())
        <div class="px-5 md:px-8 pb-10">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-sm text-gray-600">
                        Menampilkan {{ $transaksi->firstItem() ?? 0 }} - {{ $transaksi->lastItem() ?? 0 }} dari {{ $transaksi->total() }} transaksi
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        {{-- Previous Button --}}
                        @if ($transaksi->onFirstPage())
                            <span class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg font-semibold cursor-not-allowed">
                                <i class="bi bi-chevron-left"></i> Prev
                            </span>
                        @else
                            <a href="{{ $transaksi->previousPageUrl() }}" 
                               class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 rounded-lg font-semibold transition-all">
                                <i class="bi bi-chevron-left"></i> Prev
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($transaksi->getUrlRange(1, $transaksi->lastPage()) as $page => $url)
                            @if ($page == $transaksi->currentPage())
                                <span class="px-4 py-2 bg-orange-500 text-white rounded-lg font-semibold">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" 
                                   class="px-4 py-2 bg-gray-100 hover:bg-yellow-400 text-gray-700 hover:text-gray-900 rounded-lg font-semibold transition-all">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if ($transaksi->hasMorePages())
                            <a href="{{ $transaksi->nextPageUrl() }}" 
                               class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 rounded-lg font-semibold transition-all">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <span class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg font-semibold cursor-not-allowed">
                                Next <i class="bi bi-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const toggleFilter = document.getElementById('toggleFilter');
    const toggleSort = document.getElementById('toggleSort');
    const advancedFilters = document.getElementById('advancedFilters');
    const sortOptions = document.getElementById('sortOptions');
    const resetDate = document.getElementById('resetDate');
    const dariInput = document.getElementById('dari');
    const sampaiInput = document.getElementById('sampai');

    // Toggle Advanced Filters
    toggleFilter.addEventListener('click', function() {
        advancedFilters.classList.toggle('hidden');
        sortOptions.classList.add('hidden');
        this.classList.toggle('text-yellow-600');
    });

    // Toggle Sort Options
    toggleSort.addEventListener('click', function() {
        sortOptions.classList.toggle('hidden');
        advancedFilters.classList.add('hidden');
        this.classList.toggle('text-yellow-600');
    });

    // Auto submit when date changes
    dariInput.addEventListener('change', function() {
        if (this.value && sampaiInput.value) {
            filterForm.submit();
        }
    });

    sampaiInput.addEventListener('change', function() {
        if (this.value && dariInput.value) {
            filterForm.submit();
        }
    });

    // Reset date to default (30 days back)
    resetDate.addEventListener('click', function() {
        const today = new Date();
        const thirtyDaysAgo = new Date(today);
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        dariInput.value = thirtyDaysAgo.toISOString().split('T')[0];
        sampaiInput.value = today.toISOString().split('T')[0];
        
        filterForm.submit();
    });

    // Auto submit when filter checkboxes change
    const filterCheckboxes = document.querySelectorAll('input[type="checkbox"]');
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            filterForm.submit();
        });
    });

    // Auto submit when sort radio changes
    const sortRadios = document.querySelectorAll('input[name="sort"]');
    sortRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            filterForm.submit();
        });
    });

    // Validate date range
    dariInput.addEventListener('change', function() {
        if (sampaiInput.value && this.value > sampaiInput.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            this.value = '';
        }
    });

    sampaiInput.addEventListener('change', function() {
        if (dariInput.value && this.value < dariInput.value) {
            alert('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
            this.value = '';
        }
    });
});
</script>
@endpush
