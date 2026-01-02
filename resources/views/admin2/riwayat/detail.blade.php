@extends('layouts.master')

@section('content')

@php
    // Hitung subtotal dari detail transaksi
    $subtotal = $detail->sum('total_harga'); 
    $diskon = $transaksi->diskon ?? 0;
    $totalTagihan = $subtotal - $diskon;

    // Ambil total bayar / DP terbaru
    $dp = $transaksi->total_bayar ?? 0;
    $sisaBayar = $totalTagihan - $dp;

    // Tentukan status pembayaran
    if($sisaBayar <= 0){
        $statusBayar = 'lunas';
    } elseif($dp > 0){
        $statusBayar = 'DP';
    } else {
        $statusBayar = 'belum bayar';
    }
@endphp

<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    @php
        // kalau URL punya ?from=dashboard → back ke dashboard
        $backUrl = request()->from == 'dashboard'
            ? route('admin2.dashboard')
            : route('admin2.riwayat.index');
    @endphp

    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ $backUrl }}" 
            class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>

        <span class="text-2xl font-bold">Detail Transaksi</span>
    </div>

    <div class="px-8 py-6 space-y-6">
        {{-- DATA PELANGGAN --}}
        <div class="bg-white shadow-lg rounded-2xl p-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-5">
                    @if($pelanggan && $pelanggan->gambar)
                        <img src="{{ asset('storage/'.$pelanggan->gambar) }}" class="w-24 h-24 rounded-xl object-cover border-2 border-gray-200 shadow-sm">
                    @else
                        <div class="w-24 h-24 rounded-xl bg-gray-100 border-2 border-gray-200 flex items-center justify-center text-gray-400 shadow-sm">
                            <i class="bi bi-person-fill text-4xl"></i>
                        </div>
                    @endif

                    <div>
                        <p class="text-xl font-bold capitalize text-gray-800">
                            {{ $pelanggan->nama ?? $pelanggan->nama_pelanggan ?? 'Pelanggan Umum' }}
                        </p>
                        <p class="text-gray-500 mt-1 flex items-center gap-2">
                            <i class="bi bi-telephone-fill"></i>
                            {{ $pelanggan->no_hp ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button class="bg-gray-700 hover:bg-gray-800 text-white p-3 rounded-xl shadow-md hover:shadow-lg transition-all hover:scale-105">
                        <i class="bi bi-share-fill text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            {{-- LEFT COLUMN --}}
            <div class="col-span-12 lg:col-span-8 space-y-6">
               {{-- DETAIL ORDER --}}
<div class="bg-white shadow-lg rounded-2xl p-6">
    
    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-5 pb-4 border-b-2 border-gray-100">
        <h2 class="font-bold text-xl flex items-center gap-3 text-gray-800">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-basket-fill text-xl text-red-600"></i>
            </div>
            Detail Order
        </h2>

        {{-- BUTTON EDIT (KANAN) --}}
        @if($transaksi->status_transaksi == 'antrian')
            <a href="{{ route('admin2.riwayat.edit', $transaksi->id_transaksi) }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all">
                <i class="bi bi-pencil-fill"></i>
                Edit Layanan
            </a>
        @endif
    </div>
    
    {{-- LIST LAYANAN --}}
    <div class="space-y-4">
        @foreach($detail as $item)
            <div class="flex justify-between items-center gap-4 p-5 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors border border-gray-200">
                <div class="flex-1 space-y-2">
                    <p class="font-bold text-lg text-gray-800">
                        {{ $item->nama_jenis }} 
                        <span class="text-gray-500 font-normal">({{ $item->nama_layanan }})</span>
                    </p>
                    <p class="text-gray-600 text-sm flex items-center gap-2">
                        <i class="bi bi-tag-fill text-yellow-500"></i>
                        Rp {{ number_format($item->harga_jenis,0,',','.') }} / {{ $item->satuan }}
                    </p>
                    <div class="bg-green-50 px-3 py-2 rounded-lg inline-block">
                        <p class="font-semibold text-green-700">
                            SubTotal: Rp {{ number_format($item->total_harga,0,',','.') }}
                        </p>
                    </div>
                </div>

                <div class="bg-blue-500 px-6 py-4 rounded-xl font-bold text-white text-center whitespace-nowrap">
                    {{ $item->qty }} {{ $item->satuan }}
                </div>
            </div>
        @endforeach
    </div>
</div>

                {{-- INFORMASI TRANSAKSI & STATUS --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- INFORMASI TRANSAKSI --}}
                    <div class="bg-white shadow-lg rounded-2xl p-6">
                        <h3 class="font-bold text-xl mb-5 text-gray-800 flex items-center gap-3 pb-4 border-b-2 border-gray-100">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="bi bi-info-circle-fill text-blue-600 text-xl"></i>
                            </div>
                            Informasi Transaksi
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600 font-semibold text-sm">No Nota:</span>
                                <span class="font-bold text-gray-800">TRX/{{ $transaksi->id_transaksi }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600 font-semibold text-sm">Tanggal Masuk:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->tgl_transaksi }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600 font-semibold text-sm">Tanggal Lunas:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->tgl_lunas ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600 font-semibold text-sm">Estimasi Selesai:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->tgl_estimasi ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600 font-semibold text-sm">Kasir:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->nama_kasir ?? '-' }}</span>
                            </div>
                            <div class="flex flex-col p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600 font-semibold text-sm mb-2">Keterangan:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->keterangan ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="bg-white shadow-lg rounded-2xl p-6">
                        <h3 class="font-bold text-xl mb-5 text-gray-800 pb-4 border-b-2 border-gray-100">Status</h3>
                        <div class="space-y-4">
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <span class="font-semibold text-gray-600 block mb-3 text-sm">Status Transaksi</span>
                                <span class="inline-block px-5 py-3 rounded-xl bg-blue-500 text-white capitalize font-bold shadow-sm w-full text-center">
                                    {{ $transaksi->status_transaksi }}
                                </span>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <span class="font-semibold text-gray-600 block mb-3 text-sm">Status Pembayaran</span>
                                <span class="inline-block px-5 py-3 rounded-xl 
                                    {{ $statusBayar == 'lunas' ? 'bg-green-600 text-white' : ($statusBayar == 'DP' ? 'bg-yellow-400 text-gray-900' : 'bg-red-500 text-white') }} 
                                    capitalize font-bold shadow-sm w-full text-center">
                                    {{ $statusBayar }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-span-12 lg:col-span-4 space-y-6">
                {{-- RINCIAN HARGA --}}
                <div class="bg-white shadow-lg rounded-2xl p-6 border-2 border-yellow-200">
                    <h3 class="font-bold text-xl mb-5 text-gray-800 flex items-center gap-3 pb-4 border-b-2 border-gray-100">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-cash-coin text-yellow-600 text-xl"></i>
                        </div>
                        Rincian Pembayaran
                    </h3>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl">
                            <span class="text-gray-600 font-semibold text-sm">Metode Bayar</span>
                            <span class="font-bold text-gray-800">{{ $transaksi->nama_metode_bayar ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl">
                            <span class="text-gray-600 font-semibold text-sm">SubTotal</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($subtotal,0,',','.') }}</span>
                        </div>
                        @if($dp > 0)
                        <div class="flex justify-between items-center p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                            <span class="text-gray-600 font-semibold text-sm">DP</span>
                            <span class="font-bold text-yellow-600">Rp {{ number_format($dp,0,',','.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center p-4 bg-red-50 rounded-xl border border-red-200">
                            <span class="text-gray-600 font-semibold text-sm">Diskon</span>
                            <span class="font-bold text-red-600">- Rp {{ number_format($diskon,0,',','.') }}</span>
                        </div>
                        <div class="flex justify-between items-center p-5 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl shadow-md mt-4">
                            <span class="font-bold text-lg text-gray-900">Total Harga</span>
                            <span class="font-bold text-xl text-gray-900">Rp {{ number_format($totalTagihan,0,',','.') }}</span>
                        </div>
                    </div>
                </div>

    {{-- BUTTON AKSI --}}
     <div class="space-y-3">
    {{-- Tombol Proses --}}
    @if($transaksi->status_transaksi == 'antrian')
        <a href="{{ route('admin2.riwayat.proses', $transaksi->id_transaksi) }}" 
        class="bg-blue-600 hover:bg-blue-700 text-white text-center py-4 px-5 font-bold shadow-lg rounded-xl flex items-center justify-center gap-2 hover:scale-105 transition-all">
            <i class="bi bi-play-fill text-xl"></i> Proses Order
        </a>
    @endif

    {{-- Tombol Selesaikan --}}
    @if($transaksi->status_transaksi == 'proses')
        <a href="{{ route('admin2.riwayat.siap_di_ambil', $transaksi->id_transaksi) }}" 
        class="bg-teal-600 hover:bg-teal-700 text-white text-center py-4 px-5 font-bold shadow-lg rounded-xl flex items-center justify-center gap-2 hover:scale-105 transition-all">
            <i class="bi bi-check-circle-fill text-xl"></i> Order Siap Diambil
        </a>
    @endif

    {{-- Tombol Siap Diambil --}}
    @if($transaksi->status_transaksi == 'selesai' || $transaksi->status_transaksi == 'siap_di_ambil')
        <a href="{{ route('admin2.riwayat.selesai', $transaksi->id_transaksi) }}" 
        class="bg-yellow-400 hover:bg-yellow-500 text-white text-center py-4 px-5 font-bold shadow-lg rounded-xl flex items-center justify-center gap-2 hover:scale-105 transition-all">
            <i class="bi bi-box-arrow-in-down text-xl"></i> Order Selesai
        </a>
    @endif

    {{-- Tombol Bayar --}}
    @if($sisaBayar > 0)
        <button onclick="openModalBayar()"
            class="bg-green-600 hover:bg-green-700 w-full text-gray-900 text-center py-4 px-5 font-bold shadow-lg rounded-xl flex items-center justify-center gap-2 hover:scale-105 transition-all">
            <i class="bi bi-cash-stack text-xl"></i> Bayar Sekarang
        </button>
    @endif

    {{-- Batalkan --}}
    @if($transaksi->status_transaksi != 'selesai' && $transaksi->status_transaksi != 'siap_di_ambil')
        <a href="{{ route('admin2.riwayat.batal', $transaksi->id_transaksi) }}"
        class="bg-gray-500 hover:bg-gray-600 text-white text-center py-4 px-5 font-bold shadow-lg rounded-xl flex items-center justify-center gap-2 hover:scale-105 transition-all">
            <i class="bi bi-x-lg text-lg"></i> Batalkan Transaksi
        </a>
    @endif

    {{-- Hapus --}}
    <form action="{{ route('admin2.riwayat.destroy', $transaksi->id_transaksi) }}" method="POST"
        onsubmit="return confirm('Yakin mau hapus transaksi ini?')">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="bg-red-600 hover:bg-red-700 w-full text-white text-center py-4 px-5 font-bold shadow-lg rounded-xl flex items-center justify-center gap-2 hover:scale-105 transition-all">
            <i class="bi bi-trash-fill text-lg"></i> Hapus Transaksi
        </button>
    </form>
</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL BAYAR --}}
<div id="modalBayar" class="fixed inset-0 bg-black/40 hidden z-[9999] flex justify-center items-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-yellow-400 p-6">
            <h2 class="text-2xl font-bold text-gray-900">Pelunasan Pembayaran</h2>
        </div>

        <div class="p-6 space-y-4">
            <div class="space-y-3 text-gray-700">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="font-semibold">SubTotal:</span>
                    <span class="font-bold">Rp {{ number_format($subtotal,0,',','.') }}</span>
                </div>
                @if($dp > 0)
                <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <span class="font-semibold text-yellow-700">DP Terbayar:</span>
                    <span class="font-bold text-yellow-700">Rp {{ number_format($dp,0,',','.') }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-200">
                    <span class="font-semibold text-red-600">Diskon:</span>
                    <span class="font-bold text-red-600">- Rp {{ number_format($diskon,0,',','.') }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-md">
                    <span class="font-bold text-white text-lg">Sisa Bayar:</span>
                    <span class="font-bold text-white text-xl">Rp {{ number_format($sisaBayar,0,',','.') }}</span>
                </div>
            </div>

            <form action="{{ route('admin2.riwayat.bayar.submit', $transaksi->id_transaksi) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="font-bold text-gray-700 block mb-2">Masukkan Nominal Pelunasan</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 font-bold text-gray-500">Rp</span>
                        <input type="number" name="jumlah_bayar"
                            class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all"
                            placeholder="Masukkan nominal..." required min="1" max="{{ $sisaBayar }}">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModalBayar()"
                        class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModalBayar() {
    document.getElementById('modalBayar').classList.remove('hidden');
}
function closeModalBayar() {
    document.getElementById('modalBayar').classList.add('hidden');
}
</script>

@endsection
