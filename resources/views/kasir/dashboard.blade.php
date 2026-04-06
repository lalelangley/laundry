@extends('layouts.master')

@section('title', 'Dashboard kasir')

@section('content')

<!-- HEADER -->
<div class="bg-[#ffcc00] p-4 rounded-b-3xl shadow-lg">
    <div class="flex items-center justify-between">
        <!-- Toggle Sidebar -->
        <div onclick="toggleSidebar()" class="text-3xl font-bold cursor-pointer">≡</div>

        <!-- Logo -->
        <img src="{{ asset('images/dashboard_logo.png') }}" class="w-40" alt="logo">

        <!-- Notification Bell -->
        <div class="relative">
            <button onclick="toggleNotifications()" class="relative bg-white w-12 h-12 flex items-center justify-center rounded-full text-2xl text-gray-800 shadow hover:bg-gray-100 transition">
                <i class="bi bi-bell-fill"></i>
               @php
                    $totalNotif = 0;
                    if(isset($transaksiMasukHariIni)) $totalNotif += $transaksiMasukHariIni;
                    if(isset($belumLunas)) $totalNotif += $belumLunas;
                    if(isset($butuhPickup)) $totalNotif += $butuhPickup;
                    if(isset($butuhAntar)) $totalNotif += $butuhAntar;
                    if(isset($terlambatOnline)) $totalNotif += $terlambatOnline;
                    if(isset($harusSelesaiHariIni)) $totalNotif += $harusSelesaiHariIni;
                    if(isset($siapDiambil)) $totalNotif += $siapDiambil;  // ✅ TAMBAHKAN INI!
                @endphp
                @if($totalNotif > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center animate-pulse">
                    {{ $totalNotif > 99 ? '99+' : $totalNotif }}
                </span>
                @endif
            </button>
        </div>
    </div>

    <!-- Laporan Hari Ini -->
    <div class="mt-4 text-[14px] font-semibold flex justify-between">
        <div>
            <div class="flex items-center gap-2">
                <i class="bi bi-cart-fill text-lg"></i>
                Laporan Hari ini
            </div>
            <div>Total Omzet : Rp.{{ number_format($totalOmzet,0,',','.') }}</div>
        </div>
    </div>
</div>

<!-- USER BAR -->
<div class="mx-4 mt-4 flex items-center justify-end gap-3">
   <!-- Edit Profile -->
    <a href="{{ route('profile.kasir.edit') }}"
    class="bg-gray-300 w-12 h-12 flex items-center justify-center rounded-full text-2xl text-black shadow hover:bg-gray-400 transition">
        <i class="bi bi-person-fill"></i>
    </a>

    <!-- Logout -->
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" 
            class="bg-red-500 w-12 h-12 flex items-center justify-center rounded-full text-white text-2xl shadow hover:bg-red-600 transition">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>

    <!-- Role -->
    <div class="bg-[#ffcc00] px-5 py-3 rounded-xl text-black text-sm font-bold shadow">
       Kasir
    </div>
</div>

{{-- ========================================
     TAMBAHKAN POPUP REMINDER SETELAH HEADER
     Letakkan setelah <!-- USER BAR -->
     ======================================== --}}
{{-- 🚨 AUTO POPUP REMINDER - Muncul saat login --}}
<div id="reminderPopup" class="hidden fixed inset-0 bg-gradient-to-br from-black/20 via-gray-900/15 to-black/20 z-[60] flex items-center justify-center p-4 animate-fade-in">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden transform animate-scale-in">
        {{-- HEADER --}}
        <div class="px-8 py-6 bg-gradient-to-r from-yellow-400 to-amber-500">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="bi bi-bell-fill text-white text-3xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">🔔 Reminder!</h2>
                        <p class="text-sm text-gray-800 mt-1">Jangan lupa untuk memproses pesanan</p>
                    </div>
                </div>
                <button onclick="closeReminderPopup()" class="text-gray-800 hover:text-gray-900 transition">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
        </div>

        {{-- CONTENT - SIMPLE REMINDER MESSAGE --}}
        <div class="px-8 py-10 text-center">
            <div class="text-6xl mb-4">⏰</div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Ada pesanan yang perlu diproses!</h3>
            <p class="text-gray-600 mb-2">Pastikan semua pesanan ditangani dengan baik.</p>
            <p class="text-sm text-gray-500">Cek detail di panel notifikasi untuk info lengkap.</p>
        </div>

        {{-- FOOTER ACTIONS --}}
        <div class="px-8 pb-6">
            <button onclick="handleReminderAction()" 
                    class="w-full px-6 py-4 rounded-2xl font-bold text-white shadow-lg hover:shadow-xl transition-all transform hover:scale-105 bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600">
                <i class="bi bi-check-circle-fill mr-2"></i>
                <span>Saya Sudah Mengerti</span>
            </button>

            {{-- CHECKBOX: Jangan tampilkan lagi hari ini --}}
            <div class="mt-4 flex items-center justify-center gap-2 text-sm text-gray-600">
                <input type="checkbox" id="dontShowAgain" class="w-4 h-4 rounded border-gray-300">
                <label for="dontShowAgain" class="cursor-pointer">Jangan tampilkan lagi hari ini</label>
            </div>
        </div>
    </div>
</div>

{{-- NOTIFICATION PANEL - SLIDE FROM TOP --}}
<div id="notificationPanel" class="fixed top-0 left-0 right-0 bg-white shadow-2xl z-50 transform -translate-y-full transition-transform duration-300 max-h-[80vh] overflow-y-auto">
    <div class="p-6">
        <!-- Header Panel -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-[#ffcc00] rounded-xl flex items-center justify-center">
                    <i class="bi bi-bell-fill text-gray-900 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Notifikasi Penting</h3>
                    <p class="text-sm text-gray-500">{{ $totalNotif }} notifikasi memerlukan perhatian</p>
                </div>
            </div>
            <button onclick="toggleNotifications()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Notification List -->
        <div class="space-y-3">
            {{-- URGENT: Pesanan Terlambat (MERAH) --}}
            @if($terlambat > 0)
            <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer" onclick="handleNotification('terlambat')">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center flex-shrink-0 animate-pulse">
                        <i class="bi bi-exclamation-octagon-fill text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase">Urgent</span>
                            <h4 class="text-base font-bold text-gray-900">Pesanan Terlambat</h4>
                        </div>
                        <p class="text-sm text-gray-700">
                            <span class="font-bold text-red-600">{{ $terlambat }}</span> pesanan melewati batas estimasi selesai
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk lihat dan proses
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            {{-- HIGH: Driver Pickup (ORANGE) --}}
            @if($butuhPickup > 0)
            <div class="bg-orange-50 border-l-4 border-orange-500 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer" onclick="handleNotification('pickup')">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 animate-bounce">
                        <i class="bi bi-box-arrow-in-down text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase">Perlu Driver</span>
                            <h4 class="text-base font-bold text-gray-900">Pickup Menunggu</h4>
                        </div>
                        <p class="text-sm text-gray-700">
                            <span class="font-bold text-orange-600">{{ $butuhPickup }}</span> transaksi perlu driver untuk penjemputan
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk assign driver
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            {{-- HIGH: Driver Antar (ORANGE) --}}
            @if($butuhAntar > 0)
            <div class="bg-orange-50 border-l-4 border-orange-500 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer" onclick="handleNotification('antar')">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 animate-bounce">
                        <i class="bi bi-box-arrow-up text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase">Perlu Driver</span>
                            <h4 class="text-base font-bold text-gray-900">Pengantaran Menunggu</h4>
                        </div>
                        <p class="text-sm text-gray-700">
                            <span class="font-bold text-orange-600">{{ $butuhAntar }}</span> transaksi perlu driver untuk pengantaran
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk assign driver
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            {{-- MEDIUM: Belum Lunas (KUNING) --}}
            @if($belumLunas > 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer" onclick="handleNotification('belum_lunas')">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-wallet2 text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase">Pembayaran</span>
                            <h4 class="text-base font-bold text-gray-900">Belum Lunas</h4>
                        </div>
                        <p class="text-sm text-gray-700">
                            <span class="font-bold text-yellow-600">{{ $belumLunas }}</span> transaksi menunggu pelunasan
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk lihat tagihan
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            {{-- INFO: Transaksi Masuk (KUNING) --}}
            @if($transaksiMasukHariIni > 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer" onclick="handleNotification('masuk')">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-inbox-fill text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase">Baru</span>
                            <h4 class="text-base font-bold text-gray-900">Transaksi Masuk</h4>
                        </div>
                        <p class="text-sm text-gray-700">
                            <span class="font-bold text-yellow-600">{{ $transaksiMasukHariIni }}</span> transaksi baru masuk hari ini
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk proses
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            {{-- INFO: Deadline Hari Ini (KUNING) --}}
            @if($harusSelesaiHariIni > 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer" onclick="handleNotification('deadline')">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-alarm-fill text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase">Deadline</span>
                            <h4 class="text-base font-bold text-gray-900">Harus Selesai Hari Ini</h4>
                        </div>
                        <p class="text-sm text-gray-700">
                            <span class="font-bold text-yellow-600">{{ $harusSelesaiHariIni }}</span> pesanan dengan deadline hari ini
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk prioritaskan
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            {{-- SUCCESS: Siap Diambil (HIJAU) --}}
            @if($siapDiambil > 0)
            <div class="bg-green-50 border-l-4 border-green-500 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer" onclick="handleNotification('siap_ambil')">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-bag-check-fill text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase">Siap</span>
                            <h4 class="text-base font-bold text-gray-900">Siap Diambil</h4>
                        </div>
                        <p class="text-sm text-gray-700">
                            <span class="font-bold text-green-600">{{ $siapDiambil }}</span> pesanan siap diambil pelanggan
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk hubungi pelanggan
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            {{-- SUCCESS: Pembayaran Lunas (HIJAU) --}}
            @if($pembayaranLunasHariIni > 0)
            <div class="bg-green-50 border-l-4 border-green-500 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer" onclick="handleNotification('lunas')">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-check-circle-fill text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase">Lunas</span>
                            <h4 class="text-base font-bold text-gray-900">Pembayaran Lunas</h4>
                        </div>
                        <p class="text-sm text-gray-700">
                            <span class="font-bold text-green-600">{{ $pembayaranLunasHariIni }}</span> pembayaran lunas hari ini
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk lihat detail
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            {{-- Empty State --}}
            @if($totalNotif == 0)
            <div class="text-center py-10">
                <i class="bi bi-check-circle text-6xl text-green-500 mb-3"></i>
                <p class="text-xl font-bold text-gray-900">Semua Lancar!</p>
                <p class="text-gray-500 mt-2">Tidak ada notifikasi yang memerlukan tindakan</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Statistik Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4 mt-6">
    <!-- Transaksi Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-red-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-basket-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalTransaksi }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Transaksi</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-red-500 to-red-300 rounded-full"></div>
    </div>

    <!-- Kasir Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-green-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-people-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalKasir }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Aktif</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Kasir</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-green-500 to-green-300 rounded-full"></div>
    </div>

    <!-- Pelanggan Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-yellow-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-person-vcard-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalPelanggan }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Pelanggan</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-yellow-400 to-yellow-300 rounded-full"></div>
    </div>
</div>

{{-- DATA TABLES --}}
<div class="mx-4 mt-8 mb-8 bg-white rounded-3xl shadow-xl border-2 border-gray-100">
    {{-- HEADER SECTION --}}
    <div class="px-8 py-6 border-b-2 border-gray-100 bg-gradient-to-r from-gray-50 to-white rounded-t-3xl">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="bi bi-clock-history text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500">Menampilkan {{ $orders->count() }} transaksi</p>
                </div>
            </div>

            <a href="{{ route('kasir.transaksi.create') }}"
               class="bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 transition-all px-6 py-3 rounded-2xl text-gray-900 font-bold shadow-lg hover:shadow-xl hover:scale-105 transform inline-flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-lg"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="p-8">
        <div class="overflow-x-auto rounded-2xl border-2 border-gray-200">
            <table id="orderTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 text-xs uppercase tracking-wider border-b-2 border-gray-200">
                        <th class="py-4 px-4 text-left font-bold">No</th>
                        <th class="py-4 px-4 text-left font-bold">ID Transaksi</th>
                        <th class="py-4 px-4 text-left font-bold">Pelanggan</th>
                        <th class="py-4 px-4 text-left font-bold">Kasir</th>
                        <th class="py-4 px-4 text-left font-bold">Jenis</th>
                        <th class="py-4 px-4 text-left font-bold">Metode Bayar</th>
                        <th class="py-4 px-4 text-left font-bold">Status Bayar</th>
                        <th class="py-4 px-4 text-left font-bold">Status Transaksi</th>
                        <th class="py-4 px-4 text-right font-bold">Total Harga</th>
                        <th class="py-4 px-4 text-right font-bold">Diskon</th>
                        <th class="py-4 px-4 text-right font-bold">DP</th>
                        <th class="py-4 px-4 text-right font-bold">Total Bayar</th>
                        <th class="py-4 px-4 text-left font-bold">Tgl Transaksi</th>
                        <th class="py-4 px-4 text-left font-bold">Tgl Estimasi</th>
                        <th class="py-4 px-4 text-left font-bold">Tgl Lunas</th>
                        <th class="py-4 px-4 text-left font-bold">Keterangan</th>
                        <th class="py-4 px-4 text-center font-bold">Bukti</th>
                        <th class="py-4 px-4 text-center font-bold">Action</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                    @forelse ($orders as $i => $o)
                    <tr class="border-b border-gray-100 hover:bg-yellow-50/80 transition-colors">
                        <td class="py-4 px-4 text-gray-600 font-medium">{{ $i+1 }}</td>

                        <td class="py-4 px-4">
    <span class="font-bold text-gray-900">{{ $o->id_transaksi }}</span>
    
    @if($o->jenis_transaksi == 'online' && !in_array($o->status_transaksi, ['selesai', 'batal']))
        @php
            $deliveries = DB::table('delivery')->where('id_transaksi', $o->id_transaksi)->get();
            $hasPickupPending = $deliveries->where('jenis', 'pickup')->where('id_driver', null)->first();
            $hasAntarPending = $deliveries->where('jenis', 'antar')->where('id_driver', null)->first();
        @endphp
        
        @if($hasPickupPending || $hasAntarPending)
            <div class="mt-1 flex flex-wrap gap-1">
                @if($hasPickupPending)
                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                        <i class="bi bi-exclamation-circle-fill mr-1"></i> Pickup
                    </span>
                @endif
                @if($hasAntarPending)
                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                        <i class="bi bi-exclamation-circle-fill mr-1"></i> Antar
                    </span>
                @endif
            </div>
        @endif
    @endif
</td>

                        <td class="py-4 px-4">
                            <div class="min-w-[120px]">
                                <div class="font-semibold text-gray-900">{{ $o->nama_pelanggan ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $o->no_hp ?? '-' }}</div>
                                @if($o->id_pelanggan)
                                    <div class="text-xs text-gray-400">ID: {{ $o->id_pelanggan }}</div>
                                @endif
                            </div>
                        </td>

                        <td class="py-4 px-4">
                            @if($o->id_kasir)
                                @php
                                    try {
                                        $kasir = DB::table('akun_kasir')->where('id_kasir', $o->id_kasir)->first();
                                        if (!$kasir) {
                                            $kasir = DB::table('kasir')->where('id_kasir', $o->id_kasir)->first();
                                        }
                                    } catch (\Exception $e) {
                                        $kasir = null;
                                    }
                                @endphp
                                <div class="text-xs">
                                    @if($kasir)
                                        <div class="font-semibold text-gray-900">{{ $kasir->nama_kasir ?? $kasir->username ?? $kasir->name ?? 'Kasir' }}</div>
                                    @else
                                        <div class="font-semibold text-gray-900">Kasir</div>
                                    @endif
                                    <div class="text-gray-500">ID: {{ $o->id_kasir }}</div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @if($o->jenis_transaksi == 'online')
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="bi bi-globe mr-1"></i> Online
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="bi bi-shop mr-1"></i> Offline
                                </span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @if($o->id_metode_bayar)
                                @php
                                    $metodeBayar = DB::table('metode_bayar')->where('id_metode_bayar', $o->id_metode_bayar)->first();
                                @endphp
                                <div class="text-xs min-w-[100px]">
                                    <div class="font-semibold text-gray-900">{{ $metodeBayar->nama_metode_bayar ?? '-' }}</div>
                                    <div class="text-gray-500">ID: {{ $o->id_metode_bayar }}</div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Belum diisi</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @if($o->status_bayar)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap
                                    {{ $o->status_bayar == 'lunas' ? 'bg-green-100 text-green-800' : ($o->status_bayar == 'DP' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $o->status_bayar == 'belum_lunas' ? 'Belum Lunas' : ucfirst($o->status_bayar) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @php
                                $statusConfig = [
                                    'antrian' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Antrian'],
                                    'proses' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Proses'],
                                    'selesai_dicuci' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Selesai Dicuci'],
                                    'siap_di_ambil' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Siap Ambil'],
                                    'siap_di_antar' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Siap Antar'],
                                    'pick_up' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pick Up'],
                                    'selesai' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Selesai'],
                                    'batal' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Batal'],
                                ];
                                
                                $status = $statusConfig[$o->status_transaksi] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst(str_replace('_', ' ', $o->status_transaksi))];
                            @endphp
                            
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }} whitespace-nowrap">
                                {{ $status['label'] }}
                            </span>
                        </td>

                        <td class="py-4 px-4 text-right">
                            <div class="font-bold text-gray-900 whitespace-nowrap">
                                Rp {{ number_format($o->total_harga ?? 0, 0, ',', '.') }}
                            </div>
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if($o->diskon > 0)
                                <div class="font-semibold text-red-600 whitespace-nowrap">
                                    Rp {{ number_format($o->diskon, 0, ',', '.') }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if($o->dp > 0)
                                <div class="font-semibold text-yellow-600 whitespace-nowrap">
                                    Rp {{ number_format($o->dp, 0, ',', '.') }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            <div class="font-bold text-green-600 whitespace-nowrap">
                                Rp {{ number_format($o->total_bayar ?? 0, 0, ',', '.') }}
                            </div>
                        </td>

                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">
                            {{ $o->tgl_transaksi ? \Carbon\Carbon::parse($o->tgl_transaksi)->format('d/m/Y') : '-' }}
                        </td>

                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">
                            {{ $o->tgl_estimasi ? \Carbon\Carbon::parse($o->tgl_estimasi)->format('d/m/Y') : '-' }}
                        </td>

                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">
                            {{ $o->tgl_lunas ? \Carbon\Carbon::parse($o->tgl_lunas)->format('d/m/Y') : '-' }}
                        </td>

                        <td class="py-4 px-4">
                            @if($o->keterangan)
                                <div class="max-w-[150px] text-xs text-gray-700 truncate" title="{{ $o->keterangan }}">
                                    {{ $o->keterangan }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-center">
                            @if($o->foto_bukti)
                                <button onclick="showBuktiImage('{{ asset('storage/'.$o->foto_bukti) }}')" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition">
                                    <i class="bi bi-image"></i>
                                    <span>Lihat</span>
                                </button>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
    <div class="flex items-center justify-center gap-2 flex-nowrap min-w-[180px]">
        @if($o->jenis_transaksi == 'online')
            {{-- TRANSAKSI ONLINE - Route ke riwayat.detail --}}
            <a href="{{ route('kasir.pesanan.online.detail', ['id' => $o->id_transaksi, 'from' => 'dashboard']) }}"
               class="inline-flex items-center gap-1 bg-gradient-to-r from-yellow-400 to-amber-500 px-3 py-2 rounded-xl text-gray-900 text-xs font-bold hover:from-yellow-500 hover:to-amber-600 transition-all hover:shadow-lg hover:scale-105 whitespace-nowrap">
                <i class="bi bi-eye-fill"></i>
                <span>Detail</span>
            </a>
        @else
            {{-- TRANSAKSI OFFLINE - Route ke transaksi.detail --}}
            <a href="{{ route('kasir.riwayat.detail', ['id' => $o->id_transaksi]) }}"
               class="inline-flex items-center gap-1 bg-gradient-to-r from-yellow-400 to-amber-500 px-3 py-2 rounded-xl text-gray-900 text-xs font-bold hover:from-yellow-500 hover:to-amber-600 transition-all hover:shadow-lg hover:scale-105 whitespace-nowrap">
                <i class="bi bi-eye-fill"></i>
                <span>Detail</span>
            </a>
        @endif
    </div>
</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="18" class="py-10 text-center">
                            <div class="text-gray-400">
                                <i class="bi bi-inbox text-5xl mb-3 block"></i>
                                <p class="font-semibold">Belum ada transaksi</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- POPUP DELIVERY INFO --}}
<div id="deliveryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-truck text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Info Pickup & Delivery</h3>
                    <p class="text-sm text-orange-100">Transaksi #<span id="modalTransaksiId"></span></p>
                </div>
            </div>
            <button onclick="closeDeliveryModal()" class="text-white hover:bg-white/20 rounded-xl p-2 transition">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto max-h-[calc(90vh-100px)]">
            <div id="deliveryContent" class="space-y-4"></div>
        </div>
    </div>
</div>

{{-- POPUP BUKTI IMAGE --}}
<div id="buktiModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" onclick="closeBuktiModal()">
    <div class="relative max-w-4xl w-full" onclick="event.stopPropagation()">
        <button onclick="closeBuktiModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition">
            <i class="bi bi-x-lg text-3xl"></i>
        </button>
        <img id="buktiImage" src="" alt="Bukti Pembayaran" class="w-full h-auto rounded-2xl shadow-2xl">
    </div>
</div>

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
// ========================================
// DATA NOTIFIKASI DARI CONTROLLER
// ========================================
const notifications = {
    terlambat: {{ $terlambat ?? 0 }},
    butuhPickup: {{ $butuhPickup ?? 0 }},
    butuhAntar: {{ $butuhAntar ?? 0 }},
    harusSelesaiHariIni: {{ $harusSelesaiHariIni ?? 0 }},
    transaksiMasuk: {{ $transaksiMasukHariIni ?? 0 }},
    belumLunas: {{ $belumLunas ?? 0 }}
};

// ========================================
// CONFIG UNTUK SETIAP JENIS NOTIF
// ========================================
const notificationConfig = {
    terlambat: {
        priority: 1,
        icon: 'bi-exclamation-octagon-fill',
        iconBg: 'bg-red-500',
        borderColor: 'border-red-500',
        bgColor: 'bg-red-50',
        textColor: 'text-red-800',
        badgeClass: 'bg-red-500',
        title: 'Pesanan Terlambat',
        badge: 'URGENT',
        getMessage: (count) => `${count} pesanan melewati estimasi selesai`
    },
    butuhPickup: {
        priority: 2,
        icon: 'bi-box-arrow-in-down',
        iconBg: 'bg-orange-500',
        borderColor: 'border-orange-500',
        bgColor: 'bg-orange-50',
        textColor: 'text-orange-800',
        badgeClass: 'bg-orange-500',
        title: 'Butuh Driver Pickup',
        badge: 'PERLU DRIVER',
        getMessage: (count) => `${count} transaksi menunggu driver penjemputan`
    },
    butuhAntar: {
        priority: 3,
        icon: 'bi-box-arrow-up',
        iconBg: 'bg-orange-500',
        borderColor: 'border-orange-500',
        bgColor: 'bg-orange-50',
        textColor: 'text-orange-800',
        badgeClass: 'bg-orange-500',
        title: 'Butuh Driver Antar',
        badge: 'PERLU DRIVER',
        getMessage: (count) => `${count} transaksi menunggu driver pengantaran`
    },
    harusSelesaiHariIni: {
        priority: 4,
        icon: 'bi-alarm-fill',
        iconBg: 'bg-yellow-500',
        borderColor: 'border-yellow-500',
        bgColor: 'bg-yellow-50',
        textColor: 'text-yellow-800',
        badgeClass: 'bg-yellow-500',
        title: 'Deadline Hari Ini',
        badge: 'DEADLINE',
        getMessage: (count) => `${count} pesanan harus selesai hari ini`
    },
    transaksiMasuk: {
        priority: 5,
        icon: 'bi-inbox-fill',
        iconBg: 'bg-blue-500',
        borderColor: 'border-blue-500',
        bgColor: 'bg-blue-50',
        textColor: 'text-blue-800',
        badgeClass: 'bg-blue-500',
        title: 'Transaksi Baru',
        badge: 'BARU',
        getMessage: (count) => `${count} transaksi baru masuk hari ini`
    },
    belumLunas: {
        priority: 6,
        icon: 'bi-wallet2',
        iconBg: 'bg-purple-500',
        borderColor: 'border-purple-500',
        bgColor: 'bg-purple-50',
        textColor: 'text-purple-800',
        badgeClass: 'bg-purple-500',
        title: 'Belum Lunas',
        badge: 'PEMBAYARAN',
        getMessage: (count) => `${count} transaksi menunggu pelunasan`
    }
};

// ========================================
// JQUERY DOCUMENT READY - SEMUA INISIALISASI
// ========================================
// ========================================
// JQUERY DOCUMENT READY - SEMUA INISIALISASI
// ========================================
$(document).ready(function() {
    // ======== INISIALISASI DATATABLE ========
    let table = $('#orderTable').DataTable({
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        destroy: true,
        order: [[1, 'desc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 transaksi",
            infoFiltered: "(difilter dari _MAX_ total transaksi)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Tidak ada data transaksi"
        },
        initComplete: function () {
            $('div.dataTables_filter input').addClass("border-2 border-gray-300 rounded-xl px-4 py-3 ml-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition");
            $('div.dataTables_length select').addClass("border-2 border-gray-300 rounded-xl px-4 py-2.5 mr-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition");
            
            $('.dataTables_filter').append(`
                <button onclick="resetTableFilter()" 
                        class="ml-3 px-4 py-2.5 bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg">
                    <i class="bi bi-arrow-clockwise mr-1"></i> Reset Filter
                </button>
            `);
            
            setTimeout(() => {
                $('.dataTables_paginate a').addClass("px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-yellow-50 hover:border-yellow-400 transition text-sm font-semibold mx-1");
                $('.dataTables_paginate .current').addClass("bg-gradient-to-r from-yellow-400 to-amber-500 text-gray-900 border-yellow-500 font-bold shadow-md");
            }, 100);
        },
        drawCallback: function() {
            $('.dataTables_paginate a').addClass("px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-yellow-50 hover:border-yellow-400 transition text-sm font-semibold mx-1");
            $('.dataTables_paginate .current').addClass("bg-gradient-to-r from-yellow-400 to-amber-500 text-gray-900 border-yellow-500 font-bold shadow-md");
        }
    });
    
    console.log('✅ DataTable initialized - Total:', table.data().length);
    
    // ========================================
    // 🔔 AUTO SHOW POPUP - MUNCUL SETIAP LOGIN
    // ========================================
    
    // CEK NOTIFIKASI TERLEBIH DAHULU
    console.log('🔍 Checking notifications...', notifications);
    
    const activeNotifications = [];
    for (const [type, count] of Object.entries(notifications)) {
        console.log(`📊 ${type}: ${count}`);
        if (count > 0 && notificationConfig[type]) {
            activeNotifications.push({
                type: type,
                count: count,
                config: notificationConfig[type]
            });
        }
    }
    
    activeNotifications.sort((a, b) => a.config.priority - b.config.priority);
    console.log('📋 Active notifications:', activeNotifications.length);
    
    // HANYA LANJUTKAN JIKA ADA NOTIFIKASI
    // HANYA LANJUTKAN JIKA ADA NOTIFIKASI
if (activeNotifications.length > 0) {
    const today = new Date().toDateString();
    
    // ✅ DAPATKAN USER ID DARI BLADE
    const userId = '{{ auth()->id() }}'; // Ambil ID user yang login
    
    // ✅ CEK APAKAH SUDAH DITUTUP DI SESSION INI (untuk session saat ini saja)
    const dismissedInSession = sessionStorage.getItem(`reminderDismissedInSession_${userId}`);
    
    // ✅ CEK APAKAH USER MEMILIH "JANGAN TAMPILKAN LAGI HARI INI" (untuk hari yang sama)
    const dismissedForToday = localStorage.getItem(`reminderDismissedDate_${userId}`);
    const dontShowAgainToday = (dismissedForToday === today);

    console.log('📅 Today:', today);
    console.log('📅 Dismissed for today:', dismissedForToday);
    console.log('⛔ Don\'t show again today:', dontShowAgainToday);

    // ✅ TAMPILKAN POPUP JIKA BELUM DITUTUP HARI INI
    if (!dontShowAgainToday) {
        console.log('🔔 SHOWING POPUP...');
        setTimeout(() => {
            showGeneralReminderPopup(activeNotifications);
        }, 1000);
    } else {
        console.log('✅ Popup dismissed for today - SKIPPING');
    }
}
});

// ========================================
// FUNGSI TAMPILKAN POPUP GENERAL
// ========================================
function showGeneralReminderPopup(notifications) {
    console.log('🎯 showGeneralReminderPopup called');
    
    const popup = document.getElementById('reminderPopup');
    if (popup) {
        popup.classList.remove('hidden');
        console.log('✅ POPUP DISPLAYED!');
        playNotificationSound();
    } else {
        console.error('❌ ERROR: reminderPopup element NOT FOUND!');
    }
}

function closeReminderPopup() {
    const popup = document.getElementById('reminderPopup');
    if (!popup) return;
    
    const dontShowAgain = document.getElementById('dontShowAgain');
    const today = new Date().toDateString();
    const userId = '{{ auth()->id() }}'; // ✅ Ambil user ID
    
    // ✅ CEK CHECKBOX
    if (dontShowAgain && dontShowAgain.checked) {
        // User centang "Jangan tampilkan lagi hari ini"
        localStorage.setItem(`reminderDismissedDate_${userId}`, today);
        sessionStorage.setItem(`reminderDismissedInSession_${userId}`, 'with_checkbox');
        console.log('✅ Popup dismissed for today with checkbox:', today, 'User:', userId);
    } else {
        // User TIDAK centang, hanya tutup untuk session ini saja
        sessionStorage.setItem(`reminderDismissedInSession_${userId}`, 'without_checkbox');
        console.log('✅ Popup dismissed for this session only (no checkbox)', 'User:', userId);
    }
    
    // Tutup popup dengan animasi
    popup.style.opacity = '0';
    setTimeout(() => {
        popup.classList.add('hidden');
        popup.style.opacity = '1';
    }, 300);
}

function handleReminderAction() {
    const dontShowAgain = document.getElementById('dontShowAgain');
    const today = new Date().toDateString();
    const userId = '{{ auth()->id() }}'; // ✅ Ambil user ID
    
    // ✅ CEK CHECKBOX saat tombol diklik
    if (dontShowAgain && dontShowAgain.checked) {
        localStorage.setItem(`reminderDismissedDate_${userId}`, today);
        sessionStorage.setItem(`reminderDismissedInSession_${userId}`, 'with_checkbox');
        console.log('✅ Popup dismissed for today via button with checkbox:', today, 'User:', userId);
    } else {
        sessionStorage.setItem(`reminderDismissedInSession_${userId}`, 'without_checkbox');
        console.log('✅ Popup dismissed for this session via button (no checkbox)', 'User:', userId);
    }
    
    closeReminderPopup();
}

function playNotificationSound() {
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuAyvLTgjMGHm7A7+OZSA8PVqzn77BdGAg+ltzy0H8pBSh+zPDckT0KE2S36+mlThAPTKXh8L1pIAUrgM3z1YU1Bx1tv+/nm0sOD1Om4/C4ZRsGN5DY8tCBKwUle8rx34pGCRNjuuzrpE4RDkuq4/K+byEELYPO89WGNgcfcMPx6qBJDg5TqeXyt2McBTmQ1/PMfS0GJ37M8+CQPwsRZL3u66VTEw1Jqt/yvnAkBSyBzvTWhzYHH3HE8eqhSQ4OUqnl8rZlHQU5kdfy0oExBSiAyvLdkD0LElyz7OumUxMMSbDh8rxuIAQugM/01YY2Bx5xxPHqoUkODlSp5fK3YxwGOJLX8tKBMwQnf8rx3ZA9CxJctOzrplQTDEmy4fK8cCAFLoHO89WGNgceXb/w6qFJDg9Tp+Pyt2QcBjiS1/LSgTMEJ4DK8t2QPAsTW7Xs66ZUFA1JtuLyu2wgBSuB0PPUhzYGHl/A8OmhSQ4PUqfl8rJiHAU4k9byy4AzBSZ9y/LdjkALE12z7OumUxQMSrfh8rpuIQUsgc/z04c2Bx5ov/Dqn0kOD1Op5fK1YxwGN5PX8sl/MwUmfsrx3Y8+CxNdu+zrpVMUDUm14fK6biEFLIHP89OHNgcdX8Hw6Z9KDQ9Tp+Xys2McBjeR1/LJfzMFJn7K8d2OPwsUW7vs66ZUEw1KteLyumwgBSyB0PPUhjYHHmC/8OmgSQ0PUqnm8rJhHAU4ktjyzH8zBSd+yvLckD4LFVuy7OumVRQNSrLi8rlsIAUsgs/z1IY2Bx5gwPDon0kOEFGp5vKxYRwFOJLY8syAMwUnfsrx3I88DBVas+zrplQUDUqy4vK5biEFLYLO89SHNgceX8Hx559JDhBRqObysmAbBTiR2PLMgDMEJ37K8d2PPQsVW7Lr66ZVEg1JsuHyt2whBS2Cz/PUhjYHHl/B8OefSQ4QUanm8rFgHAU4kdfy0n8zBCd+y/HdjkAMFFuy7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQU=');
    audio.volume = 0.3;
    audio.play().catch(e => console.log('Audio autoplay prevented'));
}

// ========================================
// TOGGLE NOTIFICATION PANEL
// ========================================
function toggleNotifications() {
    const panel = document.getElementById('notificationPanel');
    panel.classList.toggle('-translate-y-full');
}

function handleNotification(type) {
    toggleNotifications();
    let table = $('#orderTable').DataTable();
    
    $.fn.dataTable.ext.search = [];
    table.columns().search('');
    
    switch(type) {
        case 'terlambat':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let jenis = data[4];
                let statusTransaksi = data[7];
                let tglEstimasi = data[13];
                
                if (!jenis.includes('Online')) return false;
                
                let inProgress = statusTransaksi.includes('Antrian') || 
                                statusTransaksi.includes('Proses') || 
                                statusTransaksi.includes('Selesai Dicuci');
                
                if (!inProgress) return false;
                
                if (tglEstimasi && tglEstimasi !== '-') {
                    let parts = tglEstimasi.split('/');
                    if (parts.length === 3) {
                        let estimasiDate = new Date(parts[2], parts[1] - 1, parts[0]);
                        let today = new Date();
                        today.setHours(0, 0, 0, 0);
                        return estimasiDate < today;
                    }
                }
                return false;
            });
            showToast('{{ $terlambat }} pesanan online terlambat', 'red');
            break;
            
        case 'butuhPickup':
        case 'pickup':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let jenis = data[4];
                let row = table.row(dataIndex).node();
                let idTransaksiCell = $(row).find('td:eq(1)').html();
                
                return jenis.includes('Online') && 
                       idTransaksiCell && 
                       idTransaksiCell.includes('Pickup');
            });
            showToast('{{ $butuhPickup }} transaksi online butuh pickup', 'orange');
            break;
            
        case 'butuhAntar':
        case 'antar':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let jenis = data[4];
                let row = table.row(dataIndex).node();
                let idTransaksiCell = $(row).find('td:eq(1)').html();
                
                return jenis.includes('Online') && 
                       idTransaksiCell && 
                       idTransaksiCell.includes('Antar');
            });
            showToast('{{ $butuhAntar }} transaksi online butuh pengantaran', 'orange');
            break;
            
        case 'transaksiMasuk':
        case 'masuk':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let jenis = data[4];
                let statusTransaksi = data[7];
                
                return jenis.includes('Online') && statusTransaksi.includes('Antrian');
            });
            showToast('{{ $transaksiMasukHariIni }} transaksi online masuk hari ini', 'yellow');
            break;
            
        case 'belumLunas':
        case 'belum_lunas':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let jenis = data[4];
                let statusBayar = data[6];
                
                return jenis.includes('Online') && statusBayar.includes('Belum Lunas');
            });
            showToast('{{ $belumLunas }} transaksi online belum lunas', 'yellow');
            break;
            
        case 'harusSelesaiHariIni':
        case 'deadline':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let jenis = data[4];
                let statusTransaksi = data[7];
                let tglEstimasi = data[13];
                
                if (!jenis.includes('Online')) return false;
                
                let inProgress = statusTransaksi.includes('Antrian') || 
                                statusTransaksi.includes('Proses') || 
                                statusTransaksi.includes('Selesai Dicuci');
                
                if (!inProgress) return false;
                
                if (tglEstimasi && tglEstimasi !== '-') {
                    let parts = tglEstimasi.split('/');
                    if (parts.length === 3) {
                        let estimasiDate = new Date(parts[2], parts[1] - 1, parts[0]);
                        let today = new Date();
                        today.setHours(0, 0, 0, 0);
                        estimasiDate.setHours(0, 0, 0, 0);
                        
                        return estimasiDate.getTime() === today.getTime();
                    }
                }
                return false;
            });
            showToast('{{ $harusSelesaiHariIni }} pesanan online deadline hari ini', 'yellow');
            break;
            
        case 'lunas':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let jenis = data[4];
                let statusBayar = data[6];
                
                return jenis.includes('Online') && statusBayar.includes('Lunas');
            });
            showToast('{{ $pembayaranLunasHariIni }} pembayaran online lunas', 'green');
            break;
            
        case 'siap_ambil':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let jenis = data[4];
                let statusTransaksi = data[7];
                
                return jenis.includes('Online') && statusTransaksi.includes('Siap Ambil');
            });
            showToast('{{ $siapDiambil }} pesanan online siap diambil', 'green');
            break;
    }
    
    table.draw();
    
    $('html, body').animate({
        scrollTop: $("#orderTable").offset().top - 100
    }, 500);
}

