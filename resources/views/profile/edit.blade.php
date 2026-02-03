@extends('layouts.master')
@section('title', 'Edit Profile')
@section('content')

<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('admin.dashboard') }}" class="text-white text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-white">Edit Profile</span>
    </div>

    {{-- FORM SECTION --}}
    <div class="px-12 py-10">
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-gradient-to-r from-yellow-50 to-white px-12 py-10 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-5">
                        {{-- Foto di header ikut update --}}
                        <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg overflow-hidden">
                            @if($admin->gambar)
                                <img src="{{ asset('storage/' . $admin->gambar) }}" 
                                     alt="Profile" 
                                     class="w-full h-full object-cover">
                            @else
                                <i class="bi bi-person-circle text-white text-2xl"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Edit Profile Admin</h3>
                            <p class="text-sm text-gray-600 mt-1">Update informasi akun: <strong>{{ $admin->nama }}</strong></p>
                        </div>
                    </div>
                    <div class="bg-orange-50 px-5 py-3 rounded-xl border border-orange-200">
                        <p class="text-xs text-orange-600 font-medium">Role</p>
                        <p class="text-xl font-bold text-orange-700">{{ $admin->role_id == 1 ? 'SUPER ADMIN' : 'ADMIN' }}</p>
                    </div>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="px-12 py-12">
                {{-- Alert Success --}}
                @if(session('success'))
                <div class="mb-8 bg-green-50 border-2 border-green-200 rounded-xl p-6">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-check-circle-fill text-green-600 text-xl flex-shrink-0"></i>
                        <div>
                            <h3 class="font-bold text-green-900 mb-1">Berhasil!</h3>
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

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

                {{-- enctype ditambah buat file upload --}}
                <form id="profileForm" method="POST" action="{{ route('profile.admin.update') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        {{-- Foto Profil --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Foto Profil <span class="text-gray-500 text-xs font-normal">(Opsional)</span>
                            </label>
                            <div class="flex items-center gap-4">
                                <div class="w-24 h-24 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden border-2 border-gray-200">
                                    @if($admin->gambar)
                                        <img src="{{ asset('storage/' . $admin->gambar) }}" 
                                             alt="Current" 
                                             id="preview-image"
                                             class="w-full h-full object-cover">
                                    @else
                                        <i class="bi bi-person-circle text-gray-400 text-4xl" id="preview-icon"></i>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input type="file" 
                                           name="gambar"
                                           id="photoInput"
                                           accept="image/jpeg,image/png,image/jpg"
                                           class="hidden">
                                    <button type="button"
                                            onclick="document.getElementById('photoInput').click()"
                                            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white font-semibold transition inline-flex items-center gap-2">
                                        <i class="bi bi-upload"></i>
                                        <span>Pilih Foto</span>
                                    </button>
                                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                        <i class="bi bi-info-circle"></i>
                                        Format: JPG, PNG (Max 2MB)
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Nama Lengkap --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-person text-gray-400 text-lg"></i>
                                </div>
                                <input type="text" 
                                       name="nama"
                                       value="{{ old('nama', $admin->nama) }}"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-6 py-4 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition outline-none @error('nama') border-red-300 @enderror" 
                                       placeholder="Contoh: Ahmad Fauzi"
                                       required>
                            </div>
                            @error('nama')
                                <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Alamat Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-envelope text-gray-400 text-lg"></i>
                                </div>
                                <input type="email" 
                                       name="email"
                                       value="{{ old('email', $admin->email) }}"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-6 py-4 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition outline-none @error('email') border-red-300 @enderror"
                                       placeholder="email@example.com"
                                       required>
                            </div>
                            @error('email')
                                <p class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        {{-- Password Baru --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Password Baru <span class="text-gray-500 text-xs font-normal">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-lock-fill text-gray-400 text-lg"></i>
                                </div>
                                <input type="" 
                                       name="password"
                                       id="password"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-14 py-4 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition outline-none @error('password') border-red-300 @enderror"
                                       placeholder="Minimal 8 karakter"
                                       minlength="8">
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

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Konfirmasi Password <span class="text-gray-500 text-xs font-normal">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="bi bi-shield-check text-gray-400 text-lg"></i>
                                </div>
                                <input type="" 
                                       name="password_confirmation"
                                       id="password_confirmation"
                                       class="w-full border-2 border-gray-300 rounded-xl pl-14 pr-14 py-4 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition outline-none"
                                       placeholder="Ketik ulang password baru"
                                       minlength="8">
                                <button type="button" 
                                        onclick="togglePasswordConfirm()"
                                        class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-gray-600 transition">
                                    <i class="bi bi-eye-fill" id="toggleIconConfirm"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <i class="bi bi-info-circle"></i>
                                Pastikan password sama dengan yang di atas
                            </p>
                        </div>

                        {{-- Info Terakhir Update --}}
                        <div class="lg:col-span-2 bg-gray-50 rounded-xl p-6 border border-gray-200">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-clock-history text-gray-400 text-xl"></i>
                                <div class="text-sm text-gray-600">
                                    <p class="font-semibold text-gray-900">Terakhir Diupdate:</p>
                                    <p>{{ $admin->updated_at->format('d M Y, H:i') }} WIB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer / Buttons --}}
                    <div class="flex items-center justify-between gap-4 mt-12 pt-10 border-t border-gray-200">
                        <a href="{{ url()->previous() }}"
                           class="px-8 py-3 rounded-xl bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold transition inline-flex items-center gap-2">
                            <i class="bi bi-x-circle"></i>
                            <span>Batal</span>
                        </a>
                        <button type="submit"
                                class="px-10 py-3 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white font-bold transition inline-flex items-center gap-2 shadow-lg shadow-orange-200">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="mt-8 bg-amber-50 border-2 border-amber-200 rounded-xl p-6 flex items-start gap-4">
            <i class="bi bi-info-circle-fill text-amber-600 text-xl flex-shrink-0 mt-1"></i>
            <div class="text-sm text-amber-800">
                <p class="font-semibold mb-2">Catatan Keamanan</p>
                <p>Perubahan data profile akan langsung berlaku di sistem. Jika password diubah, gunakan password baru untuk login berikutnya. Pastikan menggunakan password yang kuat dengan kombinasi huruf, angka, dan simbol untuk keamanan akun.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Preview foto
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB!');
            this.value = '';
            return;
        }
        if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            alert('Format file harus JPG atau PNG!');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('preview-image');
            const previewIcon = document.getElementById('preview-icon');
            if (previewImage) {
                previewImage.src = e.target.result;
            } else if (previewIcon) {
                previewIcon.outerHTML = `<img src="${e.target.result}" alt="Preview" id="preview-image" class="w-full h-full object-cover">`;
            }
        }
        reader.readAsDataURL(file);
    }
});

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

// Toggle Password Confirmation Visibility
function togglePasswordConfirm() {
    const passwordInput = document.getElementById('password_confirmation');
    const toggleIcon = document.getElementById('toggleIconConfirm');
    
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

// Password Match Validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirmation').value;
    
    if (password && password !== passwordConfirm) {
        e.preventDefault();
        alert('Password dan Konfirmasi Password tidak sama!');
        return false;
    }
    
    const nama = document.querySelector('input[name="nama"]').value;
    const confirm = window.confirm(`Yakin ingin mengupdate profile "${nama}"?`);
    
    if (!confirm) {
        e.preventDefault();
    }
});
</script>
@endpush