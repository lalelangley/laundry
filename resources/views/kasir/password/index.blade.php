@extends('layouts.master')

@section('title', 'Ganti Password')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
    <a href="{{ route('kasir.dashboard') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Ganti Password</span>
</div>

<div class="p-4 space-y-4 pb-24">

    {{-- FORM GANTI PASSWORD --}}
    <div class="bg-white rounded-3xl p-6 shadow-xl">
        
        {{-- PASSWORD LAMA --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="bi bi-lock-fill text-yellow-500"></i>
                Password Lama
            </label>
            <div class="relative">
                <input type="password" id="oldPassword" placeholder="Masukkan password lama"
                       class="w-full p-4 pr-12 rounded-2xl border-2 border-gray-200 bg-gray-50 text-gray-800 font-medium focus:border-yellow-400 focus:outline-none transition">
                <button type="button" onclick="togglePassword('oldPassword', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-yellow-500">
                    <i class="bi bi-eye-fill"></i>
                </button>
            </div>
        </div>

        {{-- PASSWORD BARU --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="bi bi-key-fill text-yellow-500"></i>
                Password Baru
            </label>
            <div class="relative">
                <input type="password" id="newPassword" placeholder="Masukkan password baru"
                       class="w-full p-4 pr-12 rounded-2xl border-2 border-gray-200 bg-gray-50 text-gray-800 font-medium focus:border-yellow-400 focus:outline-none transition">
                <button type="button" onclick="togglePassword('newPassword', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-yellow-500">
                    <i class="bi bi-eye-fill"></i>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                <i class="bi bi-info-circle-fill"></i> Minimal 8 karakter
            </p>
        </div>

        {{-- KONFIRMASI PASSWORD --}}
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="bi bi-shield-check text-yellow-500"></i>
                Konfirmasi Password Baru
            </label>
            <div class="relative">
                <input type="password" id="confirmPassword" placeholder="Konfirmasi password baru"
                       class="w-full p-4 pr-12 rounded-2xl border-2 border-gray-200 bg-gray-50 text-gray-800 font-medium focus:border-yellow-400 focus:outline-none transition">
                <button type="button" onclick="togglePassword('confirmPassword', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-yellow-500">
                    <i class="bi bi-eye-fill"></i>
                </button>
            </div>
        </div>

        {{-- BUTTON SIMPAN --}}
        <button id="btnSimpan" class="w-full py-4 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-white font-bold rounded-2xl text-lg shadow-lg active:scale-95 transition flex items-center justify-center gap-2">
            <i class="bi bi-check-circle-fill text-xl"></i>
            Simpan
        </button>

    </div>

</div>

{{-- POPUP SUCCESS --}}
<div id="popupSuccess" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center animate__animated animate__zoomIn">
        <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="bi bi-check2 text-white text-6xl"></i>
        </div>
        <h1 class="text-2xl font-bold mb-2">Berhasil!</h1>
        <p class="text-gray-600 mb-6">Password berhasil diubah</p>
        <button id="btnCloseSuccess" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-2xl transition">
            Tutup
        </button>
    </div>
</div>

{{-- POPUP LOADING --}}
<div id="popupLoading" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center">
        <div class="w-20 h-20 border-4 border-yellow-400 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-lg font-semibold text-gray-700">Menyimpan...</p>
    </div>
</div>

{{-- POPUP ERROR --}}
<div id="popupError" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center animate__animated animate__shakeX">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-exclamation-triangle-fill text-red-500 text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Perhatian!</h3>
        <p id="errorMessage" class="text-center text-gray-600 mb-6 leading-relaxed"></p>
        <button id="btnCloseError" class="w-full py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-2xl transition">
            Tutup
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-fill');
        icon.classList.add('bi-eye-slash-fill');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye-fill');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    
    const oldPassword = document.getElementById('oldPassword');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const btnSimpan = document.getElementById('btnSimpan');
    const popupLoading = document.getElementById('popupLoading');
    const popupSuccess = document.getElementById('popupSuccess');
    const popupError = document.getElementById('popupError');
    const errorMessage = document.getElementById('errorMessage');

    // Show error popup
    const showError = (message) => {
        errorMessage.textContent = message;
        popupError.classList.remove('hidden');
    };

    // Close error popup
    document.getElementById('btnCloseError').addEventListener('click', () => {
        popupError.classList.add('hidden');
    });

    // Close success popup and redirect
    document.getElementById('btnCloseSuccess').addEventListener('click', () => {
        popupSuccess.classList.add('hidden');
        window.location.href = "{{ route('kasir.dashboard') }}";
    });

    // Submit form
    btnSimpan.addEventListener('click', async () => {
        const oldPass = oldPassword.value.trim();
        const newPass = newPassword.value.trim();
        const confirmPass = confirmPassword.value.trim();

        // Validasi
        if (!oldPass || !newPass || !confirmPass) {
            showError('Semua field wajib diisi!');
            return;
        }

        if (newPass.length < 8) {
            showError('Password baru minimal 8 karakter!');
            return;
        }

        if (newPass !== confirmPass) {
            showError('Password baru dan konfirmasi tidak cocok!');
            return;
        }

        if (oldPass === newPass) {
            showError('Password baru tidak boleh sama dengan password lama!');
            return;
        }

        // Show loading
        popupLoading.classList.remove('hidden');

        try {
            const res = await fetch("{{ route('kasir.password.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    old_password: oldPass,
                    new_password: newPass,
                    new_password_confirmation: confirmPass
                })
            });

            const data = await res.json();
            
            popupLoading.classList.add('hidden');

            if (data.status) {
                // Clear form
                oldPassword.value = '';
                newPassword.value = '';
                confirmPassword.value = '';
                
                // Show success
                popupSuccess.classList.remove('hidden');
            } else {
                showError(data.message || 'Gagal mengubah password!');
            }

        } catch (err) {
            popupLoading.classList.add('hidden');
            console.error(err);
            showError('Terjadi kesalahan saat mengubah password!');
        }
    });

});
</script>
@endsection