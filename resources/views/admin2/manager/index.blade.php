@extends('layouts.master')

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
            <a href="{{ route('admin2.manager.kasir.create') }}"
               class="bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-medium transition flex items-center gap-2 shadow-sm">
                <i class="bi bi-person-badge text-lg"></i>
                <span>Tambah Kasir</span>
            </a>

            <a href="{{ route('admin2.manager.driver.create') }}"
               class="bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-medium transition flex items-center gap-2 shadow-sm">
                <i class="bi bi-truck text-lg"></i>
                <span>Tambah Driver</span>
            </a>
        </div>
    </div>

    <div class="px-8 pb-10 space-y-8">

        {{-- ================= KASIR ================= --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-8 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                        <i class="bi bi-person-badge-fill text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Kasir</h2>
                        <p class="text-sm text-gray-500">Manajemen akun kasir toko</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Nama Kasir</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">No HP</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
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
                                <td class="px-6 py-4 text-gray-600">{{ $kasir->no_hp ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin2.manager.update.status') }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="user_type" value="kasir">
                                        <input type="hidden" name="user_id" value="{{ $kasir->id_kasir }}">

                                        <select name="status"
                                                onchange="this.form.submit()"
                                                class="px-3 py-1 rounded-lg border-2 text-xs font-semibold cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-yellow-400
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
                                       class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded-lg text-sm font-semibold text-white transition">
                                        <i class="bi bi-pencil-fill"></i>
                                        <span>Edit</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-10 text-center">
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
        </div>

        {{-- ================= DRIVER ================= --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-8 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                        <i class="bi bi-truck text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Driver</h2>
                        <p class="text-sm text-gray-500">Manajemen akun driver pengiriman</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Nama Driver</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">No Telp</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($drivers as $driver)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <i class="bi bi-person-fill text-yellow-600"></i>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $driver->nama_driver ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $driver->no_telp ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin2.manager.update.status') }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="user_type" value="driver">
                                        <input type="hidden" name="user_id" value="{{ $driver->id_driver }}">

                                        <select name="status"
                                                onchange="this.form.submit()"
                                                class="px-3 py-1 rounded-lg border-2 text-xs font-semibold cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-yellow-400
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
                                       class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded-lg text-sm font-semibold text-white transition">
                                        <i class="bi bi-pencil-fill"></i>
                                        <span>Edit</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-10 text-center">
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
        </div>

    </div>
</div>
@endsection