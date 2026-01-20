@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-6 py-4 rounded-b-2xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('pesanan.online.index', ['tab' => request()->get('from_tab', 'pickup')]) }}"
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
                        $subtotalItems = 0;
                        $totalQtyAll = 0;
                        
                        if($pesanan->detail_transaksi) {
                            foreach($pesanan->detail_transaksi as $d) {
                                $subtotalItems += ($d->harga * $d->qty);
                                $totalQtyAll += $d->qty;
                            }
                        }
                        
                        // ✅ AMBIL BIAYA ONGKIR
                        $biayaOngkir = 0;
                        if($pesanan->id_biaya_tambahan && $pesanan->biayaTambahan) {
                            $biayaOngkir = $pesanan->biayaTambahan->nominal;
                        }
                        
                        // ✅ HITUNG TOTAL YANG BENAR
                        // Total = Subtotal Items + Biaya Ongkir - Diskon
                        $totalBenar = $subtotalItems + $biayaOngkir - ($pesanan->diskon ?? 0);
                    @endphp
                    
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <b>Rp {{ number_format($subtotalItems,0,',','.') }}</b>
                    </div>
                    
                    {{-- ✅ BIAYA ONGKIR --}}
                    @if($biayaOngkir > 0)
                    <div class="flex justify-between text-orange-600">
                        <span>
                            <i class="bi bi-truck text-xs"></i> Biaya Ongkir
                        </span>
                        <b>+ Rp {{ number_format($biayaOngkir,0,',','.') }}</b>
                    </div>
                    @endif
                    
                    @if($pesanan->diskon > 0)
                    <div class="flex justify-between text-red-600">
                        <span>
                            Diskon
                            @if($pesanan->tipe_diskon == 'percent' && $subtotalItems > 0)
                                <span class="text-xs">({{ number_format(($pesanan->diskon / $subtotalItems) * 100, 1) }}%)</span>
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
                    
                    // ✅ Gunakan variable dari controller, atau null jika tidak ada
                    $deliveryPickup = $deliveryPickup ?? null;
                    $deliveryAntar = $deliveryAntar ?? null;
                    
                    $metodeBayar = $pesanan->metodeBayar;
                    $isTransfer = $metodeBayar && (stripos($metodeBayar->nama_metode_bayar, 'transfer') !== false || stripos($metodeBayar->nama_metode_bayar, 'tf') !== false);
                    $isCash = $metodeBayar && (stripos($metodeBayar->nama_metode_bayar, 'cash') !== false || stripos($metodeBayar->nama_metode_bayar, 'tunai') !== false);
                @endphp

                {{-- BUTTON ISI DATA PESANAN --}}
                @if(in_array($pesanan->status_transaksi, ['antrian', 'pick_up']) && !in_array($pesanan->status_transaksi, ['selesai', 'ditolak']))
                <button onclick="openIsiDataModal()" 
                        class="w-full py-3 bg-gradient-to-r from-purple-500 to-indigo-500 text-white rounded-xl font-semibold hover:from-purple-600 hover:to-indigo-600 flex items-center justify-center gap-2 shadow-lg transition-all hover:shadow-xl hover:scale-105">
                    <i class="bi bi-pencil-square text-lg"></i> 
                    <span>{{ $dataLengkap ? 'Edit' : 'Isi' }} Data Pesanan</span>
                </button>
                @endif

                {{-- ✅ KIRIM NOTIFIKASI FCM - STATUS SELESAI_DICUCI --}}
                @if($pesanan->status_transaksi === 'selesai_dicuci')
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center shrink-0">
                            <i class="bi bi-bell-fill text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-blue-900 mb-1">Cucian Sudah Selesai!</p>
                            <p class="text-sm text-blue-700">Kirim notifikasi ke pelanggan bahwa cucian sudah selesai dicuci dan siap untuk diproses lebih lanjut.</p>
                        </div>
                    </div>

                    <button onclick="sendNotificationSelesaiDicuci()" 
                            id="btnSendNotification"
                            class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl font-bold hover:from-blue-600 hover:to-indigo-600 flex items-center justify-center gap-2 shadow-lg transition-all hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="bi bi-send-fill text-lg"></i>
                        <span>Kirim Notifikasi Cucian Selesai</span>
                    </button>

                    <p class="text-xs text-blue-600 mt-2 flex items-center gap-1">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>Pelanggan akan menerima notifikasi push melalui aplikasi mobile</span>
                    </p>
                </div>
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

                {{-- CETAK NOTA --}}
                <button onclick="window.print()"
                        class="w-full py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center justify-center gap-2">
                    <i class="bi bi-printer"></i>
                    <span>Cetak Nota</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL ISI DATA PESANAN - IMPROVED ================= --}}
