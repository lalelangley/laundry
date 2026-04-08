{{-- FE-DOC: Template frontend untuk resources/views/laporan/kasir/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Kasir')
@section('content')
<div class="min-h-screen bg-gray-100 pb-24">
    
    {{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
    <div class="bg-yellow-400 px-5 py-4 rounded-b-3xl sticky top-0 z-20 shadow">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('laporan.index') }}" class="text-2xl font-bold hover:scale-110 transition-transform">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Kasir</h1>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('laporan.kasir.export', ['dari' => $tglAwal, 'sampai' => $tglAkhir, 'format' => 'pdf']) }}"
               class="flex items-center gap-2 font-semibold bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-full transition-all text-sm">
                <i class="bi bi-file-earmark-pdf"></i>
                PDF
            </a>
            <a href="{{ route('laporan.kasir.export') }}?dari={{ $tglAwal }}&sampai={{ $tglAkhir }}" 
               class="flex items-center gap-2 font-semibold bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-full transition-all text-sm">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>
        </div>
        </div>
    </div>

    {{-- FILTER --}}
{{-- FE-DOC: Area filter merangkum input tanggal, pencarian, sorting, dan reset agar user bisa mempersempit data. --}}
    <form method="GET" action="{{ route('laporan.kasir.index') }}" id="filterForm">
        <div class="px-5 mt-5 flex flex-col gap-3 xl:flex-row xl:items-center">
            <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 min-w-0">
                <p class="text-xs font-semibold">Tanggal Awal</p>
                <input type="date" 
                       name="dari" 
                       id="dari"
                       value="{{ $tglAwal }}"
                       class="bg-transparent font-bold w-full border-none outline-none cursor-pointer">
            </div>
            <span class="font-bold text-xl text-gray-700 hidden xl:block">s/d</span>
            <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 min-w-0">
                <p class="text-xs font-semibold">Tanggal Akhir</p>
                <input type="date" 
                       name="sampai"
                       id="sampai" 
                       value="{{ $tglAkhir }}"
                       class="bg-transparent font-bold w-full border-none outline-none cursor-pointer">
            </div>
            <div class="flex flex-wrap gap-3">
            <a href="{{ route('laporan.kasir.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 px-4 py-3 rounded-full transition-all"
               title="Reset Filter">
                <i class="bi bi-arrow-clockwise font-bold"></i>
            </a>
            <div class="relative w-full sm:w-auto">
                <select name="sort" onchange="this.form.submit()" class="bg-white rounded-full px-4 py-3 pr-12 text-sm font-semibold outline-none shadow w-full sm:w-auto appearance-none">
                    <option value="pendapatan_tertinggi" {{ request('sort', 'pendapatan_tertinggi') === 'pendapatan_tertinggi' ? 'selected' : '' }}>Pendapatan Tertinggi</option>
                    <option value="pendapatan_terendah" {{ request('sort') === 'pendapatan_terendah' ? 'selected' : '' }}>Pendapatan Terendah</option>
                    <option value="transaksi_terbanyak" {{ request('sort') === 'transaksi_terbanyak' ? 'selected' : '' }}>Transaksi Terbanyak</option>
                    <option value="nama_az" {{ request('sort') === 'nama_az' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="nama_za" {{ request('sort') === 'nama_za' ? 'selected' : '' }}>Nama Z-A</option>
                </select>
                <i class="bi bi-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
            </div>
            </div>
        </div>
    </form>

    {{-- SUMMARY STATS --}}
    <div class="px-5 mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white rounded-xl p-4 shadow border-2 border-yellow-400 text-center">
            <div class="text-xs text-gray-500 font-semibold">Total Kasir</div>
            <div class="text-2xl font-bold text-gray-800">{{ $summary['total_kasir'] }}</div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow border-2 border-orange-400 text-center">
            <div class="text-xs text-gray-500 font-semibold">Total Transaksi</div>
            <div class="text-2xl font-bold text-orange-600">{{ $summary['total_transaksi'] }}</div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow border-2 border-green-400 text-center">
            <div class="text-xs text-gray-500 font-semibold">Total Pendapatan</div>
            <div class="text-sm font-bold text-green-600">
                Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- TOP KASIR --}}
    @php $top = $topKasir ?? $data->first(); @endphp
    @if($top)
    <div class="mx-5 mt-5 bg-gradient-to-r from-yellow-100 to-orange-100 border-2 border-yellow-400 rounded-xl p-5 shadow-md">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-yellow-400 flex items-center justify-center border-4 border-white shadow">
                    @if($top->gambar ?? false)
                        <img src="{{ asset('storage/'.$top->gambar) }}" class="w-full h-full object-cover" alt="{{ $top->nama_kasir }}">
                    @else
                        <i class="bi bi-person text-white text-2xl"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="bi bi-trophy-fill text-yellow-500"></i>
                        <span class="text-xs font-bold text-gray-600 uppercase">Top Kasir</span>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $top->nama_kasir }}</p>
                    <p class="text-sm font-semibold text-gray-600">{{ $top->no_hp }}</p>
                </div>
            </div>
            <div class="text-left md:text-right">
                <div class="text-xl md:text-2xl font-bold text-orange-600 break-words">
                    Rp {{ number_format($top->total_pendapatan, 0, ',', '.') }}
                </div>
                <div class="text-sm font-semibold text-gray-600 mt-1">
                    {{ $top->total_transaksi }} Transaksi
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- LIST KASIR --}}
    <div class="mt-6 px-5 space-y-3 pb-6">
        @forelse($data as $index => $item)
        <div class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all border border-gray-200">
            
            {{-- NAMA KASIR & RANKING --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    {{-- Ranking Badge --}}
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                        {{ $index == 0 ? 'bg-yellow-400 text-white' : '' }}
                        {{ $index == 1 ? 'bg-gray-300 text-white' : '' }}
                        {{ $index == 2 ? 'bg-orange-300 text-white' : '' }}
                        {{ $index > 2 ? 'bg-gray-100 text-gray-600' : '' }}">
                        {{ ($data->firstItem() ?? 1) + $index }}
                    </div>
                    
                    {{-- Avatar & Name --}}
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center flex-shrink-0">
                        @if($item->gambar ?? false)
                            <img src="{{ asset('storage/'.$item->gambar) }}" 
                                 class="w-full h-full object-cover" 
                                 alt="{{ $item->nama_kasir }}"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        @endif
                        <i class="bi bi-person text-white text-lg {{ ($item->gambar ?? false) ? 'hidden' : '' }}"></i>
                    </div>
                    
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-gray-800 break-words">{{ $item->nama_kasir }}</h2>
                        <p class="text-xs text-gray-500 font-semibold break-all">{{ $item->no_hp }}</p>
                    </div>
                </div>
                
                {{-- Total Transaksi Badge --}}
                <div class="text-center">
                    <div class="text-xs text-gray-500 font-semibold">Total</div>
                    <div class="text-xl font-bold text-orange-600">{{ $item->total_transaksi ?? 0 }}</div>
                </div>
            </div>

            {{-- STATUS BREAKDOWN - 5 BOXES --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 mb-3">
                {{-- Antrian --}}
                <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-bold text-gray-600">Antrian</p>
                    <p class="text-lg font-bold text-gray-700">{{ $item->antrian ?? 0 }}</p>
                </div>

                {{-- Proses --}}
                <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-bold text-yellow-700">Proses</p>
                    <p class="text-lg font-bold text-yellow-700">{{ $item->proses ?? 0 }}</p>
                </div>

                {{-- Siap Ambil --}}
                <div class="bg-orange-50 border-2 border-orange-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-bold text-orange-700">Siap Ambil</p>
                    <p class="text-lg font-bold text-orange-700">{{ $item->siap_ambil ?? 0 }}</p>
                </div>

                {{-- Selesai --}}
                <div class="bg-green-50 border-2 border-green-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-bold text-green-700">Selesai</p>
                    <p class="text-lg font-bold text-green-700">{{ $item->selesai ?? 0 }}</p>
                </div>

                {{-- Batal --}}
                <div class="bg-red-50 border-2 border-red-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-bold text-red-700">Batal</p>
                    <p class="text-lg font-bold text-red-700">{{ $item->batal ?? 0 }}</p>
                </div>
            </div>

            {{-- TOTAL PENDAPATAN --}}
            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center pt-3 border-t-2 border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50 -m-4 mt-0 p-4 rounded-b-xl">
                <span class="text-sm font-bold text-gray-700">Total Pendapatan</span>
                <span class="text-xl font-bold text-orange-600 break-words">
                    Rp {{ number_format($item->total_pendapatan ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl p-12 text-center shadow-sm">
            <i class="bi bi-inbox text-6xl text-gray-300 mb-3 block"></i>
            <p class="text-gray-500 font-semibold text-lg">Tidak ada data pada periode ini</p>
            <p class="text-gray-400 text-sm mt-1">Coba ubah rentang tanggal</p>
        </div>
        @endforelse
    </div>

    @if(method_exists($data, 'links'))
    <div class="px-5 pb-6">
        {{ $data->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const dariInput = document.getElementById('dari');
    const sampaiInput = document.getElementById('sampai');

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
