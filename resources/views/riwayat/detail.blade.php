{{-- FE-DOC: Template frontend untuk resources/views/riwayat/detail.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
{{-- resources/views/riwayat/detail.blade.php --}}
{{-- ============================================================
     HALAMAN: DETAIL TRANSAKSI
     Deskripsi: Menampilkan detail lengkap satu transaksi:
     - Detail Order (layanan yang diambil)
     - Informasi Transaksi (no nota, tanggal, kasir, keterangan)
     - Status Transaksi & Status Pembayaran
     - Rincian Pembayaran (subtotal, DP, diskon, total)
     - Tombol aksi: Proses, Siap Diambil, Selesai, Bayar, Cetak, Batal, Hapus
     Role: Admin
============================================================ --}}
@extends('layouts.master')

@section('title', 'Detail Riwayat Transaksi')

@section('content')

@php
    # Kalkulasi nilai-nilai pembayaran dari data transaksi dan detail 
    $subtotal     = $detail->sum('total_harga');         // Total semua layanan
    $diskon       = $transaksi->diskon ?? 0;             // Diskon (jika ada)
    $totalTagihan = $subtotal - $diskon;                 // Total setelah diskon
    $dp           = $transaksi->total_bayar ?? 0;        // Jumlah yang sudah dibayar
    $sisaBayar    = $totalTagihan - $dp;                 // Sisa yang belum dibayar

    # Tentukan status bayar berdasarkan sisa tagihan
    if ($sisaBayar <= 0)    $statusBayar = 'lunas';
    elseif ($dp > 0)        $statusBayar = 'DP';
    else                    $statusBayar = 'belum bayar';
    $canReadyForPickup = in_array($statusBayar, ['lunas', 'DP'], true);

    # Flag untuk menentukan apakah boleh DP atau harus pelunasan    
    $bolehDP        = in_array($transaksi->status_transaksi, ['antrian', 'proses']);
    $harusPelunasan = in_array($transaksi->status_transaksi, ['siap_di_ambil', 'selesai']);
@endphp

<link rel="stylesheet" href="{{ asset('css/riwayat-detail.css') }}">

<div class="min-h-screen bg-gray-50">

    {{-- ========================================
         HEADER
         Tombol kembali ke Riwayat Transaksi atau Dashboard
         tergantung parameter 'from' pada URL
    ======================================== --}}
    @php
        # Tentukan URL kembali berdasarkan asal halaman
        $backUrl = request()->from == 'dashboard'
            ? route('admin.dashboard')
            : route('riwayat.index');
    @endphp

    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-md sticky top-0 z-10">
        <a href="{{ $backUrl }}"
            class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-gray-900">Detail Transaksi</span>
    </div>

    <div class="px-8 py-6 space-y-6">

        {{-- ========================================
             FLASH MESSAGE
             Notifikasi hasil aksi dari controller (hapus, batal, proses, dll.)
        ======================================== --}}
        @if(session('success'))
            <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-green-600 text-xl"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-100 border border-red-300 text-red-800 rounded-xl flex items-center gap-3">
                <i class="bi bi-x-circle-fill text-red-600 text-xl"></i>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        {{-- ========================================
             DATA PELANGGAN
             Foto profil, nama, dan nomor HP pelanggan
        ======================================== --}}
        <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-5">
                    {{-- Foto profil pelanggan dengan fallback ke ikon --}}
                    <div class="w-16 h-16 rounded-full overflow-hidden bg-yellow-50 flex items-center justify-center flex-shrink-0 border-2 border-yellow-200">
                        @if(!empty($pelanggan->gambar))
                            <img src="{{ asset('images/' . $pelanggan->gambar) }}"
                                alt="{{ $pelanggan->nama_pelanggan }}"
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null; this.src='{{ asset('images/default-user.png') }}';">
                        @else
                            <i class="bi bi-person-fill text-4xl text-yellow-500"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-xl font-bold leading-tight text-gray-800">{{ $pelanggan->nama_pelanggan }}</p>
                        <p class="text-sm text-gray-600 flex items-center gap-2 mt-1">
                            <i class="bi bi-phone-fill text-yellow-500"></i>
                            {{ $pelanggan->no_hp }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">

            {{-- ========================================
                 KOLOM KIRI (8/12)
                 Detail Order + Informasi Transaksi + Status
            ======================================== --}}
            <div class="col-span-12 lg:col-span-8 space-y-6">

                {{-- ========================================
                     DETAIL ORDER
                     Daftar layanan yang ada dalam transaksi ini
                ======================================== --}}
                <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <h2 class="font-bold text-xl flex items-center gap-3 text-gray-800">
                            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center">
                                <i class="bi bi-basket-fill text-xl text-orange-500"></i>
                            </div>
                            Detail Order
                        </h2>
                        {{-- Tombol Edit Layanan hanya tampil jika status masih Antrian --}}
                        @if($transaksi->status_transaksi == 'antrian')
                            <a href="{{ route('riwayat.edit', $transaksi->id_transaksi) }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-xl shadow-sm hover:shadow-md transition-all hover:scale-105">
                                <i class="bi bi-pencil-fill"></i> Edit Layanan
                            </a>
                        @endif
                    </div>

                    {{-- Iterasi setiap item layanan dalam transaksi --}}
                    <div class="space-y-4">
                        @foreach($detail as $item)
                            <div class="flex justify-between items-center gap-4 p-5 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition-all border border-yellow-200">
                                <div class="flex-1 space-y-2">
                                    <p class="font-bold text-lg text-gray-800">
                                        {{ $item->nama_jenis }}
                                        <span class="text-gray-600 font-normal">({{ $item->nama_layanan }})</span>
                                    </p>
                                    <p class="text-gray-600 text-sm flex items-center gap-2">
                                        <i class="bi bi-tag-fill text-orange-500"></i>
                                        Rp {{ number_format($item->harga_jenis, 0, ',', '.') }} / {{ $item->satuan }}
                                    </p>
                                    <div class="bg-orange-50 px-3 py-2 rounded-lg inline-block border border-orange-200">
                                        <p class="font-semibold text-orange-700">
                                            SubTotal: Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                {{-- Badge kuantitas layanan --}}
                                <div class="bg-orange-500 px-6 py-4 rounded-xl font-bold text-white text-center whitespace-nowrap shadow-sm">
                                    {{ $item->qty }} {{ $item->satuan }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ========================================
                     INFORMASI TRANSAKSI & STATUS (Grid 2 kolom)
                ======================================== --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- INFORMASI TRANSAKSI: No Nota, Tanggal, Kasir, Keterangan --}}
                    <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all">
                        <h3 class="font-bold text-xl mb-5 text-gray-800 flex items-center gap-3 pb-4 border-b border-gray-200">
                            <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                                <i class="bi bi-info-circle-fill text-yellow-500 text-xl"></i>
                            </div>
                            Informasi Transaksi
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                                <span class="text-gray-600 font-medium text-sm">No Nota:</span>
                                <span class="font-bold text-gray-800">TRX/{{ $transaksi->id_transaksi }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                                <span class="text-gray-600 font-medium text-sm">Tanggal Masuk:</span>
                                <span class="font-bold text-gray-800">{{ $transaksi->tgl_transaksi }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                                <span class="text-gray-600 font-medium text-sm">Tanggal Lunas:</span>
                                <span class="font-bold text-gray-800">{{ $transaksi->tgl_lunas ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                                <span class="text-gray-600 font-medium text-sm">Estimasi Selesai:</span>
                                <span class="font-bold text-gray-800">{{ $transaksi->tgl_estimasi ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                                <span class="text-gray-600 font-medium text-sm">Kasir:</span>
                                <span class="font-bold text-gray-800">{{ $transaksi->nama_kasir ?? 'Admin' }}</span>
                            </div>
                            <div class="flex flex-col p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                                <span class="text-gray-600 font-medium text-sm mb-2">Keterangan:</span>
                                <span class="font-bold text-gray-800">{{ $transaksi->keterangan ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- STATUS TRANSAKSI & PEMBAYARAN --}}
                    <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all">
                        <h3 class="font-bold text-xl mb-5 text-gray-800 pb-4 border-b border-gray-200 flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center">
                                <i class="bi bi-bookmark-check-fill text-orange-500 text-xl"></i>
                            </div>
                            Status
                        </h3>
                        <div class="space-y-4">
                            {{-- Status Transaksi --}}
                            <div class="p-4 bg-orange-50 rounded-xl border border-orange-100">
                                <span class="font-medium text-gray-700 block mb-3 text-sm">Status Transaksi</span>
                                <span class="inline-block px-5 py-3 rounded-xl bg-orange-100 text-orange-700 capitalize font-bold w-full text-center border border-orange-200">
                                    {{ $transaksi->status_transaksi }}
                                </span>
                            </div>
                            {{-- Status Pembayaran (dapat berubah via AJAX bayar) --}}
                            <div class="p-4 bg-yellow-50 rounded-xl border border-yellow-100">
                                <span class="font-medium text-gray-700 block mb-3 text-sm">Status Pembayaran</span>
                                <span id="statusBayarDisplay"
                                    class="inline-block px-5 py-3 rounded-xl capitalize font-bold w-full text-center
                                    {{ $statusBayar == 'lunas'
                                        ? 'bg-orange-100 text-orange-700 border border-orange-200'
                                        : ($statusBayar == 'DP'
                                            ? 'bg-yellow-100 text-yellow-700 border border-yellow-200'
                                            : 'bg-red-100 text-red-700 border border-red-200') }}">
                                    {{ $statusBayar }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ========================================
                 KOLOM KANAN (4/12)
                 Rincian Pembayaran + Tombol Aksi
            ======================================== --}}
            <div class="col-span-12 lg:col-span-4 space-y-6">

                {{-- RINCIAN PEMBAYARAN: SubTotal, DP, Diskon, Total --}}
                <div class="bg-white shadow-sm rounded-2xl p-6 border-2 border-yellow-300 hover:shadow-md transition-all">
                    <h3 class="font-bold text-xl mb-5 text-gray-800 flex items-center gap-3 pb-4 border-b border-gray-200">
                        <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                            <i class="bi bi-cash-coin text-yellow-600 text-xl"></i>
                        </div>
                        Rincian Pembayaran
                    </h3>
                    <div class="space-y-3">
                        {{-- Metode Bayar --}}
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <span class="text-gray-600 font-medium text-sm">Metode Bayar</span>
                            <span class="font-bold text-gray-800">{{ $transaksi->nama_metode_bayar ?? '-' }}</span>
                        </div>
                        {{-- SubTotal --}}
                        <div class="flex justify-between items-center p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                            <span class="text-gray-600 font-medium text-sm">SubTotal</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        {{-- DP (tersembunyi jika belum ada DP) --}}
                        <div id="dpDisplay" class="flex justify-between items-center p-4 bg-orange-50 rounded-xl border-l-4 border-orange-400 {{ $dp > 0 ? '' : 'hidden' }}">
                            <span class="text-gray-600 font-medium text-sm">DP</span>
                            <span class="font-bold text-orange-700" id="dpAmount">Rp {{ number_format($dp, 0, ',', '.') }}</span>
                        </div>
                        {{-- Diskon --}}
                        <div class="flex justify-between items-center p-4 bg-red-50 rounded-xl border border-red-200">
                            <span class="text-gray-600 font-medium text-sm">Diskon</span>
                            <span class="font-bold text-red-600">- Rp {{ number_format($diskon, 0, ',', '.') }}</span>
                        </div>
                        {{-- Total Harga (setelah diskon) --}}
                        <div class="flex justify-between items-center p-5 bg-yellow-400 rounded-xl shadow-sm mt-4 border border-yellow-300">
                            <span class="font-bold text-lg text-gray-900">Total Harga</span>
                            <span class="font-bold text-2xl text-gray-900">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- ========================================
                     TOMBOL AKSI
                     Tombol yang tampil disesuaikan dengan status transaksi
                ======================================== --}}
                <div class="space-y-3">

                    {{-- Tombol Proses Order: hanya tampil jika status 'antrian' --}}
                    @if($transaksi->status_transaksi == 'antrian')
                        <a href="{{ route('riwayat.proses', $transaksi->id_transaksi) }}"
                            class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                            <i class="bi bi-play-fill text-xl"></i> Proses Order
                        </a>
                    @endif

                    {{-- Tombol Siap Diambil: hanya tampil jika status 'proses' --}}
                    @if($transaksi->status_transaksi == 'proses')
                        @if($canReadyForPickup)
                            <a href="{{ route('riwayat.siap_di_ambil', $transaksi->id_transaksi) }}"
                                class="bg-orange-400 hover:bg-orange-500 text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                                <i class="bi bi-check-circle-fill text-xl"></i> Order Siap Diambil
                            </a>
                        @else
                            <div class="bg-red-50 border border-red-200 text-red-700 py-4 px-5 rounded-xl flex items-start gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                                <span class="font-semibold">Order belum bisa diambil karena pelanggan belum melakukan pembayaran atau DP.</span>
                            </div>
                        @endif
                    @endif

                    {{-- Tombol Selesai: tampil jika status 'selesai' atau 'siap_di_ambil' --}}
                    @if($transaksi->status_transaksi == 'selesai' || $transaksi->status_transaksi == 'siap_di_ambil')
                        <a href="{{ route('riwayat.selesai', $transaksi->id_transaksi) }}"
                            class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                            <i class="bi bi-box-arrow-in-down text-xl"></i> Order Selesai
                        </a>
                    @endif

                    {{-- Tombol Bayar Sekarang: tersembunyi jika sudah lunas --}}
                    <button id="btnBayarSekarang" onclick="openModalBayar()"
                        class="bg-orange-500 hover:bg-orange-600 w-full text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105 {{ $statusBayar === 'lunas' ? 'hidden' : '' }}">
                        <i class="bi bi-cash-stack text-xl"></i> Bayar Sekarang
                    </button>

                    {{-- Tombol Cetak Nota: selalu tampil --}}
                    <button onclick="bukaPopupNota({{ $transaksi->id_transaksi }})"
                        class="bg-gray-600 hover:bg-gray-700 w-full text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                        <i class="bi bi-printer-fill text-xl"></i> Cetak Nota
                    </button>

                    {{-- Tombol Batalkan: hanya tampil jika status masih 'antrian' --}}
                    @if($transaksi->status_transaksi == 'antrian')
                        <form id="formBatal" action="{{ route('riwayat.batal', $transaksi->id_transaksi) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="button" onclick="confirmBatal()"
                                class="bg-red-500 hover:bg-red-600 w-full text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                                <i class="bi bi-x-lg text-lg"></i> Batalkan Transaksi
                            </button>
                        </form>
                    @endif

                    {{-- Tombol Hapus: selalu tampil, memerlukan konfirmasi --}}
                    <form id="formHapus" action="{{ route('riwayat.destroy', $transaksi->id_transaksi) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="confirmHapus()"
                            class="bg-gray-800 hover:bg-gray-900 w-full text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                            <i class="bi bi-trash-fill text-lg"></i> Hapus Transaksi
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================
     MODAL BAYAR
     Form untuk memasukkan nominal pembayaran (DP atau lunas).
     Data awal diambil dari window.DETAIL_DATA yang di-pass dari PHP.
======================================== --}}
<div id="modalBayar" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-[9999] flex justify-center items-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="bg-yellow-400 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                <div class="w-10 h-10 bg-white/30 rounded-lg flex items-center justify-center">
                    <i class="bi bi-cash-stack text-gray-900 text-xl"></i>
                </div>
                <span id="modalTitle">Pelunasan Pembayaran</span>
            </h2>
        </div>
        <div class="p-6 space-y-4">
            <div id="infoModePembayaran" class="p-4 rounded-xl border-2"></div>
            <div class="space-y-3 text-gray-700">
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <span class="font-medium">SubTotal:</span>
                    <span class="font-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                {{-- Tampil DP jika sudah ada pembayaran sebelumnya --}}
                <div id="modalDpDisplay" class="flex justify-between items-center p-4 bg-orange-50 rounded-xl border-l-4 border-orange-400 {{ $dp > 0 ? '' : 'hidden' }}">
                    <span class="font-medium text-gray-700">DP Terbayar:</span>
                    <span class="font-bold text-orange-700" id="modalDpAmount">Rp {{ number_format($dp, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-red-50 rounded-xl border border-red-200">
                    <span class="font-medium text-gray-700">Diskon:</span>
                    <span class="font-bold text-red-600">- Rp {{ number_format($diskon, 0, ',', '.') }}</span>
                </div>
                {{-- Sisa yang harus dibayar --}}
                <div class="flex justify-between items-center p-5 bg-orange-500 rounded-xl shadow-sm">
                    <span class="font-bold text-white text-lg">Sisa Bayar:</span>
                    <span class="font-bold text-white text-2xl" id="sisaBayarDisplay">Rp {{ number_format($sisaBayar, 0, ',', '.') }}</span>
                </div>
            </div>
            <form id="formBayar" class="space-y-4">
                @csrf
                <div>
                    <label class="font-bold text-gray-700 block mb-2" id="labelNominal">Masukkan Nominal Pembayaran</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 font-bold text-gray-500">Rp</span>
                        {{-- Input nominal ditampilkan dengan format ribuan, nilai asli di hidden input --}}
                        <input type="text" name="jumlah_bayar_display" id="jumlahBayarDisplay"
                            inputmode="numeric"
                            class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all"
                            placeholder="0"
                            value="{{ $sisaBayar > 0 ? number_format($sisaBayar, 0, ',', '.') : '' }}">
                        <input type="hidden" name="jumlah_bayar" id="jumlahBayar" value="{{ $sisaBayar }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-2" id="infoPembayaran"></p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModalBayar()"
                        class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-sm hover:shadow-md transition-all">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================
     PASS DATA PHP KE JAVASCRIPT
     Data transaksi di-pass ke window object agar dapat diakses
     oleh riwayat-detail.js tanpa perlu request tambahan
======================================== --}}
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
    window.DETAIL_DATA = {
        subtotal        : {{ $subtotal }},
        diskon          : {{ $diskon }},
        totalTagihan    : {{ $totalTagihan }},
        dp              : {{ $dp }},
        sisaBayar       : {{ $sisaBayar }},
        statusBayar     : '{{ $statusBayar }}',
        statusTransaksi : '{{ $transaksi->status_transaksi }}',
        bolehDP         : {{ $bolehDP        ? 'true' : 'false' }},
        harusPelunasan  : {{ $harusPelunasan ? 'true' : 'false' }},
        idTransaksi     : {{ $transaksi->id_transaksi }},
        csrfToken       : '{{ csrf_token() }}',
        routeBayar      : '{{ route('riwayat.bayar.submit', $transaksi->id_transaksi) }}'
    };
</script>

{{-- Library dan script utama halaman detail --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/riwayat-detail.js') }}?v={{ filemtime(public_path('js/riwayat-detail.js')) }}"></script>

@endsection
