{{-- FE-DOC: Template frontend untuk resources/views/admin2/manager/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Manajemen Pengguna')

@section('content')
@php
$adminLogin = auth()->guard('admin')->user();
@endphp

<div class="min-h-screen bg-gray-50">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl shadow-lg">
        <a href="{{ route('admin2.dashboard') }}" class="text-black text-3xl font-bold hover:opacity-80 transition inline-block mb-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">User Manager</h1>
        <p class="text-sm text-gray-700 mt-1">
            Daftar Kasir dan Driver Aktif
        </p>
    </div>

    {{-- BUTTONS SECTION --}}
    <div class="px-8 py-6">
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('admin2.manager.role.hak') }}"
               class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-6 py-3 rounded-xl font-bold transition flex items-center gap-2 shadow-md hover:shadow-lg">
                <i class="bi bi-sliders text-lg"></i>
                <span>Setting Hak Akses Role</span>
            </a>

            <a href="{{ route('admin2.manager.kasir.create') }}"
               class="bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold transition flex items-center gap-2 shadow-sm">
                <i class="bi bi-person-plus text-lg"></i>
                <span>Tambah Kasir</span>
            </a>

            <a href="{{ route('admin2.manager.driver.create') }}"
               class="bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold transition flex items-center gap-2 shadow-sm">
                <i class="bi bi-truck text-lg"></i>
                <span>Tambah Driver</span>
            </a>
        </div>
    </div>

    <div class="px-8 pb-10 space-y-8">

        {{-- ================= KASIR ================= --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- HEADER SECTION --}}
            <div class="px-8 py-5 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                            <i class="bi bi-person-badge-fill text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Kasir</h2>
                            <p class="text-sm text-gray-500">Manajemen akun kasir toko</p>
                        </div>
                    </div>
                    <form method="GET">
                        <input type="hidden" name="drivers_sort" value="{{ request('drivers_sort', 'nama_asc') }}">
                        <select name="kasirs_sort" onchange="this.form.submit()" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold outline-none">
                            <option value="nama_asc" {{ request('kasirs_sort', 'nama_asc') === 'nama_asc' ? 'selected' : '' }}>Kasir A-Z</option>
                            <option value="nama_desc" {{ request('kasirs_sort') === 'nama_desc' ? 'selected' : '' }}>Kasir Z-A</option>
                            <option value="terlama" {{ request('kasirs_sort') === 'terlama' ? 'selected' : '' }}>Kasir Terlama</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Nama</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">No HP</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($kasirs as $kasir)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="bi bi-person-fill text-gray-600"></i>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $kasir->nama_kasir ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $kasir->no_hp ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin2.manager.update.status') }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="user_type" value="kasir">
                                        <input type="hidden" name="user_id" value="{{ $kasir->id_kasir }}">

                                        <select name="status"
                                                onchange="this.form.submit()"
                                                class="px-3 py-1.5 rounded-lg border-2 text-xs font-bold cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-yellow-400
                                                    {{ $kasir->status === 'aktif'
                                                            ? 'bg-green-100 text-green-700 border-green-300'
                                                            : 'bg-red-100 text-red-700 border-red-300' }}">
                                            <option value="aktif" {{ $kasir->status === 'aktif' ? 'selected' : '' }}>
                                                ✓ Aktif
                                            </option>
                                            <option value="nonaktif" {{ $kasir->status === 'nonaktif' ? 'selected' : '' }}>
                                                ✕ Nonaktif
                                            </option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin2.manager.kasir.edit', $kasir->id_kasir) }}"
                                       class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 px-4 py-2 rounded-lg text-sm font-bold text-gray-900 transition shadow-sm hover:shadow-md">
                                        <i class="bi bi-pencil-fill"></i>
                                        <span>Edit</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="bi bi-inbox text-4xl text-gray-300"></i>
                                        <p class="text-gray-500 font-medium">Belum ada kasir</p>
                                        <p class="text-sm text-gray-400">Tambahkan kasir baru melalui tombol di atas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($kasirs, 'links'))
            <div class="px-8 py-5 border-t border-gray-200">
                {{ $kasirs->appends(request()->except('kasirs_page'))->links() }}
            </div>
            @endif
        </div>

        {{-- ================= DRIVER ================= --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- HEADER SECTION --}}
            <div class="px-8 py-5 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                            <i class="bi bi-truck text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Driver</h2>
                            <p class="text-sm text-gray-500">Manajemen akun driver pengiriman</p>
                        </div>
                    </div>
                    <form method="GET">
                        <input type="hidden" name="kasirs_sort" value="{{ request('kasirs_sort', 'nama_asc') }}">
                        <select name="drivers_sort" onchange="this.form.submit()" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold outline-none">
                            <option value="nama_asc" {{ request('drivers_sort', 'nama_asc') === 'nama_asc' ? 'selected' : '' }}>Driver A-Z</option>
                            <option value="nama_desc" {{ request('drivers_sort') === 'nama_desc' ? 'selected' : '' }}>Driver Z-A</option>
                            <option value="terlama" {{ request('drivers_sort') === 'terlama' ? 'selected' : '' }}>Driver Terlama</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Nama</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">No Telp</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($drivers as $driver)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="bi bi-person-fill text-gray-600"></i>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $driver->nama_driver ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $driver->no_telp ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin2.manager.update.status') }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="user_type" value="driver">
                                        <input type="hidden" name="user_id" value="{{ $driver->id_driver }}">

                                        <select name="status"
                                                onchange="this.form.submit()"
                                                class="px-3 py-1.5 rounded-lg border-2 text-xs font-bold cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-yellow-400
                                                    {{ $driver->status === 'aktif'
                                                            ? 'bg-green-100 text-green-700 border-green-300'
                                                            : 'bg-red-100 text-red-700 border-red-300' }}">
                                            <option value="aktif" {{ $driver->status === 'aktif' ? 'selected' : '' }}>
                                                ✓ Aktif
                                            </option>
                                            <option value="nonaktif" {{ $driver->status === 'nonaktif' ? 'selected' : '' }}>
                                                ✕ Nonaktif
                                            </option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin2.manager.driver.edit', $driver->id_driver) }}"
                                       class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 px-4 py-2 rounded-lg text-sm font-bold text-gray-900 transition shadow-sm hover:shadow-md">
                                        <i class="bi bi-pencil-fill"></i>
                                        <span>Edit</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="bi bi-inbox text-4xl text-gray-300"></i>
                                        <p class="text-gray-500 font-medium">Belum ada driver</p>
                                        <p class="text-sm text-gray-400">Tambahkan driver baru melalui tombol di atas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($drivers, 'links'))
            <div class="px-8 py-5 border-t border-gray-200">
                {{ $drivers->appends(request()->except('drivers_page'))->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
