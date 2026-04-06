@extends('layouts.master')
@section('title', 'Laporan Satuan')
@section('content')
<div class="min-h-screen bg-gray-100 pb-24">
{{-- HEADER --}}
    <div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <a href="{{ route('kasir.laporan.index') }}" class="text-2xl font-bold">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Satuan</h1>
        </div>
    </div>

{{-- FILTER TANGGAL --}}
    <form method="GET" class="px-5 mt-5 flex items-center gap-3">
        <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3">
            <p class="text-xs">Tanggal Awal</p>
            <input type="date" name="dari" value="{{ $tglAwal }}"
                   class="bg-transparent outline-none font-bold w-full">
        </div>
        <span class="font-bold">&gt;</span>
        <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3">
            <p class="text-xs">Tanggal Akhir</p>
            <input type="date" name="sampai" value="{{ $tglAkhir }}"
                   class="bg-transparent outline-none font-bold w-full">
        </div>
    </form>

{{-- SUMMARY CARD --}}
    <div class="px-5 mt-5">
        <div class="bg-white rounded-xl p-4 shadow">
            <p class="text-sm text-gray-600">Total Qty Keseluruhan</p>
            <p class="text-2xl font-bold text-yellow-500">{{ number_format($data->sum('total_qty')) }}</p>
        </div>
    </div>

{{-- LIST SATUAN --}}
    <div class="mt-6 space-y-4 px-5">
        @forelse ($data as $item)
        <div class="bg-white rounded-xl px-4 py-4 flex justify-between items-center shadow">
            <div class="flex items-center gap-3">
                <div class="w-1 h-8 bg-yellow-400 rounded-full"></div>
                <span class="font-bold text-lg">{{ $item->nama_satuan }}</span>
            </div>
            <span class="font-bold text-lg text-yellow-500">{{ number_format($item->total_qty) }}</span>
        </div>
        @empty
        <div class="bg-white rounded-xl px-4 py-8 text-center shadow">
            <i class="bi bi-inbox text-4xl text-gray-400"></i>
            <p class="text-gray-500 mt-2">Tidak ada data satuan</p>
        </div>
        @endforelse
    </div>

    @if(method_exists($data, 'links'))
    <div class="px-5 pb-6">
        {{ $data->links() }}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('input[type="date"]').forEach(el => {
        el.addEventListener('change', () => el.form.submit());
    });
</script>
@endsection
