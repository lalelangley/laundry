@extends('layouts.master')

@section('content')
<<<<<<< HEAD

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
=======
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
$riwayat = $detail->first()?->transaksi;
$backUrl = request()->from === 'dashboard'
    ? route('admin.dashboard')
    : ($riwayat
        ? route('riwayat.detail', ['id' => $riwayat->id_transaksi])
        : url()->previous());
@endphp

{{-- HEADER --}}
<div class="bg-yellow-400 px-6 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
    <a href="{{ $backUrl }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold text-black">Edit Transaksi</span>
</div>

{{-- USER INFO --}}
<div class="bg-white mx-4 mt-4 rounded-xl p-4 flex items-center gap-4 shadow">
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

{{-- DETAIL ORDER HEADER --}}
<div class="mx-4 mt-6 bg-white rounded-xl shadow p-4 flex items-center justify-between">
    <div class="flex items-center gap-3 text-red-500 text-lg font-bold">
        <i class="bi bi-basket-fill text-2xl"></i> Detail Order
    </div>
    @if($riwayat)
        <a href="{{ route('riwayat.add_layanan_page', $riwayat->id_transaksi) }}"
           class="bg-yellow-400 px-4 py-2 rounded-xl text-black font-semibold">
            Tambah Layanan
        </a>
    @endif
</div>

{{-- DETAIL LAYANAN FORM --}}
<form action="{{ route('riwayat.update', $riwayat->id_transaksi) }}" method="POST">
    @csrf
    @method('PUT')


    <div class="mx-4 mt-3 space-y-4 pb-32" id="layananList">
        @foreach ($detail as $d)
        <div class="bg-white rounded-xl p-4 shadow flex gap-4 items-center layanan-item group"
             data-id="{{ $d->id_detail_transaksi }}"
             data-nama="{{ optional($d->jenis)->nama_jenis }}"
             data-qty="{{ $d->qty }}"
             data-parfum="{{ $d->id_parfum ?? '' }}"
             data-harga="{{ $d->harga }}"
             data-satuan="{{ optional($d->jenis->satuan)->nama_satuan ?? 'Pcs' }}"
             onclick="openModalLayanan(this)">

            {{-- IMAGE --}}
            <img src="{{ optional($d->jenis)->gambar ?? asset('img/noimage.png') }}"
                 class="w-20 h-20 rounded-lg object-cover">

            {{-- INFO --}}
            <div class="flex-1">
                <div class="text-lg font-semibold">
                    {{ optional($d->jenis)->nama_jenis ?? 'Jenis tidak tersedia' }}
                </div>
                <div class="text-gray-600 text-sm">
                    Rp{{ number_format($d->harga,0,',','.') }} / {{ optional($d->jenis->satuan)->nama_satuan ?? 'Pcs' }}
                </div>
                <div class="mt-2 font-semibold subtotal">
                    SubTotal: Rp{{ number_format($d->qty * $d->harga,0,',','.') }}
                </div>

                {{-- HIDDEN INPUT --}}
                <input type="hidden" name="detail[{{ $d->id_detail_transaksi }}][qty]" value="{{ $d->qty }}" class="qty-input">
                <input type="hidden" name="detail[{{ $d->id_detail_transaksi }}][id_parfum]" value="{{ $d->id_parfum ?? '' }}" class="parfum-input">
            </div>

            {{-- QTY + DELETE --}}
            <div class="flex flex-col items-end gap-2 text-right">
                <div>
                    <div class="font-bold text-gray-800">Qty</div>
                    <div class="qty-display">{{ $d->qty }} {{ optional($d->jenis->satuan)->nama_satuan ?? 'Pcs' }}</div>
                </div>

                <button type="button"
                    data-url="{{ route('riwayat.delete_detail', $d->id_detail_transaksi) }}"
                    onclick="event.stopPropagation(); deleteLayanan(this)"
                    class="bg-red-500 text-white px-3 py-1 rounded-lg text-sm opacity-0 group-hover:opacity-100 transition hover:bg-red-600 shadow">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- BOTTOM BAR --}}
    <div class="fixed bottom-0 left-0 right-0 bg-yellow-400 px-6 py-4 flex items-center justify-between shadow-2xl">
        <div>
            <div class="text-white text-sm">Total Harga</div>
            <div class="text-xl font-semibold" id="totalHarga">
                Rp{{ number_format($detail->sum(fn($d) => $d->qty * $d->harga),0,',','.') }}
            </div>
        </div>
        <button type="submit" class="bg-black text-white px-6 py-2 rounded-xl font-bold">
            Simpan Perubahan
        </button>
    </div>
</form>

