<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KasminiLaundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }
        .bg-animation{position:fixed;top:0;left:0;width:100%;height:100%;overflow:hidden;z-index:0;}
        .circle{position:absolute;border-radius:50%;background:rgba(255,255,255,0.1);animation:float 20s infinite ease-in-out;}
        .circle:nth-child(1){width:80px;height:80px;top:10%;left:20%;animation-duration:15s;}
        .circle:nth-child(2){width:120px;height:120px;top:60%;left:80%;animation-delay:2s;animation-duration:18s;}
        .circle:nth-child(3){width:60px;height:60px;top:40%;left:10%;animation-delay:4s;animation-duration:20s;}
        .circle:nth-child(4){width:100px;height:100px;top:80%;left:30%;animation-delay:1s;animation-duration:16s;}
        .circle:nth-child(5){width:90px;height:90px;top:20%;left:70%;animation-delay:3s;animation-duration:22s;}
        .circle:nth-child(6){width:70px;height:70px;top:70%;left:60%;animation-delay:5s;animation-duration:19s;}
        @keyframes float{
            0%,100%{transform:translateY(0) translateX(0) scale(1);opacity:0.3;}
            25%{transform:translateY(-50px) translateX(30px) scale(1.1);opacity:0.5;}
            50%{transform:translateY(-100px) translateX(-30px) scale(0.9);opacity:0.4;}
            75%{transform:translateY(-50px) translateX(50px) scale(1.05);opacity:0.6;}
        }
        @keyframes logoFloat{0%,100%{transform:translateY(0px);}50%{transform:translateY(-8px);}}
        .float-animation{animation:logoFloat 3s ease-in-out infinite;}
        .content-wrapper{position:relative;z-index:10;}
        .shimmer-card{position:relative;overflow:hidden;}
        .shimmer-card::before{
            content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;
            background:linear-gradient(45deg,transparent 30%,rgba(255,255,255,0.1) 50%,transparent 70%);
            animation:shimmer 3s infinite;
        }
        @keyframes shimmer{
            0%{transform:translateX(-100%) translateY(-100%) rotate(45deg);}
            100%{transform:translateX(100%) translateY(100%) rotate(45deg);}
        }
        @keyframes slideIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
        .slide-in{animation:slideIn 0.3s ease forwards;}
        .input-error{border-color:#ef4444!important;}
    </style>
</head>
<body class="flex justify-center items-center min-h-screen px-4">

    <div class="bg-animation">
        <div class="circle"></div><div class="circle"></div><div class="circle"></div>
        <div class="circle"></div><div class="circle"></div><div class="circle"></div>
    </div>

    <div class="w-full max-w-md content-wrapper">

        <!-- Back Button -->
        <a href="{{ route('landing') }}"
           class="inline-flex items-center mb-4 px-4 py-2 bg-white bg-opacity-90 hover:bg-opacity-100 rounded-full shadow-lg hover:shadow-xl transition duration-300 transform hover:-translate-y-0.5">
            <i class="fas fa-arrow-left text-yellow-600 mr-2"></i>
            <span class="text-gray-800 font-semibold">Kembali</span>
        </a>

        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden shimmer-card">

            <!-- Header -->
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 px-8 py-6 text-center relative overflow-hidden">
                <div class="absolute inset-0 opacity-20">
                    <svg class="absolute bottom-0 w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                        <path fill="#ffffff" fill-opacity="0.5" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,128C1248,117,1344,107,1392,101.3L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                    </svg>
                </div>
                <div class="flex justify-center mb-3 float-animation relative z-10">
                    <div class="bg-white w-20 h-20 rounded-full shadow-lg flex items-center justify-center ring-4 ring-yellow-300 ring-opacity-50">
                        <i class="fas fa-tshirt text-4xl text-yellow-500"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white relative z-10">KasminiLaundry</h1>
                <p class="text-yellow-50 text-sm mt-1 relative z-10">Sistem Manajemen Laundry</p>
            </div>

            <!-- Form Section -->
            <div class="px-8 py-6">

                {{-- Alert JS Validation --}}
                <div id="js-alert" class="hidden mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <p id="js-alert-msg" class="text-red-700 text-sm font-medium"></p>
                    </div>
                </div>

                {{-- Server Error --}}
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Server Success --}}
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Laravel Validation Errors --}}
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2 mt-0.5"></i>
                            <ul class="text-red-700 text-sm font-medium space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.process') }}" onsubmit="return validateForm()">
                    @csrf

                    {{-- Step 1: Pilih Role --}}
                    <div class="mb-5">
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">
                            <i class="fas fa-users text-yellow-500 mr-1"></i>
                            Pilih Role
                        </label>
                        <div class="relative">
                            <select id="role-select" name="login_type"
                                onchange="onRoleChange(this.value)"
                                class="w-full px-4 py-3 pr-10 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition duration-200 appearance-none bg-white">
                                <option value="">-- Pilih role pengguna --</option>
                                <option value="admin"  {{ old('login_type') == 'admin'  ? 'selected' : '' }}>Admin / Super Admin</option>
                                <option value="kasir"  {{ old('login_type') == 'kasir'  ? 'selected' : '' }}>Kasir</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                   {{-- Form Admin --}}
