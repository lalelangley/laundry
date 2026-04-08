{{-- FE-DOC: Template frontend untuk resources/views/kasir/laporan/bayar/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Metode Bayar')
@section('content')
<div class="min-h-screen bg-gray-100 pb-24">
{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
    <div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <a href="{{ route('kasir.laporan.index') }}" class="text-2xl font-bold">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Metode Bayar</h1>
        </div>
    </div>

{{-- FILTER TANGGAL --}}
{{-- FE-DOC: Dua input tanggal biasanya menjadi filter utama untuk semua data laporan per periode. --}}
    <form method="GET" class="px-5 mt-5 flex flex-wrap items-center gap-3">
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
        <select name="sort" onchange="this.form.submit()" class="bg-white rounded-full px-4 py-3 text-sm font-semibold outline-none">
            <option value="penggunaan_tertinggi" {{ request('sort', 'penggunaan_tertinggi') === 'penggunaan_tertinggi' ? 'selected' : '' }}>Penggunaan Tertinggi</option>
            <option value="penggunaan_terendah" {{ request('sort') === 'penggunaan_terendah' ? 'selected' : '' }}>Penggunaan Terendah</option>
            <option value="nama_az" {{ request('sort') === 'nama_az' ? 'selected' : '' }}>Nama A-Z</option>
            <option value="nama_za" {{ request('sort') === 'nama_za' ? 'selected' : '' }}>Nama Z-A</option>
        </select>
    </form>

{{-- SUMMARY CARD --}}
{{-- FE-DOC: Summary card dipakai untuk menonjolkan angka utama yang paling cepat dibaca user. --}}
    <div class="px-5 mt-5">
        <div class="bg-white rounded-xl p-4 shadow">
            <p class="text-sm text-gray-600">Total Penggunaan</p>
            <p class="text-2xl font-bold text-yellow-500">{{ $totalPenggunaan }}</p>
        </div>
    </div>

{{-- LIST --}}
    <div class="mt-6 space-y-4 px-5">
        @forelse ($data as $item)
        <div class="bg-white rounded-xl px-4 py-4 flex justify-between items-center shadow">
            <div class="flex items-center gap-3">
                <div class="w-1 h-8 bg-yellow-400 rounded-full"></div>
                <span class="font-bold text-lg">{{ $item->nama_metode_bayar }}</span>
            </div>
            <span class="font-bold text-lg text-yellow-500">{{ $item->total_penggunaan }}</span>
        </div>
        @empty
        <div class="bg-white rounded-xl px-4 py-8 text-center shadow">
            <p class="text-gray-500">Tidak ada data</p>
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

@section('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
    document.querySelectorAll('input[type="date"]').forEach(el => {
        el.addEventListener('change', () => el.form.submit());
    });
</script>
@endsection
