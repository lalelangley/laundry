@extends('layouts.master')

@section('title', 'Pilih Pelanggan')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
    <a href="{{ route('kasir.transaksi.create') }}" 
       class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Pilih Pelanggan</span>
</div>

{{-- SEARCH --}}
<div class="px-5 mt-5">
    <div class="bg-white rounded-2xl px-4 py-3 flex items-center shadow hover:shadow-lg transition-all">
        <i class="bi bi-search text-yellow-500 text-xl mr-3"></i>
        <input type="text" id="searchPelanggan" placeholder="Cari pelanggan..." class="w-full focus:outline-none text-lg">
    </div>
</div>

{{-- LIST --}}
<div class="px-5 mt-6 space-y-4 mb-24" id="listPelanggan">
    @foreach ($pelanggan as $item)
    <a href="{{ route('kasir.transaksi.setPelanggan', $item->id_pelanggan) }}"
       class="block bg-white rounded-2xl px-4 py-4 flex gap-3 items-center shadow hover:shadow-xl hover:bg-yellow-50 transition-all">

        <div class="w-16 h-16 bg-gray-200 rounded-xl flex items-center justify-center">
            <i class="bi bi-person text-3xl text-gray-500"></i>
        </div>

        <div>
            <div class="text-xl font-semibold">{{ $item->nama_pelanggan }}</div>
            <div class="flex items-center text-gray-600 text-base mt-1">
                <i class="bi bi-telephone me-2"></i>{{ $item->no_hp }}
            </div>
        </div>
    </a>
    @endforeach
</div>

@endsection

@section('scripts')
<script>
    const input = document.getElementById('searchPelanggan');
    input.addEventListener('keyup', function() {
        const filter = input.value.toLowerCase();
        document.querySelectorAll('#listPelanggan a').forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
</script>
@endsection
