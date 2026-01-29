@extends('layouts.master')
@section('title', 'Laporan Driver')
@section('content')

<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl shadow-lg sticky top-0 z-10">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin2.laporan.index') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Laporan Driver</h1>
                    <p class="text-sm text-gray-700 mt-1">Monitor performa pengiriman driver</p>
                </div>
            </div>
            
            {{-- Export Button --}}
            <a href="{{ route('admin2.laporan.driver.export') }}?dari={{ $tglAwal }}&sampai={{ $tglAkhir }}" 
               class="flex items-center gap-2 font-semibold bg-white bg-opacity-20 hover:bg-opacity-30 px-5 py-3 rounded-full transition-all shadow">
                <i class="bi bi-file-earmark-spreadsheet text-xl"></i>
                Export Excel
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin2.laporan.driver.index') }}" id="filterForm" class="px-8 mt-6 space-y-4">

        {{-- FILTER TANGGAL --}}
        <div class="flex items-center gap-4">
            <div class="flex-1 bg-yellow-400 rounded-full px-6 py-4 flex items-center gap-3 font-semibold">
                <i class="bi bi-calendar-event"></i>
                <input type="date" 
                       name="dari" 
                       id="dari"
                       value="{{ $tglAwal }}"
                       class="bg-transparent outline-none w-full font-semibold cursor-pointer">
            </div>

            <span class="font-bold text-gray-700">s/d</span>

            <div class="flex-1 bg-yellow-400 rounded-full px-6 py-4 flex items-center gap-3 font-semibold">
                <i class="bi bi-calendar-event"></i>
                <input type="date" 
                       name="sampai" 
                       id="sampai"
                       value="{{ $tglAkhir }}"
                       class="bg-transparent outline-none w-full font-semibold cursor-pointer">
            </div>
            
            <a href="{{ route('admin2.laporan.driver.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 px-4 py-4 rounded-full transition-all"
               title="Reset Filter">
                <i class="bi bi-arrow-clockwise font-bold"></i>
            </a>
        </div>

        {{-- STATISTICS CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Driver Aktif --}}
            <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 font-semibold mb-2">Total Driver Aktif</p>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_driver_aktif'] }}</h3>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-person-badge text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            {{-- Total Pengiriman --}}
            <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-purple-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 font-semibold mb-2">Total Pengiriman</p>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_pengiriman'] }}</h3>
                        <div class="flex gap-3 mt-2">
                            <span class="text-xs px-2 py-1 bg-orange-100 text-orange-700 font-semibold rounded-full">
                                {{ $stats['total_pickup'] }} Pickup
                            </span>
                            <span class="text-xs px-2 py-1 bg-teal-100 text-teal-700 font-semibold rounded-full">
                                {{ $stats['total_antar'] }} Antar
                            </span>
                        </div>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-truck text-3xl text-purple-600"></i>
                    </div>
                </div>
            </div>

            {{-- Berhasil Terkirim --}}
            <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-green-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 font-semibold mb-2">Berhasil Terkirim</p>
                        <h3 class="text-3xl font-bold text-green-600">{{ $stats['total_terkirim'] }}</h3>
                        @if($stats['total_pengiriman'] > 0)
                            <p class="text-xs text-gray-500 mt-2 font-semibold">
                                <i class="bi bi-graph-up-arrow text-green-500"></i>
                                {{ number_format(($stats['total_terkirim'] / $stats['total_pengiriman']) * 100, 1) }}% Success
                            </p>
                        @endif
                    </div>
                    <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-check-circle text-3xl text-green-600"></i>
                    </div>
                </div>
            </div>

            {{-- Gagal & Dalam Proses --}}
            <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-orange-500 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-3">
                        <p class="text-sm text-gray-500 font-bold mb-2">Status</p>
                            <div class="text-center">
                                <h3 class="text-3xl font-bold text-orange-500">{{ $stats['total_proses'] }}</h3>
                                <p class="text-sm font-bold text-gray-700">Proses</p>
                            </div>
                    </div>
                    <div class="w-16 h-16 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-hourglass-split text-3xl text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="px-8 pb-10 mt-6">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- Table Header --}}
            <div class="px-8 py-5 bg-gradient-to-r from-yellow-50 to-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                            <i class="bi bi-graph-up text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Performa Driver</h2>
                            <p class="text-sm text-gray-500">
                                Periode: <span class="font-semibold">{{ \Carbon\Carbon::parse($tglAwal)->format('d M Y') }}</span> - 
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($tglAkhir)->format('d M Y') }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        <span class="font-semibold text-gray-700">{{ $data->count() }}</span> Driver
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Driver</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-truck text-purple-600 text-lg mb-1"></i>
                                    <span>Total</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-box-seam text-orange-600 text-lg mb-1"></i>
                                    <span>Pickup</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-send text-teal-600 text-lg mb-1"></i>
                                    <span>Antar</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-check-circle text-green-600 text-lg mb-1"></i>
                                    <span>Delivered</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-hourglass-split text-orange-600 text-lg mb-1"></i>
                                    <span>Proses</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($data as $index => $driver)
                            <tr class="hover:bg-yellow-50 transition-colors">
                                <td class="px-6 py-5 text-gray-900 font-bold text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 
                                        {{ $index == 0 ? 'bg-yellow-400 text-white' : '' }}
                                        {{ $index == 1 ? 'bg-gray-300 text-white' : '' }}
                                        {{ $index == 2 ? 'bg-orange-300 text-white' : '' }}
                                        {{ $index > 2 ? 'bg-gray-100 text-gray-600' : '' }}
                                        rounded-full text-sm font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                                            <i class="bi bi-person-fill text-white text-2xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-base">{{ $driver->nama_driver }}</p>
                                            <p class="text-sm text-gray-500 flex items-center gap-1 mt-1">
                                                <i class="bi bi-telephone-fill text-yellow-500"></i>
                                                {{ $driver->no_telp ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center justify-center w-14 h-14 bg-purple-500 text-white font-bold text-xl rounded-xl shadow-md">
                                        {{ $driver->total_pengiriman }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[3rem] px-4 py-2 rounded-lg text-base font-bold bg-orange-100 text-orange-700 border-2 border-orange-200">
                                        {{ $driver->total_pickup }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[3rem] px-4 py-2 rounded-lg text-base font-bold bg-teal-100 text-teal-700 border-2 border-teal-200">
                                        {{ $driver->total_antar }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[3rem] px-4 py-2 rounded-lg text-base font-bold bg-green-100 text-green-700 border-2 border-green-200">
                                        {{ $driver->terkirim }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[3rem] px-4 py-2 rounded-lg text-base font-bold bg-orange-100 text-orange-700 border-2 border-orange-200">
                                        {{ $driver->dalam_proses }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="bi bi-inbox text-5xl text-gray-300"></i>
                                        </div>
                                        <div>
                                            <p class="text-gray-500 font-bold text-lg">Belum ada data driver</p>
                                            <p class="text-sm text-gray-400 mt-2">Data akan muncul setelah ada pengiriman dengan driver</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#F4C047',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold'
        }
    });
</script>
@endif

@endsection

@push('scripts')
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