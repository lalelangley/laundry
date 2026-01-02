@extends('layouts.master')
@section('title', 'Laporan Kasir')
@section('content')
<div class="min-h-screen bg-gray-100 pb-24">
    
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <a href="{{ route('laporan.index') }}" class="text-2xl font-bold">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Kasir</h1>
        </div>
        <div class="flex items-center gap-4 text-sm font-semibold">
            <span onclick="window.print()">Export</span>
            <span>Sort</span>
        </div>
    </div>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('laporan.kasir.index') }}">
        <div class="px-5 mt-5 flex items-center gap-3">
            <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3">
                <p class="text-xs">Tanggal Awal</p>
                <input type="date" 
                       name="dari" 
                       value="{{ $tglAwal }}"
                       class="bg-transparent font-bold w-full border-none outline-none"
                       onchange="this.form.submit()">
            </div>
            <span class="font-bold text-xl">&gt;</span>
            <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3">
                <p class="text-xs">Tanggal Akhir</p>
                <input type="date" 
                       name="sampai" 
                       value="{{ $tglAkhir }}"
                       class="bg-transparent font-bold w-full border-none outline-none"
                       onchange="this.form.submit()">
            </div>
        </div>
    </form>

    {{-- LIST KASIR --}}
    <div class="mt-6 px-5 space-y-3">
        @forelse($data as $item)
        <div class="bg-white rounded-xl p-4 shadow-sm">
            
            {{-- NAMA KASIR & ICON --}}
            <div class="flex items-center gap-2 mb-3">
                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-geo-alt-fill text-white text-lg"></i>
                </div>
                <h2 class="text-xl font-bold">{{ strtolower($item->nama_kasir) }}</h2>
            </div>

            {{-- STATUS BREAKDOWN - 5 BOXES (COMPACT) --}}
            <div class="grid grid-cols-5 gap-1.5 mb-3">
                {{-- Antrian --}}
                <div class="border-2 border-yellow-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-medium text-gray-600">Antrian</p>
                    <p class="text-lg font-bold">{{ $item->antrian ?? 0 }}</p>
                </div>

                {{-- Proses --}}
                <div class="border-2 border-yellow-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-medium text-gray-600">Proses</p>
                    <p class="text-lg font-bold">{{ $item->proses ?? 0 }}</p>
                </div>

                {{-- Siap Ambil --}}
                <div class="border-2 border-yellow-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-medium text-gray-600">Siap Ambil</p>
                    <p class="text-lg font-bold">{{ $item->siap_ambil ?? 0 }}</p>
                </div>

                {{-- Selesai --}}
                <div class="border-2 border-yellow-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-medium text-gray-600">Selesai</p>
                    <p class="text-lg font-bold">{{ $item->selesai ?? 0 }}</p>
                </div>

                {{-- Batal --}}
                <div class="border-2 border-yellow-400 rounded-lg p-2 text-center">
                    <p class="text-[10px] font-medium text-gray-600">Batal</p>
                    <p class="text-lg font-bold">{{ $item->batal ?? 0 }}</p>
                </div>
            </div>

            {{-- TOTAL PENDAPATAN --}}
            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                <span class="text-sm font-bold">Total</span>
                <span class="text-lg font-bold">Rp. {{ number_format($item->total_pendapatan ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl p-8 text-center shadow-sm">
            <i class="bi bi-inbox text-6xl text-gray-300"></i>
            <p class="text-gray-500 mt-3">Tidak ada data pada periode ini</p>
        </div>
        @endforelse
    </div>
</div>

{{-- PRINT STYLES --}}
<style>
@media print {
    .sticky, button, a[href*="route"] {
        display: none !important;
    }
    .bg-gray-100 {
        background: white !important;
    }
    .shadow-sm {
        box-shadow: none !important;
    }
}
</style>

@endsection