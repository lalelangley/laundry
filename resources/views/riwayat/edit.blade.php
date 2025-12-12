@extends('layouts.master')

@section('content')

<div class="min-h-screen bg-gray-100 pb-32">

    {{-- ================== BACK BUTTON + HEADER ================== --}}
    @php
        $backUrl = request()->from == 'dashboard'
            ? route('admin.dashboard')
            : route('riwayat.detail', $detail->id_transaksi);
    @endphp

    <div class="bg-yellow-400 px-6 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ $backUrl }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-black">Edit Transaksi</span>
    </div>

    {{-- ================== USER INFO ================== --}}
    <div class="bg-white mx-4 mt-4 rounded-xl p-4 flex items-center gap-4 shadow">
        <img src="{{ $pelanggan->gambar ?? asset('img/default-user.png') }}"
            class="w-16 h-16 rounded-full object-cover">

        <div>
            <div class="text-xl font-semibold">{{ $pelanggan->nama }}</div>
            <div class="text-gray-600 flex items-center gap-2">
                <i class="bi bi-phone text-yellow-500"></i>
                {{ $pelanggan->no_hp }}
            </div>
        </div>
    </div>

    {{-- ================== DETAIL ORDER HEADER ================== --}}
    <div class="mx-4 mt-6 bg-white rounded-xl shadow p-4 flex items-center justify-between">
        <div class="flex items-center gap-3 text-red-500 text-lg font-bold">
            <i class="bi bi-basket-fill text-2xl"></i> Detail Order
        </div>

        <a href="{{ route('riwayat.addLayanan', $detail->id_transaksi) }}"
           class="bg-yellow-400 px-4 py-2 rounded-xl text-black font-semibold hover:bg-yellow-300 transition">
            Tambah Layanan
        </a>
    </div>

    {{-- ================== DETAIL LAYANAN ================== --}}
    <div class="mx-4 mt-3 space-y-4">

        {{-- CARD LAYANAN --}}
        <div class="bg-white rounded-xl p-4 shadow flex gap-4 cursor-pointer"
             onclick="openModalLayanan('{{ $detail->jenis->nama_jenis }}', '{{ $detail->qty }}', '{{ $detail->id_parfum }}')">

            <img src="{{ $detail->jenis->gambar ?? asset('img/noimage.png') }}"
                 class="w-20 h-20 rounded-lg object-cover">

            <div class="flex-1">
                <div class="text-lg font-semibold">
                    {{ $detail->jenis->nama_jenis }}
                </div>

                <div class="text-gray-600 text-sm">
                    Rp{{ number_format($detail->harga,0,',','.') }} /
                    {{ $detail->jenis->satuan->nama_satuan ?? 'Pcs' }}
                </div>

                <div class="mt-2 font-semibold">
                    SubTotal: Rp{{ number_format($detail->qty * $detail->harga,0,',','.') }}
                </div>
            </div>

            <div class="text-right">
                <div class="font-bold text-gray-800">Qty</div>
                <div>{{ $detail->qty }} {{ $detail->jenis->satuan->nama_satuan ?? 'Pcs' }}</div>

                {{-- BUTTON EDIT --}}
                <button type="button"
                        class="mt-3 bg-yellow-400 text-black font-bold px-3 py-1 rounded-lg shadow hover:bg-yellow-500 transition"
                        onclick="event.stopPropagation(); openModalLayanan('{{ $detail->jenis->nama_jenis }}','{{ $detail->qty }}','{{ $detail->id_parfum }}');">
                    <i class="bi bi-pencil-fill"></i>
                </button>
            </div>
        </div>

        {{-- KETERANGAN --}}
        <div class="bg-white rounded-xl p-4 shadow">
            <label class="font-semibold text-gray-700">Keterangan</label>
            <textarea 
                class="mt-2 w-full p-3 bg-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-400"
                placeholder="Contoh : Baju merah luntur"
                name="keterangan"
                form="updateLayananForm"
            >{{ $detail->keterangan }}</textarea>
        </div>
    </div>

</div>

{{-- ================== BOTTOM CHECKOUT BAR ================== --}}
<div class="fixed bottom-0 left-0 right-0 bg-yellow-400 px-6 py-4 flex items-center justify-between shadow-2xl">
    <div>
        <div class="text-white text-sm">Total Harga</div>
        <div class="text-xl font-semibold">
            Rp{{ number_format($detail->harga * $detail->qty, 0, ',', '.') }}
        </div>
    </div>

    <form id="updateLayananForm" 
        action="{{ route('riwayat.update_layanan', $detail->id_detail_transaksi) }}"
        method="POST">
        @csrf
        @method('PUT')

        <button type="submit"
                class="bg-green-600 text-white px-6 py-3 rounded-xl font-semibold text-lg hover:bg-green-700 transition">
            Checkout
        </button>
    </form>
</div>

{{-- ====================== MODAL LAYANAN ====================== --}}
<div id="modalLayanan"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center px-4 z-[999] hidden">

    <div id="modalBox"
         class="bg-yellow-400 w-full max-w-md mx-auto rounded-3xl shadow-2xl overflow-hidden">

        <div class="p-6 text-center">
            <h2 id="modalTitle" class="text-2xl font-extrabold text-black"></h2>
        </div>

        {{-- WHITE BOX --}}
        <div class="bg-white p-6 rounded-t-3xl space-y-6">

            {{-- QTY --}}
            <div>
                <label class="block font-bold text-gray-800 mb-2">Masukan jumlah Kuantitas</label>

                <div class="relative">
                    <img src="{{ asset('img/qty.png') }}" 
                         class="w-7 absolute left-4 top-1/2 -translate-y-1/2 opacity-70">
                    <input id="qtyInput" type="number" step="0.01"
                           class="w-full pl-14 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 outline-none transition"
                           placeholder="1">
                </div>
            </div>

            {{-- PARFUM --}}
            <div>
                <label class="block font-bold text-gray-800 mb-2">Pilih Parfum</label>

                <div class="relative">
                    <img src="{{ asset('img/parfum.png') }}" 
                         class="w-7 absolute left-4 top-1/2 -translate-y-1/2 opacity-80">

                    <select id="parfumSelect"
                            class="w-full pl-14 pr-10 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 outline-none bg-white appearance-none">
                        <option value="">Pilih Parfum</option>
                        @foreach ($parfum as $p)
                            <option value="{{ $p->id_parfum }}">{{ $p->nama_parfum }}</option>
                        @endforeach
                    </select>

                    <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                </div>
            </div>

            <button id="btnSave"
                    class="w-full bg-black text-white py-3 rounded-xl font-bold text-lg hover:bg-gray-800 transition">
                Simpan
            </button>
        </div>
    </div>
</div>

{{-- ================== SCRIPT ================== --}}
<script>
    function openModalLayanan(namaJenis, qtyAwal, parfumId) {
        document.getElementById('modalTitle').innerText = namaJenis;
        document.getElementById('qtyInput').value = qtyAwal ?? 1;
        document.getElementById('parfumSelect').value = parfumId ?? "";

        document.getElementById('modalLayanan').classList.remove('hidden');
    }

    function closeModalLayanan() {
        document.getElementById('modalLayanan').classList.add('hidden');
    }

    // Click outside modal → close
    document.getElementById('modalLayanan').addEventListener('click', function(e){
        if(e.target.id === 'modalLayanan'){
            closeModalLayanan();
        }
    });
</script>

@endsection
