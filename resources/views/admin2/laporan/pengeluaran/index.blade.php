@extends('layouts.master')
@section('title', 'Laporan Pengeluaran')
@section('content')
<div class="min-h-screen bg-gray-100 pb-28">

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center justify-between sticky top-0 z-20 shadow">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin2.laporan.index') }}" class="text-2xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-lg font-bold">Laporan Pengeluaran</h1>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="{{ route('admin2.laporan.pengeluaran.export') }}?{{ http_build_query(array_merge(request()->all(), ['format' => 'pdf'])) }}"
           class="flex items-center gap-2 font-semibold bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-full transition-all">
            <i class="bi bi-file-earmark-pdf"></i>
            PDF
        </a>
        <a href="{{ route('admin2.laporan.pengeluaran.export') }}?{{ http_build_query(request()->all()) }}" 
           class="flex items-center gap-2 font-semibold bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-full transition-all">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            Excel
        </a>
    </div>
</div>

{{-- FILTER --}}
<form method="GET" id="filterForm" class="px-6 mt-6 space-y-4">
    <div class="flex items-center gap-3">
        <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3 flex items-center gap-2 font-semibold">
            <i class="bi bi-calendar-event"></i>
            <input type="date" 
                   name="dari" 
                   id="dari"
                   value="{{ request('dari') }}"
                   class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <span class="font-bold text-gray-700">s/d</span>
        <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3 flex items-center gap-2 font-semibold">
            <i class="bi bi-calendar-event"></i>
            <input type="date" 
                   name="sampai"
                   id="sampai"
                   value="{{ request('sampai') }}"
                   class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <button type="button" 
                id="resetBtn"
                class="bg-gray-200 hover:bg-gray-300 px-4 py-3 rounded-full transition-all"
                title="Reset Filter">
            <i class="bi bi-arrow-clockwise font-bold"></i>
        </button>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-full shadow flex items-center px-4 py-3 gap-3">
        <i class="bi bi-search text-xl text-gray-400"></i>
        <input type="text" 
               name="q" 
               value="{{ request('q') }}"
               placeholder="Cari nama pengeluaran..."
               class="flex-1 outline-none bg-transparent font-semibold text-gray-700">
        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 px-5 py-2 rounded-full font-bold transition-all">
            Cari
        </button>
        @if(request()->hasAny(['q', 'dari', 'sampai']))
            <a href="{{ route('admin2.laporan.pengeluaran.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 px-5 py-2 rounded-full font-bold transition-all">
                Reset
            </a>
        @endif
    </div>
</form>

{{-- SUMMARY CARDS --}}
<div class="px-6 mt-6 grid grid-cols-2 gap-4">
    <div class="bg-white rounded-xl p-4 shadow border-2 border-yellow-400">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="bi bi-receipt text-yellow-600 text-xl"></i>
            </div>
            <div>
                <div class="text-xs text-gray-500 font-semibold">Total Item</div>
                <div class="text-xl font-bold text-gray-800">{{ $totalItem }}</div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl p-4 shadow border-2 border-orange-400">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="bi bi-wallet2 text-orange-600 text-xl"></i>
            </div>
            <div>
                <div class="text-xs text-gray-500 font-semibold">Total Pengeluaran</div>
                <div class="text-lg font-bold text-orange-600">
                    Rp {{ number_format($totalNominal,0,',','.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="px-6 mt-6">
    <div class="bg-yellow-400 rounded-xl overflow-hidden shadow">
        <div class="grid grid-cols-3 text-center font-bold text-white py-3 border-b border-yellow-500">
            <div class="flex items-center justify-center gap-2">
                <i class="bi bi-calendar-event"></i>
                Tanggal
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="bi bi-tag"></i>
                Nama Pengeluaran
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="bi bi-cash"></i>
                Nominal
            </div>
        </div>

        @forelse ($pengeluaran as $p)
            <div class="grid grid-cols-3 bg-white text-center py-4 border-b hover:bg-gray-50 transition-all">
                <div class="text-gray-700 font-semibold">
                    {{ \Carbon\Carbon::parse($p->tanggal_pengeluaran)->format('d/m/Y') }}
                </div>
                <div class="font-semibold text-gray-800 px-2">
                    {{ $p->nama_pengeluaran }}
                </div>
                <div class="font-bold text-orange-600">
                    Rp {{ number_format($p->nominal,0,',','.') }}
                </div>
            </div>
        @empty
            <div class="bg-white text-center py-20 text-gray-400">
                <i class="bi bi-inbox text-6xl mb-3 block text-gray-300"></i>
                <p class="font-semibold text-lg">Tidak ada data pengeluaran</p>
                <p class="text-sm mt-1">Coba ubah filter atau rentang tanggal</p>
            </div>
        @endforelse
    </div>
</div>

@if(method_exists($pengeluaran, 'links'))
<div class="px-6 mt-4 pb-32">
    {{ $pengeluaran->links() }}
</div>
@endif

{{-- TOTAL FOOTER --}}
<div class="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-yellow-400 to-orange-400 px-6 py-5 flex justify-between items-center font-bold text-lg shadow-2xl">
    <div class="flex items-center gap-2">
        <i class="bi bi-wallet2"></i>
        <span>Total Pengeluaran</span>
    </div>
    <span class="text-xl">
        Rp {{ number_format($totalNominal,0,',','.') }}
    </span>
</div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const resetBtn = document.getElementById('resetBtn');
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

    // Reset filter
    resetBtn.addEventListener('click', function() {
        window.location.href = '{{ route("admin2.laporan.pengeluaran.index") }}';
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
