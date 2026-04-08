{{-- FE-DOC: Template frontend untuk resources/views/admin2/laporan/pengeluaran/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Pengeluaran')
@section('content')
<div class="min-h-screen bg-gray-100 pb-28">

{{-- Container halaman admin: menjaga tinggi penuh layar dan ruang bawah --}}

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center justify-between sticky top-0 z-20 shadow">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin2.laporan.index') }}" class="text-2xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-lg font-bold">Laporan Pengeluaran</h1>
    </div>
    
    {{-- Export action tetap mempertahankan filter aktif dari request saat ini --}}
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
{{-- FE-DOC: Area filter merangkum input penting agar user bisa mempersempit data yang tampil. --}}
{{-- FE-DOC: Area filter merangkum input tanggal, pencarian, sorting, dan reset agar user bisa mempersempit data. --}}
<form method="GET" id="filterForm" class="px-6 mt-6 space-y-4">
    {{-- Sekumpulan kontrol filter untuk periode, sorting, dan reset cepat --}}
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
        {{-- Pencarian nama pengeluaran dipisah supaya lebih gampang dipakai di mobile --}}
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
            <a href="{{ route('admin2.laporan.pengeluaran.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 px-5 py-2 rounded-full font-bold transition-all">
                Reset
            </a>
        @endif
    </div>
</form>

{{-- SUMMARY CARDS --}}
{{-- FE-DOC: Summary cards menampilkan angka ringkas supaya insight utama terbaca sebelum masuk ke tabel. --}}
<div class="px-6 mt-6 grid grid-cols-2 gap-4">
    {{-- Ringkasan angka utama sebelum user masuk ke detail tabel --}}
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
    {{-- Daftar pengeluaran dibuat 3 kolom agar sederhana dan mudah discan --}}
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
    // Ambil referensi elemen filter yang dipakai oleh semua event listener.
    const filterForm = document.getElementById('filterForm');
    const resetBtn = document.getElementById('resetBtn');
    const dariInput = document.getElementById('dari');
    const sampaiInput = document.getElementById('sampai');
    const sortInput = document.getElementById('sort');

    // Jika dua tanggal sudah lengkap, perubahan tanggal mulai langsung memuat ulang data.
    dariInput.addEventListener('change', function() {
        if (this.value && sampaiInput.value) {
            filterForm.submit();
        }
    });

    // Hal yang sama berlaku saat tanggal akhir diubah.
    sampaiInput.addEventListener('change', function() {
        if (this.value && dariInput.value) {
            filterForm.submit();
        }
    });

    // Reset menghapus semua filter dengan kembali ke route index.
    resetBtn.addEventListener('click', function() {
        window.location.href = '{{ route("admin2.laporan.pengeluaran.index") }}';
    });

    // Sorting otomatis submit agar user tidak perlu klik cari lagi.
    sortInput.addEventListener('change', function() {
        filterForm.submit();
    });

    // Cegah tanggal mulai lebih besar dari tanggal akhir.
    dariInput.addEventListener('change', function() {
        if (sampaiInput.value && this.value > sampaiInput.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            this.value = '';
        }
    });

    // Cegah tanggal akhir lebih kecil dari tanggal mulai.
    sampaiInput.addEventListener('change', function() {
        if (dariInput.value && this.value < dariInput.value) {
            alert('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
            this.value = '';
        }
    });
});
</script>
@endpush
