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
       {{-- DATA PELANGGAN --}}
        <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-5">
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

                <div class="flex gap-3">
                    <button class="bg-gray-800 hover:bg-gray-900 text-white p-3 rounded-xl shadow-sm hover:shadow-md transition-all hover:scale-105">
                        <i class="bi bi-share-fill text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            {{-- LEFT COLUMN --}}
            <div class="col-span-12 lg:col-span-8 space-y-6">
               {{-- DETAIL ORDER --}}
                <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all">
                    
                    {{-- HEADER --}}
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <h2 class="font-bold text-xl flex items-center gap-3 text-gray-800">
                            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center">
                                <i class="bi bi-basket-fill text-xl text-orange-500"></i>
                            </div>
                            Detail Order
                        </h2>

                        {{-- BUTTON EDIT --}}
                        @if($transaksi->status_transaksi == 'antrian')
                            <a href="{{ route('riwayat.edit', $transaksi->id_transaksi) }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-xl shadow-sm hover:shadow-md transition-all hover:scale-105">
                                <i class="bi bi-pencil-fill"></i>
                                Edit Layanan
                            </a>
                        @endif
                    </div>
                    
                    {{-- LIST LAYANAN --}}
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
                                        Rp {{ number_format($item->harga_jenis,0,',','.') }} / {{ $item->satuan }}
                                    </p>
                                    <div class="bg-green-50 px-3 py-2 rounded-lg inline-block border border-green-200">
                                        <p class="font-semibold text-green-700">
                                            SubTotal: Rp {{ number_format($item->total_harga,0,',','.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="bg-blue-500 px-6 py-4 rounded-xl font-bold text-white text-center whitespace-nowrap shadow-sm">
                                    {{ $item->qty }} {{ $item->satuan }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- INFORMASI TRANSAKSI & STATUS --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- INFORMASI TRANSAKSI --}}
                    <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all">
                        <h3 class="font-bold text-xl mb-5 text-gray-800 flex items-center gap-3 pb-4 border-b border-gray-200">
                            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                                <i class="bi bi-info-circle-fill text-blue-500 text-xl"></i>
                            </div>
                            Informasi Transaksi
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <span class="text-gray-600 font-medium text-sm">No Nota:</span>
                                <span class="font-bold text-gray-800">TRX/{{ $transaksi->id_transaksi }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <span class="text-gray-600 font-medium text-sm">Tanggal Masuk:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->tgl_transaksi }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <span class="text-gray-600 font-medium text-sm">Tanggal Lunas:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->tgl_lunas ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <span class="text-gray-600 font-medium text-sm">Estimasi Selesai:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->tgl_estimasi ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <span class="text-gray-600 font-medium text-sm">Kasir:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->nama_kasir ?? 'Admin' }}</span>
                            </div>
                            <div class="flex flex-col p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <span class="text-gray-600 font-medium text-sm mb-2">Keterangan:</span>
                                <span class="text-gray-800 font-bold">{{ $transaksi->keterangan ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all">
                        <h3 class="font-bold text-xl mb-5 text-gray-800 pb-4 border-b border-gray-200 flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                                <i class="bi bi-bookmark-check-fill text-purple-500 text-xl"></i>
                            </div>
                            Status
                        </h3>
                        <div class="space-y-4">
                            <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                                <span class="font-medium text-gray-700 block mb-3 text-sm">Status Transaksi</span>
                                <span class="inline-block px-5 py-3 rounded-xl bg-purple-100 text-purple-700 capitalize font-bold w-full text-center border border-purple-200">
                                    {{ $transaksi->status_transaksi }}
                                </span>
                            </div>
                            <div class="p-4 bg-green-50 rounded-xl border border-green-100">
                                <span class="font-medium text-gray-700 block mb-3 text-sm">Status Pembayaran</span>
                                <span id="statusBayarDisplay" class="inline-block px-5 py-3 rounded-xl 
                                    {{ $statusBayar == 'lunas' ? 'bg-green-100 text-green-700 border border-green-200' : ($statusBayar == 'DP' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : 'bg-red-100 text-red-700 border border-red-200') }} 
                                    capitalize font-bold w-full text-center">
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
                <div class="bg-white shadow-sm rounded-2xl p-6 border-2 border-yellow-300 hover:shadow-md transition-all">
                    <h3 class="font-bold text-xl mb-5 text-gray-800 flex items-center gap-3 pb-4 border-b border-gray-200">
                        <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                            <i class="bi bi-cash-coin text-yellow-600 text-xl"></i>
                        </div>
                        Rincian Pembayaran
                    </h3>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <span class="text-gray-600 font-medium text-sm">Metode Bayar</span>
                            <span class="font-bold text-gray-800">{{ $transaksi->nama_metode_bayar ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <span class="text-gray-600 font-medium text-sm">SubTotal</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($subtotal,0,',','.') }}</span>
                        </div>
                        <div id="dpDisplay" class="flex justify-between items-center p-4 bg-yellow-50 rounded-xl border-l-4 border-yellow-400 {{ $dp > 0 ? '' : 'hidden' }}">
                            <span class="text-gray-600 font-medium text-sm">DP</span>
                            <span class="font-bold text-yellow-700" id="dpAmount">Rp {{ number_format($dp,0,',','.') }}</span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-red-50 rounded-xl border border-red-200">
                            <span class="text-gray-600 font-medium text-sm">Diskon</span>
                            <span class="font-bold text-red-600">- Rp {{ number_format($diskon,0,',','.') }}</span>
                        </div>
                        <div class="flex justify-between items-center p-5 bg-yellow-400 rounded-xl shadow-sm mt-4 border border-yellow-300">
                            <span class="font-bold text-lg text-gray-900">Total Harga</span>
                            <span class="font-bold text-2xl text-gray-900">Rp {{ number_format($totalTagihan,0,',','.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- BUTTON AKSI --}}
                <div class="space-y-3">
                    {{-- Tombol Proses --}}
                    @if($transaksi->status_transaksi == 'antrian')
                        <a href="{{ route('riwayat.proses', $transaksi->id_transaksi) }}" 
                        class="bg-blue-500 hover:bg-blue-600 text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                            <i class="bi bi-play-fill text-xl"></i> Proses Order
                        </a>
                    @endif

                    {{-- Tombol Selesaikan --}}
                    @if($transaksi->status_transaksi == 'proses')
                        <a href="{{ route('riwayat.siap_di_ambil', $transaksi->id_transaksi) }}" 
                        class="bg-teal-500 hover:bg-teal-600 text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                            <i class="bi bi-check-circle-fill text-xl"></i> Order Siap Diambil
                        </a>
                    @endif

                    {{-- Tombol Siap Diambil --}}
                    @if($transaksi->status_transaksi == 'selesai' || $transaksi->status_transaksi == 'siap_di_ambil')
                        <a href="{{ route('riwayat.selesai', $transaksi->id_transaksi) }}" 
                        class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                            <i class="bi bi-box-arrow-in-down text-xl"></i> Order Selesai
                        </a>
                    @endif

                    {{-- Tombol Bayar --}}
                    <button id="btnBayarSekarang" onclick="openModalBayar()"
                        class="bg-green-500 hover:bg-green-600 w-full text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105 {{ $statusBayar === 'lunas' ? 'hidden' : '' }}">
                        <i class="bi bi-cash-stack text-xl"></i> Bayar Sekarang
                    </button>

                    {{-- Batalkan --}}
                    @if(
                        $transaksi->status_transaksi !== 'batal' &&
                        $transaksi->status_transaksi !== 'selesai' &&
                        $statusBayar !== 'lunas'
                    )
                    <form id="formBatal" action="{{ route('riwayat.batal', $transaksi->id_transaksi) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="button" onclick="confirmBatal()"
                            class="bg-orange-400 hover:bg-orange-500 w-full text-white text-center py-4 px-5 font-bold shadow-sm rounded-xl flex items-center justify-center gap-2 hover:shadow-md transition-all hover:scale-105">
                            <i class="bi bi-x-lg text-lg"></i> Batalkan Transaksi
                        </button>
                    </form>
                    @endif

                    {{-- Hapus --}}
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

{{-- MODAL BAYAR --}}
<div id="modalBayar" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-[9999] flex justify-center items-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="bg-yellow-400 p-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                <div class="w-10 h-10 bg-white/30 rounded-lg flex items-center justify-center">
                    <i class="bi bi-cash-stack text-gray-900 text-xl"></i>
                </div>
                Pelunasan Pembayaran
            </h2>
        </div>

        <div class="p-6 space-y-4">
            <div class="space-y-3 text-gray-700">
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <span class="font-medium">SubTotal:</span>
                    <span class="font-bold">Rp {{ number_format($subtotal,0,',','.') }}</span>
                </div>
                <div id="modalDpDisplay" class="flex justify-between items-center p-4 bg-yellow-50 rounded-xl border-l-4 border-yellow-400 {{ $dp > 0 ? '' : 'hidden' }}">
                    <span class="font-medium text-gray-700">DP Terbayar:</span>
                    <span class="font-bold text-yellow-700" id="modalDpAmount">Rp {{ number_format($dp,0,',','.') }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-red-50 rounded-xl border border-red-200">
                    <span class="font-medium text-gray-700">Diskon:</span>
                    <span class="font-bold text-red-600">- Rp {{ number_format($diskon,0,',','.') }}</span>
                </div>
                <div class="flex justify-between items-center p-5 bg-green-500 rounded-xl shadow-sm">
                    <span class="font-bold text-white text-lg">Sisa Bayar:</span>
                    <span class="font-bold text-white text-2xl" id="sisaBayarDisplay">Rp {{ number_format($sisaBayar,0,',','.') }}</span>
                </div>
            </div>

            <form id="formBayar" class="space-y-4">
                @csrf
                <div>
                    <label class="font-bold text-gray-700 block mb-2">Masukkan Nominal Pelunasan</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 font-bold text-gray-500">Rp</span>
                        <input
                            type="text"
                            name="jumlah_bayar_display"
                            id="jumlahBayarDisplay"
                            inputmode="numeric"
                            class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all"
                            placeholder="0 (Kosongkan untuk belum bayar)"
                            value="{{ $sisaBayar > 0 ? number_format($sisaBayar, 0, ',', '.') : '' }}"
                        >
                        <input type="hidden" name="jumlah_bayar" id="jumlahBayar" value="{{ $sisaBayar }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="bi bi-info-circle-fill text-blue-500"></i>
                        Kosongkan atau isi 0 untuk status "Belum Bayar"
                    </p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModalBayar()"
                        class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-bold shadow-sm hover:shadow-md transition-all">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Data PHP untuk JavaScript
const subtotal = {{ $subtotal }};
const diskon = {{ $diskon }};
const totalTagihan = {{ $totalTagihan }};
let dpTerbayar = {{ $dp }};
let sisaBayar = {{ $sisaBayar }};
let currentStatusBayar = '{{ $statusBayar }}';

// Format angka ke Rupiah
function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Format input saat user mengetik
document.getElementById('jumlahBayarDisplay').addEventListener('input', function(e) {
    let value = this.value.replace(/\./g, '');
    value = value.replace(/[^0-9]/g, '');
    
    if (value) {
        this.value = formatRupiah(value);
        document.getElementById('jumlahBayar').value = value;
    } else {
        this.value = '';
        document.getElementById('jumlahBayar').value = '0';
    }
});

function openModalBayar() {
    document.getElementById('modalBayar').classList.remove('hidden');
}

function closeModalBayar() {
    document.getElementById('modalBayar').classList.add('hidden');
}

// Handle form submit dengan AJAX
document.getElementById('formBayar').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const jumlahBayar = parseInt(document.getElementById('jumlahBayar').value) || 0;
    
    if (jumlahBayar < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Nominal Tidak Valid',
            text: 'Nominal pembayaran tidak boleh negatif!',
            confirmButtonColor: '#ef4444',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold'
            }
        });
        return;
    }
    
    if (jumlahBayar > sisaBayar) {
        Swal.fire({
            icon: 'warning',
            title: 'Nominal Melebihi Tagihan',
            html: `<div class="text-gray-600">Sisa bayar: <strong>Rp ${formatRupiah(sisaBayar)}</strong><br>Nominal yang Anda masukkan melebihi sisa tagihan!</div>`,
            confirmButtonColor: '#f59e0b',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold'
            }
        });
        return;
    }
    
    if (jumlahBayar === 0) {
        Swal.fire({
            title: 'Konfirmasi',
            html: '<div class="text-gray-600">Anda tidak memasukkan DP.<br>Status pembayaran akan tetap <strong class="text-red-600">BELUM BAYAR</strong>.<br><br>Lanjutkan?</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                prosesSubmitPembayaran(jumlahBayar);
            }
        });
        return;
    }
    
    prosesSubmitPembayaran(jumlahBayar);
});

