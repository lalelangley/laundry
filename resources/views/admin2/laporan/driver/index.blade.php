@extends('layouts.master')
@section('title', 'Laporan Driver')
@section('content')

<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('laporan.index') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Laporan Driver</h1>
                    <p class="text-sm text-gray-700 mt-1">Monitor performa pengiriman driver</p>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER SECTION --}}
    <div class="px-8 py-6">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <form method="GET" action="{{ route('laporan.driver.index') }}" class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="bi bi-calendar-event me-1"></i>Dari Tanggal
                    </label>
                    <input type="date" 
                           name="dari" 
                           value="{{ $tglAwal }}"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition">
                </div>
                
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="bi bi-calendar-check me-1"></i>Sampai Tanggal
                    </label>
                    <input type="date" 
                           name="sampai" 
                           value="{{ $tglAkhir }}"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition">
                </div>
                
                <button type="submit" 
                        class="px-8 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold rounded-xl transition flex items-center gap-2 shadow-lg">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                </button>
            </form>
        </div>
    </div>

    {{-- STATISTICS CARDS --}}
    <div class="px-8 pb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Total Driver Aktif</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $data->count() }}</h3>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-person-badge text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Total Pengiriman</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $data->sum('total_pengiriman') }}</h3>
                    </div>
                    <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-truck text-3xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Berhasil Terkirim</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $data->sum('terkirim') }}</h3>
                    </div>
                    <div class="w-16 h-16 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-check-circle text-3xl text-emerald-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Total Pendapatan</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">Rp {{ number_format($data->sum('total_pendapatan'), 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-cash-stack text-3xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="px-8 pb-10">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="px-8 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                        <i class="bi bi-graph-up text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Performa Driver</h2>
                        <p class="text-sm text-gray-500">Periode: {{ \Carbon\Carbon::parse($tglAwal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tglAkhir)->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">No</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Driver</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Total Pengiriman</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                <span class="inline-flex items-center gap-1">
                                    <i class="bi bi-check-circle text-green-600"></i>
                                    Terkirim
                                </span>
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                <span class="inline-flex items-center gap-1">
                                    <i class="bi bi-x-circle text-red-600"></i>
                                    Gagal
                                </span>
                            </th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($data as $index => $driver)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-900 font-medium">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <i class="bi bi-person-fill text-yellow-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $driver->nama_driver }}</p>
                                            <p class="text-sm text-gray-500">
                                                <i class="bi bi-telephone-fill me-1"></i>{{ $driver->no_telp ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 text-blue-700 font-bold text-lg rounded-full">
                                        {{ $driver->total_pengiriman }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                                        {{ $driver->terkirim }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-700">
                                        {{ $driver->gagal }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-gray-900">
                                        Rp {{ number_format($driver->total_pendapatan, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <i class="bi bi-inbox text-5xl text-gray-300"></i>
                                        <p class="text-gray-500 font-medium">Belum ada data driver</p>
                                        <p class="text-sm text-gray-400">Data akan muncul setelah ada transaksi dengan driver</p>
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
    alert("{{ session('success') }}");
</script>
@endif

@endsection