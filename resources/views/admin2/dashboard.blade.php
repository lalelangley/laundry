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

    <!-- Logout -->
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
            class="bg-red-500 w-12 h-12 flex items-center justify-center rounded-full text-white text-2xl shadow hover:bg-red-600 transition">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>

    <!-- Role -->
    <div class="bg-[#ffcc00] px-5 py-3 rounded-xl text-black text-sm font-bold shadow">
        Admin
    </div>

</div>

<!-- STATISTIK CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4 mt-6">

    <!-- MASUK -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-blue-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-box-arrow-in-down text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $masuk ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Masuk</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-blue-500 to-blue-300 rounded-full"></div>
    </div>

    <!-- HARUS SELESAI -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-green-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-check-circle-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $harusSelesai ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Harus Selesai</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-green-500 to-green-300 rounded-full"></div>
    </div>

    <!-- TERLAMBAT -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-red-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-clock-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $terlambat ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Terlambat</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-red-500 to-red-300 rounded-full"></div>
    </div>

</div>

{{-- DATA TABLE KASIR --}}
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

            <a href="{{ route('admin2.transaksi.create') }}"
               class="bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 transition-all px-6 py-3 rounded-2xl text-gray-900 font-bold shadow-lg hover:shadow-xl hover:scale-105 transform inline-flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-lg"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="p-6">
        <div class="overflow-x-auto rounded-2xl border">
            <table id="adminTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-xs uppercase">
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">No Order</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Deadline</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($orders as $i => $o)
                    <tr class="border-b hover:bg-yellow-50 transition">
                        <td class="px-4 py-3">{{ $i+1 }}</td>

                        <td class="px-4 py-3 font-bold">
                            {{ $o->id_transaksi }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $o->nama_pelanggan ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="px-3 py-1 rounded-xl text-xs font-bold
                                @if($o->status == 'selesai') bg-green-100 text-green-700
                                @elseif($o->status == 'proses') bg-blue-100 text-blue-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ ucfirst($o->status) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ $o->tgl_selesai ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin2.riwayat.detail', $o->id_transaksi) }}"
                               class="bg-yellow-400 px-4 py-2 rounded-xl font-bold hover:bg-yellow-500 transition">
                                Detail
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('adminTable').DataTable({
        pageLength: 5,
        lengthChange: false,
        ordering: true,
        searching: true
    });
});
</script>
@endpush