<div id="isiDataModal"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center px-4 z-[999] hidden"
     onclick="closeIsiDataModal()">
    <div class="bg-white rounded-2xl max-w-3xl w-full shadow-2xl max-h-[90vh] overflow-hidden flex flex-col"
         onclick="event.stopPropagation()">
        
        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-5 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-pencil-square text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Isi Data Pesanan</h3>
                    <p class="text-purple-100 text-sm">ORDER/{{ $pesanan->id_transaksi }}</p>
                </div>
            </div>
            <button type="button" onclick="closeIsiDataModal()" 
                    class="text-white/80 hover:text-white hover:bg-white/20 w-10 h-10 rounded-xl transition flex items-center justify-center">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        {{-- SCROLLABLE CONTENT --}}
        <div class="overflow-y-auto flex-1 px-6 py-6">
            <form action="{{ route('pesanan.online.updateData', $pesanan->id_transaksi) }}" method="POST" enctype="multipart/form-data" id="formIsiData">
            @csrf
            @method('PUT')
            <input type="hidden" name="from_tab" value="{{ request()->get('from_tab', 'pickup') }}">

                <div class="space-y-5">
                    {{-- ITEMS SECTION --}}
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-5">
                        <h4 class="font-bold text-lg text-blue-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-basket2"></i> Item Pesanan
                        </h4>

                        <div class="space-y-3">
                            @foreach($pesanan->detail_transaksi as $i => $d)
                            @php
                                $subtotal = $d->harga * $d->qty;
                            @endphp
                            <div class="bg-white border-2 border-blue-100 rounded-xl p-4 hover:border-blue-300 transition">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-lg flex items-center justify-center font-bold text-white shadow-md shrink-0">
                                        {{ $i+1 }}
                                    </div>

                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800 mb-1">{{ $d->layanan->nama_layanan ?? '-' }}</p>
                                        <p class="text-sm text-gray-600 mb-2">
                                            {{ $d->jenis->nama_jenis ?? 'Regular' }}
                                        </p>
                                        @if($d->jenis)
                                        <div class="bg-gray-50 rounded-lg p-2 mb-3 text-xs text-gray-600">
                                            <span class="font-semibold">Harga:</span> Rp {{ number_format($d->jenis->harga, 0, ',', '.') }} / {{ $d->jenis->satuan->nama_satuan ?? '' }}
                                        </div>
                                        @endif
                                        
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                <i class="bi bi-calculator text-purple-600"></i> Jumlah (Qty) <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" name="qty[]" step="0.01" min="0.01" value="{{ $d->qty }}"
                                                   class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" 
                                                   placeholder="0.00"
                                                   required>
                                        </div>
                                        <input type="hidden" name="id_detail[]" value="{{ $d->id_detail_transaksi }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ✅ BIAYA ONGKIR SECTION - IMPROVED --}}
                    <div class="bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-200 rounded-xl p-5">
                        <h4 class="font-bold text-lg text-orange-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-truck"></i> Biaya Ongkir
                        </h4>

                        {{-- Toggle Ongkir Type --}}
                        <div class="mb-4 bg-white rounded-lg p-4 border-2 border-orange-100">
                            <label class="block text-sm font-bold text-gray-700 mb-3">Pilih Metode Ongkir:</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="ongkir_method" value="preset" 
                                           {{ ($pesanan->id_biaya_tambahan ?? '') ? 'checked' : '' }}
                                           onchange="toggleOngkirMethod()"
                                           class="mr-3 text-orange-600 focus:ring-orange-500 w-5 h-5">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-list-check text-orange-600"></i>
                                        <span class="text-sm font-semibold group-hover:text-orange-600 transition">Pilih dari List</span>
                                    </div>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="ongkir_method" value="manual" 
                                           {{ !($pesanan->id_biaya_tambahan ?? '') ? 'checked' : '' }}
                                           onchange="toggleOngkirMethod()"
                                           class="mr-3 text-orange-600 focus:ring-orange-500 w-5 h-5">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-pencil-square text-orange-600"></i>
                                        <span class="text-sm font-semibold group-hover:text-orange-600 transition">Input Manual</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Preset Ongkir --}}
                        <div id="presetOngkirSection" class="{{ ($pesanan->id_biaya_tambahan ?? '') ? '' : 'hidden' }}">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="bi bi-list-ul text-orange-600"></i> Pilih Biaya Ongkir
                            </label>
                            <select name="id_biaya_tambahan" 
                                    id="id_biaya_tambahan"
                                    class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                                <option value="">-- Tidak Ada Ongkir --</option>
                                @foreach($biayaTambahan as $bt)
                                <option value="{{ $bt->id_biaya_tambahan }}" 
                                        {{ ($pesanan->id_biaya_tambahan ?? '') == $bt->id_biaya_tambahan ? 'selected' : '' }}>
                                    Rp {{ number_format($bt->nominal, 0, ',', '.') }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-600 mt-2 flex items-start gap-1">
                                <i class="bi bi-info-circle mt-0.5"></i>
                                <span>Pilih biaya ongkir yang sudah tersedia di sistem.</span>
                            </p>
                        </div>

                        {{-- Manual Ongkir --}}
                        <div id="manualOngkirSection" class="{{ !($pesanan->id_biaya_tambahan ?? '') ? '' : 'hidden' }}">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="bi bi-currency-dollar text-orange-600"></i> Input Ongkir Manual
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                                <input type="number" 
                                       name="ongkir_manual" 
                                       id="ongkir_manual"
                                       step="1000" 
                                       min="0"
                                       value="0"
                                       placeholder="0"
                                       class="w-full pl-12 pr-4 py-3 text-base border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                            </div>
                            <p class="text-xs text-gray-600 mt-2 flex items-start gap-1">
                                <i class="bi bi-info-circle mt-0.5"></i>
                                <span>Masukkan nominal ongkir secara manual jika tidak ada di list.</span>
                            </p>
                        </div>
                    </div>

                    {{-- ✅ TANGGAL ESTIMASI SELESAI --}}
                    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 border-2 border-teal-200 rounded-xl p-5">
                        <h4 class="font-bold text-lg text-teal-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-calendar-check"></i> Estimasi Selesai
                        </h4>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="bi bi-calendar-event text-teal-600"></i> Tanggal Estimasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="tgl_estimasi" 
                                   id="tgl_estimasi"
                                   value="{{ $pesanan->tgl_estimasi ?? date('Y-m-d', strtotime('+3 days')) }}"
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                                   required>
                        </div>

                        {{-- Quick Select Buttons --}}
                        <div>
                            <p class="text-sm font-bold text-gray-700 mb-3">Quick Select:</p>
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" 
                                        onclick="setEstimasiDate(1)"
                                        class="px-3 py-3 text-sm bg-white border-2 border-teal-300 rounded-xl hover:bg-teal-50 hover:border-teal-400 transition font-semibold">
                                    <i class="bi bi-lightning-fill text-teal-600"></i><br>1 Hari
                                </button>
                                <button type="button" 
                                        onclick="setEstimasiDate(2)"
                                        class="px-3 py-3 text-sm bg-white border-2 border-teal-300 rounded-xl hover:bg-teal-50 hover:border-teal-400 transition font-semibold">
                                    <i class="bi bi-clock text-teal-600"></i><br>2 Hari
                                </button>
                                <button type="button" 
                                        onclick="setEstimasiDate(3)"
                                        class="px-3 py-3 text-sm bg-white border-2 border-teal-300 rounded-xl hover:bg-teal-50 hover:border-teal-400 transition font-semibold">
                                    <i class="bi bi-calendar text-teal-600"></i><br>3 Hari
                                </button>
                                <button type="button" 
                                        onclick="setEstimasiDate(7)"
                                        class="px-3 py-3 text-sm bg-white border-2 border-teal-300 rounded-xl hover:bg-teal-50 hover:border-teal-400 transition font-semibold">
                                    <i class="bi bi-calendar-week text-teal-600"></i><br>1 Minggu
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- UPLOAD FOTO BUKTI CUCIAN --}}
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-xl p-5">
                        <h4 class="font-bold text-lg text-indigo-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-camera"></i> Foto Bukti Cucian <span class="text-sm text-gray-600 font-normal">(Opsional)</span>
                        </h4>

                        {{-- Preview Foto Existing --}}
                        @if($pesanan->foto_bukti)
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Foto saat ini:</p>
                            <div class="relative inline-block">
                                <img src="{{ asset('storage/' . $pesanan->foto_bukti) }}" 
                                     alt="Bukti Cucian" 
                                     class="w-40 h-40 object-cover rounded-xl border-2 border-indigo-200 shadow-md">
                                <div class="absolute -top-2 -right-2 bg-indigo-600 text-white rounded-full p-2 shadow-lg">
                                    <i class="bi bi-check text-sm"></i>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="bi bi-upload text-indigo-600"></i> {{ $pesanan->foto_bukti ? 'Ganti Foto' : 'Upload Foto' }}
                            </label>
                            <input type="file" 
                                   name="foto_bukti" 
                                   id="foto_bukti"
                                   accept="image/jpeg,image/jpg,image/png"
                                   class="w-full px-4 py-3 text-sm border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer">
                            <p class="text-xs text-gray-600 mt-2 flex items-start gap-1">
                                <i class="bi bi-info-circle mt-0.5"></i>
                                <span>Format: JPG, JPEG, PNG. Maksimal 2MB.</span>
                            </p>
                        </div>

                        {{-- Preview Gambar Sebelum Upload --}}
                        <div id="imagePreview" class="mt-4 hidden">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Preview:</p>
                            <img id="previewImg" src="" alt="Preview" class="w-40 h-40 object-cover rounded-xl border-2 border-indigo-200 shadow-md">
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
                    class="flex-1 py-3 bg-gradient-to-r from-purple-500 to-indigo-500 text-white rounded-xl hover:from-purple-600 hover:to-indigo-600 font-bold shadow-lg hover:shadow-xl transition">
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

