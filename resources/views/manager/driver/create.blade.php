{{-- FE-DOC: Template frontend untuk resources/views/manager/driver/create.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('manager.index') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-gray-900">Tambah Driver</span>
    </div>

    {{-- FORM SECTION --}}
    <div class="px-12 py-10">
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-gradient-to-r from-yellow-50 to-white px-12 py-10 border-b border-gray-200">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 bg-yellow-400 rounded-2xl flex items-center justify-center">
                        <i class="bi bi-truck text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Informasi Driver Baru</h3>
                        <p class="text-sm text-gray-600 mt-1">Lengkapi data di bawah untuk menambahkan driver ke sistem pengiriman</p>
                    </div>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="px-12 py-12">

                {{-- ===== ALERT ERROR GLOBAL ===== --}}
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

                {{-- ===== ALERT SUCCESS ===== --}}
                @if (session('success'))
                <div class="mb-8 bg-green-50 border-2 border-green-300 rounded-xl p-5 flex items-start gap-4">
                    <i class="bi bi-check-circle-fill text-green-500 text-xl flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                <form action="{{ route('manager.driver.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                        {{-- ===== NAMA DRIVER ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Nama Driver <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-person-fill text-lg
                                        {{ $errors->has('nama_driver') ? 'text-red-400' : 'text-gray-400' }}"></i>
                                </div>
                                <input type="text"
                                       name="nama_driver"
                                       value="{{ old('nama_driver') }}"
                                       class="w-full border-2 rounded-xl pl-14 pr-6 py-4 transition outline-none
                                              {{ $errors->has('nama_driver')
                                                 ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                                 : 'border-gray-300 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                       placeholder="Contoh: Budi Santoso"
                                       required>
                            </div>
                            @error('nama_driver')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- ===== NO TELEPON ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                No. Telepon <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-telephone-fill text-lg
                                        {{ $errors->has('no_telp') ? 'text-red-400' : 'text-gray-400' }}"></i>
                                </div>
                                <div class="absolute inset-y-0 left-14 flex items-center pointer-events-none text-gray-500 font-medium text-sm">
                                    +62
                                </div>
                                <input type="tel"
                                       name="no_telp"
                                       id="noTelpInput"
                                       value="{{ old('no_telp') }}"
                                       class="w-full border-2 rounded-xl pl-24 pr-6 py-4 transition outline-none
                                              {{ $errors->has('no_telp')
                                                 ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                                 : 'border-gray-300 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                       placeholder="81234567890"
                                       pattern="[0-9]+"
                                       maxlength="15"
                                       required>
                            </div>
                            @error('no_telp')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <i class="bi bi-info-circle"></i>
                                Format: 81234567890 (tanpa 0 di depan)
                            </p>
                        </div>

                        {{-- ===== PASSWORD ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Password / PIN <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-shield-lock-fill text-lg
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
                                       minlength="6"
                                       required>
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

                        {{-- ===== STATUS ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Status Driver <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-toggle-on text-lg
                                        {{ $errors->has('status') ? 'text-red-400' : 'text-gray-400' }}"></i>
                                </div>
                                <select name="status"
                                        class="w-full border-2 rounded-xl pl-14 pr-6 py-4 transition outline-none appearance-none bg-white
                                               {{ $errors->has('status')
                                                  ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                                                  : 'border-gray-300 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                        required>
                                    <option value="aktif"   {{ old('status', 'aktif') == 'aktif'    ? 'selected' : '' }}>✓ Aktif - Siap Bertugas</option>
                                    <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>✕ Nonaktif - Tidak Bertugas</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                    <i class="bi bi-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Info keamanan password --}}
                        <div class="lg:col-span-2 bg-gray-50 rounded-xl p-6 border border-gray-200">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-shield-check text-gray-400 text-lg flex-shrink-0 mt-0.5"></i>
                                <div class="text-sm text-gray-600">
                                    <p class="font-semibold text-gray-700 mb-1">Keamanan Password:</p>
                                    <ul class="space-y-1 list-disc list-inside">
                                        <li>Password akan di-hash secara otomatis untuk keamanan</li>
                                        <li>Minimal <strong>6 karakter</strong> untuk keamanan optimal</li>
                                        <li>Password dapat diubah kapan saja oleh Super Admin</li>
                                    </ul>
                                </div>
                            </div>
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
                            <span>Simpan Driver</span>
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
                <p>Driver yang ditambahkan akan digunakan untuk pengiriman laundry ke pelanggan.
                   Pastikan <strong>nama driver</strong> dan <strong>nomor telepon</strong> belum terdaftar di sistem sebelum menyimpan.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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

    // Hanya izinkan angka pada field no telepon
    document.getElementById('noTelpInput').addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
</script>
@endpush
@endsection