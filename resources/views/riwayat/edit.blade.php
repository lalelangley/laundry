@extends('layouts.master')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $riwayat = $detail->first()?->transaksi;
    $from = request('from');

    if ($from === 'pesanan_online') {
        $backUrl = route('pesanan.online.index');
    } elseif ($riwayat) {
        $backUrl = route('riwayat.detail', $riwayat->id_transaksi);
    } else {
        $backUrl = url()->previous();
    }
@endphp


<div class="min-h-screen bg-gray-100 pb-32">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-6 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ $backUrl }}" class="text-black text-3xl font-bold">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Edit Transaksi</span>
    </div>

    {{-- USER --}}
    <div class="bg-white mx-4 mt-4 rounded-xl p-4 flex items-center gap-4 shadow">
        <img src="{{ $pelanggan->gambar ?? asset('img/default-user.png') }}"
             class="w-16 h-16 rounded-full object-cover">
        <div>
            <div class="text-xl font-semibold">
                {{ $pelanggan->nama_pelanggan ?? 'Pelanggan Umum' }}
            </div>
            <div class="text-gray-600">
                {{ $pelanggan->no_hp ?? '-' }}
            </div>
        </div>
    </div>

    {{-- DETAIL ORDER --}}
    <div class="mx-4 mt-6 bg-white rounded-xl shadow p-4 flex justify-between items-center">
        <div class="flex items-center gap-2 text-red-500 font-bold">
            <i class="bi bi-basket-fill"></i> Detail Order
        </div>

        @if($riwayat)
            <a href="{{ route('riwayat.add_layanan_page', $riwayat->id_transaksi) }}"
               class="bg-yellow-400 px-4 py-2 rounded-xl font-semibold">
                Tambah Layanan
            </a>
        @endif
    </div>

    {{-- LIST LAYANAN --}}
   @if($riwayat)
    <form action="{{ route('riwayat.update', $riwayat->id_transaksi) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mx-4 mt-4 space-y-4" id="layananList">
                @foreach ($detail as $d)
                <div class="bg-white rounded-xl p-4 shadow flex gap-4 layanan-item cursor-pointer"
                    data-id="{{ $d->id_detail_transaksi }}"
                    data-nama="{{ optional($d->jenis)->nama_jenis }}"
                    data-qty="{{ $d->qty }}"
                    data-parfum="{{ $d->id_parfum }}"
                    data-harga="{{ $d->harga }}"
                    data-satuan="{{ optional($d->jenis->satuan)->nama_satuan ?? 'Pcs' }}"
                    onclick="openModalLayanan(this)">

                    <img src="{{ optional($d->jenis)->gambar ?? asset('img/noimage.png') }}"
                        class="w-20 h-20 rounded-lg object-cover">

                    <div class="flex-1">
                        <div class="font-semibold text-lg">
                            {{ optional($d->jenis)->nama_jenis ?? '-' }}
                        </div>
                        <div class="text-gray-600 text-sm">
                            Rp{{ number_format($d->harga,0,',','.') }} /
                            {{ optional($d->jenis->satuan)->nama_satuan ?? 'Pcs' }}
                        </div>
                        <div class="mt-1 font-semibold subtotal">
                            SubTotal: Rp{{ number_format($d->qty * $d->harga,0,',','.') }}
                        </div>

                        <input type="hidden" name="detail[{{ $d->id_detail_transaksi }}][qty]" value="{{ $d->qty }}" class="qty-input">
                        <input type="hidden" name="detail[{{ $d->id_detail_transaksi }}][id_parfum]" value="{{ $d->id_parfum }}" class="parfum-input">
                    </div>

                    <div class="text-right">
                        <div class="font-bold">Qty</div>
                        <div class="qty-display">
                            {{ $d->qty }} {{ optional($d->jenis->satuan)->nama_satuan ?? 'Pcs' }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- BOTTOM BAR --}}
            <div class="fixed bottom-0 left-0 right-0 bg-yellow-400 px-6 py-4 flex justify-between items-center shadow-xl">
                <div>
                    <div class="text-white text-sm">Total Harga</div>
                    <div class="text-xl font-bold" id="totalHarga">
                        Rp{{ number_format($detail->sum(fn($d) => $d->qty * $d->harga),0,',','.') }}
                    </div>
                </div>

                <button class="bg-black text-white px-6 py-3 rounded-xl font-bold">
                    Simpan
                </button>
            </div>
        </form>
    @endif
</div>

{{-- MODAL --}}
<div id="modalLayanan"
     class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h2 id="modalTitle" class="text-xl font-bold mb-4"></h2>

        <input id="qtyInput" type="number" step="0.01"
               class="w-full border rounded p-2 mb-3">

        <select id="parfumSelect" class="w-full border rounded p-2 mb-4">
            <option value="">Pilih Parfum</option>
            @foreach($parfum as $p)
                <option value="{{ $p->id_parfum }}">{{ $p->nama_parfum }}</option>
            @endforeach
        </select>

        <button id="btnSave" class="w-full bg-black text-white py-2 rounded">
            Simpan
        </button>
    </div>
</div>

{{-- SCRIPT --}}
<script>
let currentCard = null;

function openModalLayanan(card){
    currentCard = card;
    modalLayanan.classList.remove('hidden');
    modalTitle.innerText = card.dataset.nama;
    qtyInput.value = card.dataset.qty;
    parfumSelect.value = card.dataset.parfum;
}

btnSave.onclick = () => {
    const qty = parseFloat(qtyInput.value);
    const harga = parseFloat(currentCard.dataset.harga);

    currentCard.dataset.qty = qty;
    currentCard.querySelector('.qty-input').value = qty;
    currentCard.querySelector('.qty-display').innerText =
        qty + ' ' + currentCard.dataset.satuan;
    currentCard.querySelector('.subtotal').innerText =
        'SubTotal: Rp' + (qty * harga).toLocaleString('id-ID');

    updateTotal();
    modalLayanan.classList.add('hidden');
};

function updateTotal(){
    let total = 0;
    document.querySelectorAll('.layanan-item').forEach(c=>{
        total += parseFloat(c.dataset.qty) * parseFloat(c.dataset.harga);
    });
    totalHarga.innerText = 'Rp' + total.toLocaleString('id-ID');
}
</script>

@endsection
