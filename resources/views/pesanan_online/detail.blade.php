@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-6 py-4 rounded-b-2xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('pesanan.online.index') }}"
           class="text-black text-2xl font-bold hover:opacity-70">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold">Detail Pesanan</h1>
            <p class="text-sm text-gray-800">ORDER/{{ $pesanan->id_transaksi }}</p>
        </div>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <div id="successAlert" class="mx-6 mt-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3 shadow-md animate-slideDown">
        <i class="bi bi-check-circle-fill text-green-600 text-2xl"></i>
        <div class="flex-1">
            <p class="font-semibold text-green-800">Berhasil!</p>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
        <button onclick="closeAlert()" class="text-green-600 hover:text-green-800">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-6 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ================= LEFT CONTENT ================= --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ITEM PESANAN --}}
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="bi bi-basket text-purple-600"></i> Item Pesanan
                </h2>

                @if($pesanan->detail_transaksi && $pesanan->detail_transaksi->count() > 0)
                    <div class="space-y-3">
                        @foreach($pesanan->detail_transaksi as $i => $d)
                        @php
                            $subtotal = $d->harga * $d->qty;
                        @endphp
                        <div class="flex gap-4 bg-gray-50 p-4 rounded-lg">
                            <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center font-bold">
                                {{ $i+1 }}
                            </div>

                            <div class="flex-1">
                                <p class="font-semibold">{{ $d->layanan->nama_layanan ?? '-' }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $d->jenis->nama_jenis ?? 'Regular' }}
                                </p>
                                @if($d->parfum)
                                <p class="text-xs text-gray-500">
                                    <i class="bi bi-flower1"></i> {{ $d->parfum->nama_parfum }}
                                </p>
                                @endif
                                @if($d->satuan)
                                <p class="text-xs text-gray-500">
                                    <i class="bi bi-box"></i> {{ $d->satuan->nama_satuan }}
                                </p>
                                @endif
                                <p class="text-sm mt-1">
                                    Qty: <b>{{ $d->qty }}</b> |
                                    Harga: Rp {{ number_format($d->harga,0,',','.') }}
                                </p>
                            </div>

                            <div class="text-right font-semibold">
                                Rp {{ number_format($subtotal,0,',','.') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 p-4 rounded text-center">
                        <p class="text-yellow-800">Tidak ada item pesanan</p>
                    </div>
                @endif
            </div>

            {{-- DATA PELANGGAN --}}
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="bi bi-person text-blue-600"></i> Data Pelanggan
                </h2>

                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nama</p>
                        <p class="font-semibold">{{ $pesanan->nama_pelanggan ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">No HP</p>
                        <p class="font-semibold">{{ $pesanan->no_hp ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-gray-500">Alamat</p>
                        <p class="font-semibold">{{ $pesanan->pelanggan->alamat ?? '-' }}</p>
                    </div>
                    
                    {{-- ✅ ESTIMASI SELESAI --}}
                    @if($pesanan->tgl_estimasi)
                    <div class="md:col-span-2">
                        <p class="text-gray-500">Estimasi Selesai</p>
                        @php
                            $estimasi = \Carbon\Carbon::parse($pesanan->tgl_estimasi);
                            $today = \Carbon\Carbon::today();
                            $daysLeft = $today->diffInDays($estimasi, false);
                        @endphp
                        
                        <div class="flex items-center gap-2">
                            <p class="font-semibold">
                                {{ $estimasi->format('d F Y') }}
                            </p>
                            
                            @if($daysLeft < 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    Terlambat {{ abs($daysLeft) }} hari
                                </span>
                            @elseif($daysLeft == 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold animate-pulse">
                                    <i class="bi bi-clock-fill"></i>
                                    Hari ini
                                </span>
                            @elseif($daysLeft == 1)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                    <i class="bi bi-hourglass-split"></i>
                                    Besok
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                                    <i class="bi bi-calendar-check"></i>
                                    {{ $daysLeft }} hari lagi
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- FOTO BUKTI CUCIAN --}}
            @if($pesanan->foto_bukti)
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="bi bi-camera text-purple-600"></i> Foto Bukti Cucian
                </h2>

                <div class="relative group">
                    <img src="{{ asset('storage/' . $pesanan->foto_bukti) }}" 
                         alt="Bukti Cucian" 
                         class="w-full h-64 object-cover rounded-lg border-2 border-gray-200 cursor-pointer hover:border-purple-400 transition"
                         onclick="openImageModal('{{ asset('storage/' . $pesanan->foto_bukti) }}')">
                    
                    <div class="absolute top-2 right-2 bg-purple-600 text-white px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 shadow-lg">
                        <i class="bi bi-check-circle"></i>
                        <span>Verified</span>
                    </div>

                    <div class="absolute bottom-2 left-2 right-2 bg-black/50 text-white px-3 py-2 rounded-lg text-xs opacity-0 group-hover:opacity-100 transition">
                        <i class="bi bi-zoom-in"></i> Klik untuk memperbesar
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                    <span><i class="bi bi-calendar"></i> Upload: {{ $pesanan->updated_at->format('d/m/Y H:i') }}</span>
                    <a href="{{ asset('storage/' . $pesanan->foto_bukti) }}" 
                       download 
                       class="text-purple-600 hover:text-purple-700 font-semibold">
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>
            </div>
            @endif
        </div>

        {{-- ================= RIGHT CONTENT ================= --}}
        <div class="space-y-6">

            {{-- STATUS --}}
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="font-bold mb-4">Status</h2>

                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Status Transaksi</p>
                        <span class="inline-block px-3 py-1 rounded-full text-white text-xs
                            @if($pesanan->status_transaksi=='menunggu_konfirmasi' || $pesanan->status_transaksi=='antrian') bg-orange-500
                            @elseif($pesanan->status_transaksi=='dikonfirmasi') bg-blue-500
                            @elseif($pesanan->status_transaksi=='proses') bg-purple-600
                            @elseif($pesanan->status_transaksi=='selesai') bg-green-600
                            @elseif($pesanan->status_transaksi=='pick_up') bg-yellow-500
                            @else bg-gray-500
                            @endif">
                            {{ str_replace('_',' ',$pesanan->status_transaksi) }}
                        </span>
                    </div>

                    <div>
                        <p class="text-gray-500">Pembayaran</p>
                        <span class="font-semibold">
                            @if($pesanan->status_bayar == 'lunas')
                                <span class="text-green-600">Lunas</span>
                            @elseif($pesanan->status_bayar == 'DP')
                                <span class="text-blue-600">DP</span>
                            @else
                                <span class="text-red-600">Belum Bayar</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

           {{-- RINGKASAN --}}
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="font-bold mb-4">Ringkasan</h2>

                <div class="space-y-2 text-sm">
                    @php
                        $subtotalAll = 0;
                        $totalQtyAll = 0;
                        
                        if($pesanan->detail_transaksi) {
                            foreach($pesanan->detail_transaksi as $d) {
                                $subtotalAll += ($d->harga * $d->qty);
                                $totalQtyAll += $d->qty;
                            }
                        }
                    @endphp
                    
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <b>Rp {{ number_format($subtotalAll,0,',','.') }}</b>
                    </div>
                    
                    {{-- ✅ BIAYA ONGKIR --}}
                    @if($pesanan->id_biaya_tambahan && $pesanan->biayaTambahan)
                    <div class="flex justify-between text-orange-600">
                        <span>
                            <i class="bi bi-truck text-xs"></i> Biaya Ongkir
                        </span>
                        <b>+ Rp {{ number_format($pesanan->biayaTambahan->nominal,0,',','.') }}</b>
                    </div>
                    @endif
                    
                    @if($pesanan->diskon > 0)
                    <div class="flex justify-between text-red-600">
                        <span>
                            Diskon
                            @if($pesanan->tipe_diskon == 'percent' && $subtotalAll > 0)
                                <span class="text-xs">({{ number_format(($pesanan->diskon / $subtotalAll) * 100, 1) }}%)</span>
                            @endif
                        </span>
                        <b>- Rp {{ number_format($pesanan->diskon,0,',','.') }}</b>
                    </div>
                    @endif
                    
                    @if($totalQtyAll > 0)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Total Item</span>
                        <b>{{ $totalQtyAll }} item</b>
                    </div>
                    @endif
                    
                    <hr>
                    <div class="flex justify-between text-lg font-bold text-yellow-600">
                        <span>Total</span>
                        <span>Rp {{ number_format($pesanan->total_harga,0,',','.') }}</span>
                    </div>

                    @if($pesanan->dp > 0)
                    <div class="flex justify-between bg-blue-50 -mx-2 px-2 py-2 rounded">
                        <span class="text-blue-600">DP Dibayar</span>
                        <b class="text-blue-600">Rp {{ number_format($pesanan->dp,0,',','.') }}</b>
                    </div>
                    @endif

                    @if($pesanan->total_bayar > 0)
                    <div class="flex justify-between bg-green-50 -mx-2 px-2 py-2 rounded">
                        <span class="text-green-600">Total Dibayar</span>
                        <b class="text-green-600">Rp {{ number_format($pesanan->total_bayar,0,',','.') }}</b>
                    </div>
                    @endif

                    @php
                        $sisa = $pesanan->total_harga - $pesanan->total_bayar;
                    @endphp
                    @if($sisa > 0)
                    <div class="flex justify-between bg-red-50 -mx-2 px-2 py-2 rounded">
                        <span class="text-red-600 font-semibold">Sisa Pembayaran</span>
                        <b class="text-red-600">Rp {{ number_format($sisa,0,',','.') }}</b>
                    </div>
                    @elseif($sisa < 0)
                    <div class="flex justify-between bg-yellow-50 -mx-2 px-2 py-2 rounded">
                        <span class="text-yellow-600 font-semibold">Kembalian</span>
                        <b class="text-yellow-600">Rp {{ number_format(abs($sisa),0,',','.') }}</b>
                    </div>
                    @endif
                </div>
            </div>

            {{-- AKSI --}}
            <div class="bg-white rounded-xl shadow p-5 space-y-3">
                <h2 class="font-bold mb-2">Aksi</h2>

                @php
                    $dataLengkap = $pesanan->detail_transaksi->count() > 0 && $pesanan->total_harga > 0;
                    
                    // Refresh delivery data from database to get latest status
                    $deliveryPickup = $pesanan->delivery()->where('jenis', 'pickup')->first();
                    $deliveryAntar = $pesanan->delivery()->where('jenis', 'antar')->first();
                    
                    $metodeBayar = $pesanan->metodeBayar;
                    $isTransfer = $metodeBayar && (stripos($metodeBayar->nama_metode_bayar, 'transfer') !== false || stripos($metodeBayar->nama_metode_bayar, 'tf') !== false);
                    $isCash = $metodeBayar && (stripos($metodeBayar->nama_metode_bayar, 'cash') !== false || stripos($metodeBayar->nama_metode_bayar, 'tunai') !== false);
                @endphp

                {{-- ===================== STATUS PICK_UP ===================== --}}
                @if($pesanan->status_transaksi === 'pick_up')
                    
                    {{-- JIKA BELUM ADA DRIVER (PENDING) --}}
                    @if($deliveryPickup && $deliveryPickup->status === 'pending')
                    <div class="bg-orange-50 border border-orange-200 p-4 rounded-lg">
                        <p class="text-sm font-semibold text-orange-800 mb-2 flex items-center gap-2">
                            <i class="bi bi-truck"></i>
                            Menunggu Driver Pickup
                        </p>

                        <p class="text-xs text-orange-700 mb-3">
                            Pesanan masih di pelanggan. Silakan tentukan driver untuk menjemput cucian.
                        </p>

                        <a href="{{ route('pesanan.online.list-driver', $pesanan->id_transaksi) }}"
                        class="block w-full text-center py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-semibold flex items-center justify-center gap-2 shadow-md transition">
                            <i class="bi bi-person-check"></i>
                            Tentukan Driver
                        </a>
                    </div>
                    @elseif(!$deliveryPickup)
                    {{-- Kalau gak ada delivery sama sekali --}}
                    <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            ⚠️ Belum ada delivery pickup untuk pesanan ini. Silakan hubungi admin.
                        </p>
                    </div>
                    @endif

                    {{-- JIKA SUDAH ADA DRIVER (ACCEPTED) --}}
                    @if($deliveryPickup && $deliveryPickup->status === 'accepted')
                    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                        <p class="text-sm font-semibold text-blue-800 mb-2 flex items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Driver Sudah Ditentukan
                        </p>

                        <div class="bg-white p-3 rounded-lg mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ strtoupper(substr($deliveryPickup->driver->nama_driver ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold">{{ $deliveryPickup->driver->nama_driver ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">📱 {{ $deliveryPickup->driver->no_hp ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-blue-700 flex items-start gap-2">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Driver sedang dalam perjalanan untuk menjemput cucian. Menunggu driver tiba di laundry.</span>
                        </p>
                    </div>
                    @endif

                    {{-- JIKA CUCIAN SUDAH TIBA DI LAUNDRY (ARRIVED_AT_LAUNDRY) --}}
                    @if($deliveryPickup && $deliveryPickup->status === 'arrived_at_laundry')
                    <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                        <p class="text-sm font-semibold text-green-800 mb-2 flex items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Cucian Sudah Tiba di Laundry
                        </p>

                        <div class="bg-white p-3 rounded-lg mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ strtoupper(substr($deliveryPickup->driver->nama_driver ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold">{{ $deliveryPickup->driver->nama_driver ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">📱 {{ $deliveryPickup->driver->no_hp ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-green-700 mb-3 flex items-start gap-2">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Cucian sudah sampai di laundry. Silakan isi data pesanan untuk melanjutkan proses.</span>
                        </p>
                    </div>

                    {{-- BUTTON ISI DATA PESANAN UNTUK ARRIVED_AT_LAUNDRY --}}
                    <button onclick="openIsiDataModal()" 
                            class="w-full py-3 bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600 flex items-center justify-center gap-2 shadow-md transition">
                        <i class="bi bi-pencil-square"></i> 
                        <span>{{ $dataLengkap ? 'Edit' : 'Isi' }} Data Pesanan</span>
                    </button>
                    @endif

                    {{-- INFO STATUS PICKUP DINAMIS --}}
                    @if($deliveryPickup)
                    <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-hourglass-split text-yellow-600 mt-0.5"></i>
                            <div class="text-sm text-yellow-800">
                                @if($deliveryPickup->status === 'pending')
                                    <p class="font-semibold">Status: Menunggu Driver</p>
                                    <p class="text-xs mt-1">Pesanan masih di pelanggan. Tentukan driver untuk menjemput cucian.</p>
                                @elseif($deliveryPickup->status === 'accepted')
                                    <p class="font-semibold">Status: Driver Ditentukan</p>
                                    <p class="text-xs mt-1">Driver sudah ditentukan dan siap berangkat ke lokasi pelanggan.</p>
                                @elseif($deliveryPickup->status === 'on_the_way_to_pickup')
                                    <p class="font-semibold">Status: Driver Menuju Pelanggan</p>
                                    <p class="text-xs mt-1">Driver sedang dalam perjalanan menuju lokasi pelanggan untuk menjemput cucian.</p>
                                @elseif($deliveryPickup->status === 'picked_up')
                                    <p class="font-semibold">Status: Cucian Sudah Dijemput</p>
                                    <p class="text-xs mt-1">Driver sudah mengambil cucian dari pelanggan dan akan menuju laundry.</p>
                                @elseif($deliveryPickup->status === 'on_the_way_to_laundry')
                                    <p class="font-semibold">Status: Driver Menuju Laundry</p>
                                    <p class="text-xs mt-1">Driver sedang dalam perjalanan menuju laundry dengan cucian pelanggan.</p>
                                @elseif($deliveryPickup->status === 'arrived_at_laundry')
                                    <p class="font-semibold">Status: Tiba di Laundry - Siap Diproses</p>
                                    <p class="text-xs mt-1">Cucian sudah tiba di laundry. Isi data pesanan untuk masuk ke antrian.</p>
                                @else
                                    <p class="font-semibold">Status: Menunggu Pickup</p>
                                    <p class="text-xs mt-1">Pesanan akan masuk ke antrian setelah driver tiba di laundry dengan cucian.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-hourglass-split text-yellow-600 mt-0.5"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-semibold">Status: Menunggu Pickup</p>
                                <p class="text-xs mt-1">Belum ada driver yang ditentukan untuk pesanan ini.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- CETAK NOTA --}}
                    <button onclick="window.print()"
                            class="w-full py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center justify-center gap-2">
                        <i class="bi bi-printer"></i>
                        <span>Cetak Nota</span>
                    </button>

                {{-- ===================== STATUS SIAP_DI_ANTAR ===================== --}}
                @elseif($pesanan->status_transaksi === 'siap_di_antar')
                    
                    {{-- JIKA BELUM ADA DRIVER ANTAR (PENDING ATAU TIDAK ADA) --}}
                    @if(!$deliveryAntar || $deliveryAntar->status === 'pending')
                    <div class="bg-orange-50 border border-orange-200 p-4 rounded-lg">
                        <p class="text-sm font-semibold text-orange-800 mb-2 flex items-center gap-2">
                            <i class="bi bi-truck"></i>
                            Menunggu Driver Delivery
                        </p>

                        <p class="text-xs text-orange-700 mb-3">
                            Cucian sudah selesai dan siap diantar. Silakan tentukan driver untuk mengantar cucian ke pelanggan.
                        </p>

                        @if($deliveryAntar)
                        <a href="{{ route('pesanan.online.list-driver', $pesanan->id_transaksi) }}"
                        class="block w-full text-center py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-semibold flex items-center justify-center gap-2 shadow-md transition">
                            <i class="bi bi-person-check"></i>
                            Tentukan Driver
                        </a>
                        @else
                        <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg mt-2">
                            <p class="text-sm text-yellow-800">
                                ⚠️ Belum ada delivery antar untuk pesanan ini. Silakan buat delivery terlebih dahulu.
                            </p>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- JIKA SUDAH ADA DRIVER ANTAR (ACCEPTED) --}}
                    @if($deliveryAntar && $deliveryAntar->status === 'accepted')
                    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                        <p class="text-sm font-semibold text-blue-800 mb-2 flex items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Driver Delivery Sudah Ditentukan
                        </p>

                        <div class="bg-white p-3 rounded-lg mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ strtoupper(substr($deliveryAntar->driver->nama_driver ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold">{{ $deliveryAntar->driver->nama_driver ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">📱 {{ $deliveryAntar->driver->no_hp ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-lg mb-3">
                            <p class="text-xs text-blue-800 flex items-start gap-2">
                                <i class="bi bi-geo-alt-fill text-blue-600 mt-0.5"></i>
                                <span><strong>Tujuan:</strong> {{ $deliveryAntar->alamat_tujuan ?? $pesanan->pelanggan->alamat ?? '-' }}</span>
                            </p>
                        </div>

                        <p class="text-xs text-blue-700 flex items-start gap-2">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Driver siap mengantar cucian ke alamat pelanggan.</span>
                        </p>
                    </div>
                    @endif

                    {{-- JIKA DRIVER SEDANG MENUJU PELANGGAN (ON_THE_WAY_TO_CUSTOMER) --}}
                    @if($deliveryAntar && $deliveryAntar->status === 'on_the_way_to_customer')
                    <div class="bg-purple-50 border border-purple-200 p-4 rounded-lg">
                        <p class="text-sm font-semibold text-purple-800 mb-2 flex items-center gap-2">
                            <i class="bi bi-bicycle"></i>
                            Driver Menuju Pelanggan
                        </p>

                        <div class="bg-white p-3 rounded-lg mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ strtoupper(substr($deliveryAntar->driver->nama_driver ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold">{{ $deliveryAntar->driver->nama_driver ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">📱 {{ $deliveryAntar->driver->no_hp ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 p-3 rounded-lg mb-3">
                            <p class="text-xs text-purple-800 flex items-start gap-2">
                                <i class="bi bi-geo-alt-fill text-purple-600 mt-0.5"></i>
                                <span><strong>Tujuan:</strong> {{ $deliveryAntar->alamat_tujuan ?? $pesanan->pelanggan->alamat ?? '-' }}</span>
                            </p>
                        </div>

                        <p class="text-xs text-purple-700 flex items-start gap-2">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Driver sedang dalam perjalanan menuju lokasi pelanggan untuk mengantar cucian.</span>
                        </p>
                    </div>
                    @endif

                    {{-- JIKA CUCIAN SUDAH DIANTAR (DELIVERED) --}}
                    @if($deliveryAntar && $deliveryAntar->status === 'delivered')
                    <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                        <p class="text-sm font-semibold text-green-800 mb-2 flex items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Cucian Sudah Diantar
                        </p>

                        <div class="bg-white p-3 rounded-lg mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ strtoupper(substr($deliveryAntar->driver->nama_driver ?? 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold">{{ $deliveryAntar->driver->nama_driver ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">📱 {{ $deliveryAntar->driver->no_hp ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-green-700 mb-3 flex items-start gap-2">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Cucian sudah berhasil diantar ke pelanggan. Pesanan siap diselesaikan.</span>
                        </p>
                    </div>
                    @endif

                    {{-- INFO STATUS DELIVERY DINAMIS --}}
                    @if($deliveryAntar)
                    <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-hourglass-split text-yellow-600 mt-0.5"></i>
                            <div class="text-sm text-yellow-800">
                                @if($deliveryAntar->status === 'pending')
                                    <p class="font-semibold">Status: Menunggu Driver</p>
                                    <p class="text-xs mt-1">Cucian siap diantar. Tentukan driver untuk mengantar cucian ke pelanggan.</p>
                                @elseif($deliveryAntar->status === 'accepted')
                                    <p class="font-semibold">Status: Driver Ditentukan</p>
                                    <p class="text-xs mt-1">Driver sudah ditentukan dan siap mengantar cucian ke lokasi pelanggan.</p>
                                @elseif($deliveryAntar->status === 'on_the_way_to_customer')
                                    <p class="font-semibold">Status: Driver Menuju Pelanggan</p>
                                    <p class="text-xs mt-1">Driver sedang dalam perjalanan menuju lokasi pelanggan untuk mengantar cucian.</p>
                                @elseif($deliveryAntar->status === 'delivered')
                                    <p class="font-semibold">Status: Cucian Sudah Diantar</p>
                                    <p class="text-xs mt-1">Cucian sudah berhasil diantar ke pelanggan. Silakan selesaikan pesanan.</p>
                                @else
                                    <p class="font-semibold">Status: Menunggu Delivery</p>
                                    <p class="text-xs mt-1">Pesanan menunggu untuk diantar ke pelanggan.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-hourglass-split text-yellow-600 mt-0.5"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-semibold">Status: Menunggu Delivery</p>
                                <p class="text-xs mt-1">Belum ada driver yang ditentukan untuk mengantar pesanan ini.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                  {{-- ✅ BUTTON PEMBAYARAN CASH (UNTUK DELIVERY DELIVERED) --}}
                    @if($pesanan->id_metode_bayar && $isCash && $deliveryAntar && $deliveryAntar->status === 'delivered')
                    <a href="{{ route('pesanan.online.bukti-pembayaran', $pesanan->id_transaksi) }}"
                    class="block text-center py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center justify-center gap-2">
                        <i class="bi bi-cash-stack"></i>
                        <span>Konfirmasi Pembayaran Cash</span>
                    </a>
                    @endif

                    {{-- SELESAI --}}
                    <a href="{{ route('pesanan.online.selesai',$pesanan->id_transaksi) }}"
                    class="block text-center py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle"></i>
                        <span>Selesaikan Pesanan</span>
                    </a>

                    {{-- CETAK NOTA --}}
                    <button onclick="window.print()"
                            class="w-full py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center justify-center gap-2">
                        <i class="bi bi-printer"></i>
                        <span>Cetak Nota</span>
                    </button>

                {{-- ===================== STATUS LAINNYA (BUKAN PICK_UP DAN SIAP_DI_ANTAR) ===================== --}}
                @else

                    {{-- BUTTON ISI DATA PESANAN --}}
                    @if(in_array($pesanan->status_transaksi, ['antrian']) && !in_array($pesanan->status_transaksi, ['selesai', 'ditolak']))
                    <button onclick="openIsiDataModal()" 
                            class="w-full py-2 bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600 flex items-center justify-center gap-2">
                        <i class="bi bi-pencil-square"></i> 
                        <span>{{ $dataLengkap ? 'Edit' : 'Isi' }} Data Pesanan</span>
                    </button>
                    @endif

                    {{-- WARNING JIKA BELUM ISI DATA --}}
                    @if(!$dataLengkap && in_array($pesanan->status_transaksi, ['antrian', 'menunggu_konfirmasi', 'dikonfirmasi']))
                    <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                        <div class="flex items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-amber-600 mt-0.5"></i>
                            <div class="text-sm text-amber-800">
                                <p class="font-semibold">Data Pesanan Belum Lengkap</p>
                                <p class="text-xs mt-1">Silakan isi data pesanan terlebih dahulu sebelum melanjutkan proses.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ✅ BUTTON KONFIRMASI PESANAN (KOSONG - AKAN DIISI FCM NANTI) --}}
                    @if($dataLengkap && !$pesanan->id_metode_bayar && in_array($pesanan->status_transaksi, ['antrian', 'menunggu_konfirmasi', 'dikonfirmasi']))
                    <button onclick="alert('Fitur notifikasi akan segera ditambahkan via FCM')" 
                            class="w-full py-2 bg-indigo-500 text-white rounded-lg font-semibold hover:bg-indigo-600 flex items-center justify-center gap-2">
                        <i class="bi bi-bell"></i> 
                        <span>Kirim Notifikasi ke Pelanggan</span>
                    </button>
                    @endif

                   {{-- BUKTI PEMBAYARAN (TRANSFER) --}}
                    @if($pesanan->id_metode_bayar && $isTransfer && in_array($pesanan->status_transaksi, ['antrian', 'menunggu_konfirmasi', 'dikonfirmasi', 'proses', 'siap_di_ambil', 'siap_di_antar']))
                    <a href="{{ route('pesanan.online.bukti-pembayaran', $pesanan->id_transaksi) }}"
                    class="block text-center py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 flex items-center justify-center gap-2">
                        <i class="bi bi-receipt"></i>
                        <span>Bukti Pembayaran (TF)</span>
                    </a>
                    @endif


                    {{-- PROSES --}}
                    @if($dataLengkap && in_array($pesanan->status_transaksi, ['antrian', 'menunggu_konfirmasi', 'dikonfirmasi']))
                    <a href="{{ route('pesanan.online.proses',$pesanan->id_transaksi) }}"
                    class="block text-center py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center justify-center gap-2">
                        <i class="bi bi-play-circle"></i>
                        <span>Mulai Proses</span>
                    </a>
                    @endif

                  {{-- PICKUP / DELIVERY --}}
@if($pesanan->status_transaksi == 'proses')
    @php
        $terlambat = false;
        if ($pesanan->tgl_estimasi) {
            $estimasi = \Carbon\Carbon::parse($pesanan->tgl_estimasi);
            $today = \Carbon\Carbon::today();
            $terlambat = $today->greaterThan($estimasi);
        }
    @endphp

    @if($terlambat)
        {{-- ✅ JIKA TERLAMBAT: LANGSUNG DELIVERY --}}
        <div class="bg-red-50 border border-red-300 p-4 rounded-lg">
            <div class="flex items-start gap-3 mb-3">
                <i class="bi bi-exclamation-triangle-fill text-red-600 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-red-800">Pesanan Melewati Estimasi!</p>
                    <p class="text-xs text-red-700 mt-1">
                        Estimasi: {{ \Carbon\Carbon::parse($pesanan->tgl_estimasi)->format('d F Y') }}
                    </p>
                    <p class="text-xs text-red-600 mt-2">
                        Untuk mempercepat proses, silakan gunakan layanan <b>Delivery</b>.
                    </p>
                </div>
            </div>

            <a href="{{ route('pesanan.online.list-driver', $pesanan->id_transaksi) }}"
               class="block w-full text-center py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-semibold flex items-center justify-center gap-2 shadow-md transition">
                <i class="bi bi-bicycle"></i> Pilih Driver Delivery
            </a>
        </div>
    @else
        {{-- NORMAL: PILIHAN PICKUP / DELIVERY --}}
            <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                <p class="text-sm font-semibold text-blue-800 mb-2">
                    <i class="bi bi-truck"></i> Pilih Metode Pengambilan:
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('pesanan.online.siap_di_ambil', $pesanan->id_transaksi) }}"
                    class="block text-center py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 text-sm font-medium">
                        <i class="bi bi-shop"></i> Pick Up
                    </a>
                    <button onclick="openDeliveryModal()"
                            class="py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 text-sm font-medium">
                        <i class="bi bi-bicycle"></i> Delivery
                    </button>
                </div>
            </div>
        @endif
    @endif

                    {{-- SELESAI --}}
                    @if(in_array($pesanan->status_transaksi, ['siap_di_ambil', 'siap_di_antar']))
                    <a href="{{ route('pesanan.online.selesai',$pesanan->id_transaksi) }}"
                    class="block text-center py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle"></i>
                        <span>Selesaikan Pesanan</span>
                    </a>
                    @endif

                    {{-- CETAK NOTA --}}
                    <button onclick="window.print()"
                            class="w-full py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center justify-center gap-2">
                        <i class="bi bi-printer"></i>
                        <span>Cetak Nota</span>
                    </button>

                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL ISI DATA PESANAN ================= --}}
<div id="isiDataModal"
     class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden"
     onclick="closeIsiDataModal()">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto"
         onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4 sticky top-0 bg-white pb-3 border-b">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="bi bi-pencil-square text-purple-600"></i> Isi Data Pesanan
            </h3>
            <button type="button" onclick="closeIsiDataModal()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        <form action="{{ route('pesanan.online.updateData', $pesanan->id_transaksi) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                {{-- ITEMS --}}
                @foreach($pesanan->detail_transaksi as $i => $d)
                <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                    <p class="font-semibold text-sm text-blue-800 mb-2">
                        {{ $d->layanan->nama_layanan ?? 'Layanan' }}
                    </p>
                    <p class="text-xs text-gray-600 mb-2">
                        {{ $d->jenis->nama_jenis ?? '-' }} — 
                        <b>Rp {{ number_format($d->jenis->harga,0,',','.') }} / {{ $d->jenis->satuan->nama_satuan ?? '' }}</b>
                    </p>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            Qty <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="qty[]" step="0.01" min="0.01" value="{{ $d->qty }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                    <input type="hidden" name="id_detail[]" value="{{ $d->id_detail_transaksi }}">
                </div>
                @endforeach

                {{-- ✅ BIAYA ONGKIR (BARU) --}}
                <div class="bg-orange-50 border border-orange-200 p-4 rounded-lg">
                    <h4 class="font-semibold text-sm text-orange-800 mb-3 flex items-center gap-2">
                        <i class="bi bi-truck"></i> Biaya Ongkir (Opsional)
                    </h4>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Pilih Biaya Ongkir</label>
                        <select name="id_biaya_tambahan" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">-- Tidak Ada Ongkir --</option>
                            @foreach($biayaTambahan as $bt)
                            <option value="{{ $bt->id_biaya_tambahan }}" 
                                    {{ ($pesanan->id_biaya_tambahan ?? '') == $bt->id_biaya_tambahan ? 'selected' : '' }}>
                                Rp {{ number_format($bt->nominal, 0, ',', '.') }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-2 flex items-start gap-1">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Pilih biaya ongkir jika ada pengiriman/pickup. Kosongkan jika tidak ada ongkir.</span>
                        </p>
                    </div>
                </div>

                {{-- ✅ TANGGAL ESTIMASI SELESAI --}}
                <div class="bg-teal-50 border border-teal-200 p-4 rounded-lg">
                    <h4 class="font-semibold text-sm text-teal-800 mb-3 flex items-center gap-2">
                        <i class="bi bi-calendar-check"></i> Estimasi Selesai
                    </h4>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">
                            Tanggal Estimasi Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="tgl_estimasi" 
                               id="tgl_estimasi"
                               value="{{ $pesanan->tgl_estimasi ?? date('Y-m-d', strtotime('+3 days')) }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               required>
                        <p class="text-xs text-gray-500 mt-2 flex items-start gap-1">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Perkiraan tanggal kapan cucian akan selesai dikerjakan. Default: 3 hari dari sekarang.</span>
                        </p>
                    </div>

                    {{-- Quick Select Buttons --}}
                    <div class="mt-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Quick Select:</p>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" 
                                    onclick="setEstimasiDate(1)"
                                    class="px-3 py-2 text-xs bg-white border border-teal-300 rounded-lg hover:bg-teal-50 transition">
                                <i class="bi bi-lightning-fill text-teal-600"></i> 1 Hari
                            </button>
                            <button type="button" 
                                    onclick="setEstimasiDate(2)"
                                    class="px-3 py-2 text-xs bg-white border border-teal-300 rounded-lg hover:bg-teal-50 transition">
                                <i class="bi bi-clock text-teal-600"></i> 2 Hari
                            </button>
                            <button type="button" 
                                    onclick="setEstimasiDate(3)"
                                    class="px-3 py-2 text-xs bg-white border border-teal-300 rounded-lg hover:bg-teal-50 transition">
                                <i class="bi bi-calendar text-teal-600"></i> 3 Hari
                            </button>
                        </div>
                    </div>
                </div>

                {{-- UPLOAD FOTO BUKTI CUCIAN --}}
                <div class="bg-indigo-50 border border-indigo-200 p-4 rounded-lg">
                    <h4 class="font-semibold text-sm text-indigo-800 mb-3 flex items-center gap-2">
                        <i class="bi bi-camera"></i> Foto Bukti Cucian (Opsional)
                    </h4>

                    {{-- Preview Foto Existing --}}
                    @if($pesanan->foto_bukti)
                    <div class="mb-3">
                        <p class="text-xs text-gray-600 mb-2">Foto saat ini:</p>
                        <div class="relative inline-block">
                            <img src="{{ asset('storage/' . $pesanan->foto_bukti) }}" 
                                 alt="Bukti Cucian" 
                                 class="w-32 h-32 object-cover rounded-lg border-2 border-indigo-200">
                            <div class="absolute -top-2 -right-2 bg-indigo-600 text-white rounded-full p-1">
                                <i class="bi bi-check text-xs"></i>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">
                            {{ $pesanan->foto_bukti ? 'Ganti Foto' : 'Upload Foto' }}
                        </label>
                        <input type="file" 
                               name="foto_bukti" 
                               id="foto_bukti"
                               accept="image/jpeg,image/jpg,image/png"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200">
                        <p class="text-xs text-gray-500 mt-2 flex items-start gap-1">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Format: JPG, JPEG, PNG. Maksimal 2MB. Upload foto sebagai bukti bahwa cucian sudah diterima.</span>
                        </p>
                    </div>

                    {{-- Preview Gambar Sebelum Upload --}}
                    <div id="imagePreview" class="mt-3 hidden">
                        <p class="text-xs text-gray-600 mb-2">Preview:</p>
                        <img id="previewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-indigo-200">
                    </div>
                </div>

                {{-- DISKON SECTION --}}
                <div class="bg-red-50 border border-red-200 p-4 rounded-lg">
                    <h4 class="font-semibold text-sm text-red-800 mb-3 flex items-center gap-2">
                        <i class="bi bi-percent"></i> Diskon (Opsional)
                    </h4>

                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Tipe Diskon</label>
                        <div class="flex gap-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="tipe_diskon" value="nominal" 
                                       {{ ($pesanan->tipe_diskon ?? 'nominal') == 'nominal' ? 'checked' : '' }}
                                       class="mr-2 text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Nominal (Rp)</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="tipe_diskon" value="percent" 
                                       {{ ($pesanan->tipe_diskon ?? 'nominal') == 'percent' ? 'checked' : '' }}
                                       class="mr-2 text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Persen (%)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nilai Diskon</label>
                        <input type="number" name="diskon" step="0.01" min="0"
                               value="{{ $pesanan->diskon ?? 0 }}" placeholder="0"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-info-circle"></i> Kosongkan jika tidak ada diskon
                        </p>
                    </div>
                </div>

                {{-- KETERANGAN --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan (Opsional)</label>
                    <textarea name="keterangan" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                              placeholder="Tambahkan catatan jika ada...">{{ $pesanan->keterangan ?? '' }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6 sticky bottom-0 bg-white pt-3 border-t">
                <button type="button" onclick="closeIsiDataModal()"
                        class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 font-semibold">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DELIVERY --}}
<div id="deliveryModal"
     class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden"
     onclick="closeDeliveryModal()">

    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl"
         onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="bi bi-bicycle text-orange-600"></i> Delivery
            </h3>
            <button onclick="closeDeliveryModal()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        <p class="text-sm text-gray-600 mb-4">
            Pesanan akan dikirim ke alamat pelanggan. Pastikan alamat sudah benar.
        </p>

        <div class="bg-gray-50 p-3 rounded-lg mb-4 text-sm border border-gray-200">
            <p class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="bi bi-geo-alt-fill text-orange-500"></i>
                Alamat Pengiriman:
            </p>
            <p class="text-gray-600 mt-1 ml-6">{{ $pesanan->pelanggan->alamat ?? '-' }}</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg mb-4">
            <div class="flex items-start gap-2">
                <i class="bi bi-info-circle-fill text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold">Langkah Selanjutnya</p>
                    <p class="text-xs mt-1">Anda akan diarahkan untuk memilih driver yang akan mengantar pesanan ini.</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="button" 
                    onclick="closeDeliveryModal()"
                    class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold">
                Batal
            </button>
            <a href="{{ route('pesanan.online.list-driver', $pesanan->id_transaksi) }}"
               class="flex-1 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-semibold text-center flex items-center justify-center gap-2 shadow-md">
                <i class="bi bi-person-check"></i>
                Tentukan Driver
            </a>
        </div>
    </div>
</div>

{{-- MODAL ZOOM FOTO --}}
<div id="imageModal" class="fixed inset-0 bg-black/90 flex items-center justify-center px-4 z-[9999] hidden" onclick="closeImageModal()">
    <div class="relative max-w-5xl w-full">
        <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white text-4xl hover:text-gray-300 transition">
            <i class="bi bi-x-circle"></i>
        </button>
        <img id="zoomedImage" src="" alt="Zoomed Image" class="w-full h-auto rounded-lg shadow-2xl">
    </div>
</div>

<style>
@media print {
    .sticky, button, a[href*="route"] {
        display: none !important;
    }
    .bg-gray-50 {
        background: white !important;
    }
    .shadow-lg, .shadow-md {
        box-shadow: none !important;
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slideDown {
    animation: slideDown 0.3s ease-out;
}
</style>

<script>
function closeAlert() {
    const alert = document.getElementById('successAlert');
    if (alert) {
        alert.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

// Auto close alert after 5 seconds
setTimeout(() => {
    closeAlert();
}, 5000);

function openIsiDataModal() {
    document.getElementById('isiDataModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeIsiDataModal() {
    document.getElementById('isiDataModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openDeliveryModal() {
    document.getElementById('deliveryModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeliveryModal() {
    document.getElementById('deliveryModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openImageModal(imageSrc) {
    document.getElementById('zoomedImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// ✅ Function untuk set estimasi date berdasarkan jumlah hari
function setEstimasiDate(days) {
    const today = new Date();
    today.setDate(today.getDate() + days);
    
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    
    const dateString = `${year}-${month}-${day}`;
    document.getElementById('tgl_estimasi').value = dateString;
}

document.getElementById('isiDataModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeIsiDataModal();
});

document.getElementById('deliveryModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeliveryModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeIsiDataModal();
        closeDeliveryModal();
        closeImageModal();
    }
});

// Preview image sebelum upload
document.getElementById('foto_bukti')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        // Validasi ukuran file (max 2MB)
        const maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            e.target.value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            return;
        }

        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG.');
            e.target.value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewImg').src = event.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('imagePreview').classList.add('hidden');
    }
});
</script>

@endsection