{{-- MODAL LAYANAN --}}
<div id="modalLayanan" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-yellow-400 w-full max-w-md mx-auto rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6 text-center">
            <h2 id="modalTitle" class="text-2xl font-extrabold text-black"></h2>
        </div>
        <div class="bg-white p-6 rounded-t-3xl space-y-6">
            <div>
                <label class="block font-bold text-gray-800 mb-2">Masukkan jumlah Kuantitas</label>
                <div class="relative">
                    <img src="{{ asset('img/qty.png') }}" class="w-7 absolute left-4 top-1/2 -translate-y-1/2 opacity-70">
                    <input id="qtyInput" type="number" step="0.01" class="w-full pl-14 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 outline-none transition" placeholder="1">
                </div>
            </div>
            <div>
                <label class="block font-bold text-gray-800 mb-2">Pilih Parfum</label>
                <div class="relative">
                    <img src="{{ asset('img/parfum.png') }}" class="w-7 absolute left-4 top-1/2 -translate-y-1/2 opacity-80">
                    <select id="parfumSelect" class="w-full pl-14 pr-10 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 outline-none bg-white appearance-none">
>>>>>>> web
                        <option value="">Pilih Parfum</option>
                        @foreach ($parfum as $p)
                            <option value="{{ $p->id_parfum }}">{{ $p->nama_parfum }}</option>
                        @endforeach
                    </select>
<<<<<<< HEAD

                    <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                </div>
            </div>

            <button id="btnSave"
                    class="w-full bg-black text-white py-3 rounded-xl font-bold text-lg hover:bg-gray-800 transition">
                Simpan
            </button>
=======
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                </div>
            </div>
            <button type="button" id="btnSave" class="w-full bg-black text-white py-3 rounded-xl font-bold">Simpan</button>
>>>>>>> web
        </div>
    </div>
</div>

<<<<<<< HEAD
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

=======
{{-- SCRIPT --}}
<script>
function openModalLayanan(card){
    const modal = document.getElementById('modalLayanan');
    const title = document.getElementById('modalTitle');
    const qtyInput = document.getElementById('qtyInput');
    const parfumSelect = document.getElementById('parfumSelect');
    const btnSave = document.getElementById('btnSave');

    title.innerText = card.dataset.nama;
    qtyInput.value = card.dataset.qty ?? 1;
    parfumSelect.value = card.dataset.parfum ?? '';
    btnSave.dataset.id = card.dataset.id;

    modal.classList.remove('hidden');
}

function closeModalLayanan() {
    document.getElementById('modalLayanan').classList.add('hidden');
}

document.getElementById('modalLayanan').addEventListener('click', function(e){
    if(e.target.id === 'modalLayanan'){
        closeModalLayanan();
    }
});

// SAVE FROM MODAL
document.getElementById('btnSave').addEventListener('click', function(){
    const qty = parseFloat(document.getElementById('qtyInput').value);
    const parfum = document.getElementById('parfumSelect').value;
    const id = this.dataset.id;

    if(!qty || qty <= 0){
        alert("Masukkan qty valid");
        return;
    }

    const card = document.querySelector(`.layanan-item[data-id='${id}']`);
    
    // Update dataset & hidden input
    card.dataset.qty = qty;
    card.dataset.parfum = parfum;
    card.querySelector('.qty-input').value = qty;
    card.querySelector('.parfum-input').value = parfum;

    // Update tampilan
    card.querySelector('.qty-display').innerText = qty + ' ' + (card.dataset.satuan ?? 'Pcs');

    // Update subtotal
    const harga = parseFloat(card.dataset.harga);
    card.querySelector('.subtotal').innerText = 'SubTotal: Rp' + (qty * harga).toLocaleString('id-ID');

    updateTotalHarga();
    closeModalLayanan();
});

// Update total harga
function updateTotalHarga(){
    let total = 0;
    document.querySelectorAll('.layanan-item').forEach(card=>{
        const harga = parseFloat(card.dataset.harga);
        const qty = parseFloat(card.dataset.qty);
        total += harga * qty;
    });
    document.getElementById('totalHarga').innerText = 'Rp' + total.toLocaleString('id-ID');
}

// DELETE
function deleteLayanan(button){
    if(!confirm('Yakin hapus layanan ini?')) return;

    const url = button.dataset.url;

    fetch(url, {
        method:'DELETE',
        headers:{
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept':'application/json'
        }
    })
    .then(res=>res.json())
    .then(res=>{
        if(res.success) location.reload();
        else alert(res.message || 'Gagal menghapus layanan');
    })
    .catch(err=>{
        console.error(err);
        alert('Terjadi kesalahan server');
    });
}

</script>
>>>>>>> web
@endsection
