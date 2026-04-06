{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.master')

@section('title', 'Dashboard Super Admin')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@section('content')

{{-- ════════════════════════════════════════
     HEADER
════════════════════════════════════════ --}}
@php
    $totalNotif = ($transaksiMasukHariIni ?? 0)
        + ($belumLunas ?? 0)
        + ($butuhPickup ?? 0)
        + ($butuhAntar ?? 0)
        + ($harusSelesaiHariIni ?? 0)
        + ($siapDiambil ?? 0)
        + ($terlambat ?? 0);
@endphp

<div class="bg-[#ffcc00] p-4 rounded-b-3xl shadow-lg">
    <div class="flex items-center justify-between">
        <div onclick="toggleSidebar()" class="text-3xl font-bold cursor-pointer">≡</div>
        <img src="{{ asset('images/dashboard_logo.png') }}" class="w-40" alt="logo">
        <div class="relative">
            <button onclick="toggleNotifications()"
                    class="relative bg-white w-12 h-12 flex items-center justify-center rounded-full text-2xl text-gray-800 shadow hover:bg-gray-100 transition">
                <i class="bi bi-bell-fill"></i>
                @if($totalNotif > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center animate-pulse">
                        {{ $totalNotif > 99 ? '99+' : $totalNotif }}
                    </span>
                @endif
            </button>
        </div>
    </div>
    <div class="mt-4 text-[14px] font-semibold flex justify-between">
        <div>
            <div class="flex items-center gap-2">
                <i class="bi bi-cart-fill text-lg"></i> Laporan Hari ini
            </div>
            <div>Total Omzet : Rp.{{ number_format($totalOmzet, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     USER BAR
════════════════════════════════════════ --}}
<div class="mx-4 mt-4 flex items-center justify-end gap-3">
    <a href="{{ route('profile.admin.edit') }}"
       class="bg-gray-300 w-12 h-12 flex items-center justify-center rounded-full text-2xl text-black shadow hover:bg-gray-400 transition">
        <i class="bi bi-person-fill"></i>
    </a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
                class="bg-red-500 w-12 h-12 flex items-center justify-center rounded-full text-white text-2xl shadow hover:bg-red-600 transition">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>
    <div class="bg-[#ffcc00] px-5 py-3 rounded-xl text-black text-sm font-bold shadow">
        Super Admin
    </div>
</div>

{{-- ════════════════════════════════════════
     REMINDER POPUP
════════════════════════════════════════ --}}
<div id="reminderPopup"
     class="hidden fixed inset-0 bg-gradient-to-br from-black/20 via-gray-900/15 to-black/20 z-[60] flex items-center justify-center p-4 animate-fade-in">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden transform animate-scale-in">
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
        <div class="px-8 py-10 text-center">
            <div class="text-6xl mb-4">⏰</div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Ada pesanan yang perlu diproses!</h3>
            <p class="text-gray-600 mb-2">Pastikan semua pesanan ditangani dengan baik.</p>
            <p class="text-sm text-gray-500">Cek detail di panel notifikasi untuk info lengkap.</p>
        </div>
        <div class="px-8 pb-6">
            <button onclick="handleReminderAction()"
                    class="w-full px-6 py-4 rounded-2xl font-bold text-white shadow-lg hover:shadow-xl transition-all transform hover:scale-105 bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600">
                <i class="bi bi-check-circle-fill mr-2"></i> Saya Sudah Mengerti
            </button>
            <div class="mt-4 flex items-center justify-center gap-2 text-sm text-gray-600">
                <input type="checkbox" id="dontShowAgain" class="w-4 h-4 rounded border-gray-300">
                <label for="dontShowAgain" class="cursor-pointer">Jangan tampilkan lagi hari ini</label>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     NOTIFICATION PANEL
════════════════════════════════════════ --}}
<div id="notificationPanel"
     class="fixed top-0 left-0 right-0 bg-white shadow-2xl z-50 transform -translate-y-full transition-transform duration-300 max-h-[80vh] overflow-y-auto">
    <div class="p-6">
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

        <div class="space-y-3">
            @if(($terlambat ?? 0) > 0)
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
                        <p class="text-sm text-gray-700"><span class="font-bold text-red-600">{{ $terlambat }}</span> pesanan melewati batas estimasi selesai</p>
                        <p class="text-xs text-gray-500 mt-1"><i class="bi bi-hand-index-thumb"></i> Tap untuk lihat dan proses</p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            @if(($butuhPickup ?? 0) > 0)
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
                        <p class="text-sm text-gray-700"><span class="font-bold text-orange-600">{{ $butuhPickup }}</span> transaksi perlu driver untuk penjemputan</p>
                        <p class="text-xs text-gray-500 mt-1"><i class="bi bi-hand-index-thumb"></i> Tap untuk assign driver</p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            @if(($butuhAntar ?? 0) > 0)
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
                        <p class="text-sm text-gray-700"><span class="font-bold text-orange-600">{{ $butuhAntar }}</span> transaksi perlu driver untuk pengantaran</p>
                        <p class="text-xs text-gray-500 mt-1"><i class="bi bi-hand-index-thumb"></i> Tap untuk assign driver</p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            @if(($belumLunas ?? 0) > 0)
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
                        <p class="text-sm text-gray-700"><span class="font-bold text-yellow-600">{{ $belumLunas }}</span> transaksi menunggu pelunasan</p>
                        <p class="text-xs text-gray-500 mt-1"><i class="bi bi-hand-index-thumb"></i> Tap untuk lihat tagihan</p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            @if(($transaksiMasukHariIni ?? 0) > 0)
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
                        <p class="text-sm text-gray-700"><span class="font-bold text-yellow-600">{{ $transaksiMasukHariIni }}</span> transaksi baru masuk hari ini</p>
                        <p class="text-xs text-gray-500 mt-1"><i class="bi bi-hand-index-thumb"></i> Tap untuk proses</p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            @if(($harusSelesaiHariIni ?? 0) > 0)
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
                        <p class="text-sm text-gray-700"><span class="font-bold text-yellow-600">{{ $harusSelesaiHariIni }}</span> pesanan dengan deadline hari ini</p>
                        <p class="text-xs text-gray-500 mt-1"><i class="bi bi-hand-index-thumb"></i> Tap untuk prioritaskan</p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            @if(($siapDiambil ?? 0) > 0)
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
                        <p class="text-sm text-gray-700"><span class="font-bold text-green-600">{{ $siapDiambil }}</span> pesanan siap diambil pelanggan</p>
                        <p class="text-xs text-gray-500 mt-1"><i class="bi bi-hand-index-thumb"></i> Tap untuk hubungi pelanggan</p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            @if(($pembayaranLunasHariIni ?? 0) > 0)
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
                        <p class="text-sm text-gray-700"><span class="font-bold text-green-600">{{ $pembayaranLunasHariIni }}</span> pembayaran lunas hari ini</p>
                        <p class="text-xs text-gray-500 mt-1"><i class="bi bi-hand-index-thumb"></i> Tap untuk lihat detail</p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

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

{{-- ════════════════════════════════════════
     STATS CARDS
════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4 mt-6">
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
        <div class="text-base font-semibold text-gray-700">Admin</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-green-500 to-green-300 rounded-full"></div>
    </div>

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

{{-- ════════════════════════════════════════
     TABEL TRANSAKSI
════════════════════════════════════════ --}}
<div class="mx-4 mt-8 mb-8 bg-white rounded-3xl shadow-xl border-2 border-gray-100">
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
        </div>
    </div>

    <div class="p-8">
        <div class="overflow-x-auto rounded-2xl border-2 border-gray-200">
            <table id="orderTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 text-xs uppercase tracking-wider border-b-2 border-gray-200">
                        <th class="py-4 px-4 text-left font-bold">No</th>
                        <th class="py-4 px-4 text-left font-bold">ID Transaksi</th>
                        <th class="py-4 px-4 text-left font-bold">Pelanggan</th>
                        <th class="py-4 px-4 text-left font-bold">Admin</th>
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
                        <td class="py-4 px-4 text-gray-600 font-medium">{{ $i + 1 }}</td>

                        <td class="py-4 px-4">
                            <span class="font-bold text-gray-900">{{ $o->id_transaksi }}</span>
                            @if($o->jenis_transaksi == 'online' && !in_array($o->status_transaksi, ['selesai', 'batal']))
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @if($o->has_pickup_pending ?? false)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <i class="bi bi-exclamation-circle-fill mr-1"></i> Pickup
                                        </span>
                                    @endif
                                    @if($o->has_antar_pending ?? false)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <i class="bi bi-exclamation-circle-fill mr-1"></i> Antar
                                        </span>
                                    @endif
                                </div>
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
                            @if($o->id_admin)
                                <div class="text-xs">
                                    <div class="font-semibold text-gray-900">{{ $o->nama_admin ?? 'Admin' }}</div>
                                    <div class="text-gray-500">ID: {{ $o->id_admin }}</div>
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
                            @if($o->nama_metode_bayar ?? false)
                                <div class="text-xs min-w-[100px]">
                                    <div class="font-semibold text-gray-900">{{ $o->nama_metode_bayar }}</div>
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
                                    'antrian'        => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Antrian'],
                                    'proses'         => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Proses'],
                                    'selesai_dicuci' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Selesai Dicuci'],
                                    'siap_di_ambil'  => ['bg' => 'bg-green-100',  'text' => 'text-green-800',  'label' => 'Siap Ambil'],
                                    'siap_di_antar'  => ['bg' => 'bg-green-100',  'text' => 'text-green-800',  'label' => 'Siap Antar'],
                                    'pick_up'        => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pick Up'],
                                    'selesai'        => ['bg' => 'bg-green-100',  'text' => 'text-green-800',  'label' => 'Selesai'],
                                    'batal'          => ['bg' => 'bg-red-100',    'text' => 'text-red-800',    'label' => 'Batal'],
                                ];
                                $status = $statusConfig[$o->status_transaksi]
                                    ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800',
                                        'label' => ucfirst(str_replace('_', ' ', $o->status_transaksi))];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }} whitespace-nowrap">
                                {{ $status['label'] }}
                            </span>
                        </td>

                        <td class="py-4 px-4 text-right">
                            <div class="font-bold text-gray-900 whitespace-nowrap">Rp {{ number_format($o->total_harga ?? 0, 0, ',', '.') }}</div>
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if(($o->diskon ?? 0) > 0)
                                <div class="font-semibold text-red-600 whitespace-nowrap">Rp {{ number_format($o->diskon, 0, ',', '.') }}</div>
                                @if($o->tipe_diskon)
                                    <div class="text-xs text-gray-500">{{ ucfirst($o->tipe_diskon) }}</div>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if(($o->dp ?? 0) > 0)
                                <div class="font-semibold text-yellow-600 whitespace-nowrap">Rp {{ number_format($o->dp, 0, ',', '.') }}</div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            <div class="font-bold text-green-600 whitespace-nowrap">Rp {{ number_format($o->total_bayar ?? 0, 0, ',', '.') }}</div>
                        </td>

                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">{{ $o->tgl_transaksi ? \Carbon\Carbon::parse($o->tgl_transaksi)->format('d/m/Y') : '-' }}</td>
                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">{{ $o->tgl_estimasi ? \Carbon\Carbon::parse($o->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">{{ $o->tgl_lunas ? \Carbon\Carbon::parse($o->tgl_lunas)->format('d/m/Y') : '-' }}</td>

                        <td class="py-4 px-4">
                            @if($o->keterangan)
                                <div class="max-w-[150px] text-xs text-gray-700 truncate" title="{{ $o->keterangan }}">{{ $o->keterangan }}</div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-center">
                            @if($o->foto_bukti)
                                <button onclick="showBuktiImage('{{ asset('storage/' . $o->foto_bukti) }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition">
                                    <i class="bi bi-image"></i> <span>Lihat</span>
                                </button>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            <div class="flex items-center justify-center gap-2 flex-nowrap min-w-[180px]">
                                @if($o->jenis_transaksi == 'online')
                                    <a href="{{ route('pesanan.online.detail', ['id' => $o->id_transaksi, 'from' => 'dashboard']) }}"
                                       class="inline-flex items-center gap-1 bg-gradient-to-r from-yellow-400 to-amber-500 px-3 py-2 rounded-xl text-gray-900 text-xs font-bold hover:from-yellow-500 hover:to-amber-600 transition-all hover:shadow-lg hover:scale-105 whitespace-nowrap">
                                        <i class="bi bi-eye-fill"></i> <span>Detail</span>
                                    </a>
                                @else
                                    <a href="{{ route('riwayat.detail', ['id' => $o->id_transaksi]) }}"
                                       class="inline-flex items-center gap-1 bg-gradient-to-r from-yellow-400 to-amber-500 px-3 py-2 rounded-xl text-gray-900 text-xs font-bold hover:from-yellow-500 hover:to-amber-600 transition-all hover:shadow-lg hover:scale-105 whitespace-nowrap">
                                        <i class="bi bi-eye-fill"></i> <span>Detail</span>
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

