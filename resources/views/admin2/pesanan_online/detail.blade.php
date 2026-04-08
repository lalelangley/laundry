{{-- FE-DOC: Template frontend untuk resources/views/admin2/pesanan_online/detail.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
{{-- FE-DOC: Blok CSS khusus halaman ini. --}}
<style>
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

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slideIn {
    animation: slideIn 0.3s ease-out;
}

.animate-slideUp {
    animation: slideUp 0.3s ease-out;
}

.biaya-item {
    transition: all 0.3s ease;
}
</style>
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-6 py-4 rounded-b-2xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('admin2pesanan.online.index', ['tab' => request()->get('from_tab', 'pickup')]) }}"
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
                    <i class="bi bi-basket text-yellow-600"></i> Item Pesanan
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
                    <i class="bi bi-person text-orange-600"></i> Data Pelanggan
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
                    
                    {{-- ESTIMASI SELESAI --}}
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
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
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
                    <i class="bi bi-camera text-yellow-600"></i> Foto Bukti Cucian
                </h2>

                <div class="relative group">
                    <img src="{{ asset('storage/' . $pesanan->foto_bukti) }}" 
                        alt="Bukti Cucian" 
                        class="w-full h-64 object-cover rounded-lg border-2 border-gray-200 cursor-pointer hover:border-yellow-400 transition"
                        onclick="openImageModal('{{ asset('storage/' . $pesanan->foto_bukti) }}')">
                    
                    <div class="absolute top-2 right-2 bg-orange-600 text-white px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 shadow-lg">
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
                    class="text-yellow-600 hover:text-yellow-700 font-semibold">
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
                            @elseif($pesanan->status_transaksi=='dikonfirmasi') bg-yellow-500
                            @elseif($pesanan->status_transaksi=='proses') bg-orange-600
                            @elseif($pesanan->status_transaksi=='selesai') bg-green-600
                            @elseif($pesanan->status_transaksi=='pick_up') bg-yellow-500
                            @elseif($pesanan->status_transaksi=='selesai_dicuci') bg-yellow-600
                            @elseif($pesanan->status_transaksi=='siap_di_antar') bg-orange-500
                            @else bg-gray-500
                            @endif">
                            {{ str_replace('_',' ',$pesanan->status_transaksi) }}
                        </span>
                    </div>

                    {{-- Di bagian Status --}}
                    <div>
                        <p class="text-gray-500">Pembayaran</p>
                        <span class="font-semibold">
                            @if($pesanan->status_bayar == 'lunas')
                                <span class="text-green-600 flex items-center gap-1">
                                    <i class="bi bi-check-circle-fill"></i> Lunas
                                </span>
                            @elseif($pesanan->status_bayar == 'DP')
                                <span class="text-orange-600 flex items-center gap-1">
                                    <i class="bi bi-hourglass-split"></i> DP
                                </span>
                            @elseif($pesanan->status_bayar == 'belum_lunas')
                                <span class="text-red-600 flex items-center gap-1">
                                    <i class="bi bi-exclamation-circle"></i> Belum Lunas
                                </span>
                            @else
                                <span class="text-red-600 flex items-center gap-1">
                                    <i class="bi bi-x-circle"></i> Belum Bayar
                                </span>
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
            $subtotalItems = 0;
            $totalQtyAll = 0;
            
            if($pesanan->detail_transaksi) {
                foreach($pesanan->detail_transaksi as $d) {
                    $subtotalItems += ($d->harga * $d->qty);
                    $totalQtyAll += $d->qty;
                }
            }
            
            // ✅ HITUNG BIAYA TAMBAHAN
            $totalBiayaTambahan = 0;
            if($pesanan->biayaTambahan) {
                $totalBiayaTambahan = $pesanan->biayaTambahan->sum('nominal');
            }
            
            // ✅ HITUNG TOTAL DIBAYAR DARI TABEL PEMBAYARAN (bukan dari field dp)
            $totalDibayar = $pesanan->pembayaran()->sum('nominal');
            
            // ✅ HITUNG SISA
            $sisa = $pesanan->total_harga - $totalDibayar;
        @endphp
        
        <div class="flex justify-between">
            <span>Subtotal</span>
            <b>Rp {{ number_format($subtotalItems,0,',','.') }}</b>
        </div>
        
        @if($totalBiayaTambahan > 0)
        <div class="flex justify-between text-orange-600">
            <span>
                <i class="bi bi-plus-circle text-xs"></i> Biaya Tambahan
            </span>
            <b>+ Rp {{ number_format($totalBiayaTambahan,0,',','.') }}</b>
        </div>
        @endif
        
        @if($pesanan->diskon > 0)
        <div class="flex justify-between text-red-600">
            <span>
                <i class="bi bi-percent text-xs"></i> Diskon
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
        
        <hr class="my-2">
        
        {{-- ✅ TOTAL HARGA --}}
        <div class="flex justify-between text-lg font-bold text-yellow-600">
            <span>Total Harga</span>
            <span>Rp {{ number_format($pesanan->total_harga,0,',','.') }}</span>
        </div>

        {{-- ✅ TOTAL DIBAYAR (hanya 1x, dari tabel pembayaran) --}}
        @if($totalDibayar > 0)
        <div class="flex justify-between bg-green-50 -mx-2 px-2 py-2 rounded">
            <span class="text-green-600 font-semibold">
                <i class="bi bi-check-circle-fill"></i> Sudah Dibayar
            </span>
            <b class="text-green-600">Rp {{ number_format($totalDibayar,0,',','.') }}</b>
        </div>
        @endif

        {{-- ✅ SISA PEMBAYARAN atau KEMBALIAN --}}
        @if($sisa > 0)
        <div class="flex justify-between bg-red-50 -mx-2 px-2 py-2 rounded">
            <span class="text-red-600 font-semibold">
                <i class="bi bi-exclamation-circle"></i> Sisa Pembayaran
            </span>
            <b class="text-red-600">Rp {{ number_format($sisa,0,',','.') }}</b>
        </div>
        @elseif($sisa < 0)
        <div class="flex justify-between bg-yellow-50 -mx-2 px-2 py-2 rounded">
            <span class="text-yellow-600 font-semibold">
                <i class="bi bi-cash-coin"></i> Kembalian
            </span>
            <b class="text-yellow-600">Rp {{ number_format(abs($sisa),0,',','.') }}</b>
        </div>
        @elseif($totalDibayar > 0 && $sisa == 0)
        <div class="flex justify-between bg-green-50 -mx-2 px-2 py-2 rounded border-2 border-green-300">
            <span class="text-green-700 font-bold text-base">
                <i class="bi bi-check-circle-fill"></i> LUNAS
            </span>
            <b class="text-green-700 text-base">✓</b>
        </div>
        @endif
    </div>
</div>

        {{-- ✅ AKSI SECTION - CLEAN & FIXED --}}
