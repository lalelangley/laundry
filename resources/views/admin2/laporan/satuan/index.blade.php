@extends('layouts.master')

@section('title', 'Laporan Satuan')

@section('content')
<div class="min-h-screen bg-gray-100 pb-24">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <a href="{{ route('laporan.index') }}" class="text-2xl font-bold">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Satuan</h1>
        </div>

        <span class="text-sm font-semibold">Export</span>
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

    {{-- LIST SATUAN --}}
    <div class="mt-6 space-y-4">
        @foreach ($data as $item)
            <div class="bg-white mx-5 rounded-xl px-4 py-4 flex justify-between items-center shadow">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 bg-yellow-400 rounded-full"></div>
                    <span class="font-bold text-lg">
                        {{ $item->nama_satuan }}
                    </span>
                </div>

                <span class="font-bold text-lg">
                    {{ $item->total_qty }}
                </span>
            </div>
        @endforeach
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('input[type="date"]').forEach(el => {
        el.addEventListener('change', () => el.form.submit());
    });
</script>
@endsection
