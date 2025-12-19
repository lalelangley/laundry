@extends('layouts.master')
@section('content')

@php
    use App\Models\Admin;
    $adminLogin = session('admin_id')
        ? Admin::find(session('admin_id'))
        : null;
@endphp


<div class="min-h-screen bg-gray-50">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl shadow-lg flex items-center justify-between">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">User Manager</h1>
            <p class="text-sm text-gray-800 mt-1">
                Daftar Super Admin, Admin, dan Kasir Aktif
            </p>
        </div>
    </div>

    <div class="px-8 py-10 space-y-10">

        {{-- ================= ADMIN ================= --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-8 py-5 border-b flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">
                    <i class="bi bi-people-fill me-2"></i>Admin Sistem
                </h2>
                <a href="{{ route('manager.admin.create') }}"
                   class="bg-yellow-400 hover:bg-yellow-500 px-4 py-2 rounded-lg font-semibold text-gray-900">
                    + Tambah Admin
                </a>
            </div>

            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-8 py-4 text-left">Nama</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Hak Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($admins as $admin)
                        <tr>
                            <td class="px-8 py-4 font-medium">{{ $admin->nama }}</td>
                            <td class="px-6 py-4">{{ $admin->email }}</td>
                            <td class="px-6 py-4">
                                @if($admin->role_id == 1)
                                    <span class="text-green-600 font-semibold">Super Admin</span>
                                @else
                                    <span class="text-blue-600 font-semibold">Admin</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($admin->is_active ?? true)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">Aktif</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($adminLogin && $adminLogin->role_id == 1)
                                    <a href="{{ route('manager.akses', ['user_type' => 'admin', 'user_id' => $admin->id_admin]) }}"
                                       class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">
                                        Hak Akses
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-6 text-center text-gray-500">
                                Belum ada admin
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ================= KASIR ================= --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-8 py-5 border-b flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">
                    <i class="bi bi-person-badge-fill me-2"></i>Kasir
                </h2>
                <a href="{{ route('manager.kasir.create') }}"
                   class="bg-yellow-400 hover:bg-yellow-500 px-4 py-2 rounded-lg font-semibold text-gray-900">
                    + Tambah Kasir
                </a>
            </div>

            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-8 py-4 text-left">Nama Kasir</th>
                        <th class="px-6 py-4">No HP</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Hak Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($kasirs as $kasir)
                        <tr>
                            <td class="px-8 py-4 font-medium">{{ $kasir->nama_kasir ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $kasir->no_hp ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($kasir->is_active ?? true)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">Aktif</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($adminLogin && $adminLogin->role_id == 1)
                                    <a href="{{ route('manager.akses', ['user_type' => 'kasir', 'user_id' => $kasir->id]) }}"
                                       class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">
                                        Hak Akses
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-6 text-center text-gray-500">
                                Belum ada kasir
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
