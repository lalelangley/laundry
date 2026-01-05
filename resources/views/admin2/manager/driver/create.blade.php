@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('manager.index') }}" class="text-white text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-white">Tambah Driver</span>
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
                {{-- Alert Error --}}
                @if($errors->any())
                <div class="mb-8 bg-red-50 border-2 border-red-200 rounded-xl p-6">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-exclamation-triangle-fill text-red-600 text-xl flex-shrink-0"></i>
                        <div>
                            <h3 class="font-bold text-red-900 mb-2">Terdapat Kesalahan!</h3>
                            <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <form action="{{ route('manager.driver.store') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        {{-- Nama Driver --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Nama Driver <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-person-fill text-gray-400 text-lg"></i>
                                </div>
                                <input type="text" 
                                       name="nama_driver"
                                       value="{{ old('nama_driver') }}"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-6 py-4 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition outline-none @error('nama_driver') border-red-300 @enderror" 
                                       placeholder="Contoh: Budi Santoso"
                                       required>
                            </div>
                            @error('nama_driver')
                                <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- No Telp --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                No Telepon <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-telephone-fill text-gray-400 text-lg"></i>
                                </div>
                                <div class="absolute inset-y-0 left-14 flex items-center pointer-events-none text-gray-500 font-medium">
                                    +62
                                </div>
                                <input type="tel" 
                                       name="no_telp"
                                       value="{{ old('no_telp') }}"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-24 pr-6 py-4 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition outline-none @error('no_telp') border-red-300 @enderror"
                                       placeholder="81234567890"
                                       pattern="[0-9]+"
                                       maxlength="15"
                                       required>
                            </div>
                            @error('no_telp')
                                <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <i class="bi bi-info-circle"></i>
                                Format: 81234567890 (tanpa 0 di depan)
                            </p>
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Password/PIN <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-shield-lock-fill text-gray-400 text-lg"></i>
                                </div>
                                <input type="password" 
                                       name="password"
                                       id="password"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-14 py-4 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition outline-none @error('password') border-red-300 @enderror"
                                       placeholder="Minimal 6 karakter"
                                       minlength="6"
                                       required>
                                <button type="button" 
                                        onclick="togglePassword()"
                                        class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-gray-600 transition">
                                    <i class="bi bi-eye-fill" id="toggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Status Driver <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-toggle-on text-gray-400 text-lg"></i>
                                </div>
                                <select name="status" 
                                        class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-6 py-4 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition outline-none appearance-none bg-white" 
                                        required>
                                    <option value="aktif" selected>✓ Aktif - Siap Bertugas</option>
                                    <option value="nonaktif">✕ Nonaktif - Tidak Bertugas</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                    <i class="bi bi-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Info Text --}}
                        <div class="mt-8 bg-blue-50 border-2 border-blue-200 rounded-xl p-6 flex items-start gap-4">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-shield-check text-orange-600 text-xl flex-shrink-0 mt-1"></i>
                                <div class="text-sm text-gray-700">
                                    <p class="font-semibold text-gray-900 mb-2">Keamanan Password:</p>
                                    <ul class="space-y-1 list-disc list-inside">
                                        <li>Password akan di-hash secara otomatis untuk keamanan</li>
                                        <li>Minimal 6 karakter untuk keamanan optimal</li>
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
                                class="px-10 py-3 rounded-xl bg-gradient-to-r from-orange-400 to-amber-500 hover:from-orange-500 hover:to-amber-600 text-white font-bold transition inline-flex items-center gap-2 shadow-lg shadow-orange-200">
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
                <p>Driver yang ditambahkan akan digunakan untuk pengiriman laundry ke pelanggan. Pastikan nomor telepon aktif untuk komunikasi dan data yang dimasukkan sudah benar sebelum menyimpan.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle Password Visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye-fill');
        toggleIcon.classList.add('bi-eye-slash-fill');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash-fill');
        toggleIcon.classList.add('bi-eye-fill');
    }
}

// Format Phone Number (hanya angka)
document.querySelector('input[name="no_telp"]').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});

// Konfirmasi sebelum submit
document.querySelector('form').addEventListener('submit', function(e) {
    const nama = document.querySelector('input[name="nama_driver"]').value;
    const confirm = window.confirm(`Yakin ingin menambahkan driver "${nama}"?`);
    
    if (!confirm) {
        e.preventDefault();
    }
});
</script>
@endpush