<div class="bg-white rounded-xl shadow p-5 space-y-3">
    <h2 class="font-bold mb-2">Aksi</h2>

    @php
        $dataLengkap = $pesanan->detail_transaksi->count() > 0 && $pesanan->total_harga > 0;
        $deliveryPickup = $deliveryPickup ?? null;
        $deliveryAntar = $deliveryAntar ?? null;
        
        // ✅ AMBIL METODE BAYAR DARI TABEL PEMBAYARAN (prioritas utama)
        $pembayaranTerbaru = $pesanan->pembayaran()->latest()->first();
        
        if ($pembayaranTerbaru && $pembayaranTerbaru->id_metode_bayar) {
            $metodeBayar = $pembayaranTerbaru->metodeBayar;
        } else {
            $metodeBayar = $pesanan->metodeBayar;
        }
        
        $isTransfer = false;
        $isCash = false;
        
        if ($metodeBayar && isset($metodeBayar->nama_metode_bayar)) {
            $namaMetode = strtolower($metodeBayar->nama_metode_bayar);
            $isTransfer = (stripos($namaMetode, 'transfer') !== false || stripos($namaMetode, 'tf') !== false);
            $isCash = (stripos($namaMetode, 'cash') !== false || stripos($namaMetode, 'tunai') !== false);
        }
        
        $sudahLunas = $pesanan->status_bayar === 'lunas';
    @endphp

    {{-- ========== STATUS: PICK_UP ========== --}}
    @if($pesanan->status_transaksi === 'pick_up')
        {{-- Driver sudah sampai di laundry --}}
        @if($deliveryPickup && $deliveryPickup->status === 'arrived_at_laundry')
            <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shrink-0">
                        <i class="bi bi-check-circle-fill text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-green-900 mb-1">Driver Sudah Sampai di Laundry</p>
                        
                        @if($deliveryPickup->driver)
                        <p class="text-sm text-green-700">
                            <i class="bi bi-person-badge"></i> {{ $deliveryPickup->driver->nama_driver }}
                        </p>
                        @endif
                        
                        <p class="text-xs text-green-600 mt-1">
                            <i class="bi bi-info-circle"></i> Cucian sudah tiba, silakan isi data pesanan.
                        </p>
                        
                        @if($deliveryPickup->waktu)
                        <p class="text-xs text-green-600">
                            <i class="bi bi-clock"></i> Tiba: {{ \Carbon\Carbon::parse($deliveryPickup->waktu)->format('d M Y, H:i') }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tombol Isi Data Pesanan --}}
            <button onclick="openIsiDataModal()" 
                    class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl font-semibold hover:from-yellow-600 hover:to-orange-600 flex items-center justify-center gap-2 shadow-lg transition-all hover:shadow-xl hover:scale-105">
                <i class="bi bi-pencil-square text-lg"></i> 
                <span>{{ $dataLengkap ? 'Edit' : 'Isi' }} Data Pesanan</span>
            </button>

            {{-- Info otomatis ke antrian --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-3">
                <div class="flex items-start gap-2">
                    <i class="bi bi-info-circle-fill text-orange-600 mt-0.5"></i>
                    <p class="text-xs text-yellow-700">
                        <strong>Info:</strong> Setelah data pesanan disimpan, status akan otomatis berubah menjadi <strong>"Antrian"</strong> dan siap untuk diproses.
                    </p>
                </div>
            </div>

        {{-- Driver sudah assigned tapi belum sampai --}}
        @elseif($deliveryPickup && $deliveryPickup->id_driver && $deliveryPickup->driver)
            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center shrink-0">
                        <i class="bi bi-person-check-fill text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-yellow-700 mb-1">Driver Pickup Ditugaskan</p>
                        
                        <p class="text-sm text-yellow-700">
                            <i class="bi bi-person-badge"></i> {{ $deliveryPickup->driver->nama_driver }}
                        </p>
                        
                        @if($deliveryPickup->driver->no_hp)
                        <p class="text-sm text-yellow-700">
                            <i class="bi bi-telephone"></i> {{ $deliveryPickup->driver->no_hp }}
                        </p>
                        @endif
                        
                        <p class="text-xs text-orange-600 mt-1">
                            <i class="bi bi-info-circle"></i> Status: 
                            <span class="font-semibold">
                                {{ str_replace('_', ' ', ucwords($deliveryPickup->status, '_')) }}
                            </span>
                        </p>
                        
                        @if($deliveryPickup->waktu)
                        <p class="text-xs text-orange-600">
                            <i class="bi bi-clock"></i> 
                            {{ \Carbon\Carbon::parse($deliveryPickup->waktu)->format('d M Y, H:i') }}
                        </p>
                        @endif
                        
                        @if($deliveryPickup->catatan)
                        <p class="text-xs text-orange-600 mt-2 bg-yellow-100 p-2 rounded">
                            <i class="bi bi-chat-left-text"></i> {{ $deliveryPickup->catatan }}
                        </p>
                        @endif
                    </div>
                </div>
                
                {{-- Tombol Driver Sampai --}}
                <a href="{{ route('admin2.pesanan.online.driver-arrive', $pesanan->id_transaksi) }}" 
                   class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl font-bold hover:from-yellow-600 hover:to-orange-600 transition flex items-center justify-center gap-2 shadow-lg">
                    <i class="bi bi-check-circle-fill"></i> 
                    <span>Driver Sampai di Laundry</span>
                </a>
            </div>

        {{-- Belum ada driver --}}
        @else
            <div class="bg-orange-50 border-2 border-orange-200 rounded-xl p-4 mb-3">
                <p class="text-orange-800 font-semibold mb-2">
                    <i class="bi bi-exclamation-triangle-fill"></i> Perlu Driver Pickup
                </p>
                <p class="text-sm text-orange-700">
                    Silakan tentukan driver untuk menjemput cucian pelanggan.
                </p>
            </div>

            <a href="{{ route('admin2.pesanan.online.list-driver', $pesanan->id_transaksi) }}?from_tab=pickup" 
               class="w-full py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-semibold hover:from-orange-600 hover:to-red-600 flex items-center justify-center gap-2 shadow-lg transition-all hover:shadow-xl hover:scale-105">
                <i class="bi bi-truck text-lg"></i>
                <span>Pilih Driver Pickup</span>
            </a>
        @endif
    @endif

    {{-- ========== STATUS: ANTRIAN ========== --}}
    @if($pesanan->status_transaksi === 'antrian')
        {{-- Info Delivery Pickup jika ada --}}
        @if($deliveryPickup && $deliveryPickup->driver)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3">
            <p class="text-xs text-gray-600 font-semibold mb-1">
                <i class="bi bi-truck"></i> Info Pickup:
            </p>
            <p class="text-xs text-gray-700">
                Driver: {{ $deliveryPickup->driver->nama_driver }}
            </p>
            @if($deliveryPickup->waktu)
            <p class="text-xs text-gray-600">
                Tiba: {{ \Carbon\Carbon::parse($deliveryPickup->waktu)->format('d M Y, H:i') }}
            </p>
            @endif
        </div>
        @endif

        {{-- Tombol Isi/Edit Data Pesanan --}}
        <button onclick="openIsiDataModal()" 
                class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl font-semibold hover:from-yellow-600 hover:to-orange-600 flex items-center justify-center gap-2 shadow-lg transition-all hover:shadow-xl hover:scale-105">
            <i class="bi bi-pencil-square text-lg"></i> 
            <span>{{ $dataLengkap ? 'Edit' : 'Isi' }} Data Pesanan</span>
        </button>
        
        {{-- Warning atau Tombol Proses --}}
        @if(!$dataLengkap)
            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                <div class="flex items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-amber-600 mt-0.5"></i>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold">Data Pesanan Belum Lengkap</p>
                        <p class="text-xs mt-1">Silakan isi data pesanan terlebih dahulu sebelum melanjutkan proses.</p>
                    </div>
                </div>
            </div>
        @else
            {{-- ✅ TOMBOL PROSES - TAMPIL HANYA JIKA DATA LENGKAP --}}
            <a href="{{ route('admin2.pesanan.online.proses', $pesanan->id_transaksi) }}" 
               class="w-full py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-xl font-bold hover:from-orange-700 hover:to-red-700 transition shadow-lg flex items-center justify-center gap-2">
                <i class="bi bi-play-circle-fill text-lg"></i>
                <span>Mulai Proses</span>
            </a>
        @endif
    @endif

    {{-- ========== STATUS: PROSES ========== --}}
    @if($pesanan->status_transaksi === 'proses')
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-3">
            <div class="flex items-center gap-2">
                <i class="bi bi-hourglass-split text-orange-600 text-lg"></i>
                <p class="text-sm text-orange-700 font-semibold">Pesanan Sedang Diproses</p>
            </div>
        </div>

        <a href="{{ route('admin2.pesanan.online.selesai_di_cuci', $pesanan->id_transaksi) }}" 
           class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl font-bold hover:from-yellow-600 hover:to-orange-600 transition shadow-lg flex items-center justify-center gap-2">
            <i class="bi bi-check2-circle text-lg"></i>
            <span>Tandai Selesai Dicuci</span>
        </a>
    @endif

    {{-- ========== STATUS: SELESAI_DICUCI ========== --}}
    @if($pesanan->status_transaksi === 'selesai_dicuci')
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
            <div class="flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-green-600 text-lg"></i>
                <p class="text-sm text-green-700 font-semibold">Cucian Sudah Selesai</p>
            </div>
            <p class="text-xs text-green-600 mt-1">Pilih metode pengiriman ke pelanggan</p>
        </div>
        {{-- Pilih: Antar atau Ambil Sendiri --}}
        <a href="{{ route('admin2.pesanan.online.siap_di_antar', $pesanan->id_transaksi) }}" 
           class="w-full py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-bold hover:from-orange-600 hover:to-red-600 transition shadow-lg flex items-center justify-center gap-2 mb-2">
            <i class="bi bi-truck text-lg"></i>
            <span>Siap Diantar (Butuh Driver)</span>
        </a>

        <a href="{{ route('admin2.pesanan.online.siap_di_ambil', $pesanan->id_transaksi) }}" 
           class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl font-bold hover:from-green-600 hover:to-emerald-600 transition shadow-lg flex items-center justify-center gap-2">
            <i class="bi bi-shop text-lg"></i>
            <span>Siap Diambil (Pelanggan Ambil)</span>
        </a>
    @endif

    {{-- ========== STATUS: SIAP_DI_ANTAR ========== --}}
    @if($pesanan->status_transaksi === 'siap_di_antar')
        @if($deliveryAntar && $deliveryAntar->id_driver && $deliveryAntar->driver)
            {{-- Driver sudah assigned --}}
            <div class="bg-orange-50 border-2 border-orange-200 rounded-xl p-4 mb-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center shrink-0">
                        <i class="bi bi-person-check-fill text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-orange-900 mb-1">Driver Delivery Ditugaskan</p>
                        <p class="text-sm text-orange-700">
                            <i class="bi bi-person-badge"></i> {{ $deliveryAntar->driver->nama_driver }}
                        </p>
                        @if($deliveryAntar->driver->no_hp)
                        <p class="text-sm text-orange-700">
                            <i class="bi bi-telephone"></i> {{ $deliveryAntar->driver->no_hp }}
                        </p>
                        @endif
                        <p class="text-xs text-orange-600 mt-1">
                            <i class="bi bi-info-circle"></i> Status: {{ str_replace('_', ' ', ucwords($deliveryAntar->status, '_')) }}
                        </p>
                    </div>
                </div>
            </div>
            
            {{-- Tombol Selesai hanya muncul setelah delivered --}}
            @if($deliveryAntar->status === 'delivered' || $sudahLunas)
            <a href="{{ route('admin2.pesanan.online.selesai', $pesanan->id_transaksi) }}" 
               class="w-full py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold hover:from-green-700 hover:to-emerald-700 transition shadow-lg flex items-center justify-center gap-2">
                <i class="bi bi-check-circle-fill text-lg"></i>
                <span>Selesaikan Pesanan</span>
            </a>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                <p class="text-yellow-700 font-semibold">
                    <i class="bi bi-hourglass-split"></i> Menunggu Delivery
                </p>
                <p class="text-xs text-yellow-600 mt-1">Driver sedang dalam perjalanan...</p>
            </div>
            @endif
        @else
            {{-- Belum ada driver delivery --}}
            <div class="bg-orange-50 border-2 border-orange-200 rounded-xl p-4 mb-3">
                <p class="text-orange-800 font-semibold mb-2">
                    <i class="bi bi-exclamation-triangle-fill"></i> Perlu Driver Delivery
                </p>
                <p class="text-sm text-orange-700">
                    Silakan tentukan driver untuk mengantar cucian ke pelanggan.
                </p>
            </div>

            <a href="{{ route('admin2.pesanan.online.list-driver', $pesanan->id_transaksi) }}" 
               class="w-full py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-semibold hover:from-orange-600 hover:to-red-600 flex items-center justify-center gap-2 shadow-lg transition-all hover:shadow-xl hover:scale-105">
                <i class="bi bi-truck text-lg"></i>
                <span>Pilih Driver Delivery</span>
            </a>
        @endif
    @endif

    {{-- ========== STATUS: SIAP_DI_AMBIL ========== --}}
    @if($pesanan->status_transaksi === 'siap_di_ambil')
        @if($sudahLunas)
        <a href="{{ route('admin2.pesanan.online.selesai', $pesanan->id_transaksi) }}" 
           class="w-full py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold hover:from-green-700 hover:to-emerald-700 transition shadow-lg flex items-center justify-center gap-2">
            <i class="bi bi-check-circle-fill text-lg"></i>
            <span>Selesaikan Pesanan</span>
        </a>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center mb-3">
            <p class="text-yellow-700 font-semibold">
                <i class="bi bi-hourglass-split"></i> Menunggu Pengambilan
            </p>
            <p class="text-xs text-yellow-600 mt-1">Pelanggan belum mengambil cuciannya</p>
        </div>
        @endif
    @endif

{{-- ========== TOMBOL BUKTI PEMBAYARAN ========== --}}
@php
    $showBuktiPembayaran = false;
    $labelButton = 'Input Bukti Pembayaran';
    $showWarning = false;
    
    // ✅ CEK STATUS LUNAS DARI TABEL TRANSAKSI (bukan pembayaran)
    $sudahLunas = in_array($pesanan->status_bayar, ['lunas', 'Lunas']);
    $isBelumBayar = in_array($pesanan->status_bayar, ['belum_bayar', 'Belum Bayar']);
    $isDP = in_array($pesanan->status_bayar, ['DP', 'dp']);
    
    // ✅ CEK METODE BAYAR
    $hasMetodeBayar = $metodeBayar && isset($metodeBayar->nama_metode_bayar);
    
    if ($hasMetodeBayar) {
        $namaMetode = strtolower($metodeBayar->nama_metode_bayar);
        $isTransfer = (strpos($namaMetode, 'transfer') !== false || 
                       strpos($namaMetode, 'qris') !== false || 
                       strpos($namaMetode, 'e-wallet') !== false ||
                       strpos($namaMetode, 'emoney') !== false ||
                       strpos($namaMetode, 'gopay') !== false ||
                       strpos($namaMetode, 'dana') !== false ||
                       strpos($namaMetode, 'ovo') !== false);
        
        $isCash = (strpos($namaMetode, 'cash') !== false || 
                   strpos($namaMetode, 'tunai') !== false ||
                   strpos($namaMetode, 'cod') !== false);
        
        if ($isTransfer) {
            // ✅ TRANSFER: Muncul di antrian, proses, selesai_dicuci
            $allowedStatus = ['antrian', 'proses', 'selesai_dicuci'];
            $showBuktiPembayaran = in_array($pesanan->status_transaksi, $allowedStatus);
            $labelButton = $sudahLunas ? 'Lihat Bukti Pembayaran Transfer' : 'Input Bukti Pembayaran Transfer';
            
        } elseif ($isCash) {
            // ✅ CASH: Muncul di siap_di_antar DAN siap_di_ambil jika belum lunas
            $allowedStatusCash = ['siap_di_antar', 'siap_di_ambil'];
            $showBuktiPembayaran = in_array($pesanan->status_transaksi, $allowedStatusCash) && !$sudahLunas;
            $labelButton = 'Konfirmasi Pembayaran Cash';
        }
    } else {
        // ✅ Belum ada metode bayar - tampilkan warning
        $allowedStatusForWarning = ['antrian', 'proses', 'selesai_dicuci', 'siap_di_antar', 'siap_di_ambil'];
        $showWarning = in_array($pesanan->status_transaksi, $allowedStatusForWarning);
    }
@endphp

{{-- ✅ WARNING JIKA BELUM ADA METODE BAYAR --}}
@if($showWarning)
    <hr class="my-3">
    
    <div class="bg-orange-50 border-2 border-orange-300 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <div class="shrink-0">
                <i class="bi bi-exclamation-triangle-fill text-orange-600 text-2xl"></i>
            </div>
            <div class="flex-1">
                <p class="font-bold text-orange-900 mb-2">Metode Pembayaran Belum Ditentukan</p>
                <p class="text-sm text-orange-700 mb-3">
                    Metode pembayaran (Transfer/Cash) belum tersedia untuk pesanan ini.
                </p>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-xs text-yellow-700 flex items-start gap-2">
                        <i class="bi bi-info-circle mt-0.5"></i>
                        <span>
                            <strong>Info:</strong> Metode pembayaran akan otomatis tersimpan saat pelanggan 
                            melakukan pembayaran pertama dari aplikasi mobile.
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ✅ TOMBOL BUKTI PEMBAYARAN --}}
@if($showBuktiPembayaran)
    <hr class="my-3">
    
    @if($isCash)
        {{-- ✅ INFO CASH - BELUM LUNAS --}}
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4 mb-3">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center shrink-0">
                    <i class="bi bi-cash-coin text-white text-2xl"></i>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-yellow-900 mb-2">Pembayaran Cash Belum Dikonfirmasi</p>
                    <p class="text-sm text-yellow-700 mb-2">
                        @if($pesanan->status_transaksi === 'siap_di_antar')
                            Konfirmasi pembayaran cash setelah driver mengantar dan pelanggan melakukan pembayaran.
                        @else
                            Konfirmasi pembayaran cash setelah pelanggan mengambil cucian dan melakukan pembayaran.
                        @endif
                    </p>
                    <div class="bg-yellow-100 rounded-lg p-3 mt-2">
                        <p class="text-xs text-yellow-800">
                            <i class="bi bi-info-circle-fill"></i> 
                            <strong>Catatan:</strong> Konfirmasi ini diperlukan untuk menandai pesanan sebagai lunas setelah pembayaran cash diterima.
                        </p>
                    </div>
                    
                    {{-- ✅ TAMPILKAN STATUS PEMBAYARAN --}}
                    <div class="mt-3 p-2 bg-white border border-yellow-200 rounded-lg">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-600">Status Bayar:</span>
                            <span class="font-bold {{ $sudahLunas ? 'text-green-600' : ($isDP ? 'text-blue-600' : 'text-red-600') }}">
                                @if($sudahLunas)
                                    ✅ Lunas
                                @elseif($isDP)
                                    📝 DP (Rp {{ number_format($pesanan->dp ?? 0, 0, ',', '.') }})
                                @else
                                    ❌ Belum Bayar
                                @endif
                            </span>
                        </div>
                        @if(!$sudahLunas)
                        <div class="flex justify-between text-xs mt-1">
                            <span class="text-gray-600">Sisa Bayar:</span>
                            <span class="font-bold text-red-600">
                                Rp {{ number_format(($pesanan->total_harga ?? 0) - ($pesanan->total_bayar ?? 0), 0, ',', '.') }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin2.pesanan.online.bukti-pembayaran', $pesanan->id_transaksi) }}" 
           class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl font-bold hover:from-green-600 hover:to-emerald-600 flex items-center justify-center gap-2 shadow-lg transition animate-pulse">
            <i class="bi bi-check2-circle text-lg"></i>
            <span>{{ $labelButton }}</span>
        </a>
        
    @elseif($isTransfer)
        {{-- ✅ INFO TRANSFER --}}
        @if($sudahLunas)
        <div class="bg-green-50 border-2 border-green-300 rounded-lg p-3 mb-3">
            <div class="flex items-start gap-2">
                <i class="bi bi-check-circle-fill text-green-600 text-xl mt-0.5"></i>
                <div class="text-sm text-green-700">
                    <p class="font-semibold mb-1">✅ Pembayaran Transfer - Lunas</p>
                    <p>Pembayaran telah dikonfirmasi dan lunas.</p>
                    <div class="mt-2 text-xs bg-green-100 p-2 rounded">
                        Total Bayar: <strong>Rp {{ number_format($pesanan->total_bayar ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @elseif($isDP)
        <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-3 mb-3">
            <div class="flex items-start gap-2">
                <i class="bi bi-info-circle-fill text-blue-600 text-xl mt-0.5"></i>
                <div class="text-sm text-blue-700">
                    <p class="font-semibold mb-1">📝 Pembayaran DP</p>
                    <p>DP telah dikonfirmasi, menunggu pelunasan.</p>
                    <div class="mt-2 text-xs bg-blue-100 p-2 rounded space-y-1">
                        <div class="flex justify-between">
                            <span>DP Terbayar:</span>
                            <strong>Rp {{ number_format($pesanan->total_bayar ?? 0, 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between border-t border-blue-200 pt-1">
                            <span>Sisa:</span>
                            <strong class="text-red-600">Rp {{ number_format(($pesanan->total_harga ?? 0) - ($pesanan->total_bayar ?? 0), 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-3 mb-3">
            <div class="flex items-start gap-2">
                <i class="bi bi-info-circle-fill text-orange-600 text-xl mt-0.5"></i>
                <div class="text-sm text-yellow-700">
                    <p class="font-semibold mb-1">⏳ Menunggu Pembayaran Transfer</p>
                    <p>Tunggu bukti transfer dari pelanggan untuk konfirmasi pembayaran.</p>
                    <div class="mt-2 text-xs bg-yellow-100 p-2 rounded">
                        Total Tagihan: <strong>Rp {{ number_format($pesanan->total_harga ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <a href="{{ route('admin2.pesanan.online.bukti-pembayaran', $pesanan->id_transaksi) }}" 
           class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl font-bold hover:from-yellow-600 hover:to-orange-600 flex items-center justify-center gap-2 shadow-lg transition">
            <i class="bi bi-receipt text-lg"></i>
            <span>{{ $labelButton }}</span>
        </a>
    @endif
@endif

{{-- ✅ DEBUG INFO (HAPUS DI PRODUCTION) --}}
@if(config('app.debug'))
<div class="mt-3 p-3 bg-gray-100 border border-gray-300 rounded text-xs">
    <p class="font-bold mb-1">🔍 Debug Info:</p>
    <ul class="space-y-0.5">
        <li>Status Transaksi: <strong>{{ $pesanan->status_transaksi }}</strong></li>
        <li>Status Bayar: <strong>{{ $pesanan->status_bayar ?? 'NULL' }}</strong></li>
        <li>Metode Bayar: <strong>{{ $metodeBayar->nama_metode_bayar ?? 'NULL' }}</strong></li>
        <li>Is Cash: <strong>{{ $isCash ? 'YES' : 'NO' }}</strong></li>
        <li>Is Transfer: <strong>{{ $isTransfer ? 'YES' : 'NO' }}</strong></li>
        <li>Show Button: <strong>{{ $showBuktiPembayaran ? 'YES' : 'NO' }}</strong></li>
        <li>Sudah Lunas: <strong>{{ $sudahLunas ? 'YES' : 'NO' }}</strong></li>
    </ul>
</div>
@endif

    {{-- ========== TOMBOL CETAK NOTA ========== --}}
    <hr class="my-4">
    
    <button onclick="window.print()"
            class="w-full py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center justify-center gap-2 transition">
        <i class="bi bi-printer"></i>
        <span>Cetak Nota</span>
    </button>
</div>
        </div>
    </div>
</div>

{{-- MODAL ISI DATA PESANAN --}}
<div id="isiDataModal"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center px-4 z-[999] hidden"
     onclick="closeIsiDataModal()">
    <div class="bg-white rounded-2xl max-w-3xl w-full shadow-2xl max-h-[90vh] overflow-hidden flex flex-col"
         onclick="event.stopPropagation()">
        
        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-5 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-pencil-square text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Isi Data Pesanan</h3>
                    <p class="text-orange-100 text-sm">ORDER/{{ $pesanan->id_transaksi }}</p>
                </div>
            </div>
            <button type="button" onclick="closeIsiDataModal()" 
                    class="text-white/80 hover:text-white hover:bg-white/20 w-10 h-10 rounded-xl transition flex items-center justify-center">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        {{-- SCROLLABLE CONTENT --}}
        <div class="overflow-y-auto flex-1 px-6 py-6">
            <form action="{{ route('admin2.pesanan.online.updateData', $pesanan->id_transaksi) }}" method="POST" enctype="multipart/form-data" id="formIsiData">
            @csrf
            @method('PUT')
            <input type="hidden" name="from_tab" value="{{ request()->get('from_tab', 'pickup') }}">

                <div class="space-y-5">
                  {{-- ITEMS SECTION --}}
<div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-xl p-5">
    <h4 class="font-bold text-lg text-yellow-700 mb-4 flex items-center gap-2">
        <i class="bi bi-basket2"></i> Item Pesanan
    </h4>

    <div class="space-y-3">
        @foreach($pesanan->detail_transaksi as $i => $d)
        @php
            // Ambil harga dari field, bukan relasi
            $harga = $d->harga;
            
            // Fallback ke relasi jika harga 0
            if ($harga <= 0) {
                if ($d->jenis) {
                    $harga = $d->jenis->harga;
                } elseif ($d->layanan) {
                    $harga = $d->layanan->harga ?? 0;
                }
            }
            
            $subtotal = $harga * $d->qty;
            $satuan = $d->jenis->satuan->nama_satuan ?? ($d->layanan->satuan->nama_satuan ?? 'unit');
        @endphp
        <div class="bg-white border-2 border-yellow-200 rounded-xl p-4 hover:border-orange-300 transition">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-lg flex items-center justify-center font-bold text-white shadow-md shrink-0">
                    {{ $i+1 }}
                </div>

                <div class="flex-1">
                    <p class="font-bold text-gray-800 mb-1">{{ $d->layanan->nama_layanan ?? '-' }}</p>
                    <p class="text-sm text-gray-600 mb-2">
                        {{ $d->jenis->nama_jenis ?? 'Regular' }}
                    </p>
                    
                    {{-- HARGA INFO - SEKARANG TAMPIL --}}
                    <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border border-orange-200 rounded-lg p-3 mb-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">💰 Harga:</span>
                            <span class="text-base font-bold text-orange-600">
                                Rp {{ number_format($harga, 0, ',', '.') }} / {{ $satuan }}
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="bi bi-calculator text-yellow-600"></i> Jumlah ({{ $satuan }}) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="qty[]" 
                                   step="0.01" 
                                   min="0.01" 
                                   value="{{ $d->qty }}"
                                   class="w-full px-4 py-3 text-lg font-semibold border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition" 
                                   placeholder="Contoh: 2.2"
                                   required
                                   oninput="updateSubtotal(this, {{ $harga }}, {{ $i }})">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">
                                {{ $satuan }}
                            </div>
                        </div>
                        
                        {{-- SUBTOTAL REALTIME --}}
                        <div class="mt-2 bg-gray-50 rounded-lg p-2 flex items-center justify-between">
                            <span class="text-xs text-gray-600">Subtotal:</span>
                            <span class="text-sm font-bold text-gray-800" id="subtotal-{{ $i }}">
                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                            <i class="bi bi-info-circle"></i>
                            Bisa desimal (contoh: 0.5, 2.2, 3.75)
                        </p>
                    </div>
                    <input type="hidden" name="id_detail[]" value="{{ $d->id_detail_transaksi }}">
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

                   {{-- BIAYA TAMBAHAN SECTION (SIMPLE INPUT) --}}
<div class="bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-200 rounded-xl p-5">
    <h4 class="font-bold text-lg text-orange-900 mb-4 flex items-center gap-2">
        <i class="bi bi-plus-circle"></i> Biaya Tambahan
    </h4>

    {{-- List Biaya Tambahan yang Sudah Ada --}}
    <div id="biayaTambahanList" class="space-y-3 mb-4">
        @if($pesanan->biayaTambahan && $pesanan->biayaTambahan->count() > 0)
            @foreach($pesanan->biayaTambahan as $index => $bt)
            <div class="biaya-item bg-white border-2 border-orange-200 rounded-lg p-3" data-index="{{ $index }}" data-id="{{ $bt->id_biaya_tambahan }}">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center text-white font-bold shrink-0">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm">{{ $bt->nama_biaya }}</p>
                        <p class="text-xs text-gray-600">Rp {{ number_format($bt->nominal, 0, ',', '.') }}</p>
                    </div>
                    <button type="button" onclick="removeBiayaTambahan({{ $index }}, {{ $bt->id_biaya_tambahan }})" 
                            class="text-red-600 hover:text-red-800 hover:scale-110 transition">
                        <i class="bi bi-trash text-lg"></i>
                    </button>
                </div>
                <input type="hidden" name="existing_biaya_id[]" value="{{ $bt->id_biaya_tambahan }}">
            </div>
            @endforeach
        @else
            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center" id="emptyMessage">
                <i class="bi bi-inbox text-gray-400 text-3xl mb-2"></i>
                <p class="text-gray-500 text-sm italic">Belum ada biaya tambahan</p>
                <p class="text-xs text-gray-400 mt-1">Tambahkan biaya di bawah ini</p>
            </div>
        @endif
    </div>

    {{-- Form Input Biaya Tambahan --}}
    <div class="bg-white rounded-xl p-5 border-2 border-orange-100 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
                <i class="bi bi-plus-lg text-white"></i>
            </div>
            <h5 class="font-bold text-gray-800">Tambah Biaya Baru</h5>
        </div>

        <div class="space-y-4">
            {{-- Input Nama Biaya --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="bi bi-tag text-orange-600"></i> Nama Biaya <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="inputNamaBiaya" 
                       placeholder="Contoh: Ongkir, Service Express, Pewangi Premium"
                       class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition placeholder:text-gray-400">
            </div>

            {{-- Input Nominal --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="bi bi-cash text-orange-600"></i> Nominal <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-lg">Rp</span>
                    <input type="number" 
                           id="inputNominalBiaya" 
                           step="1000" 
                           min="0" 
                           placeholder="0"
                           class="w-full pl-14 pr-4 py-3 text-base border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition placeholder:text-gray-400">
                </div>
                <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                    <i class="bi bi-lightbulb"></i>
                    <span>Tip: Gunakan kelipatan 1000 untuk nominal yang rapi</span>
                </p>
            </div>

            {{-- Quick Amount Buttons --}}
            <div>
                <p class="text-sm font-bold text-gray-700 mb-2">Quick Amount:</p>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" onclick="setNominal(5000)" 
                            class="px-3 py-2 text-sm bg-orange-50 border-2 border-orange-200 rounded-lg hover:bg-orange-100 hover:border-orange-300 transition font-semibold text-orange-700">
                        5K
                    </button>
                    <button type="button" onclick="setNominal(10000)" 
                            class="px-3 py-2 text-sm bg-orange-50 border-2 border-orange-200 rounded-lg hover:bg-orange-100 hover:border-orange-300 transition font-semibold text-orange-700">
                        10K
                    </button>
                    <button type="button" onclick="setNominal(15000)" 
                            class="px-3 py-2 text-sm bg-orange-50 border-2 border-orange-200 rounded-lg hover:bg-orange-100 hover:border-orange-300 transition font-semibold text-orange-700">
                        15K
                    </button>
                    <button type="button" onclick="setNominal(20000)" 
                            class="px-3 py-2 text-sm bg-orange-50 border-2 border-orange-200 rounded-lg hover:bg-orange-100 hover:border-orange-300 transition font-semibold text-orange-700">
                        20K
                    </button>
                </div>
            </div>

            {{-- Tombol Tambah --}}
            <button type="button" onclick="addBiayaTambahan()"
                    class="w-full py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-bold hover:from-orange-600 hover:to-red-600 transition-all shadow-lg hover:shadow-xl hover:scale-[1.02] flex items-center justify-center gap-2">
                <i class="bi bi-plus-circle-fill text-lg"></i>
                <span>Tambah Biaya</span>
            </button>
        </div>
    </div>

    {{-- Hidden inputs untuk biaya tambahan baru --}}
    <div id="newBiayaTambahanInputs"></div>
</div>

                    {{-- TANGGAL ESTIMASI SELESAI --}}
<div class="bg-gradient-to-r from-yellow-50 to-cyan-50 border-2 border-yellow-200 rounded-xl p-5">
    <h4 class="font-bold text-lg text-yellow-600 mb-4 flex items-center gap-2">
        <i class="bi bi-calendar-check"></i> Estimasi Selesai
        <span class="text-sm text-gray-600 font-normal">(Opsional)</span>
    </h4>

    <div class="mb-4">
        <label class="block text-sm font-bold text-gray-700 mb-2">
            <i class="bi bi-calendar-event text-yellow-600"></i> Tanggal Estimasi
        </label>
        <input type="date" 
               name="tgl_estimasi" 
               id="tgl_estimasi"
               value="{{ old('tgl_estimasi', $pesanan->tgl_estimasi ?? date('Y-m-d', strtotime('+3 days'))) }}"
               min="{{ date('Y-m-d') }}"
               class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
            <i class="bi bi-info-circle"></i>
            <span>Kosongkan jika tidak ingin mengubah estimasi</span>
        </p>
    </div>

    {{-- Quick Select Buttons --}}
    <div>
        <p class="text-sm font-bold text-gray-700 mb-3">Quick Select:</p>
        <div class="grid grid-cols-4 gap-2">
            <button type="button" 
                    onclick="setEstimasiDate(1)"
                    class="px-3 py-3 text-sm bg-white border-2 border-yellow-300 rounded-xl hover:bg-yellow-50 hover:border-yellow-400 transition font-semibold">
                <i class="bi bi-lightning-fill text-yellow-600"></i><br>1 Hari
            </button>
            <button type="button" 
                    onclick="setEstimasiDate(2)"
                    class="px-3 py-3 text-sm bg-white border-2 border-yellow-300 rounded-xl hover:bg-yellow-50 hover:border-yellow-400 transition font-semibold">
                <i class="bi bi-clock text-yellow-600"></i><br>2 Hari
            </button>
            <button type="button" 
                    onclick="setEstimasiDate(3)"
                    class="px-3 py-3 text-sm bg-white border-2 border-yellow-300 rounded-xl hover:bg-yellow-50 hover:border-yellow-400 transition font-semibold">
                <i class="bi bi-calendar text-yellow-600"></i><br>3 Hari
            </button>
            <button type="button" 
                    onclick="setEstimasiDate(7)"
                    class="px-3 py-3 text-sm bg-white border-2 border-yellow-300 rounded-xl hover:bg-yellow-50 hover:border-yellow-400 transition font-semibold">
                <i class="bi bi-calendar-week text-yellow-600"></i><br>1 Minggu
            </button>
        </div>
    </div>
</div>

                    {{-- UPLOAD FOTO BUKTI CUCIAN --}}
                    <div class="bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-200 rounded-xl p-5">
                        <h4 class="font-bold text-lg text-orange-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-camera"></i> Foto Bukti Cucian <span class="text-sm text-gray-600 font-normal">(Opsional)</span>
                        </h4>

                        {{-- Preview Foto Existing --}}
                        @if($pesanan->foto_bukti)
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Foto saat ini:</p>
                            <div class="relative inline-block">
                                <img src="{{ asset('storage/' . $pesanan->foto_bukti) }}" 
                                    alt="Bukti Cucian" 
                                    class="w-40 h-40 object-cover rounded-xl border-2 border-orange-200 shadow-md">
                                <div class="absolute -top-2 -right-2 bg-orange-600 text-white rounded-full p-2 shadow-lg">
                                    <i class="bi bi-check text-sm"></i>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="bi bi-upload text-orange-600"></i> {{ $pesanan->foto_bukti ? 'Ganti Foto' : 'Upload Foto' }}
                            </label>
                            <input type="file" 
                                   name="foto_bukti" 
                                   id="foto_bukti"
                                   accept="image/jpeg,image/jpg,image/png"
                                   class="w-full px-4 py-3 text-sm border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200 cursor-pointer">
                            <p class="text-xs text-gray-600 mt-2 flex items-start gap-1">
                                <i class="bi bi-info-circle mt-0.5"></i>
                                <span>Format: JPG, JPEG, PNG. Maksimal 2MB.</span>
                            </p>
                        </div>

                        {{-- Preview Gambar Sebelum Upload --}}
                        <div id="imagePreview" class="mt-4 hidden">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Preview:</p>
                            <img id="previewImg" src="" alt="Preview" class="w-40 h-40 object-cover rounded-xl border-2 border-orange-200 shadow-md">
                        </div>
                    </div>

                    {{-- DISKON SECTION --}}
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 border-2 border-red-200 rounded-xl p-5">
                        <h4 class="font-bold text-lg text-red-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-percent"></i> Diskon <span class="text-sm text-gray-600 font-normal">(Opsional)</span>
                        </h4>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-3">Tipe Diskon:</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="tipe_diskon" value="nominal" 
                                           {{ ($pesanan->tipe_diskon ?? 'nominal') == 'nominal' ? 'checked' : '' }}
                                           class="mr-3 text-red-600 focus:ring-red-500 w-5 h-5">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-currency-dollar text-red-600"></i>
                                        <span class="text-sm font-semibold group-hover:text-red-600 transition">Nominal (Rp)</span>
                                    </div>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="tipe_diskon" value="percent" 
                                           {{ ($pesanan->tipe_diskon ?? 'nominal') == 'percent' ? 'checked' : '' }}
                                           class="mr-3 text-red-600 focus:ring-red-500 w-5 h-5">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-percent text-red-600"></i>
                                        <span class="text-sm font-semibold group-hover:text-red-600 transition">Persen (%)</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nilai Diskon:</label>
                            <input type="number" name="diskon" step="0.01" min="0"
                                   value="{{ $pesanan->diskon ?? 0 }}" placeholder="0"
                                   class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition">
                            <p class="text-xs text-gray-600 mt-2 flex items-start gap-1">
                                <i class="bi bi-info-circle mt-0.5"></i>
                                <span>Kosongkan jika tidak ada diskon</span>
                            </p>
                        </div>
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="bg-gradient-to-r from-gray-50 to-slate-50 border-2 border-gray-200 rounded-xl p-5">
                        <label class="block text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <i class="bi bi-chat-left-text text-gray-600"></i> Keterangan <span class="text-sm text-gray-600 font-normal">(Opsional)</span>
                        </label>
                        <textarea name="keterangan" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition resize-none"
                                  placeholder="Tambahkan catatan jika ada...">{{ $pesanan->keterangan ?? '' }}</textarea>
                    </div>
                </div>
            </form>
        </div>

        {{-- FOOTER ACTIONS --}}
        <div class="flex gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 shrink-0">
            <button type="button" onclick="closeIsiDataModal()"
                    class="flex-1 py-3 border-2 border-gray-300 rounded-xl hover:bg-gray-100 font-bold text-gray-700 transition">
                <i class="bi bi-x-circle"></i> Batal
            </button>
            <button type="submit" form="formIsiData"
                    class="flex-1 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl hover:from-yellow-600 hover:to-orange-600 font-bold shadow-lg hover:shadow-xl transition">
                <i class="bi bi-save"></i> Simpan Data
            </button>
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

{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}

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

function openImageModal(imageSrc) {
    document.getElementById('zoomedImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function toggleOngkirMethod() {
    const method = document.querySelector('input[name="ongkir_method"]:checked').value;
    const presetSection = document.getElementById('presetOngkirSection');
    const manualSection = document.getElementById('manualOngkirSection');
    const idBiayaTambahanSelect = document.getElementById('id_biaya_tambahan');
    const ongkirManualInput = document.getElementById('ongkir_manual');
    
    if (method === 'preset') {
        presetSection.classList.remove('hidden');
        manualSection.classList.add('hidden');
        ongkirManualInput.value = 0;
    } else {
        presetSection.classList.add('hidden');
        manualSection.classList.remove('hidden');
        idBiayaTambahanSelect.value = '';
    }
}

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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeIsiDataModal();
        closeImageModal();
    }
});

document.getElementById('foto_bukti')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            e.target.value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            return;
        }

        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG.');
            e.target.value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            return;
        }

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

function sendNotificationSelesaiDicuci() {
    const btn = document.getElementById('btnSendNotification');
    const originalContent = btn.innerHTML;
    
    if (!confirm('Kirim notifikasi ke pelanggan bahwa cucian sudah selesai dicuci?')) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Mengirim...</span>
    `;
    
    fetch('{{ route("pesanan.online.send-fcm-notification", $pesanan->id_transaksi) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            notification_type: 'selesai_dicuci'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = `
                <i class="bi bi-check-circle-fill text-lg"></i>
                <span>Notifikasi Terkirim!</span>
            `;
            btn.classList.remove('from-yellow-500', 'to-orange-500', 'hover:from-yellow-600', 'hover:to-orange-600');
            btn.classList.add('from-green-500', 'to-emerald-500');
            
            alert('✅ ' + data.message);
            
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.classList.remove('from-green-500', 'to-emerald-500');
                btn.classList.add('from-yellow-500', 'to-orange-500', 'hover:from-yellow-600', 'hover:to-orange-600');
                btn.disabled = false;
            }, 3000);
        } else {
            throw new Error(data.message || 'Gagal mengirim notifikasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ ' + error.message);
        btn.innerHTML = originalContent;
        btn.disabled = false;
    });
}

// =====================================
// BIAYA TAMBAHAN FUNCTIONS
// =====================================

let biayaIndex = {{ $pesanan->biayaTambahan ? $pesanan->biayaTambahan->count() : 0 }};
let biayaToDelete = [];

// SET NOMINAL QUICK BUTTON
function setNominal(amount) {
    document.getElementById('inputNominalBiaya').value = amount;
    document.getElementById('inputNominalBiaya').focus();
}

// TAMBAH BIAYA TAMBAHAN
function addBiayaTambahan() {
    const namaBiaya = document.getElementById('inputNamaBiaya').value.trim();
    const nominal = parseFloat(document.getElementById('inputNominalBiaya').value) || 0;
    
    // Validasi
    if (!namaBiaya) {
        showErrorNotif('Nama biaya harus diisi!');
        document.getElementById('inputNamaBiaya').focus();
        return;
    }
    
    if (nominal <= 0) {
        showErrorNotif('Nominal harus lebih dari 0!');
        document.getElementById('inputNominalBiaya').focus();
        return;
    }
    
    // Hapus pesan "Belum ada biaya tambahan"
    const emptyMsg = document.getElementById('emptyMessage');
    if (emptyMsg) emptyMsg.remove();
    
    // Hitung nomor urut
    const currentItems = document.querySelectorAll('.biaya-item').length;
    const nomorUrut = currentItems + 1;
    
    // Tambahkan item ke list
    const listContainer = document.getElementById('biayaTambahanList');
    const newItem = document.createElement('div');
    newItem.className = 'biaya-item bg-white border-2 border-green-200 rounded-lg p-3 animate-slideIn';
    newItem.dataset.index = biayaIndex;
    newItem.dataset.new = 'true';
    
    newItem.innerHTML = `
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center text-white font-bold shrink-0">
                ${nomorUrut}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <p class="font-semibold text-sm">${namaBiaya}</p>
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">BARU</span>
                </div>
                <p class="text-xs text-gray-600">Rp ${nominal.toLocaleString('id-ID')}</p>
            </div>
            <button type="button" onclick="removeBiayaTambahan(${biayaIndex}, null)" 
                    class="text-red-600 hover:text-red-800 hover:scale-110 transition">
                <i class="bi bi-trash text-lg"></i>
            </button>
        </div>
    `;
    
    listContainer.appendChild(newItem);
    
    // Tambahkan hidden input untuk dikirim ke backend
    const inputsContainer = document.getElementById('newBiayaTambahanInputs');
    const hiddenInputs = document.createElement('div');
    hiddenInputs.id = `new-biaya-${biayaIndex}`;
    hiddenInputs.innerHTML = `
        <input type="hidden" name="new_biaya_nama[]" value="${namaBiaya}">
        <input type="hidden" name="new_biaya_nominal[]" value="${nominal}">
    `;
    inputsContainer.appendChild(hiddenInputs);
    
    biayaIndex++;
    
    // Reset form
    document.getElementById('inputNamaBiaya').value = '';
    document.getElementById('inputNominalBiaya').value = '';
    document.getElementById('inputNamaBiaya').focus();
    
    // Update nomor urut semua item
    updateNomorUrut();
    
    // Tampilkan notifikasi
    showSuccessNotif(`${namaBiaya} berhasil ditambahkan!`);
}

// HAPUS BIAYA TAMBAHAN
function removeBiayaTambahan(index, biayaId) {
    if (!confirm('Hapus biaya tambahan ini?')) return;
    
    const item = document.querySelector(`.biaya-item[data-index="${index}"]`);
    if (item) {
        // Jika biaya sudah ada di database, tandai untuk dihapus
        if (biayaId) {
            biayaToDelete.push(biayaId);
            // Tambahkan hidden input untuk delete
            const inputsContainer = document.getElementById('newBiayaTambahanInputs');
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_biaya_id[]';
            deleteInput.value = biayaId;
            inputsContainer.appendChild(deleteInput);
        } else {
            // Hapus hidden input untuk biaya baru
            const hiddenInputs = document.getElementById(`new-biaya-${index}`);
            if (hiddenInputs) hiddenInputs.remove();
        }
        
        // Animasi fade out
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            item.remove();
            
            // Update nomor urut
            updateNomorUrut();
            
            // Cek apakah masih ada item
            const items = document.querySelectorAll('.biaya-item');
            if (items.length === 0) {
                const listContainer = document.getElementById('biayaTambahanList');
                listContainer.innerHTML = `
                    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center" id="emptyMessage">
                        <i class="bi bi-inbox text-gray-400 text-3xl mb-2"></i>
                        <p class="text-gray-500 text-sm italic">Belum ada biaya tambahan</p>
                        <p class="text-xs text-gray-400 mt-1">Tambahkan biaya di bawah ini</p>
                    </div>
                `;
            }
        }, 300);
    }
}

// UPDATE NOMOR URUT
function updateNomorUrut() {
    const items = document.querySelectorAll('.biaya-item');
    items.forEach((item, index) => {
        const nomorElement = item.querySelector('.w-8.h-8');
        if (nomorElement) {
            nomorElement.textContent = index + 1;
        }
    });
}

// NOTIFIKASI SUCCESS
function showSuccessNotif(message) {
    const notif = document.createElement('div');
    notif.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 z-[9999] animate-slideUp';
    notif.innerHTML = `
        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
            <i class="bi bi-check-lg font-bold"></i>
        </div>
        <span class="font-semibold">${message}</span>
    `;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.style.opacity = '0';
        notif.style.transform = 'translateY(10px)';
        setTimeout(() => notif.remove(), 300);
    }, 2500);
}

// NOTIFIKASI ERROR
function showErrorNotif(message) {
    const notif = document.createElement('div');
    notif.className = 'fixed bottom-4 right-4 bg-red-600 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 z-[9999] animate-slideUp';
    notif.innerHTML = `
        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
            <i class="bi bi-exclamation-lg font-bold"></i>
        </div>
        <span class="font-semibold">${message}</span>
    `;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.style.opacity = '0';
        notif.style.transform = 'translateY(10px)';
        setTimeout(() => notif.remove(), 300);
    }, 2500);
}

// KEYBOARD SHORTCUT (ENTER untuk tambah)
document.getElementById('inputNamaBiaya')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('inputNominalBiaya').focus();
    }
});

document.getElementById('inputNominalBiaya')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addBiayaTambahan();
    }
});
</script>
<script>
function updateSubtotal(input, harga, index) {
    const qty = parseFloat(input.value) || 0;
    const subtotal = qty * harga;
    
    const subtotalEl = document.getElementById('subtotal-' + index);
    if (subtotalEl) {
        subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    }
}
</script>

@endsection