<div id="form-admin" class="hidden space-y-4">
    <div>
        <label class="block text-gray-700 font-semibold mb-2 text-sm">
            <i class="fas fa-envelope text-yellow-500 mr-1"></i> Email
        </label>
        <input type="email" name="email" id="input-email"
            value="{{ old('email') }}"
            autocomplete="email"
            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition duration-200"
            placeholder="Masukkan email...">
    </div>
    <div>
        <label class="block text-gray-700 font-semibold mb-2 text-sm">
            <i class="fas fa-lock text-yellow-500 mr-1"></i> Password
        </label>
        <div class="relative">
            {{-- Ganti name jadi password_admin --}}
            <input type="password" name="password_admin" id="input-password-admin"
                autocomplete="current-password"
                class="w-full px-4 py-3 pr-10 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition duration-200"
                placeholder="Masukkan password...">
            <button type="button" onclick="togglePassword('input-password-admin','icon-admin')"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-eye" id="icon-admin"></i>
            </button>
        </div>
    </div>
</div>

{{-- Form Kasir --}}
<div id="form-kasir" class="hidden space-y-4">
    <div>
        <label class="block text-gray-700 font-semibold mb-2 text-sm">
            <i class="fas fa-phone text-yellow-500 mr-1"></i> No. HP
        </label>
        <input type="text" name="no_hp" id="input-nohp"
            value="{{ old('no_hp') }}"
            autocomplete="tel"
            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition duration-200"
            placeholder="Contoh: 08123456789">
    </div>
    <div>
        <label class="block text-gray-700 font-semibold mb-2 text-sm">
            <i class="fas fa-lock text-yellow-500 mr-1"></i> Password
        </label>
        <div class="relative">
            {{-- Ganti name jadi password_kasir --}}
            <input type="password" name="password_kasir" id="input-password-kasir"
                autocomplete="current-password"
                class="w-full px-4 py-3 pr-10 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition duration-200"
                placeholder="Masukkan password...">
            <button type="button" onclick="togglePassword('input-password-kasir','icon-kasir')"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-eye" id="icon-kasir"></i>
            </button>
        </div>
    </div>
</div>

                    {{-- Submit Button --}}
                    <div class="mt-5">
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            <span id="btn-label">LOGIN</span>
                        </button>
                    </div>
                </form>

                <!-- Footer -->
                <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600 text-xs flex items-center justify-center">
                        <i class="fas fa-shield-alt text-yellow-500 mr-2"></i>
                        Login aman dengan password terenkripsi
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p class="text-white text-sm bg-black bg-opacity-20 backdrop-blur-sm rounded-full px-4 py-2 inline-block">
                <i class="fas fa-info-circle mr-1"></i>
                Hubungi admin jika lupa password
            </p>
        </div>
    </div>

    <script>
        // Auto restore form saat ada old input (setelah validasi gagal)
        document.addEventListener('DOMContentLoaded', function () {
            const role = document.getElementById('role-select').value;
            if (role) onRoleChange(role);
        });

        function onRoleChange(role) {
            const formAdmin = document.getElementById('form-admin');
            const formKasir = document.getElementById('form-kasir');
            const btnLabel  = document.getElementById('btn-label');

            resetErrors();
            hideAlert();

            if (role === 'admin') {
                formAdmin.classList.remove('hidden');
                formAdmin.classList.add('slide-in');
                formKasir.classList.add('hidden');
                btnLabel.textContent = 'LOGIN ADMIN';
            } else if (role === 'kasir') {
                formKasir.classList.remove('hidden');
                formKasir.classList.add('slide-in');
                formAdmin.classList.add('hidden');
                btnLabel.textContent = 'LOGIN KASIR';
            } else {
                formAdmin.classList.add('hidden');
                formKasir.classList.add('hidden');
                btnLabel.textContent = 'LOGIN';
            }
        }

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function validateForm() {
            const role     = document.getElementById('role-select').value;
            const messages = [];
            let isValid    = true;

            resetErrors();
            hideAlert();

            if (!role) {
                document.getElementById('role-select').classList.add('input-error');
                messages.push('role pengguna');
                isValid = false;
            }

            if (role === 'admin') {
                const email    = document.getElementById('input-email');
                const password = document.getElementById('input-password-admin');
                if (!email.value.trim()) {
                    email.classList.add('input-error');
                    messages.push('email');
                    isValid = false;
                }
                if (!password.value.trim()) {
                    password.classList.add('input-error');
                    messages.push('password');
                    isValid = false;
                }
            } else if (role === 'kasir') {
                const nohp     = document.getElementById('input-nohp');
                const password = document.getElementById('input-password-kasir');
                if (!nohp.value.trim()) {
                    nohp.classList.add('input-error');
                    messages.push('no. HP');
                    isValid = false;
                }
                if (!password.value.trim()) {
                    password.classList.add('input-error');
                    messages.push('password');
                    isValid = false;
                }
            }

            if (!isValid) {
                showAlert('Harap isi ' + messages.join(' dan ') + ' terlebih dahulu.');
                return false;
            }

            return true;
        }

        function showAlert(msg) {
            const el = document.getElementById('js-alert');
            document.getElementById('js-alert-msg').textContent = msg;
            el.classList.remove('hidden');
        }

        function hideAlert() {
            document.getElementById('js-alert').classList.add('hidden');
        }

        function resetErrors() {
            ['role-select','input-email','input-password-admin','input-nohp','input-password-kasir']
                .forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.remove('input-error');
                });
        }

        window.addEventListener('load', () => {
            document.getElementById('role-select').focus();
        });
    </script>
</body>
</html>