function prosesSubmitPembayaran(jumlahBayar) {
    Swal.fire({
        title: 'Memproses Pembayaran...',
        html: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = new FormData(document.getElementById('formBayar'));
    
    fetch("{{ route('riwayat.bayar.submit', $transaksi->id_transaksi) }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        Swal.close();
        
        if (data.success) {
            dpTerbayar = data.dp_terbayar || (dpTerbayar + jumlahBayar);
            sisaBayar = data.sisa_bayar || (totalTagihan - dpTerbayar);
            currentStatusBayar = data.status_bayar || currentStatusBayar;
            
            updatePaymentUI();
            closeModalBayar();
            
            if (jumlahBayar === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Transaksi Disimpan',
                    html: `
                        <div class="text-left space-y-2 mt-4">
                            <div class="flex justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-gray-600 font-semibold">Status Pembayaran:</span>
                                <span class="font-bold text-red-600 uppercase">Belum Bayar</span>
                            </div>
                            <div class="flex justify-between p-3 bg-red-50 rounded-lg">
                                <span class="text-gray-600">Sisa Bayar:</span>
                                <span class="font-bold text-red-700">Rp ${formatRupiah(sisaBayar)}</span>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6b7280',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-xl px-6 py-3 font-bold'
                    }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    html: `
                        <div class="text-left space-y-2 mt-4">
                            <div class="flex justify-between p-3 bg-blue-50 rounded-lg">
                                <span class="text-gray-600">Jumlah Bayar:</span>
                                <span class="font-bold">Rp ${formatRupiah(jumlahBayar)}</span>
                            </div>
                            <div class="flex justify-between p-3 bg-yellow-50 rounded-lg">
                                <span class="text-gray-600">Total DP:</span>
                                <span class="font-bold text-yellow-700">Rp ${formatRupiah(dpTerbayar)}</span>
                            </div>
                            <div class="flex justify-between p-3 bg-green-50 rounded-lg">
                                <span class="text-gray-600">Sisa Bayar:</span>
                                <span class="font-bold text-green-700">Rp ${formatRupiah(sisaBayar)}</span>
                            </div>
                            <div class="flex justify-between p-4 bg-purple-50 rounded-lg border-2 ${sisaBayar <= 0 ? 'border-green-500' : 'border-purple-300'}">
                                <span class="text-gray-600 font-semibold">Status:</span>
                                <span class="font-bold ${sisaBayar <= 0 ? 'text-green-600' : 'text-yellow-600'} uppercase">${currentStatusBayar}</span>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#22c55e',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-xl px-6 py-3 font-bold'
                    }
                }).then(() => {
                    if (sisaBayar <= 0) {
                        location.reload();
                    }
                });
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Gagal',
                text: data.message || 'Terjadi kesalahan saat memproses pembayaran',
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold'
                }
            });
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            html: `
                <div class="text-gray-600">
                    <p>Gagal menghubungi server.</p>
                    <p class="text-sm mt-2 text-red-600">${error.message}</p>
                    <p class="text-sm mt-2">Data mungkin sudah tersimpan. Silakan refresh halaman untuk memastikan.</p>
                </div>
            `,
            confirmButtonText: 'Refresh Halaman',
            showCancelButton: true,
            cancelButtonText: 'Tutup',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });
    });
}