{{-- ════════════════════════════════════════
     MODALS
════════════════════════════════════════ --}}
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    window.DASHBOARD_DATA = {
        userId: {{ auth()->id() }},
        notifications: {
            terlambat:           {{ $terlambat ?? 0 }},
            butuhPickup:         {{ $butuhPickup ?? 0 }},
            butuhAntar:          {{ $butuhAntar ?? 0 }},
            harusSelesaiHariIni: {{ $harusSelesaiHariIni ?? 0 }},
            transaksiMasuk:      {{ $transaksiMasukHariIni ?? 0 }},
            belumLunas:          {{ $belumLunas ?? 0 }},
        },
        toastMessages: {
            terlambat:    '{{ $terlambat ?? 0 }} pesanan online terlambat',
            pickup:       '{{ $butuhPickup ?? 0 }} transaksi butuh pickup',
            antar:        '{{ $butuhAntar ?? 0 }} transaksi butuh pengantaran',
            masuk:        '{{ $transaksiMasukHariIni ?? 0 }} transaksi masuk hari ini',
            belum_lunas:  '{{ $belumLunas ?? 0 }} transaksi belum lunas',
            deadline:     '{{ $harusSelesaiHariIni ?? 0 }} pesanan deadline hari ini',
            lunas:        '{{ $pembayaranLunasHariIni ?? 0 }} pembayaran lunas',
            siap_ambil:   '{{ $siapDiambil ?? 0 }} pesanan siap diambil',
        }
    };
</script>
<script src="{{ asset('js/superadmin/dashboard.js') }}"></script>
@endpush