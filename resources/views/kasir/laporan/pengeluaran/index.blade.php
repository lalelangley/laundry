{{-- FE-DOC: Template frontend untuk resources/views/kasir/laporan/pengeluaran/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Pengeluaran')
@section('content')
<div class="min-h-screen bg-gray-100 pb-28">

{{-- Wrapper halaman: background abu muda + padding bawah supaya pagination tidak mepet --}}

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center justify-between sticky top-0 z-20 shadow">
    <div class="flex items-center gap-4">
        <a href="{{ route('kasir.laporan.index') }}" class="text-2xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-lg font-bold">Laporan Pengeluaran</h1>
    </div>
    
</div>

{{-- FILTER --}}
{{-- FE-DOC: Area filter merangkum input penting agar user bisa mempersempit data yang tampil. --}}
{{-- FE-DOC: Area filter merangkum input tanggal, pencarian, sorting, dan reset agar user bisa mempersempit data. --}}
<form method="GET" id="filterForm" class="px-6 mt-6 space-y-4">
    {{-- Baris filter utama: rentang tanggal, reset, dan urutan data --}}
    <div class="flex flex-wrap items-center gap-3">
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
        <select name="sort"
                id="sort"
                class="bg-white rounded-full px-4 py-3 font-semibold shadow outline-none">
            <option value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
            <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
            <option value="nominal_tertinggi" {{ request('sort') === 'nominal_tertinggi' ? 'selected' : '' }}>Nominal Tertinggi</option>
            <option value="nominal_terendah" {{ request('sort') === 'nominal_terendah' ? 'selected' : '' }}>Nominal Terendah</option>
        </select>
    </div>

    {{-- Search --}}
{{-- FE-DOC: Search dipakai untuk pencarian cepat tanpa perlu membuka filter lanjutan. --}}
    <div class="bg-white rounded-full shadow flex items-center px-4 py-3 gap-3">
        {{-- Search dipisah supaya fokus ke pencarian nama pengeluaran --}}
        <i class="bi bi-search text-xl text-gray-400"></i>
        <input type="text" 
               name="q" 
               value="{{ request('q') }}"
               placeholder="Cari nama pengeluaran..."
               class="flex-1 outline-none bg-transparent font-semibold text-gray-700">
        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 px-5 py-2 rounded-full font-bold transition-all">
            Cari
        </button>
        @if(request()->hasAny(['q', 'dari', 'sampai', 'sort']))
            <a href="{{ route('kasir.laporan.pengeluaran.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 px-5 py-2 rounded-full font-bold transition-all">
                Reset
            </a>
        @endif
    </div>
</form>

{{-- SUMMARY CARDS --}}
{{-- FE-DOC: Summary cards menampilkan angka ringkas supaya insight utama terbaca sebelum masuk ke tabel. --}}
<div class="px-6 mt-6 grid grid-cols-2 gap-4">
    {{-- Ringkasan cepat untuk jumlah item dan total nominal --}}
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
{{-- FE-DOC: Tabel atau daftar utama berisi detail data hasil filter dan sorting. --}}
<div class="px-6 mt-6">
    {{-- Tabel dibuat pakai grid 3 kolom agar layout tetap rapi di mobile --}}
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
</div>
@endsection

@push('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil semua elemen filter sekali di awal supaya mudah dipakai ulang.
    const filterForm = document.getElementById('filterForm');
    const resetBtn = document.getElementById('resetBtn');
    const dariInput = document.getElementById('dari');
    const sampaiInput = document.getElementById('sampai');
    const sortInput = document.getElementById('sort');

    // Saat tanggal awal berubah, form langsung submit kalau tanggal akhir sudah ada.
    dariInput.addEventListener('change', function() {
        if (this.value && sampaiInput.value) {
            filterForm.submit();
        }
    });

    // Saat tanggal akhir berubah, form juga auto-submit kalau tanggal awal sudah terisi.
    sampaiInput.addEventListener('change', function() {
        if (this.value && dariInput.value) {
            filterForm.submit();
        }
    });

    // Tombol reset mengembalikan user ke URL dasar tanpa query string filter.
    resetBtn.addEventListener('click', function() {
        window.location.href = '{{ route("kasir.laporan.pengeluaran.index") }}';
    });

    // Perubahan urutan data langsung refresh hasil tanpa perlu klik tombol cari.
    sortInput.addEventListener('change', function() {
        filterForm.submit();
    });

    // Validasi sederhana agar tanggal mulai tidak melewati tanggal akhir.
    dariInput.addEventListener('change', function() {
        if (sampaiInput.value && this.value > sampaiInput.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            this.value = '';
        }
    });

    // Validasi kebalikannya untuk menjaga rentang tanggal tetap masuk akal.
    sampaiInput.addEventListener('change', function() {
        if (dariInput.value && this.value < dariInput.value) {
            alert('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
            this.value = '';
        }
    });
});
</script>
@endpush
