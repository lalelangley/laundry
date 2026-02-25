<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry - Solusi Laundry Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #EEF6AE 0%, #FFC21A 100%); }
        .hero-pattern {
            background-color: #f5f5f5;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23fbbf24' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .stat-card { backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.9); }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        .float-animation { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="hero-pattern">

    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="bg-yellow-400 p-2 rounded-lg">
                        <i class="fas fa-tshirt text-2xl text-gray-800"></i>
                    </div>
                    <span class="text-2xl font-bold text-gray-800">KasminiLaundry</span>
                </div>
                <a href="{{ route('login') }}" 
                   class="bg-yellow-400 hover:bg-yellow-500 text-gray-800 px-6 py-2 rounded-full font-semibold transition duration-300 shadow-md hover:shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <h1 class="text-5xl md:text-6xl font-bold text-gray-800 leading-tight mb-6">
                        Solusi <span class="text-yellow-400">Laundry</span> Modern & Efisien
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        Kelola bisnis laundry Anda dengan mudah. Sistem terintegrasi untuk manajemen pesanan, pelanggan, dan transaksi.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('login') }}" 
                           class="bg-yellow-400 hover:bg-yellow-500 text-gray-800 px-8 py-4 rounded-full font-semibold text-lg transition duration-300 shadow-lg hover:shadow-xl inline-flex items-center justify-center">
                            <i class="fas fa-rocket mr-2"></i>Mulai Sekarang
                        </a>
                        <a href="#features" 
                           class="bg-white hover:bg-gray-50 text-gray-800 px-8 py-4 rounded-full font-semibold text-lg transition duration-300 shadow-lg hover:shadow-xl inline-flex items-center justify-center border-2 border-gray-200">
                            <i class="fas fa-info-circle mr-2"></i>Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>

                <div class="relative hidden lg:block">
                    <div class="float-animation flex justify-center">
                        <div class="bg-yellow-400 w-80 h-80 rounded-full flex items-center justify-center shadow-2xl">
                            <i class="fas fa-tshirt text-9xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-gradient-to-r from-yellow-400 to-yellow-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-gray-800 mb-2">{{ $pesanan_hari_ini }}</div>
                    <p class="text-gray-700 font-medium">Pesanan Hari Ini</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-gray-800 mb-2">{{ $dalam_proses }}</div>
                    <p class="text-gray-700 font-medium">Dalam Proses</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-gray-800 mb-2">{{ $selesai }}</div>
                    <p class="text-gray-700 font-medium">Selesai</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-gray-800 mb-2">{{ $total_pelanggan }}</div>
                    <p class="text-gray-700 font-medium">Total Pelanggan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Fitur Unggulan</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Semua yang Anda butuhkan untuk mengelola bisnis laundry dalam satu platform
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="stat-card p-8 rounded-2xl shadow-xl card-hover">
                    <div class="bg-yellow-400 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-shopping-cart text-2xl text-gray-800"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Manajemen Pesanan</h3>
                    <p class="text-gray-600">Kelola pesanan online dan offline dengan mudah. Tracking real-time dari penerimaan hingga pengiriman.</p>
                </div>

                <div class="stat-card p-8 rounded-2xl shadow-xl card-hover">
                    <div class="bg-yellow-400 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-users text-2xl text-gray-800"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Database Pelanggan</h3>
                    <p class="text-gray-600">Simpan dan kelola data pelanggan dengan aman. Riwayat transaksi lengkap untuk setiap pelanggan.</p>
                </div>

                <div class="stat-card p-8 rounded-2xl shadow-xl card-hover">
                    <div class="bg-yellow-400 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-2xl text-gray-800"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Laporan & Analytics</h3>
                    <p class="text-gray-600">Dashboard lengkap dengan grafik dan laporan penjualan. Pantau performa bisnis Anda secara real-time.</p>
                </div>

                <div class="stat-card p-8 rounded-2xl shadow-xl card-hover">
                    <div class="bg-yellow-400 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-money-bill-wave text-2xl text-gray-800"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Pembayaran Fleksibel</h3>
                    <p class="text-gray-600">Support berbagai metode pembayaran. Tracking status pembayaran lunas atau belum lunas.</p>
                </div>

                <div class="stat-card p-8 rounded-2xl shadow-xl card-hover">
                    <div class="bg-yellow-400 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-truck text-2xl text-gray-800"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Pickup & Delivery</h3>
                    <p class="text-gray-600">Manajemen driver untuk pickup dan pengantaran. Notifikasi otomatis untuk pelanggan.</p>
                </div>

                <div class="stat-card p-8 rounded-2xl shadow-xl card-hover">
                    <div class="bg-yellow-400 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-user-shield text-2xl text-gray-800"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Multi-User Access</h3>
                    <p class="text-gray-600">Sistem role-based access untuk Admin dan Kasir. Keamanan data terjamin dengan PIN login.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Business Metrics -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Performa Bisnis</h2>
                <p class="text-xl text-gray-600">Statistik bulan ini</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-8 text-white shadow-2xl card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <i class="fas fa-receipt text-4xl opacity-50"></i>
                        <div class="text-right">
                            <p class="text-yellow-100 text-sm font-medium">Total Transaksi</p>
                            <p class="text-4xl font-bold">{{ $transaksi_bulan_ini }}</p>
                        </div>
                    </div>
                    <p class="text-yellow-100">Transaksi bulan ini</p>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-8 text-white shadow-2xl card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <i class="fas fa-dollar-sign text-4xl opacity-50"></i>
                        <div class="text-right">
                            <p class="text-yellow-100 text-sm font-medium">Pendapatan</p>
                            <p class="text-4xl font-bold">Rp {{ number_format($pendapatan_bulan_ini, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <p class="text-yellow-100">Pendapatan bulan ini</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Siap Tingkatkan Bisnis Laundry Anda?</h2>
            <p class="text-xl text-white mb-10 opacity-90">Bergabunglah dengan sistem manajemen laundry yang telah dipercaya</p>
            <a href="{{ route('login') }}" 
               class="bg-white hover:bg-gray-100 text-yellow-600 px-10 py-5 rounded-full font-bold text-xl transition duration-300 shadow-2xl hover:shadow-3xl inline-flex items-center">
                <i class="fas fa-sign-in-alt mr-3"></i>Masuk ke Dashboard
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="bg-yellow-400 p-2 rounded-lg">
                            <i class="fas fa-tshirt text-2xl text-gray-800"></i>
                        </div>
                        <span class="text-2xl font-bold">KasminiLaundry</span>
                    </div>
                    <p class="text-gray-400">Solusi terpadu untuk manajemen bisnis laundry modern dan efisien.</p>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-yellow-400 transition">Fitur</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-yellow-400 transition">Login</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4">Kontak</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i> info@KasminiLaundry.com</li>
                        <li><i class="fas fa-phone mr-2"></i> +62 123 4567 890</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Jakarta, Indonesia</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 KasminiLaundry. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>