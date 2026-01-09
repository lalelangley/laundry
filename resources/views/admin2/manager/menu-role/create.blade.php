@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('manager.index') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-gray-900">Tambah Hak Akses</span>
    </div>

    <div class="bg-white rounded-xl shadow p-6 max-w-xl">

        <form action="{{ route('manager.store') }}" method="POST">
            @csrf

            {{-- ROLE --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Role</label>
                <select name="role_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Pilih Role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">
                            {{ $role->nama_role }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- MENU --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Menu</label>
                <select name="menu_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Pilih Menu --</option>
                    @foreach ($menus as $menu)
                        <option value="{{ $menu->id }}">
                            {{ $menu->nama_menu }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- PERMISSION --}}
            <div class="grid grid-cols-2 gap-3 mb-6">
                <label><input type="checkbox" name="can_view"> View</label>
                <label><input type="checkbox" name="can_add"> Add</label>
                <label><input type="checkbox" name="can_edit"> Edit</label>
                <label><input type="checkbox" name="can_delete"> Delete</label>
            </div>

            <div class="flex gap-3">
                <button class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
                    Simpan
                </button>
                <a href="{{ route('manager.index') }}"
                   class="bg-gray-300 px-5 py-2 rounded">
                    Batal
                </a>
            </div>
        </form>

    </div>
</div>
@endsection
