@extends('layouts.master')

@section('title', 'Dashboard Admin')

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
                @endphp
                @if($totalNotif > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center animate-pulse">
                    {{ $totalNotif > 99 ? '99+' : $totalNotif }}
                </span>
                @endif
            </button>
        </div>
    </div>

    <!-- Daily Report -->
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
    <a href="{{ route('profile.admin2.edit') }}"
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
        Admin
    </div>
</div>

{{-- 🚨 AUTO POPUP REMINDER --}}
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

        {{-- CONTENT --}}
        <div class="px-8 py-10 text-center">
            <div class="text-6xl mb-4">⏰</div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Ada pesanan yang perlu diproses!</h3>
            <p class="text-gray-600 mb-2">Pastikan semua pesanan ditangani dengan baik.</p>
            <p class="text-sm text-gray-500">Cek detail di panel notifikasi untuk info lengkap.</p>
        </div>

        {{-- FOOTER --}}
        <div class="px-8 pb-6">
            <button onclick="handleReminderAction()" 
                    class="w-full px-6 py-4 rounded-2xl font-bold text-white shadow-lg hover:shadow-xl transition-all transform hover:scale-105 bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600">
                <i class="bi bi-check-circle-fill mr-2"></i>
                <span>Saya Sudah Mengerti</span>
            </button>

            <div class="mt-4 flex items-center justify-center gap-2 text-sm text-gray-600">
                <input type="checkbox" id="dontShowAgain" class="w-4 h-4 rounded border-gray-300">
                <label for="dontShowAgain" class="cursor-pointer">Jangan tampilkan lagi hari ini</label>
            </div>
        </div>
    </div>
</div>

<!-- NOTIFICATION PANEL -->
<div id="notificationPanel" class="fixed top-0 left-0 right-0 bg-white shadow-2xl z-50 transform -translate-y-full transition-transform duration-300 max-h-[80vh] overflow-y-auto">
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
            @if(isset($terlambatOnline) && $terlambatOnline > 0)
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
                            <span class="font-bold text-red-600">{{ $terlambatOnline }}</span> pesanan online melewati batas estimasi selesai
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-hand-index-thumb"></i> Tap untuk lihat dan proses
                        </p>
                    </div>
                    <i class="bi bi-chevron-right text-xl text-gray-400"></i>
                </div>
            </div>
            @endif

            @if(isset($butuhPickup) && $butuhPickup > 0)
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

            @if(isset($butuhAntar) && $butuhAntar > 0)
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

            @if(isset($belumLunas) && $belumLunas > 0)
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

            @if(isset($transaksiMasukHariIni) && $transaksiMasukHariIni > 0)
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

            @if(isset($harusSelesaiHariIni) && $harusSelesaiHariIni > 0)
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

<!-- STATISTIK CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4 mt-6">
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-blue-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-box-arrow-in-down text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $masuk ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Masuk</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-blue-500 to-blue-300 rounded-full"></div>
    </div>

    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-green-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-check-circle-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $harusSelesai ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Harus Selesai</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-green-500 to-green-300 rounded-full"></div>
    </div>

    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-red-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-clock-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $terlambat ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Terlambat</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-red-500 to-red-300 rounded-full"></div>
    </div>
</div>

<!-- DATA TABLE -->
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

            <a href="{{ route('admin2.transaksi.create') }}"
               class="bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 transition-all px-6 py-3 rounded-2xl text-gray-900 font-bold shadow-lg hover:shadow-xl hover:scale-105 transform inline-flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-lg"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto rounded-2xl border">
            <table id="AdminTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-xs uppercase">
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">No Order</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Admin</th>
                        <th class="px-4 py-3 text-center">Jenis</th>
                        <th class="px-4 py-3 text-center">Status Bayar</th>
                        <th class="px-4 py-3 text-center">Status Transaksi</th>
                        <th class="px-4 py-3 text-right">Total Bayar</th>
                        <th class="px-4 py-3 text-center">Tgl Transaksi</th>
                        <th class="px-4 py-3 text-center">Deadline</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($orders as $i => $o)
                    <tr class="border-b hover:bg-yellow-50 transition">
                        <td class="px-4 py-3">{{ $i+1 }}</td>

                        <td class="px-4 py-3">
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

                        <td class="px-4 py-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $o->nama_pelanggan ?? '-' }}</div>
                                @if($o->no_hp)
                                    <div class="text-xs text-gray-500">{{ $o->no_hp }}</div>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3">
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
                                        <div class="font-semibold text-gray-900">{{ $kasir->nama_kasir ?? $kasir->username ?? $kasir->name ?? 'Admin' }}</div>
                                    @else
                                        <div class="font-semibold text-gray-900">Admin</div>
                                    @endif
                                    <div class="text-gray-500">ID: {{ $o->id_kasir }}</div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($o->jenis_transaksi == 'online')
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <i class="bi bi-globe mr-1"></i> Online
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="bi bi-shop mr-1"></i> Offline
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($o->status_bayar)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap
                                    {{ $o->status_bayar == 'lunas' ? 'bg-green-100 text-green-800' : ($o->status_bayar == 'DP' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $o->status_bayar == 'belum_lunas' ? 'Belum Lunas' : ucfirst($o->status_bayar) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $statusConfig = [
                                    'antrian' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Antrian'],
                                    'proses' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Proses'],
                                    'selesai_dicuci' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'label' => 'Selesai Dicuci'],
                                    'siap_di_ambil' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Siap Ambil'],
                                    'siap_di_antar' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'label' => 'Siap Antar'],
                                    'pick_up' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'label' => 'Pick Up'],
                                    'selesai' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Selesai'],
                                    'batal' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Batal'],
                                ];
                                
                                $status = $statusConfig[$o->status_transaksi] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst(str_replace('_', ' ', $o->status_transaksi))];
                            @endphp
                            
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }} whitespace-nowrap">
                                {{ $status['label'] }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="font-bold text-green-600 whitespace-nowrap">
                                Rp {{ number_format($o->total_bayar ?? 0, 0, ',', '.') }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center text-gray-600 whitespace-nowrap">
                            {{ $o->tgl_transaksi ? \Carbon\Carbon::parse($o->tgl_transaksi)->format('d/m/Y') : '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if(isset($o->deadline_status))
                                @if($o->deadline_status === 'terlambat')
                                    <div class="flex items-center justify-center gap-1">
                                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-red-100 text-red-700 inline-flex items-center gap-1 whitespace-nowrap">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            {{ $o->deadline }}
                                        </span>
                                    </div>
                                @elseif($o->deadline_status === 'mendesak')
                                    <div class="flex items-center justify-center gap-1">
                                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-orange-100 text-orange-700 inline-flex items-center gap-1 whitespace-nowrap">
                                            <i class="bi bi-clock-fill"></i>
                                            {{ $o->deadline }}
                                        </span>
                                    </div>
                                @elseif($o->deadline_status === 'normal')
                                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-green-100 text-green-700 whitespace-nowrap">
                                        {{ $o->deadline }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Tidak ada deadline</span>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin2.riwayat.detail', $o->id_transaksi) }}"
                               class="bg-gradient-to-r from-yellow-400 to-amber-500 px-4 py-2 rounded-xl text-gray-900 font-bold hover:from-yellow-500 hover:to-amber-600 transition-all hover:shadow-lg inline-flex items-center gap-2 justify-center whitespace-nowrap">
                                <i class="bi bi-eye-fill"></i>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-10 text-center">
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

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
// ========================================
// DATA NOTIFIKASI
// ========================================
const notifications = {
    terlambat: {{ $terlambatOnline ?? 0 }},
    butuhPickup: {{ $butuhPickup ?? 0 }},
    butuhAntar: {{ $butuhAntar ?? 0 }},
    harusSelesaiHariIni: {{ $harusSelesaiHariIni ?? 0 }},
    transaksiMasuk: {{ $transaksiMasukHariIni ?? 0 }},
    belumLunas: {{ $belumLunas ?? 0 }}
};

// ========================================
// DOCUMENT READY - SEMUA INISIALISASI
// ========================================
$(document).ready(function () {
    // ======== INISIALISASI DATATABLE ========
    let table = $('#AdminTable').DataTable({
        pageLength: 25,
        lengthChange: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        ordering: true,
        searching: true,
        order: [[1, 'desc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total transaksi)",
            zeroRecords: "Tidak ada transaksi yang cocok",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
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
    
    console.log('✅ DataTable initialized - Total rows:', table.data().length);
    
    // ======== POPUP REMINDER LOGIC ========
    const dismissedDate = localStorage.getItem('reminderDismissedDate');
    const today = new Date().toDateString();
    
    console.log('📅 Today:', today);
    console.log('📅 Dismissed date:', dismissedDate);
    
    // Jika sudah di-dismiss hari ini, SKIP popup
    if (dismissedDate === today) {
        console.log('✅ Popup already dismissed today - SKIPPING');
        return;
    }
    
    // CEK NOTIFIKASI
    console.log('🔍 Checking notifications...', notifications);
    
    let hasNotifications = false;
    for (const [type, count] of Object.entries(notifications)) {
        if (count > 0) {
            hasNotifications = true;
            break;
        }
    }
    
    // SHOW POPUP JIKA ADA NOTIFIKASI
    if (hasNotifications) {
        console.log('🔔 SHOWING POPUP...');
        setTimeout(() => {
            showReminderPopup();
        }, 1000);
    } else {
        console.log('✅ No notifications - no popup needed');
    }
});

// ========================================
// POPUP FUNCTIONS
// ========================================
function showReminderPopup() {
    const popup = document.getElementById('reminderPopup');
    if (popup) {
        popup.classList.remove('hidden');
        console.log('✅ POPUP DISPLAYED!');
        playNotificationSound();
    }
}

function closeReminderPopup() {
    const popup = document.getElementById('reminderPopup');
    if (!popup) return;
    
    const dontShowAgain = document.getElementById('dontShowAgain');
    
    if (dontShowAgain && dontShowAgain.checked) {
        const today = new Date().toDateString();
        localStorage.setItem('reminderDismissedDate', today);
        console.log('✅ Popup dismissed for today:', today);
    }
    
    popup.style.opacity = '0';
    setTimeout(() => {
        popup.classList.add('hidden');
        popup.style.opacity = '1';
    }, 300);
}

function handleReminderAction() {
    const dontShowAgain = document.getElementById('dontShowAgain');
    
    if (dontShowAgain && dontShowAgain.checked) {
        const today = new Date().toDateString();
        localStorage.setItem('reminderDismissedDate', today);
        console.log('✅ Popup dismissed for today via button:', today);
    }
    
    closeReminderPopup();
}

function playNotificationSound() {
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuAyvLTgjMGHm7A7+OZSA8PVqzn77BdGAg+ltzy0H8pBSh+zPDckT0KE2S36+mlThAPTKXh8L1pIAUrgM3z1YU1Bx1tv+/nm0sOD1Om4/C4ZRsGN5DY8tCBKwUle8rx34pGCRNjuuzrpE4RDkuq4/K+byEELYPO89WGNgcfcMPx6qBJDg5TqeXyt2McBTmQ1/PMfS0GJ37M8+CQPwsRZL3u66VTEw1Jqt/yvnAkBSyBzvTWhzYHH3HE8eqhSQ4OUqnl8rZlHQU5kdfy0oExBSiAyvLdkD0LElyz7OumUxMMSbDh8rxuIAQugM/01YY2Bx5xxPHqoUkODlSp5fK3YxwGOJLX8tKBMwQnf8rx3ZA9CxJctOzrplQTDEmy4fK8cCAFLoHO89WGNgceXb/w6qFJDg9Tp+Pyt2QcBjiS1/LSgTMEJ4DK8t2QPAsTW7Xs66ZUFA1JtuLyu2wgBSuB0PPUhzYGHl/A8OmhSQ4PUqfl8rJiHAU4k9byy4AzBSZ9y/LdjkALE12z7OumUxQMSrfh8rpuIQUsgc/z04c2Bx5ov/Dqn0kOD1Op5fK1YxwGN5PX8sl/MwUmfsrx3Y8+CxNdu+zrpVMUDUm14fK6biEFLIHP89OHNgcdX8Hw6Z9KDQ9Tp+Xys2McBjeR1/LJfzMFJn7K8d2OPwsUW7vs66ZUEw1KteLyumwgBSyB0PPUhjYHHmC/8OmgSQ0PUqnm8rJhHAU4ktjyzH8zBSd+yvLckD4LFVuy7OumVRQNSrLi8rlsIAUsgs/z1IY2Bx5gwPDon0kOEFGp5vKxYRwFOJLY8syAMwUnfsrx3I88DBVas+zrplQUDUqy4vK5biEFLYLO89SHNgceX8Hx559JDhBRqObysmAbBTiR2PLMgDMEJ37K8d2PPQsVW7Lr66ZVEg1JsuHyt2whBS2Cz/PUhjYHHl/B8OefSQ4QUanm8rFgHAU4kdfy0n8zBCd+y/HdjkAMFFuy7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQU=');
    audio.volume = 0.3;
    audio.play().catch(e => console.log('Audio autoplay prevented'));
}

// ========================================
// NOTIFICATION FUNCTIONS
// ========================================
function toggleNotifications() {
    const panel = document.getElementById('notificationPanel');
    panel.classList.toggle('-translate-y-full');
}

function handleNotification(type) {
    toggleNotifications();
    let table = $('#AdminTable').DataTable();
    
    $.fn.dataTable.ext.search = [];
    table.columns().search('');
    
    console.log('🔍 Filter triggered:', type);
    
    switch(type) {
        case 'terlambat':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                let jenisCell = $(row).find('td:eq(4)');
                if (!jenisCell.text().includes('Online')) return false;
                
                let statusCell = $(row).find('td:eq(6)');
                let statusText = statusCell.text().trim();
                let isInProgress = statusText.includes('Antrian') || 
                                  statusText.includes('Proses') || 
                                  statusText.includes('Selesai Dicuci');
                if (!isInProgress) return false;
                
                let deadlineCell = $(row).find('td:eq(9)');
                return deadlineCell.find('.bg-red-100').length > 0 || 
                       deadlineCell.find('i.bi-exclamation-triangle-fill').length > 0;
            });
            showToast('Menampilkan pesanan online terlambat', 'red');
            break;
            
        case 'pickup':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                let jenisCell = $(row).find('td:eq(4)');
                if (!jenisCell.text().includes('Online')) return false;
                
                let statusCell = $(row).find('td:eq(6)');
                let statusText = statusCell.text().trim();
                if (statusText.includes('Selesai') || statusText.includes('Batal')) return false;
                
                let idCell = $(row).find('td:eq(1)');
                return idCell.find('span:contains("Pickup")').length > 0;
            });
            showToast('Menampilkan transaksi butuh pickup', 'orange');
            break;
            
        case 'antar':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                let jenisCell = $(row).find('td:eq(4)');
                if (!jenisCell.text().includes('Online')) return false;
                
                let statusCell = $(row).find('td:eq(6)');
                let statusText = statusCell.text().trim();
                if (statusText.includes('Selesai') || statusText.includes('Batal')) return false;
                
                let idCell = $(row).find('td:eq(1)');
                return idCell.find('span:contains("Antar")').length > 0;
            });
            showToast('Menampilkan transaksi butuh pengantaran', 'orange');
            break;
            
        case 'masuk':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                let jenisCell = $(row).find('td:eq(4)');
                let statusCell = $(row).find('td:eq(6)');
                return jenisCell.text().includes('Online') && statusCell.text().includes('Antrian');
            });
            showToast('Menampilkan transaksi baru masuk', 'yellow');
            break;
            
        case 'belum_lunas':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                let jenisCell = $(row).find('td:eq(4)');
                let bayarCell = $(row).find('td:eq(5)');
                return jenisCell.text().includes('Online') && bayarCell.text().includes('Belum Lunas');
            });
            showToast('Menampilkan transaksi belum lunas', 'yellow');
            break;
            
        case 'deadline':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                let jenisCell = $(row).find('td:eq(4)');
                if (!jenisCell.text().includes('Online')) return false;
                
                let statusCell = $(row).find('td:eq(6)');
                let statusText = statusCell.text().trim();
                let isInProgress = statusText.includes('Antrian') || 
                                  statusText.includes('Proses') || 
                                  statusText.includes('Selesai Dicuci');
                if (!isInProgress) return false;
                
                let deadlineCell = $(row).find('td:eq(9)');
                return deadlineCell.find('.bg-orange-100').length > 0 ||
                       deadlineCell.find('i.bi-clock-fill').length > 0;
            });
            showToast('Menampilkan pesanan deadline hari ini', 'yellow');
            break;
    }
    
    table.draw();
    $('html, body').animate({
        scrollTop: $("#AdminTable").offset().top - 100
    }, 500);
}

function resetTableFilter() {
    let table = $('#AdminTable').DataTable();
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
// EVENT LISTENERS
// ========================================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
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