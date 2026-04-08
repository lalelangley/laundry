{{-- FE-DOC: Template frontend untuk resources/views/manager/kasir/create.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Tambah Manajemen Pengguna')
@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('manager.index') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-gray-900">Tambah Kasir</span>
    </div>

    {{-- FORM SECTION --}}
    <div class="px-12 py-10">
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-gradient-to-r from-yellow-50 to-white px-12 py-10 border-b border-gray-200">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 bg-yellow-400 rounded-2xl flex items-center justify-center">
                        <i class="bi bi-person-plus-fill text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Informasi Kasir Baru</h3>
                        <p class="text-sm text-gray-600 mt-1">Lengkapi data di bawah untuk menambahkan kasir ke sistem</p>
                    </div>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="px-12 py-12">
                <form action="{{ route('manager.kasir.store') }}" method="POST">
                    @csrf

                    {{-- ===== ALERT ERROR GLOBAL (duplikat / validasi gagal) ===== --}}
                    @if ($errors->any())
                    <div class="mb-8 bg-red-50 border-2 border-red-300 rounded-xl p-5 flex items-start gap-4">
                        <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-red-700 mb-2">Data tidak dapat disimpan!</p>
                            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    {{-- ===== ALERT SUCCESS (opsional, jika ada session success) ===== --}}
                    @if (session('success'))
                    <div class="mb-8 bg-green-50 border-2 border-green-300 rounded-xl p-5 flex items-start gap-4">
                        <i class="bi bi-check-circle-fill text-green-500 text-xl flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

                        {{-- ===== NAMA KASIR ===== --}}
                        <div class="lg:col-span-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-person text-lg
                                        {{ $errors->has('nama_kasir') ? 'text-red-400' : 'text-gray-400' }}"></i>
                                </div>
                                <input type="text"
                                       name="nama_kasir"
                                       value="{{ old('nama_kasir') }}"
                                       class="w-full border-2 rounded-xl pl-14 pr-6 py-4 transition outline-none
                                              {{ $errors->has('nama_kasir')
                                                 ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                                 : 'border-gray-300 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                       placeholder="Contoh: Budi Santoso"
                                       required>
                            </div>
                            @error('nama_kasir')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- ===== EMAIL ===== --}}
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-envelope-fill text-lg
                                        {{ $errors->has('email') ? 'text-red-400' : 'text-gray-400' }}"></i>
                                </div>
                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="w-full border-2 rounded-xl pl-14 pr-6 py-4 transition outline-none
                                              {{ $errors->has('email')
                                                 ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                                 : 'border-gray-300 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                       placeholder="Contoh: kasir@kasmini.com"
                                       required>
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- ===== NO TELEPON ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                No. Telepon <span class="text-gray-400 text-xs">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-telephone-fill text-lg
                                        {{ $errors->has('no_hp') ? 'text-red-400' : 'text-gray-400' }}"></i>
                                </div>
                                <input type="text"
                                       name="no_hp"
                                       value="{{ old('no_hp') }}"
                                       class="w-full border-2 rounded-xl pl-14 pr-6 py-4 transition outline-none
                                              {{ $errors->has('no_hp')
                                                 ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                                 : 'border-gray-300 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                       placeholder="Contoh: 08xxxxxxxxxx">
                            </div>
                            @error('no_hp')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- ===== PASSWORD ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-lock-fill text-lg
                                        {{ $errors->has('password') ? 'text-red-400' : 'text-gray-400' }}"></i>
                                </div>
                                <input type="password"
                                       name="password"
                                       id="passwordInput"
                                       class="w-full border-2 rounded-xl pl-14 pr-14 py-4 transition outline-none
                                              {{ $errors->has('password')
                                                 ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                                 : 'border-gray-300 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                       placeholder="Minimal 6 karakter"
                                       required>
                                {{-- Toggle show/hide password --}}
                                <button type="button"
                                        onclick="togglePassword()"
                                        class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-gray-600 transition">
                                    <i class="bi bi-eye-fill text-lg" id="eyeIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Info password --}}
                        <div class="lg:col-span-3 bg-gray-50 rounded-xl p-6 border border-gray-200">
                            <p class="text-sm text-gray-600">
                                <i class="bi bi-info-circle-fill text-gray-400 me-2"></i>
                                Gunakan kombinasi huruf, angka, dan simbol untuk keamanan password yang lebih baik.
                                Password minimal <strong>6 karakter</strong>.
                            </p>
                        </div>

                    </div>

                    {{-- Card Footer / Buttons --}}
                    <div class="flex items-center justify-between gap-4 mt-12 pt-10 border-t border-gray-200">
                        <a href="{{ route('manager.index') }}"
                           class="px-8 py-3 rounded-xl bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold transition inline-flex items-center gap-2">
                            <i class="bi bi-x-circle"></i>
                            <span>Batal</span>
                        </a>
                        <button type="submit"
                                class="px-10 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold transition inline-flex items-center gap-2 shadow-lg shadow-yellow-200">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Simpan Kasir</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="mt-8 bg-blue-50 border-2 border-blue-200 rounded-xl p-6 flex items-start gap-4">
            <i class="bi bi-info-circle-fill text-blue-600 text-xl flex-shrink-0 mt-1"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-2">Informasi Penting</p>
                <p>Kasir yang ditambahkan akan mendapatkan akses ke sistem sesuai dengan hak akses yang telah ditentukan.
                   Pastikan <strong>nama kasir</strong>, <strong>email</strong>, dan <strong>nomor HP</strong> belum terdaftar di sistem sebelum menyimpan.</p>
            </div>
        </div>

    </div>
</div>

{{-- Script toggle password --}}
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
        }
    }
</script>
@endsection
