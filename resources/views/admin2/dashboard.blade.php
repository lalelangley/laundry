@extends('layouts.master')

@section('title', 'Dashboard Admin2')

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

    <!-- Statistik Singkat -->
    <div class="mt-4 text-[14px] font-semibold flex justify-between">
        <div>
            <div class="flex items-center gap-2">
                <i class="bi bi-cart-fill text-lg"></i>
                Laporan Hari ini
            </div>
            <div>Total Omzet : Rp.{{ number_format($totalOmzet ?? 0,0,',','.') }}</div>
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
        Admin 2
    </div>

</div>

<!-- Statistik Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4 mt-6">
    <!-- Transaksi -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-red-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-basket-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalTransaksi ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Transaksi</div>
    </div>

    <!-- Pelanggan -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-blue-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-people-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalPelanggan ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Pelanggan</div>
    </div>
</div>

@endsection
