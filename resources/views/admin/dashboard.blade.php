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
            Versi 1.6.8
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
    <div class="bg-gray-300 w-12 h-12 flex items-center justify-center rounded-full text-2xl">
        <i class="bi bi-person-fill"></i>
    </div>

    <!-- logout -->
    <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit" 
        class="bg-red-500 w-12 h-12 flex items-center justify-center rounded-full text-white text-2xl shadow">
        <i class="bi bi-box-arrow-right"></i>
    </button>
</form>


    <!-- Role -->
    <div class="bg-[#ffcc00] px-5 py-3 rounded-xl text-black text-sm font-bold shadow">
        Super Admin
    </div>

</div>


<!-- PROFILE TOKO -->
<div class="mx-4 mt-4 bg-white rounded-xl p-4 shadow flex gap-4 items-center">
    <img src="{{ asset('images/toko_default.png') }}" class="w-[70px] h-[70px] rounded-xl object-cover" />

    <div class="flex-1">
        <div class="text-xl font-bold">Nama Toko</div>
        <div class="border-b border-yellow-400 w-20 mb-1"></div>
        <div class="text-gray-600 text-sm">Alamat toko default</div>
    </div>

    <div class="text-yellow-500 text-2xl cursor-pointer">
        <i class="bi bi-pencil-square"></i>
    </div>
</div>

<!-- Statistik -->
<div class="grid grid-cols-3 gap-3 mx-4 mt-4">

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

<!-- MENU GRID -->
<div class="grid grid-cols-3 gap-4 mx-4 mt-6">

    <a href="{{ route('layanan.index') }}" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-bag-check-fill"></i></div>
        <div class="font-semibold">Layanan</div>
    </a>

    <a href="#" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-search"></i></div>
        <div class="font-semibold">Riwayat</div>
    </a>

    <a href="#" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-clipboard-data-fill"></i></div>
        <div class="font-semibold">Laporan</div>
    </a>

    <a href="{{ route('parfum.index') }}" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-wind"></i></div>
        <div class="font-semibold">Parfum</div>
    </a>

    <a href="{{ route('satuan.index') }}" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <div class="font-semibold">Satuan</div>
    </a>

    <a href="#" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-person-fill"></i></div>
        <div class="font-semibold">Pelanggan</div>
    </a>

    <a href="#" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-cash-stack"></i></div>
        <div class="font-semibold">Pengeluaran</div>
    </a>

    <a href="#" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-gear-fill"></i></div>
        <div class="font-semibold">Pengaturan</div>
    </a>

    <a href="#" class="bg-white rounded-xl p-6 shadow text-center">
        <div class="text-4xl mb-2"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="font-semibold">Security</div>
    </a>

</div>

<!-- TRANSAKSI BUTTON -->
<div class="mx-4 mt-8 mb-10">
    <button class="w-full bg-[#ffcc00] py-4 rounded-xl text-xl font-bold shadow">
        Transaksi
    </button>
</div>

@endsection
