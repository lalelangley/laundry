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
                        <p class="font-semibold">{{ $pesanan->pelanggan->nama_pelanggan ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">No HP</p>
                        <p class="font-semibold">{{ $pesanan->pelanggan->no_hp ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-gray-500">Alamat</p>
                        <p class="font-semibold">{{ $pesanan->pelanggan->alamat ?? '-' }}</p>
                    </div>
                </div>
            </div>
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
                            @else bg-gray-500
                            @endif">
                            {{ str_replace('_',' ',$pesanan->status_transaksi) }}
                        </span>
                    </div>

                    <div>
                        <p class="text-gray-500">Pembayaran</p>
                        <span class="font-semibold">
                            {{ $pesanan->status_bayar == 'lunas' ? 'Lunas' : 'Belum Bayar' }}
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
                        if($pesanan->detail_transaksi) {
                            foreach($pesanan->detail_transaksi as $d) {
                                $subtotalAll += ($d->harga * $d->qty);
                            }
                        }
                    @endphp
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <b>Rp {{ number_format($subtotalAll,0,',','.') }}</b>
                    </div>
                    
                    @if($pesanan->total_qty && $pesanan->id_satuan)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Total Qty</span>
                        <b>{{ $pesanan->total_qty }} {{ $pesanan->satuan->nama_satuan ?? '' }}</b>
                    </div>
                    @endif
                    
                    <hr>
                    <div class="flex justify-between text-lg font-bold text-yellow-600">
                        <span>Total</span>
                        <span>Rp {{ number_format($pesanan->total_harga,0,',','.') }}</span>
                    </div>
                </div>
            </div>

            {{-- AKSI --}}
            <div class="bg-white rounded-xl shadow p-5 space-y-3">
                <h2 class="font-bold mb-2">Aksi</h2>

                @php
                    // Cek apakah data pesanan sudah diisi
                    $dataLengkap = $pesanan->total_qty && $pesanan->id_satuan;
                @endphp

                {{-- 🔥 BUTTON ISI DATA PESANAN --}}
                @if(!in_array($pesanan->status_transaksi, ['selesai', 'ditolak']))
                <button onclick="openIsiDataModal()" 
                        class="w-full py-2 bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600 flex items-center justify-center gap-2">
                    <i class="bi bi-pencil-square"></i> 
                    <span>{{ $dataLengkap ? 'Edit' : 'Isi' }} Data Pesanan</span>
                </button>
                @endif

                {{-- ⚠️ WARNING JIKA BELUM ISI DATA --}}
                @if(!$dataLengkap && !in_array($pesanan->status_transaksi, ['selesai', 'ditolak']))
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

                {{-- 🔥 BUTTON KONFIRMASI PESANAN - HANYA MUNCUL JIKA DATA LENGKAP --}}
                @if($dataLengkap && !in_array($pesanan->status_transaksi, ['selesai', 'ditolak']))
                <button onclick="openKonfirmasiModal()" 
                        class="w-full py-2 bg-indigo-500 text-white rounded-lg font-semibold hover:bg-indigo-600 flex items-center justify-center gap-2">
                    <i class="bi bi-send"></i> 
                    <span>Kirim Konfirmasi ke Pelanggan</span>
                </button>
                @endif

                {{-- PROSES - HANYA MUNCUL JIKA DATA LENGKAP --}}
                @if($dataLengkap && in_array($pesanan->status_transaksi, ['antrian', 'menunggu_konfirmasi', 'dikonfirmasi']))
                <a href="{{ route('pesanan.online.proses',$pesanan->id_transaksi) }}"
                   class="block text-center py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center justify-center gap-2">
                    <i class="bi bi-play-circle"></i>
                    <span>Mulai Proses</span>
                </a>
                @endif

                {{-- 🔥 DECISION: PICKUP / DELIVERY - MUNCUL SAAT STATUS PROSES --}}
                @if($pesanan->status_transaksi == 'proses')
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

                {{-- SELESAI - MUNCUL SAAT SUDAH SIAP DI AMBIL --}}
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
            </div>
        </div>
    </div>
</div>

{{-- ========================================
     MODAL ISI DATA PESANAN
======================================== --}}
<div id="isiDataModal" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="bi bi-pencil-square text-purple-600"></i> Isi Data Pesanan
            </h3>
            <button onclick="closeIsiDataModal()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        <form action="{{ route('pesanan.online.updateData', $pesanan->id_transaksi) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                {{-- TOTAL QTY + SATUAN --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Total Qty + Satuan <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="number" 
                               name="total_qty" 
                               step="0.01"
                               value="{{ $pesanan->total_qty ?? '' }}"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Contoh: 5" 
                               required>
                        
                        {{-- ✅ DROPDOWN SATUAN DARI DATABASE --}}
                        <select name="id_satuan" 
                                class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                required>
                            <option value="">Pilih</option>
                            @foreach(\App\Models\Satuan::all() as $satuan)
                                <option value="{{ $satuan->id_satuan }}" 
                                        {{ ($pesanan->id_satuan ?? '') == $satuan->id_satuan ? 'selected' : '' }}>
                                    {{ $satuan->nama_satuan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Contoh: 5 Kg atau 10 Item</p>
                </div>

                {{-- TOTAL HARGA --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Total Harga <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                        <input type="number" 
                               name="total_harga" 
                               value="{{ $pesanan->total_harga ?? 0 }}"
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="100000" 
                               required>
                    </div>
                </div>

                {{-- KETERANGAN (OPTIONAL) --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Keterangan (Opsional)
                    </label>
                    <textarea name="keterangan" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              placeholder="Tambahkan catatan jika ada...">{{ $pesanan->keterangan ?? '' }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" 
                        onclick="closeIsiDataModal()"
                        class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
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

{{-- ========================================
     MODAL KONFIRMASI PESANAN
======================================== --}}
<div id="konfirmasiModal" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="bi bi-send text-indigo-600"></i> Konfirmasi Pesanan
            </h3>
            <button onclick="closeKonfirmasiModal()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        {{-- PREVIEW PESAN --}}
        <div class="bg-gray-50 p-4 rounded-lg mb-4 border border-gray-200">
            <p class="text-sm font-semibold text-gray-700 mb-2">Preview Pesan:</p>
            <div class="text-sm text-gray-600 space-y-1" id="pesanPreview">
                <p><strong>Halo {{ $pesanan->pelanggan->nama_pelanggan ?? 'Pelanggan' }}</strong>,</p>
                <p>Pesanan Anda sudah dikonfirmasi! 🎉</p>
                <p class="mt-2"><strong>Detail Pesanan:</strong></p>
                <p>📦 Order ID: {{ $pesanan->id_transaksi }}</p>
                <p>📋 Total Item: {{ $pesanan->total_qty ?? '-' }} {{ $pesanan->satuan->nama_satuan ?? 'item' }}</p>
                <p>💰 Total Harga: Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs">Terima kasih telah mempercayai layanan kami! 😊</p>
            </div>
        </div>

        <form action="{{ route('pesanan.online.konfirmasi', $pesanan->id_transaksi) }}" method="POST">
            @csrf

            {{-- PILIHAN METODE KIRIM --}}
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Kirim Via
                </label>
                <div class="space-y-2">
                    <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="metode_kirim" value="whatsapp" checked class="mr-3">
                        <i class="bi bi-whatsapp text-green-600 text-xl mr-2"></i>
                        <span class="font-medium">WhatsApp</span>
                    </label>
                    <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="metode_kirim" value="sms" class="mr-3">
                        <i class="bi bi-chat-dots text-blue-600 text-xl mr-2"></i>
                        <span class="font-medium">SMS</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" 
                        onclick="closeKonfirmasiModal()"
                        class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 font-semibold">
                    <i class="bi bi-send"></i> Kirim Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================
     MODAL DELIVERY
======================================== --}}
<div id="deliveryModal" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
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

        <div class="bg-gray-50 p-3 rounded-lg mb-4 text-sm">
            <p class="font-semibold text-gray-700">Alamat Pengiriman:</p>
            <p class="text-gray-600 mt-1">{{ $pesanan->pelanggan->alamat ?? '-' }}</p>
        </div>

        <form action="{{ route('pesanan.online.siap_di_ambil', $pesanan->id_transaksi) }}" method="GET">
            {{-- Hidden field untuk menandai ini delivery --}}
            <input type="hidden" name="mode" value="delivery">
            
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeDeliveryModal()"
                        class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-semibold">
                    <i class="bi bi-check-circle"></i> Konfirmasi Delivery
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================
     PRINT STYLES
======================================== --}}
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
</style>

{{-- ========================================
     MODAL JAVASCRIPT
======================================== --}}
<script>
// MODAL ISI DATA
function openIsiDataModal() {
    document.getElementById('isiDataModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeIsiDataModal() {
    document.getElementById('isiDataModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// MODAL KONFIRMASI
function openKonfirmasiModal() {
    document.getElementById('konfirmasiModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeKonfirmasiModal() {
    document.getElementById('konfirmasiModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// MODAL DELIVERY
function openDeliveryModal() {
    document.getElementById('deliveryModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeliveryModal() {
    document.getElementById('deliveryModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('isiDataModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeIsiDataModal();
    }
});

document.getElementById('konfirmasiModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeKonfirmasiModal();
    }
});

document.getElementById('deliveryModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeliveryModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeIsiDataModal();
        closeKonfirmasiModal();
        closeDeliveryModal();
    }
});
</script>

@endsection