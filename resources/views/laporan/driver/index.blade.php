{{-- FE-DOC: Template frontend untuk resources/views/laporan/driver/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Driver')
@section('content')
<div class="min-h-screen bg-gray-100 pb-28">
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl sticky top-0 z-20 shadow">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('laporan.index') }}" class="text-2xl font-bold hover:scale-110 transition-transform">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Driver</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('laporan.driver.export') }}?dari={{ $tglAwal }}&sampai={{ $tglAkhir }}"
               class="flex items-center gap-1.5 text-sm font-semibold bg-white bg-opacity-30 hover:bg-opacity-50 px-3 py-2 rounded-full transition-all">
                <i class="bi bi-file-earmark-spreadsheet text-green-700"></i>
                <span class="hidden sm:inline">Excel</span>
            </a>
            <a href="{{ route('laporan.driver.export', ['dari' => $tglAwal, 'sampai' => $tglAkhir, 'format' => 'pdf']) }}"
               class="flex items-center gap-1.5 text-sm font-semibold bg-white bg-opacity-30 hover:bg-opacity-50 px-3 py-2 rounded-full transition-all">
                <i class="bi bi-file-earmark-pdf text-red-600"></i>
                <span class="hidden sm:inline">PDF</span>
            </a>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('laporan.driver.index') }}" id="filterForm" class="px-6 mt-6 space-y-3">
    <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
        <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 flex items-center gap-2 font-semibold min-w-0">
            <i class="bi bi-calendar-event"></i>
            <input type="date" name="dari" id="dari" value="{{ $tglAwal }}" class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <span class="font-bold text-gray-700 hidden xl:block">s/d</span>
        <div class="flex-1 bg-yellow-400 rounded-3xl xl:rounded-full px-4 py-3 flex items-center gap-2 font-semibold min-w-0">
            <i class="bi bi-calendar-event"></i>
            <input type="date" name="sampai" id="sampai" value="{{ $tglAkhir }}" class="bg-transparent outline-none w-full font-semibold cursor-pointer">
        </div>
        <div class="flex flex-wrap gap-3 xl:mr-4">
            <button type="button" id="resetBtn" class="bg-gray-200 hover:bg-gray-300 px-4 py-3 rounded-full transition-all" title="Reset Filter">
                <i class="bi bi-arrow-clockwise font-bold"></i>
            </button>
            <div class="relative w-full sm:w-auto">
                <select name="sort" id="sort" class="bg-white rounded-full px-4 py-3 pr-12 text-sm font-semibold outline-none shadow w-full sm:w-auto appearance-none">
                    <option value="total_tertinggi" {{ request('sort', 'total_tertinggi') === 'total_tertinggi' ? 'selected' : '' }}>Total Tertinggi</option>
                    <option value="total_terendah" {{ request('sort') === 'total_terendah' ? 'selected' : '' }}>Total Terendah</option>
                    <option value="sukses_tertinggi" {{ request('sort') === 'sukses_tertinggi' ? 'selected' : '' }}>Sukses Tertinggi</option>
                    <option value="nama_az" {{ request('sort') === 'nama_az' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="nama_za" {{ request('sort') === 'nama_za' ? 'selected' : '' }}>Nama Z-A</option>
                </select>
                <i class="bi bi-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
            </div>
        </div>
    </div>
</form>

<div class="px-6 mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl p-4 shadow border-2 border-blue-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-100 rounded-full flex items-center justify-center"><i class="bi bi-person-badge text-blue-600 text-xl"></i></div>
            <div><div class="text-xs text-gray-500 font-semibold">Total Driver Aktif</div><div class="text-2xl font-bold text-gray-800">{{ $stats['total_driver_aktif'] }}</div></div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow border-2 border-purple-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-purple-100 rounded-full flex items-center justify-center"><i class="bi bi-truck text-purple-600 text-xl"></i></div>
            <div><div class="text-xs text-gray-500 font-semibold">Total Pengiriman</div><div class="text-2xl font-bold text-purple-600">{{ $stats['total_pengiriman'] }}</div></div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow border-2 border-green-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-green-100 rounded-full flex items-center justify-center"><i class="bi bi-check-circle text-green-600 text-xl"></i></div>
            <div><div class="text-xs text-gray-500 font-semibold">Berhasil Terkirim</div><div class="text-2xl font-bold text-green-600">{{ $stats['total_terkirim'] }}</div></div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow border-2 border-orange-400">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-orange-100 rounded-full flex items-center justify-center"><i class="bi bi-hourglass-split text-orange-600 text-xl"></i></div>
            <div><div class="text-xs text-gray-500 font-semibold">Dalam Proses</div><div class="text-2xl font-bold text-orange-600">{{ $stats['total_proses'] }}</div></div>
        </div>
    </div>
</div>

<div class="px-6 mt-5">
    <div class="bg-white rounded-xl overflow-hidden shadow">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px]">
                <thead>
                    <tr class="bg-yellow-400 border-b border-yellow-500">
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Driver</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-900 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-900 uppercase tracking-wider">Pickup</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-900 uppercase tracking-wider">Antar</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-900 uppercase tracking-wider">Delivered</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-900 uppercase tracking-wider">Proses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $index => $driver)
                        <tr class="hover:bg-yellow-50 transition-colors">
                            <td class="px-6 py-4 text-center text-gray-500 font-semibold">{{ ($data->firstItem() ?? 1) + $index }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center shrink-0">
                                        <i class="bi bi-person-fill text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $driver->nama_driver }}</p>
                                        <p class="text-sm text-gray-500">{{ $driver->no_telp ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-purple-600">{{ $driver->total_pengiriman }}</td>
                            <td class="px-4 py-4 text-center font-bold text-orange-600">{{ $driver->total_pickup }}</td>
                            <td class="px-4 py-4 text-center font-bold text-teal-600">{{ $driver->total_antar }}</td>
                            <td class="px-4 py-4 text-center font-bold text-green-600">{{ $driver->terkirim }}</td>
                            <td class="px-4 py-4 text-center font-bold text-orange-600">{{ $driver->dalam_proses }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-20 text-gray-400">
                                <i class="bi bi-inbox text-6xl mb-3 block text-gray-300"></i>
                                <p class="font-semibold text-lg">Belum ada data driver</p>
                                <p class="text-sm mt-1">Data akan muncul setelah ada pengiriman dengan driver</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($data, 'hasPages') && $data->hasPages())
<div class="px-6 mt-4 pb-4">
    <div class="px-5 mt-6 flex items-center justify-center gap-2">
        <p class="text-sm text-gray-500 font-semibold">Menampilkan {{ $data->firstItem() }}-{{ $data->lastItem() }} dari {{ $data->total() }} data</p>
        <div class="flex items-center gap-2">
            @if ($data->onFirstPage())
                <span class="px-3 py-2 rounded-full bg-gray-100 text-gray-400 text-sm font-semibold cursor-not-allowed"><i class="bi bi-chevron-left"></i></span>
            @else
                <a href="{{ $data->previousPageUrl() }}" class="px-3 py-2 rounded-full bg-yellow-400 hover:bg-yellow-500 text-sm font-semibold transition-all"><i class="bi bi-chevron-left"></i></a>
            @endif
            @foreach ($data->getUrlRange(max(1, $data->currentPage()-2), min($data->lastPage(), $data->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-bold transition-all {{ $page == $data->currentPage() ? 'bg-yellow-400 text-gray-900 shadow' : 'bg-white hover:bg-yellow-50 text-gray-600 shadow-sm' }}">{{ $page }}</a>
            @endforeach
            @if ($data->hasMorePages())
                <a href="{{ $data->nextPageUrl() }}" class="px-3 py-2 rounded-full bg-yellow-400 hover:bg-yellow-500 text-sm font-semibold transition-all"><i class="bi bi-chevron-right"></i></a>
            @else
                <span class="px-3 py-2 rounded-full bg-gray-100 text-gray-400 text-sm font-semibold cursor-not-allowed"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    </div>
</div>
@endif
</div>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    confirmButtonColor: '#F4C047'
});
</script>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const resetBtn = document.getElementById('resetBtn');
    const dariInput = document.getElementById('dari');
    const sampaiInput = document.getElementById('sampai');
    const sortInput = document.getElementById('sort');
    dariInput.addEventListener('change', function() {
        if (sampaiInput.value && this.value > sampaiInput.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            this.value = '';
            return;
        }
        if (this.value && sampaiInput.value) filterForm.submit();
    });
    sampaiInput.addEventListener('change', function() {
        if (dariInput.value && this.value < dariInput.value) {
            alert('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai');
            this.value = '';
            return;
        }
        if (this.value && dariInput.value) filterForm.submit();
    });
    resetBtn.addEventListener('click', function() {
        window.location.href = '{{ route("laporan.driver.index") }}';
    });
    sortInput.addEventListener('change', function() {
        filterForm.submit();
    });
});
</script>
@endpush
