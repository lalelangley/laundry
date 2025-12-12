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

<!-- Statistik -->
<div class="grid grid-cols-3 gap-4 mx-4 mt-4">

    <div class="bg-[#ffcc00] text-center rounded-xl p-4 shadow font-semibold">
        <div class="text-red-600 text-3xl mb-1">
            <i class="bi bi-basket-fill"></i>
        </div>
        <div>Transaksi</div>
        <div class="text-2xl">{{ $totalTransaksi }}</div>
    </div>

    <div class="bg-[#ffcc00] text-center rounded-xl p-4 shadow font-semibold">
        <div class="text-green-600 text-3xl mb-1">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>Kasir</div>
        <div class="text-2xl">{{ $totalKasir }}</div>
    </div>

    <div class="bg-[#ffcc00] text-center rounded-xl p-4 shadow font-semibold">
        <div class="text-blue-600 text-3xl mb-1">
            <i class="bi bi-person-vcard-fill"></i>
        </div>
        <div>Pelanggan</div>
        <div class="text-2xl">{{ $totalPelanggan }}</div>
    </div>

</div>

{{-- ======================================
       DATA TABLES + BUTTON TRANSAKSI
======================================= --}}
<div class="mx-4 mt-8 bg-white rounded-2xl shadow-lg p-5">

    {{-- BUTTON TRANSAKSI --}}
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-lg font-bold text-gray-700">Riwayat Transaksi</h2>

        <a href="{{ route('transaksi.create') }}"
           class="bg-[#ffcc00] hover:bg-yellow-400 transition px-5 py-3 rounded-xl text-black font-bold shadow">
            + Transaksi
        </a>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table id="orderTable" class="w-full text-sm text-left border-separate border-spacing-y-2">
            <thead>
                <tr class="bg-[#ffcc00] text-black text-[13px] uppercase rounded-xl">
                    <th class="py-3 px-4 rounded-l-xl">No</th>
                    <th class="py-3 px-4">No. Order</th>
                    <th class="py-3 px-4">Tgl Order</th>
                    <th class="py-3 px-4">Nama Pelanggan</th>
                    <th class="py-3 px-4">Jenis Layanan</th>
                    <th class="py-3 px-4">QYT</th>
                    <th class="py-3 px-4">Satuan</th>
                    <th class="py-3 px-4 rounded-r-xl text-center">Action</th>
                </tr>
            </thead>

            <tbody class="text-gray-700">
                @foreach ($orders as $i => $o)
                @php
                    $d = $o->detail->first(); 
                @endphp

                <tr class="bg-white shadow-sm hover:bg-yellow-50 transition rounded-xl">
                    <td class="py-3 px-4">{{ $i+1 }}</td>

                    <td class="py-3 px-4 font-semibold text-gray-800">
                        {{ $o->id_transaksi }}
                    </td>

                    <td class="py-3 px-4">
                        {{ $o->tgl_transaksi ? \Carbon\Carbon::parse($o->tgl_transaksi)->format('d/m/Y') : '-' }}
                    </td>

                    <td class="py-3 px-4">
                        {{ $o->nama_pelanggan ?? '-' }}
                    </td>

                    <td class="py-3 px-4">
                        {{ $d->jenis->nama_jenis ?? '-' }}
                    </td>

                    <td class="py-3 px-4">
                        {{ $d->qty ?? '-' }}
                    </td>

                    <td class="py-3 px-4">
                        {{ $d->jenis->satuan->nama_satuan ?? '-' }}
                    </td>

                    <td class="py-3 px-6 text-center">
                        <a href="{{ route('riwayat.detail', ['id' => $o->id_transaksi, 'from' => 'dashboard']) }}"
                    class="inline-block bg-[#ffcc00] px-4 py-2 rounded-xl text-black font-semibold hover:bg-yellow-400 transition">
                        Detail
                    </a>

                 </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
@push('scripts')

<!-- DataTables CSS -->
<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

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
                    .addClass("border border-gray-300 rounded-xl px-3 py-2 ml-2 focus:ring-2 focus:ring-yellow-400 outline-none");

                // SELECT LENGTH
                $('div.dataTables_length select')
                    .addClass("border border-gray-300 rounded-xl px-3 py-2 mr-2");

                // PAGINATION (delayed to avoid conflict)
                setTimeout(() => {
                    $('.dataTables_paginate a')
                        .addClass("px-3 py-1 rounded-lg border bg-white hover:bg-yellow-200 transition text-sm");

                    $('.dataTables_paginate .current')
                        .addClass("bg-[#ffcc00] text-black border-none");
                }, 100);
            }
        });

    });
</script>

@endpush