function resetTableFilter() {
    let table = $('#orderTable').DataTable();
    
    $.fn.dataTable.ext.search = [];
    table.columns().search('');
    table.search('');
    table.draw();
    
    let totalRows = table.rows().count();
    showToast('Filter direset - menampilkan ' + totalRows + ' transaksi', 'green');
}

function showToast(message, color) {
    const colors = {
        'red': 'bg-red-500',
        'orange': 'bg-orange-500',
        'yellow': 'bg-yellow-500',
        'green': 'bg-green-500'
    };
    
    const icons = {
        'red': 'bi-exclamation-triangle-fill',
        'orange': 'bi-truck',
        'yellow': 'bi-info-circle-fill',
        'green': 'bi-check-circle-fill'
    };
    
    const toast = $(`
        <div class="fixed bottom-4 right-4 ${colors[color]} text-white px-6 py-4 rounded-2xl shadow-2xl z-50 flex items-center gap-3 animate-slide-in max-w-md">
            <i class="bi ${icons[color]} text-2xl flex-shrink-0"></i>
            <div class="flex-1">
                <div class="font-bold text-sm mb-1">Filter Diterapkan</div>
                <span class="text-sm">${message}</span>
            </div>
            <button onclick="$(this).parent().remove()" class="ml-2 hover:bg-white/20 rounded-lg p-1 flex-shrink-0">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `);
    
    $('body').append(toast);
    setTimeout(() => { toast.fadeOut(300, function() { $(this).remove(); }); }, 5000);
}

