@extends('layouts.master')

@section('title', 'Transaksi')

@section('content')

@php
$detail = session('detail_transaksi', []);
$keterangan = session('keterangan_transaksi', '');

@endphp

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
   <a href="{{ route('transaksi.reset') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Transaksi</span>
</div>

{{-- WRAPPER + BAWAH DITAMBAH PAD BIAR TIDAK KETUTUP FOOTER --}}
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
        @if (!$detail || count($detail) === 0)
            <div class="text-center py-10">
                <i class="bi bi-search text-7xl text-yellow-400"></i>
                <p class="mt-4 font-semibold text-gray-600">List Layanan kosong</p>
                <p class="text-sm text-gray-500">Silahkan tambahkan layanan terlebih dahulu</p>
            </div>
        @else
            <div class="space-y-5">
                @foreach ($detail as $d)
                <div class="group bg-gray-100 p-5 rounded-3xl shadow hover:shadow-lg transition">

                    <div class="flex gap-4">

                        {{-- GAMBAR --}}
                        <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white border border-gray-200 flex items-center justify-center">
                            <img src="{{ asset('images/default.png') }}"
                                class="w-full h-full object-cover">
                        </div>

                        {{-- DETAIL --}}
                        <div class="flex-1">
                            <p class="font-bold text-lg leading-tight">
                                {{ $d['nama_layanan'] }}
                            </p>

                            <p class="text-sm text-gray-700">
                                Rp{{ number_format($d['harga'],0,',','.') }} / {{ $d['satuan'] }}
                            </p>

                            <p class="text-sm text-gray-600 flex items-center gap-1 mt-1">
                                <i class="bi bi-bag-heart-fill text-red-500"></i>
                                {{ empty($d['parfum_nama']) || $d['parfum_nama'] === 'Pilih Parfum'
                                ? 'Tanpa parfum'
                                : $d['parfum_nama'] }}  
                            </p>

                            <p class="font-semibold mt-1">
                                SubTotal : Rp{{ number_format($d['harga'] * $d['qty'],0,',','.') }}
                            </p>
                        </div>

                        {{-- QTY + REMOVE --}}
                        <div class="flex flex-col items-end">
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Qty</p>
                                <p class="text-lg font-bold">{{ $d['qty'] }} {{ $d['satuan'] }}</p>
                            </div>

                            <form 
                                action="{{ route('transaksi.remove', $d['id_layanan']) }}" 
                                method="POST"
                                class="mt-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none group-hover:pointer-events-auto"
                            >
                                @csrf
                                <button type="submit" class="bg-red-500 text-white p-2 rounded-full shadow">
                                    <i class="bi bi-trash-fill text-lg"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
        @endif

        {{-- KETERANGAN TRANSAKSI (1 UNTUK 1 TRANSAKSI) --}}
        <form id="checkoutForm" action="{{ route('transaksi.checkout') }}" method="POST">
            @csrf
            <div class="bg-white rounded-3xl p-5 shadow-xl mt-6">
                <p class="font-semibold mb-2">Keterangan</p>

                <textarea name="keterangan" id="keteranganTransaksi"
                        class="w-full p-3 border rounded-xl"
                        placeholder="Tambahkan keterangan untuk transaksi...">{{ $keterangan }}</textarea>

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

<div class="fixed bottom-0 left-0 w-full bg-yellow-400 px-5 py-5 flex justify-between items-center shadow-xl">
    <div>
        <p class="text-sm">Total Harga</p>
        <p class="text-2xl font-bold">Rp. {{ number_format($totalHarga, 0, ',', '.') }}</p>
    </div>

    <button type="submit" form="checkoutForm"
        class="bg-green-600 hover:bg-green-700 text-white px-7 py-3 rounded-2xl text-lg shadow">
        Checkout
    </button>

    <!-- POPUP ALERT -->
<div id="popupAlert"
    class="fixed inset-0 bg-black/40 flex items-center justify-center px-6 z-[999] hidden">

    <div class="bg-white rounded-3xl shadow-xl p-7 w-full max-w-xs text-center animate__animated animate__fadeInUp">

        <div class="w-20 h-20 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-exclamation-lg text-white text-5xl"></i>
        </div>

        <p id="popupAlertMessage" class="text-lg font-semibold text-gray-700 mb-4">
            <!-- isi dari JS -->
        </p>

        <button id="popupAlertOk"
            class="w-full bg-yellow-500 py-3 rounded-2xl font-bold text-black shadow hover:bg-yellow-600 transition">
            OK
        </button>
    </div>
</div>
</div>

<script>
document.querySelector("button[form='checkoutForm']").addEventListener("click", function (e) {

    const totalLayanan = {{ count($detail) }};

    if (totalLayanan === 0) {
        e.preventDefault();

        // munculin popup
        document.getElementById("popupAlertMessage").textContent =
            "Silakan tambahkan layanan terlebih dahulu sebelum checkout!";

        document.getElementById("popupAlert").classList.remove("hidden");
    }
});

// tombol OK menutup popup
document.getElementById("popupAlertOk").addEventListener("click", function () {
    document.getElementById("popupAlert").classList.add("hidden");
});
</script>



@endsection
