@extends('layouts.master')
@section('title', 'Edit Profile')
@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    * {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    
    .header-gradient {
        background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%);
        position: relative;
        overflow: hidden;
    }
    
    .header-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.5;
    }
    
    .profile-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
    }
    
    .profile-avatar {
        background: linear-gradient(135deg, #FFD700 0%, #FFEB3B 100%);
        box-shadow: 0 10px 40px rgba(255, 215, 0, 0.6);
        animation: pulse 3s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 10px 40px rgba(255, 215, 0, 0.6); }
        50% { box-shadow: 0 15px 50px rgba(255, 215, 0, 0.8); }
    }
    
    .role-badge {
        background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%);
        border: 2px solid #FFD700;
        box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
    }
    
    .input-modern {
        transition: all 0.3s ease;
        border: 2px solid #E5E7EB;
        background: white;
    }
    
    .input-modern:focus {
        outline: none;
        border-color: #FFD700;
        box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.2);
        background: #FFFEF0;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%);
        box-shadow: 0 6px 16px rgba(255, 215, 0, 0.5);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #FFD700;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(255, 215, 0, 0.7);
        background: linear-gradient(135deg, #FFEB3B 0%, #FFD700 100%);
    }
    
    .btn-primary:active {
        transform: translateY(0);
    }
    
    .alert-success {
        background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
        border: 2px solid #6EE7B7;
        animation: slideIn 0.4s ease;
    }
    
    .alert-error {
        background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
        border: 2px solid #FCA5A5;
        animation: slideIn 0.4s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .profile-header-bg {
        background: linear-gradient(135deg, #FFFEF0 0%, #FFFACD 50%, #FFE66D 100%);
        position: relative;
        overflow: hidden;
    }
    
    .profile-header-bg::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 215, 0, 0.3) 0%, transparent 70%);
        animation: shimmer 6s infinite;
    }
    
    @keyframes shimmer {
        0%, 100% { transform: translate(-20%, -20%) rotate(0deg); }
        50% { transform: translate(20%, 20%) rotate(180deg); }
    }
    
    .input-icon {
        color: #9CA3AF;
        transition: color 0.3s ease;
    }
    
    .input-modern:focus ~ .input-icon {
        color: #FFD700;
    }
    
    .section-card {
        background: white;
        border: 3px solid #FFE66D;
        transition: all 0.3s ease;
    }
    
    .section-card:hover {
        border-color: #FFD700;
        box-shadow: 0 10px 30px rgba(255, 215, 0, 0.25);
        transform: translateY(-2px);
    }
    
    .icon-yellow {
        background: linear-gradient(135deg, #FFD700 0%, #FFEB3B 100%);
        box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
    }
    
    .tips-card {
        border: 3px solid #FFE66D;
        transition: all 0.3s ease;
    }
    
    .tips-card:hover {
        border-color: #FFD700;
        box-shadow: 0 8px 24px rgba(255, 215, 0, 0.3);
        transform: translateY(-3px);
    }
    
    .info-box {
        background: linear-gradient(135deg, #FFFEF0 0%, #FFFACD 100%);
        border: 2px solid #FFD700;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-yellow-50 via-white to-yellow-50">
    {{-- HEADER --}}
    <div class="header-gradient px-6 py-6 shadow-2xl relative z-10">
        <div class="max-w-7xl mx-auto flex items-center gap-4">
            <a href="{{ route('kasir.dashboard') }}" 
               class="w-11 h-11 bg-white/30 backdrop-blur-sm rounded-xl flex items-center justify-center text-white hover:bg-white/40 transition-all active:scale-95 shadow-lg">
                <i class="bi bi-arrow-left text-xl font-bold"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-white tracking-tight drop-shadow-lg">Edit Profile</h1>
                <p class="text-white/90 text-sm font-medium">Perbarui informasi akun Anda</p>
            </div>
        </div>
    </div>

    <div class="px-6 py-8 max-w-7xl mx-auto">
        {{-- MAIN CARD --}}
        <div class="profile-card rounded-3xl shadow-2xl overflow-hidden">
            
            {{-- PROFILE HEADER - HORIZONTAL LAYOUT --}}
            <div class="profile-header-bg px-8 py-6 relative">
                <div class="relative z-10 flex items-center gap-6">
                    <div class="profile-avatar w-20 h-20 rounded-2xl flex items-center justify-center transform hover:scale-110 transition-all flex-shrink-0">
                        @if($kasir->gambar)
                            <img src="{{ asset('storage/' . $kasir->gambar) }}" 
                                 alt="Profile" 
                                 class="w-full h-full object-cover rounded-2xl">
                        @else
                            <i class="bi bi-person-circle text-white text-4xl drop-shadow-lg"></i>
                        @endif
                    </div>
                    
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-800 mb-1">{{ $kasir->nama_kasir }}</h2>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="bi bi-telephone-fill text-yellow-600 drop-shadow"></i>
                                <span class="font-semibold text-sm">{{ $kasir->no_hp }}</span>
                            </div>
                            <div class="role-badge px-4 py-1.5 rounded-lg">
                                <span class="text-xs font-black text-gray-800">
                                    💼 KASIR
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM SECTION - WIDE LAYOUT --}}
            <div class="p-8">
                {{-- ALERTS --}}
                <div id="alert-container"></div>

                <form id="profileForm" enctype="multipart/form-data">
                    @csrf

                    {{-- GRID LAYOUT 2 COLUMNS --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        {{-- LEFT COLUMN: INFORMASI DASAR --}}
                        <div class="section-card rounded-2xl p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="icon-yellow w-12 h-12 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-person-badge text-white text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Informasi Dasar</h3>
                            </div>

                            <div class="space-y-5">
                                {{-- FOTO PROFIL --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        <i class="bi bi-image-fill text-yellow-600 mr-1"></i>
                                        Foto Profil
                                    </label>
                                    <div class="flex items-center gap-4">
                                        <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden">
                                            @if($kasir->gambar)
                                                <img src="{{ asset('storage/' . $kasir->gambar) }}" 
                                                     alt="Current" 
                                                     id="currentPhoto"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <i class="bi bi-person-circle text-gray-400 text-3xl" id="currentPhoto"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" 
                                                   name="gambar"
                                                   id="photoInput"
                                                   accept="image/*"
                                                   class="hidden">
                                            <button type="button"
                                                    onclick="document.getElementById('photoInput').click()"
                                                    class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg font-bold hover:bg-yellow-200 transition text-sm">
                                                <i class="bi bi-upload mr-1"></i>
                                                Pilih Foto
                                            </button>
                                            <p class="text-xs text-gray-500 mt-1">JPG, PNG (Max 2MB)</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- NAMA --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        <i class="bi bi-person-fill text-yellow-600 mr-1"></i>
                                        Nama Lengkap
                                    </label>
                                    <div class="relative">
                                        <i class="bi bi-person-fill absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                                        <input type="text" 
                                               name="nama_kasir"
                                               value="{{ $kasir->nama_kasir }}"
                                               class="input-modern w-full pl-12 pr-4 py-3.5 rounded-xl font-semibold"
                                               placeholder="Masukkan nama lengkap"
                                               required>
                                    </div>
                                </div>

                                {{-- NO HP --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        <i class="bi bi-telephone-fill text-yellow-600 mr-1"></i>
                                        Nomor HP
                                    </label>
                                    <div class="relative">
                                        <i class="bi bi-telephone-fill absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                                        <input type="text" 
                                               name="no_hp"
                                               value="{{ $kasir->no_hp }}"
                                               class="input-modern w-full pl-12 pr-4 py-3.5 rounded-xl font-semibold"
                                               placeholder="08xxxxxxxxxx"
                                               required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: KEAMANAN --}}
                        <div class="section-card rounded-2xl p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="icon-yellow w-12 h-12 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-shield-lock text-white text-xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Keamanan Akun</h3>
                            </div>

                            <div class="info-box rounded-xl p-3 mb-5">
                                <div class="flex gap-2 items-start">
                                    <i class="bi bi-info-circle-fill text-yellow-600 flex-shrink-0 mt-0.5 text-lg"></i>
                                    <p class="text-xs text-gray-800 font-bold">
                                        Kosongkan field password jika tidak ingin mengubah
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-5">
                                {{-- PASSWORD --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        <i class="bi bi-key-fill text-yellow-600 mr-1"></i>
                                        Password Baru
                                    </label>
                                    <div class="relative">
                                        <i class="bi bi-lock-fill absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                                        <input type="password" 
                                               name="password"
                                               class="input-modern w-full pl-12 pr-4 py-3.5 rounded-xl font-semibold"
                                               placeholder="Minimal 6 karakter">
                                    </div>
                                </div>

                                {{-- CONFIRM PASSWORD --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        <i class="bi bi-shield-check text-yellow-600 mr-1"></i>
                                        Konfirmasi Password
                                    </label>
                                    <div class="relative">
                                        <i class="bi bi-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                                        <input type="password" 
                                               name="password_confirmation"
                                               class="input-modern w-full pl-12 pr-4 py-3.5 rounded-xl font-semibold"
                                               placeholder="Ketik ulang password">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS - FULL WIDTH --}}
                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t-2 border-gray-100">
                        <a href="{{ route('kasir.dashboard') }}"
                           class="px-8 py-3.5 rounded-xl border-2 border-gray-300 font-bold text-gray-700 hover:bg-gray-50 transition-all active:scale-95">
                            <i class="bi bi-x-circle mr-2"></i>
                            Batal
                        </a>
                        <button type="submit"
                                class="btn-primary px-8 py-3.5 rounded-xl text-white font-bold active:scale-95 flex items-center gap-2">
                            <i class="bi bi-save-fill text-lg"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TIPS SECTION - HORIZONTAL --}}
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow-lg tips-card">
                <div class="flex items-center gap-4">
                    <div class="icon-yellow w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-shield-check text-white text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm mb-1">Password Kuat</h4>
                        <p class="text-xs text-gray-600">Kombinasi huruf, angka & simbol</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-lg tips-card">
                <div class="flex items-center gap-4">
                    <div class="icon-yellow w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-telephone-fill text-white text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm mb-1">Nomor Aktif</h4>
                        <p class="text-xs text-gray-600">Untuk notifikasi penting</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-lg tips-card">
                <div class="flex items-center gap-4">
                    <div class="icon-yellow w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-person-check text-white text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm mb-1">Data Akurat</h4>
                        <p class="text-xs text-gray-600">Mudahkan verifikasi akun</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview foto
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const currentPhoto = document.getElementById('currentPhoto');
            if (currentPhoto.tagName === 'IMG') {
                currentPhoto.src = e.target.result;
            } else {
                currentPhoto.outerHTML = `<img src="${e.target.result}" alt="Preview" id="currentPhoto" class="w-full h-full object-cover">`;
            }
        }
        reader.readAsDataURL(file);
    }
});

// Submit form
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-2"></i>Menyimpan...';
    
    try {
        const response = await fetch('{{ route("profile.kasir.update") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.status) {
            showAlert('success', data.message);
            
            // Reset password fields
            this.querySelector('input[name="password"]').value = '';
            this.querySelector('input[name="password_confirmation"]').value = '';
            
            // Redirect after 1.5 seconds
            setTimeout(() => {
                window.location.href = '{{ route("kasir.dashboard") }}';
            }, 1500);
        } else {
            showAlert('error', data.message || 'Gagal menyimpan perubahan');
        }
    } catch (error) {
        showAlert('error', 'Terjadi kesalahan pada server');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

function showAlert(type, message) {
    const container = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    const bgClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const textClass = type === 'success' ? 'text-green-800' : 'text-red-800';
    
    container.innerHTML = `
        <div class="${alertClass} px-6 py-4 rounded-2xl mb-6 flex items-center gap-3">
            <div class="w-10 h-10 ${bgClass} rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi ${iconClass} text-white text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="font-bold ${textClass}">${message}</p>
            </div>
        </div>
    `;
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}
</script>

@endsection