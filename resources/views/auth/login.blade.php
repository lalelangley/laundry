<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KasminiLaundry</title>
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Circles */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .circle:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 20%;
            animation-delay: 0s;
            animation-duration: 15s;
        }

        .circle:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
            animation-duration: 18s;
        }

        .circle:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 40%;
            left: 10%;
            animation-delay: 4s;
            animation-duration: 20s;
        }

        .circle:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 80%;
            left: 30%;
            animation-delay: 1s;
            animation-duration: 16s;
        }

        .circle:nth-child(5) {
            width: 90px;
            height: 90px;
            top: 20%;
            left: 70%;
            animation-delay: 3s;
            animation-duration: 22s;
        }

        .circle:nth-child(6) {
            width: 70px;
            height: 70px;
            top: 70%;
            left: 60%;
            animation-delay: 5s;
            animation-duration: 19s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0.3;
            }
            25% {
                transform: translateY(-50px) translateX(30px) scale(1.1);
                opacity: 0.5;
            }
            50% {
                transform: translateY(-100px) translateX(-30px) scale(0.9);
                opacity: 0.4;
            }
            75% {
                transform: translateY(-50px) translateX(50px) scale(1.05);
                opacity: 0.6;
            }
        }

        /* Floating Animation for Logo */
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .float-animation {
            animation: logoFloat 3s ease-in-out infinite;
        }

        /* Content Container */
        .content-wrapper {
            position: relative;
            z-index: 10;
        }

        /* Shimmer Effect on Card */
        .shimmer-card {
            position: relative;
            overflow: hidden;
        }

        .shimmer-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.1) 50%,
                transparent 70%
            );
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen px-4">
    
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <!-- Content -->
    <div class="w-full max-w-md content-wrapper">
        <!-- Back Button -->
        <a href="{{ route('landing') }}" 
           class="inline-flex items-center mb-4 px-4 py-2 bg-white bg-opacity-90 hover:bg-opacity-100 rounded-full shadow-lg hover:shadow-xl transition duration-300 transform hover:-translate-y-0.5">
            <i class="fas fa-arrow-left text-yellow-600 mr-2"></i>
            <span class="text-gray-800 font-semibold">Kembali</span>
        </a>

        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden shimmer-card">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 px-8 py-6 text-center relative overflow-hidden">
                <!-- Animated waves background -->
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
                <!-- Error Message -->
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg animate-pulse">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-3 rounded-lg animate-pulse">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login.process') }}" class="space-y-4">
                    @csrf

                    <!-- User Selection -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">
                            <i class="fas fa-user text-yellow-500 mr-1"></i>
                            Pilih Pengguna
                        </label>
                        <div class="relative">
                            <select name="user_id" 
                                    class="w-full px-4 py-3 pr-10 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition duration-200 appearance-none bg-white" 
                                    required>
                                <option value="">Pilih pengguna...</option>
                                
                                <optgroup label="Super Admin">
                                    @foreach($admins->where('role_id', 1) as $superAdmin)
                                        <option value="admin-{{ $superAdmin->id_admin }}">
                                            {{ $superAdmin->nama }}
                                        </option>
                                    @endforeach
                                </optgroup>

                                <optgroup label="Admin">
                                    @foreach($admins->where('role_id', '!=', 1) as $admin)
                                        <option value="admin-{{ $admin->id_admin }}">
                                            {{ $admin->nama }}
                                        </option>
                                    @endforeach
                                </optgroup>

                                <optgroup label="Kasir">
                                    @foreach($kasirs as $kasir)
                                        <option value="kasir-{{ $kasir->id_kasir }}">
                                            {{ $kasir->nama_kasir }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- PIN Input -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">
                            <i class="fas fa-lock text-yellow-500 mr-1"></i>
                            PIN
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   name="pin"
                                   id="pin-input"
                                   class="w-full px-4 py-3 pr-10 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition duration-200 tracking-widest text-center text-lg font-semibold"
                                   placeholder="• • • • • • • •"
                                   required
                                   maxlength="8">
                            <button type="button"
                                    onclick="togglePassword()"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                <i class="fas fa-eye" id="toggle-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:scale-98 transition duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        LOGIN
                    </button>
                </form>

                <!-- Footer Info -->
                <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600 text-xs flex items-center justify-center">
                        <i class="fas fa-shield-alt text-yellow-500 mr-2"></i>
                        Login aman dengan PIN terenkripsi
                    </p>
                </div>
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-4 text-center">
            <p class="text-white text-sm bg-black bg-opacity-20 backdrop-blur-sm rounded-full px-4 py-2 inline-block">
                <i class="fas fa-info-circle mr-1"></i>
                Hubungi admin jika lupa PIN
            </p>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function togglePassword() {
            const pinInput = document.getElementById('pin-input');
            const toggleIcon = document.getElementById('toggle-icon');
            
            if (pinInput.type === 'password') {
                pinInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                pinInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-focus on first input
        window.addEventListener('load', function() {
            document.querySelector('select[name="user_id"]').focus();
        });

        // Add number validation for PIN
        document.getElementById('pin-input').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>