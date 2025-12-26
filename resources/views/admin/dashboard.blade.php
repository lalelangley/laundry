@extends('layouts.master')

@section('title', 'Dashboard Admin')

@section('content')

<!-- HEADER -->
<div class="bg-[#ffcc00] p-4 rounded-b-3xl shadow-lg">
    <div class="flex items-center justify-between">

        <!-- Toggle Sidebar -->
        <div onclick="toggleSidebar()" class="text-3xl font-bold cursor-pointer">≡</div>

        <!-- Logo -->
        <img src="{{ asset('images/dashboard_logo.png') }}" class="w-40" alt="logo">

        <!-- Versi -->
        <div class="text-sm font-semibold text-black">
            Versi -
        </div>
    </div>

    <!-- Laporan Hari Ini -->
    <div class="mt-4 text-[14px] font-semibold flex justify-between">
        <div>
            <div class="flex items-center gap-2">
                <i class="bi bi-cart-fill text-lg"></i>
                Laporan Hari ini
            </div>
            <div>Total Omzet : Rp.{{ number_format($totalOmzet,0,',','.') }}</div>
        </div>
    </div>
</div>

<!-- USER BAR -->
<div class="mx-4 mt-4 flex items-center justify-end gap-3">

    <!-- Icon User -->
    <div class="bg-gray-300 w-12 h-12 flex items-center justify-center rounded-full text-2xl text-black shadow">
        <i class="bi bi-person-fill"></i>
    </div>

    <!-- Logout (bulat) -->
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" 
            class="bg-red-500 w-12 h-12 flex items-center justify-center rounded-full text-white text-2xl shadow hover:bg-red-600 transition">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>

    <!-- Role -->
    <div class="bg-[#ffcc00] px-5 py-3 rounded-xl text-black text-sm font-bold shadow">
        Super Admin
    </div>

</div>

<!-- Statistik Cards - IMPROVED -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4 mt-6">
    <!-- Transaksi Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-red-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-basket-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalTransaksi }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Transaksi</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-red-500 to-red-300 rounded-full"></div>
    </div>

    <!-- Kasir Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-green-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-people-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalKasir }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Aktif</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Kasir</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-green-500 to-green-300 rounded-full"></div>
    </div>

    <!-- Pelanggan Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-blue-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-person-vcard-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalPelanggan }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Pelanggan</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-blue-500 to-blue-300 rounded-full"></div>
    </div>
</div>

{{-- DATA TABLES - IMPROVED --}}
<div class="mx-4 mt-8 mb-8 bg-white rounded-3xl shadow-xl border-2 border-gray-100">
    
    {{-- HEADER SECTION --}}
    <div class="px-8 py-6 border-b-2 border-gray-100 bg-gradient-to-r from-gray-50 to-white rounded-t-3xl">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="bi bi-clock-history text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500">Daftar transaksi terbaru</p>
                </div>
            </div>

            <a href="{{ route('transaksi.create') }}"
               class="bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 transition-all px-6 py-3 rounded-2xl text-gray-900 font-bold shadow-lg hover:shadow-xl hover:scale-105 transform inline-flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-lg"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="p-8">
        <div class="overflow-x-auto rounded-2xl border-2 border-gray-200">
            <table id="orderTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 text-xs uppercase tracking-wider border-b-2 border-gray-200">
                        <th class="py-4 px-6 text-left font-bold">No</th>
                        <th class="py-4 px-6 text-left font-bold">No. Order</th>
                        <th class="py-4 px-6 text-left font-bold">Tgl Order</th>
                        <th class="py-4 px-6 text-left font-bold">Nama Pelanggan</th>
                        <th class="py-4 px-6 text-left font-bold">Jenis Layanan</th>
                        <th class="py-4 px-6 text-center font-bold">QYT</th>
                        <th class="py-4 px-6 text-center font-bold">Satuan</th>
                        <th class="py-4 px-6 text-center font-bold">Action</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                    @foreach ($orders as $i => $o)
                    @php
                        $d = $o->detail->first(); 
                    @endphp

                    <tr class="border-b border-gray-100 hover:bg-yellow-50/80 transition-colors">
                        <td class="py-4 px-6 text-gray-600 font-medium">{{ $i+1 }}</td>

                        <td class="py-4 px-6">
                            <span class="font-bold text-gray-900">{{ $o->id_transaksi }}</span>
                        </td>

                        <td class="py-4 px-6 text-gray-600">
                            {{ $o->tgl_transaksi ? \Carbon\Carbon::parse($o->tgl_transaksi)->format('d/m/Y') : '-' }}
                        </td>

                        <td class="py-4 px-6 font-semibold text-gray-900">
                            {{ $o->nama_pelanggan ?? '-' }}
                        </td>

                        <td class="py-4 px-6 text-gray-600">
                            {{ $d->jenis->nama_jenis ?? '-' }}
                        </td>

                        <td class="py-4 px-6 text-center">
                            <span class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1.5 rounded-xl bg-blue-100 text-blue-700 font-bold text-sm">
                                {{ $d->qty ?? '-' }}
                            </span>
                        </td>

                        <td class="py-4 px-6 text-center text-gray-600 font-medium">
                            {{ $d->jenis->satuan->nama_satuan ?? '-' }}
                        </td>

                        <td class="py-4 px-6 text-center">
                            <a href="{{ route('riwayat.detail', ['id' => $o->id_transaksi, 'from' => 'dashboard']) }}"
                               class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-400 to-amber-500 px-4 py-2.5 rounded-xl text-gray-900 font-bold hover:from-yellow-500 hover:to-amber-600 transition-all hover:shadow-lg hover:scale-105">
                                <i class="bi bi-eye-fill"></i>
                                <span>Detail</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        let table = $('#orderTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 20, 50],
            ordering: true,
            searching: true,
            destroy: true,

            initComplete: function () {
                // SEARCH BOX
                $('div.dataTables_filter input')
                    .addClass("border-2 border-gray-300 rounded-xl px-4 py-3 ml-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition");

                // SELECT LENGTH
                $('div.dataTables_length select')
                    .addClass("border-2 border-gray-300 rounded-xl px-4 py-2.5 mr-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition");

                // PAGINATION
                setTimeout(() => {
                    $('.dataTables_paginate a')
                        .addClass("px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-yellow-50 hover:border-yellow-400 transition text-sm font-semibold mx-1");

                    $('.dataTables_paginate .current')
                        .addClass("bg-gradient-to-r from-yellow-400 to-amber-500 text-gray-900 border-yellow-500 font-bold shadow-md");
                }, 100);
            }
        });
    });
</script>
@endpush