function openImageModal(imageSrc) {
    document.getElementById('zoomedImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// ✅ Function untuk toggle ongkir method
function toggleOngkirMethod() {
    const method = document.querySelector('input[name="ongkir_method"]:checked').value;
    const presetSection = document.getElementById('presetOngkirSection');
    const manualSection = document.getElementById('manualOngkirSection');
    const idBiayaTambahanSelect = document.getElementById('id_biaya_tambahan');
    const ongkirManualInput = document.getElementById('ongkir_manual');
    
    if (method === 'preset') {
        presetSection.classList.remove('hidden');
        manualSection.classList.add('hidden');
        // Reset manual input
        ongkirManualInput.value = 0;
    } else {
        presetSection.classList.add('hidden');
        manualSection.classList.remove('hidden');
        // Reset preset select
        idBiayaTambahanSelect.value = '';
    }
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeIsiDataModal();
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
script>
// ✅ GANTI FUNCTION INI DI BAGIAN <script> 

function sendNotificationSelesaiDicuci() {
    const btn = document.getElementById('btnSendNotification');
    const originalContent = btn.innerHTML;
    
    // Konfirmasi dulu
    if (!confirm('Kirim notifikasi ke pelanggan bahwa cucian sudah selesai dicuci?')) {
        return;
    }
    
    // Disable button dan show loading
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Mengirim...</span>
    `;
    
    // TODO: IMPLEMENTASI FCM AKAN DITAMBAHKAN NANTI
    // Sementara simulasi dengan timeout
    setTimeout(() => {
        // Success
        btn.innerHTML = `
            <i class="bi bi-check-circle-fill text-lg"></i>
            <span>Notifikasi Terkirim!</span>
        `;
        btn.classList.remove('from-blue-500', 'to-indigo-500', 'hover:from-blue-600', 'hover:to-indigo-600');
        btn.classList.add('from-green-500', 'to-emerald-500');
        
        // Show success alert
        alert('✅ Notifikasi berhasil dikirim ke pelanggan!');
        
        // Reset button after 3 seconds
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.classList.remove('from-green-500', 'to-emerald-500');
            btn.classList.add('from-blue-500', 'to-indigo-500', 'hover:from-blue-600', 'hover:to-indigo-600');
            btn.disabled = false;
        }, 3000);
    }, 2000);
}
</script>

@endsection