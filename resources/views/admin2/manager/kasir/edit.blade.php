@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
   <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('admin2.manager.index') }}" class="text-white text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-white">Edit Kasir</span>
    </div>

    {{-- FORM SECTION --}}
    <div class="px-12 py-10">
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Card Header --}}
             <div class="bg-gradient-to-r from-yellow-50 to-white px-12 py-10 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="bi bi-pencil-square text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Edit Data Kasir</h3>
                            <p class="text-sm text-gray-600 mt-1">Update informasi kasir: <strong>{{ $kasir->nama_kasir }}</strong></p>
                        </div>
                    </div>
                    <div class="bg-yellow-50 px-5 py-3 rounded-xl border border-yellow-200">
                        <p class="text-xs text-yellow-600 font-medium">ID Kasir</p>
                        <p class="text-xl font-bold text-yellow-700">#{{ $kasir->id_kasir }}</p>
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

                <form action="{{ route('admin2.manager.kasir.update', $kasir->id_kasir) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        {{-- Nama Kasir --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Nama Kasir <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-person text-gray-400 text-lg"></i>
                                </div>
                                <input type="text" 
                                       name="nama_kasir"
                                       value="{{ old('nama_kasir', $kasir->nama_kasir) }}"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-6 py-4 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition outline-none @error('nama_kasir') border-red-300 @enderror" 
                                       placeholder="Contoh: Siti Nurhaliza"
                                       required>
                            </div>
                            @error('nama_kasir')
                                <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- No HP --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                No HP <span class="text-gray-500 text-xs font-normal">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-telephone text-gray-400 text-lg"></i>
                                </div>
                                <div class="absolute inset-y-0 left-14 flex items-center pointer-events-none text-gray-500 font-medium">
                                    +62
                                </div>
                                <input type="tel" 
                                       name="no_hp"
                                       value="{{ old('no_hp', $kasir->no_hp) }}"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-24 pr-6 py-4 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition outline-none @error('no_hp') border-red-300 @enderror"
                                       placeholder="81234567890"
                                       pattern="[0-9]+"
                                       maxlength="15">
                            </div>
                            @error('no_hp')
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
                                Password Baru <span class="text-gray-500 text-xs font-normal">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-lock-fill text-gray-400 text-lg"></i>
                                </div>
                                <input type="password" 
                                       name="password"
                                       id="password"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-14 py-4 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition outline-none @error('password') border-red-300 @enderror"
                                       placeholder="Kosongkan jika tidak ingin mengubah"
                                       minlength="6">
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
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <i class="bi bi-info-circle"></i>
                                Kosongkan jika tidak ingin mengubah password
                            </p>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-toggle-on text-gray-400 text-lg"></i>
                                </div>
                                <select name="status" 
                                        class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-6 py-4 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition outline-none appearance-none bg-white" 
                                        required>
                                    <option value="aktif" {{ old('status', $kasir->status) === 'aktif' ? 'selected' : '' }}>✓ Aktif - Siap Bertugas</option>
                                    <option value="nonaktif" {{ old('status', $kasir->status) === 'nonaktif' ? 'selected' : '' }}>✕ Nonaktif - Tidak Bertugas</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                    <i class="bi bi-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Info Terakhir Update --}}
                        <div class="lg:col-span-2 bg-gray-50 rounded-xl p-6 border border-gray-200">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-clock-history text-gray-400 text-xl"></i>
                                <div class="text-sm text-gray-600">
                                    <p class="font-semibold text-gray-900">Terakhir Diupdate:</p>
                                    <p>{{ $kasir->updated_at->format('d M Y, H:i') }} WIB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer / Buttons --}}
                    <div class="flex items-center justify-between gap-4 mt-12 pt-10 border-t border-gray-200">
                        <a href="{{ route('admin2.manager.index') }}"
                           class="px-8 py-3 rounded-xl bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold transition inline-flex items-center gap-2">
                            <i class="bi bi-x-circle"></i>
                            <span>Batal</span>
                        </a>
                        <button type="submit"
                                class="px-10 py-3 rounded-xl bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-white font-bold transition inline-flex items-center gap-2 shadow-lg shadow-yellow-200">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Update Kasir</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="mt-8 bg-amber-50 border-2 border-amber-200 rounded-xl p-6 flex items-start gap-4">
            <i class="bi bi-info-circle-fill text-amber-600 text-xl flex-shrink-0 mt-1"></i>
            <div class="text-sm text-amber-800">
                <p class="font-semibold mb-2">Catatan Update</p>
                <p>Perubahan data kasir akan langsung berlaku di sistem. Jika password diubah, kasir harus menggunakan password baru untuk login. Pastikan data sudah benar sebelum menyimpan perubahan.</p>
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
document.querySelector('input[name="no_hp"]').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});

// Konfirmasi sebelum submit
document.querySelector('form').addEventListener('submit', function(e) {
    const nama = document.querySelector('input[name="nama_kasir"]').value;
    const confirm = window.confirm(`Yakin ingin mengupdate data kasir "${nama}"?`);
    
    if (!confirm) {
        e.preventDefault();
    }
});
</script>
@endpush