function updatePaymentUI() {
    const dpDisplay = document.getElementById('dpDisplay');
    const dpAmount = document.getElementById('dpAmount');
    const modalDpDisplay = document.getElementById('modalDpDisplay');
    const modalDpAmount = document.getElementById('modalDpAmount');
    
    if (dpTerbayar > 0) {
        dpDisplay.classList.remove('hidden');
        modalDpDisplay.classList.remove('hidden');
        dpAmount.textContent = 'Rp ' + formatRupiah(dpTerbayar);
        modalDpAmount.textContent = 'Rp ' + formatRupiah(dpTerbayar);
    }
    
    document.getElementById('sisaBayarDisplay').textContent = 'Rp ' + formatRupiah(sisaBayar);
    document.getElementById('jumlahBayarDisplay').value = formatRupiah(sisaBayar);
    document.getElementById('jumlahBayar').value = sisaBayar;
    
    const statusBayarDisplay = document.getElementById('statusBayarDisplay');
    statusBayarDisplay.className = 'inline-block px-5 py-3 rounded-xl capitalize font-bold w-full text-center';
    
    if (currentStatusBayar === 'lunas') {
        statusBayarDisplay.classList.add('bg-green-100', 'text-green-700', 'border', 'border-green-200');
        statusBayarDisplay.textContent = 'Lunas';
        document.getElementById('btnBayarSekarang').classList.add('hidden');
    } else if (currentStatusBayar === 'DP') {
        statusBayarDisplay.classList.add('bg-yellow-100', 'text-yellow-700', 'border', 'border-yellow-200');
        statusBayarDisplay.textContent = 'DP';
    } else {
        statusBayarDisplay.classList.add('bg-red-100', 'text-red-700', 'border', 'border-red-200');
        statusBayarDisplay.textContent = 'Belum Bayar';
    }
}

function confirmBatal() {
    Swal.fire({
        title: 'Batalkan Transaksi?',
        html: '<div class="text-gray-600">Transaksi <strong>TRX/{{ $transaksi->id_transaksi }}</strong> akan dibatalkan.<br>Tindakan ini tidak dapat diurungkan.</div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#fb923c',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-x-lg"></i> Ya, Batalkan!',
        cancelButtonText: 'Tidak',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg',
            cancelButton: 'rounded-xl px-6 py-3 font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Membatalkan...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('formBatal').submit();
        }
    });
}

function confirmHapus() {
    Swal.fire({
        title: 'Hapus Transaksi?',
        html: '<div class="text-gray-600">Transaksi <strong>TRX/{{ $transaksi->id_transaksi }}</strong> akan dihapus permanen.<br><span class="text-red-600 font-semibold">Data tidak dapat dikembalikan!</span></div>',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-trash-fill"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg',
            cancelButton: 'rounded-xl px-6 py-3 font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('formHapus').submit();
        }
    });
}
</script>

@endsection