{{-- FE-DOC: Template frontend untuk resources/views/transaksi/create.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Transaksi')

@section('content')

@php
$detail = session('detail_transaksi', []);
$keterangan = session('keterangan_transaksi', '');
@endphp

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
   <a href="{{ route('admin.dashboard') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Transaksi</span>
</div>

<div class="p-4 space-y-6 pb-[180px]">

    {{-- CARD PELANGGAN --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between">
        <div class="flex items-center gap-4">
            <i class="bi bi-person-fill text-5xl text-gray-700"></i>

            <div>
                <p class="font-semibold text-lg">Pelanggan</p>

                @if ($pelanggan)
                    <p class="text-xl font-bold leading-tight">{{ $pelanggan['nama_pelanggan'] }}</p>
                    <p class="text-sm text-gray-500">{{ $pelanggan['no_hp'] }}</p>
                @else
                    <p class="text-sm text-gray-500">Silahkan pilih pelanggan terlebih dahulu</p>
                @endif
            </div>
        </div>

        <a href="{{ route('transaksi.pelanggan') }}"
            class="bg-yellow-400 px-4 py-3 rounded-2xl text-black font-bold shadow hover:bg-yellow-500 transition">
            {{ $pelanggan ? 'Ganti' : 'Cari' }}
        </a>
    </div>


    {{-- DETAIL ORDER --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <i class="bi bi-basket-fill text-4xl text-red-500"></i>
                <span class="text-xl font-semibold">Detail Order</span>
            </div>

            <a href="{{ route('layanan.index', ['from' => 'transaksi']) }}"
                class="bg-yellow-400 px-4 py-3 rounded-2xl text-black font-bold shadow hover:bg-yellow-500 transition">
                Tambah Layanan
            </a>
        </div>


        {{-- LIST LAYANAN --}}
        @if (count($detail) === 0)

            <div class="text-center py-10">
                <i class="bi bi-search text-7xl text-yellow-400"></i>
                <p class="mt-4 font-semibold text-gray-600">List Layanan kosong</p>
                <p class="text-sm text-gray-500">Silahkan tambahkan layanan terlebih dahulu</p>
            </div>

        @else
            <div class="space-y-5">
                {{-- ✅ PAKAI INDEX LOOP --}}
                @foreach ($detail as $index => $d)
                <div class="group bg-gray-100 p-5 rounded-3xl shadow hover:shadow-lg transition">

                    <div class="flex gap-4">

                        {{-- ✅ GAMBAR FIXED - PAKAI STORAGE --}}
                        <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white border border-gray-200 flex items-center justify-center flex-shrink-0">
                            @php
                                $imagePath = isset($d['gambar']) && !empty($d['gambar']) 
                                    ? 'storage/' . $d['gambar'] 
                                    : 'images/default.png';
                            @endphp
                            
                            <img src="{{ asset($imagePath) }}"
                                alt="{{ $d['nama_layanan'] ?? 'Layanan' }}"
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null; this.src='{{ asset('images/default.png') }}';">
                        </div>

                        {{-- DETAIL --}}
                        <div class="flex-1">
                            <p class="font-bold text-lg leading-tight">
                                {{ $d['nama_layanan'] }}
                                @if(isset($d['jenis']))
                                    <span class="text-gray-600">({{ $d['jenis'] }})</span>
                                @endif
                            </p>

                            <p class="text-sm text-gray-700">
                                Rp{{ number_format($d['harga'],0,',','.') }}
                            </p>

                            <p class="text-sm text-gray-600 flex items-center gap-1 mt-1">
                                <i class="bi bi-bag-heart-fill text-red-500"></i>
                                {{ empty($d['parfum_nama']) || $d['parfum_nama'] === 'Pilih Parfum'
                                ? 'Tanpa parfum'
                                : $d['parfum_nama'] }}  
                            </p>

                            <p class="font-semibold mt-1">
                                SubTotal: Rp{{ number_format($d['harga'] * $d['qty'],0,',','.') }}
                            </p>
                        </div>

                        {{-- QTY + REMOVE --}}
                        <div class="flex flex-col items-end justify-between">
                            <div class="text-center">
                                <p class="text-sm font-semibold text-gray-700">Qty</p>
                                <p class="text-lg font-bold">{{ $d['qty'] }}</p>
                            </div>

                            {{-- ✅ SWEET ALERT DELETE - PAKAI INDEX --}}
                            <form 
                                action="{{ route('transaksi.remove', $index) }}" 
                                method="POST"
                                class="opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none group-hover:pointer-events-auto delete-layanan-form"
                            >
                                @csrf
                                <button type="button" 
                                        class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow transition btn-delete-layanan"
                                        data-layanan="{{ $d['nama_layanan'] }}"
                                        data-index="{{ $index }}">
                                    <i class="bi bi-trash-fill text-lg"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
        @endif


        {{-- KETERANGAN TRANSAKSI --}}
        <form id="checkoutForm" action="{{ route('transaksi.checkout') }}" method="POST">
            @csrf
            <div class="bg-white rounded-3xl p-5 shadow-xl mt-6">
                <p class="font-semibold mb-2">Keterangan</p>

                <textarea name="keterangan" id="keteranganTransaksi"
                    class="w-full p-3 border rounded-xl"
                    placeholder="Tambahkan keterangan untuk transaksi...">{{ $keterangan }}</textarea>

                {{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}

                <script>
                    document.getElementById('keteranganTransaksi').addEventListener('input', function () {
                        fetch("{{ route('transaksi.updateKeterangan') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            },
                            body: JSON.stringify({ keterangan: this.value })
                        });
                    });
                </script>

            </div>
        </form>
    </div>
</div>

{{-- FOOTER FIXED --}}
@php
    $totalHarga = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));
@endphp

<div class="fixed bottom-0 left-0 w-full bg-yellow-400 px-5 py-5 flex justify-between items-center shadow-xl z-50 desktop-docked-bar lg:bottom-4 lg:rounded-[28px]">
    <div>
        <p class="text-sm">Total Harga</p>
        <p class="text-2xl font-bold">Rp. {{ number_format($totalHarga, 0, ',', '.') }}</p>
    </div>

    {{-- Button trigger modal --}}
    <button type="button" id="btnCheckout"
            class="bg-green-600 hover:bg-green-700 text-white px-7 py-3 rounded-2xl text-lg shadow font-bold transition">
        Checkout
    </button>
</div>

{{-- ✅ MODAL KONFIRMASI CHECKOUT --}}
<div id="modalCheckout" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white w-full max-w-2xl mx-auto rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto"
        onclick="event.stopPropagation()">
        
        {{-- Header Modal --}}
        <div class="bg-yellow-400 p-6 sticky top-0 z-10">
            <h2 class="text-2xl font-bold text-black text-center flex items-center justify-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                Konfirmasi Checkout
            </h2>
        </div>

        {{-- Body Modal --}}
        <div class="p-6 space-y-5">
            
            {{-- Info Pelanggan --}}
            <div class="bg-gray-50 p-4 rounded-xl">
                <h3 class="font-bold text-lg mb-3 flex items-center gap-2">
                    <i class="bi bi-person-fill text-xl"></i>
                    Informasi Pelanggan
                </h3>
                @if($pelanggan)
                <div class="space-y-1 text-sm">
                    <p><span class="font-semibold">Nama:</span> {{ $pelanggan['nama_pelanggan'] }}</p>
                    <p><span class="font-semibold">No. HP:</span> {{ $pelanggan['no_hp'] }}</p>
                </div>
                @endif
            </div>

            {{-- Detail Order --}}
            <div class="bg-gray-50 p-4 rounded-xl">
                <h3 class="font-bold text-lg mb-3 flex items-center gap-2">
                    <i class="bi bi-basket-fill text-xl text-red-500"></i>
                    Detail Order
                </h3>
                
                <div class="space-y-3 max-h-60 overflow-y-auto">
                    @foreach($detail as $d)
                    <div class="flex gap-3 bg-white p-3 rounded-lg shadow-sm">
                        {{-- ✅ GAMBAR DI MODAL - PAKAI STORAGE --}}
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center flex-shrink-0">
                            @php
                                $imagePath = isset($d['gambar']) && !empty($d['gambar']) 
                                    ? 'storage/' . $d['gambar'] 
                                    : 'images/default.png';
                            @endphp
                            
                            <img src="{{ asset($imagePath) }}"
                                alt="{{ $d['nama_layanan'] ?? 'Layanan' }}"
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null; this.src='{{ asset('images/default.png') }}';">
                        </div>
                        
                        {{-- Detail --}}
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm truncate">
                                {{ $d['nama_layanan'] }}
                                @if(isset($d['jenis']))
                                    <span class="text-gray-600">({{ $d['jenis'] }})</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-600">
                                {{ $d['qty'] }} x Rp{{ number_format($d['harga'], 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-600">
                                <i class="bi bi-bag-heart-fill text-red-500"></i>
                                {{ $d['parfum_nama'] ?? 'Tanpa parfum' }}
                            </p>
                        </div>
                        
                        {{-- Subtotal --}}
                        <div class="text-right">
                            <p class="font-bold text-sm">Rp{{ number_format($d['harga'] * $d['qty'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Keterangan --}}
            @if($keterangan)
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="font-semibold text-sm mb-1 flex items-center gap-2">
                    <i class="bi bi-chat-left-text-fill text-yellow-500"></i>
                    Keterangan:
                </p>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $keterangan }}</p>
            </div>
            @endif

            {{-- Total --}}
            <div class="bg-yellow-100 border-2 border-yellow-400 p-4 rounded-xl">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold">Total Pembayaran:</span>
                    <span class="text-2xl font-bold text-green-600">
                        Rp{{ number_format($totalHarga, 0, ',', '.') }}
                    </span>
                </div>
            </div>

        </div>

        {{-- Footer Modal --}}
        <div class="p-6 bg-gray-50 flex gap-3 sticky bottom-0">
            <button type="button" onclick="closeCheckoutModal()"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition">
                Batal
            </button>
            
            <a href="{{ route('transaksi.confirm') }}"
               class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl
                      font-bold shadow-lg transition text-center inline-flex
                      items-center justify-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                Lanjutkan
            </a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const modal = document.getElementById('modalCheckout');
const btnCheckout = document.getElementById('btnCheckout');

// Function buka modal
function openCheckoutModal() {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Function tutup modal
function closeCheckoutModal() {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

// Click button checkout
btnCheckout.addEventListener('click', function() {
    const pelanggan = @json($pelanggan);
    const detail = @json($detail);

    // ✅ VALIDASI PELANGGAN
    if (!pelanggan) {
        Swal.fire({
            title: "Pelanggan belum dipilih",
            text: "Silahkan pilih pelanggan terlebih dahulu.",
            icon: "warning",
            confirmButtonColor: "#facc15",
            confirmButtonText: "Mengerti"
        });
        return;
    }

    // ✅ VALIDASI LAYANAN
    if (!detail || detail.length === 0) {
        Swal.fire({
            title: "Layanan kosong",
            text: "Tambahkan layanan sebelum checkout.",
            icon: "warning",
            confirmButtonColor: "#facc15",
            confirmButtonText: "Oke"
        });
        return;
    }

    // ✅ BUKA MODAL kalau validasi OK
    openCheckoutModal();
});

// Click di luar modal untuk tutup
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeCheckoutModal();
    }
});

// ESC key untuk tutup modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
        closeCheckoutModal();
    }
});

// ✅ SWEET ALERT DELETE LAYANAN
document.addEventListener('DOMContentLoaded', function() {
    // Attach event ke semua button delete
    document.querySelectorAll('.btn-delete-layanan').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const namaLayanan = this.getAttribute('data-layanan');
            const index = this.getAttribute('data-index');
            const form = this.closest('.delete-layanan-form');
            
            Swal.fire({
                title: 'Hapus Layanan?',
                html: `Yakin ingin menghapus <b>${namaLayanan}</b> dari keranjang?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