// ========================================
// DELIVERY & BUKTI MODALS
// ========================================
function showDeliveryInfo(transaksiId) {
    document.getElementById('modalTransaksiId').textContent = transaksiId;
    document.getElementById('deliveryModal').classList.remove('hidden');
    
    fetch(`/api/delivery-info/${transaksiId}`)
        .then(response => response.json())
        .then(data => {
            let content = '';
            if (data.deliveries && data.deliveries.length > 0) {
                data.deliveries.forEach(delivery => {
                    const jenisIcon = delivery.jenis === 'pickup' ? 'bi-box-arrow-in-down' : 'bi-box-arrow-up';
                    const jenisColor = 'from-orange-400 to-orange-500';
                    const jenisText = delivery.jenis === 'pickup' ? 'PICKUP' : 'ANTAR';
                    
                    const statusConfig = {
                        'pending': { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Pending', icon: 'bi-clock' },
                        'accepted': { bg: 'bg-green-100', text: 'text-green-800', label: 'Diterima', icon: 'bi-check-circle' },
                        'on_the_way_to_pickup': { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Menuju Pickup', icon: 'bi-truck' },
                        'picked_up': { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Sudah Pickup', icon: 'bi-check' },
                        'on_the_way_to_deliver': { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Menuju Antar', icon: 'bi-truck' },
                        'on_the_way_to_customer': { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Menuju Pelanggan', icon: 'bi-truck' },
                        'arrived_at_customer': { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Sampai di Pelanggan', icon: 'bi-geo-alt' },
                        'on_the_way_to_laundry': { bg: 'bg-orange-100', text: 'text-orange-800', label: 'Menuju Laundry', icon: 'bi-arrow-left-right' },
                        'arrived_at_laundry': { bg: 'bg-green-100', text: 'text-green-800', label: 'Sampai di Laundry', icon: 'bi-house-check' },
                        'delivered': { bg: 'bg-green-100', text: 'text-green-800', label: 'Terkirim', icon: 'bi-check-circle-fill' },
                        'failed': { bg: 'bg-red-100', text: 'text-red-800', label: 'Gagal', icon: 'bi-x-circle' },
                    };
                    
                    const status = statusConfig[delivery.status] || { bg: 'bg-gray-100', text: 'text-gray-800', label: delivery.status || 'Unknown', icon: 'bi-question-circle' };
                    
                    const driverInfo = delivery.id_driver 
                        ? `<div class="flex items-center gap-2 text-sm text-gray-700">
                            <i class="bi bi-person-badge"></i>
                            <span>Driver ID: ${delivery.id_driver}</span>
                           </div>`
                        : `<div class="flex items-center gap-2 text-sm text-red-600">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span class="font-semibold">Belum Ada Driver</span>
                           </div>`;
                    
                    content += `
                        <div class="bg-gradient-to-r ${jenisColor} rounded-2xl p-1 shadow-lg">
                            <div class="bg-white rounded-xl p-5">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-r ${jenisColor} rounded-xl flex items-center justify-center">
                                            <i class="bi ${jenisIcon} text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-lg text-gray-900">${jenisText}</div>
                                            <div class="text-xs text-gray-500">ID: ${delivery.id_delivery}</div>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full ${status.bg} ${status.text} text-xs font-semibold">
                                        <i class="bi ${status.icon}"></i>
                                        ${status.label}
                                    </span>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-start gap-2 text-sm">
                                        <i class="bi bi-geo-alt-fill text-gray-400 mt-0.5"></i>
                                        <div>
                                            <div class="text-gray-500 text-xs">Alamat Tujuan</div>
                                            <div class="font-semibold text-gray-900">${delivery.alamat_tujuan || '-'}</div>
                                        </div>
                                    </div>
                                    ${driverInfo}
                                    ${delivery.waktu ? `
                                        <div class="flex items-center gap-2 text-sm text-gray-700">
                                            <i class="bi bi-clock-fill text-gray-400"></i>
                                            <span>${new Date(delivery.waktu).toLocaleString('id-ID')}</span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                content = `
                    <div class="text-center py-10">
                        <i class="bi bi-inbox text-5xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 font-semibold">Belum ada data pickup/delivery</p>
                    </div>
                `;
            }
            document.getElementById('deliveryContent').innerHTML = content;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('deliveryContent').innerHTML = `
                <div class="text-center py-10">
                    <i class="bi bi-exclamation-triangle text-5xl text-red-400 mb-3"></i>
                    <p class="text-red-600 font-semibold">Gagal memuat data delivery</p>
                </div>
            `;
        });
}

function closeDeliveryModal() {
    document.getElementById('deliveryModal').classList.add('hidden');
}

function showBuktiImage(imageUrl) {
    document.getElementById('buktiImage').src = imageUrl;
    document.getElementById('buktiModal').classList.remove('hidden');
}

function closeBuktiModal() {
    document.getElementById('buktiModal').classList.add('hidden');
}

// ========================================
// EVENT LISTENERS
// ========================================
document.getElementById('deliveryModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeliveryModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBuktiModal();
        closeDeliveryModal();
        closeReminderPopup();
    }
});

document.addEventListener('click', function(e) {
    const panel = document.getElementById('notificationPanel');
    const bellButton = e.target.closest('button[onclick="toggleNotifications()"]');
    
    if (!panel.contains(e.target) && !bellButton && !panel.classList.contains('-translate-y-full')) {
        toggleNotifications();
    }
});
</script>

<style>
@keyframes slide-in {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes scale-in {
    from { 
        opacity: 0;
        transform: scale(0.9) translateY(-20px); 
    }
    to { 
        opacity: 1;
        transform: scale(1) translateY(0); 
    }
}
.animate-slide-in { animation: slide-in 0.3s ease-out; }
.animate-fade-in { animation: fade-in 0.3s ease-out; }
.animate-scale-in { animation: scale-in 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
</style>
@endpush
