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
$(document).ready(function () {
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
});

function toggleNotifications() {
    const panel = document.getElementById('notificationPanel');
    panel.classList.toggle('-translate-y-full');
}
// ========================================
// ULTIMATE FIX: Filter Terlambat yang BENAR
// ========================================
// Harus match dengan query di controller:
// - ONLINE only
// - Status: antrian, proses, selesai_dicuci
// - Deadline < today

function handleNotification(type) {
    toggleNotifications();
    let table = $('#AdminTable').DataTable();
    
    // Clear all filters
    $.fn.dataTable.ext.search = [];
    table.columns().search('');
    
    console.log('🔍 Filter triggered:', type);
    
    switch(type) {
        case 'terlambat':
            // ✅ CRITICAL FIX: Match dengan controller query
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                
                // 1️⃣ CEK JENIS TRANSAKSI (must be ONLINE)
                let jenisCell = $(row).find('td:eq(4)');
                let isOnline = jenisCell.text().includes('Online');
                
                if (!isOnline) {
                    return false;
                }
                
                // 2️⃣ CEK STATUS TRANSAKSI (must be antrian, proses, atau selesai_dicuci)
                let statusCell = $(row).find('td:eq(6)');
                let statusText = statusCell.text().trim();
                
                let isInProgress = statusText.includes('Antrian') || 
                                  statusText.includes('Proses') || 
                                  statusText.includes('Selesai Dicuci');
                
                if (!isInProgress) {
                    console.log('❌ Skipped (already completed):', data[1], '- Status:', statusText);
                    return false;
                }
                
                // 3️⃣ CEK DEADLINE (must have red badge = terlambat)
                let deadlineCell = $(row).find('td:eq(9)');
                let hasTerlambatBadge = deadlineCell.find('.bg-red-100').length > 0 || 
                                       deadlineCell.find('i.bi-exclamation-triangle-fill').length > 0;
                
                if (!hasTerlambatBadge) {
                    return false;
                }
                
                // ✅ PASS ALL CHECKS
                console.log('✅ FOUND TERLAMBAT:', {
                    id: data[1],
                    jenis: 'Online',
                    status: statusText,
                    deadline: 'OVERDUE'
                });
                
                return true;
            });
            showToast('Menampilkan pesanan online terlambat (belum selesai)', 'red');
            break;
            
        case 'pickup':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                
                // Must be ONLINE
                let jenisCell = $(row).find('td:eq(4)');
                if (!jenisCell.text().includes('Online')) return false;
                
                // Must NOT be completed
                let statusCell = $(row).find('td:eq(6)');
                let statusText = statusCell.text().trim();
                if (statusText.includes('Selesai') || statusText.includes('Batal')) {
                    return false;
                }
                
                // Must have Pickup badge
                let idCell = $(row).find('td:eq(1)');
                let hasPickupBadge = idCell.find('span:contains("Pickup")').length > 0;
                
                if (hasPickupBadge) {
                    console.log('✅ Found pickup:', data[1]);
                }
                
                return hasPickupBadge;
            });
            showToast('Menampilkan transaksi butuh pickup', 'orange');
            break;
            
        case 'antar':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                
                // Must be ONLINE
                let jenisCell = $(row).find('td:eq(4)');
                if (!jenisCell.text().includes('Online')) return false;
                
                // Must NOT be completed
                let statusCell = $(row).find('td:eq(6)');
                let statusText = statusCell.text().trim();
                if (statusText.includes('Selesai') || statusText.includes('Batal')) {
                    return false;
                }
                
                // Must have Antar badge
                let idCell = $(row).find('td:eq(1)');
                let hasAntarBadge = idCell.find('span:contains("Antar")').length > 0;
                
                if (hasAntarBadge) {
                    console.log('✅ Found antar:', data[1]);
                }
                
                return hasAntarBadge;
            });
            showToast('Menampilkan transaksi butuh pengantaran', 'orange');
            break;
            
        case 'masuk':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                
                let jenisCell = $(row).find('td:eq(4)');
                let statusCell = $(row).find('td:eq(6)');
                
                return jenisCell.text().includes('Online') && 
                       statusCell.text().includes('Antrian');
            });
            showToast('Menampilkan transaksi baru masuk', 'yellow');
            break;
            
        case 'belum_lunas':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                
                let jenisCell = $(row).find('td:eq(4)');
                let bayarCell = $(row).find('td:eq(5)');
                
                return jenisCell.text().includes('Online') && 
                       bayarCell.text().includes('Belum Lunas');
            });
            showToast('Menampilkan transaksi belum lunas', 'yellow');
            break;
            
        case 'deadline':
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let row = table.row(dataIndex).node();
                
                // Must be ONLINE
                let jenisCell = $(row).find('td:eq(4)');
                if (!jenisCell.text().includes('Online')) return false;
                
                // Must be IN PROGRESS
                let statusCell = $(row).find('td:eq(6)');
                let statusText = statusCell.text().trim();
                let isInProgress = statusText.includes('Antrian') || 
                                  statusText.includes('Proses') || 
                                  statusText.includes('Selesai Dicuci');
                
                if (!isInProgress) return false;
                
                // Must have ORANGE badge (mendesak = deadline hari ini)
                let deadlineCell = $(row).find('td:eq(9)');
                let hasMendesakBadge = deadlineCell.find('.bg-orange-100').length > 0 ||
                                      deadlineCell.find('i.bi-clock-fill').length > 0;
                
                if (hasMendesakBadge) {
                    console.log('✅ Found deadline today:', data[1]);
                }
                
                return hasMendesakBadge;
            });
            showToast('Menampilkan pesanan deadline hari ini (belum selesai)', 'yellow');
            break;
    }
    
    table.draw();
    
    let filteredCount = table.rows({search: 'applied'}).count();
    console.log('📊 Filter result:', filteredCount, 'rows found');
    
    if (filteredCount === 0) {
        console.warn('⚠️ No matching rows found!');
    }
    
    // Scroll to table
    $('html, body').animate({
        scrollTop: $("#AdminTable").offset().top - 100
    }, 500);
}

// ========================================
// DEBUGGING HELPER
// ========================================
function debugTableData() {
    let table = $('#AdminTable').DataTable();
    
    console.log('=== TABLE DEBUG INFO ===');
    console.log('Total rows:', table.rows().count());
    
    table.rows().every(function(rowIdx) {
        let row = this.node();
        let data = this.data();
        
        let jenis = $(row).find('td:eq(4)').text().trim();
        let status = $(row).find('td:eq(6)').text().trim();
        let deadlineCell = $(row).find('td:eq(9)');
        let hasTerlambat = deadlineCell.find('.bg-red-100').length > 0;
        
        if (hasTerlambat) {
            console.log('Row with RED badge:', {
                id: data[1],
                jenis: jenis,
                status: status,
                isOnline: jenis.includes('Online'),
                isCompleted: status.includes('Selesai') || status.includes('Batal')
            });
        }
    });
}

// Call this in console to debug:
// debugTableData()
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
.animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>
@endpush